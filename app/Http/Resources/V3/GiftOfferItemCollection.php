<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\ResourceCollection;

class GiftOfferItemCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($item) {
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
                    'gift_offer_id' => $item->gift_offer_id,
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
                    'current_stock' => $product->stocks?->first()?->qty ?? 0,
                    'offer_title' => $item->giftOffer?->title,
                    'offer_slug' => $item->giftOffer?->slug,
                ];
            })->filter()->values()
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
