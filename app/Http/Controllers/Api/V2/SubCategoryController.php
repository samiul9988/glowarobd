<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CategoryCollection;
use App\Models\Category;

class SubCategoryController extends Controller
{
    public function index($id)
    {
        $getTheId = Category::find($id);
        if($getTheId){
            $category = Category::where('parent_id', $id)->get();
        }else{
            $getTheIdByslug = Category::where('slug', $id)->first();
            $category = !empty($getTheIdByslug) ? Category::where('parent_id', $getTheIdByslug->id)->get() : [];
        }
        return new CategoryCollection($category);
    }
}
