<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantProduct extends Model
{
    protected $fillable = [
        'merchant_id',
        'product_id',
        'last_price',
        'pushed_at',
        'price_updated_at',
    ];

    protected $casts = [
        'pushed_at' => 'datetime',
        'price_updated_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

    public function merchant()
    {
        return $this->belongsTo(User::class,'merchant_id');
    }
}
