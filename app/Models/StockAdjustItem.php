<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'stock_adjust_items';
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

    public function stockadjust()
    {
        return $this->belongsTo(StockAdjust::class, 'stock_adjust_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
}
