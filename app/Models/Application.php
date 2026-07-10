<?php

namespace App\Models;

use App\Enums\ApplicationTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['applicable'];

    protected $casts = [
        'type' => ApplicationTypes::class,
        'modified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'attachments' => 'array'
    ];

    public function scopeLeaves($query)
    {
        return $query->where('type', ApplicationTypes::LEAVE);
    }

    public function applicable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    // Override booted method
    protected static function booted()
    {
        static::deleting(function ($application) {
            if ($application->applicable) {
                $application->applicable->delete();
            }
        });
    }
}
