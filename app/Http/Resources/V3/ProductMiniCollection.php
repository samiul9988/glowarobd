<?php

namespace App\Http\Resources\V3;

use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class ProductMiniCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $user_info = Auth::guard('api')->user() ?? null;
        // $user_info = auth()->guard('api')->check() ? auth()->guard('api')->user()->load('customeringroup.group') : null;
        if (! $user_info && filled($request->header('uid', null))) {
            $user_info = User::find($request->header('uid'));
        }
        if ($user_info) {
            $user_info = $user_info->load('customeringroup.group');
        }
        $source = $request->header('source', 'app');

        // $user_info = auth()->guard('api')->check() ? auth()->guard('api')->user()->load('customeringroup.group') : null;
        return [
            'data' => $this->collection->map(function ($data) use ($user_info, $source) {
                $product_stock = $data->stocks->where('variant', $request->variant ?? '')->first();
                $flash_deal = $data->flash_deal_product?->flash_deals ?? null;
                $flash_deals_data = null;
                if ($flash_deal) {
                    $flash_deals_data = [
                        'id' => (int) $flash_deal->id,
                        'title' => $flash_deal->title,
                        'slug' => $flash_deal->slug,
                        'start_date' => $flash_deal->start_date,
                        'end_date' => $flash_deal->end_date,
                        'status' => $flash_deal->status,
                        'featured' => $flash_deal->featured,
                        'background_color' => $flash_deal->background_color,
                        'text_color' => $flash_deal->text_color,
                        'banner' => api_asset($flash_deal->banner),
                        'desktop_banner' => api_asset($flash_deal->desktop_banner),
                        'quantity' => (int) $data->flash_deal_product->quantity,
                        'is_valid' => $flash_deal->isValid(),
                    ];
                }
                $isFlashDealProduct = check_flash_deal_product($data);
                if ($isFlashDealProduct && data_get($flash_deals_data, 'quantity', 0) > 0) {
                    $isInStock = true;
                    $currentStock = data_get($flash_deals_data, 'quantity', 0);
                } else {
                    $isFlashDealProduct = false;
                    $isInStock = check_in_stock($data);
                    $currentStock = $data->stocks?->first()?->qty ?? 0;
                }
                $base_price = home_base_price($data, false);
                $discounted_base_price = home_discounted_base_price($data, false, $user_info->id ?? null);
                $priceByVariant = getMinimumPriceByVariant($data, $product_stock, $source, 1, $user_info);
                $webPrice = $source === 'web' ? $priceByVariant : getMinimumPriceByVariant($data, $product_stock, 'web', 1, $user_info);
                $hasDiscount = $base_price != $discounted_base_price || single_price($base_price) !== single_price($priceByVariant);
                $discount_type = home_discounted_type($data, $user_info->id ?? null);
                if ($hasDiscount && in_array($discount_type, ['percent', 'amount']) && $base_price > 0) {
                    $reduce = $base_price - $priceByVariant;
                    $discount = ($reduce * 100) / $base_price;
                } else {
                    $discount = 0;
                }
                return [
                    'id' => $data->id,
                    'slug' => $data->slug,
                    'name' => $data->name ?? '',
                    'sku' => $data->barcode ?? '',
                    'thumbnail_image' => api_asset($data->thumbnail_img) ?? 'assets/img/placeholder.jpg',
                    'faq_image' => is_null($data->faq_img) ? null : api_asset($data->faq_img),
                    'has_discount' => $hasDiscount,
                    'discount_type' => $discount_type,
                    'formatted_discount' => $discount > 0 ? round($discount).'% OFF' : '',
                    'min_order_amount' => (float) $data->min_order_amount,
                    'stroked_price' => single_price($base_price),
                    'main_price' => single_price($priceByVariant),
                    'web_price' => single_price($webPrice),
                    'nonformated_price' => $priceByVariant,
                    'brand' => $data->brand->slug ?? null,
                    'rating' => (float) $data->reviews?->avg('rating') ?? 0,
                    'sales' => (int) $data->num_of_sale,
                    'links' => [
                        'details' => route('products.show', $data->id),
                    ],
                    'flash_deal' => [
                        'is_flash_deal' => $isFlashDealProduct,
                        // 'data' => $data->flash_deal_product->flash_deals ?? ''
                        'data' => $flash_deals_data ?? '',
                    ],
                    'in_stock' => $isInStock,
                    'current_stock' => $currentStock,
                    'is_preorder' => check_preorder_product($data),
                    'shipping_discount' => check_shipping_discount_product([$data->id], 0),
                    'num_of_sale' => (int) $data->num_of_sale,
                    'total_reviews' => (int) $data->reviews?->count() ?? 0,
                    'is_new' => $data->is_new ?? 0,
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
                                    'description' => $item['description'] ?? '',
                                    'image' => filled($item['image']) ? api_asset($item['image']) : '',
                                ];
                            }
                        }

                        return [
                            $field->productCustomField->slug => [
                                'banner' => $field->productCustomField->banner ? api_asset($field->productCustomField->banner) : '',
                                'type' => $field->productCustomField->type,
                                'value' => $field->metaObject ? $items : json_decode($field->value, true),
                            ],
                        ];
                    })->toArray(),
                ];
            }),
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200,
            'min_price' => $request->min_price_product ?? 0,
            'max_price' => $request->max_price_product ?? 0,
        ];
    }
}
