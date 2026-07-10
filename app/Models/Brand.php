<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'status'
    ];
    //   protected $with = ['brand_translations'];
    use Searchable;

    public function getScoutKey()
    {
        return $this->id;
    }

    public function getScoutKeyName()
    {
        return 'id';
    }

    public function searchableAs()
    {
        return config('scout.prefix') . 'brands_index';
    }

    public function shouldBeSearchable()
    {
        return get_setting('enable_meilisearch') == 1;
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            // 'search_name' => str_replace('.', ' ', strtolower($this->name)),
            'search_name' => normalizeMeiliSearchText($this->name),
            // These are for filtering
            'id' => $this->id,
            // 'slug' => $this->slug,
            'status' => $this->status,
            'top' => $this->top,
            'created_at' => $this->created_at?->timestamp,
        ];
    }

    public static function generateUniqueSlug(string $slugable, ?int $except = null): string
    {
        $slug = Str::slug($slugable);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->when($except, fn ($query) => $query->where('id', '!=', $except))->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    public function getTranslation($field = '', $lang = false)
    {
        return $this->$field;
        $lang              = $lang == false ? App::getLocale() : $lang;
        $brand_translation = $this->brand_translations->where('lang', $lang)->first();
        return $brand_translation != null ? $brand_translation->$field : $this->$field;
    }

    public function brand_translations()
    {
        return $this->hasMany(BrandTranslation::class);
    }

    public function logo()
    {
        return $this->hasOne(Upload::class, 'id', 'logo');
    }

    public function scopeActive($query)
    {
        return $query;
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($brand) {
            Cache::forget('filter_brands');
        });

        static::updated(function ($brand) {
            Cache::forget('filter_brands');
        });

        static::deleting(function ($brand) {
            Cache::forget('filter_brands');
        });
    }

    public function highlighted_items()
    {
        return $this->morphMany(HighlightedItem::class, 'linkable');
    }

}
