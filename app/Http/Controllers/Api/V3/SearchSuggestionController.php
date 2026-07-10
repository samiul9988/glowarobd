<?php

namespace App\Http\Controllers\Api\V3;

use App\Models\Shop;
use App\Models\Brand;
use App\Models\Search;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\V3\SimpleProductResource;

class SearchSuggestionController extends Controller
{
    public function getListOld(Request $request)
    {
        $query_key = $request->query_key;
        $type = $request->type;

        $search_query  = Search::select('id', 'query', 'count');
        if ($query_key != "") {
            $search_query->where('query', 'like', "%{$query_key}%");
        }
        $searches = $search_query->orderBy('count', 'desc')->limit(10)->get();

        if ($type == "product") {
            $product_query = Product::query()->with('brand', 'category', 'thumbnail_image', 'stocks', 'productprices');

            if ($query_key != "") {
                // Generate close alternative words if the length is significant for better matching
                $closeAlternatives = [];
                if (strlen($query_key) >= 4) {
                    // Generate a Levenshtein close alternative (simple approximation for illustrative purposes)
                    $closeAlternatives[] = Str::singular($query_key); // e.g., "cats" to "cat"
                    $closeAlternatives[] = Str::plural($query_key); // e.g., "cat" to "cats"
                }

                $product_query->where(function ($query) use ($query_key, $closeAlternatives) {
                    // Add base partial matching
                    $query->where('name', 'LIKE', "%{$query_key}%")
                          ->orWhere('tags', 'LIKE', "%{$query_key}%");

                    // Soundex for similar pronunciation matching
                    $query->orWhereRaw("SOUNDEX(name) = SOUNDEX(?)", [$query_key])
                          ->orWhereRaw("SOUNDEX(tags) = SOUNDEX(?)", [$query_key]);

                    // Include alternatives in the search
                    foreach ($closeAlternatives as $alt) {
                        $query->orWhere('name', 'LIKE', "%{$alt}%")
                              ->orWhere('tags', 'LIKE', "%{$alt}%")
                              ->orWhereRaw("SOUNDEX(name) = SOUNDEX(?)", [$alt])
                              ->orWhereRaw("SOUNDEX(tags) = SOUNDEX(?)", [$alt]);
                    }

                    // Related brand and category matching with alternatives
                    $query->orWhereHas('brand', function ($q) use ($query_key, $closeAlternatives) {
                        $q->where('name', 'LIKE', "%{$query_key}%")
                          ->orWhereRaw("SOUNDEX(name) = SOUNDEX(?)", [$query_key]);

                        foreach ($closeAlternatives as $alt) {
                            $q->orWhere('name', 'LIKE', "%{$alt}%")
                              ->orWhereRaw("SOUNDEX(name) = SOUNDEX(?)", [$alt]);
                        }
                    });

                    $query->orWhereHas('category', function ($q) use ($query_key, $closeAlternatives) {
                        $q->where('name', 'LIKE', "%{$query_key}%")
                          ->orWhereRaw("SOUNDEX(name) = SOUNDEX(?)", [$query_key]);

                        foreach ($closeAlternatives as $alt) {
                            $q->orWhere('name', 'LIKE', "%{$alt}%")
                              ->orWhereRaw("SOUNDEX(name) = SOUNDEX(?)", [$alt]);
                        }
                    });
                });
            }

            // Fetch filtered and limited products
            $products = $product_query->limit(15)->get();
        }

        if ($type == "brands") {
            $brand_query = Brand::query();
            if ($query_key != "") {
                $brand_query->where('name', 'like', "%$query_key%");
            }

            $brands = $brand_query->limit(3)->get();
        }

        if ($type == "sellers") {
            $shop_query = Shop::query();
            if ($query_key != "") {
                $shop_query->where('name', 'like', "%$query_key%");
            }

            $shops = $shop_query->limit(3)->get();
        }



        $items = [];

        //shop push
        if ($type == "sellers" &&  !empty($shops)) {
            foreach ($shops as  $shop) {
                $item = [];
                $item['id'] = $shop->id;
                $item['query'] = $shop->name;
                $item['count'] = 0;
                $item['type'] = "shop";
                $item['type_string'] = "Shop";

                $items[] = $item;
            }
        }

        //brand push
        if ($type == "brands" && !empty($brands)) {
            foreach ($brands as  $brand) {
                $item = [];
                $item['id'] = $brand->id;
                $item['query'] = $brand->name;
                $item['count'] = 0;
                $item['type'] = "brand";
                $item['type_string'] = "Brand";

                $items[] = $item;
            }
        }

        //product push
        if ($type == "product" &&  !empty($products)) {
            $user_info = auth()->guard('api')->check() ? auth()->guard('api')->user()->load('customeringroup.group') : null;
            foreach ($products as  $product) {
                $item = [];
                $item['id'] = $product->id;
                $item['query'] = $product->name;
                $item['count'] = 0;
                $item['type'] = "product";
                $item['type_string'] = "Product";
                $item['thumbnail_image'] = api_asset($product->thumbnail_img);
                $item['has_discount'] = home_base_price($product, false) != home_discounted_base_price($product, false, isset(Auth::guard('api')->user()->id)?Auth::guard('api')->user()->id:null);
                $item['discount_type'] = home_discounted_type($product, isset(Auth::guard('api')->user()->id)?Auth::guard('api')->user()->id:null);
                $item['stroked_price'] = home_base_price($product, true);
                $item['main_price'] = single_price(getMinimumPriceByVariant($product, $product->stocks->first(), 'app', 1, $user_info));
                $item['total_reviews'] = (integer) $product->reviews->count() ?? 0;
                $item['is_new'] = $product->is_new ?? 0;

                $items[] = $item;
            }
        }

        //search push
        if (!empty($searches)) {
            foreach ($searches as  $search) {
                $item = [];
                $item['id'] = $search->id;
                $item['query'] = $search->query;
                $item['count'] = intval($search->count);
                $item['type'] = "search";
                $item['type_string'] = "Search";

                $items[] = $item;
            }
        }

        return $items; // should return a valid json of search list;
    }

