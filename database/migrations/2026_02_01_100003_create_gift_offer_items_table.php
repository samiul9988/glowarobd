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
        Schema::create('gift_offer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gift_offer_id');
            $table->integer('product_id');

            // Stock allocated for this gift item
            $table->integer('available_qty')->default(0);
            $table->decimal('offer_price', 10, 2)->default(0)->comment('Discounted price for the gift product (0 = free)');
            $table->integer('used_qty')->default(0);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('gift_offer_id');
            $table->index('product_id');

            $table->unique(['gift_offer_id', 'product_id'], 'unique_offer_product');

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
        Schema::dropIfExists('gift_offer_items');
    }
};
