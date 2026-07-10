<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Shop;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use App\Models\Category;
use App\Models\FlashDeal;
use Illuminate\Http\Request;
use App\Utility\SearchUtility;
use App\Utility\CategoryUtility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\V2\ProductCollection;
use App\Http\Resources\V2\FlashDealCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Http\Resources\V2\ProductDetailCollection;
use App\Http\Resources\V2\DiscountedProductCollection;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        //return Auth::guard('api')->user()->id;
        $limit = 10;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }
        if(@$_GET['orderby']=='rand'){
            return new ProductMiniCollection(Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'customFieldsData.productCustomField', 'customFieldsData.metaObject:id', 'customFieldsData.metaObject.items')->where('published', 1)->orderByRaw(DB::raw('RAND()'))->paginate($limit));
        }else{
            return new ProductMiniCollection(Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'customFieldsData.productCustomField', 'customFieldsData.metaObject:id', 'customFieldsData.metaObject.items')->where('published', 1)->latest()->paginate($limit));
        }

    }

    public function show(Request $request, $id)
    {
        $field = is_numeric($id) ? 'id' : 'slug';
        $product = Product::with('stocks', 'customFieldsData.productCustomField', 'customFieldsData.metaObject:id', 'customFieldsData.metaObject.items')
        ->where($field, $id)
        ->get();

        return new ProductDetailCollection($product);
    }

    public function admin(Request $request)
    {
        $limit = 10;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }
        return new ProductCollection(Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->where('added_by', 'admin')->latest()->paginate($limit));
    }

    public function seller($id, Request $request)
    {
        $limit = 10;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }
        $shop = Shop::findOrFail($id);
        $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->where('added_by', 'seller')->where('user_id', $shop->user_id);
        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        $products->where('published', 1);
        return new ProductMiniCollection($products->latest()->paginate($limit));
    }

    public function categoryOld($id, Request $request)
    {

        $getTheId = Cache::remember('uni_get_the_id_'.$id, 86400, function() use($id){
            return Category::find($id);
        });

        $category_ids = [];
        if($getTheId){
            $category_ids = CategoryUtility::children_ids($id);
            $category_ids[] = $id;
        }else{
            $getTheIdByslug = Cache::remember('uni_get_the_id_by_slug_' . $id, 86400, function () use ($id) {
                return Category::where('slug', $id)->first();
            });

            $category_ids = CategoryUtility::children_ids($getTheIdByslug->id);
            $category_ids[] = $getTheIdByslug->id;
        }

        $products = Product::latest()->with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->whereIn('category_id', $category_ids);

        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        $products->where('published', 1);

        $limit = 10;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }

        if($request->orderby=='rand') {
            $products = $products->inRandomOrder();
        }
        return new ProductMiniCollection($products->paginate($limit));

    }

    public function category($id, Request $request)
    {
        $sort_by = $request->sort_by ?? $request->orderby ?? null;
        $category = Category::where('id', $id)->orWhere('slug', $id)->first();

        $category_ids = [];
        if ($category) {
            $category_ids = CategoryUtility::children_ids($category->id);
            $category_ids[] = $category->id;
        } else {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
        // dd($category_ids);

        $products = Product::published()->with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews');

        // $products = $products->where('category_id', $category->id);
        $products = $products->whereIn('category_id', $category_ids);

        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->brand_id) {
            if(Brand::find($request->brand_id)){
                $brand_id = $request->brand_id;
            }else{
                $brand_id = Brand::where('slug', $request->brand_id)->value('id');
            }
            $products->where('brand_id',$brand_id);
        }
        // dd($brand_id);

        if (filled($request->min_price) && filled($request->max_price)) {
            $products->whereBetween('unit_price', [$request->min_price, $request->max_price]);
        }

        if(filled($request->rating) && $request->rating > 0 && $request->rating <= 5) {
            $products->where('rating', '>=', (int)$request->rating);
            // $products->whereHas('reviews', function($query) use ($request) {
            //     $query->where('rating', '>=', (int)$request->rating);
            // });
        }

        switch ($sort_by) {
            case 'newest':      $products->orderBy('created_at', 'desc'); break;
            case 'oldest':      $products->orderBy('created_at', 'asc'); break;
            case 'price-asc':   $products->orderBy('unit_price', 'asc'); break;
            case 'price-desc':  $products->orderBy('unit_price', 'desc'); break;
            // case 'rand':        $products->orderByRaw(DB::raw('RAND()')); break;
            case 'rand':        $products->inRandomOrder(); break;
            default:            $products->orderBy('id', 'desc'); break;
        }

        $priceRange = (clone $products)->selectRaw('MIN(unit_price) as min_price, MAX(unit_price) as max_price')->first();

        $request->merge([
            'min_price_product' => $priceRange->min_price ?? 0,
            'max_price_product' => $priceRange->max_price ?? 0
        ]);

        $limit = $request->limit ?? 10;

        return new ProductMiniCollection($products->latest()->paginate($limit));
    }


    public function brand($id, Request $request)
    {
        $sort_by = $request->sort_by ?? $request->orderby ?? null;
        $min_price = $request->min_price;
        $max_price = $request->max_price;


        $getTheBrand = Brand::find($id);
        if(!empty($getTheBrand)){
            $conditions = ['published' => 1];
            $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->where($conditions)->where('brand_id', $id);
        }else{
            $getTheBrandByslug = Brand::where('slug', $id)->first();
            if(empty($getTheBrandByslug)){
                $products = [];
                return new ProductMiniCollection($products);
            }else{
                $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->where('brand_id', $getTheBrandByslug->id);
            }
        }

        if($min_price != null && $max_price != null){
            $products->where('unit_price', '>=', $min_price)->where('unit_price', '<=', $max_price);
        }

        $limit = $request->limit ?? 12;

        switch ($sort_by) {
            case 'newest':
                $products->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $products->orderBy('created_at', 'asc');
                break;
            case 'price-asc':
                $products->orderBy('unit_price', 'asc');
                break;
            case 'price-desc':
                $products->orderBy('unit_price', 'desc');
                break;
            case 'rand':
                $products->orderByRaw(DB::raw('RAND()'));
                break;
            default:
                $products->orderBy('id', 'desc');
                break;
        }

        if($request->has('color')){
            $str = '"'.$request->color.'"';
            $products->where('colors', 'like', '%'.$str.'%');
            $selected_color = $request->color;
        }

        return new ProductMiniCollection($products->latest()->paginate($limit));
    }

    public function todaysDeal()
    {
        return Cache::remember('app.todays_deal_v2', 86400, function(){
            $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->where('todays_deal', 1);
            return new ProductMiniCollection($products->limit(20)->latest()->get());
        });
    }

    public function flashDeal()
    {
        return Cache::remember('app.flash_deals_v2', 86400, function(){
            $flash_deals = FlashDeal::where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
            return new FlashDealCollection($flash_deals);
        });
    }

    public function featured()
    {
        $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->where('featured', 1);
        return new ProductMiniCollection($products->orderByRaw("RAND()")->latest()->paginate(48));
    }

    public function bestSeller(Request $request)
    {
        return Cache::remember('app.best_selling_products_v2', 86400, function() use ($request){
            $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->orderBy('num_of_sale', 'desc');
            return new ProductMiniCollection($products->limit($request->limit ?? 10)->get());
        });
    }

    public function related($id, Request $request)
    {
        return Cache::remember("app.related_products-".$id, 86400, function() use ($id, $request){
            $product = Product::find($id);
            $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->where('category_id', $product?->category_id)->where('id', '!=', $id);
            return new ProductMiniCollection($products->orderByRaw("RAND()")->limit($request->limit ?? 10)->get());
        });
    }

    public function topFromSeller($id)
    {
        return Cache::remember("app.top_from_this_seller_products-$id", 86400, function() use ($id){
            $product = Product::find($id);
            $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->where('user_id', $product->user_id)->orderBy('num_of_sale', 'desc');

            return new ProductMiniCollection($products->limit(10)->get());
        });
    }

    public function search(Request $request)
    {
        if (get_setting('enable_meilisearch') == 1) {
            return $this->meilisearch($request);
        }

        $category_ids = [];
        if (!empty($request->categories)) {
            $category_ids = explode(',', $request->categories);
            // Include child categories
            $n_cid = [];
            foreach ($category_ids as $cid) {
                $n_cid = array_merge($n_cid, CategoryUtility::children_ids($cid));
            }
            if (!empty($n_cid)) {
                $category_ids = array_merge($category_ids, $n_cid);
            }
        }

        $brand_ids = [];
        if (!empty($request->brands)) {
            $brand_ids = explode(',', $request->brands);
        }

        $sort_by = $request->sort_key;
        $name = $request->name;
        $min = $request->min;
        $max = $request->max;

        $limit = 10;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }

        $products = Product::query();

        $products->with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->where('published', 1);

        if (!empty($brand_ids)) {
            $products->whereIn('brand_id', $brand_ids);
        }

        if (!empty($category_ids)) {
            $products->whereIn('category_id', $category_ids);
        }

        if (!empty($name)) {
            $products->where(function ($query) use ($name) {
                $query->where('name', 'like', '%'.$name.'%')->orWhere('tags', 'like', '%'.$name.'%');
            });
            SearchUtility::store($name);
        }

        if ($min != null && $min != "" && is_numeric($min)) {
            $products->where('unit_price', '>=', $min);
        }

        if ($max != null && $max != "" && is_numeric($max)) {
            $products->where('unit_price', '<=', $max);
        }

        $priceRange = (clone $products)->selectRaw('MIN(unit_price) as min_price, MAX(unit_price) as max_price')->first();

        $request->merge([
            'min_price_product' => $priceRange->min_price ?? 0,
            'max_price_product' => $priceRange->max_price ?? 0
        ]);

        switch ($sort_by) {
            case 'price_low_to_high':
                $products->orderBy('unit_price', 'asc');
                break;

            case 'price_high_to_low':
                $products->orderBy('unit_price', 'desc');
                break;

            case 'new_arrival':
                $products->orderBy('created_at', 'desc');
                break;

            case 'popularity':
                $products->orderBy('num_of_sale', 'desc');
                break;

            case 'top_rated':
                $products->orderBy('rating', 'desc');
                break;

            default:
                $products->orderBy('created_at', 'asc');
                break;
        }

        return new ProductMiniCollection($products->paginate($limit));
    }

    // Using meili search
    public function meilisearch(Request $request)
    {
        $name = $request->name;
        $productIds = getProductIds($request);

        $category_ids = [];
        if (!empty($request->categories)) {
            $category_ids = explode(',', $request->categories);
            // Include child categories
            $n_cid = [];
            foreach ($category_ids as $cid) {
                $n_cid = array_merge($n_cid, CategoryUtility::children_ids($cid));
            }
            if (!empty($n_cid)) {
                $category_ids = array_merge($category_ids, $n_cid);
            }
        }

        $brand_ids = [];
        if (!empty($request->brands)) {
            $brand_ids = explode(',', $request->brands);
        }

        // Start search
        $products = Product::with(['thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews'])
            ->whereIn('id', $productIds);

        if (count($brand_ids)) {
            $products->whereIn('brand_id', $brand_ids);
        }

        if (count($category_ids)) {
            $products->whereIn('category_id', $category_ids);
        }

        // Store search query
        if (!empty($name)) {
            SearchUtility::store($name);
        }

        // $priceRange = (clone $products)->selectRaw('MIN(unit_price) as min_price, MAX(unit_price) as max_price')->first();

        // $request->merge([
        //     'min_price_product' => $priceRange->min_price ?? 0,
        //     'max_price_product' => $priceRange->max_price ?? 0
        // ]);

        // Sorting
        $sort_by = $request->sort_by ?? $request->orderby ?? $request->sort_key ?? null;
        switch ($sort_by) {
            case 'price_low_to_high': $products->orderBy('unit_price', 'asc'); break;
            case 'price_high_to_low': $products->orderBy('unit_price', 'desc'); break;
            case 'new_arrival': $products->orderBy('created_at', 'desc'); break;
            case 'popularity': $products->orderBy('num_of_sale', 'desc'); break;
            case 'top_rated': $products->orderBy('rating', 'desc'); break;
            default: $products->orderByRaw('FIELD(id, ' . implode(',', $productIds) . ')'); break;
        }

        // Get results with relationships
        $products = $products->paginate($request->limit ?? 10);

        return new ProductMiniCollection($products);
    }

    public function variantPrice(Request $request)
    {
        $product = Product::with('stocks', 'productprices')->findOrFail($request->id);
        $str = '';
        $tax = 0;

        if ($request->has('color') && $request->color != "") {
            $str = Color::where('code', '#' . $request->color)->first()->name;
        }

        $var_str = str_replace(',', '-', $request->variants);
        $var_str = str_replace(' ', '', $var_str);

        if ($var_str != "") {
            $temp_str = $str == "" ? $var_str : '-' . $var_str;
            $str .= $temp_str;
        }

        $product_stock = $product->stocks->where('variant', $str)->first();

        if(empty($product_stock)){
            return response()->json([
                'status' => false,
                'message' => 'No variant found with provided combination'
            ]);
        }
        $price = getMinimumPriceByVariant($product, $product_stock, 'app');
        $stockQuantity = $product_stock->qty;

        //discount calculation
        /* $discount_applicable = false;
        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $price -= $product->discount;
            }
        }

        if ($product->tax_type == 'percent') {
            $price += ($price * $product->tax) / 100;
        } elseif ($product->tax_type == 'amount') {
            $price += $product->tax;
        } */

        return response()->json([
            'product_id' => $product->id,
            'variant' => $str,
            'price' => (double)convert_price($price),
            'price_string' => format_price(convert_price($price)),
            'stock' => intval($stockQuantity),
            'image' => $product_stock->image == null ? "" : api_asset($product_stock->image)
        ]);
    }

    public function home()
    {
        return new ProductCollection(Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->where('published', 1)->inRandomOrder()->take(50)->get());
    }

    public function listingBytag($tag, Request $request)
    {
        $limit = 10;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }
        return new ProductMiniCollection(Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->where('tags', 'like' , '%'.$tag.'%')->where('published', 1)->paginate($limit));
    }

    // Returns discounted products
    public function discountedProducts(Request $request)
    {
        $limit = 10;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }
        $resource = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand')->where('published', 1)->whereNotNull('discount_start_date')->where(function($query){
            return $query->where('discount_start_date', '<=', strtotime(date('d-m-Y H:i:s')))->where('discount_end_date', '>=', strtotime(date('d-m-Y H:i:s')));
        })->inRandomOrder()->take($limit)->get();
        return new ProductMiniCollection($resource);
    }

}
