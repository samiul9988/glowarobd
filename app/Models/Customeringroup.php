<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customeringroup extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'customer_groups_id', 'status'];
    protected $table = 'customer_in_groups';


    public function group(){
        return $this->belongsTo(Customergroup::class, 'customer_groups_id','id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id','id');
    }
}
