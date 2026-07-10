<?php
namespace App\Observers;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\LeaveLog;
use App\Models\Staff;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveObserver
{
    public function saving(Leave $leave)
    {
        $startDate = $leave->approved_start_date ?? $leave->start_date;
        $endDate = $leave->approved_end_date ?? $leave->approved_start_date ?? $leave->end_date ?? $leave->start_date;

        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);

        // Get holidays within range
        $holidays = Holiday::whereBetween('date', [$start, $end])
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->toDateString())
            ->toArray();

        // Get staff weekly offdays (e.g. ['Saturday', 'Monday'])
        $offDays = collect($leave->user?->staff?->weekly_offday ?? [])
            ->map(fn($day) => ucfirst(strtolower($day)))
            ->toArray();
        // Make sure it's array of day names

        $period = CarbonPeriod::create($start, $end);

        $dateBreakdowns = [];

        $workingDays = collect($period)->filter(function ($date) use ($holidays, $offDays, &$dateBreakdowns) {
            $dateString = $date->toDateString();
            $dayName    = $date->format('l');

            if (in_array($dateString, $holidays)) {
                $dateBreakdowns['holidays'][] = $dateString;
                return false;
            }

            if (in_array($dayName, $offDays)) {
                $dateBreakdowns['off_days'][] = $dateString;
                return false;
            }

            $dateBreakdowns['leave_days'][] = $dateString;

            return true;
        });

        $leave->date_breakdowns = $dateBreakdowns;

        $leave->duration = $workingDays->count();

        $leave->unpaid_days = $leave->duration - ($leave->paid_days ?? 0);

        $leaveDays = $dateBreakdowns['leave_days'] ?? [];
        // Sort the leave days in ascending order
        sort($leaveDays);
        LeaveLog::where('leave_id', $leave->id)->delete();
        if ($leave->application?->status === 'approved') {
            foreach ($leaveDays as $index => $date) {
                LeaveLog::create([
                    'leave_id' => $leave->id,
                    'user_id' => $leave->user_id,
                    'date' => $date,
                    'is_paid' => $index < ($leave->paid_days ?? 0),
                ]);
            }
        }

        if (!empty($leaveDays)) {
            $leaveStatus = $leave->application?->status;
            if (!$leaveStatus) {
                $leaveStatus = is_null($leave->approved_start_date) && is_null($leave->approved_end_date) ? 'rejected' : 'approved';
            }
            $this->manageExistingAttendance($leave->user_id, $leaveDays, $leaveStatus);
        }
    }

    private function manageExistingAttendance(int $userId, array $leaveDays, string $leaveStatus)
    {
        $staffId = Staff::where('user_id', $userId)->first()?->id;
        if ($staffId) {
            $attendances = Attendance::whereIn('date', $leaveDays)
                ->where('staff_id', $staffId)
                ->whereIn('status', ['present', 'absent']);

            if ($leaveStatus === 'approved') {
                $attendances->update(['status' => 'leave']);
            } else {
                $attendances->get()->each(function (Attendance $attendance) {
                    $attendance->status = $attendance->check_in && $attendance->check_out ? 'present' : 'absent';
                    $attendance->saveQuietly();
                });
            }
        }
    }
}
