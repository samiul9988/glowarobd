<?php


namespace App\Http\Controllers\Api\V3;

use App\Models\Cart;
use App\Models\CombinedOrder;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CheckoutController
{
    public function apply_coupon_code(Request $request)
    {
        $coupon = Coupon::valid()->where('code', $request->coupon_code)->first();
        if(!$coupon) {
            return response()->json([
                'result' => false,
                'message' => ('Invalid coupon!')
            ]);
        }

        $response = is_coupon_valid($coupon, $request->user_id ?? null, $request->header('source', 'app'));

        if(data_get($response, 'status', false) === false){
            return response()->json([
                'result' => false,
                'type' => 'danger',
                'message' => data_get($response, 'message', 'Invalid coupon!')
            ], data_get($response, 'code', 200));
        }

        $cartItems = Cart::whereNotNull($request->user_field)
            ->where($request->user_field, $request->user_id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'result' => false,
                'message' => ('Cart is empty')
            ]);
        }

        if($coupon->force_apply == 0){
            //check discount product
            foreach ($cartItems as $key => $item) {
                if(check_discount_product_from_cart($item->product_id) == true){
                    return response()->json([
                        'result' => false,
                        'message' => 'Coupon not available for discounted products.'
                    ]);
                }
            }
        }

        // Group discount check
        if (!$request->is_guest_user && $request->user_id && $coupon->force_apply == 0) {
            $user = User::with('customeringroup.group')->find($request->user_id);

            if ($user && $user->customeringroup) {
                $group = $user->customeringroup->group ?? null;
                $now = now()->timestamp;

                if ($group && $group->discount_status == 1 &&
                    $now >= $group->start_date &&
                    $now <= $group->end_date) {
                    return response()->json([
                        'result' => false,
                        'message' => 'You already have a group discount!'
                    ]);
                }
            }
        }

        $details = json_decode($coupon->details);
        $discount = 0;

        if ($coupon->type == 'cart_base') {
            $subtotal = $cartItems->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            if ($subtotal < $details->min_buy) {
                $buyMore = single_price(round($details->min_buy - $subtotal, 2));
                return response()->json([
                    'result' => false,
                    'message' => 'Add more ' . $buyMore . ' to apply this coupon.'
                ]);
            }

            if ($coupon->discount_type === 'percent') {
                $discount = ($subtotal * $coupon->discount) / 100;
                $discount = min($discount, $details->max_discount);
            } else {
                $discount = $coupon->discount;
            }
        } elseif ($coupon->type == 'product_base') {
            $eligibleProducts = collect($details)->pluck('product_id')->toArray();
            $eligibleItems = $cartItems->whereIn('product_id', $eligibleProducts);
            foreach ($eligibleItems as $item) {
                if ($coupon->discount_type === 'percent') {
                    $discount += ($item->price * $coupon->discount) / 100;
                } else {
                    $discount += $coupon->discount;
                }
            }
        } elseif ($coupon->type == 'shipping_charge') {
            $shippingCost = $cartItems->sum('shipping_cost');
            $cartTotal = $cartItems->sum(function ($cartItem) {
                return $cartItem['price'] * $cartItem['quantity'];
            });
            if ($cartTotal < $details->min_buy) {
                $needToBuyMore = $details->min_buy - $cartTotal;
                return response()->json([
                    'result' => false,
                    'message' => 'Add more ' . single_price($needToBuyMore) . ' to apply this coupon!'
                ]);
            }
            if ($coupon->discount_type === 'percent') {
                $discount = ($shippingCost * $coupon->discount) / 100;
                $discount = min($discount, $details->max_discount);
            } else {
                $discount = $coupon->discount;
            }
        }

        Cart::whereNotNull($request->user_field)
            ->where($request->user_field, $request->user_id)
            ->update([
                'discount' => 0.00,
                'coupon_code' => '',
                'coupon_applied' => 0
            ]);

        $cart = Cart::whereNotNull($request->user_field)
            ->where($request->user_field, $request->user_id)
            ->first();

        if ($cart) {
            $cart->update([
                'discount' => $discount,
                'coupon_code' => $coupon->code,
                'coupon_applied' => 1
            ]);
        }

        return response()->json([
            'result' => true,
            'message' => 'Coupon Applied'
        ]);
    }

    public function remove_coupon_code(Request $request)
    {
        $cart = Cart::whereNotNull($request->user_field)
            ->where($request->user_field, $request->user_id)
            ->first();

        if(!$cart || $cart->coupon_applied == 0){
            return response()->json([
                'result' => false,
                'message' => 'No Coupon Applied'
            ]);
        }

        Cart::whereNotNull($request->user_field)
            ->where($request->user_field, $request->user_id)
            ->update([
                'discount' => 0,
                'coupon_code' => null,
                'coupon_applied' => 0
            ]);

        return response()->json([
            'result' => true,
            'message' => 'Coupon Removed'
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
