<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReturnItem extends Model
{
    protected $fillable = [
        'order_return_id',
        'order_item_id',
        'quantity',
        'unit_price',
    ];

    public function orderReturn()
    {
        return $this->belongsTo(OrderReturn::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderDetail::class);
    }
}
