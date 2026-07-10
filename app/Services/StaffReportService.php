<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Staff;
use App\Models\Ticket;
use App\Models\CallLog;
use App\Models\Product;
use App\Models\OrderLog;
use App\Models\TicketLog;
use Illuminate\Http\Request;
use App\Models\OrderFeedback;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class StaffReportService
{
    protected $staffs;
    public function __construct()
    {
        $this->staffs = Staff::with(['user', 'role'])->get();
    }

    public static function customerCareReport(Request $request, $userId = null)
    {
        $filter_date = $request->filter_date;
        $start_date = now()->subDays(7)->startOfDay();
        $end_date = now()->endOfDay();

        if (filled($filter_date)) {
            $dates = explode(' to ', $filter_date);
            if(count($dates) === 2){
                $start_date = Carbon::parse($dates[0])->startOfDay();
                $end_date = Carbon::parse($dates[1])->endOfDay();
            }
        }

        $daysDifference = $start_date->diffInDays($end_date);
        if (is_null($userId)) {
            $userId = Auth::user()->id;
            $user = Auth::user();
        }else{
            $user = User::find($userId);
        }

        $cache_key = 'customer_care_report_' . $start_date->format('Ymd') . '_' . $end_date->format('Ymd') . '_' . $userId;
        Cache::forget($cache_key);
        $report = Cache::remember($cache_key, now()->addHour(), function () use ($userId, $user, $start_date, $end_date, $daysDifference) {
            $callLogs = CallLog::forOrders()
                ->where('called_by', $userId)
                ->where('duration', '>', 0)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->get();
            $orderLogs = OrderLog::where('managed_by', $userId)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->get();
            $ticketLogs = TicketLog::where('user_id', $userId)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->get();

            if ($daysDifference > 30) {
                // Group by month
                // $periodicCallLogs = CallLog::forOrders()->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as total")
                $periodicCallLogs = CallLog::forOrders()
                    ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(DISTINCT reference_id) as total")
                    ->where('called_by', $userId)
                    ->where('duration', '>', 0)
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            } else {
                // Group by day
                // $periodicCallLogs = CallLog::forOrders()->selectRaw('DATE(created_at) as period, COUNT(*) as total')
                $periodicCallLogs = CallLog::forOrders()
                    ->selectRaw('DATE(created_at) as period, COUNT(DISTINCT reference_id) as total')
                    ->where('called_by', $userId)
                    ->where('duration', '>', 0)
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            }

            $tickets = Ticket::where('status', '!=', 'closed')
                ->whereIn('issue', ['authenticity-issue', 'skincare-suggestion'])
                ->get();
            return [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->staff?->role?->getTranslation('name') ?? '',
                'pending_orders_count' => Order::where('delivery_status', 'pending')->where('order_type', '!=', 'merchant')->count(),
                'processing_orders_count' => Order::where('delivery_status', 'processing')->count(),
                'call_count' => $callLogs->pluck('reference_id')->unique()->count(),
                'create_count' => $orderLogs->where('action', 'created')->pluck('order_id')->unique()->count(),
                'update_count' => $orderLogs->whereIn('action', ['updated', 'cancelled', 'delivery_status', 'payment_status'])->pluck('order_id')->unique()->count(),
                'ticket_count' => $ticketLogs->pluck('ticket_id')->unique()->count(),
                'callLogs' => $periodicCallLogs,
                'authenticity_issue_count' => $tickets->where('issue', 'authenticity-issue')->count(),
                'skincare_suggestion_count' => $tickets->where('issue', 'skincare-suggestion')->count(),
                'feedback_count' => OrderFeedback::where('created_by', $userId)
                    ->whereBetween('created_at', [$start_date, $end_date])->count(),
            ];
        });

        return $report;
    }

    public static function packagingReport(Request $request, $userId = null)
    {
        $filter_date = $request->filter_date;
        // $start_date = now()->startOfDay();
        $start_date = now()->subDays(7)->startOfDay();
        $end_date = now()->endOfDay();

        if (filled($filter_date)) {
            $dates = explode(' to ', $filter_date);
            if(count($dates) === 2){
                $start_date = Carbon::parse($dates[0])->startOfDay();
                $end_date = Carbon::parse($dates[1])->endOfDay();
            }
        }

        $daysDifference = $start_date->diffInDays($end_date);

        if (is_null($userId)) {
            $userId = Auth::user()->id;
            $user = Auth::user();
        }else{
            $user = User::find($userId);
        }

        $cache_key = 'packaging_report_' . $start_date->format('Ymd') . '_' . $end_date->format('Ymd') . '_' . $userId;
        Cache::forget($cache_key);
        $report = Cache::remember($cache_key, now()->addHour(), function () use ($userId, $user, $start_date, $end_date, $daysDifference) {
            $packagedOrderLogs = OrderLog::where('managed_by', $userId)
                ->where('action', 'packaged')
                ->whereBetween('created_at', [$start_date, $end_date])
                ->pluck('order_id')->unique();

            $hold = CallLog::forOrders()
                ->where('called_by', $userId)
                ->where('status', ['shipment_failed'])
                ->whereBetween('created_at', [$start_date, $end_date])
                ->whereIn('reference_id', $packagedOrderLogs)
                ->pluck('reference_id as order_id')->unique()->toArray();

            $completed = $packagedOrderLogs->count() - count($hold);

            if ($daysDifference > 30) {
                // Group by month
                $periodicPackagingLogs = OrderLog::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(DISTINCT reference_id) as total")
                    ->where('managed_by', $userId)
                    ->where('action', 'packaged')
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            } else {
                // Group by day
                $periodicPackagingLogs = OrderLog::selectRaw('DATE(created_at) as period, COUNT(DISTINCT order_id) as total')
                    ->where('managed_by', $userId)
                    ->where('action', 'packaged')
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            }

            return [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->staff?->role?->getTranslation('name') ?? '',
                'pending_packages_count' => Order::where('delivery_status', 'packaging')->count(),
                'package_count' => $packagedOrderLogs->count(),
                'hold_count' => count($hold),
                'completed_count' => $completed,
                'packagingLogs' => $periodicPackagingLogs,
            ];
        });

        // dd($report);
        return $report;
    }

    public static function accountInventoryReport(Request $request, $userId = null)
    {
        $filter_date = $request->filter_date;
        $start_date = now()->subDays(7)->format('Y-m-d 00:00:00');
        $end_date = now()->format('Y-m-d 23:59:59');

        if (filled($filter_date)) {
            $start_date = Carbon::parse(explode(' to ', $filter_date)[0])->startOfDay()->format('Y-m-d H:i:s');
            $end_date = Carbon::parse(explode(' to ', $filter_date)[1])->endOfDay()->format('Y-m-d H:i:s');
        }

        if (is_null($userId)) {
            $userId = Auth::user()->id;
            $user = Auth::user();
        }else{
            $user = User::find($userId);
        }

        $cache_key = 'account_inventory_report_' . $start_date . '_' . $end_date. '_' . $userId;
        Cache::forget($cache_key);
        $report = Cache::remember($cache_key, now()->addHour(), function () use ($userId, $user, $start_date, $end_date) {
            $confirmedOrdersCount = Order::where('delivery_status', 'confirmed')
                ->count();
            $cancelledOrdersCount = Order::where('delivery_status', 'cancelled')
                ->count();
            $returnedOrdersCount = Order::where('delivery_status', 'returned')
                ->count();
            $deliveredOrdersCount = Order::where('delivery_status', 'delivered')
                ->count();
            $publishedProductsCount = Product::where('published', 1)
                ->count();
            $unpublishedProductsCount = Product::where('published', 0)
                ->count();
            $outOfStockProductsCount = Product::with('stocks')->where('published', 1)
                ->whereHas('stocks', function ($q) {
                    $q->where('qty', '<=', 0);
                })->count();
            return [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->staff?->role?->getTranslation('name') ?? '',
                'confirmed_orders_count' => $confirmedOrdersCount,
                'cancelled_orders_count' => $cancelledOrdersCount,
                'returned_orders_count' => $returnedOrdersCount,
                'delivered_orders_count' => $deliveredOrdersCount,
                'published_products_count' => $publishedProductsCount,
                'unpublished_products_count' => $unpublishedProductsCount,
                'out_of_stock_products_count' => $outOfStockProductsCount,
            ];
        });

        // dd($report);
        return $report;
    }
}
