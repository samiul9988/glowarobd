<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class AttendanceLog extends Model
{
    use HasFactory, Prunable;

    protected $fillable = [
        'attendance_id',
        'created_by',
        'old_data',
        'new_data',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault([
            'name' => 'System',
        ]);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subMonths(6));
    }
}
