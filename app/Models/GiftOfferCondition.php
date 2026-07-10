<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftOfferCondition extends Model
{
    protected $fillable = [
        'gift_offer_id',
        'condition_type',
        'item_id',
        'min_qty',
    ];

    /**
     * Relationship: Gift Offer
     */
    public function giftOffer()
    {
        return $this->belongsTo(GiftOffer::class);
    }

    /**
     * Relationship: Product (for product type condition)
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    /**
     * Get the condition reference name
     */
    public function getConditionNameAttribute(): string
    {
        return match($this->condition_type) {
            'product' => $this->product?->name ?? 'Unknown Product',
            default => 'Unknown'
        };
    }
}
