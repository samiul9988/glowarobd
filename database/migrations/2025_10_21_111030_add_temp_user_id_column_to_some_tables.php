<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTempUserIdColumnToSomeTables extends Migration
{
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('temp_user_id')->nullable()->after('user_id')->index();
            $table->integer('user_id')->nullable()->change();
        });
        Schema::table('wishlists', function (Blueprint $table) {
            $table->string('temp_user_id')->nullable()->after('user_id')->index();
        });
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->string('temp_user_id')->nullable()->after('user_id')->index();
        });
    }

    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('temp_user_id');
            $table->integer('user_id')->nullable(false)->change();
        });
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropColumn('temp_user_id');
        });
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropColumn('temp_user_id');
        });
    }
}
