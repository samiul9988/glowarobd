<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRewardColumnsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->tinyInteger('reward_point_applied')->default(0);
            $table->double('reward_point_discount', 20, 2)->nullable();
            $table->bigInteger('applied_reward_point')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('reward_point_applied');
            $table->dropColumn('reward_point_discount');
            $table->dropColumn('applied_reward_point');
        });
    }
}
