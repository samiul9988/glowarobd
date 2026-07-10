<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShowroomReportController extends Controller
{
    public function __invoke(Request $request)
    {
        [$startDate, $endDate] = $this->parseDateRange($request->date);

        $baseQuery = Order::with('payments')
            ->where('order_source', 'showroom')
            ->where('delivery_status', '!=', 'cancelled')
            ->where('delivery_status', '!=', 'returned')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Apply filters
        if ($request->filled('payment_method')) {
            $baseQuery->whereHas('payments', fn($q) => $q->where('payment_method', $request->payment_method));
        }

        if ($request->filled('payment_status') && in_array($request->payment_status, ['paid', 'unpaid', 'partial'])) {
            $baseQuery->where('payment_status', $request->payment_status);
        }

        $orders = $baseQuery->clone()->latest()->paginate(20);

        $summary = $this->calculateSummary($request, $startDate, $endDate, $baseQuery);

        return view('backend.reports.showroom_sales_report', compact('orders', 'summary'));
    }

    private function parseDateRange(?string $date): array
    {
        if (filled($date)) {
            $dateRange = explode(' to ', $date);
            if (count($dateRange) === 2) {
                return [
                    Carbon::parse($dateRange[0])->startOfDay(),
                    Carbon::parse($dateRange[1])->endOfDay(),
                ];
            }
        }

        return [
            Carbon::now()->subDays(7)->startOfDay(),
            Carbon::now()->endOfDay(),
        ];
    }

    private function calculateSummary(Request $request, Carbon $startDate, Carbon $endDate, $baseQuery): array
    {
        $cacheKey = 'showroom_summary_' . md5($startDate . '_' . $endDate . '_' . $request->payment_status);

        return Cache::remember($cacheKey, now()->addHours(3), function () use ($request, $baseQuery) {
            $paymentStatus = $request->payment_status;

            // If filtering by specific status
            if (filled($paymentStatus) && in_array($paymentStatus, ['paid', 'unpaid', 'partial'])) {
                if ($paymentStatus === 'paid' || $paymentStatus === 'partial') {
                    return [
                        'paid_order_amount'   => $this->calculatePaidAmount($baseQuery),
                        'unpaid_order_amount' => 0,
                    ];
                } else {
                    return [
                        'paid_order_amount'   => 0,
                        'unpaid_order_amount' => $baseQuery->clone()->sum('grand_total'),
                    ];
                }
            }

            // Calculate both amounts
            return [
                'paid_order_amount'   => $this->calculatePaidAmount($baseQuery),
                'unpaid_order_amount' => $baseQuery->clone()
                    ->where('payment_status', 'unpaid')
                    ->sum('grand_total'),
            ];
        });
    }

    private function calculatePaidAmount($query): float
    {
        $paidOrderIds = $query->clone()
            ->whereIn('payment_status', ['paid', 'partial'])
            ->pluck('id');

        if ($paidOrderIds->isEmpty()) {
            return 0;
        }

        return Payment::where('reference_type', Order::class)
            ->whereIn('reference_id', $paidOrderIds)
            ->sum('amount');
    }
}
