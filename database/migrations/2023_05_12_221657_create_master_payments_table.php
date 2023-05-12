<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_payments', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('userable');
            $table->nullableMorphs('orderable');
            $table->unsignedTinyInteger('status')->index()->comment('INITIATED:0, PAYMENT_FAILED:1, PAYMENT_COMPLETE:2, FULFILLED:3, REFUND_INITIATED:4, PAYMENT_REFUNDED:5');
            $table->string('gateway_status')->nullable()->comment('The payment status returned by the gateway');
            $table->string('gateway')->comment('The payment gateway name');
            $table->string('payment_method')->nullable()->comment('The payment method name');
            $table->string('gateway_order_id')->nullable()->comment('The payment gateway/merchant order id');
            $table->string('gateway_payment_id')->nullable()->comment('The payment gateway payment id');
            $table->string('description')->comment('Short description about the payable item');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('currency', 5)->default('INR');
            $table->decimal('amount', 10)->comment('The final payment amount charged');
            $table->decimal('gateway_fee', 10)->nullable()->comment('Gateway fee including all the taxes and additional charges collected by payment gateway.');
            $table->decimal('gateway_gst_rate', 4)->nullable()->comment('GST rate charged by the payment gateway');
            $table->decimal('gateway_gst_amount', 10)->nullable()->comment('GST amount charged by the payment gateway');
            $table->string('error_message')->nullable();
            $table->longText('order_response')->nullable()->comment('The full gateway order api response data');
            $table->longText('payment_response')->nullable()->comment('The full gateway payment api response data');
            $table->ipAddress('ip')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_payments');
    }
}
