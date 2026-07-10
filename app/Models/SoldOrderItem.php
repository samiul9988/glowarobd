<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_detail_id',
        'barcode',
        'qty',
        'status',
    ];

    public function orderDetails()
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }

    public function purchaseOrderItem()
    {
        return $this->hasOne(PurchaseOrderItem::class, 'barcode', 'barcode');
    }
}
