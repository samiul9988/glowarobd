<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHighlightedItemsTable extends Migration
{
    public function up()
    {
        Schema::create('highlighted_items', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('linkable_type')->nullable(); // custom, product, brand, category
            $table->unsignedBigInteger('linkable_id')->nullable()->index(); // id of product, brand, category
            $table->string('custom_link')->nullable();
            $table->string('banner_img')->nullable();
            $table->json('highlights')->nullable();
            $table->string('button_text')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('highlighted_items');
    }
}
