<?php

namespace App\Http\Controllers\Api\V3;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Utility\SearchUtility;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\V3\BrandCollection;
use App\Http\Resources\V3\ProductMiniCollection;

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

    public function show($id)
    {
        $brand = Brand::where('id', $id)->orWhere('slug', $id)->first();
        if (!$brand) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }
        return response()->json([
            'status' => 200,
            'success' => true,
            'data' => [
                'id' => $brand->id,
                'name' => $brand->name ?? '',
                'slug' => $brand->slug,
                'logo' => api_asset($brand->logo),
                'banner' => api_asset($brand->page_banner),
                'meta' => [
                    'title' => $brand->meta_title ?? $brand->name,
                    'description' => $brand->meta_description,
                    'keywords' => $brand->meta_keywords ?? '',
                ]
            ]
        ]);
    }

    public function top()
    {
        return Cache::remember('app.top_brands', 86400, function(){
            return new BrandCollection(Brand::where('top', 1)->get());
        });
    }

    public function highlight(Request $request)
    {
        $highlight_brand = json_decode(get_setting('highlight_brand'), true);

        if (!$highlight_brand || !isset($highlight_brand['id'])) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
        $brand = Brand::find($highlight_brand['id']);
        if (!$brand) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $products = Product::published()
            ->with('thumbnail_image', 'stocks', 'flash_deal_product.flash_deals', 'brand', 'reviews')
            ->where('brand_id', $brand->id)
            ->when(!$request->boolean('show_stock_out', true), function ($query) {
                $query->availableInStock();
            })
            ->when(filled($request->sort_by), function ($query) use ($request) {
                switch ($request->sort_by) {
                    case 'latest':
                        $query->orderBy('created_at', 'desc');
                        break;
                    case 'oldest':
                        $query->orderBy('created_at', 'asc');
                        break;
                    case 'rand':
                        $query->inRandomOrder();
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                        break;
                }
            })
            ->paginate($request->limit ?? 10);

        $products = new ProductMiniCollection($products);

        return $products->additional([
            'title' => $highlight_brand['title'] ?? '',
            'banner' => api_asset($highlight_brand['banner'] ?? ''),
            'icon' => api_asset($highlight_brand['icon'] ?? ''),
            'brand_name' => $brand->name,
            'brand_slug' => $brand->slug,
        ]);
    }
}
