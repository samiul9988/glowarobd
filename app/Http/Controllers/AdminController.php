<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\CallLog;
use App\Models\OrderLog;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StaffReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;


class AdminController extends Controller
{
    private $DASHBOARDS = [
        'Admin Dashboard' => 'admin_dashboard',
        'Customer Care Dashboard' => 'customer_care_dashboard',
        'Packaging Dashboard' => 'packaging_dashboard',
        'Account & Inventory Dashboard' => 'account_inventory_dashboard'
    ];

    public function index(Request $request)
    {
        $view = $request->view ?? null;
        $permissions = json_decode(Auth::user()->staff?->role?->permissions ?? '[]', true) ?? [];
        if(Auth::user()->user_type == 'admin'){
            $dashboards = $this->DASHBOARDS;
        } else{
            $dashboards = array_intersect($this->DASHBOARDS, $permissions);
        }
        $request->merge([
            'dashboards' => $dashboards,
        ]);
        if(!is_null($view) && (Auth::user()->user_type == 'admin' || in_array($view, $permissions))){
            $request->merge([
                'view' => $view,
            ]);
            return $this->$view($request);
        }
        if(Auth::user()->user_type == 'admin' || in_array('admin_dashboard', $permissions)){
            return $this->admin_dashboard($request);
        }
        elseif(in_array('customer_care_dashboard', $permissions)){
            return $this->customer_care_dashboard($request);
        }
        elseif(in_array('packaging_dashboard', $permissions)){
            return $this->packaging_dashboard($request);
        }
        elseif(in_array('account_inventory_dashboard', $permissions)){
            return $this->account_inventory_dashboard($request);
        }
        else{
            return view('backend.dashboard.global_dashboard');
        }
    }

