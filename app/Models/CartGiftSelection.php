<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartGiftSelection extends Model
{
    protected $fillable = [
        'user_id',
        'temp_user_id',
        'gift_offer_id',
        'gift_offer_item_id',
        'product_id',
        'quantity',
        'variation',
    ];

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Gift Offer
     */
    public function giftOffer()
    {
        return $this->belongsTo(GiftOffer::class);
    }

    /**
     * Relationship: Gift Offer Item
     */
    public function giftOfferItem()
    {
        return $this->belongsTo(GiftOfferItem::class);
    }

    /**
     * Relationship: Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
