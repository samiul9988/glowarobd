<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'notice_category_id',
        'title',
        'slug',
        'content',
        'status',
        'publish_at',
        'visibility',
    ];

    protected $casts = [
        'publish_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
            // ->where(function($q) {
            //     $q->whereNull('publish_at')
            //       ->orWhere('publish_at', '<=', now());
            // });
    }

    public function scopeVisibleFor($query, $type)
    {
        return $query->whereIn('visibility', [
            'both',
            $type,
        ]);
    }

    public function category()
    {
        return $this->belongsTo(NoticeCategory::class, 'notice_category_id');
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $slugable = filled($model->slug) ? $model->slug : $model->title;
            $model->generateSlug($slugable);
        });
        
        static::updating(function ($model) {
            // Only regenerate slug if title changed
            if ($model->isDirty('title') || $model->isDirty('slug')) {
                $slugable = filled($model->slug) ? $model->slug : $model->title;
                $model->generateSlug($slugable);
            }
        });
    }
    
    /**
     * Generate a unique slug for the model.
     *
     * @return void
     */
    public function generateSlug($slugable = null)
    {
        $slug = Str::slug($slugable ?? $this->title);
        $originalSlug = $slug;
        $count = 1;
        
        // Check if slug already exists
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $count++;
        }
        
        $this->slug = $slug;
    }
    
    /**
     * Check if a slug already exists in the database.
     *
     * @param string $slug
     * @return bool
     */
    protected function slugExists($slug)
    {
        $query = static::where('slug', $slug);
        
        // When updating, exclude the current model
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }
        
        return $query->exists();
    }
}