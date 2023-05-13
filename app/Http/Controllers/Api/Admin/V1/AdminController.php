<?php

namespace App\Http\Controllers\Api\Admin\V1;

use DB;
use Exception;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\Business;
use App\Models\BusinessPlan;
use App\Models\BusinessPlanOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\ValidateException;
use App\Services\Handler\CartHandler;
use App\Http\Resources\V1\BusinessPlanResource;

class AdminController extends Controller
{
    public function clientBusinessPlanInitiateOrder(Request $req)
    {
        $req->validate([
            'plan_id' => 'required|numeric|min:1',
            'business_id' => 'required|numeric|min:1'
        ]);

        /** Validate the plan */
        $businessPlan = BusinessPlan::where('id', $req->plan_id)->first();
        if(!$businessPlan){
            throw new ValidateException(['plan_id' => 'the selected plan is Invalid']);
        }
        /** Validate the Business */
        $business = Business::where('id', $req->business_id)->first();
        if(!$business){
            throw new ValidateException(['business_id' => 'the selected business is Invalid']);
        }
        /** Validate the Client */
        $client = Client::where('id', $business->client_id)->first();
        if(!$client){
            throw new ValidateException(['business_id' => 'the client for this business is not available']);
        }
        $orderHandler = orderHandler();
        DB::beginTransaction();
        try{
            $planPrice = round($businessPlan->price - ($businessPlan->price * $businessPlan->discount_percentage / 100), 2);
            $cart = new CartHandler($planPrice);

            /** Creating the Order for Purchasing the plan */
            $order = new BusinessPlanOrder();
                $order->client_id = $business->client_id;
                $order->business_id = $business->id;
                $order->business_plan_id = $businessPlan->id;
                $order->purchase_price = $cart->purchaseAmount;
                $order->igst_rate = $cart->igstRate;
                $order->igst_amount = $cart->igstAmount;
                $order->cgst_rate = $cart->cgstRate;
                $order->cgst_amount = $cart->cgstAmount;
                $order->sgst_rate = $cart->sgstRate;
                $order->sgst_amount = $cart->sgstAmount;
                $order->net_payable = $cart->netPayable;
                $order->by_wallet = $cart->consumableWallet;
                $order->by_online = $cart->onlinePayable;
            $order->save();

            /** Creating the Master Order */
            $masterOrder = $orderHandler->createMasterOrder($order, $client);
            /** If Wallet Involved in the Order */
            if ($masterOrder->by_wallet > 0){}

            /** If Online Payment Involved in the Order */
            if ($masterOrder->by_online > 0){
                $payment = $orderHandler->createPayment($masterOrder, $client);
            }
            DB::commit();
        }catch (Exception $e) {
            DB::rollBack();
            abort(422, $e->getMessage());
        }

        return $this->respondOk([
            'order_id' => $order->id,
            'net_payable' => $masterOrder->net_payable,
            'by_wallet' => $masterOrder->by_wallet,
            'by_online' => $masterOrder->by_online,
            'proceed_to_gateway' => ($masterOrder->by_online > 0),
            'gateway_order_id' => ($masterOrder->gateway_order_id ?? null)
        ]);
    }

    public function clientBusinessPlanFulfilOrder(Request $req, $orderId)
    {
        $req->validate([
            'gateway_order_id' => 'nullable|string',
            'gateway_payment_id' => 'nullable|string',
            'gateway_signature' => 'nullable|string',
            'payment_failed' => 'required|boolean'
        ]);
        $gatewayOrderId = $req->get('gateway_order_id');
        $paymentFailedAtUserEnd = (bool) $req->get('payment_failed', false);

        /** Validate The Plan Order */
        $order = BusinessPlanOrder::where('id', $orderId)->first();
        if (!$order) {
            abort(404, 'Order not found');
        }
        /** Validate The Selected Plan */
        $businessPlan = $order->businessPlan;
        if (!$businessPlan) {
            abort(404, 'Plan not found');
        }
        /** Validate The Business for the Selected Plan */
        $business = $order->business;
        if (!$business) {
            abort(404, 'Business not found');
        }
        /* To handle webhook's faster processed requests .*/
        if ($order->status == BusinessPlanOrder::ACTIVE && $order->paid_at) {
            return $this->respondOk(new BusinessPlanResource($businessPlan));
        }
        $masterOrder = $order->masterOrder;
        $orderHandler = orderHandler();
        try {
            /** if wallet involved in the payment */
            if($masterOrder->by_wallet){}

            /** if online involved in the payment */
            if($masterOrder->by_online){
                $payment = $order->masterPayment;
                $orderHandler->processPayment($payment, $masterOrder ,$req->all(), $paymentFailedAtUserEnd);
            }
        }catch (Exception $e) {
            throw $e;
        }
        $orderHandler->markMasterOrderAsFulfilled($masterOrder);
        $order->update(['paid_at' => now(),'status' => BusinessPlanOrder::ACTIVE]);
        /** Extend the same Validity for the Business */
        $newValidity = $this->extendValidityForPlan($businessPlan->validity, $business->valid_till);
        $business->update(['active_plan_id' => $businessPlan->id, 'valid_till' => $newValidity]);

        return $this->respondOk(new BusinessPlanResource($businessPlan));
    }

    public function extendValidityForPlan($planValidity, $currentValidity = null)
    {
        $today = Carbon::now();
        $isGteToday = $today->greaterThanOrEqualTo(Carbon::parse($currentValidity));
        if($isGteToday){/** The date will be Extend from Today */
            return Carbon::now()->addDays($planValidity * 365.3);
        }
        /** The Date will be add in the Existing Date */
        return Carbon::parse($currentValidity)->addDays($planValidity * 365.3);
    }    
}
