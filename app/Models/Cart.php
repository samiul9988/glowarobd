<?php

namespace App\Models;

use App\Models\User;
use App\Models\Address;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Cart extends Model
{
    protected $guarded = [];
    protected $fillable = ['address_id','price','tax','shipping_cost','discount','product_referral_code','coupon_code','coupon_applied','quantity','user_id','temp_user_id','owner_id','product_id','variation','subscription_day', 'shipping_type','cart_type', 'gift_offer_id', 'gift_offer_item_id'];

    public function scopeWithoutRegular($query)
    {
        return $query->where('cart_type', '!=', 'regular');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(){
        return $this->belongsTo(ProductStock::class, 'variation', 'variant');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function giftOffer()
    {
        return $this->belongsTo(GiftOffer::class, 'gift_offer_id');
    }

    public function giftOfferItem()
    {
        return $this->belongsTo(GiftOfferItem::class, 'gift_offer_item_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('regular', function (Builder $builder) {
            $builder->where('cart_type', 'regular');   // adjust condition
        });
    }
}
