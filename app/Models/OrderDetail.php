<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function giftOffer()
    {
        return $this->belongsTo(GiftOffer::class, 'gift_offer_id');
    }

    public function giftOfferItem()
    {
        return $this->belongsTo(GiftOfferItem::class, 'gift_offer_item_id');
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function refund_request()
    {
        return $this->hasOne(RefundRequest::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method', 'id');
    }

    public function soldItems()
    {
        return $this->hasMany(SoldOrderItem::class, 'order_detail_id', 'id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($orderDetail) {
            $product = Product::with('stocks')->find($orderDetail->product_id);

            $product_variation = $orderDetail->variation ?? null;

            $lastPurchaseItem = $product->getLastPurchaseOrderItemByVariant($product_variation);
            if ($lastPurchaseItem) {
                $lastPurchasePrice = $lastPurchaseItem->price;
            } else {
                $lastPurchasePrice = 0;
            }

            if (is_null($orderDetail->last_purchase_price)) {
                $orderDetail->last_purchase_price = $lastPurchasePrice > 0
                        ? $lastPurchasePrice
                        : ($orderDetail->quantity > 0
                            ? $orderDetail->price / $orderDetail->quantity
                            : 0);

                $orderDetail->save();
                Log::channel('custom')->info('OrderDetail last_purchase_price updated from model event', [
                    'order_detail_id' => $orderDetail->id,
                    'last_purchase_price' => $orderDetail->last_purchase_price,
                ]);
            }
        });
    }
}
