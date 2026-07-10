<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAttendanceCheckout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $attendanceIds;

    public function __construct(array $attendanceIds)
    {
        $this->attendanceIds = $attendanceIds;
    }

    public function handle(AttendanceService $attendanceService)
    {
        $now = now();

        $attendances = Attendance::whereIn('id', $this->attendanceIds)->get();

        foreach ($attendances as $attendance) {
            $attendance->updateQuietly([
                'check_out' => $now,
                'status' => 'present',
                'note' => 'Auto checkout by system',
            ]);

            $attendanceService->applyCheckOutMetrics($attendance);
        }
    }
}
