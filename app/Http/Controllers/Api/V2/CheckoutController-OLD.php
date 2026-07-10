<?php


namespace App\Http\Controllers\Api\V2;


use Session;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use App\Models\CombinedOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController
{
    public function apply_coupon_code(Request $request)
    {
        $coupon = Coupon::where('code', $request->coupon_code)->first();
        if(!$coupon) {
            return response()->json([
                'result' => false,
                'message' => ('Invalid coupon!')
            ]);
        }
        $is_used = CouponUsage::where('user_id', $request->user_id)->where('coupon_id', $coupon->id)->exists();
        if($is_used && $coupon->usage_limit=='single') {
            return response()->json([
                'result' => false,
                'message' => ('You have already used this coupon!')
            ]);
        }
        $cart_items = Cart::where('user_id', $request->user_id)->get();

        //check discount product
        foreach ($cart_items as $key => $item) {
            if(check_discount_product_from_cart($item->product_id) == true){
                return response()->json(['result' => false, 'message' => ('COUPON Not Available For Discounted Products.')]);
            }
        }

        // Group Discount Section
        if($request->user_id!=''):
            $user_info = User::findOrFail($request->user_id);
            if($user_info->customeringroup){
               $discount_status = $user_info->customeringroup->group->discount_status;
                $start_date = $user_info->customeringroup->group->start_date;
                $end_date = $user_info->customeringroup->group->end_date;
                $cur_date = strtotime(date('Y-m-d H:i:s'));
                if($discount_status==1 && $cur_date >= $start_date && $cur_date <= $end_date){
                    return response()->json(['result' => false, 'message' => ("You already have a group discount!")]);
                }
            }
        endif;
        // Group Discount Section

        if ($cart_items->isEmpty()) {
            return response()->json([
                'result' => false,
                'message' => ('Cart is empty')
            ]);
        }

        $in_range = strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date;

        if (!$in_range) {
            return response()->json([
                'result' => false,
                'message' => ('Coupon expired!')
            ]);
        }

        $coupon_details = json_decode($coupon->details);

        if ($coupon->type == 'cart_base') {
            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            foreach ($cart_items as $key => $cartItem) {
                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                //$tax += $cartItem['tax'] * $cartItem['quantity'];
                //$shipping += $cartItem['shipping'] * $cartItem['quantity'];
            }
            $sum = $subtotal + $tax + $shipping;

            if ($sum >= $coupon_details->min_buy) {
                if ($coupon->discount_type == 'percent') {
                    $coupon_discount = ($sum * $coupon->discount) / 100;
                    if ($coupon_discount > $coupon_details->max_discount) {
                        $coupon_discount = $coupon_details->max_discount;
                    }
                } elseif ($coupon->discount_type == 'amount') {
                    $coupon_discount = $coupon->discount;
                }

                Cart::where('user_id', $request->user_id)->update([
                    'discount' => $coupon_discount / count($cart_items),
                    'coupon_code' => $request->coupon_code,
                    'coupon_applied' => 1
                ]);

                return response()->json([
                    'result' => true,
                    'message' => ('Coupon Applied')
                ]);


            }else{
                return response()->json([
                    'result' => false,
                    'message' => ('Minimum order amount needed to apply this coupon!')
                ]);
            }
        } elseif ($coupon->type == 'product_base') {
            $coupon_discount = 0;
            foreach ($cart_items as $key => $cartItem) {
                foreach ($coupon_details as $key => $coupon_detail) {
                    if ($coupon_detail->product_id == $cartItem['product_id']) {
                        if ($coupon->discount_type == 'percent') {
                            $coupon_discount += $cartItem['price'] * $coupon->discount / 100;
                        } elseif ($coupon->discount_type == 'amount') {
                            $coupon_discount += $coupon->discount;
                        }
                    }
                }
            }


            Cart::where('user_id', $request->user_id)->update([
                'discount' => $coupon_discount / count($cart_items),
                'coupon_code' => $request->coupon_code,
                'coupon_applied' => 1
            ]);

            return response()->json([
                'result' => true,
                'message' => ('Coupon Applied')
            ]);

        }


    }

    public function remove_coupon_code(Request $request)
    {
        Cart::where('user_id', $request->user_id)->update([
            'discount' => 0.00,
            'coupon_code' => "",
            'coupon_applied' => 0
        ]);

        return response()->json([
            'result' => true,
            'message' => ('Coupon Removed')
        ]);
    }

    //redirects to this method after a successfull checkout
    public function checkout_done($combined_order_id, $payment)
    {
        $combined_order = CombinedOrder::findOrFail($combined_order_id);
        foreach ($combined_order->orders as $key => $order) {
            $order = Order::findOrFail($order->id);

            // Newly added code for store payment details
            $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
            $pinv = "PAY-" . date('Ymd') . ($inv_counting + 1);

            $method = $payment['method'] ?? Session::get('payment_method', null);
            $pdetails = [
                'payment_method' => $method,
                'bank_type' => $payment['bank_type'] ?? null,
                'bank_info' => $method,
                'payment_amount' => get_order_grand_total($order),
            ];

            try{
                DB::beginTransaction();
                $payment = new Payment;
                $payment->invoice_no = $pinv;
                $payment->date = date('Y-m-d');
                $payment->payable_id = $order->user_id;
                $payment->payable_type = User::class;
                $payment->reference_id = $order->id;
                $payment->reference_type = Order::class;
                $payment->seller_id = null;
                $payment->amount = get_order_grand_total($order);
                $payment->payment_details = json_encode($pdetails);
                $payment->payment_method = $method;
                $payment->txn_code = null;
                $payment->user_id = auth()->user()?->id ?? null;
                $payment->remarks = "Payment for Order #" . $order->code;
                $payment->save();
                // End of newly added code

                $order->payment_status = 'paid';
                $order->due_amount = 0;
                $order->payment_details = $payment;
                $order->save();
                DB::commit();

                calculateCommissionAffilationClubPoint($order);
            }catch(\Exception $e){
                DB::rollback();
                Log::error('Payment Error: ' . $e->getMessage(), $e->getTrace());
            }
            // $order = Order::findOrFail($order->id);
            // $order->payment_status = 'paid';
            // $order->payment_details = $payment;
            // $order->save();

            // calculateCommissionAffilationClubPoint($order);
        }

        // Session::put('combined_order_id', $combined_order_id);
        return redirect()->route('aamarpay.done');
    }
}
