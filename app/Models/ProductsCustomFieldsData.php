<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsCustomFieldsData extends Model
{
    use HasFactory;

    protected $table = 'products_custom_fields_data';

    protected $fillable = [
        'product_id',
        'product_custom_field_id',
        'meta_object_id',
        'value',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productCustomField()
    {
        return $this->belongsTo(ProductCustomField::class);
    }

    public function metaObject()
    {
        return $this->belongsTo(MetaObject::class);
    }
}
