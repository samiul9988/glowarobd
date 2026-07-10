<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{
    protected $guarded = [];

    protected $casts = [
        'deadline' => 'date',
        'published_at' => 'datetime',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'application_form' => 'array',
    ];

    public function getDeadlineEndAttribute()
    {
        return $this->deadline?->copy()->endOfDay();
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
