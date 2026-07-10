<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id', 'qty' ];
    protected $table = 'purchase_order_item';
    //
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function sellername()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function product_stock()
    {
        return $this->belongsTo(ProductStock::class, 'variant', 'id');
    }

    public function purchase_order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function barcode()
    {
        return $this->hasOne(Barcode::class, 'code', 'barcode');
    }
}
