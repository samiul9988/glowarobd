<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $table = "coupon_usages";

    protected $fillable = [
        'coupon_id',
        'user_id',
        'temp_user_id',
        'order_id',
        'ref_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id')->withDefault([
            'code' => 'Deleted',
        ]);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'ref_id')->withDefault([
            'name' => 'N/A',
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Unknown/Deleted',
        ]);
    }
}
