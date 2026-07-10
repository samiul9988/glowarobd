<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffEvent extends Model
{
    protected $fillable = [
        'staff_id',
        'event_type',
        'event_date',
        'title',
        'attachment',
    ];

    protected $casts = [
        'event_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
