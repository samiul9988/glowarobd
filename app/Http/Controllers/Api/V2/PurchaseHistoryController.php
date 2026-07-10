<?php

namespace App\Http\Controllers\Api\V2;

use App\Events\ProductStockAffected;
use App\Http\Resources\V2\PurchaseHistoryMiniCollection;
use App\Http\Resources\V2\PurchaseHistoryCollection;
use App\Http\Resources\V2\PurchaseHistoryItemsCollection;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductStock;
use Illuminate\Http\Request;

class PurchaseHistoryController extends Controller
{
    public function index($id, Request $request)
    {
        $order_query = Order::with('payments');
        if ($request->payment_status != "" || $request->payment_status != null) {
            $order_query->where('payment_status', $request->payment_status);
        }
        if ($request->delivery_status != "" || $request->delivery_status != null) {
            $delivery_status = $request->delivery_status;
            $order_query->whereIn("id", function ($query) use ($delivery_status) {
                $query->select('order_id')
                    ->from('order_details')
                    ->where('delivery_status', $delivery_status);
            });
        }

        return new PurchaseHistoryMiniCollection($order_query->where('user_id', $id)->latest()->paginate(5));
    }

    public function details($id)
    {
        $order_query = Order::with('payments')->where('id', $id);
        return new PurchaseHistoryCollection($order_query->get());
    }

    public function purchase_history_cancel(Request $request)
    {
        $orderid = intval($request->order_id);
        $userid = intval($request->user_id);

        $order = Order::with('payments')->findOrFail($orderid);
        if($order->user_id==$userid):
            if($order->delivery_status=='pending' && $order->payment_status != 'paid'):
                $order->delivery_status = 'cancelled';
                if($order->save()){
                    if (filled($request->reason)) {
                        $reasonLabel = \App\Enums\Reasons::value(trim($request->reason));
                        record_order_cancellation([
                            'order_id' => $order->id,
                            'user_id' => $userid,
                            'user_type' => 'customer',
                            'reason_type' => is_null($reasonLabel) ? 'other' : trim($request->reason),
                            'reason' => is_null($reasonLabel) ? trim($request->reason) : $reasonLabel,
                        ]);
                    }
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
                    return response()->json(['result' => true, 'message' => ('Order Canceled')], 200);
                }else{
                    return response()->json(['result' => false, 'message' => ('Order not found')], 200);
                }
            endif;
        else:
            return response()->json(['result' => false, 'message' => ('You have no permission to cancel this order!')], 200);
        endif;
    }

    public function cancellation_reasons()
    {
        $reasons = \App\Enums\Reasons::cancelReason();
        return response()->json(['success' => true, 'reasons' => $reasons], 200);
    }

    public function items($id)
    {
        $order_query = OrderDetail::where('order_id', $id);
        return new PurchaseHistoryItemsCollection($order_query->get());
    }

    public function detailsByCode($code)
    {
        $order_query = Order::with('payments')->where('code', $code);
        return new PurchaseHistoryCollection($order_query->get());
    }
}
