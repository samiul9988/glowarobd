<?php

namespace App\Services;

use App\Enums\ShiftEnum;
use App\Models\Attendance;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    protected $lateMinutesThreshold = 10;

    /**
     * Check-in a staff member
     */
    public function checkIn(Staff $staff, array $payload): array
    {
        $date = today();
        $now = now();

        // Use database transaction to ensure data consistency
        return DB::transaction(function () use ($staff, $payload, $date, $now) {
            // Don't allow check-in if there's already an open session
            $openAttendance = Attendance::where('staff_id', $staff->id)
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->latest('date')
                ->first();

            if ($openAttendance) {
                return [
                    'success' => false,
                    'message' => 'You have an open session. Please check out first.',
                ];
            }

            $shift = $this->determineShift($staff);
            $attendance = Attendance::firstOrCreate([
                'staff_id' => $staff->id,
                'date' => $date,
            ], ['shift' => $shift]);

            // Only process if not already checked in
            if (! $attendance->check_in) {
                $data = [
                    'status' => 'present',
                    'check_in' => $now->toDateTimeString(),
                    'device_info' => [
                        'start_by' => [
                            'ip' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                        ],
                    ],
                ];

                // Handle alternative check-in
                if ($this->isAlternativeCheckIn($payload)) {
                    $data['is_alternative'] = true;
                    $data['alternative_date'] = $payload['alternativeDate'];
                    $this->manageAlternative($attendance, $payload['alternativeDate']);
                }

                $attendance->updateQuietly($data);

                $this->applyCheckInMetrics($attendance->refresh());

                return [
                    'success' => true,
                    'message' => 'Check In Complete',
                ];
            }

            return [
                'success' => false,
                'message' => 'Already Checked In',
            ];
        });
    }

    /**
     * Check-out a staff member
     */
    public function checkOut(Staff $staff): ?array
    {
        return DB::transaction(function () use ($staff) {
            $attendance = Attendance::where('staff_id', $staff->id)
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->latest('date')
                ->first();

            if (! $attendance) {
                return [
                    'success' => false,
                    'message' => 'No open attendance found',
                ];
            }

            $attendance->updateQuietly([
                'check_out' => now()->toDateTimeString(),
                'device_info' => [
                    'stop_by' => [
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ],
                ],
            ]);

            $this->applyCheckOutMetrics($attendance);

            return [
                'success' => true,
                'message' => 'Check Out Complete',
            ];
        });
    }

    /**
     * Handle overtime in for a staff member
     */
    public function overtimeIn(User $user): array
    {
        $user->loadMissing('staff');
        if (! $user->staff) {
            return ['success' => false, 'message' => 'Unauthorized', 'code' => 401];
        }

        $attendanceData = getTodayAttendanceData($user);
        if (! $attendanceData['doing_overtime']) {
            $attendance = $attendanceData['attendance'];

            if ($attendance) {
                \App\Models\AttendeeOvertime::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => now()->toDateTimeString(),
                    'device_info' => [
                        'start_by' => [
                            'ip' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                        ],
                    ],
                ]);

                return ['success' => true, 'message' => 'Overtime Initiated'];
            }
        }

        return ['success' => false, 'message' => 'Cannot Initiate Overtime'];
    }

    /**
     * Handle overtime out for a staff member
     */
    public function overtimeOut(User $user): array
    {
        $user->loadMissing('staff');
        if (! $user->staff) {
            return ['success' => false, 'message' => 'Unauthorized', 'code' => 401];
        }

        $attendanceData = getTodayAttendanceData($user);
        if ($attendanceData['doing_overtime']) {
            $attendance = $attendanceData['attendance'];

            if ($attendance) {
                $attendeeOvertime = \App\Models\AttendeeOvertime::where('attendance_id', $attendance->id)->latest()->first();

                if (! $attendeeOvertime) {
                    return ['success' => false, 'message' => 'Overtime Record Not Found', 'code' => 404];
                }

                $attendeeOvertime->update([
                    'end_time' => now()->toDateTimeString(),
                    'device_info' => [
                        'stop_by' => [
                            'ip' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                        ],
                    ],
                ]);

                return ['success' => true, 'message' => 'Overtime Stopped'];
            }
        }

        return ['success' => false, 'message' => 'Cannot Stop Overtime'];
    }

    private function manageAlternative(Attendance $attendance, string $alternativeDate): void
    {
        $alternativeDate = Carbon::parse($alternativeDate);

        $alternativeAttendance = Attendance::where('staff_id', $attendance->staff_id)
            ->where('date', $alternativeDate->toDateString())
            ->first();

        // Create new alternative attendance if it doesn't exist
        if (! $alternativeAttendance) {
            $alternativeAttendance = Attendance::create([
                'staff_id' => $attendance->staff_id,
                'date' => $alternativeDate->toDateString(),
                'shift' => $attendance->shift,
                'status' => 'offday',
                'is_alternative' => true,
                'alternative_date' => $attendance->date, // Set current attendance date as alternative date for the new record
            ]);

            return;
        }

        if ($alternativeAttendance->is_alternative && ! is_null($alternativeAttendance->alternative_date) && $alternativeAttendance->alternative_date != $attendance->date) {
            $attendance->logs()->create([
                'new_data' => [
                    'type' => 'alternative_conflict',
                    'staff_id' => $attendance->staff_id,
                    'requested_date' => $alternativeDate->toDateString(),
                    'existing_date' => $alternativeAttendance->date,
                    'existing_alternative' => $alternativeAttendance->alternative_date,
                    'action' => 'auto_reassigned',
                    'message' => "Alternative attendance conflict detected for {$alternativeAttendance->date}. An existing alternative mapping already exists ({$alternativeAttendance->alternative_date}). System automatically adjusted the record to prevent duplicate or inconsistent alternative assignments.",
                ],
            ]);

            return;
        }

        $alternativeAttendance->updateQuietly([
            'is_alternative' => true,
            'alternative_date' => $attendance->date,
            'status' => 'offday',
        ]);
    }

    /**
     * Determine the appropriate shift for a staff member
     */
    private function determineShift(Staff $staff): string
    {
        // If staff has a fixed shift, use it
        if ($staff->shift) {
            return $staff->shift->value;
        }

        // Otherwise determine based on current time
        $now = now();
        $windowMinutes = 10;
        foreach (ShiftEnum::cases() as $case) {
            $checkout = $case->checkOut($now);

            if ($now->betweenIncluded(
                $checkout->copy()->subMinutes($windowMinutes),
                $checkout->copy()->addMinutes($windowMinutes)
            )) {
                return $case->value;
            }
        }

        // Default to day shift
        return ShiftEnum::DAY->value;
    }

    /**
     * Apply metrics after check-in
     */
    public function applyCheckInMetrics(Attendance $attendance): void
    {
        if (in_array($attendance->status, ['holiday', 'offday']) || is_null($attendance->check_in)) {
            $attendance->updateQuietly(['late_minutes' => 0]);
        }

        $shiftStart = $this->getShiftStartDateTime($attendance);
        $checkIn = is_null($attendance->check_in) ? $shiftStart : Carbon::parse($attendance->check_in);

        $lateMinutes = $checkIn->greaterThan($shiftStart)
            ? (int) $shiftStart->diffInMinutes($checkIn)
            : 0;

        $attendance->updateQuietly(['late_minutes' => $lateMinutes > $this->lateMinutesThreshold ? $lateMinutes : 0]);
    }

    /**
     * Apply metrics after check-out
     */
    public function applyCheckOutMetrics(Attendance $attendance): void
    {
        if (in_array($attendance->status, ['holiday', 'offday']) || is_null($attendance->check_out)) {
            $attendance->updateQuietly([
                'work_minutes' => 0,
                'early_leave_minutes' => 0,
            ]);

            return;
        }

        $attendance->loadMissing('staff');

        $shiftStart = $this->getShiftStartDateTime($attendance);
        $shiftEnd = $this->getShiftEndDateTime($attendance);

        $checkIn = is_null($attendance->check_in) ? $shiftStart : Carbon::parse($attendance->check_in);
        $checkOut = is_null($attendance->check_out) ? null : Carbon::parse($attendance->check_out);

        $workMinutes = (int) ceil($checkIn->diffInMinutes($checkOut));
        $earlyLeaveMinutes = $checkOut->lessThan($shiftEnd)
            ? (int) $checkOut->diffInMinutes($shiftEnd)
            : 0;
        $staffWorkMinutes = ($attendance->staff->working_hours ?? 8) * 60;

        $attendance->updateQuietly([
            'work_minutes' => $workMinutes,
            'early_leave_minutes' => $earlyLeaveMinutes,
        ]);
    }

    public function updateAttendance(Attendance $attendance, array $payload): Attendance
    {
        return DB::transaction(function () use ($attendance, $payload) {
            $data = [];

            // ✅ CHECK-IN FIX
            if (isset($payload['check_in']) && ! in_array($payload['status'], ['holiday', 'offday'])) {
                if (! is_null($attendance->check_in)) {
                    $previousCheckIn = Carbon::parse($attendance->check_in)->format('Y-m-d H:i');
                } else {
                    $previousCheckIn = Carbon::parse($attendance->date)->startOfDay()->format('Y-m-d H:i');
                }
                $newDateTime = Carbon::parse($payload['check_in'])->format('Y-m-d H:i');

                if (is_null($attendance->check_in) || $previousCheckIn !== $newDateTime) {
                    $data['check_in'] = $newDateTime;
                }
            }

            // ✅ CHECK-OUT FIX
            if (isset($payload['check_out']) && ! in_array($payload['status'], ['holiday', 'offday'])) {
                if (! is_null($attendance->check_out)) {
                    $previousCheckOut = Carbon::parse($attendance->check_out)->format('Y-m-d H:i');
                } else {
                    $previousCheckOut = Carbon::parse($attendance->date)->startOfDay()->format('Y-m-d H:i');
                }
                $newDateTime = Carbon::parse($payload['check_out'])->format('Y-m-d H:i');

                if (is_null($attendance->check_out) || $previousCheckOut !== $newDateTime) {
                    $data['check_out'] = $newDateTime;
                }
            }

            // ✅ SHIFT
            if (isset($payload['shift']) && $attendance->shift !== $payload['shift']) {
                $data['shift'] = $payload['shift'];
            }

            // ✅ ALTERNATIVE TYPE
            if (isset($payload['check_out_type']) && $payload['check_out_type'] === 'alternative' && ! $attendance->is_alternative) {
                $data['is_alternative'] = true;
            }

            // ✅ ALTERNATIVE DATE
            if (isset($payload['alternative_date']) && ($attendance->is_alternative || ($data['is_alternative'] ?? false))) {
                $newDate = Carbon::parse($payload['alternative_date'])->toDateString();

                if ($attendance->alternative_date != $newDate) {
                    $data['alternative_date'] = $newDate;
                    $this->manageAlternative($attendance, $newDate);
                }
            }

            // ✅ STATUS
            if (isset($payload['status']) && $attendance->status !== $payload['status']) {
                $data['status'] = $payload['status'];

                if ($payload['status'] !== 'present') {
                    $data['check_in'] = null;
                    $data['check_out'] = null;
                    $data['work_minutes'] = 0;
                    $data['overtime_minutes'] = 0;
                    $data['late_minutes'] = 0;
                    $data['early_leave_minutes'] = 0;
                }
            }

            // ✅ NOTE
            if (isset($payload['note']) && $attendance->note !== $payload['note']) {
                $data['note'] = $payload['note'];
            }

            // dd($data, $attendance->toArray() ?? []);
            // ✅ Only update if something changed
            if (! empty($data)) {
                $attendance->update($data);
            }

            $attendance->overtimes()->delete();
            $attendance->updateQuietly([
                'overtime_minutes' => 0,
            ]);
            // ✅ OVERTIME UPDATE
            if (! empty($payload['overtimes']) && is_array($payload['overtimes'])) {
                foreach ($payload['overtimes'] as $overtime) {
                    $attendance->overtimes()->create($overtime);
                }
            }

            // dd(array_intersect_key($data, array_flip(['check_in', 'shift', 'is_alternative', 'alternative_date'])), array_intersect_key($data, array_flip(['check_out', 'shift', 'is_alternative', 'alternative_date'])));
            // ✅ Re-apply metrics only if relevant fields changed
            if (array_intersect_key($data, array_flip(['check_in', 'shift', 'is_alternative', 'alternative_date']))) {
                $this->applyCheckInMetrics($attendance);
            }

            if (array_intersect_key($data, array_flip(['check_out', 'shift', 'is_alternative', 'alternative_date']))) {
                $this->applyCheckOutMetrics($attendance);
            }

            return $attendance;
        });
    }

    public function getByMonth(int $staffId, int $year, int $month)
    {
        return Attendance::with('overtimes', 'staff:id,user_id')
            ->withCount('logs')
            ->where('staff_id', $staffId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();
    }

    public function monthlyAttendanceForAllStaff($year, $month)
    {
        return DB::table('staff')
            ->whereNotIn('staff.employment_status', ['terminated', 'resigned'])
            ->leftJoin('attendances', function ($join) use ($year, $month) {
                $join->on('staff.id', '=', 'attendances.staff_id')
                    ->whereYear('attendances.date', $year)
                    ->whereMonth('attendances.date', $month);
            })
            ->leftJoin('users', 'staff.user_id', '=', 'users.id')
            ->select(
                'staff.id',
                'users.name as staff_name',
                DB::raw("SUM(CASE WHEN attendances.status='present' THEN 1 ELSE 0 END) present"),
                DB::raw("SUM(CASE WHEN attendances.status='absent' THEN 1 ELSE 0 END) absent"),
                DB::raw("SUM(CASE WHEN attendances.status='leave' THEN 1 ELSE 0 END) leave_days"),
                DB::raw('SUM(attendances.overtime_minutes) overtime_minutes'),
                DB::raw("SUM(CASE WHEN attendances.status IN ('present','absent','leave') THEN 1 ELSE 0 END) working_days")
            )
            ->groupBy('staff.id', 'staff_name')
            ->orderByDesc('users.recent_login')
            ->get();
    }

    public function getTodaySummary(): array
    {
        $today = today();

        $summary = Cache::remember("today_summary_{$today->toDateString()}", now()->addHour(), function () use ($today) {
            return DB::table('attendances')
                ->whereDate('date', $today)
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END), 0) as present,
                    COALESCE(SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END), 0) as absent,
                    COALESCE(SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END), 0) as leaves,
                    COALESCE(SUM(CASE WHEN status = 'holiday' THEN 1 ELSE 0 END), 0) as holidays,
                    COALESCE(SUM(CASE WHEN status = 'offday' THEN 1 ELSE 0 END), 0) as offdays
                ")
                ->first();
        });

        return (array) $summary;
    }

    /**
     * Get shift start datetime
     */
    private function getShiftStartDateTime(Attendance $attendance): Carbon
    {
        $shift = $attendance->shift ?? ShiftEnum::DAY;

        return $shift->checkIn($attendance->date ?? today());
    }

    /**
     * Get shift end datetime (handles overnight shifts)
     */
    private function getShiftEndDateTime(Attendance $attendance): Carbon
    {
        $shift = $attendance->shift ?? ShiftEnum::DAY;

        return $shift->checkOut($attendance->date ?? today());
    }

    /**
     * Check if this is an alternative check-in
     */
    private function isAlternativeCheckIn(array $payload): bool
    {
        return isset($payload['checkInType'])
            && $payload['checkInType'] === 'alternative'
            && isset($payload['alternativeDate']);
    }
}
