<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTypeColumnInCategoriesContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories_content', function (Blueprint $table) {
            // Dropping the unique constraint from 'type' column
            $table->dropUnique(['type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories_content', function (Blueprint $table) {
            // Re-adding the unique constraint if you roll back the migration
            $table->unique('type');
        });
    }
}
