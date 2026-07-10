<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VideoPlaylist extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'thumbnail',
        'status',
        'featured',
    ];

    protected $slugColumn = 'slug';
    protected $slugSourceColumn = 'name';

    // public function videos()
    // {
    //     return $this->hasMany(Video::class, 'playlist_id');
    // }

    public function videos()
    {
        return $this->belongsToMany(Video::class, 'playlist_video', 'playlist_id', 'video_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', 1);
    }
}
