<?php

namespace App\Http\Controllers\Api\Merchant\V1;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Merchant\ProductMiniCollection;
use App\Http\Resources\Merchant\ProductStockCollection;

class ProductController extends Controller
{
    public function getProducts(Request $request)
    {
        // dd($request->all());
        try{
            $request->merge([
                'category_ids' => array_filter($request->input('category_ids', [])),
                'product_ids' => array_filter($request->input('product_ids', []))
            ]);
            $request->validate([
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'nullable|integer',
                'product_ids' => 'nullable|array',
                'product_ids.*' => 'nullable|integer',
            ], [
                'category_ids.*.integer' => 'Category ID must be an integer',
                'product_ids.*.integer' => 'Product ID must be an integer',
            ]);

            // Get the page number from the request, default to 1 if not provided
            $page = max(1, intval($request->input('page', 1)));

            // Define the number of items per page
            // $perPage = min(100, max(1, intval($request->input('per_page', 10))));
            $perPage = min(500, intval($request->input('per_page', 1000))); // Limit the number of items per page to 1000
            $perPage = max(1, $perPage); // Ensure the number of items per page is at least 1


            $category_ids = $request->input('category_ids', []);
            $product_ids = $request->input('product_ids', []);

            if(app()->environment('production') && 'test' === $request->header('merchant_type')){
                $request->merge([
                    'page' => 1,
                    'per_page' => 10
                ]);
            }

            $products = Product::with('customFieldsData.productCustomField', 'customFieldsData.metaObject:id', 'customFieldsData.metaObject.items')->published();

            if (!empty($category_ids)) {
                $products = $products->whereIn('category_id', $category_ids);
                // dd($products->toSql(), $products->getBindings());
            }

            if (!empty($product_ids)) {
                $products = $products->whereIn('id', $product_ids);
            }

            $products = $products->orderBy('name')->paginate($perPage);

            // dd($products);

            return new ProductMiniCollection($products);

            // Return the paginated response
            // return ResponseHelper::success('Products fetched successfully', 200, $products);
        } catch(ValidationException $e){
            $errors = Arr::flatten(array_values($e->errors()));
            return ResponseHelper::error('Invalid data', 400, $errors);
        } catch(\Exception $e){
            return ResponseHelper::error('Server Error', 500);
        }
    }

    public function getProductsStock(Request $request)
    {
        try{
            $request->merge([
                'category_slugs' => array_filter($request->input('category_slugs', [])),
                'product_ids' => array_filter($request->input('product_ids', []))
            ]);
            $request->validate([
                'category_slugs' => 'nullable|array',
                'product_ids' => 'nullable|array',
                'product_ids.*' => 'nullable|integer',
            ], [
                'product_ids.*.integer' => 'Product ID must be an integer',
            ]);

            // Get the page number from the request, default to 1 if not provided
            $page = max(1, intval($request->input('page', 1)));

            // Define the number of items per page
            $perPage = min(100, intval($request->input('per_page', 100))); // Limit the number of items per page to 100
            $perPage = max(1, $perPage); // Ensure the number of items per page is at least 1

            $category_slugs = $request->input('category_slugs', []);
            $product_ids = $request->input('product_ids', []);

            if(app()->environment('production') && 'test' === $request->header('merchant_type')){
                $request->merge([
                    'page' => 1,
                    'per_page' => 10
                ]);
            }

            $products = Product::with('category')->published();


            if (!empty($category_slugs)) {
                $products = $products->whereHas('category', function($query) use ($category_slugs) {
                    $query->whereIn('slug', $category_slugs);
                });
            }

            if (!empty($product_ids)) {
                $products = $products->whereIn('id', $product_ids);
            }

            $products = $products->orderBy('id')->paginate($perPage);

            return new ProductStockCollection($products);
        } catch(ValidationException $e){
            $errors = Arr::flatten(array_values($e->errors()));
            return ResponseHelper::error('Invalid data', 400, $errors);
        } catch(\Exception $e){
            return ResponseHelper::error('Server Error', 500, $e->getMessage());
        }
    }
}
