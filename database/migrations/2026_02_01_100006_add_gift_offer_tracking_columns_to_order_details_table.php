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
        Schema::table('order_details', function (Blueprint $table) {
            if (Schema::hasColumn('order_details', 'gift_offer_item_id')) {
                try {
                    $table->dropForeign('order_details_gift_offer_item_id_foreign');
                } catch (\Exception $e) {
                    // Handle the exception if needed
                }
                $table->dropColumn('gift_offer_item_id');
            }
            if (Schema::hasColumn('order_details', 'gift_offer_id')) {
                try {
                    $table->dropForeign('order_details_gift_offer_id_foreign');
                } catch (\Exception $e) {
                    // Handle the exception if needed
                }
                $table->dropColumn('gift_offer_id');
            }
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->string('product_type')->default('regular')->index();
            $table->unsignedBigInteger('gift_offer_id')->nullable()->after('product_id');
            $table->unsignedBigInteger('gift_offer_item_id')->nullable()
            ->after('gift_offer_id')
            ->comment('References the gift offer item associated with this order detail, if applicable');

            $table->foreign('gift_offer_id')->references('id')->on('gift_offers')->onDelete('set null');
            $table->foreign('gift_offer_item_id')->references('id')->on('gift_offer_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            if (Schema::hasColumn('order_details', 'gift_offer_item_id')) {
                $table->dropForeign('order_details_gift_offer_item_id_foreign');
            }

            if (Schema::hasColumn('order_details', 'gift_offer_id')) {
                $table->dropForeign('order_details_gift_offer_id_foreign');
            }

            if (Schema::hasColumn('order_details', 'gift_offer_id')) {
                $table->dropColumn('gift_offer_id');
            }

            if (Schema::hasColumn('order_details', 'gift_offer_item_id')) {
                $table->dropColumn('gift_offer_item_id');
            }

            if (Schema::hasColumn('order_details', 'product_type')) {
                $table->dropColumn('product_type');
            }
        });
    }
};
