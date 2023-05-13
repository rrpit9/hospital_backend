<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_orders', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('userable');
            $table->nullableMorphs('orderable');
            $table->unsignedTinyInteger('status')->default(0)->index()->comment('INITIATED:0, PAYMENT_FAILED:1, PAYMENT_COMPLETE:2, FULFILLED:3, REFUND_INITIATED:4, PAYMENT_REFUNDED:5');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('purchase_price', 10,2)->default(0)->comment('Amount of the plan');
            $table->decimal('discount_percentage', 6,2)->default(0)->comment('Discount percentage applied for this order');
            $table->decimal('discount_amount', 10,2)->default(0)->comment('Discount Amount applied for this order');
            $table->decimal('net_payable', 10,2)->default(0)->comment('Net Payable Amount for this Order');
            $table->decimal('by_wallet', 10,2)->default(0)->comment('Amount Paid By Wallet');
            $table->decimal('by_online', 10,2)->default(0)->comment('Amount Paid By Online');
            $table->float('igst_rate', 5,2)->default(0);
            $table->float('igst_amount', 10,2)->default(0);
            $table->float('cgst_rate', 5,2)->default(0);
            $table->float('cgst_amount', 10,2)->default(0);
            $table->float('sgst_rate', 5,2)->default(0);
            $table->float('sgst_amount', 10,2)->default(0);
            $table->decimal('net_profit', 10, 2)->default(0)->comment('Profit on this Order');
            $table->ipAddress('ip')->nullable();
            $table->string('gateway_order_id',70)->nullable()->comment('The payment gateway/merchant order id');
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
        Schema::dropIfExists('master_orders');
    }
}
