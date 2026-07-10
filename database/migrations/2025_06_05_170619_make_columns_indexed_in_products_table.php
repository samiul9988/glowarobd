<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeColumnsIndexedInProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['featured', 'approved', 'published']);
            $table->index(['current_stock']);
            $table->string('slug', 300)->change();
            $table->index(['slug']);
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
            $table->dropIndex(['featured', 'approved', 'published']);
            $table->dropIndex(['current_stock']);
            $table->dropIndex(['slug']);
        });
    }
}
