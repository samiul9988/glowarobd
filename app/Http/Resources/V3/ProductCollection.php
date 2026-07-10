<?php

namespace App\Http\Resources\V3;

use Auth;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $flash_deal = $data->flash_deal_product?->flash_deals ?? null;
                $flash_deals_data = null;
                if ($flash_deal) {
                    $flash_deals_data = [
                        'id' => (integer)$flash_deal->id,
                        'title' => $flash_deal->title,
                        'slug' => $flash_deal->slug,
                        'start_date' => $flash_deal->start_date,
                        'end_date' => $flash_deal->end_date,
                        'status' => $flash_deal->status,
                        'featured' => $flash_deal->featured,
                        "background_color" => $flash_deal->background_color,
                        "text_color" => $flash_deal->text_color,
                        "banner" => api_asset($flash_deal->banner),
                        "desktop_banner" => api_asset($flash_deal->desktop_banner),
                        'quantity' => (integer)$data->flash_deal_product->quantity,
                    ];
                }
                return [
                    'id' => $data->id,
                    'slug' => $data->slug,
                    'name' => $data->getTranslation('name'),
                    'sku' => $data->barcode ?? "",
                    'photos' => explode(',', $data->photos),
                    'thumbnail_image' => api_asset($data->thumbnail_img),
                    'faq_image' => is_null($data->faq_img) ? null : api_asset($data->faq_img),
                    'base_price' => (double) home_base_price($data, false),
                    'base_discounted_price' => (double) home_discounted_base_price($data, false),
                    'todays_deal' => (integer) $data->todays_deal,
                    'featured' =>(integer) $data->featured,
                    'unit' => $data->unit,
                    'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false, isset(Auth::guard('api')->user()->id)?Auth::guard('api')->user()->id:null),
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
                        // 'data' => $data->flash_deal_product->flash_deals ?? ''
                        'data' => $flash_deals_data ?? ''
                    ],
                    'in_stock' => check_in_stock($data),
                    'current_stock' => $data->stocks->first()->qty,
                    'is_preorder' => check_preorder_product($data),
                    'num_of_sale' => (integer)$data->num_of_sale,
                    'total_reviews' => (integer) $data->reviews->count() ?? 0,
                    'is_new' => $data->is_new
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
