<?php

namespace Database\Seeders;

use App\Enums\ShiftEnum;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('attendance_logs')->truncate();
        DB::table('attendances')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $from = Carbon::parse('2026-03-01')->startOfMonth();
        $to = Carbon::parse('2026-03-31')->endOfMonth();

        $staffs = \App\Models\Staff::active()->get();

        foreach ($staffs as $staff) {
            $date = $from->copy();

            while ($date <= $to) {
                // Default status
                $status = fake()->randomElement([
                    'present','present','present','present', // high probability
                    'offday', 'offday',
                    'absent',
                    'leave',
                    'holiday',
                ]);

                $checkIn = null;
                $checkOut = null;
                $workMinutes = 0;
                $lateMinutes = 0;
                $earlyLeaveMinutes = 0;
                $overtimeMinutes = 0;
                $shift = ShiftEnum::tryFrom($staff->shift) ?? ShiftEnum::DAY;
                if ($status === 'present') {
                    $increment = fake()->randomElement([1,2,3]);
                    $checkInHour = $shift->checkIn($date)->hour;
                    $checkOutHour = $shift->checkOut($date)->hour;

                    if ($increment === 1) {
                        $checkInHour += rand(0, 2);
                        $checkOutHour += rand(0, 2);
                    } elseif ($increment === 2) {
                        $checkInHour -= rand(0, 2);
                        $checkOutHour -= rand(0, 2);
                    }

                    $checkIn = $date->copy()->setTime($checkInHour, rand(0, 59));
                    $checkOut = $date->copy()->setTime($checkOutHour, rand(0, 59));

                    $workMinutes = $checkOut->diffInMinutes($checkIn);

                    $lateMinutes = max(0, $shift->checkIn($date)->diffInMinutes($checkIn, false));

                    $earlyLeaveMinutes = max(0, $checkOut->diffInMinutes($shift->checkOut($date), false));

                    $overtimeMinutes = max(0, $shift->checkOut($date)->diffInMinutes($checkOut, false));
                }

                \App\Models\Attendance::create([
                    'staff_id' => $staff->id,
                    'date' => $date->copy(),
                    'shift' => $staff->shift?->value ?? \App\Enums\ShiftEnum::DAY->value,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'work_minutes' => $workMinutes,
                    'overtime_minutes' => $overtimeMinutes,
                    'late_minutes' => $lateMinutes,
                    'early_leave_minutes' => $earlyLeaveMinutes,
                    'is_alternative' => false,
                    'status' => $status,
                    'note' => fake()->optional()->sentence(),
                ]);

                $date->addDay();
            }
        }
    }

    private function isHoliday($date)
    {
        $date = Carbon::parse($date);

        return \App\Models\Holiday::whereDate('date', $date)->exists();
    }

    private function isWeeklyOff($staff, $date)
    {
        $date = Carbon::parse($date);

        $offdays = $staff->weekly_offday ?? [];

        return in_array($date->format('l'), $offdays);
    }

    private function isOnLeave($staff, $date)
    {
        $date = Carbon::parse($date);

        return \App\Models\Leave::where('user_id', $staff->user_id)
            ->whereNotNull('approved_start_date')
            ->whereNotNull('approved_end_date')
            ->whereDate('approved_start_date', '<=', $date)
            ->whereDate('approved_end_date', '>=', $date)
            ->whereHas('application', function ($query) {
                $query->where('status', 'approved');
            })
            ->exists();
    }
}
