<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Staff;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\SalarySheet;

class SalaryServiceCopy
{
    public function generateSalarySheet(int $month, int $year, ?int $generatedBy = null): SalarySheet
    {
        DB::beginTransaction();
        try {
            $now = now();
            if ($month >= $now->month && $year >= $now->year) {
                throw new \Exception('Cannot generate salary sheet for current or future month');
            }

            $staffs = DB::table('staff')
                ->join('users', function ($join) {
                    $join->on('users.id', '=', 'staff.user_id')
                        ->where('users.banned', 0);
                })
                ->join('roles','staff.role_id','=','roles.id')
                ->whereNotNull('staff.user_id')
                ->whereNotIn('staff.employment_status', ['terminated', 'resigned'])
                ->orderByDesc('users.recent_login')
                ->select('staff.*', 'roles.name as role_name', 'users.id as user_id', 'users.name as staff_name')
                ->get();

            $staffIds = $staffs->pluck('id')->toArray();

            $attendances = $this->getAttendances($month, $year, $staffIds);

            $sheet = $staffs->map(function ($staff) use ($attendances) {
                $attendanceRows = $attendances->get($staff->id, collect());

                $summary = [
                    'present' => $attendanceRows->where('status', 'present')->count(),
                    'absent' => $attendanceRows->where('status', 'absent')->count(),
                    'leave_days' => $attendanceRows->where('status', 'leave')->count(),
                    'late_count' => $attendanceRows->where('late_minutes', '>', 0)->count(),
                    'working_days' => $attendanceRows->whereIn('status', ['present', 'absent', 'leave'])->count(),
                    'work_minutes' => (int) $attendanceRows->sum('work_minutes'),
                    'overtime_minutes' => (int) $attendanceRows->sum('overtime_minutes'),
                    'late_minutes' => (int) $attendanceRows->sum('late_minutes'),
                    'early_leave_minutes' => (int) $attendanceRows->sum('early_leave_minutes'),
                ];

                $summary['work_hours'] = round($summary['work_minutes'] / 60, 2);

                $profileSalary = (float) ($staff->salary ?? 0);
                $profileWorkingHoursPerDay = (float) ($staff->working_hours ?? 0);

                $expectedMonthlyHours = $summary['working_days'] > 0 && $profileWorkingHoursPerDay > 0
                    ? $summary['working_days'] * $profileWorkingHoursPerDay
                    : 0;

                $hourlyRate = $expectedMonthlyHours > 0
                    ? $profileSalary / $expectedMonthlyHours
                    : 0;

                $basicSalary = $hourlyRate > 0
                    ? $summary['work_hours'] * $hourlyRate
                    : 0;

                return $this->buildSalaryRow($staff, $summary, $basicSalary);
            })->values();

            $salarySheet = $this->assertSalarySheet($sheet, $month, $year, $generatedBy);
            DB::commit();
            return $salarySheet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function assertSalarySheet(Collection $sheets, int $month, int $year, ?int $generatedBy = null): SalarySheet
    {
        $salarySheet = SalarySheet::firstOrCreate(
            [
                'month' => $month,
                'year' => $year,
            ],
            [
                'generated_by' => $generatedBy ?? null,
            ]
        );

        foreach ($sheets as $row) {
            $salarySheet->details()->updateOrCreate(
                [
                    'staff_id' => $row['staff_id'],
                ],
                [
                    'attendance_summary' => [
                        'working_days' => $row['attendance']['working_days'],
                        'present' => $row['attendance']['present'],
                        'absent' => $row['attendance']['absent'],
                        'leave_days' => $row['attendance']['leave_days'],
                        'late_count' => $row['attendance']['late_count'],
                    ],
                    'working_summary' => [
                        'work_hours' => $row['attendance']['work_hours'],
                        'work_minutes' => $row['attendance']['work_minutes'],
                        'overtime_minutes' => $row['attendance']['overtime_minutes'],
                        'late_minutes' => $row['attendance']['late_minutes'],
                        'early_leave_minutes' => $row['attendance']['early_leave_minutes'],
                    ],
                    'bonuses' => $row['bonuses'] ?? [],
                    'profile_salary' => $row['profile_salary'],
                    'working_hours_per_day' => $row['working_hours_per_day'],
                    'basic_salary' => $row['basic_salary'],
                    'overtime_amount' => $row['overtime_amount'],
                    'late_fee_amount' => $row['late_fee_amount'],
                    'bonus_total' => $row['bonus_total'],
                    'gross_salary' => $row['gross_salary'],
                    'net_salary' => $row['net_salary'],
                ]
            );
        }

        return $salarySheet;
    }

    public function updateSalarySheet(int $month, int $year, array $staffUpdates): Collection
    {
        $adjustments = $this->getSalaryAdjustments($month, $year);

        foreach ($staffUpdates as $staffId => $update) {
            if (! is_array($update)) {
                continue;
            }

            $current = $adjustments[$staffId] ?? [
                'overtime_amount' => 0,
                'late_fee_amount' => 0,
                'bonuses' => [],
            ];

            $bonuses = $update['bonuses'] ?? $current['bonuses'];

            $adjustments[$staffId] = [
                'overtime_amount' => round((float) ($update['overtime_amount'] ?? $current['overtime_amount'] ?? 0), 2),
                'late_fee_amount' => round((float) ($update['late_fee_amount'] ?? $current['late_fee_amount'] ?? 0), 2),
                'bonuses' => $this->normalizeBonuses(is_array($bonuses) ? $bonuses : []),
            ];
        }

        $this->setSalaryAdjustments($month, $year, $adjustments);

        return $this->generateSalarySheet($month, $year);
    }

    private function buildSalaryRow($staff, array $summary, float $basicSalary, ?SalarySheet $salarySheet): array
    {
        $salarySheetDetails = $salarySheet->details()->where('staff_id', $staff->id)->first();
        $overtimeAmount = round((float) ($salarySheetDetails->overtime_amount ?? 0), 2);
        $lateFeeAmount = round((float) ($salarySheetDetails->late_fee_amount ?? 0), 2);
        $bonuses = $salarySheetDetails->bonuses ?? [];

        $bonusTotal = collect($bonuses)->sum('amount');
        $grossSalary = round($basicSalary + $overtimeAmount + $bonusTotal, 2);
        $netSalary = round(max(0, $grossSalary - $lateFeeAmount), 2);

        return [
            'staff_id' => $staff->id,
            'user_id' => $staff->user_id,
            'employee_id' => $staff->employee_id,
            'staff_name' => $staff->staff_name,
            'role' => $staff->role_name,
            'attendance' => $summary,
            'profile_salary' => round((float) ($staff->salary ?? 0)),
            'working_hours_per_day' => (float) ($staff->working_hours ?? 0),
            'basic_salary' => round($basicSalary),
            'overtime_amount' => round($overtimeAmount),
            'late_fee_amount' => round($lateFeeAmount),
            'bonuses' => $bonuses,
            'bonus_total' => round($bonusTotal),
            'gross_salary' => round($grossSalary),
            'net_salary' => round($netSalary),
        ];
    }

    private function normalizeBonuses(array $bonuses, float $baseSalary = 0): array
    {
        return collect($bonuses)
            ->filter(function ($bonus) {
                return is_array($bonus);
            })
            ->map(function (array $bonus) use ($baseSalary) {
                $type = strtolower((string) ($bonus['type'] ?? 'fixed'));
                $amount = (float) ($bonus['amount'] ?? 0);
                $hasExplicitBase = array_key_exists('base_amount', $bonus) && $bonus['base_amount'] !== null;
                $resolvedBaseAmount = $hasExplicitBase
                    ? (float) $bonus['base_amount']
                    : $baseSalary;
                $calculatedAmount = $type === 'percentage'
                    ? round(($resolvedBaseAmount * $amount) / 100, 2)
                    : round($amount, 2);

                return [
                    'title' => (string) ($bonus['title'] ?? 'Bonus'),
                    'type' => in_array($type, ['percentage', 'fixed'], true) ? $type : 'fixed',
                    'amount' => round($amount, 2),
                    'base_amount' => $hasExplicitBase ? round($resolvedBaseAmount, 2) : null,
                    'calculated_amount' => $calculatedAmount,
                ];
            })
            ->values()
            ->all();
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

    public function getSalarySheet(int $month, int $year): ?SalarySheet
    {
        $salarySheet = SalarySheet::where('month', $month)
            ->where('year', $year)
            ->first();

        return $salarySheet;
    }

    private function salaryAdjustmentsCacheKey(int $month, int $year): string
    {
        return "salary-sheet-adjustments:{$year}:{$month}";
    }

    private function getSalaryAdjustments(int $month, int $year): array
    {
        $key = $this->salaryAdjustmentsCacheKey($month, $year);

        return Cache::get($key, []);
    }

    private function setSalaryAdjustments(int $month, int $year, array $adjustments): void
    {
        $key = $this->salaryAdjustmentsCacheKey($month, $year);

        Cache::put($key, $adjustments, now()->addMonths(6));
    }
}
