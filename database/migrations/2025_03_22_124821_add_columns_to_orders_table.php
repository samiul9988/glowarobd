<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('merchant_source')->nullable()->comment('This column only use for the merchant order');
            $table->string('merchant_order_id')->nullable()->comment('This column only use for the merchant order');
            $table->json('merchant_payload')->nullable()->comment('This column only use for the merchant order');
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
            $table->dropColumn(['merchant_source', 'merchant_order_id', 'merchant_payload']);
        });
    }
}
