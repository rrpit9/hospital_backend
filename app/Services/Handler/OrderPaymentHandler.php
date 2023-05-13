<?php

namespace App\Services\Handler;

use Exception;
use Razorpay\Api\Api as RazorpayApi;
use App\Models\MasterOrder;
use App\Models\MasterPayment;
use Illuminate\Database\Eloquent\Model;
use App\Services\Constants\MasterOrderPaymentStatus;

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

    public function createMasterOrder(Model $order, Model $user)
    {
        $masterOrder = MasterOrder::create([
            'userable_type' => $user->getMorphClass(),
            'userable_id' => $user->getKey(),
            'orderable_type' => $order->getMorphClass(),
            'orderable_id' => $order->getKey(),
            'status' => MasterOrderPaymentStatus::INITIATED,
            'purchase_price' => round($order->purchase_price, 2),
            'discount_percentage' => round($order->discount_percentage, 2),
            'discount_amount' => round($order->discount_amount, 2),
            'discount_amount' => round($order->discount_amount, 2),
            'net_payable' => round($order->net_payable, 2),
            'by_wallet' => round($order->by_wallet, 2),
            'by_online' => round($order->by_online, 2),
            'igst_rate' => round($order->igst_rate, 2),
            'igst_amount' => round($order->igst_amount, 2),
            'cgst_rate' => round($order->cgst_rate, 2),
            'cgst_amount' => round($order->cgst_amount, 2),
            'sgst_rate' => round($order->sgst_rate, 2),
            'sgst_amount' => round($order->sgst_amount, 2),
            'ip' => request()->ip()
        ]);
        return $masterOrder;
    }

    public function createPayment(Model $masterOrder, Model $user)
    {
        $razorpayApi = new RazorpayApi($this->razorpayKey, $this->razorpaySecret);

        $gatewayOrder = $razorpayApi->order->create([
            'amount' => $masterOrder->by_online * 100, // Convert to paisa
            'receipt' => $masterOrder->getKey(),
            'payment_capture' => 1,
            'currency' => 'INR'
        ]);

        if (! $gatewayOrder) {
            throw new Exception("Failed to create Razorpay order.");
        }

        $payment = MasterPayment::create([
            'userable_type' => $masterOrder->userable_type,
            'userable_id' => $masterOrder->userable_id,
            'orderable_type' => $masterOrder->orderable_type,
            'orderable_id' => $masterOrder->orderable_id,
            'status' => MasterOrderPaymentStatus::INITIATED,
            'gateway' => 'Razorpay',
            'gateway_order_id' => $gatewayOrder->id,
            'description' => "Master Order Payment",
            'amount' => $masterOrder->by_online,
            'ip' => request()->ip(),
            'order_response' => json_encode($gatewayOrder->toArray())
        ]);
        $masterOrder->update(['gateway_order_id' => $payment->gateway_order_id]);
        return $payment;
    }

    public function processPayment(Model $payment, Model $masterOrder, $attributes = [], $failedAtUserEnd = false)
    {
        if(in_array($payment->status, MasterOrderPaymentStatus::NOT_ALLOWED_RE_PAYEMENT)){
            abort(200, 'Payment already done for this order');
        }
        try{
            if($failedAtUserEnd){
                throw new Exception('Payment failed at user end.');
            }
            $this->validatePaymentResponse($attributes);

            $gatewayOrderId = $attributes['gateway_order_id'];

            if ($payment->gateway_order_id !== $gatewayOrderId) {
                throw new Exception("Failed to validate Razorpay payment. Gateway order id mismatch. Stored: [{$payment->gateway_order_id}] Request: [{$gatewayOrderId}]");
            }

            $gatewayPaymentId = $attributes['gateway_payment_id'];

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
                'status' => MasterOrderPaymentStatus::PAYMENT_COMPLETE,
                'gateway_status' => $gatewayPayment->status,
                'gateway_payment_id' => $gatewayPayment->id,
                'gateway_fee' => $gatewayFee,
                'gateway_gst_rate' => $gatewayTaxRate,
                'gateway_gst_amount' => $gatewayTax,
                'payment_method' => strtoupper($gatewayPayment->method),
                'payment_response' => json_encode($gatewayPayment->toArray()),
                'error_message' => null
            ]);
            /** Updating the Master Order as Payment Complete */
            $masterOrder->status = MasterOrderPaymentStatus::PAYMENT_COMPLETE;
            $masterOrder->paid_at = now();
            $masterOrder->save();
        }catch (Exception $e) {
            $this->markPaymentAsFailed($payment, $masterOrder, $e->getMessage());
            throw $e;
        }
        return $payment;
    }

    public function validatePaymentResponse($attributes = [])
    {
        if (! isset($attributes['gateway_order_id'], $attributes['gateway_payment_id'], $attributes['gateway_signature'])) {
            throw new Exception('Invalid Payment Response.');
        }

        $payload = $attributes['gateway_order_id'] . '|' . $attributes['gateway_payment_id'];
        $expectedSignature = hash_hmac('sha256', $payload, $this->razorpaySecret);

        if (hash_equals($expectedSignature, $attributes['gateway_signature']) === false) {
            throw new Exception('Payment Response Signature Validation Failed.');
        }
    }

    public function markPaymentAsFailed($payment, $masterOrder, $errorMessage = null)
    {
        $payment->update([
            'status' => MasterOrderPaymentStatus::PAYMENT_FAILED,
            'gateway_status' => 'failed',
            'error_message' => $errorMessage ?? null
        ]);
        /** Updating The Master Order as Payment Failed */
        $masterOrder->status = MasterOrderPaymentStatus::PAYMENT_FAILED;
        $masterOrder->save();
    }

    public function markMasterOrderAsFulfilled($masterOrder)
    {
        $masterOrder->fulfilled_at = now();
        $masterOrder->status = MasterOrderPaymentStatus::FULFILLED;
        $masterOrder->save();
    }
}