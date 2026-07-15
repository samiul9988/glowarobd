<?php

namespace App\Http\Controllers\Api\V3;

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
use App\Http\Resources\V3\ProductCollection;
use App\Http\Resources\V3\FlashDealCollection;
use App\Http\Resources\V3\ProductMiniCollection;
use App\Http\Resources\V3\ProductDetailCollection;
use App\Http\Resources\V3\DiscountedProductCollection;

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

    public function showOld(Request $request, $id)
    {
        $field = is_numeric($id) ? 'id' : 'slug';
        $product = Product::with('stocks', 'customFieldsData.productCustomField', 'customFieldsData.metaObject:id', 'customFieldsData.metaObject.items', 'flash_deal_product.flash_deals')
        ->where($field, $id)
        ->get();

        foreach ($product as $product) {
            if (check_flash_deal_product($product) && $product->flash_deal_product->quantity <= 0) {
                remove_from_flashdeal($product->flash_deal_product?->flash_deal_id ?? 0, $product->id);
                $product->refresh();
            }
        }

        return new ProductDetailCollection($product);
    }

    public function show(Request $request, $id)
    {
        $field = is_numeric($id) ? 'id' : 'slug';

        $product = Product::with([
            'stocks',
            'user',
            'user.shop',
            'customFieldsData.productCustomField',
            'customFieldsData.metaObject:id',
            'customFieldsData.metaObject.items',
            'flash_deal_product.flash_deals'
        ])->where($field, $id)->first();

        if (!$product) {
            return new ProductDetailCollection(collect());
        }

        if (
            check_flash_deal_product($product) &&
            $product->flash_deal_product?->quantity <= 0
        ) {
            $isRemoved = remove_from_flashdeal(
                $product->flash_deal_product?->flash_deal_id ?? 0,
                $product->id
            );

            if ($isRemoved) {
                $product->load('flash_deal_product.flash_deals');
            }
        }

        return new ProductDetailCollection(collect([$product]));
    }

    public function admin(Request $request)
    {
        $limit = 10;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }
        return new ProductCollection(Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews')->where('added_by', 'admin')->latest()->paginate($limit));
    }

    public function seller($id, Request $request)
    {
        $limit = 10;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }
        $shop = Shop::findOrFail($id);
        $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews')->where('added_by', 'seller')->where('user_id', $shop->user_id);
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

        $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews')->whereIn('category_id', $category_ids);

        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        $products->where('published', 1);

        $limit = 10;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }

        if(@$_GET['orderby']=='rand')
            $products = $products->orderByRaw(DB::raw('RAND()'));
        return new ProductMiniCollection($products->latest()->paginate($limit));

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

        $products = Product::published()
            ->with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews')
            ->when(!$request->boolean('show_stock_out', true), function ($query) {
                $query->availableInStock();
            })
            ->whereIn('category_id', $category_ids);

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
        }

        switch ($sort_by) {
            case 'newest':      $products->orderBy('created_at', 'desc'); break;
            case 'oldest':      $products->orderBy('created_at', 'asc'); break;
            case 'price-asc':   $products->orderBy('unit_price', 'asc'); break;
            case 'price-desc':  $products->orderBy('unit_price', 'desc'); break;
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

        $brand = Brand::where('id', $id)->orWhere('slug', $id)->first();
        if(!$brand){
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        $products = Product::published()
            ->with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews')
            ->when(!$request->boolean('show_stock_out', true), function ($query) {
                $query->availableInStock();
            })
            ->where('brand_id', $brand->id);

        if (filled($request->min_price) && filled($request->max_price)) {
            $products->whereBetween('unit_price', [$request->min_price, $request->max_price]);
        }

        if(filled($request->rating) && $request->rating > 0 && $request->rating <= 5) {
            $products->where('rating', '>=', (int)$request->rating);
        }

        switch ($sort_by) {
            case 'newest':      $products->orderBy('created_at', 'desc'); break;
            case 'oldest':      $products->orderBy('created_at', 'asc'); break;
            case 'price-asc':   $products->orderBy('unit_price', 'asc'); break;
            case 'price-desc':  $products->orderBy('unit_price', 'desc'); break;
            case 'rand':        $products->inRandomOrder(); break;
            default:            $products->orderBy('id', 'desc'); break;
        }

        $priceRange = (clone $products)->selectRaw('MIN(unit_price) as min_price, MAX(unit_price) as max_price')->first();

        $request->merge([
            'min_price_product' => $priceRange->min_price ?? 0,
            'max_price_product' => $priceRange->max_price ?? 0
        ]);

        $limit = $request->limit ?? 10;

        if($request->has('color')){
            $str = '"'.$request->color.'"';
            $products->where('colors', 'like', '%'.$str.'%');
        }

        return new ProductMiniCollection($products->latest()->paginate($limit));
    }

    public function relatedVideos($id)
    {
        $product = Product::find($id);
        if(!$product){
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }
        $videos = $product->videos;
        return response()->json([
            'status' => true,
            'data' => $videos->map(function($video){
                return [
                    'id'          => $video->id,
                    'title'       => $video->title,
                    'slug'        => $video->slug,
                    'description' => $video->description,
                    'thumbnail'   => $video->thumbnail ? api_asset($video->thumbnail) : null,
                    'video_url'   => $video->attachment ? api_asset($video->attachment) : $video->video_url,
                    'views_count' => (int) $video->views,
                    'type'        => $video->type,
                ];
            })
        ]);
    }

    public function todaysDeal()
    {
        return Cache::remember('app.todays_deal', 86400, function(){
            $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews')->where('todays_deal', 1);
            return new ProductMiniCollection($products->limit(20)->latest()->get());
        });
    }

    public function flashDeal()
    {
        return Cache::remember('app.flash_deals', 86400, function(){
            $flash_deals = FlashDeal::where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
            return new FlashDealCollection($flash_deals);
        });
    }

    public function featured()
    {
        $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews')
            ->latest()
            ->where('featured', 1);
        $products = filter_products($products)
            ->inRandomOrder()
            ->paginate(request()->limit ?? 48);
        return new ProductMiniCollection($products);
    }

    public function bestSeller(Request $request)
    {
        $limit = 20;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }

        return Cache::remember('app.best_selling_products', 86400, function() use ($limit){
            $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews')->orderBy('num_of_sale', 'desc');
            return new ProductMiniCollection($products->limit($limit)->get());
        });
    }

    // Optimized for large datasets
    public function related(int $id, Request $request)
    {
        $limit = min($request->integer('limit', 10), 50);

        $cacheKey = "app.related_products.{$id}.{$limit}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($id, $limit) {

            $product = Product::select('id', 'category_id')->find($id);

            if (!$product) {
                return new ProductMiniCollection(collect());
            }

            // Get random product IDs first (lighter query)
            $relatedIds = Product::published()
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $id)
                ->inRandomOrder()
                ->limit($limit)
                ->pluck('id');

            // Return empty collection early
            if ($relatedIds->isEmpty()) {
                return new ProductMiniCollection(collect());
            }

            // Load full product data only for selected IDs
            $products = Product::with([
                    'thumbnail_image',
                    'stocks',
                    'productprices',
                    'flash_deal_product.flash_deals',
                    'brand',
                    'reviews',
                    'taxes',
                ])
                ->whereIn('id', $relatedIds)
                ->get();

            return new ProductMiniCollection($products);
        });
    }

    public function relatedOld(int $id, Request $request)
    {
        $limit = min($request->integer('limit', 10), 50);

        $cacheKey = "app.related_products.{$id}.{$limit}";
        return Cache::remember($cacheKey, now()->addDay(), function() use ($id, $limit){
            $product = Product::select('id', 'category_id')->find($id);

            if (!$product) {
                return new ProductMiniCollection(collect());
            }

            $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews', 'taxes')->published()->where('category_id', $product->category_id)->where('id', '!=', $id);
            return new ProductMiniCollection($products->inRandomOrder()->limit($limit)->get());
        });
    }

    public function topFromSeller(int $id)
    {
        return Cache::remember("app.top_from_this_seller_products-$id", 86400, function() use ($id){
            $product = Product::find($id);
            $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews')->published()->where('user_id', $product->user_id)->orderBy('num_of_sale', 'desc');

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

        $products->with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews')->where('published', 1);

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

        $priceRange = (clone $products)->selectRaw('MIN(unit_price) as min_price, MAX(unit_price) as max_price')->first();

        $request->merge([
            'min_price_product' => $priceRange->min_price ?? 0,
            'max_price_product' => $priceRange->max_price ?? 0
        ]);

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
        return new ProductCollection(Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'category', 'reviews')->where('published', 1)->inRandomOrder()->take(50)->get());
    }

    public function listingBytag($tag, Request $request)
    {
        $limit = 10;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }
        return new ProductMiniCollection(Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews')->where('tags', 'like' , '%'.$tag.'%')->where('published', 1)->paginate($limit));
    }

    // Returns discounted products
    public function discountedProducts(Request $request)
    {
        $limit = 10;
        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }
        $resource = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews')->where('published', 1)->whereNotNull('discount_start_date')->where(function($query){
            return $query->where('discount_start_date', '<=', strtotime(date('d-m-Y H:i:s')))->where('discount_end_date', '>=', strtotime(date('d-m-Y H:i:s')));
        })->inRandomOrder()->take($limit)->get();
        return new ProductMiniCollection($resource);
    }

    public function newArrivals(Request $request)
    {
        $days = (int) get_setting('new_arrival_days', 30);
        $limit = $request->limit ?? $request->per_page ?? 10;
        $sort_by = $request->sort_by ?? $request->order_by ?? 'latest';

        $products = Product::with('thumbnail_image', 'stocks', 'flash_deal_product.flash_deals', 'brand', 'reviews')
            ->where('published', 1)
            ->where('created_at', '>=', now()->subDays($days))
            ->when(filled($request->min_price) && filled($request->max_price), function ($query) use ($request) {
                $query->whereBetween('unit_price', [$request->min_price, $request->max_price]);
            })
            ->when(filled($request->rating) && $request->rating > 0 && $request->rating <= 5, function ($query) use ($request) {
                $query->where('rating', '>=', (int)$request->rating);
            })
            ->when(!$request->boolean('show_stock_out', true), function ($query) {
                $query->availableInStock();
            })
            ->when(filled($sort_by), function ($query) use ($sort_by) {
                switch ($sort_by) {
                    case 'oldest':
                        $query->orderBy('created_at', 'asc');
                        break;
                    case 'rand':
                        $query->inRandomOrder();
                        break;
                    case 'price-asc':
                        $query->orderBy('unit_price', 'asc');
                        break;
                    case 'price-desc':
                        $query->orderBy('unit_price', 'desc');
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                        break;
                }
            });

        $priceRange = (clone $products)->selectRaw('MIN(unit_price) as min_price, MAX(unit_price) as max_price')->first();

        $request->merge([
            'min_price_product' => $priceRange->min_price ?? 0,
            'max_price_product' => $priceRange->max_price ?? 0
        ]);

        $products = new ProductMiniCollection($products->paginate($limit));

        $new_arrival_content = json_decode(get_setting('new_arrival_content'), true) ?? [];
        $additionalData = [
            'title' => data_get($new_arrival_content, 'title', 'New Arrivals'),
            'icon' => isset($new_arrival_content['icon']) ? api_asset(data_get($new_arrival_content, 'icon')) : null,
        ];
        return $products->additional($additionalData);
    }

    public function highlighted(Request $request)
    {
        $highlightedProducts = get_setting('highlighted_products');
        $products = Product::with('thumbnail_image', 'stocks', 'flash_deal_product.flash_deals', 'brand', 'reviews')
            ->where('published', 1)
            ->where('highlighted', 1)
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
        return new ProductMiniCollection($products);
    }
}