    public function orderPaymentsChart(Request $request)
    {
        if(filled($request->date)){
            $filter_date = $request->date;
            $from = date('Y-m-d 00:00:00', strtotime(explode(' to ', $filter_date)[0]));
            $to = date('Y-m-d 23:59:59', strtotime(explode(' to ', $filter_date)[1]));
        } else{
            $from = date('Y-m-d 00:00:00', strtotime('-6 Days'));
            $to = date('Y-m-d 23:59:59');
        }

        $orders = Order::where('delivery_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$from, $to])
            ->get();
        $paid_orders = $orders->where('payment_status', 'paid')->count();
        $due_orders = $orders->where('payment_status', 'unpaid')->count();

        return response()->json([
            'status' => true,
            'paid_orders' => $paid_orders,
            'due_orders' => $due_orders,
        ]);
    }
    public function orderSourceChart(Request $request)
    {
        if(filled($request->date)){
            $filter_date = $request->date;
            $from = date('Y-m-d 00:00:00', strtotime(explode(' to ', $filter_date)[0]));
            $to = date('Y-m-d 23:59:59', strtotime(explode(' to ', $filter_date)[1]));
        } else{
            $from = date('Y-m-d 00:00:00', strtotime('-6 Days'));
            $to = date('Y-m-d 23:59:59');
        }
        $orders = Order::where('delivery_status', '!=', 'cancelled')
                ->whereBetween('created_at', [$from, $to])
                ->get()
                ->map(function ($order) {
                    $order->order_source = strtolower($order->order_source);
                    return $order;
                });

        $sourceCounts = [
            'website' => $orders->where('order_source', 'website')->count(),
            'android' => $orders->where('order_source', 'android')->count(),
            'ios' => $orders->where('order_source', 'ios')->count(),
            'pos' => $orders->where('order_source', 'pos')->count(),
            'merchant' => $orders->where('order_type', 'merchant')->count(),
            'showroom' => $orders->where('order_source', 'showroom')->count(),
        ];

        // $orderCounts = Order::where('delivery_status', '!=', 'cancelled')
        //         ->whereBetween('created_at', [$from, $to])
        //         ->select('order_source', DB::raw('count(*) as count'))
        //         ->groupBy('order_source')
        //         ->get()
        //         ->pluck('count', 'order_source');

        return response()->json([
            'status' => true,
            'counts' => $sourceCounts,
            // 'orderCounts' => $orderCounts,
        ]);
    }
    public function getGraphData(Request $request)
    {
        $filterDate = $request->input('date');

        // Determine date range
        if ($filterDate) {
            [$startDate, $endDate] = explode(' to ', $filterDate);
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } else {
            $end = Carbon::today()->endOfDay();
            $start = Carbon::today()->subDays(6)->startOfDay();
        }

        // Generate date ranges based on the period length
        $dateRanges = $this->generateDateRanges($start, $end);

        // Get order data
        $orderData = $this->getOrderData($dateRanges);

        return response()->json([
            'labels' => $orderData['labels'],
            'orderCounts' => $orderData['counts'],
            'orderAmounts' => array_map(function ($amount) {
                return number_format($amount, 2, '.', '');
            }, $orderData['amounts']),
            'dateRanges' => $dateRanges
        ]);
    }

    protected function generateDateRanges(Carbon $start, Carbon $end): array
    {
        $diffInDays = $start->diffInDays($end);

        if ($diffInDays > 30) {
            return $this->generateMonthlyRanges($start, $end);
        }

        return $this->generateDailyRanges($start, $end);
    }

    protected function generateMonthlyRanges(Carbon $start, Carbon $end): array
    {
        $period = CarbonPeriod::create($start, '1 month', $end);
        $ranges = [];

        foreach ($period as $date) {
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            // Adjust end date if it's beyond our requested end date
            if ($monthEnd > $end) {
                $monthEnd = $end;
            }

            $ranges[] = [
                'labelname' => $date->format('F, Y'), // Localized month name
                'month' => $date->format('F, Y'),
                'from_date' => $monthStart->toDateTimeString(),
                'to_date' => $monthEnd->toDateTimeString(),
                'year' => $date->year,
            ];
        }

        return $ranges;
    }

    protected function generateDailyRanges(Carbon $start, Carbon $end): array
    {
        $period = CarbonPeriod::create($start, '1 day', $end);
        $ranges = [];

        foreach ($period as $date) {
            $ranges[] = [
                'labelname' => $date->format('d-m-Y'),
                'from_date' => $date->format('Y-m-d'),
                'to_date' => $date->format('Y-m-d'),
            ];
        }

        return $ranges;
    }

    protected function getOrderData(array $dateRanges): array
    {
        $counts = [];
        $amounts = [];
        $labels = [];

        foreach ($dateRanges as $range) {
            $from = Carbon::parse($range['from_date'])->startOfDay();
            $to = Carbon::parse($range['to_date'])->endOfDay();

            $counts[] = Order::where('delivery_status', '!=', 'cancelled')
                ->whereBetween('created_at', [$from, $to])
                ->count();

            $amounts[] = Order::where('delivery_status', '!=', 'cancelled')
                ->whereBetween('created_at', [$from, $to])
                ->sum('grand_total');

            $labels[] = $range['labelname'];
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
            'amounts' => $amounts,
        ];
    }

    public function getTopSellingProducts(Request $request)
    {
        if (filled($request->date)) {
            [$startDate, $endDate] = explode(' to ', $request->date);
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } else {
            $end = Carbon::today()->endOfDay();
            $start = Carbon::today()->subDays(6)->startOfDay();
        }
        $limit = $request->limit ?? 10;

        $topSellingProducts = \App\Models\OrderDetail::with('product')
                    ->selectRaw('sum(quantity) as total_quantity, product_id , variation')
                    ->where('created_at', '>=', $start)
                    ->where('created_at', '<=', $end)
                    ->groupBy('product_id')
                    ->groupBy('variation')
                    ->orderBy('total_quantity', 'desc')
                    ->take($limit)
                    ->get();

        return response()->json([
            'status' => true,
            'view' => view('backend.components.top-selling-products', [
                'products' => $topSellingProducts,
            ])->render(),
        ]);
    }

    public function getCardsData(Request $request)
    {
        if (filled($request->date)) {
            [$startDate, $endDate] = explode(' to ', $request->date);
            $from = Carbon::parse($startDate)->startOfDay();
            $to = Carbon::parse($endDate)->endOfDay();
        } else {
            $to = Carbon::today()->endOfDay();
            $from = Carbon::today()->subDays(6)->startOfDay();
        }

        $pendingOrderCount = Order::where('delivery_status', 'pending')->whereBetween('created_at', [$from, $to])->count();
        $totalOrderCount = Order::where('delivery_status', '!=', 'cancelled')->whereBetween('created_at', [$from, $to])->count();
        $newCustomerCount = User::where('user_type', 'customer')->whereBetween('created_at', [$from, $to])->count();
        $sms_balance = 0;

        // Define the URL you want to send the POST request to
        $url = 'https://smsplus.sslwireless.com/api/v3/balance';

        // Data to be sent in the request body
        $data = [
            'api_token' => env('SSL_SMS_API_TOKEN'),
            'sid' => env('SSL_SMS_SID'),
        ];

        // Make the POST request
        $response = Http::post($url, $data);

        // Handle the response
        if ($response->successful()) {
            // Request was successful (2xx status code)
            $responseData = $response->json(); // Access response data in JSON format
            // Process $responseData as needed

            $sms_balance = $responseData['balance'] ?? 0;
        } else {
            // Request failed (non-2xx status code)
            $errorCode = $response->status(); // Get the HTTP status code
            $errorResponse = $response->json(); // Access error response data in JSON format
            // Handle the error
        }

        return response()->json([
            'status' => true,
            'data' => [
                'pending_order_count' => $pendingOrderCount,
                'total_order_count' => $totalOrderCount,
                'new_customer_count' => $newCustomerCount,
                'sms_balance' => $sms_balance,
            ]
        ]);
    }

    // public function admin_dashboard(Request $request)
    // {
    //     $dashboards = $request->dashboards ?? [];
    //     return view('backend.dashboard.admin_dashboard', compact('dashboards'));
    // }

    // Cached View
    public function admin_dashboard(Request $request)
    {
        // dd(get_setting('enable_dashboard_cache'));
        if(get_setting('enable_dashboard_cache') == 1){
            return response(
                Cache::remember('admin_dashboard_'.Auth::id(), now()->addMinutes(get_setting('dashboard_cache_time', 10)), function () use ($request) {
                    $dashboards = $request->dashboards ?? [];
                    $cached_at = now();
                    return view('backend.dashboard.admin_dashboard', compact('dashboards', 'cached_at'))->render();
                })
            );
        } else {
            $dashboards = $request->dashboards ?? [];
            $cached_at = null;
            return view('backend.dashboard.admin_dashboard', compact('dashboards', 'cached_at'));
        }
    }

    public function resetDashboardCache()
    {
        Cache::forget('admin_dashboard_'.Auth::id());
        return response()->json([
            'status' => true,
            'message' => 'Cache cleared successfully',
        ]);
    }

    public function customer_care_dashboard(Request $request)
    {
        $filter_date = $request->filter_date;
        $report = StaffReportService::customerCareReport($request);
        $dashboards = $request->dashboards ?? [];
        $view = $request->view ?? null;
        return view('backend.dashboard.customer_care_dashboard', compact('report', 'filter_date', 'dashboards', 'view'));
    }

    public function packaging_dashboard(Request $request)
    {
        $filter_date = $request->filter_date;
        $report = StaffReportService::packagingReport($request);
        $dashboards = $request->dashboards ?? [];
        $view = $request->view ?? null;
        return view('backend.dashboard.packaging_dashboard', compact('report', 'filter_date', 'dashboards', 'view'));
    }

    public function account_inventory_dashboard(Request $request)
    {
        $filter_date = $request->filter_date;
        $report = StaffReportService::accountInventoryReport($request);
        $dashboards = $request->dashboards ?? [];
        $view = $request->view ?? null;
        return view('backend.dashboard.account_inventory_dashboard', compact('report', 'filter_date', 'dashboards', 'view'));
    }

    public function get_module_shortcuts(Request $request) {
        $modules = \App\Models\ShortcutModule::with('shortcuts')->where('status', 1)->get();
        $dashboards = $request->dashboards ?? [];
        return response()->json([
            'status' => true,
            'view' => view('backend.components.shortcut-modules', compact('modules','dashboards'))->render(),
        ]);
    }

    public function staffReport(Request $request, $userId = null)
    {
        $filter_date = $request->filter_date;
        $start_date = now()->format('Y-m-d 00:00:00');
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

        $cache_key = 'staff_report_' . $start_date . '_' . $end_date. '_' . $userId;
        $report = Cache::remember($cache_key . '_report', now()->addHour(), function () use ($userId, $user, $start_date, $end_date) {
            $callLogs = CallLog::forOrders()
                ->where('called_by', $userId)
                ->whereNotIn('status', ['out_of_stock', 'shipment_failed', 'others'])
                ->whereBetween('created_at', [$start_date, $end_date])
                ->get();
            $orderLogs = OrderLog::where('managed_by', $userId)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->get();

            return [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->staff?->role?->name ?? '',
                'pending_orders_count' => Order::where('delivery_status', 'pending')->where('order_type', '!=', 'merchant')->count(),
                'call_count' => $callLogs->pluck('order_id')->unique()->count(),
                'create_count' => $orderLogs->where('action', 'created')->pluck('order_id')->unique()->count(),
                'update_count' => $orderLogs->whereIn('action', ['updated', 'cancelled', 'delivery_status', 'payment_status'])->pluck('order_id')->unique()->count(),
                'callLogs' => $callLogs,
            ];
        });

        return $report;
    }

    public function getLowStockProducts(Request $request)
    {
        $data = DB::table('product_stocks')
            ->join('products', 'product_stocks.product_id' ,'=', 'products.id')
            ->where('products.published','=', 1)
            ->where('products.approved','=', 1)
            ->whereColumn('product_stocks.qty', '<=', 'products.low_stock_quantity')
            ->orderBy('product_stocks.qty', 'asc')
            ->paginate(10);

        return view('backend._lowStockProducts', compact('data'));
    }

    function clearCache(Request $request)
    {
        // PurgeCloudflareCache::dispatch('', true)->onQueue('high');
        Cache::flush();
        flash(('Cache cleared successfully'))->success();
        return back();
    }

    public function not_found()
    {
        return view('errors.404');
    }
}
