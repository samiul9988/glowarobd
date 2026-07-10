<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Staff;
use App\Models\CallLog;
use App\Models\OrderLog;
use App\Models\TicketLog;
use Illuminate\Http\Request;
use App\Models\OrderFeedback;
use Illuminate\Support\Facades\DB;

class OrderLogController extends Controller
{
    public function index(Request $request)
    {
        $filter_date = $request->filter_date ?? null;
        $userId = $request->staff ?? null;
        $event = $request->event ?? null;
        $search = $request->search ?? null;

        if($event === 'ticket'){
            return $this->ticketLogReport($request);
        } elseif($event === 'feedback') {
            return $this->feedbackLogReport($request);
        }

        $perPage = $request->input('per_page', 25);
        $currentPage = $request->input('page', 1);

        // Calculate offset for pagination
        $offset = ($currentPage - 1) * $perPage;

        if(filled($event) && $event == 'updated'){
            $event = ['updated', 'cancelled', 'delivery_status', 'payment_status'];
        }

        // Set date range
        $start_date = now()->format('Y-m-d 00:00:00');
        $end_date = now()->format('Y-m-d 23:59:59');
        if (filled($filter_date)) {
            $start_date = Carbon::parse(explode(' to ', $filter_date)[0])->startOfDay()->format('Y-m-d H:i:s');
            $end_date = Carbon::parse(explode(' to ', $filter_date)[1])->endOfDay()->format('Y-m-d H:i:s');
        }


        if (filled($search)) {
            // Search by order code - get both call logs and order logs for the matching order
            $order = Order::where('code', $search)->first();

            if ($order) {
                // Get call logs for the order
                $logs = CallLog::with(['reference:id,code,shipping_address', 'caller:id,name'])
                    ->where('reference_type', Order::class) // filter only orders
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->when($userId, fn($q) => $q->where('called_by', $userId))
                    ->where('duration', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($log) {
                        return [
                            'order_id' => $log->reference_id,
                            'order_code' => $log->reference?->code,
                            'duration' => $log->duration,
                            'message' => $log->note,
                            'status' => $log->status,
                            'created_at' => $log->created_at->format('d-m-Y h:i A'),
                            'shipping_address' => $log->reference?->shipping_address ? json_decode($log->reference->shipping_address, true) : null,
                            'staff_name' => $log->caller?->name ?? 'Pathao',
                        ];
                    })->sortByDesc('created_at')->values();

                // Get order logs for the order
                $logs = OrderLog::with('order:id,code,shipping_address', 'managedBy:id,name')
                    ->where('order_id', $order->id)
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->when(filled($userId), fn($q) => $q->where('managed_by', $userId))
                    ->when($event, function ($query) use ($event) {
                        if (is_array($event)) {
                            return $query->whereIn('action', $event);
                        }
                        return $query->where('action', $event);
                    })
                    ->orderBy('created_at', 'desc')
                    ->select('order_id', 'managed_by', DB::raw('NULL as duration'), 'message', DB::raw('NULL as status'), 'created_at')
                    ->get()
                    ->map(function ($log) {
                        $user = $log->caller ?? $log->managedBy;
                        $messages = explode('by ', $log->message);
                        $message = count($messages) > 0 ? trim($messages[0]) : $log->message;
                        return [
                            'order_id' => $log->order_id,
                            'order_code' => $log->order?->code,
                            'duration' => $log->duration,
                            'message' => $message,
                            'status' => $log->status,
                            'created_at' => $log->created_at->format('d-m-Y h:i A'),
                            'shipping_address' => $log->order?->shipping_address ? json_decode($log->order?->shipping_address, true) : null,
                            'staff_name' => $user ? $user->name : 'Pathao',
                        ];
                    })->sortByDesc('created_at')->values();
            } else {
                $logs = collect([]);
            }
        } else {
            // Original filtering logic when search is null
            if(is_null($event) && is_null($userId)){
                $logs = collect([]);
            } else {
                if($event == 'called'){
                    $logs = CallLog::with(['reference:id,code,shipping_address', 'caller:id,name'])
                        ->where('reference_type', Order::class) // filter only orders
                        ->whereBetween('created_at', [$start_date, $end_date])
                        ->when($userId, fn($q) => $q->where('called_by', $userId))
                        ->where('duration', '>', 0)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function ($log) {
                            return [
                                'order_id' => $log->reference_id,
                                'order_code' => $log->reference?->code,
                                'duration' => $log->duration,
                                'message' => $log->note,
                                'status' => $log->status,
                                'created_at' => $log->created_at->format('d-m-Y h:i A'),
                                'shipping_address' => $log->reference?->shipping_address ? json_decode($log->reference->shipping_address, true) : null,
                                'staff_name' => $log->caller?->name ?? 'Pathao',
                            ];
                        })->sortByDesc('created_at')->values();
                } else {
                    $logs = OrderLog::with('order:id,code,shipping_address', 'managedBy:id,name')
                        ->whereBetween('created_at', [$start_date, $end_date])
                        ->when(filled($userId), fn($q) => $q->where('managed_by', $userId))
                        ->when($event, function ($query) use ($event) {
                            if (is_array($event)) {
                                return $query->whereIn('action', $event);
                            }
                            return $query->where('action', $event);
                        })
                        ->orderBy('created_at', 'desc')
                        ->select('order_id', 'managed_by', DB::raw('NULL as duration'), 'message', DB::raw('NULL as status'), 'created_at')
                        ->get()
                        ->map(function ($log) {
                            $user = $log->caller ?? $log->managedBy;
                            $messages = explode('by ', $log->message);
                            $message = count($messages) > 0 ? trim($messages[0]) : $log->message;
                            return [
                                'order_id' => $log->order_id,
                                'order_code' => $log->order?->code,
                                'duration' => $log->duration,
                                'message' => $message,
                                'status' => $log->status,
                                'created_at' => $log->created_at->format('d-m-Y h:i A'),
                                'shipping_address' => $log->order?->shipping_address ? json_decode($log->order?->shipping_address, true) : null,
                                'staff_name' => $user ? $user->name : 'Pathao',
                            ];
                        })->sortByDesc('created_at')->values();
                }
            }
        }