    // Using MeiliSearch
    public function getList(Request $request)
    {
        $query_key = $request->query_key;
        $type = $request->type;

        $search_query  = Search::select('id', 'query', 'count');
        if ($query_key != "") {
            $search_query->where('query', 'like', "%{$query_key}%");
        }
        $searches = $search_query->orderBy('count', 'desc')->limit(10)->get();

        if ($type == "product") {
            if (get_setting('enable_meilisearch') == 1) {
                $product_query = Product::search($query_key ?: '')->query(function ($query) {
                    $query->with('brand', 'category', 'thumbnail_image', 'stocks', 'productprices');
                });
                // Fetch filtered and limited products
                $products = $product_query->take(15)->get();
            } else {
                $product_query = Product::published()
                    ->when((filled($query_key)), function ($query) use ($query_key) {
                        $query->where(function ($query) use ($query_key) {
                            $query->where('name', 'like', '%'.$query_key.'%')->orWhere('tags', 'like', '%'.$query_key.'%');
                        });
                    });
                $products = $product_query->orderBy('id', 'asc')->limit(15)->get();
            }
        }

        if ($type == "brands") {
            if (get_setting('enable_meilisearch') == 1) {
                $brands = Brand::search($query_key ?: '')->take(3)->get();
            } else {
                $brands = Brand::where('name', 'like', "%$query_key%")->limit(3)->get();
            }
        }

        if ($type == "sellers") {
            $shop_query = Shop::query();
            if ($query_key != "") {
                $shop_query->where('name', 'like', "%$query_key%");
            }

            $shops = $shop_query->limit(3)->get();
        }



        $items = [];

        //shop push
        if ($type == "sellers" &&  !empty($shops)) {
            foreach ($shops as  $shop) {
                $item = [];
                $item['id'] = $shop->id;
                $item['query'] = $shop->name;
                $item['count'] = 0;
                $item['type'] = "shop";
                $item['type_string'] = "Shop";

                $items[] = $item;
            }
        }

        //brand push
        if ($type == "brands" && !empty($brands)) {
            foreach ($brands as  $brand) {
                $item = [];
                $item['id'] = $brand->id;
                $item['query'] = $brand->name;
                $item['count'] = 0;
                $item['type'] = "brand";
                $item['type_string'] = "Brand";

                $items[] = $item;
            }
        }

        //product push
        if ($type == "product" &&  !empty($products)) {
            $user_info = auth()->guard('api')->check() ? auth()->guard('api')->user()->load('customeringroup.group') : null;
            foreach ($products as  $product) {
                $item = [];
                $item['id'] = $product->id;
                $item['query'] = $product->name;
                $item['count'] = 0;
                $item['type'] = "product";
                $item['type_string'] = "Product";
                $item['thumbnail_image'] = api_asset($product->thumbnail_img);
                $item['has_discount'] = home_base_price($product, false) != home_discounted_base_price($product, false, isset(Auth::guard('api')->user()->id)?Auth::guard('api')->user()->id:null);
                $item['discount_type'] = home_discounted_type($product, isset(Auth::guard('api')->user()->id)?Auth::guard('api')->user()->id:null);
                $item['stroked_price'] = home_base_price($product, true);
                $item['main_price'] = single_price(getMinimumPriceByVariant($product, $product->stocks->first(), 'app', 1, $user_info));
                $item['total_reviews'] = (integer) $product->reviews->count() ?? 0;
                $item['is_new'] = $product->is_new ?? 0;

                $items[] = $item;
            }
        }

        //search push
        if (!empty($searches)) {
            foreach ($searches as  $search) {
                $item = [];
                $item['id'] = $search->id;
                $item['query'] = $search->query;
                $item['count'] = intval($search->count);
                $item['type'] = "search";
                $item['type_string'] = "Search";

                $items[] = $item;
            }
        }

        return $items; // should return a valid json of search list;
    }

