<?php

namespace App\Observers;

use App\Models\AttendeeOvertime;

class AttendeeOvertimeObserver
{
    public function saving(AttendeeOvertime $overtime)
    {
        // Calculate duration before saving
        if ($overtime->start_time && $overtime->end_time) {
            $overtime->overtime_duration =
                $overtime->start_time->diffInMinutes($overtime->end_time);
        }
    }

    public function saved(AttendeeOvertime $overtime)
    {
        $this->recalculateAttendanceOvertime($overtime);
    }

    public function deleted(AttendeeOvertime $overtime)
    {
        $this->recalculateAttendanceOvertime($overtime);
    }

    private function recalculateAttendanceOvertime(AttendeeOvertime $overtime)
    {
        $attendance = $overtime->attendance;

        if (!$attendance) {
            return;
        }

        $total = $attendance->overtimes()->sum('overtime_duration');

        $attendance->updateQuietly([
            'overtime_minutes' => $total
        ]);
    }
}
