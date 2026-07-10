<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RefundRequest;
use Auth;
use Illuminate\Http\Request;

class RefundRequestController extends Controller
{
    // All refund requsts
    public function requests(Request $request){

        $currentStatus = @$request->status;
        if($currentStatus==null){
            $currentStatus = 'pending';
        }
        $status = null;

        $requests = RefundRequest::with('user','order')->orderBy('id', 'desc');

        if ($request->status != null) {
            $requests = $requests->where('status', $request->status);
            $status = $request->status;
        }
        if ($currentStatus != null){
            $requests = $requests->where('status', $currentStatus);
        }
        $requests = $requests->paginate(15);

        $statusCount = [
            'pending'=>RefundRequest::where('status', 'pending')->count(),
            'approved'=>RefundRequest::where('status', 'approved')->count(),
            'cancelled'=>RefundRequest::where('status', 'cancelled')->count()
        ];

        return view('backend.refund.index', compact('requests', 'currentStatus', 'status', 'statusCount'));
    }

    // Store a refund request
    public function requestRefund(Request $request){
        $order = Order::findOrFail($request->order_id);
        if($order->delivery_status=='pending' && $order->payment_status == 'paid'){
            $refund_count = RefundRequest::where(['user_id'=>Auth::user()->id, 'order_id'=> $order->id])->count();
            if($refund_count <= 0){
                $refund = new RefundRequest;
                $refund->user_id = Auth::user()->id;
                $refund->order_id = $order->id;
                $refund->refund_amount = $order->grand_total;
                $refund->payment_type = $order->payment_type;
                $refund->payment_details = $order->payment_details;
                $refund->reason = $request->reason ?? 'No reason specified by the customer';
                if($refund->save()){
                    $order->delivery_status = 'cancelled';
                    $order->save();
                    flash(('Refund request submitted successfully!'))->success();
                }
            }else{
                flash(('You already submitted a refund request for this order.'))->warning();
            }
        }else{
            flash(('Refund request not applicable for this order.'))->warning();
        }
    }

    // Accept a refund request
    public function accept(Request $request)
    {
        $refundRequest = RefundRequest::findOrFail($request->id);
        $order = Order::findOrFail($refundRequest->order_id);
        $request->order_id = $refundRequest->order_id;
        if($order->delivery_status=='cancelled' && $order->payment_status == 'paid' && $order->payment_type == 'bkash'){
            $bkashController =  new BkashController;
            $data = $bkashController->refund($request);
            if(json_decode($data)->transactionStatus == 'Completed'){
                $order->payment_status = 'refunded';
                $order->save();
                $refundRequest->status = 'approved';
                $refundRequest->save();
                flash(('Refund request approved successfully!'))->success();
            }
        }else{
            flash(('Something went wrong!!!'))->warning();
        }
    }

    // Cancel a refund request
    public function cancel(Request $request)
    {
        $refundRequest = RefundRequest::findOrFail($request->id);
        if($refundRequest->status=='pending'){
            $refundRequest->status = 'cancelled';
            $refundRequest->save();
            flash(('Refund request cancelled successfully!'))->success();
        }else{
            flash(('You can not cancel request that are already approved or cancelled!!'))->warning();
        }
    }
}
