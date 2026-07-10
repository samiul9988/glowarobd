<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("reviews", function (Blueprint $table) {
            $table->string("product_id")->nullable()->change();
            $table->string("review_type")->nullable()->default("default")->after("viewed")->comment("Type of review: text, image, video, default");
            $table->json("videos")->nullable()->after("photos");
            $table->unsignedInteger("created_by")->nullable()->after("videos");
            $table->unsignedInteger("updated_by")->nullable()->after("created_by");

            $table->index(['product_id', 'review_type'], 'reviews_product_id_review_type_index');
            $table->index(['created_by', 'updated_by'], 'reviews_created_updated_by_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("reviews", function (Blueprint $table) {
            $table->dropIndex('reviews_product_id_review_type_index');
            $table->dropIndex('reviews_created_updated_by_index');

            $table->unsignedInteger("product_id")->nullable(false)->change();
            $table->dropColumn("review_type");
            $table->dropColumn("videos");
            $table->dropColumn("created_by");
            $table->dropColumn("updated_by");
        });
    }
}
