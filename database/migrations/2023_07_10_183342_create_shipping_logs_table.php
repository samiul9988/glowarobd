<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->bigInteger('shipping_method_id')->nullable();
            $table->tinyInteger('createdEntry')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_logs');
    }
}
