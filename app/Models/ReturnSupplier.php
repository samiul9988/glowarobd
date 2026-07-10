<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnSupplier extends Model
{
    protected $guarded = ['id'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withDefault([
            'name' => 'Unknown Supplier'
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Deleted User'
        ]);
    }

    public function stockAdjust()
    {
        return $this->belongsTo(StockAdjust::class, 'stock_adjust_id');
    }

    public function items()
    {
        return $this->hasMany(StockAdjustItem::class, 'stock_adjust_id', 'stock_adjust_id');
    }
}
