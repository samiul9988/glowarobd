<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStockManage extends Model
{
    protected $fillable = ['product_id', 'qty'];
    protected $table = 'product_stocks_manage';
    //
    public function product(){
    	return $this->belongsTo(Product::class);
    }

    public function supplier(){
    	return $this->belongsTo(Supplier::class);
    }

    public function wholesalePrices() {
        return $this->hasMany(WholesalePrice::class);
    }
}
