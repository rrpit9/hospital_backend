<?php

namespace App\Services\Handler;

use Exception;
use App\Models\Cart;
use Razorpay\Api\Api as RazorpayApi;
use App\Models\MasterOrder;
use App\Models\MasterPayment;
use App\Services\Handler\CartHandler;
use Illuminate\Database\Eloquent\Model;
use App\Services\Constants\OrderPaymentStatus;

class OrderPaymentHandler
{
    /** @var string|null */
    private $razorpayKey;

    /** @var string|null */
    private $razorpaySecret;

    public function __construct($razorpayKey = null, $razorpaySecret = null)
    {
        $this->razorpayKey = $razorpayKey ?? config('services.payment.razorpay_key');
        $this->razorpaySecret = $razorpaySecret ?? config('services.payment.razorpay_secret');
    }

    public function createMasterOrder(Model $user)
    {
        $cartHandler = new CartHandler();
        $cartdata = $cartHandler->getUserCartInfo($user);

        if(count($cartdata) <= 0){
            abort(422, 'Add in the cart to place any order');
        }

        $totalAmount = 0;$cartIds = [];
        foreach($cartdata as $index => $cart){
            $totalAmount += $cart->total_payable_amount;
            $cartIds[] = $cart->id;
        }
        $masterOrder = MasterOrder::create([
            'user_id' => $user->getKey(),
            'status' => MasterOrderAndCartStatus::INITIATED,
            'total_amount' => round($totalAmount, 2),
            'by_wallet' => 0,
            'by_online' => round($totalAmount, 2),
            'cart_ids' => implode(',',$cartIds),
        ]);
        Cart::whereIn('id',$cartIds)->update(['master_order_id' => $masterOrder->id]);
        return $masterOrder;
    }

    public function createPayment(Model $masterOrder, Model $user, $amount)
    {
        $razorpayApi = new RazorpayApi($this->razorpayKey, $this->razorpaySecret);

        $gatewayOrder = $razorpayApi->order->create([
            'amount' => $amount * 100, // Convert to paisa
            'receipt' => $masterOrder->getKey(),
            'payment_capture' => 1,
            'currency' => 'INR'
        ]);

        if (! $gatewayOrder) {
            throw new Exception("Failed to created Razorpay order. Item: [{$masterOrder->getMorphClass()}] [{$masterOrder->getKey()}]");
        }

        $payment = MasterPayment::create([
            'user_id' => $user->getKey(),
            'orderable_type' => $masterOrder->getMorphClass(),
            'orderable_id' => $masterOrder->getKey(),
            'status' => MasterPaymentStatus::INITIATED,
            'gateway' => 'Razorpay',
            'gateway_order_id' => $gatewayOrder->id,
            'description' => "Master Order Payment",
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'currency' => 'INR',
            'amount' => $amount,
            'ip' => request()->ip(),
            'order_response' => json_encode($gatewayOrder->toArray())
        ]);
        $masterOrder->update(['gateway_order_id' => $payment->gateway_order_id]);
        return $payment;
    }

    public function fetchMasterOrder($orderId, $user = null)
    {
        $searchWith = ['id' => $orderId];
        if($user){
            $searchWith['user_id'] = $user->id;
        }
        $order = MasterOrder::where($searchWith)->first();

        if (! $order) {
            abort(422, __('validation.order_not_found'));
        }

        if (!in_array($order->status, MasterOrderAndCartStatus::REPEAT_PAYMENT)) {
            abort(403, __('validation.order_already_processed'));
        }

        return $order;
    }

    public function fetchOrderPayment(Model $order, $gatewayOrderId)
    {
        $payment = $order->payments()->where([
            'user_id' => $order->user_id,
            'gateway_order_id' => $gatewayOrderId
        ])->first();

        if (! $payment) {
            abort(404, __('validation.payment_not_found'));
        }

        if (!in_array($payment->status,MasterPaymentStatus::REPEAT_PAYMENT)) {
            abort(403, __('validation.payment_processed'));
        }

        return $payment;
    }