    public function globalSearch(Request $request)
    {
        $limit = $request->limit ?? 8;
        $type = $request->input('type', 'all');
        $query = trim($request->input('query', ''));

        if (empty($query)) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $products = $categories = $brands = collect(); // Initialize empty collections
        if ($type == 'all' || $type == 'product') {
            if (get_setting('enable_meilisearch') == 1) {
                $products = Product::search($query)->paginate($limit);
            } else {
                $products = Product::published()->where('name', 'like', '%'.$query.'%')->paginate($limit);
            }
        }
        if ($type == 'all' || $type == 'category') {
            if (get_setting('enable_meilisearch') == 1) {
                $categories = Category::search($query)
                    ->take($limit)
                    ->get();
            } else {
                $categories = Category::where('name', 'like', '%'.$query.'%')->take($limit)->get();
            }
        }
        if ($type == 'all' || $type == 'brand') {
            if (get_setting('enable_meilisearch') == 1) {
                $brands = Brand::search($query)
                ->take($limit)
                ->get();
            } else {
                $brands = Brand::where('name', 'like', '%'.$query.'%')->take($limit)->get();
            }
        }

        $data = [];
        if ($type == 'all' || $type == 'product') {
            $data['products'] = SimpleProductResource::collection($products);
        }
        if ($type == 'all' || $type == 'category') {
            $data['categories'] = $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ];
            });
        }
        if ($type == 'all' || $type == 'brand') {
            $data['brands'] = $brands->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                ];
            });
        }

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $data,
            'links' => [
                'prev' => $products->previousPageUrl(),
                'next' => $products->nextPageUrl(),
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
            ],
        ]);
    }
}
