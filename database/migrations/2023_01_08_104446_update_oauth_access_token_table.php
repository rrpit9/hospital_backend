<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOauthAccessTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->string('device_type')->after('revoked')->nullable()->comment('ios,android,web');
            $table->string('device_token')->after('device_type')->nullable()->comment('Device Token will stored here');
            $table->ipAddress('ip_address')->after('device_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropColumn(['device_type','device_token','ip_address']);
        });
    }
}
