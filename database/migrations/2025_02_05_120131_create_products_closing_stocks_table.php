<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsClosingStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_closing_stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->string('variant', 255)->nullable();
            $table->string('sku', 255)->nullable();
            $table->double('price', 20, 2)->default(0);
            $table->integer('closing_stock')->default(0);
            $table->integer('last_opening_purchase')->default(0);
            $table->integer('last_opening_sale')->default(0);
            $table->integer('last_opening_plus_adjustment')->default(0);
            $table->integer('last_opening_minus_adjustment')->default(0);
            $table->timestamp('date');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_closing_stocks');
    }
}
