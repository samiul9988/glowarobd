<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PosProductCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            // $base_prices = home_base_prices_by_stock_ids($this->collection->pluck('stock_id')->toArray()),
            'data' => $this->collection->map(function($data) {
                $stock = $data->stocks->first();
                return [
                    'id' => $data->id,
                    'name' => $data->name,
                    'barcode' => $data->barcode,
                    'stock_id' => $stock->id,
                    'thumbnail_image' => uploaded_asset($data->thumbnail_img) ?? static_asset('assets/img/placeholder.jpg'),
                    'price' => single_price(getMinimumPriceByVariant($data, $stock, 'web', 1, null)),
                    // 'price' => home_discounted_base_price_by_stock_id($data->stock_id),
                    'base_price' => home_base_price_by_stock_id($stock->id),
                    // 'base_price' => $base_prices[$data->stock_id] ?? 0,
                    'qty' => $stock->qty ?? 0,
                    'variant' => $data->variant ?? '',
                    // 'data' => $data,
                    'url' => \Illuminate\Support\Facades\Route::has('product') ? to_frontend(route('product', $data->slug)) : url('/product/'.$data->slug),
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
