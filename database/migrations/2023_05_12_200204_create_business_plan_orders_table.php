<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessPlanOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_plan_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->comment('Referance of Client Table')->index();
            $table->unsignedBigInteger('business_id')->comment('Referance of Business Table')->index();
            $table->bigInteger('business_plan_id')->comment('Referance of Business Plan Id')->index();
            $table->string('invoice_link')->nullable();
            $table->decimal('purchase_price', 10,2)->default(0)->comment('Amount of the plan');
            $table->decimal('by_wallet', 10,2)->default(0)->comment('Amout will be pay via Wallet');
            $table->decimal('by_online', 10,2)->default(0)->comment('Amout will be pay via Online');
            $table->decimal('tax_amount', 10,2)->default(0)->comment('Total PG Charges');
            $table->decimal('net_payable', 10,2)->default(0)->comment('Net Payable Amount');
            $table->timestamp('purchased_at')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->float('igst_rate', 5,2)->default(0);
            $table->float('cgst_rate', 5,2)->default(0);
            $table->float('sgst_rate', 5,2)->default(0);
            $table->float('igst_amount', 10,2)->default(0);
            $table->float('cgst_amount', 10,2)->default(0);
            $table->float('sgst_amount', 10,2)->default(0);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_upgrade')->default(false);
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
        Schema::dropIfExists('business_plan_orders');
    }
}
