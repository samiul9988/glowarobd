<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DefaultPosProductCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $base_prices = home_base_prices_by_stock_ids($this->collection->pluck('stock_id')->toArray());
        return [
            'data' => $this->collection->map(function($data) use ($base_prices) {
                return [
                    'id' => $data->id,
                    'stock_id' => $data->stock_id,
                    'name' => $data->name,
                    'thumbnail_image' => ($data->stock_image == null)  ? uploaded_asset($data->thumbnail_img) : uploaded_asset($data->stock_image),
                    'price' => single_price(getMinimumPriceByVariant($data, null, 'web', 1, null)),
                    // 'price' => home_discounted_base_price_by_stock_id($data->stock_id),
                    // 'base_price' => home_base_price_by_stock_id($data->stock_id),
                    'base_price' => $base_prices[$data->stock_id] ?? 0,
                    'qty' => $data->stock_qty,
                    'variant' => $data->variant,
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
