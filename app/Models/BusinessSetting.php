<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $fillable = ['type','value','lang'];
    
    // protected $hidden = ['created_at', 'updated_at'];
}
