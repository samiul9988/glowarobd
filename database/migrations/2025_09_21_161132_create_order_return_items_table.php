<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderReturnItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_return_id');
            $table->unsignedBigInteger('order_item_id');
            $table->integer('quantity');
            $table->decimal('unit_price',10,2);
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
        Schema::dropIfExists('order_return_items');
    }
}
