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
        Schema::create('gift_offers', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 255)->nullable();
            $table->text('description')->nullable();

            $table->string('offer_type')->default('cart'); // e.g., 'cart', 'product'

            $table->decimal('min_cart_amount', 20, 2)->nullable()->default(0);

            $table->integer('max_item_per_order')->default(1);
            $table->integer('max_qty_per_order')->default(1);

            // Date range (Unix timestamps to match existing pattern)
            $table->integer('start_date')->nullable();
            $table->integer('end_date')->nullable();

            // Status
            $table->tinyInteger('status')->default(1);  // 1=active, 0=inactive

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('offer_type');
            $table->index(['status', 'start_date', 'end_date']);
            $table->index('min_cart_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gift_offers');
    }
};
