<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::updateOrCreate([
            'email' => 'merchant@gmail.com',
        ], [
            'name' => 'Merchant',
            'phone' => '01234567890',
            'password' => bcrypt('password'),
            'user_type' => 'merchant',
            'delivered_order' => 0,
        ]);

        if(config('app.theme') === 'tekka'){
            $user->update([
                'email' => 'merchant@tekka.com.bd'
            ]);
        }elseif(config('app.theme') === 'theme22'){
            $user->update([
                'email' => 'merchant@glowarobd.com'
            ]);
        }else{
            $user->update([
                'email' => 'merchant@gmail.com'
            ]);
        }

        $user->generateAppId();
    }
}