    public function processPayment(Model $payment, Model $order , $attributes = [], $failedAtUserEnd = false)
    {
        try{
            if($failedAtUserEnd){
                throw new Exception('Payment failed at user end.');
            }
            $this->validatePaymentResponse($attributes);

            $gatewayOrderId = $attributes['razorpay_order_id'];

            if ($payment->gateway_order_id !== $gatewayOrderId) {
                throw new Exception("Failed to validate Razorpay payment. Gateway order id mismatch. Stored: [{$payment->gateway_order_id}] Request: [{$gatewayOrderId}]");
            }

            $gatewayPaymentId = $attributes['razorpay_payment_id'];

            $razorpayApi = new RazorpayApi($this->razorpayKey, $this->razorpaySecret);
            $gatewayPayment = $razorpayApi->payment->fetch($gatewayPaymentId);
            if (! $gatewayPayment) {
                throw new Exception("Failed to validate Razorpay payment. Unable to fetch payment. Payment: [{$payment->id}]");
            }

            if (mb_strtolower($gatewayPayment->status ?? '') !== 'captured') {
                throw new Exception("Failed to validate Razorpay payment. Invalid status. Payment: [{$payment->id}]");
            }

            // We validate the payment amount with a marginal difference of 1 rupee to allow for rounding errors.
            if (abs(($gatewayPayment->amount / 100) - $payment->amount) > 1) {
                throw new Exception("Failed to validate Razorpay payment. Amount mismatch. Payment: [{$payment->id}]");
            }

            $gatewayFee = round($gatewayPayment->fee / 100, 2);
	        $gatewayTax = round($gatewayPayment->tax / 100, 2);
	        $gatewayTaxRate = 0;
	        if ($gatewayFee > 0 && $gatewayFee > $gatewayTax ) {
		        $gatewayTaxRate = round(($gatewayTax / ($gatewayFee - $gatewayTax)) * 100, 2);
	        }

            $payment->update([
                'status' => MasterPaymentStatus::PAYMENT_COMPLETE,
                'gateway_status' => $gatewayPayment->status,
                'gateway_payment_id' => $gatewayPayment->id,
                'gateway_fee' => $gatewayFee,
                'gateway_gst_rate' => $gatewayTaxRate,
                'gateway_gst_amount' => $gatewayTax,
                'payment_method' => strtoupper($gatewayPayment->method),
                'payment_response' => json_encode($gatewayPayment->toArray()),
                'error_message' => null
            ]);
            /* Updating the master Order as Payment Complete*/
            $this->updateOrderStatus($order, MasterOrderAndCartStatus::PAYMENT_COMPLETE);
            return $payment;
        }catch (Exception $e) {
            $this->markPaymentasFailed($payment, $e->getMessage());
            throw $e;
        }
    }

    public function validatePaymentResponse($attributes = [])
    {
        if (! isset($attributes['razorpay_order_id'], $attributes['razorpay_payment_id'], $attributes['razorpay_signature'])) {
            throw new Exception('Invalid Payment Response.');
        }

        $payload = $attributes['razorpay_order_id'] . '|' . $attributes['razorpay_payment_id'];
        $expectedSignature = hash_hmac('sha256', $payload, $this->razorpaySecret);

        if (hash_equals($expectedSignature, $attributes['razorpay_signature']) === false) {
            throw new Exception('Payment Response Signature Validation Failed.');
        }
    }

    public function markPaymentasFailed(Model $payment, $errorMessage = null)
    {
        $payment->update([
            'status' => MasterPaymentStatus::PAYMENT_FAILED,
            'gateway_status' => 'failed',
            'error_message' => $errorMessage ? str_limit($errorMessage, 255) : null
        ]);
    }

    public function updateOrderStatus(Model $order, $status)
    {
        $orderUpdate = [
            'status' => $status
        ];
        if($status == MasterOrderAndCartStatus::FULFILLED){
            $orderUpdate['fulfilled_at'] = now();
        }
        if($status == MasterOrderAndCartStatus::PAYMENT_COMPLETE){
            $orderUpdate['paid_at'] = now();
        }
        /* master Order Status Update */
        $order->update($orderUpdate);

        /* User Cart Status Update */
        $order->cart()->where([
            'user_id' => $order->user_id
        ])->update(['status' => $status]);
    }
}