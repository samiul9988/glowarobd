<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $guarded = ['duration', 'unpaid_days', 'date_breakdowns'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_start_date' => 'date',
        'approved_end_date' => 'date',
        'duration' => 'integer',
        'paid_days' => 'integer',
        'unpaid_days' => 'integer',
        'date_breakdowns' => 'array',
    ];

    public function application()
    {
        return $this->morphOne(Application::class, 'applicable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
