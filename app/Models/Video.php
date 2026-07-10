<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasSlug;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'thumbnail',
        'video_url',
        'attachment',
        'playlist_id',
        'status',
        'featured',
        'views',
        'completed',
        'last_viewed_at',
        'type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_viewed_at' => 'datetime',
    ];

    protected $slugColumn = 'slug';
    protected $slugSourceColumn = 'title';

    // public function playlist()
    // {
    //     return $this->belongsTo(VideoPlaylist::class);
    // }

    public function playlists()
    {
        return $this->belongsToMany(VideoPlaylist::class, 'playlist_video', 'video_id', 'playlist_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_video', 'video_id', 'product_id');
    }

    public function attachmentFile()
    {
        return $this->belongsTo(Upload::class, 'attachment', 'id');
    }

    public function scopeReels($query)
    {
        return $query->where('type', 'reel');
    }
    public function scopeNotReels($query)
    {
        return $query->where('type', '!=', 'reel');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', 1);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}
