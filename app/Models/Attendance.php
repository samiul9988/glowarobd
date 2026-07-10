<?php

namespace App\Models;

use App\Enums\ShiftEnum;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'staff_id',
        'date',
        'shift',
        'check_in',
        'check_out',
        'work_minutes',
        'overtime_minutes',
        'late_minutes',
        'early_leave_minutes',
        'is_alternative',
        'alternative_date',
        'note',
        'status',
        'device_info',
    ];

    public function setShiftAttribute($value)
    {
        $this->attributes['shift'] = $value ?: ShiftEnum::DAY->value;
    }

    protected $casts = [
        'shift' => ShiftEnum::class,
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'is_alternative' => 'boolean',
        'alternative_date' => 'date',
        'device_info' => 'array',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function logs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function overtimes()
    {
        return $this->hasMany(AttendeeOvertime::class);
    }
}
