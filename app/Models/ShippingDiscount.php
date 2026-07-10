<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingDiscount extends Model
{
    use HasFactory;

    public function zone(){
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }
}
