<?php

namespace App\Http\Controllers\Api\Merchant\V1;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\Merchant\ProductMiniCollection;

class CategoryController extends Controller
{
    public function getCategories(Request $request)
    {
        // Get the page number from the request, default to 1 if not provided
        $page = max(1, intval($request->input('page', 1)));

        // Define the number of items per page
        $perPage = min(100, max(1, intval($request->input('per_page', 10))));

        if(app()->environment('production') && 'test' === $request->header('merchant_type')){
            $request->merge([
                'page' => 1,
                'per_page' => 10
            ]);
        }
        // Generate a unique cache key based on the page and per_page values
        $cacheKey = 'merchant.categories.page.' . $page . '.per_page.' . $perPage;

        try{
            // Use the cache to store the paginated results
            $categories = Cache::remember($cacheKey, 86400, function () use ($perPage) {
                $paginatedCategories = Category::with(['childrens' => function ($query) {
                        $query->active()->orderBy('name');
                    }])
                    ->active()
                    ->where('parent_id', 0)
                    ->orderBy('name')
                    ->paginate($perPage);

                $transformedCategories = $paginatedCategories->getCollection()->map(function ($category) {
                    return self::transformCategory($category);
                });

                // Replace the original collection with the transformed collection
                $paginatedCategories->setCollection($transformedCategories);

                return $paginatedCategories;
            });

            // Return the paginated response
            return ResponseHelper::success('Category fetched successfully', 200, $categories);
        } catch(\Exception $e){
            return ResponseHelper::error('Server Error', 500);
        }
    }

    private static function transformCategory($category)
    {
        $childrens = $category->parent_id == 0 ? 'sub_category' : 'sub_sub_category';
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            // 'url' => route('merchant.categories.products', $category->slug),
            // 'parent_id' => $category->parent_id,
            $childrens => $category->childrens->map(function ($child) {
                return self::transformCategory($child); // Recursively transform children
            }),
        ];
    }

    public function getProductsByCategory(Request $request)
    {
        // Get the page number from the request, default to 1 if not provided
        $page = max(1, intval($request->input('page', 1)));

        // Define the number of items per page
        $perPage = min(100, intval($request->input('per_page', 10))); // Limit the number of items per page to 100
        $perPage = max(1, $perPage); // Ensure the number of items per page is at least 1

        $category = Category::active()->where('slug', $request->slug)->first();

        if(!$category) {
            return ResponseHelper::error('Category not found', 404);
        }

        // Generate a unique cache key based on the page and per_page values
        $cacheKey = 'merchant.category.'.$category->id.'.products.page.' . $page . '.per_page.' . $perPage;

        try{
            // Use the cache to store the paginated results
            $products = Cache::remember($cacheKey, 86400, function () use ($perPage, $category) {
                return Product::published()
                    ->where('category_id', $category->id)
                    ->orderBy('name')
                    ->paginate($perPage);
            });

            return new ProductMiniCollection($products);
            // Return the paginated response
            // return ResponseHelper::success('Products retrieved for category `'.ucwords($category->name).'`', 200, $products);
        } catch(\Exception $e){
            return ResponseHelper::error('Server Error', 500);
        }
    }
}
