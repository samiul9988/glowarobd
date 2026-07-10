<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddViewCountAndLastViewedAtColumnsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();

            $table->index(['views_count', 'last_viewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['views_count', 'last_viewed_at']);
            $table->dropColumn('views_count');
            $table->dropColumn('last_viewed_at');
        });
    }
}
