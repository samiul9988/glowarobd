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
        Schema::create('gift_offer_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gift_offer_id');

            $table->string('condition_type');
            $table->unsignedBigInteger('item_id')->nullable();

            // Minimum quantity of the condition item required in cart
            $table->integer('min_qty')->default(1);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('gift_offer_id');
            $table->index('item_id');

            $table->foreign('gift_offer_id')->references('id')->on('gift_offers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gift_offer_conditions');
    }
};
