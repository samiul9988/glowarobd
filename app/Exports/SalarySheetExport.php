<?php

namespace App\Exports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class SalarySheetExport implements FromCollection, WithHeadings, WithStrictNullComparison, ShouldQueue, WithChunkReading
{
    protected $sheet;

    public function __construct(array $sheet)
    {
        $this->sheet = $sheet;
    }

    public function collection()
    {
        return collect($this->sheet['details'] ?? [])->map(function ($detail) {
            return $this->map($detail);
        });
    }

    public function map(array $detail): array
    {
        $remarks = '';
        $deductionAmount = $additionAmount = 0;
        if (data_get($detail, 'adjustment_amount', 0) > 0) {
            $additionAmount += data_get($detail, 'adjustment_amount', 0);
        } else {
            $deductionAmount += abs(data_get($detail, 'adjustment_amount', 0));
        }
        $deductionAmount += (data_get($detail, 'late_fee_amount', 0) + data_get($detail, 'leave_amount', 0));
        $totalBonus = collect($detail['bonuses'] ?? [])->sum('amount');
        $additionAmount += data_get($detail, 'overtime_amount', 0) + $totalBonus;

        if ($deductionAmount > 0) {
            $remarks .= '# Deductions' . PHP_EOL;
            $remarks .= '- Late Fee: ' . round(data_get($detail, 'late_fee_amount', 0), 2) . PHP_EOL;
            $remarks .= '- Leave: ' . round(data_get($detail, 'leave_amount', 0), 2) . PHP_EOL;
            if (data_get($detail, 'adjustment_amount', 0) < 0) {
                $remarks .= '- Adjustment: ' . abs(round(data_get($detail, 'adjustment_amount', 0), 2)) . PHP_EOL;
            }
        }

        if ($additionAmount > 0) {
            $remarks .= '# Additions' . PHP_EOL;
            $remarks .= '- Overtime: ' . round(data_get($detail, 'overtime_amount', 0), 2) . PHP_EOL;
            $remarks .= '- Bonus: ' . round($totalBonus, 2) . PHP_EOL;
            if (data_get($detail, 'adjustment_amount', 0) > 0) {
                $remarks .= '- Adjustment: ' . abs(round(data_get($detail, 'adjustment_amount', 0), 2)) . PHP_EOL;
            }
        }

        $remarks .= '# Bank Info' . PHP_EOL;
        $remarks .= '- Bank Name: ' . (data_get($detail, 'bank_account.bank_name') ?? '--') . PHP_EOL;
        $remarks .= '- Branch: ' . (data_get($detail, 'bank_account.branch') ?? '--') . PHP_EOL;
        return [
            data_get($detail, 'employee_id', null),
            data_get($detail, 'name', null),
            data_get($detail, 'role', null),
            data_get($detail, 'joining_date', null),
            data_get($detail, 'bank_account.account_no', null),
            round(data_get($detail, 'gross_salary', 0), 2),
            round(data_get($detail, 'overtime_amount', 0), 2),
            round(data_get($detail, 'adjustment_amount', 0), 2),
            round($deductionAmount, 2),
            round(data_get($detail, 'net_salary', 0), 2),
            $remarks,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Designation',
            'Joining Date',
            'Account Number',
            'Gross Salary',
            'Overtime',
            'Adjustment',
            'Deduction',
            'Payable',
            'Remarks',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
