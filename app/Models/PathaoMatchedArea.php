<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PathaoMatchedArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_area_id',
        'pathao_area_id',
    ];

    public function systemArea()
    {
        return $this->belongsTo(Area::class, 'system_area_id');
    }

    public function pathaoArea()
    {
        return $this->belongsTo(PathaoArea::class, 'pathao_area_id');
    }
}
