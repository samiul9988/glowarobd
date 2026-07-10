<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HighlightedItem extends Model
{
    protected $table = "highlighted_items";

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'linkable_type',
        'linkable_id',
        'custom_link',
        'banner_img',
        'highlights',
        'button_text',
        'position',
        'status',
    ];

    protected $casts = [
        'highlights' => 'array',
        'status' => 'boolean',
    ];

    public function linkable()
    {
        return $this->morphTo();
    }
}
