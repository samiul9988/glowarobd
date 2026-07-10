<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionDesign extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'image', 'file_name'];
    protected $hidden = ['created_at', 'updated_at'];
}
