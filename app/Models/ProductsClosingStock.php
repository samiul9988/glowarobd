<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsClosingStock extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'closing_stock', 'price', 'date','last_opening_purchase','last_opening_sale','last_opening_plus_adjustment','last_opening_minus_adjustment'];

    protected $casts = [
        'date' => 'datetime:d-m-Y',
    ];

    public function product(){
    	return $this->belongsTo(Product::class);
    }

    public function wholesalePrices() {
        return $this->hasMany(WholesalePrice::class);
    }
}
