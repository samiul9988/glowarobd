<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlaylistVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('playlist_video', function (Blueprint $table) {
            $table->id();
            $table->integer('playlist_id');
            $table->unsignedBigInteger('video_id');
        });

        foreach(DB::table('videos')->get() as $video) {
            if ($video->playlist_id) {
                DB::table('playlist_video')->insert([
                    'playlist_id' => $video->playlist_id,
                    'video_id' => $video->id,
                ]);
            }
        }

        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('playlist_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('playlist_video');
    }
}
