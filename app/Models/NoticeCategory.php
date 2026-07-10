<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NoticeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function notices()
    {
        return $this->hasMany(Notice::class);
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
            $slugable = filled($model->slug) ? $model->slug : $model->name;
            $model->generateSlug($slugable);
        });
        
        static::updating(function ($model) {
            // Only regenerate slug if name changed
            if ($model->isDirty('name') || $model->isDirty('slug')) {
                $slugable = filled($model->slug) ? $model->slug : $model->name;
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
        $slug = Str::slug($slugable ?? $this->name);
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
