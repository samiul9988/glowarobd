<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Category extends Model
{
    // protected $with = ['category_translations'];
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
        return config('scout.prefix') . 'categories_index';
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
            'parent_id' => $this->parent_id,
            'level' => $this->level,
            'order_level' => $this->order_level,
            'status' => $this->status,
            'featured' => $this->featured,
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

    public function getTranslation($field = '', $lang = false){
        return $this->$field;
        $lang = $lang == false ? App::getLocale() : $lang;
        $category_translation = $this->category_translations->where('lang', $lang)->first();
        return $category_translation != null ? $category_translation->$field : $this->$field;
    }

    public function scopeActive($query){
        return $query;
    }

    public function scopeFeatured($query){
        return $query->where('featured', 1);
    }

    public function scopeTop($query){
        return $query->where('top', 1);
    }

    public function category_translations(){
    	return $this->hasMany(CategoryTranslation::class);
    }

    public function products(){
    	return $this->hasMany(Product::class);
    }

    public function classified_products(){
    	return $this->hasMany(CustomerProduct::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('categories');
    }

    public function childrenCategories()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('categories');
    }

    public function childrens()
    {
        return $this->hasMany(Category::class, 'parent_id')
        ->with(['childrens' => function ($query) {
            $query->active()->orderBy('name'); // Recursive sorting
        }]);
    }

    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class);
    }

    public function content(){
        return $this->hasMany(CategoryContent::class, 'category_id');
    }

    public function subcategories() {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($category) {
            Cache::forget('filter_categories');
        });

        static::updated(function ($category) {
            Cache::forget('filter_categories');
        });

        static::deleting(function ($category) {
            Cache::forget('filter_categories');
        });
    }

    public function highlighted_items()
    {
        return $this->morphMany(HighlightedItem::class, 'linkable');
    }

}
