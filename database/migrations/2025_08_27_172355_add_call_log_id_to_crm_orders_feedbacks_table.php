<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCallLogIdToCrmOrdersFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_orders_feedbacks', function (Blueprint $table) {
            $table->string('call_log_id')->nullable()->after('order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_orders_feedbacks', function (Blueprint $table) {
            $table->dropColumn('call_log_id');
        });
    }
}
