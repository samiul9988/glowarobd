<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'slug' => $data->slug,
                    'name' => $data->getTranslation('name'),
                    'photos' => explode(',', $data->photos),
                    'thumbnail_image' => api_asset($data->thumbnail_img),
                    'base_price' => (double) home_base_price($data, false),
                    'base_discounted_price' => (double) home_discounted_base_price($data, false),
                    'todays_deal' => (integer) $data->todays_deal,
                    'featured' =>(integer) $data->featured,
                    'unit' => $data->unit,
                    'discount' => (double) $data->discount,
                    'discount_type' => $data->discount_type,
                    'rating' => (double) $data->rating,
                    'sales' => (integer) $data->num_of_sale,
                    'links' => [
                        'details' => route('products.show', $data->id),
                        'reviews' => route('api.reviews.index', $data->id),
                        'related' => route('products.related', $data->id),
                        // 'top_from_seller' => route('products.topFromSeller', $data->id)
                    ],
                    'flash_deal' => [
                        'is_flash_deal' => check_flash_deal_product($data),
                        'data' => $data->flash_deal_product->flash_deals ?? ''
                    ],
                    'in_stock' => check_in_stock($data),
                    'current_stock' => $data->stocks->first()->qty,
                    'is_preorder' => check_preorder_product($data)
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
