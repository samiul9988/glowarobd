<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('product_id');
            $table->string('variant', 255)->nullable();
            $table->string('sku', 255)->nullable();
            $table->integer('qty')->default(0);
            $table->boolean('isAddition')->nullable();
            $table->boolean('isSubtraction')->nullable();
            $table->string('purpose', 255)->nullable();
            $table->bigInteger('purpose_id')->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('stock_transactions');
    }
};
