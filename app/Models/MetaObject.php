<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaObject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
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

    public function items()
    {
        return $this->hasMany(MetaObjectItem::class);
    }

    public function productsCustomFieldsData()
    {
        return $this->hasMany(ProductsCustomFieldsData::class);
    }
}
