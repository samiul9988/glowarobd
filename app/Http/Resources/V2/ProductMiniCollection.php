<?php

namespace App\Http\Resources\V2;

use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductMiniCollection extends ResourceCollection
{
    public function toArray($request)
    {
        //Auth::guard('api')->user()->id
        $user_info = auth()->guard('api')->check() ? auth()->guard('api')->user()->load('customeringroup.group') : null;
        return [
            'data' => $this->collection->map(function($data) use ($user_info) {
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
                $isFlashDealProduct = check_flash_deal_product($data);
                if ($isFlashDealProduct) {
                    $isInStock = data_get($flash_deals_data, 'quantity', 0) > 0;
                    $currentStock = data_get($flash_deals_data, 'quantity', 0);
                } else {
                    $isInStock = check_in_stock($data);
                    $currentStock = $data->stocks?->first()?->qty ?? 0;
                }
                return [
                    'id' => $data->id,
                    'slug' => $data->slug,
                    'name' => $data->name ?? '',
                    'thumbnail_image' => $data->thumbnail_image->file_name ?? api_asset($data->thumbnail_img) ?? 'assets/img/placeholder.jpg',
                    'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false, isset(Auth::guard('api')->user()->id)?Auth::guard('api')->user()->id:null) ,
                    'discount_type' => home_discounted_type($data, isset(Auth::guard('api')->user()->id)?Auth::guard('api')->user()->id:null) ,
                    'min_order_amount' => (double)$data->min_order_amount ,
                    'stroked_price' => home_base_price($data, true),
                    'main_price' => single_price(getMinimumPriceByVariant($data, $data->stocks->first(), 'app', 1, $user_info)),
                    'web_price' => single_price(getMinimumPriceByVariant($data, $data->stocks->first(), 'web', 1, $user_info)),
                    'unit_price' => single_price(getMinimumPriceByVariant($data, $data->stocks->first(), 'web', 1, $user_info)),
                    'nonformated_price' => getMinimumPriceByVariant($data, $data->stocks->first(), 'app', 1, $user_info),
                    'brand' => $data->brand->slug ?? null,
                    'rating' => (double) $data->rating,
                    'sales' => (integer) $data->num_of_sale,
                    'links' => [
                        'details' => route('products.show', $data->id),
                    ],
                    'flash_deal' => [
                        'is_flash_deal' => $isFlashDealProduct,
                        // 'data' => $data->flash_deal_product->flash_deals ?? ''
                        'data' => $flash_deals_data ?? ''
                    ],
                    'in_stock' => $isInStock,
                    'current_stock' => $currentStock,
                    'is_preorder' => check_preorder_product($data),
                    'shipping_discount' => check_shipping_discount_product([$data->id], 0),
                    'custom_fields' => $data->customFieldsData?->mapWithKeys(function ($field) {
                        $metaObjectItems = [];
                        $items = [];

                        if ($field->metaObject) {
                            $metaObjectItems = $field->metaObject->items
                                ->whereIn('id', json_decode($field->value, true))
                                ->values()
                                ->toArray();
                        }

                        if (count($metaObjectItems) > 0) {
                            foreach ($metaObjectItems as $item) {
                                $items[] = [
                                    'title' => $item['title'] ?? '',
                                    'image' => filled($item['image']) ? api_asset($item['image']) : "",
                                ];
                            }
                        }

                        return [
                            $field->productCustomField->slug => [
                                'type'  => $field->productCustomField->type,
                                'value' => $field->metaObject ? $items : json_decode($field->value, true),
                            ]
                        ];
                    })->toArray()
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
