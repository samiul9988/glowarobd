<?php

namespace Modules\Waitlist\Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class WaitlistDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $range = rand(10, 50);
        for ($i = 1; $i <= $range; $i++) {
            $faker = \Faker\Factory::create();
            $contactType = $faker->randomElement(['email', 'phone']);
            $notified = $faker->randomElement([true, false]);
            $createdAt = $faker->dateTimeBetween('-1 year', 'now');
            \Modules\Waitlist\Entities\Waitlist::create([
                'product_id' => Product::inRandomOrder()->first()->id,
                'contact_type' => $contactType,
                'contact' => $contactType === 'email'
                        ? $faker->safeEmail()
                        : $faker->regexify('01[3-9][0-9]{8}'),
                'notified' => $notified,
                'notified_at' => $notified ? $faker->dateTimeBetween($createdAt, 'now') : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
