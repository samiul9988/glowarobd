<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftOfferItem extends Model
{
    protected $fillable = [
        'gift_offer_id',
        'product_id',
        'available_qty',
        'offer_price',
        'used_qty',
    ];

    protected $casts = [
        'offer_price' => 'decimal:2',
    ];

    /**
     * Get original product price
     */
    public function getOriginalPriceAttribute(): float
    {
        return $this->product ? (float) $this->product->unit_price : 0;
    }

    /**
     * Check if item is free (offer_price = 0)
     */
    public function isFree(): bool
    {
        return (float) $this->offer_price == 0;
    }

    /**
     * Relationship: Gift Offer
     */
    public function giftOffer()
    {
        return $this->belongsTo(GiftOffer::class);
    }

    /**
     * Relationship: Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
