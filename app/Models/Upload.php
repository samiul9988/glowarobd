<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Upload extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'file_original_name', 'file_name', 'user_id', 'extension', 'type', 'file_size',
    ];

    protected $appends = ['full_url'];

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function getFullUrlAttribute()
    {
        if (function_exists('uploaded_asset')) {
            return uploaded_asset($this->id);
        }
        return null;
    }
}
