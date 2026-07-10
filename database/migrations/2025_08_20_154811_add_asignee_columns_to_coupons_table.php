<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAsigneeColumnsToCouponsTable extends Migration
{
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_to')->nullable()->after('code');
            $table->unsignedBigInteger('assigned_by')->nullable()->after('assigned_to');
            $table->boolean('is_affiliate')->default(false)->after('assigned_by');
            $table->boolean('force_apply')->default(false)->after('is_affiliate');
        });
    }

    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('assigned_to');
            $table->dropColumn('assigned_by');
            $table->dropColumn('is_affiliate');
            $table->dropColumn('force_apply');
        });
    }
}
