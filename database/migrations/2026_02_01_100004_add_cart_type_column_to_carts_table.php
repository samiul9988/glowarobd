<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            if (Schema::hasColumn('carts', 'gift_offer_item_id')) {
                try{
                    $table->dropForeign('carts_gift_offer_item_id_foreign');
                } catch (\Exception $e) {
                    // Handle the exception if needed
                }
                $table->dropColumn('gift_offer_item_id');
            }
            if (Schema::hasColumn('carts', 'gift_offer_id')) {
                try{
                    $table->dropForeign('carts_gift_offer_id_foreign');
                } catch (\Exception $e) {
                    // Handle the exception if needed
                }
                $table->dropColumn('gift_offer_id');
            }
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->string('cart_type', 50)->default('regular')->after('shipping_type');
            $table->unsignedBigInteger('gift_offer_id')->nullable()->after('cart_type')->index();
            $table->unsignedBigInteger('gift_offer_item_id')->nullable()->after('gift_offer_id');

            $table->foreign('gift_offer_id')->references('id')->on('gift_offers')->onDelete('set null');
            $table->foreign('gift_offer_item_id')->references('id')->on('gift_offer_items')->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            if (Schema::hasColumn('carts', 'gift_offer_item_id')) {
                $table->dropForeign('carts_gift_offer_item_id_foreign');
            }

            if (Schema::hasColumn('carts', 'gift_offer_id')) {
                $table->dropForeign('carts_gift_offer_id_foreign');
            }

            if (Schema::hasColumn('carts', 'cart_type')) {
                $table->dropColumn('cart_type');
            }

            if (Schema::hasColumn('carts', 'gift_offer_id')) {
                $table->dropColumn('gift_offer_id');
            }

            if (Schema::hasColumn('carts', 'gift_offer_item_id')) {
                $table->dropColumn('gift_offer_item_id');
            }
        });
    }
};
