<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertizement extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function product(){
        return $this->belongsTo(Product::class, 'link', 'id');
    }

    public function category(){
        return $this->belongsTo(Category::class, 'link', 'id');
    }
    public function brand(){
        return $this->belongsTo(Brand::class, 'link', 'id');
    }
}
