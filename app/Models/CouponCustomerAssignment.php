<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponCustomerAssignment extends Model
{
    protected $fillable = [
        'coupon_id',
        'customer_id',
        'assigned_by',
        'expire_date',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
