<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjust extends Model
{
    use HasFactory;

    protected $guarded = ['id']; 
    protected $table = 'stock_adjust';
    //
    public function product(){
    	return $this->belongsTo(Product::class);
    }

    public function sellername(){
    	return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function stockAdjustDetails()
    {
        return $this->hasMany(StockAdjustItem::class);
    }
}
