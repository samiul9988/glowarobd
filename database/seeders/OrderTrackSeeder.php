<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OrderTrackSeeder extends Seeder
{
    public function run()
    {
        $range = rand(10, 20);
        for ($i = 1; $i <= $range; $i++) {
            $sources = ['app', 'website', 'google', 'facebook', 'youtube', 'instagram', 'affiliate'];
            $mediums = ['cpc', 'organic', 'referral', 'email', 'social'];
            $source = $sources[array_rand($sources)];
            try{
                \App\Models\OrderTrack::create([
                    'order_id' => \App\Models\Order::inRandomOrder()->first()->id,
                    'utm_source' => $source,
                    'utm_medium' => $mediums[array_rand($mediums)],
                    'ref_id' => $source === 'affiliate' ? 'AFF'.rand(1000, 9999) : null,
                    'utm_campaign' => 'Campaign '.rand(1,5),
                ]);
            }catch (\Exception $e){

            }
        }
    }
}
