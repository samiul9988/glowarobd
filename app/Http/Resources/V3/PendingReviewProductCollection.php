<?php

namespace App\Http\Resources\V3;

use App\Models\Brand;
use Auth;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PendingReviewProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = $this->collection->map(function($data) {
            if($data->reviews->count() == 0){
                return [
                    'id' => $data->id,
                    'slug' => $data->slug,
                    'name' => $data->getTranslation('name'),
                    'thumbnail_image' => api_asset($data->thumbnail_img),
                    'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false, isset(Auth::guard('api')->user()->id)?Auth::guard('api')->user()->id:null),
                    'discount_type' => home_discounted_type($data, isset(Auth::guard('api')->user()->id)?Auth::guard('api')->user()->id:null) ,
                    'min_order_amount' => (double)$data->min_order_amount ,
                    'stroked_price' => home_base_price($data, true),
                    'main_price' => home_discounted_base_price($data, true, isset(Auth::guard('api')->user()->id)?Auth::guard('api')->user()->id:null),
                    'nonformated_price' => home_discounted_base_price($data, false, isset(Auth::guard('api')->user()->id)?Auth::guard('api')->user()->id:null),
                    'brand' => Brand::find($data->brand_id)->slug ?? null,
                    'rating' => (double) $data->rating,
                    'sales' => (integer) $data->num_of_sale,
                    'links' => [
                        'details' => route('products.show', $data->id),
                    ],
                    'flash_deal' => [
                        'is_flash_deal' => check_flash_deal_product($data),
                        'data' => $data->flash_deal_product->flash_deals ?? ''
                    ]
                ];
            }
        });
        return [
            'data' => $data->filter()->all()
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
