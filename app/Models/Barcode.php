<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'value',
    ];

    protected $key = 'code';

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'barcode', 'code');
    }
}
