<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory;
    protected $fillable = [
        'campaign_category_id',
        'title',
        'slug',
        'thumbnail',
        'description',
        'status',
        'start_date',
        'end_date',
        'created_by',
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $appends = ['formatted_date'];


    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    public function category()
    {
        return $this->belongsTo(CampaignCategory::class, 'campaign_category_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedDateAttribute()
    {
        $formattedData = '<i class="las la-calendar-alt"></i> ';
        if ($this->start_date && $this->end_date) {
            if (date('Y-m-d', strtotime($this->start_date)) == date('Y-m-d', strtotime($this->end_date))) {
                $formattedData .= 'Until ' . date('d M Y', strtotime($this->start_date));
            } else {
                $formattedData .= date('d M Y', strtotime($this->start_date)) . ' - ' .
                                date('d M Y', strtotime($this->end_date));
            }
        } elseif ($this->start_date) {
            $formattedData .= ($this->start_date->isPast() ? 'Started' : 'Start') . ' From ' . date('d M Y', strtotime($this->start_date));
        } elseif ($this->end_date) {
            $formattedData .= 'Until ' . date('d M Y', strtotime($this->end_date));
        }
        
        return $formattedData;
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
