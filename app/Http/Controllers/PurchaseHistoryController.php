<?php

namespace App\Http\Controllers;

use App\Events\OrderPlaced;
use App\Events\ProductStockAffected;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductStock;
use App\Models\RewardPointLog;
use App\Models\User;
use Auth;
use DB;

class PurchaseHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::user()->id)->orderBy('date', 'desc')->orderBy('delivery_date', 'desc')->paginate(9);
        return view('frontend.user.purchase_history', compact('orders'));
    }

    public function digital_index()
    {
        $orders = DB::table('orders')
                        ->orderBy('code', 'desc')
                        ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                        ->join('products', 'order_details.product_id', '=', 'products.id')
                        ->where('orders.user_id', Auth::user()->id)
                        ->where('products.digital', '1')
                        ->where('order_details.payment_status', 'paid')
                        ->select('order_details.id')
                        ->paginate(15);
        return view('frontend.user.digital_purchase_history', compact('orders'));
    }

    public function purchase_history_details(Request $request)
    {
        $order = Order::with('payments')->findOrFail($request->order_id);
        $order->delivery_viewed = 1;
        $order->payment_status_viewed = 1;
        $order->save();
        return view('frontend.user.order_details_customer', compact('order'));

    }
    public function purchase_history_cancel(Request $request)
    {
        $order = Order::with('orderDetails')->findOrFail($request->order_id);
        if($order->delivery_status=='pending' && $order->payment_status != 'paid'):
            $order->delivery_status = 'cancelled';
            if($order->save()){
                $reasonLabel = \App\Enums\Reasons::value(trim($request->reason));
                record_order_cancellation([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'user_type' => Auth::user()->user_type,
                    'reason_type' => is_null($reasonLabel) ? 'other' : trim($request->reason),
                    'reason' => is_null($reasonLabel) ? trim($request->reason) : $reasonLabel,
                ]);
                foreach($order->orderDetails as $key => $orderDetail){
                    $orderDetail->delivery_status = 'cancelled';
                    $orderDetail->save();

                    $variant = $orderDetail->variation;
                    if ($orderDetail->variation == null) {
                        $variant = '';
                    }

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)->where('variant', $variant)->first();
                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();

                        $isAddition = true;
                        // Store Stock Transaction
                        $transaction = [
                            'product_id'    => (int)$orderDetail->product_id,
                            'variant'       => empty($product_stock->variant) ? null : $product_stock->variant,
                            'sku'           => $product_stock->sku ?? null,
                            'qty'           => abs($orderDetail->quantity),
                            'isAddition'    => ($isAddition) ? 1 : 0,
                            'isSubtraction' => ($isAddition) ? 0 : 1,
                            'purpose'       => 'order_cancelled',
                            'purpose_id'    => $order->id ?? 0,
                            'note'          => 'Order Cancelled, Ref. Code = '.$order->code ?? 'Unknown'.''
                        ];
                        // Trigger The Event
                        event(new ProductStockAffected($transaction));
                    }
                }
                event(new OrderPlaced($order));
                return true;
            }else{
                return false;
            }
        endif;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function reward_point_log(){
        $user_id = auth()->id();
        $user = User::where('id', $user_id)->select(['point_balance', 'reward_point_expires_at'])->first();
        $rewardPointLogs = RewardPointLog::where('user_id', $user_id)->select(['activity_type', 'earned','spent','activity_str', 'created_at'])->latest()->paginate(5);
        return view("frontend.user.reward_point_log.index", compact('rewardPointLogs', 'user'));
    }


}
