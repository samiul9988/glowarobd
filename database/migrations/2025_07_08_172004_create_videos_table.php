<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('videos')) {
            Schema::dropIfExists('videos');
        }
        Schema::create('videos', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('video_url')->nullable();
            $table->unsignedBigInteger('attachment')->nullable();
            $table->string('type')->default('default')->nullable()->comment('Type of video (default, reel)')->index();
            $table->unsignedBigInteger('playlist_id')->nullable()->index();

            $table->boolean('status')->default(1)->index();
            $table->boolean('featured')->default(0)->index();
            $table->unsignedBigInteger('views')->default(0);

            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Create pivot table for product and video relationship
        Schema::create('product_video', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->unsignedBigInteger('video_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('videos');
        Schema::dropIfExists('product_video');
    }
}
