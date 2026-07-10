<?php

namespace App\Http\Resources\V3;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleProductCollection extends JsonResource
{
    public function toArray($request)
    {
        $user = Auth::guard('api')->user() ?? null;
        if(!$user && filled($request->header('uid',null))) {
            $user = User::find($request->header('uid'));
        }
        if($user){
            $user = $user->load('customeringroup.group');
        }
        $source = $request->header('source', 'app');

        $base_price = (float) home_base_price($this, false);
        $base_discounted_price = (float) home_discounted_base_price($this, false, optional($user)->id);
        $product_stock = $this->stocks->where('variant', $request->variant ?? '')->first();
        $minimum_price = getMinimumPriceByVariant($this, $product_stock, $source, 1, $user);
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name ?? '',
            'thumbnail_image' => $this->thumbnail_image?->file_name ?? api_asset($this->thumbnail_img) ?? 'assets/img/placeholder.jpg',
            'has_discount' => $base_price != $base_discounted_price,
            'discount_type' => home_discounted_type($this, $user->id ?? null) ,
            'min_order_amount' => (float) $this->min_order_amount ,
            'stroked_price' => home_base_price($this, true),
            'main_price' => single_price($minimum_price),
            'web_price' => single_price(getMinimumPriceByVariant($this, $product_stock, 'web', 1, $user)),
            'nonformated_price' => $minimum_price,
            'brand' => !$this->brand ? [] : [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
                'slug' => $this->brand->slug,
            ],
            'category' => !$this->category ? [] : [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],
            'rating' => intval($this->rating),
            'sales' => intval($this->num_of_sale),
            'links' => [
                'details' => route('products.show', $this->id),
            ],
            'flash_deal' => [
                'is_flash_deal' => check_flash_deal_product($this),
                'data' => $this->flash_deal_product->flash_deals ?? ''
            ],
            'in_stock' => check_in_stock($this),
            'current_stock' => $this->stocks->first()->qty,
            'is_preorder' => check_preorder_product($this),
            'shipping_discount' => check_shipping_discount_product([$this->id], 0),
            'num_of_sale' => intval($this->num_of_sale),
            'total_reviews' => intval($this->reviews->count() ?? 0),
            'is_new' => $this->is_new ?? 0,
            'custom_fields' => $this->customFieldsData?->mapWithKeys(function ($field) {
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
                        'banner' => $field->productCustomField->banner ? api_asset($field->productCustomField->banner) : "",
                        'type'  => $field->productCustomField->type,
                        'value' => $field->metaObject ? $items : json_decode($field->value, true),
                    ]
                ];
            })->toArray()
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
