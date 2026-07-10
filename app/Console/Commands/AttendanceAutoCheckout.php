<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAttendanceCheckout;
use App\Models\Attendance;
use Illuminate\Console\Command;

class AttendanceAutoCheckout extends Command
{
    protected $signature = 'attendance:checkout';

    public function handle()
    {
        if (get_setting('enable_attendance_management', 0) != 1) {
            return;
        }

        $now = now();

        Attendance::whereNotNull('check_in')
            ->whereNull('check_out')
            ->chunkById(100, function ($attendances) use ($now) {
                $needToProcess = [];
                foreach ($attendances as $attendance) {
                    $checkoutTime = $attendance->shift->checkOut($attendance->date);
                    if ($now->gte($checkoutTime->copy()->addMinutes(30))) {
                        $needToProcess[] = $attendance->id;
                    }
                }
                if (count($needToProcess) > 0) {
                    ProcessAttendanceCheckout::dispatch($needToProcess);
                    $this->info("Dispatched checkout job for " . count($needToProcess) . " attendances.");
                }
            });

        $this->info('Auto checkout processed for all overdue attendances.');
    }
}
