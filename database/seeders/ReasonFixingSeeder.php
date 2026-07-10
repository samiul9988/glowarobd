<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ReasonFixingSeeder extends Seeder
{
    public function run()
    {
        \App\Models\OrderCancellation::all()->each(function($cancellation) {
            $cancellation->reason_type = \App\Enums\Reasons::key(trim($cancellation->reason));
            $cancellation->save();
        });

        \App\Models\OrderReturn::all()->each(function($return) {
            $return->reason_type = \App\Enums\Reasons::key(trim($return->reason));
            $return->save();
        });
    }
}
