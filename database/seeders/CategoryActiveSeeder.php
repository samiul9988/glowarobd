<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategoryActiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Category::all()->each(function ($category) {
            $category->status = 1;
            $category->save();
        });
    }
}