        // Paginate the results
        $logs = new \Illuminate\Pagination\LengthAwarePaginator(
            $logs->slice($offset, $perPage)->values(),
            $logs->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $staffs = Staff::with('user:id,name')
            ->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->user->id,
                    'name' => $staff->user->name,
                ];
            });

        $staff = $userId;
        $event = $request->event ?? null;
        return view('backend.order_log.index', compact('staffs', 'logs', 'filter_date', 'staff', 'event', 'search'));
    }

    public function feedbackLogReport(Request $request)
    {
        if(get_setting('enable_crm_module') != 1){
            abort(404);
        }
        $filter_date = $request->filter_date ?? null;
        $userId = $request->staff ?? null;
        $search = $request->search ?? null;

        $start_date = now()->subDays(6)->startOfDay();
        $end_date = now()->endOfDay();
        if (filled($filter_date)) {
            [$start, $end] = explode(' to ', $filter_date);
            $start_date = Carbon::parse($start)->startOfDay();
            $end_date = Carbon::parse($end)->endOfDay();
        }

        $logs = OrderFeedback::with([
            'order:id,code,shipping_address,created_at',
            'callLog' => function ($query) {
                $query->where('reference_type', User::class);
            },
            'callLog.caller:id,name',
            'callLog.reference:id,name,email,phone,email_verified_at',
            'callLog.reference.metaData'
        ])
        // ->whereHas('callLog.reference')
        ->whereBetween('created_at', [$start_date, $end_date])
        ->when($userId, fn($q) => $q->where('created_by', $userId))
        ->orderBy('created_at', 'desc')
        // ->get();
        ->paginate(25);

        // dd( $logs );
        $staffs = Staff::with('user:id,name')
            ->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->user->id,
                    'name' => $staff->user->name,
                ];
            });

        $staff = $userId;
        $event = $request->event ?? null;

        return view('backend.order_log.feedback_logs', compact('staffs', 'logs', 'filter_date', 'staff', 'event', 'search'));
    }

    public function ticketLogReport(Request $request)
    {
        $filter_date = $request->filter_date ?? null;
        $userId = $request->staff ?? null;
        $search = $request->search ?? null;

        // Set date range
        $start_date = now()->format('Y-m-d 00:00:00');
        $end_date = now()->format('Y-m-d 23:59:59');
        if (filled($filter_date)) {
            $start_date = Carbon::parse(explode(' to ', $filter_date)[0])->startOfDay()->format('Y-m-d H:i:s');
            $end_date = Carbon::parse(explode(' to ', $filter_date)[1])->endOfDay()->format('Y-m-d H:i:s');
        }

        $logs = TicketLog::with('ticket', 'user:id,name')
            ->where('action', '!=', 'deleted')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('ticket', function ($q) use ($search) {
                    $q->where('issue', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $staffs = Staff::with('user:id,name')
            ->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->user->id,
                    'name' => $staff->user->name,
                ];
            });

        $staff = $userId;
        $event = $request->event ?? null;
        return view('backend.order_log.ticket_logs', compact('staffs', 'logs', 'filter_date', 'staff', 'event', 'search'));
    }
}
