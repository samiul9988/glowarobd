<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsTypeInMetaObjectItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meta_object_items', function (Blueprint $table) {
            $table->text('subtitle')->change();
            $table->longText('description')->change();
            $table->text('url')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meta_object_items', function (Blueprint $table) {
            $table->string('subtitle')->change();
            $table->string('description')->change();
            $table->string('url')->change();
        });
    }
}
