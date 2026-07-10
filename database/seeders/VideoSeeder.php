<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 35; $i++) {
            \App\Models\Video::create([
                'title' => $faker->realText(20) . $faker->randomElement(['@','$','&','#','!','%','*']) . $faker->realText(20),
                'description' => $faker->realText(150),
                'thumbnail' => rand(1000, 2999),
                'video_url' => 'https://example.com/video.mp4',
                'status' => rand(0, 1), // Randomly set status to 0 or 1
            ]);
        }
    }
}
