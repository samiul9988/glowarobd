<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\CategoryCollection;
use App\Models\Category;

class SubCategoryController extends Controller
{
    public function index($id)
    {
        $getTheId = Category::find($id);
        if($getTheId){
            $category = Category::with('subcategories.childrenCategories', 'content')->where('parent_id', $id)->get();
        }else{
            $getTheIdByslug = Category::with('subcategories.childrenCategories', 'content')->where('slug', $id)->first();
            $category = !empty($getTheIdByslug) ? Category::with('subcategories.childrenCategories', 'content')->where('parent_id', $getTheIdByslug->id)->get() : [];
        }
        return new CategoryCollection($category);
    }
}
