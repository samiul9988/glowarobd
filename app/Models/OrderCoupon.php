<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCoupon extends Model
{
    protected $fillable = [
        'order_id',
        'coupon_id',
        'customer_id',
        'ref_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'ref_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
