<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function getSmsTypeAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->attributes['type']));
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Unknown User',
            'phone' => 'N/A',
        ]);
    }
}
