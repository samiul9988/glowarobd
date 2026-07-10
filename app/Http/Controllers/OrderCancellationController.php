<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\OrderCancellation;
use Illuminate\Support\Facades\Cache;

class OrderCancellationController extends Controller
{
    public function index(Request $request)
    {
        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        $order_source = $request->source;
        $cancelled_by = $request->cancelled_by;

        if(filled($request->date))
        {
            $dateRange = explode(' to ', $request->date);
            if(count($dateRange) == 2) {
                $startDate = Carbon::parse($dateRange[0])->startOfDay();
                $endDate = Carbon::parse($dateRange[1])->endOfDay();
            }
        }

        $cancellations = OrderCancellation::latest()
            ->with('order:id,code,shipping_address,order_source,created_at', 'cancelledBy:id,name')
            ->when($order_source, function ($query) use ($order_source) {
                $query->whereHas('order', function ($q) use ($order_source) {
                    $q->where('order_source', $order_source);
                });
            })
            ->when($cancelled_by, function ($query) use ($cancelled_by) {
                $query->where('user_type', $cancelled_by);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->paginate(15);

        $user_types = OrderCancellation::select('user_type')
                ->distinct()
                ->pluck('user_type')
                ->filter()
                ->values()
                ->toArray();

        return view('backend.reports.orders.cancellation-report', compact('cancellations', 'order_source', 'user_types', 'cancelled_by'));
    }

    public function getCancellationRatio(Request $request)
    {
        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        if(filled($request->date))
        {
            $dateRange = explode(' to ', $request->date);
            if(count($dateRange) == 2) {
                $startDate = Carbon::parse($dateRange[0])->startOfDay();
                $endDate = Carbon::parse($dateRange[1])->endOfDay();
            }
        }
        $cancelRatioCacheKey = 'cancel_ratio_' . strtotime($startDate) . '_' . strtotime($endDate);
        $cancelRatio = Cache::remember($cancelRatioCacheKey, now()->addHour(1), function() use ($startDate, $endDate) {
            $report = OrderCancellation::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('user_type, COUNT(*) as count')
                ->groupBy('user_type')
                ->pluck('count', 'user_type')
                ->toArray();
            $finalReport = [];
            foreach ($report as $key => $value) {
                $finalReport[ucwords($key)] = $value;
            }
            return $finalReport;
        });

        $cancelRatioByReasonsCacheKey = 'cancel_ratio_reasons_' . strtotime($startDate) . '_' . strtotime($endDate);
        $cancelRatioByReasons = Cache::remember($cancelRatioByReasonsCacheKey, now()->addHour(1), function() use ($startDate, $endDate) {
            $report = OrderCancellation::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('reason_type, COUNT(*) as count')
                ->groupBy('reason_type')
                ->pluck('count', 'reason_type')
                ->toArray();
            $finalReport = [];
            foreach ($report as $key => $value) {
                $finalReport[ucwords(\App\Enums\Reasons::value($key))] = $value;
            }
            return $finalReport;
        });

        return response()->json([
            'success' => true,
            'ratioByUserTypes' => $cancelRatio,
            'ratioByReasons' => $cancelRatioByReasons,
        ]);
    }
}
