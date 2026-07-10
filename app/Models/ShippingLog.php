<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingLog extends Model
{
    use HasFactory;

    protected $table = 'shipping_logs';
    protected $guarded = [];

    public function order(){
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function shipping_method(){
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }
}
