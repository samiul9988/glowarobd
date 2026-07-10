<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\FlashDealCollection;
use App\Http\Resources\V2\ProductCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Models\FlashDeal;
use App\Models\Product;

class FlashDealController extends Controller
{
    public function index()
    {
        // $flash_deals = FlashDeal::where('status', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
        $flash_deals = FlashDeal::where('status', 1)->where('app_featured', 1)->where('start_date', '<=', strtotime(date('Y-m-d H:i:s')))->where('end_date', '>=', strtotime(date('Y-m-d H:i:s')))->get();
        return new FlashDealCollection($flash_deals);
    }

    public function show($id){
        $getTheId = FlashDeal::find($id);
        if($getTheId){
            $flash_deal = FlashDeal::where('id', $id)->get();
        }else{
            $flash_deal = FlashDeal::where('slug', $id)->get();
        }

        return new FlashDealCollection($flash_deal);
    }

    public function products($id){
        $getTheId = FlashDeal::find($id);
        if($getTheId){
            $flash_deal = FlashDeal::with('flash_deal_products.product.stocks')->find($id);
        }else{
            $flash_deal = FlashDeal::with('flash_deal_products.product.stocks')->where('slug', $id)->first();
        }
        $products = collect();
        if(!empty($flash_deal)){
            foreach ($flash_deal->flash_deal_products as $key => $flash_deal_product) {
                if(Product::find($flash_deal_product->product_id) != null){
                    $products->push(Product::find($flash_deal_product->product_id));
                }
            }
        }

        return new ProductMiniCollection($products);
    }
}
