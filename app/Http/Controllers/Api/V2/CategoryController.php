<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CategoryCollection;
use App\Models\BusinessSetting;
use App\Models\Category;
use Cache;

class CategoryController extends Controller
{

    public function index($parent_id = 0)
    {
        if(request()->has('parent_id') && is_numeric (request()->get('parent_id'))){
          $parent_id = request()->get('parent_id');
        }
        return Cache::remember("app.categories_v2_$parent_id", 86400, function() use ($parent_id){
            return new CategoryCollection(Category::where('parent_id', $parent_id)->get());
        });
    }

    public function featured()
    {
        return Cache::remember('app.featured_categories_v2', 86400, function(){
            return new CategoryCollection(Category::where('featured', 1)->get());
        });
    }

    public function home()
    {
        return Cache::remember('app.home_categories_v2', 86400, function(){
            $cat = json_decode(get_setting('home_categories'), true);
            $catArray = [];
            foreach($cat as $key => $data){
                $catArray[] = $data['cid'];
            }
            return new CategoryCollection(Category::whereIn('id', $catArray)->get());
        });
    }

    public function top()
    {   
        return Cache::remember('app.top_categories_v2', 86400, function(){
            $cat = json_decode(get_setting('home_categories'), true);
            $catArray = [];
            foreach($cat as $key => $data){
                $catArray[] = $data['cid'];
            }
            return new CategoryCollection(Category::whereIn('id', $catArray)->limit(20)->get());
        });
    }

    //Category Details by slug or Id
    public function show($id){
        $getTheId = Category::find($id);
        if($getTheId){
            $category = Category::where('id', $id)->get();
        }else{
            $category = Category::where('slug', $id)->get();
        }
        
        return new CategoryCollection($category);
    }

    public function left_category()
    {
        $firstLevelCat = cache()->remember('firstLevelCat_v2', 86400, function () {
            return \App\Models\Category::where('level', 0)->orderBy('order_level', 'desc')->get();
        });
        $p_category = [];
        $second_array = [];
        $third_array = [];

        foreach ($firstLevelCat as $key => $category){
            $p_category[$category->id] = ['id' => $category->id, 'name' => $category->name];

            $s_category = \App\Models\Category::where('parent_id', $category->id)->orderBy('order_level', 'desc')->get();

            foreach($s_category as $s_key => $s_cat){
                $second_array[$category->id][] = ['id' => $s_cat->id, 'name' => $s_cat->name];

                $third_category = \App\Models\Category::where('parent_id', $s_cat->id)->orderBy('order_level', 'desc')->get();
                foreach($third_category as $third_value){
                    $third_array[$category->id][$s_cat->id][] = ['id' => $third_value->id, 'name' => $third_value->name];
                }
            }
        }
        return response()->json([
            'status' => true,
            'p_category' => $p_category,
            's_category' => $second_array,
            't_category' => $third_array,
        ]);
    }
}
