<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_discounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('zone_id')->nullable();
            $table->enum('type', ['all', 'product', 'brand', 'category'])->nullable()->comment('all = All products, product = selected product, brand = selected brands or category = selected categories');
            $table->json('details')->nullable();
            $table->double('s_charge', 20, 2)->default(0.00)->nullable();
            $table->double('discount', 20, 2)->default(0.00)->nullable();
            $table->double('threshold_amount', 20, 2)->default(0.00)->nullable();
            $table->bigInteger('start_date')->nullable();
            $table->bigInteger('end_date')->nullable();
            $table->string('usage_limit', 255)->nullable();
            $table->tinyInteger('status')->default(1)->nullable();
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
        Schema::dropIfExists('shipping_discounts');
    }
}
