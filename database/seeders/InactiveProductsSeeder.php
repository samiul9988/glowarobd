<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class InactiveProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Take 20 products in random order and make them active
        Product::inRandomOrder()->limit(20)->get()->each(function ($product) {
            $product->update([
                'published' => 1
            ]);
        });
    }
}
