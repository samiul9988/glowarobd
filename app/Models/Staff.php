<?php

namespace App\Models;

use App\Enums\ShiftEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'role_id',
        'employee_id',
        'personal_email',
        'profile_picture',
        'address',
        'salary',
        'educational_background',
        'joining_date',
        'shift',
        'working_hours',
        'weekly_offday',
        'emergency_contact',
        'employment_status',
        'blood_group',
        'resign_date',
        'resignation_letter',
        'termination_date',
        'termination_reason',
        'bank_account',
        'note',
    ];

    protected $casts = [
        'weekly_offday' => 'array',
        'emergency_contact' => 'array',
        'bank_account' => 'array',
        'joining_date' => 'date',
        'resign_date' => 'date',
        'termination_date' => 'date',
        'salary' => 'decimal:2',
        'working_hours' => 'decimal:2',
        'shift' => ShiftEnum::class,
    ];

    public function scopeActive($query)
    {
        return $query->whereNotIn('employment_status', ['terminated', 'resigned']);
    }

    public function thisMonthAttendances()
    {
        return $this->hasMany(Attendance::class)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->orderBy('date', 'asc');
    }

    public function allAttendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function pick_up_point(): HasOne
    {
        return $this->hasOne(PickupPoint::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(StaffEvent::class)->orderByDesc('event_date');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(StaffAttachment::class);
    }

    public function salarySheets(): HasMany
    {
        return $this->hasMany(SalarySheet::class);
    }

    public function jobApplication(): HasOne
    {
        return $this->hasOne(JobApplication::class);
    }

    public static function generateEmployeeId(): string
    {
        $prefix = config('app.employee_id_prefix', 'EMP');

        return DB::transaction(function () use ($prefix) {
            // Lock the latest row so concurrent transactions must wait,
            // preventing two processes from reading the same max value.
            $latest = static::query()
                ->where('employee_id', 'like', $prefix.'%')
                ->orderByDesc('employee_id')
                ->lockForUpdate()
                ->value('employee_id');

            $nextNumber = $latest
                ? (int) substr($latest, strlen($prefix)) + 1
                : 1;

            return $prefix.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        });
    }
}
