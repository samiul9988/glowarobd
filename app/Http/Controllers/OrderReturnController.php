<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderReturn;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessOrderReturnJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class OrderReturnController extends Controller
{
    public function index(Request $request, $status = 'pending')
    {
        $returnRequests = OrderReturn::with('order:id,code', 'user:id,name', 'approver:id,name')
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($request->date, function ($query) use ($request) {
                $dateRange = explode(' to ', $request->date);
                if (count($dateRange) == 2) {
                    $start = Carbon::parse($dateRange[0])->startOfDay();
                    $end = Carbon::parse($dateRange[1])->endOfDay();
                    $query->whereBetween('created_at', [$start, $end]);
                }
            })
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('code', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(15);

        $statusCounts = OrderReturn::query()
            ->when($request->date, function ($query) use ($request) {
                $dateRange = explode(' to ', $request->date);
                if (count($dateRange) == 2) {
                    $start = Carbon::parse($dateRange[0])->startOfDay();
                    $end = Carbon::parse($dateRange[1])->endOfDay();
                    $query->whereBetween('created_at', [$start, $end]);
                }
            })
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('backend.return_orders.index', compact('returnRequests', 'status', 'statusCounts'));
    }

    public function create()
    {
        return view('backend.return_orders.create');
    }

    function store(Request $request)
    {
        try{
            DB::beginTransaction();
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'products' => 'required|array|min:1',
                'products.*.item_id' => 'required|exists:order_details,id',
                'products.*.id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
            ]);

            // dd($request->all());
            $order = Order::with('allOrderDetails')->findOrFail($request->order_id);
            $orderDetails = $order->allOrderDetails;

            // Check if it is partial return or full return
            $orderDetailsQuantitySum = $orderDetails->sum('quantity');
            $requestQuantitySum = collect($request->products)->sum('quantity');
            $isPartialReturn = $requestQuantitySum < $orderDetailsQuantitySum && $orderDetails->count() != count($request->products);

            // dd($isPartialReturn);

            // Create Order Return
            $reasonLabel = \App\Enums\Reasons::value(trim($request->reason));
            $orderReturnRequest = OrderReturn::create([
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'reason_type' => is_null($reasonLabel) ? 'other' : trim($request->reason),
                'reason' => is_null($reasonLabel) ? trim($request->reason) : $reasonLabel,
                'status' => 'pending',
                'is_partial' => $isPartialReturn,
            ]);

            foreach($request->products as $product){
                $orderDetail = $orderDetails->where('id', $product['item_id'])->first();
                if($orderDetail){
                    $orderReturnRequest->items()->create([
                        'order_item_id' => $orderDetail->id,
                        'quantity' => $product['quantity'],
                        'unit_price' => $orderDetail->price / $orderDetail->quantity,
                    ]);
                }
            }

            if($orderReturnRequest->items()->count() == 0){
                throw ValidationException::withMessages(['products' => 'No valid products found for return.']);
            }
            logOrder($order, 'return_request', 'Order return request has been created.');
            DB::commit();
            flash('Return request submitted successfully.')->success();
            return redirect()->route('return-orders.create');
        } catch (ValidationException $e) {
            DB::rollBack();
            // dd($e->errors(), $e->getMessage());
            $firstError = collect($e->errors())->first();
            flash($firstError[0])->error();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order Return Error: '.$e->getMessage() .' on line '. $e->getLine());
            flash('Server Error!')->error();
            return back();
        }
    }

    public function show($id)
    {
        $returnRequest = OrderReturn::with('order.allOrderDetails.product', 'user:id,name', 'approver:id,name', 'items.orderItem.product')->findOrFail(decrypt($id));

        // dd($returnRequest->toArray());
        return view('backend.return_orders.show', compact('returnRequest'));
    }

    public function updateStatus(Request $request)
    {
        try{
            DB::beginTransaction();
            $orderReturn = OrderReturn::findOrFail($request->id);

            if(($request->status == 'rejected')){
                $orderReturn->items()->delete();
                $orderReturn->delete();
                DB::commit();
                return response()->json(['success' => true]);
            } elseif ($request->status == 'approved') {
                $orderReturn->status = 'processing';
                $orderReturn->approved_by = auth()->id();
                $orderReturn->approved_at = now();
            }
            $orderReturn->save();

            if($request->status == 'approved') {
                ProcessOrderReturnJob::dispatch($orderReturn->id)->onQueue('high');
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order Return Update Status Error: '.$e->getMessage() .' on line '. $e->getLine());
            return response()->json(['success' => false, 'message' => 'Server Error!', 'error' => $e->getMessage()], 500);
        }
    }

    public function bulkUpdateStatus(Request $request)
    {
        if(empty($request->ids) || count($request->ids) == 0){
            return response()->json(['success' => false, 'message' => 'No orders selected.'], 400);
        }
        $counts = ['updated' => 0, 'failed' => 0];
        foreach($request->ids as $id){
            $response = $this->updateStatus(new Request(['id' => $id, 'status' => $request->status]));
            if($response->getStatusCode() == 200){
                $counts['updated']++;
            } else {
                $counts['failed']++;
            }
        }
        return response()->json([
            'success' => true,
            'message' => $counts['updated'].' requests are updated, '.$counts['failed'].' failed.'
        ]);
    }

    public function getReturnRatio(Request $request)
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
        $returnRatioCacheKey = 'return_ratio_' . (filled($request->date) ? strtotime($startDate) . '_' . strtotime($endDate) : 'all_time');
        $returnRatio = Cache::remember($returnRatioCacheKey, now()->addHour(1), function() use ($request, $startDate, $endDate) {
            $report = OrderReturn::query()
                ->when(filled($request->date), function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
            $finalReport = [];
            foreach ($report as $key => $value) {
                $finalReport[ucfirst($key)] = $value;
            }
            return $finalReport;
        });

        $returnRatioByReasonsCacheKey = 'return_ratio_reasons_' . (filled($request->date) ? strtotime($startDate) . '_' . strtotime($endDate) : 'all_time');
        $returnRatioByReasons = Cache::remember($returnRatioByReasonsCacheKey, now()->addHour(1), function() use ($request, $startDate, $endDate) {
            $report = OrderReturn::query()
                ->when(filled($request->date), function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->selectRaw('reason_type, COUNT(*) as count')
                ->groupBy('reason_type')
                ->pluck('count', 'reason_type')
                ->toArray();
            $finalReport = [];
            foreach ($report as $key => $value) {
                $finalReport[\App\Enums\Reasons::value($key)] = $value;
            }
            return $finalReport;
        });

        return response()->json([
            'success' => true,
            'ratioByStatus' => $returnRatio,
            'ratioByReasons' => $returnRatioByReasons,
        ]);
    }
}
