<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['user_id', 'grand_total', 'po_number', 'total_payment', 'total_due', 'supplier_id', 'purchase_date'];
    protected $table = 'purchase_order';
    //
    public function product(){
    	return $this->belongsTo(Product::class);
    }


    public function sellername(){
    	return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function supplier(){
    	return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function purchaseOrderDetails()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function payments(){
        return $this->morphMany(Payment::class, 'reference');
    }
}
