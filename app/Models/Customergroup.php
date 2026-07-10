<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customergroup extends Model
{
    use HasFactory;
    protected $table = 'customer_groups';

    public function image(){
        return $this->hasOne(Upload::class, 'id', 'group_image');
    }
}
