<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class VideoPlaylistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 35; $i++) {
            \App\Models\VideoPlaylist::create([
                'name' => \Str::title(\Str::random(rand(5, 8)) . ' Playlist'),
                'description' => \Str::random(50),
                'thumbnail' => rand(999, 1999),
                'status' => rand(0, 1),
            ]);
        }
    }
}
