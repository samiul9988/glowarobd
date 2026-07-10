<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductCustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'banner',
        'is_active',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Only generate slug if not manually set
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->name);
            }
        });

        static::updating(function ($model) {
            // Only regenerate slug if the 'name' field has changed
            if ($model->isDirty('name') || $model->isDirty('slug')) {
                $model->slug = static::generateUniqueSlug($model->name, $model->id);
            }
        });
    }

    protected static function generateUniqueSlug(string $slugable, ?int $except = null): string
    {
        $slug = Str::slug($slugable, '_');
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->when($except, fn ($query) => $query->where('id', '!=', $except))->exists()) {
            $slug = $originalSlug . '_' . $count++;
        }

        return $slug;
    }

    public static function fields()
    {
        return [
            'html_box' => 'HTML Box',
            'single_text_box' => 'Single Line Text Box',
            'multi_text_box' => 'Multi Line Text Box',
            'single_select' => 'Single Select',
            'multi_select' => 'Multi Select',
        ];
    }

    public static function getFields()
    {
        return self::fields();
    }

    public static function getField($field)
    {
        return self::fields()[$field];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function fieldsData()
    {
        return $this->hasOne(ProductsCustomFieldsData::class);
    }
}
