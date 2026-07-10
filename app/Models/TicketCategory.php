<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketCategory extends Model
{
    use HasFactory;

    protected $table = "ticket_categories";

    protected $fillable = [
        'name', 'slug', 'description', 'parent_id', 'status'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function parent()
    {
        return $this->belongsTo(TicketCategory::class, 'parent_id');
    }

    public function childs()
    {
        return $this->hasMany(TicketCategory::class, 'parent_id')->with('childs');
    }

    public function childsWithTicketsCount()
    {
        return $this->hasMany(TicketCategory::class, 'parent_id')->withCount('tickets')->with('childs');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

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
