<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\ResourceCollection;

class GiftOfferCollection extends ResourceCollection
{
    public function toArray($request = null)
    {
        return [
            'data' => $this->collection->map(function ($offer) {
                return [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'slug' => $offer->slug,
                    'description' => $offer->description,
                    'offer_type' => $offer->offer_type,
                    'min_cart_amount' => (float) $offer->min_cart_amount,
                    'formatted_min_cart_amount' => format_price($offer->min_cart_amount),
                    'max_item_per_order' => (int) $offer->max_item_per_order,
                    'max_qty_per_order' => (int) $offer->max_qty_per_order,
                    'start_date' => (int) $offer->start_date,
                    'end_date' => (int) $offer->end_date,
                    'is_valid' => isset($offer->is_valid)
                        ? $offer->is_valid
                        : ($offer->isValid() ?? false),
                    'items' => $this->formatItems($offer->items),
                    'conditions' => $this->formatConditions($offer->conditions),
                ];
            })
        ];
    }

    /**
     * Format offer items (discounted/gift products)
     */
    protected function formatItems($items)
    {
        if (!$items || $items->isEmpty()) {
            return [];
        }

        return $items->map(function ($item) {
            $product = $item->product;
            if (!$product) {
                return null;
            }

            $originalPrice = (float) $product->unit_price;
            $offerPrice = (float) $item->offer_price;
            $discountAmount = max(0, $originalPrice - $offerPrice);
            $discountPercent = $originalPrice > 0 ? round(($discountAmount / $originalPrice) * 100, 2) : 0;
            $isFree = $offerPrice == 0;
            $availableQty = max(0, (int) $item->available_qty);

            return [
                'id' => $item->id,
                'product_id' => $product->id,
                'product_slug' => $product->slug,
                'product_name' => $product->name,
                'product_thumbnail' => api_asset($product->thumbnail_img),
                'product_thumbnail_url' => uploaded_asset($product->thumbnail_img),
                'available_qty' => $availableQty,
                'original_price' => $originalPrice,
                'formatted_original_price' => format_price($originalPrice),
                'offer_price' => $offerPrice,
                'formatted_offer_price' => format_price($offerPrice),
                'discount_amount' => $discountAmount,
                'formatted_discount_amount' => format_price($discountAmount),
                'discount_percent' => $discountPercent,
                'is_free' => $isFree,
                'discount_label' => $isFree ? 'FREE' : ($discountPercent > 0 ? round($discountPercent) . '% OFF' : ''),
                'is_in_stock' => check_in_stock($product) && $availableQty > 0,
            ];
        })->filter()->values();
    }

    /**
     * Format offer conditions
     */
    protected function formatConditions($conditions)
    {
        if (!$conditions || $conditions->isEmpty()) {
            return [];
        }

        return $conditions->map(function ($condition) {
            $data = [
                'id' => $condition->id,
                'condition_type' => $condition->condition_type,
            ];

            if ($condition->condition_type === 'product' && $condition->product) {
                $data['product_id'] = $condition->item_id;
                $data['product_name'] = $condition->product->name;
                $data['product_slug'] = $condition->product->slug;
            }

            if ($condition->min_qty) {
                $data['min_quantity'] = (int) $condition->min_qty;
            }

            return $data;
        })->values();
    }

    public function with($request = null)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
