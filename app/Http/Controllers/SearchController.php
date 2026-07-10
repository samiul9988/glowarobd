<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Search;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\FlashDeal;
use Illuminate\Http\Request;
use App\Models\MetaObjectItem;
use App\Utility\CategoryUtility;
use App\Models\AttributeCategory;
use App\Models\ProductCustomField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\ProductsCustomFieldsData;
use Illuminate\Database\Eloquent\Builder;

class SearchController extends Controller
{
    public function indexOLD(Request $request, $category_id = null, $brand_id = null, $tag = null)
    {
        $keyword = trim($request->keyword);
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $rating = $request->rating ?? 0;

        $productIds = $this->getProductIds($request);

        $keys = array_keys($request->all()); // Get all keys from request
        $values = array_values($request->all()); // Get all values from request
        $metaObjectItems = MetaObjectItem::with('metaObject')->whereIn('title', $values)->get()->toArray(); // Get all meta object items which title matches the values
        $customFields = ProductCustomField::select('id', 'slug')
            ->whereIn('type', ['single_select', 'multi_select']) // Because there are only two types of custom fields that can be used for filtering
            ->whereIn('slug', $keys)
            ->get()->toArray(); // Get all custom fields which slug matches the keys
        $customFieldsValues = array_map(function ($customField) use ($request) {
            return $request[$customField['slug']];
        }, $customFields); // Get all values of custom fields from request
        $filteredCustomFields = array_filter($customFieldsValues, function ($value) {
            return $value !== null;
        }); // Filter out null values

        // dd($metaObjectItems,$customFields,$customFieldsValues,$filteredCustomFields);

        $sort_by = $request->sort_by;
        $seller_id = $request->seller_id;
        $selected_attribute_values = array();
        $colors = Color::all();
        $selected_color = null;

        $conditions = ['published' => 1];

        if($brand_id != null){
            $conditions = array_merge($conditions, ['brand_id' => $brand_id]);
        }elseif ($request->brand != null) {
            $brand_id = Brand::where('slug', $request->brand)->first()?->id ?? null;
            $conditions = array_merge($conditions, ['brand_id' => $brand_id]);
        }

        if($seller_id != null){
            $conditions = array_merge($conditions, ['user_id' => Seller::findOrFail($seller_id)->user->id]);
        }

        // $products = Product::with('thumbnail_image', 'stocks', 'productprices')->where($conditions); // Previous query
        $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'customFieldsData.metaObject.items')->whereIn('id', $productIds); // New query
        if(!empty($filteredCustomFields)){
            // Filter products by custom fields only if there are any custom field values in the request
            $products->whereHas('customFieldsData', function (Builder $query) use ($metaObjectItems) {
                $metaObjectIds = array_column($metaObjectItems, 'meta_object_id');
                $idsToSearch = array_column($metaObjectItems, 'id');

                $query->whereIn('meta_object_id', $metaObjectIds)
                    ->where(function ($query) use ($idsToSearch) {
                        foreach ($idsToSearch as $id) {
                            $query->orWhereJsonContains('value', $id) // Check if value exists in JSON array
                        ->orWhere('value', 'LIKE', '%'.json_encode($id).'%'); // Check if value matches a simple string
                        }
                    });
            });
        }
        $products->where($conditions);
        // dd($products->get()->toArray());

        if($category_id != null){
            $category_ids = CategoryUtility::children_ids($category_id);
            $category_ids[] = $category_id;

            $products->whereIn('category_id', $category_ids);

            $attribute_ids = AttributeCategory::whereIn('category_id', $category_ids)->pluck('attribute_id')->toArray();
            $attributes = Attribute::with('attribute_values')->whereIn('id', $attribute_ids)->get();
        }
        else {
            $attributes = Attribute::with('attribute_values')->get();
            // if ($query != null) {
            //     foreach (explode(' ', trim($query)) as $word) {
            //         $ids = Category::where('name', 'like', '%'.$word.'%')->pluck('id')->toArray();
            //         if (count($ids) > 0) {
            //             foreach ($ids as $id) {
            //                 $category_ids[] = $id;
            //                 array_merge($category_ids, CategoryUtility::children_ids($id));
            //             }
            //         }
            //     }
            //     $attribute_ids = AttributeCategory::whereIn('category_id', $category_ids)->pluck('attribute_id')->toArray();
            //     $attributes = Attribute::whereIn('id', $attribute_ids)->get();
            // }
        }

