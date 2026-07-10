<?php

namespace App\Http\Controllers;

use App\Exports\SalarySheetExport;
use App\Jobs\GenerateSalarySheet;
use App\Models\Attendance;
use App\Services\SalaryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class SalarySheetController extends Controller
{
    public function __construct(private SalaryService $salaryService) {}

    public function index(Request $request)
    {
        $date = $request->month
            ? Carbon::createFromFormat('Y-m', $request->month)
            : now()->subMonth(); // Default to previous month

        $sheet = $this->salaryService->getSalarySheet($date->month, $date->year);

        $summary = $this->buildSummary(collect($sheet['details'] ?? []));

        if ($request->has('export') && $request->export == '1') {
            $fileName = "salary_sheet_{$date->format('Y_m')}.xlsx";
            return Excel::download(new SalarySheetExport($sheet), $fileName);
        }

        $previousMonth = now()->subMonth()->startOfMonth();

        $editable = !$date->isSameMonth($previousMonth) ? false : true;

        return view('backend.salary_sheets.index', compact('sheet', 'summary', 'editable'));
    }

    public function getByMonth(Request $request, $staffId): JsonResponse
    {
        $date = $request->month
            ? Carbon::createFromFormat('Y-m', $request->month)
            : now()->subMonth(); // Default to previous month

        $sheet = $this->salaryService->getSalarySheet($date->month, $date->year, $staffId);

        return response()->json([
            'success' => true,
            'html' => view('backend.salary_sheets.partials.table', ['sheet' => $sheet, 'editable' => false, 'compact' => true])->render(),
        ]);
    }

    public function generate(Request $request): JsonResponse
    {
        try {
            $date = $request->month
            ? Carbon::createFromFormat('Y-m', $request->month)
            : now();

            $previousMonth = now()->subMonth()->startOfMonth();

            if (!$date->isSameMonth($previousMonth)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only ' . $previousMonth->format('F Y') . ' month salary sheet can be edited.',
                ], 400);
            }

            if (Attendance::whereMonth('date', $date->month)->whereYear('date', $date->year)->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No attendance records found for the selected month. Salary sheet cannot be generated.',
                ], 400);
            }

            dispatch(new GenerateSalarySheet($date->year, $date->month, auth()->id()));
            return response()->json([
                'success' => true,
                'message' => 'Salary sheet generation has been queued. Please reload the page after a few moments.',
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to dispatch salary sheet generation job: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to queue salary sheet generation. Please try again.',
            ], 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'id' => 'required|integer|exists:salary_sheets,id',
            'adjustments' => 'required|array',
            'adjustments.*.overtime_amount' => 'nullable|numeric|min:0',
            'adjustments.*.late_fee_amount' => 'nullable|numeric|min:0',
            'adjustments.*.leave_amount' => 'nullable|numeric|min:0',
            'adjustments.*.adjustment_amount' => 'nullable|numeric',
            'adjustments.*.bonuses' => 'nullable|array',
            'adjustments.*.bonuses.*.title' => 'nullable|string|max:100',
            'adjustments.*.bonuses.*.amount' => 'nullable|numeric|min:0',
        ], [
            'id.required' => 'No salary sheet specified.',
            'id.exists' => 'Salary sheet not found.',
            'adjustments.required' => 'No adjustments provided.',
        ]);

        try {
            $sheet = $this->salaryService->updateSalarySheet(
                (int) $payload['id'],
                $payload['adjustments']
            );

            if (is_null($sheet)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salary sheet not found.',
                ], 404);
            }

            $summary = $this->buildSummary(collect($sheet['details'] ?? []));

            $month = $sheet['month'];
            $year = $sheet['year'];
            $date = Carbon::createFromDate($year, $month, 1);

            $previousMonth = now()->subMonth()->startOfMonth();
            $editable = !$date->isSameMonth($previousMonth) ? false : true;
            return response()->json([
                'success' => true,
                'message' => 'Salary sheet updated successfully.',
                'html' => view('backend.salary_sheets.partials.table', ['sheet' => $sheet, 'editable' => $editable])->render(),
                'summary_html' => view('backend.salary_sheets.partials.summary', ['summary' => $summary])->render(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update salary sheet: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Request failed. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function buildSummary(Collection $rows): array
    {
        return [
            'staff_count' => $rows->count(),
            'basic_salary_total' => round((float) $rows->sum('basic_salary')),
            'overtime_total' => round((float) $rows->sum('overtime_amount')),
            'late_fee_total' => round((float) $rows->sum('late_fee_amount')),
            'leave_amount_total' => round((float) $rows->sum('leave_amount')),
            'bonus_total' => round((float) $rows->sum('bonus_total')),
            'net_salary_total' => round((float) $rows->sum('net_salary')),
        ];
    }
}
