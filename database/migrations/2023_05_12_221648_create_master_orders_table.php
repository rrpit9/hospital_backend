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
            $table->decimal('by_wallet', 10,2)->default(0)->comment('Amount Paid By Wallet');
            $table->decimal('by_online', 10,2)->default(0)->comment('Amount Paid By Online');
            $table->decimal('net_payable', 10,2)->default(0)->comment('Net Payable Amount for this Order');
            $table->unsignedTinyInteger('status')->default(0)->index()->comment('INITIATED:0, PAYMENT_FAILED:1, PAYMENT_COMPLETE:2, FULFILLED:3, REFUND_INITIATED:4, PAYMENT_REFUNDED:5');
            $table->string('gateway_order_id',70)->nullable()->comment('The payment gateway/merchant order id');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->decimal('net_profit', 10, 2)->default(0)->comment('Profit on this Order');
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
