<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToShippingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_logs', function (Blueprint $table) {
            $table->text('error_response')->nullable();
            $table->text('success_response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipping_logs', function (Blueprint $table) {
            $table->dropColumn('error_response');
            $table->dropColumn('success_response');
        });
    }
}
