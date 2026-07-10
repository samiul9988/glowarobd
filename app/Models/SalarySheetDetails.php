<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalarySheetDetails extends Model
{
    protected $table = 'salary_sheets_details';

    protected $guarded = [];

    protected $casts = [
        'attendance_summary' => 'array',
        'working_summary' => 'array',
        'leave_summary' => 'array',
        'bonuses' => 'array',
        'profile_salary' => 'decimal:2',
        'working_hours_per_day' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'leave_amount' => 'decimal:2',
        'bonus_total' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
    ];

    public function salarySheet(): BelongsTo
    {
        return $this->belongsTo(SalarySheet::class, 'salary_sheets_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function scopeForStaff(Builder $query, int $staffId): Builder
    {
        return $query->where('staff_id', $staffId);
    }
}
