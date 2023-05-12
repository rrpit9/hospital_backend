<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('type', 30);
            $table->float('validity', 5,2)->comment('Validity in Year');
            $table->string('description')->nullable();
            $table->float('price', 8,2)->default(0);
            $table->float('discount_percentage',5,2)->default(0);
            $table->boolean('is_displayable')->default(true);
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->bigInteger('active_plan_id')->after('valid_till')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_plans');

        Schema::table('business_plans', function (Blueprint $table) {
            $table->dropColumn(['active_plan_id']);
        });
    }
}
