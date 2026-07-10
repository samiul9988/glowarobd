<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class ShippingMethod extends Model
{
    public function logo(){
        return $this->hasOne(Upload::class, 'id', 'logo');
    }
}
