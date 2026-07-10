<?php

namespace App\Http\Controllers\Api\V2;


use App\Models\Shop;
use App\Models\Brand;
use App\Models\Search;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            $product_query = Product::query();
            if ($query_key != "") {
                $product_query->where(function ($query) use ($query_key) {
                    $query->where('name', 'like', '%'.$query_key.'%')->orWhere('tags', 'like', '%'.$query_key.'%');
                });
            }

            $products = $product_query->orderBy('id', 'asc')->limit(15)->get();
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
            foreach ($products as  $product) {
                $item = [];
                $item['id'] = $product->id;
                $item['query'] = $product->name;
                $item['count'] = 0;
                $item['type'] = "product";
                $item['type_string'] = "Product";

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
                $product_query = Product::query()
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
}
