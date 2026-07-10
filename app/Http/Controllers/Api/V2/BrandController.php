<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Utility\SearchUtility;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\V2\BrandCollection;

class BrandController extends Controller
{
    // Using MeiliSearch
    public function index(Request $request)
    {
        if (filled($request->name)) {
            SearchUtility::store($request->name);
        }

        $limit = $request->limit ?? 10;
        if (get_setting('enable_meilisearch') == 1) {
            $brands = Brand::search($request->name ?: '')->query(function ($query) {
                $query->with('logo');
            })->paginate($limit);
        } else {
            $brands = Brand::with('logo')->where('name', 'like', '%'.$request->name.'%')->paginate($limit);
        }

        return new BrandCollection($brands);
    }

    public function top()
    {
        return Cache::remember('app.top_brands_v2', 86400, function(){
            return new BrandCollection(Brand::where('top', 1)->get());
        });
    }
}
