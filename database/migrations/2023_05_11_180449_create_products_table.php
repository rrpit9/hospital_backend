<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('business_id');
            $table->bigInteger('client_id');
            $table->string('name');
            $table->string('image');
            $table->decimal('mrp',8,2)->nullable()->comment('Product Price Per Unit');
            $table->decimal('purchage_price',8,2)->nullable()->comment('Purchase Price');
            $table->decimal('discount',4,2)->nullable()->comment('Discounted Percentage');
            $table->longText('description')->nullable()->comment('Discription about Product');
            $table->date('expiry')->nullable();
            $table->nullableMorphs('updateduser');
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('products');
    }
}
