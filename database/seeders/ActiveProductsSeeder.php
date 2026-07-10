<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ActiveProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::chunk(100, function ($products) {
            foreach ($products as $product) {
                $product->update([
                    'published' => 1
                ]);
            }
        });
    }
}
