<?php

namespace Database\Seeders;

use App\Models\ProductCustomField;
use Illuminate\Database\Seeder;

class GenerateSlugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProductCustomField::all()->each(function ($productCustomField) {
            $productCustomField->slug = \Str::slug($productCustomField->name, '_');
            $productCustomField->save();
        });
    }
}
