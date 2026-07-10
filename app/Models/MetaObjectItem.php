<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaObjectItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta_object_id',
        'title',
        'subtitle',
        'description',
        'image',
        'url',
        'is_active',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function metaObject()
    {
        return $this->belongsTo(MetaObject::class);
    }
}
