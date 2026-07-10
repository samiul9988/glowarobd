<?php
namespace App\Console\Commands;

use App\Enums\ShiftEnum;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Staff;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GenerateAttendance extends Command
{
    protected $signature = 'attendance:generate {month? : Format Y-m}';

    public function handle()
    {
        if (get_setting('enable_attendance_management', 0) != 1) {
            return;
        }

        DB::transaction(function () {
            [$start, $end] = $this->resolveDateRange();

            $dates = collect(CarbonPeriod::create($start, $end))
                ->map(fn($d) => $d->toDateString());

            // 1. Load staff (single query)
            $staffs = Staff::active()
                ->with('user:id,banned')
                ->get();

            if ($staffs->isEmpty()) {
                $this->info('No active staff found.');
                return;
            }

            $staffIds = $staffs->pluck('id');
            $userIds  = $staffs->pluck('user_id');

            // 2. Holidays (single query)
            $holidays = Holiday::whereBetween('date', [$start, $end])
                ->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->toArray();

            // 3. Leaves (single query)
            $leaves = Leave::whereIn('user_id', $userIds)
                ->whereNotNull('approved_start_date')
                ->whereNotNull('approved_end_date')
                ->whereHas('application', fn($q) => $q->where('status', 'approved'))
                ->get()
                ->groupBy('user_id');

            // 4. Existing attendance (single query)
            $existing = Attendance::whereIn('staff_id', $staffIds)
                ->whereBetween('date', [$start, $end])
                ->get()
                ->groupBy(fn($row) => $row->staff_id . '_' . Carbon::parse($row->date)->toDateString());
            $existing = Attendance::whereIn('staff_id', $staffIds)
                ->whereBetween('date', [$start, $end])
                ->get()
                ->mapWithKeys(function ($row) {
                    $key = $row->staff_id . '_' . Carbon::parse($row->date)->toDateString();
                    return [$key => $row];
                });

            $rows = [];
            foreach ($dates as $date) {
                $dayName   = Carbon::parse($date)->format('l');
                $isHoliday = in_array($date, $holidays);

                foreach ($staffs as $staff) {

                    // Skip banned users
                    if ($staff->user?->banned) {
                        continue;
                    }

                    $key = $staff->id . '_' . $date;

                    try{
                        // Skip existing
                        if (isset($existing[$key]) && $existing[$key]->is_alternative && $existing[$key]->alternative_date) {
                            continue;
                        }
                    } catch (\Exception $e) {
                        $this->error("Error occurred while processing attendance for staff {$staff->id} on {$date}: " . $e->getMessage());
                        // show the $existing[$key]
                        $this->error("Existing attendance data for staff {$staff->id} on {$date}: " . json_encode($existing[$key] ?? null));
                        continue;
                    }

                    $status = 'absent';

                    if ($isHoliday) {
                        $status = 'holiday';
                    } elseif (in_array($dayName, $staff->weekly_offday ?? [])) {
                        $status = 'offday';
                    } elseif ($this->hasLeave($leaves, $staff->user_id, $date)) {
                        $status = 'leave';
                    }

                    $rows[] = [
                        'staff_id'   => $staff->id,
                        'date'       => $date,
                        'shift'      => $staff->shift?->value ?? ShiftEnum::DAY->value,
                        'status'     => $status,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // 5. Bulk upsert (VERY FAST)
            if (! empty($rows)) {
                Attendance::upsert(
                    $rows,
                    ['staff_id', 'date'], // unique constraint
                    ['status', 'shift', 'updated_at']
                );
            }

            $this->info("Attendance generated from {$start->toDateString()} to {$end->toDateString()}");
        });

        return Command::SUCCESS;
    }

    private function resolveDateRange(): array
    {
        $inputMonth = $this->argument('month');

        if ($inputMonth) {
            try {
                $start = Carbon::createFromFormat('Y-m', $inputMonth)->startOfMonth();
                $end   = $start->copy()->endOfMonth();

                if ($start->isSameMonth(now()) && $start->isSameYear(now())) {
                    $end = now();
                }
            } catch (\Exception $e) {
                $this->error('Invalid format. Use Y-m');
                exit;
            }
        } else {
            $start = today();
            $end   = today();
        }

        return [$start, $end];
    }

    private function hasLeave(Collection $leaves, $userId, $date): bool
    {
        if (! isset($leaves[$userId])) {
            return false;
        }

        foreach ($leaves[$userId] as $leave) {
            if (
                $date >= Carbon::parse($leave->approved_start_date)->toDateString() &&
                $date <= Carbon::parse($leave->approved_end_date)->toDateString()
            ) {
                return true;
            }
        }

        return false;
    }
}
