<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class MetaObjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('meta_object_items')->truncate();
        DB::table('meta_objects')->truncate();
        DB::table('product_custom_fields')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $metaObjects = [
            ['name' => 'Key Ingredient'],
            ['name' => 'Other Ingredients'],
            ['name' => 'Highlight'],
            ['name' => 'Skin Type'],
            ['name' => 'Skin Concern'],
            ['name' => 'Caution'],
            ['name' => 'Uses'],
            ['name' => 'Duration of Use'],
            ['name' => 'Result Time'],
            ['name' => 'Faqs'],
        ];
        DB::table('meta_objects')->insert($metaObjects);
        

        // Meta Object Items
        DB::table('meta_object_items')->insert([
            ['meta_object_id' => 1, 'title' => 'Niacynamide'],
            ['meta_object_id' => 1, 'title' => 'Azelic Acid'],
            ['meta_object_id' => 1, 'title' => 'Salicylic Acid'],
            ['meta_object_id' => 3, 'title' => 'Skin Brightening'],
            ['meta_object_id' => 3, 'title' => 'Pore Minimize'],
            ['meta_object_id' => 3, 'title' => 'Reduce acne'],
            ['meta_object_id' => 3, 'title' => 'Solve Uneven Skintone'],
            ['meta_object_id' => 4, 'title' => 'Oily'],
            ['meta_object_id' => 4, 'title' => 'Combination'],
            ['meta_object_id' => 4, 'title' => 'Sensitive'],
            ['meta_object_id' => 4, 'title' => 'Dry'],
            ['meta_object_id' => 5, 'title' => 'Acne'],
            ['meta_object_id' => 5, 'title' => 'Dark Spot'],
            ['meta_object_id' => 5, 'title' => 'Uneven Skintone'],
            ['meta_object_id' => 5, 'title' => 'Melasma'],
            ['meta_object_id' => 5, 'title' => 'Open Pores'],
            ['meta_object_id' => 5, 'title' => 'White Heads'],
            ['meta_object_id' => 5, 'title' => 'Black Heads'],
            ['meta_object_id' => 5, 'title' => 'Wrinkle'],
            ['meta_object_id' => 6, 'title' => 'Pregnancy Safe'],
            ['meta_object_id' => 6, 'title' => 'Irritation Safe'],
            ['meta_object_id' => 6, 'title' => 'Acne Prone Safe'],
            ['meta_object_id' => 10, 'title' => 'Are the products genuine?'],
            ['meta_object_id' => 10, 'title' => 'Do your products come with a warranty?'],
            ['meta_object_id' => 10, 'title' => 'Can I request a custom or personalized product?'],
        ]);

        // Custom Fields
        DB::table('product_custom_fields')->insert([
            ['name' => 'Key Ingredient', 'type' => 'multi_select'],
            ['name' => 'Other Ingredients', 'type' => 'multi_text_box'],
            ['name' => 'Highlight', 'type' => 'multi_select'],
            ['name' => 'Skin Type', 'type' => 'single_select'],
            ['name' => 'Skin Concern', 'type' => 'multi_select'],
            ['name' => 'Caution', 'type' => 'multi_select'],
            ['name' => 'Uses', 'type' => 'multi_text_box'],
            ['name' => 'Duration of Use', 'type' => 'html_box'],
            ['name' => 'Result Time', 'type' => 'html_box'],
            ['name' => 'Faqs', 'type' => 'multi_select'],
        ]);
    }
}
