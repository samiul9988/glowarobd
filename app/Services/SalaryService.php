<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\LeaveLog;
use App\Models\SalarySheet;
use App\Models\SalarySheetDetails;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryService
{
    public function getSalarySheet(int $month, int $year, ?int $staffId = null): ?array
    {
        $cacheKey = $this->salarySheetCacheKey($month, $year, $staffId);

        $salarySheet = Cache::rememberForever($cacheKey, function () use ($month, $year, $staffId) {
            $query = SalarySheet::query()
                ->where('month', $month)
                ->where('year', $year)
                ->with([
                    'details' => function ($q) use ($staffId) {
                        if ($staffId) {
                            $q->where('staff_id', $staffId);
                        }

                        $q->with([
                            'staff:id,user_id,role_id,employee_id,salary,working_hours,joining_date,bank_account',
                            'staff.user:id,name',
                            'staff.role:id,name',
                        ]);
                    }
                ]);

            return $query->first();
        });

        if (!$salarySheet || $salarySheet->details->isEmpty()) {
            return null;
        }

        return $this->transformSheet($salarySheet);
    }

    public function generateSalarySheet(int $month, int $year, ?int $generatedBy = null): SalarySheet
    {
        DB::beginTransaction();
        try {
            $now = now();
            if ($month >= $now->month && $year >= $now->year) {
                throw new \Exception('Cannot generate salary sheet for current or future month');
            }

            $date = Carbon::createFromDate($year, $month, 1);

            $staffs = DB::table('staff')
                ->join('users', function ($join) {
                    $join->on('users.id', '=', 'staff.user_id')
                        ->where('users.banned', 0);
                })
                ->join('roles','staff.role_id','=','roles.id')
                ->whereNotNull('staff.user_id')
                ->whereNotNull('staff.salary')
                ->where('staff.salary', '>', 0)
                ->whereNotIn('staff.employment_status', ['terminated', 'resigned'])
                ->orderByDesc('users.recent_login')
                ->select('staff.*', 'roles.name as role_name', 'users.id as user_id', 'users.name as staff_name')
                ->get();

            $staffIds = $staffs->pluck('id')->toArray();
            $userIds = $staffs->pluck('user_id')->toArray();

            $attendances = $this->getAttendances($month, $year, $staffIds);
            $leaves = $this->getLeaves($month, $year, $userIds);

            $existingSheet = SalarySheet::with('details')->where('month', $month)->where('year', $year)->first();

            $sheet = $staffs->map(function ($staff) use ($attendances, $existingSheet, $leaves, $date) {
                $existingRow = $existingSheet?->details?->firstWhere('staff_id', $staff->id) ?? null;
                $attendanceRows = $attendances->get($staff->id, collect());
                $leaveRows = $leaves->get($staff->user_id, collect());
                $totalLeave = $attendanceRows->where('status', 'leave')->count();
                $paidLeave = $leaveRows->where('is_paid', true)->count();
                $unpaidLeave = $totalLeave - $paidLeave;

                $summary = [
                    'staff_id' => $staff->id,
                    'attendance_summary' => [
                        'present' => $attendanceRows->where('status', 'present')->count(),
                        'absent' => $attendanceRows->where('status', 'absent')->count(),
                        'leave_days' => $attendanceRows->where('status', 'leave')->count(),
                        'late_count' => $attendanceRows->where('late_minutes', '>', 0)->count(),
                        'working_days' => $attendanceRows->whereIn('status', ['present', 'absent', 'leave'])->count(),
                    ],
                    'working_summary' => [
                        'work_minutes' => (int) $attendanceRows->where('status', 'present')->sum('work_minutes'),
                        'overtime_minutes' => (int) $attendanceRows->sum('overtime_minutes'),
                        'late_minutes' => (int) $attendanceRows->where('status', 'present')->sum('late_minutes'),
                        'early_leave_minutes' => (int) $attendanceRows->where('status', 'present')->sum('early_leave_minutes'),
                        'expected_work_hours' => 0, // to be calculated later
                        'work_hours' => 0, // to be calculated later
                        'per_day_salary' => 0, // to be calculated later
                    ],
                    'leave_summary' => [
                        'total_leave' => $totalLeave,
                        'paid_leave' => $paidLeave,
                        'unpaid_leave' => $unpaidLeave,
                    ],
                    'bonuses' => [],
                    'bonus_total' => 0,
                    'leave_amount' => 0, // to be calculated later based on unpaid leaves
                ];

                if ($existingRow) {
                    $profileSalary = (float) ($existingRow->profile_salary ?? 0);
                    $profileWorkingHoursPerDay = (float) ($existingRow->working_hours_per_day ?? 0);
                    $summary['bonuses'] = $existingRow->bonuses ?? [];
                    $summary['bonus_total'] = collect($summary['bonuses'])->sum('amount');
                } else {
                    $profileSalary = (float) ($staff->salary ?? 0);
                    $profileWorkingHoursPerDay = (float) ($staff->working_hours ?? 0);
                }

                $expectedMonthlyHours = 0;
                if (data_get($summary, 'attendance_summary.working_days', 0) > 0 && $profileWorkingHoursPerDay > 0) {
                    $expectedMonthlyHours = round(data_get($summary, 'attendance_summary.working_days', 0) * $profileWorkingHoursPerDay, 2);
                }

                $summary['working_summary']['expected_work_hours'] = $expectedMonthlyHours;
                $summary['working_summary']['work_hours'] = round(data_get($summary, 'working_summary.work_minutes', 0) / 60, 2);

                $dailySalary = round($profileSalary / $date->daysInMonth);
                $summary['working_summary']['per_day_salary'] = $dailySalary;
                // $basicSalary = $dailySalary * ($summary['attendance_summary']['present'] + $summary['leave_summary']['paid_leave']);

                $basicSalary = $profileSalary - ($dailySalary * ($unpaidLeave + data_get($summary, 'attendance_summary.absent', 0)));

                $summary['profile_salary'] = round($profileSalary);
                $summary['working_hours_per_day'] = round($profileWorkingHoursPerDay);
                $summary['basic_salary'] = round($basicSalary);
                // $summary['leave_amount'] = round($unpaidLeave * $profileWorkingHoursPerDay * $hourlyRate);
                $summary['leave_amount'] = ($existingRow->leave_amount ?? 0) > 0 ? $existingRow->leave_amount : (round($unpaidLeave * $dailySalary));

                if (!$existingRow || $existingRow->gross_salary == 0 || $existingRow->net_salary == 0) {
                    $summary['gross_salary'] = round($basicSalary + $summary['bonus_total']);
                    $summary['net_salary'] = round($summary['gross_salary'] - $summary['leave_amount']);
                }

                return $summary;
            })->values()->toArray();

            // Remove those staffs from existing sheet who are no longer eligible for salary sheet generation
            if ($existingSheet) {
                $existingSheet->details()->whereNotIn('staff_id', $staffIds)->delete();
            }

            $salarySheet = $this->assertSalarySheet($sheet, $month, $year, $generatedBy);
            DB::commit();

            Cache::forget($this->salarySheetCacheKey($month, $year));
            return $salarySheet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateSalarySheet(int $salarySheetId, array $staffUpdates): ?array
    {
        $salarySheet = SalarySheet::with('details')->find($salarySheetId);

        if (!$salarySheet) {
            return null;
        }

        DB::beginTransaction();
        try {
            foreach ($salarySheet->details as $details) {
                $adjustments = $staffUpdates[$details->staff_id] ?? null;
                if (is_null($adjustments)) {
                    continue;
                }

                $overTimeAmount = round($adjustments['overtime_amount'] ?? $details->overtime_amount);
                $lateFeeAmount = round($adjustments['late_fee_amount'] ?? $details->late_fee_amount);
                $leaveAmount = round($adjustments['leave_amount'] ?? $details->leave_amount);
                $adjustmentAmount = round($adjustments['adjustment_amount'] ?? $details->adjustment_amount);
                $bonuses = $adjustments['bonuses'] ?? $details->bonuses ?? [];
                $bonusTotal = collect($bonuses)->sum('amount');
                $grossSalary = round($details->basic_salary + $overTimeAmount + $bonusTotal);
                $netSalary = round(max(0, $grossSalary - $lateFeeAmount - $leaveAmount + $adjustmentAmount));

                $details->update([
                    'overtime_amount' => $overTimeAmount,
                    'late_fee_amount' => $lateFeeAmount,
                    'leave_amount' => $leaveAmount,
                    'adjustment_amount' => $adjustmentAmount,
                    'bonuses' => $bonuses,
                    'bonus_total' => $bonusTotal,
                    'gross_salary' => $grossSalary,
                    'net_salary' => $netSalary,
                ]);
            }

            $salarySheet->touch(); // Update the updated_at timestamp
            DB::commit();

            // Clear cache after update
            Cache::forget($this->salarySheetCacheKey($salarySheet->month, $salarySheet->year));

            return $this->getSalarySheet($salarySheet->month, $salarySheet->year);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update salary sheet ID {$salarySheetId}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function transformSheet(SalarySheet $salarySheet): array
    {
        $details = [];
        if ($salarySheet->details && $salarySheet->details->isNotEmpty()) {
            $details = $salarySheet->details->map(function (SalarySheetDetails $detail) {
                return $this->transformDetail($detail);
            })->toArray();
        }

        return [
            'id' => $salarySheet->id,
            'month' => $salarySheet->month,
            'year' => $salarySheet->year,
            'generated_by' => $salarySheet->generatedBy?->name ?? 'System',
            'generated_at' => $salarySheet->generated_at?->toDateTimeString(),
            'updated_at' => $salarySheet->updated_at->toDateTimeString(),
            'details' => $details,
        ];
    }

    protected function transformDetail(SalarySheetDetails $detail): array
    {
        return [
            'staff_id' => $detail->staff_id,
            'employee_id' => $detail->staff?->employee_id ?? '--',
            'joining_date' => $detail->staff?->joining_date?->format('d-m-Y') ?? '',
            'name' => $detail->staff?->user?->name ?? '--',
            'role' => $detail->staff?->role?->name ?? '--',
            'salary' => round((float) $detail->profile_salary ?? 0),
            'bank_account' => $detail->staff?->bank_account ?? [],
            'working_hours' => (int) $detail->staff?->working_hours ?? 0,
            'attendance_summary' => $detail->attendance_summary ?? [],
            'working_summary' => $detail->working_summary ?? [],
            'leave_summary' => $detail->leave_summary ?? [],
            'basic_salary' => round((float) $detail->basic_salary ?? 0),
            'overtime_amount' => round((float) $detail->overtime_amount ?? 0),
            'late_fee_amount' => round((float) $detail->late_fee_amount ?? 0),
            'leave_amount' => round((float) $detail->leave_amount ?? 0),
            'adjustment_amount' => round((float) $detail->adjustment_amount ?? 0),
            'bonuses' => $detail->bonuses ?? [],
            'bonus_total' => round((float) $detail->bonus_total ?? 0),
            'net_salary' => round((float) $detail->net_salary ?? 0),
            'gross_salary' => round((float) $detail->gross_salary ?? 0),
        ];
    }

    private function assertSalarySheet(array $sheets, int $month, int $year, ?int $generatedBy = null): SalarySheet
    {
        $salarySheet = SalarySheet::where('month', $month)->where('year', $year)->first();
        if ($salarySheet === null) {
            $salarySheet = SalarySheet::create([
                'month' => $month,
                'year' => $year,
            ]);
        }

        $salarySheetPayload = ['generated_at' => now()];
        if ($salarySheet->generated_by != $generatedBy) {
            $salarySheetPayload['generated_by'] = $generatedBy;
        }

        $salarySheet->update($salarySheetPayload);

        foreach ($sheets as $row) {
            $staffId = $row['staff_id'];
            unset($row['staff_id']);
            $salarySheet->details()->updateOrCreate(
                [
                    'staff_id' => $staffId,
                ],
                $row
            );
        }

        return $salarySheet;
    }

    private function getAttendances(int $month, int $year, array $staffIds): Collection
    {
        $attendances = Attendance::query()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereIn('staff_id', $staffIds)
            ->get()
            ->groupBy('staff_id');

        return $attendances;
    }

    private function getLeaves(int $month, int $year, array $userIds): Collection
    {
        $leaves = LeaveLog::query()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        return $leaves;
    }

    private function salarySheetCacheKey(int $month, int $year, ?int $staffId = null): string
    {
        $key = "salary-sheet:{$year}:{$month}";
        if ($staffId !== null) {
            $key .= ":{$staffId}";
        }
        return $key;
    }
}
