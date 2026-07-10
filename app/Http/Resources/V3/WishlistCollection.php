<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WishlistCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $user_info = auth()->guard('api')->check() ? auth()->guard('api')->user()->load('customeringroup.group') : null;

        return [
            'data' => $this->collection->map(function($data) use ($user_info) {
                return [
                    'id' => (integer) $data->id,
                    'product' => [
                        'id' => $data->product->id,
                        'name' => $data->product->name,
                        'slug' => $data->product->slug,
                        'thumbnail_image' => api_asset($data->product->thumbnail_img),
                        'base_price' => format_price(home_base_price($data->product, false)),
                        'main_price' => single_price(getMinimumPriceByVariant($data->product, $data->product->stocks?->first(), 'app', 1, $user_info)),
                        'rating' => (double) $data->product->rating,
                    ]
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
