<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FlashDeal extends Model
{
    protected $fillable = [
        'title', 'slug', 'start_date', 'end_date', 'text_color', 'background_color', 'status', 'featured', 'app_featured', 'banner', 'desktop_banner',
    ];

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
        $lang = $lang == false ? App::getLocale() : $lang;
        $flash_deal_translation = $this->flash_deal_translations->where('lang', $lang)->first();

        return $flash_deal_translation != null ? $flash_deal_translation->$field : $this->$field;
    }

    public function flash_deal_translations()
    {
        return $this->hasMany(FlashDealTranslation::class);
    }

    public function flash_deal_products()
    {
        return $this->hasMany(FlashDealProduct::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeOnlyValid($query)
    {
        $now = now()->timestamp;

        return $query->where('start_date', '<=', $now)->where('end_date', '>=', $now);
    }

    public function scopeUpcoming($query)
    {
        $now = now()->timestamp;

        return $query->where('start_date', '>', $now);
    }

    public function isValid()
    {
        $now = now()->timestamp;

        return $this->start_date <= $now && $this->end_date >= $now && $this->status == 1;
    }
}
