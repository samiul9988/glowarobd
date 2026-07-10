<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\AttendanceLog;

class AttendanceObserver
{
    protected $ignored = ['updated_at'];
    public function updated(Attendance $attendance)
    {
        $original = $attendance->getOriginal();
        // $changes = $attendance->getChanges();
        $changes = array_diff_key($attendance->getChanges(), array_flip($this->ignored));

        if (!empty($changes)) {
            AttendanceLog::create([
                'attendance_id' => $attendance->id,
                'created_by' => auth()->id(),
                'old_data' => array_intersect_key($original, $changes),
                'new_data' => $changes,
            ]);
        }
    }
}
