<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\FlashDealCollection;
use App\Http\Resources\V3\ProductMiniCollection;
use App\Models\FlashDeal;
use App\Models\Product;
use Illuminate\Http\Request;

class FlashDealController extends Controller
{
    public function index()
    {
        $flash_deals = FlashDeal::active()
            ->where(function ($query) {
                $query->onlyValid()
                    ->orWhere(function ($query) {
                        $query->upcoming();
                    });
            })
            ->get();

        return new FlashDealCollection($flash_deals);
    }

    public function show($id)
    {
        $flash_deal = FlashDeal::where('id', $id)->get();
        if ($flash_deal->isEmpty()) {
            $flash_deal = FlashDeal::where('slug', $id)->get();
        }

        return new FlashDealCollection($flash_deal);
    }

    public function productsOld($id)
    {
        $getTheId = FlashDeal::find($id);
        if ($getTheId) {
            $flash_deal = FlashDeal::with('flash_deal_products.product.stocks')->find($id);
        } else {
            $flash_deal = FlashDeal::with('flash_deal_products.product.stocks')->where('slug', $id)->first();
        }
        $products = collect();
        if (! empty($flash_deal)) {
            foreach ($flash_deal->flash_deal_products as $key => $flash_deal_product) {
                if (Product::find($flash_deal_product->product_id) != null) {
                    $products->push(Product::find($flash_deal_product->product_id));
                }
            }
        }

        return new ProductMiniCollection($products);
    }

    public function products(Request $request, $id)
    {
        $flash_deal = FlashDeal::with('flash_deal_products.product.stocks')->find($id);
        if (! $flash_deal) {
            $flash_deal = FlashDeal::with('flash_deal_products.product.stocks')->where('slug', $id)->first();
        }

        $productIds = [];
        if ($flash_deal && $flash_deal->flash_deal_products) {
            $productIds = $flash_deal->flash_deal_products->pluck('product_id')->toArray();
        }

        if (! $flash_deal || empty($productIds)) {
            return new ProductMiniCollection(collect());
        }

        $limit = $request->limit ?? 15;
        $orderBy = $request->order_by ?? 'latest';
        $products = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals', 'brand', 'reviews')
            ->whereIn('id', $productIds)
            ->published();

        switch ($orderBy) {
            case 'latest':      $products->orderBy('created_at', 'desc');
                break;
            case 'oldest':      $products->orderBy('created_at', 'asc');
                break;
            case 'rand':        $products->inRandomOrder();
                break;
            default:            $products->orderBy('created_at', 'desc');
                break;
        }

        // $priceRange = (clone $products)->selectRaw('MIN(unit_price) as min_price, MAX(unit_price) as max_price')->first();

        // $request->merge([
        //     'min_price_product' => $priceRange->min_price ?? 0,
        //     'max_price_product' => $priceRange->max_price ?? 0
        // ]);
        return new ProductMiniCollection($products->paginate($limit));
    }
}
