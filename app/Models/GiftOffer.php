<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;

class GiftOffer extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'offer_type',
        'min_cart_amount',
        'max_item_per_order',
        'max_qty_per_order',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'min_cart_amount' => 'decimal:2',
        'status' => 'boolean'
    ];

    /**
     * Scope for active offers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for valid date range
     */
    public function scopeValid($query)
    {
        $now = time();
        return $query->active()->where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now);
    }

    /**
     * Check if offer is currently valid
     */
    public function isValid(): bool
    {
        $now = time();
        return $this->status == 1
            && $this->start_date <= $now
            && $this->end_date >= $now;
    }

    /**
     * Relationship: Gift Offer Conditions
     */
    public function conditions()
    {
        return $this->hasMany(GiftOfferCondition::class);
    }

    /**
     * Relationship: Gift Offer Items (Free Products)
     */
    public function items()
    {
        return $this->hasMany(GiftOfferItem::class);
    }

    /**
     * Relationship: Created By User
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Updated By User
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
