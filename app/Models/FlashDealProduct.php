<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashDealProduct extends Model
{
    protected $fillable = [
        'flash_deal_id',
        'product_id',
        'discount',
        'discount_type',
        'quantity',
        'sell_quantity'
    ];

    public function flash_deals()
    {
        return $this->belongsTo(FlashDeal::class, 'flash_deal_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
