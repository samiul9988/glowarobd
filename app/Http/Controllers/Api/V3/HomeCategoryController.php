<?php

namespace App\Http\Controllers\Api\V3;

//use App\Http\Resources\V3\HomeCategoryCollection;
use App\Http\Resources\V3\CategoryCollection;
use App\Models\Category;
//use App\Models\HomeCategory;

class HomeCategoryController extends Controller
{
    public function index()
    {
        if(request()->header('source', 'app') === 'web'){
            return $this->indexWeb();
        }
        $cat=[];
        if(get_setting('home_categories_app')!=NULL)
            $cat = json_decode(get_setting('home_categories_app'));

        $ncat = [];
        $catData = [];
        foreach ($cat as $key => $value) {
            $ncat[$key] = intval($value->cid);
            $catData[$key] = Category::with('subcategories.childrenCategories', 'content')->find($value->cid);
            $catData[$key]['design'] = $value->did;
        }

        if(count($catData)>0)
            return new CategoryCollection($catData);
        else
            $homeCategory = Category::with('subcategories.childrenCategories', 'content')->whereIn('id',$ncat)->get();

        return new CategoryCollection($homeCategory);
    }

    public function indexWeb()
    {
        $cat=[];
        $catArray = [];
        if(get_setting('home_categories')!=NULL){
            $cat = json_decode(get_setting('home_categories'), true);
            foreach($cat as $key => $data){
                $catArray[] = $data['cid'];
            }
        }

        if(count($cat)>0)
            $homeCategory = Category::whereIn('id',$catArray)->get();
        else
            $homeCategory = Category::whereIn('id',[0])->get();

        return new CategoryCollection($homeCategory);
    }
}
