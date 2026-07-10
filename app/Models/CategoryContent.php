<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryContent extends Model
{
    use HasFactory;

    protected $table = 'categories_content';
    protected $fillable = [
        'category_id',
        'type',
        'value',
        'lang'
    ];

    public function category(){
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
