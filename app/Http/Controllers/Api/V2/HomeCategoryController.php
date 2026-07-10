<?php

namespace App\Http\Controllers\Api\V2;

//use App\Http\Resources\V2\HomeCategoryCollection;
use App\Http\Resources\V2\CategoryCollection;
use App\Models\Category;
//use App\Models\HomeCategory;

class HomeCategoryController extends Controller
{
    public function index()
    {
        // dd('HomeCategoryController@index');
        $cat=[];
        $catArray = [];
        if(get_setting('home_categories_app')!=NULL){
            $cat = json_decode(get_setting('home_categories_app'), true);
            foreach($cat as $key => $data){
                $catArray[] = $data['cid'];
            }
            // dd($catArray);
        }

        if(count($cat)>0)
            $homeCategory = Category::whereIn('id',$catArray)->get();
        else
            $homeCategory = Category::whereIn('id',[0])->get();

        return new CategoryCollection($homeCategory);
    }
}