        if($min_price != null && $max_price != null){
            $products->where('unit_price', '>=', $min_price)->where('unit_price', '<=', $max_price);
        }

        if(filled($keyword)){
            $searchController = new SearchController;
            $searchController->store($request);
        }

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
            default:
                $products->orderBy('id', 'desc');
                break;
        }

        if($request->has('color')){
            $str = '"'.$request->color.'"';
            $products->where('colors', 'like', '%'.$str.'%');
            $selected_color = $request->color;
        }

        if($request->has('selected_attribute_values')){
            $selected_attribute_values = $request->selected_attribute_values;
            foreach ($selected_attribute_values as $key => $value) {
                $str = '"'.$value.'"';
                $products->where('choice_options', 'like', '%'.$str.'%');
            }
        }

        $products = $products->with('taxes')->paginate(8)->appends($request->query());
        $nextPageUrl = $products->appends($request->query())->nextPageUrl();
        // dd($products->toArray());
        $customFields = ProductCustomField::select('id', 'slug')
            ->whereIn('type', ['single_select', 'multi_select'])
            ->get();

        return view('frontend.product_listing', compact('products', 'query', 'category_id', 'brand_id', 'sort_by', 'seller_id','min_price', 'max_price', 'attributes', 'selected_attribute_values', 'colors', 'selected_color', 'tag', 'nextPageUrl', 'customFields'));
    }

    public function index(Request $request, $category_id = null, $brand_id = null, $tag = null)
    {
        if($request->ajax() || $request->wantsJson()){
            return $this->fetchProducts($request, $category_id, $brand_id, $tag);
        }
        $productsQuery = $this->buildFilteredProductsQuery($request, $category_id, $brand_id, $tag);
        $products = $productsQuery['products']->paginate(12)->appends($request->query());
        $nextPageUrl = $products->nextPageUrl();
        // dd($products, $nextPageUrl);

        $customFields = ProductCustomField::select('id', 'slug')
            ->whereIn('type', ['single_select', 'multi_select'])
            ->get();

        $attributes = $category_id
            ? Attribute::with('attribute_values')->whereIn('id',
                AttributeCategory::whereIn('category_id', array_merge(CategoryUtility::children_ids($category_id), [$category_id]))
                ->pluck('attribute_id')->toArray()
            )->get()
            : Attribute::with('attribute_values')->get();

        $colors = Color::all();

        $query = $productsQuery['query'] ?? '';
        $sort_by = $productsQuery['sort_by'] ?? '';
        $seller_id = $productsQuery['seller_id'] ?? null;
        $min_price = $productsQuery['min_price'] ?? null;
        $max_price = $productsQuery['max_price'] ?? null;
        $selected_attribute_values = $productsQuery['selected_attribute_values'] ?? [];
        $selected_color = $productsQuery['selected_color'] ?? null;
        $tag = $productsQuery['tag'] ?? null;

        // compact('products', 'category_id', 'brand_id', 'attributes', 'colors', 'nextPageUrl', 'customFields')
        return view('frontend.product_listing', compact('products', 'query', 'category_id', 'brand_id', 'sort_by', 'seller_id','min_price', 'max_price', 'attributes', 'selected_attribute_values', 'colors', 'selected_color', 'tag', 'nextPageUrl', 'customFields'));
    }

    private function buildFilteredProductsQuery(Request $request, $category_id = null, $brand_id = null, $tag = null)
    {
        $keyword = trim($request->keyword);
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $rating = $request->rating ?? 0;
        $productIds = getProductIds($request->merge(['brand_id' => $brand_id]));

        $keys = array_keys($request->all());
        $values = array_values($request->all());
        $metaObjectItems = MetaObjectItem::with('metaObject')->whereIn('title', $values)->get()->toArray();
        $customFields = ProductCustomField::select('id', 'slug')
            ->whereIn('type', ['single_select', 'multi_select'])
            ->whereIn('slug', $keys)
            ->get()->toArray();

        $customFieldsValues = array_map(function ($customField) use ($request) {
            return $request[$customField['slug']];
        }, $customFields);

        $filteredCustomFields = array_filter($customFieldsValues, fn($v) => $v !== null);

        $sort_by = $request->sort_by;
        $seller_id = $request->seller_id;
        $selected_attribute_values = $request->selected_attribute_values ?? [];
        $selected_color = $request->color;

        $conditions = ['published' => 1];

        if ($brand_id) {
            $conditions['brand_id'] = $brand_id;
        } elseif ($request->brand) {
            $brand_id = Brand::where('slug', $request->brand)->value('id');
            $conditions['brand_id'] = $brand_id;
        }

        if ($seller_id) {
            $conditions['user_id'] = Seller::findOrFail($seller_id)->user->id;
        }

        $products = Product::with('thumbnail_image', 'stocks', 'flash_deal_product', 'productprices', 'customFieldsData.metaObject.items')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if (get_setting('enable_meilisearch') == 1) {
            $products->whereIn('id', $productIds);
        } else {
            if (!empty($keyword)) {
                $products->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', '%'.$keyword.'%')
                      ->orWhere('tags', 'like', '%'.$keyword.'%');
                });
            }

            if ($tag) {
                $products->where(function ($q) use ($tag) {
                    $q->where('tags', 'like', '%'.$tag.'%');
                });
            }
        }

        if (!empty($filteredCustomFields)) {
            $products->whereHas('customFieldsData', function (Builder $query) use ($metaObjectItems) {
                $metaObjectIds = array_column($metaObjectItems, 'meta_object_id');
                $idsToSearch = array_column($metaObjectItems, 'id');

                $query->whereIn('meta_object_id', $metaObjectIds)
                    ->where(function ($query) use ($idsToSearch) {
                        foreach ($idsToSearch as $id) {
                            $query->orWhereJsonContains('value', $id)
                                ->orWhere('value', 'LIKE', '%' . json_encode($id) . '%');
                        }
                    });
            });
        }

        $products->where($conditions);

        if (get_setting('enable_meilisearch') != 1) {
            if (filled($min_price) && filled($max_price)) {
                $products->whereBetween('unit_price', [$min_price, $max_price]);
            } elseif (filled($min_price)) {
                $products->where('unit_price', '>=', $min_price);
            } elseif (filled($max_price)) {
                $products->where('unit_price', '<=', $max_price);
            }

            if($rating > 0 && $rating <= 5) {
                $products->where('rating', '>=', $rating);
            }
        }

        if ($category_id) {
            $category_ids = CategoryUtility::children_ids($category_id);
            $category_ids[] = $category_id;
            $products->whereIn('category_id', $category_ids);
        }

        if ($min_price && $max_price) {
            $products->whereBetween('unit_price', [$min_price, $max_price]);
        }

        if (filled($keyword)) {
            (new SearchController)->store($request);
        }

        switch ($sort_by) {
            case 'newest':      $products->orderBy('created_at', 'desc'); break;
            case 'oldest':      $products->orderBy('created_at', 'asc'); break;
            case 'price-asc':   $products->orderBy('unit_price', 'asc'); break;
            case 'price-desc':  $products->orderBy('unit_price', 'desc'); break;
            default:            get_setting('enable_meilisearch') == 1 ? $products->orderByRaw('FIELD(id, ' . implode(',', $productIds) . ')') : $products->orderBy('id', 'desc'); break;
        }

        if ($selected_color) {
            $products->where('colors', 'like', '%"'.$selected_color.'"%');
        }

        if (!empty($selected_attribute_values)) {
            foreach ($selected_attribute_values as $value) {
                $products->where('choice_options', 'like', '%"'.$value.'"%');
            }
        }

        // dd(count($productIds), $products->get()->count());
        return [
            'products' => filter_products($products),
            'query' => $keyword,
            'sort_by' => $sort_by,
            'seller_id' => $seller_id,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'selected_attribute_values' => $selected_attribute_values,
            'selected_color' => $selected_color,
            'tag' => $tag
        ];
    }

    public function fetchProducts(Request $request, $category_id = null, $brand_id = null, $tag = null)
    {
        $productsQuery = $this->buildFilteredProductsQuery($request, $category_id, $brand_id, $tag);
        $products = $productsQuery['products']->paginate(12)->appends($request->query());

        $view = view('frontend.product_container', compact('products'))->render();

        return response()->json([
            // 'products'=> $products->items(),
            'view' => $view,
            'current_page' => $products->currentPage(),
            'next_page_url' => $products->nextPageUrl()
        ]);
    }


    public function listing(Request $request)
    {
        return $this->index($request);
    }

    public function listingByCategory(Request $request, $category_slug)
    {
        $category = Category::where('slug', $category_slug)->firstOrFail();
        return $this->index($request, $category->id);
    }

    public function listingByCategoryBlank(Request $request, $category_slug)
    {
        $category = Category::where('slug', $category_slug)->firstOrFail();
        $request->merge(['blank' => true]);
        return $this->index($request, $category->id);
    }

    public function listingByBrand(Request $request, $brand_slug)
    {
        $brand = Brand::where('slug', $brand_slug)->firstOrFail();
        return $this->index($request, null, $brand->id);
    }

    //Suggestional Search
    public function ajax_search(Request $request)
    {
        if (get_setting('enable_meilisearch') == 1) {
            return $this->ajax_meilisearch($request);
        }
        $keywords = array();
        $query = $request->search;

        $products = (Product::query()->with('thumbnail_image', 'stocks', 'productprices'));

        $products = $products->where(function ($q) use ($query){
                            $q->where('name', 'like', '%'.$query.'%')->orWhere('tags', 'like', '%'.$query.'%');
                        })->when(!str_contains($query, ' '), function ($q) use ($query) {
                            $q->orWhereRaw("SOUNDEX(name) LIKE SOUNDEX(?)", [$query]);
                        })
                    // ->orderByRaw(\DB::raw('RAND()'))
                    ->limit(25)->get();

        $categories = Category::where('name', 'like', '%'.$query.'%')->limit(3)->get();

        if (get_setting('vendor_system_activation') == 1) {
            $shops = Shop::whereIn('user_id', verified_sellers_id())->where('name', 'like', '%'.$query.'%')->limit(3)->get();
        } else {
            $shops = collect();
        }

        if(sizeof($categories)>0 || sizeof($products)>0 || sizeof($shops) >0){
            return view('frontend.partials.search_content', compact('products', 'categories', 'shops'));
        }
        return '0';
    }

    // Suggestional Search By Meilisearch
    public function ajax_meilisearch(Request $request)
    {
        $query = $request->search;

        $products = (Product::search($query)->query(function ($query) {
            return $query->with('thumbnail_image', 'stocks', 'productprices');
        }))->take(25)->get();

        // dd($products->pluck('id')->toArray());

        $categories = Category::search($query)->take(3)->get();

        if (get_setting('vendor_system_activation') == 1) {
            $shops = Shop::whereIn('user_id', verified_sellers_id())->where('name', 'like', '%'.$query.'%')->take(3)->get();
        } else {
            $shops = [];
        }

        if(sizeof($categories)>0 || sizeof($products)>0 || sizeof($shops) >0){
            return view('frontend.partials.search_content', compact('products', 'categories', 'shops'));
        }
        return '0';
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $search = Search::where('query', $request->keyword)->first();
        if($search != null){
            $search->count = $search->count + 1;
            $search->save();
        }
        else{
            $search = new Search;
            $search->query = $request->keyword;
            $search->save();
        }
    }

    public function listingByTag(Request $request, $tag){
        if ($tag != null) {
            return $this->index($request, null, null, $tag);
        }
        abort(404);
    }


    // messaging dashboard product search
    public function messageSearchProduct(Request $request)
    {
        $query = $request->search;
        if(isset($query)){
            $products_arr = Http::get(url('/').'/api/v3/products/search?page=1&name='.$query.'&orderby=rand&limit=18')->collect();
            $products = $products_arr['data'] ?? [];
        }else{
            $products_arr = Http::get(url('/').'/api/v3/products/search?page=1&orderby=rand&limit=18')->collect();
            $products = $products_arr['data'] ?? [];
        }

        if(! empty($products)){
            return view("firebase-message.search_product", compact('products'))->render();
        }
        return false;
    }


}
