<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeCategory extends Model
{
    //

    protected $table = "home_categories";

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
