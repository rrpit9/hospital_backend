<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Address;

class CreateAuthenticationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** For Admin */
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('mobile',15)->nullable()->unique();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('login_pin', 10)->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('referral_code',20)->nullable()->index();
            $table->nullableMorphs('referredable');
            $table->string('image')->default('images/user.jpg');
            $table->string('gender', 15)->nullable();
            $table->date('dob')->nullable();
            $table->string('marital', 20)->nullable();
            $table->date('aniversary')->nullable();
            $table->boolean('is_registered')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });

        /** For Client */
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('mobile',15)->nullable()->unique();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('login_pin', 10)->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('referral_code',20)->nullable()->index();
            $table->nullableMorphs('referredable');
            $table->string('image')->default('images/user.jpg');
            $table->string('gender', 20)->nullable();
            $table->date('dob')->nullable();
            $table->string('marital', 20)->nullable();
            $table->date('aniversary')->nullable();
            $table->boolean('is_registered')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });

        /** For Employee */
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->comment('Referance of Business Table')->index();
            $table->unsignedBigInteger('client_id')->comment('Referance of Client Table')->index();
            $table->string('name')->nullable();
            $table->string('mobile',15)->nullable()->unique();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('login_pin', 10)->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('referral_code',20)->nullable()->index();
            $table->nullableMorphs('referredable');
            $table->string('image')->default('images/user.jpg');
            $table->string('gender', 20)->nullable();
            $table->date('dob')->nullable();
            $table->string('marital', 20)->nullable();
            $table->date('aniversary')->nullable();
            $table->boolean('is_registered')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });

        /** For Customers */
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('mobile',15)->nullable()->unique();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('login_pin', 10)->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('referral_code',20)->nullable()->index();
            $table->nullableMorphs('referredable');
            $table->string('image')->default('images/user.jpg');
            $table->string('gender', 15)->nullable();
            $table->date('dob')->nullable();
            $table->string('marital', 20)->nullable();
            $table->date('aniversary')->nullable();
            $table->boolean('is_registered')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });

        /** For Business */
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->comment('Referance of Client Table')->index();
            $table->string('name');
            $table->string('email', 100)->nullable();
            $table->string('mobile', 15)->nullable();
            $table->string('logo')->default('images/business-img.png');
            $table->text('address')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->timestamp('valid_till')->nullable();
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });

        /** For Address */
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('userable');
            $table->string('type',20)->default(Address::OFFICE);
            $table->longText('address_line_1')->nullable();
            $table->longText('address_line_2')->nullable();
            $table->longText('landmark')->nullable();
            $table->string('pincode',10)->nullable();
            $table->string('latitude',80)->nullable();
            $table->string('longitude',80)->nullable();
            $table->boolean('default')->default(false);
            $table->softDeletes();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });

        /** For Notification */
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('userable');
            $table->string('title')->nullable();
            $table->longText('message')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });

        /** For Configs */
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('key',100)->index();
            $table->string('value');
            $table->longText('description')->nullable();
            $table->bigInteger('updated_by')->comment('UserId from Users Table')->nullable();
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
        Schema::dropIfExists('admins');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('businesses');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('configs');
    }
}
