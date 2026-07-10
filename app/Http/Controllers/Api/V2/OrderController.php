<?php

namespace App\Http\Controllers\Api\V2;

use DB;
use DateTime;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Address;
use App\Models\Product;
use App\Events\OrderPlaced;
use App\Models\CouponUsage;
use App\Models\OrderDetail;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use App\Models\CombinedOrder;
use App\Models\RewardPointLog;
use App\Models\BusinessSetting;
use App\Models\RewardRedeemAction;
use App\Jobs\CourierSuccessRateJob;
use App\Events\ProductStockAffected;
use \App\Utility\NotificationUtility;
use App\Models\CouponCustomerAssignment;
use App\Http\Controllers\AffiliateController;

class OrderController extends Controller
{
    public function store(Request $request, $set_paid = false)
    {
        $cartItems = Cart::where('user_id', $request->user_id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'combined_order_id' => 0,
                'result' => false,
                'message' => ('Cart is Empty')
            ]);
        }

        $cartCouponDiscountData = check_coupon_discount($request->user_id, $cartItems);
        if(!empty($cartCouponDiscountData) && !$cartCouponDiscountData['result']){
            return response()->json([
                'combined_order_id' => 0,
                'result' => false,
                'message' => ($cartCouponDiscountData['message'])
            ]);
        };

        $user = User::find($request->user_id);

        $address = Address::where('id', $cartItems->first()->address_id)->first();
        $shippingAddress = [];
        if ($address != null) {
            $shippingAddress['name']        = $address->name ?? $user->name;
            $shippingAddress['email']       = $address->email ?? $user->email;
            $shippingAddress['address']     = $address->address;
            $shippingAddress['country']     = $address->country?->name;
            $shippingAddress['state']       = $address->state?->name;
            $shippingAddress['city']        = $address->city?->name;
            $shippingAddress['area']        = $address->area?->name;
            $shippingAddress['postal_code'] = $address->postal_code;
            $shippingAddress['phone']       = $address->phone;
            if ($address->latitude || $address->longitude) {
                $shippingAddress['lat_lang'] = $address->latitude . ',' . $address->longitude;
            }
        }

        $combined_order = new CombinedOrder;
        $combined_order->user_id = $user->id;
        $combined_order->shipping_address = json_encode($shippingAddress);
        $combined_order->save();

        $seller_products = array();
        foreach ($cartItems as $cartItem) {
            $product_ids = array();
            $product = Product::find($cartItem['product_id']);
            if (isset($seller_products[$product->user_id])) {
                $product_ids = $seller_products[$product->user_id];
            }
            array_push($product_ids, $cartItem);
            $seller_products[$product->user_id] = $product_ids;
        }

        $shippingDiscountAmount = PHP_INT_MAX;
        if(check_shipping_discount()){
            $matchZone = $address ? ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$address->area_id ?? null])->first() : ShippingZone::where('rest_of_the_world', 1)->first();
            $shippingDiscountAmount = getDiscountShippingCharge($cartItems, $matchZone->id ?? null);
        }

        foreach ($seller_products as $seller_product) {
            $order = new Order;
            $order->combined_order_id = $combined_order->id;
            $order->user_id = $user->id;
            $order->shipping_address = json_encode($shippingAddress);
            $order->address_id = isset($address) ? $address->id : intval($cartItems->first()->address_id);

            $order->payment_type = $request->payment_type;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            $order->code = config('app.order_no_prefix').date('YmdHis') . rand(10, 99); //date('Ymd-His') . rand(10, 99);
            $order->date = strtotime('now');
            if($set_paid){
                $order->payment_status = 'paid';
            }else{
                $order->payment_status = 'unpaid';
            }
            $notes = [];
            if(isset($request->note)){
                $notes[] = [
                    'message' => strip_tags($request->note),
                    'created_by' => auth()->user()->id,
                ];
                $order->notes = $notes;
            }
            $order->order_source = $request->order_source;
            // Pre-order
            $order->order_type = 'regular';
            if(has_preorder_product_to_cart($cartItems)){
                $order->order_type = 'preorder';
                $order->delivery_status = 'preorder';
            }

            $order->save();

            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            $coupon_discount = 0;

            $user_info = ($request->user_id != '') ? User::with('customeringroup.group')->find($request->user_id) : null;

            //Order Details Storing
            foreach ($seller_product as $cartItem) {
                $product = Product::with('stocks', 'flash_deal_product.flash_deals')->find($cartItem['product_id']);

                $product_variation = $cartItem['variation'];
                $product_stock = $product->stocks->where('variant', $product_variation)->first();

                $lastPurchaseItem = $product->getLastPurchaseOrderItemByVariant($product_variation);
                if ($lastPurchaseItem) {
                    $lastPurchasePrice = $lastPurchaseItem->price;
                } else {
                    $lastPurchasePrice = 0;
                }

                $cartItem['price'] = getMinimumPriceByVariant($product, $product_stock, 'app', $cartItem['quantity'], $user_info);
                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $coupon_discount += $cartItem['discount'];

                if(!check_preorder_product($product)){
                    if ($product->digital != 1 && $cartItem['quantity'] > $product_stock->qty && $product->allow_stock_out_purchases == 0) {
                        $order->delete();
                        $combined_order->delete();
                        return response()->json([
                            'combined_order_id' => 0,
                            'result' => false,
                            'message' => ('The requested quantity is not available for ') . $product->name
                        ]);
                    } elseif ($product->digital != 1) {
                        $product_stock->qty -= $cartItem['quantity'];
                        $product_stock->save();

                        $isFlashDealProduct = check_flash_deal_product($product);
                        $flashDealProduct = $product->flash_deal_product ?? null;
                        if ($isFlashDealProduct && $flashDealProduct) {
                            $flashDealProductQuantity = $flashDealProduct->quantity ?? 0;
                            if ($flashDealProductQuantity > 0) {
                                $flashDealProductQuantity -= $cartItem['quantity'];
                                $flashDealProduct->quantity = max(0, $flashDealProductQuantity);
                                $flashDealProduct->save();
                            } else {
                                remove_from_flashdeal($flashDealProduct->flash_deal_id, $product->id);
                            }
                        }

                        $isAddition = false;
                        // Store Stock Transaction
                        $transaction = [
                            'product_id'    => (int)$product->id,
                            'variant'       => empty($product_stock->variant) ? null : $product_stock->variant,
                            'sku'           => $product_stock->sku ?? null,
                            'qty'           => abs($cartItem['quantity']),
                            'isAddition'    => ($isAddition) ? 1 : 0,
                            'isSubtraction' => ($isAddition) ? 0 : 1,
                            'purpose'       => 'sales',
                            'purpose_id'    => $order->id ?? 0,
                            'note'          => 'New App Sales, Ref. ID = '.$order->code ?? 'Unknown'.''
                        ];
                        // Trigger The Event
                        event(new ProductStockAffected($transaction));
                    }
                }else{
                    if ($product->digital != 1 && $cartItem['quantity'] > ($product->preorder_max_qty - preorder_product_count($product))) {
                        $order->delete();
                        $combined_order->delete();
                        return response()->json([
                            'combined_order_id' => 0,
                            'result' => false,
                            'message' => ('The requested pre-order quantity is not available for ') . $product->name
                        ]);
                    }
                }

                $order_detail = new OrderDetail;
                $order_detail->order_id = $order->id;
                $order_detail->seller_id = $product->user_id;
                $order_detail->product_id = $product->id;
                $order_detail->variation = empty($product_variation) ? null : $product_variation;
                $order_detail->price = $cartItem['price'] * $cartItem['quantity'];
                $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                $order_detail->shipping_type = $cartItem['shipping_type'];
                $order_detail->shipping_method = $cartItem['shipping_method'];
                $order_detail->coupon_code = $cartItem['coupon_code'];
                $order_detail->product_referral_code = $cartItem['product_referral_code'];
                $order_detail->shipping_cost = min($shippingDiscountAmount, $cartItem['shipping_cost']);
                $order_detail->last_purchase_price = $lastPurchasePrice > 0 ? $lastPurchasePrice : $cartItem['price'];

                // Pre-order
                $order_detail->delivery_status = 'pending';
                if(has_preorder_product_to_cart($cartItems)){
                    $order_detail->delivery_status = 'preorder';
                }

                $shipping += $order_detail->shipping_cost;

                if ($cartItem['shipping_type'] == 'pickup_point') {
                    $order_detail->pickup_point_id = $cartItem['pickup_point'];
                }
                //End of storing shipping cost

                $order_detail->quantity = $cartItem['quantity'];
                $order_detail->save();

                $product->num_of_sale = $product->num_of_sale + $cartItem['quantity'];
                $product->save();

                $order->seller_id = $product->user_id;

                if (addon_is_activated('affiliate_system')) {
                    if ($order_detail->product_referral_code) {
                        $referred_by_user = User::where('referral_code', $order_detail->product_referral_code)->first();

                        $affiliateController = new AffiliateController;
                        $affiliateController->processAffiliateStats($referred_by_user->id, 0, $order_detail->quantity, 0, 0);
                    }
                }
            }

            $order->grand_total = $subtotal + $tax + $shipping;

            if ($seller_product[0]->coupon_code != null) {
                // if (Session::has('club_point')) {
                //     $order->club_point = Session::get('club_point');
                // }
                $coupon = Coupon::where('status', 1)->where('code', $seller_product[0]->coupon_code)->first();

                if($coupon){
                    $order->coupon_discount = $coupon_discount;
                    $order->grand_total -= $coupon_discount;
                    $order->save();

                    $coupon_usage = new CouponUsage;
                    $coupon_usage->user_id = $user->id;
                    $coupon_usage->coupon_id = $coupon->id;
                    $coupon_usage->order_id = $order->id;
                    $coupon_usage->save();

                    $couponAssignedToCustomer = CouponCustomerAssignment::where('customer_id', $user->id)->where('coupon_id', $coupon->id)->first();
                    if($couponAssignedToCustomer){
                        $couponAssignedToCustomer->is_used = 1;
                        $couponAssignedToCustomer->save();

                        // Update Coupon Usage Table
                        $coupon_usage->ref_id = $couponAssignedToCustomer->assigned_by;
                        $coupon_usage->save();
                    }
                }
            }

            if($request->has('reward_point')){
                $currentDateTime = new DateTime();
                $timestamp = $user->reward_point_expires_at;

                if(($user->point_balance >= $request->reward_point) && ($currentDateTime <= new DateTime ($timestamp))) {
                    $rewardPoint = $request->has('reward_point') ? $request->reward_point : 0;
                    $rewardAmount = $request->has('reward_amount') ? $request->reward_amount : 0;
                    $order->grand_total -= $rewardAmount;
                    $order->reward_point_applied = 1;
                    $order->reward_point_discount = $rewardAmount;
                    $order->applied_reward_point = $rewardPoint;

                    $redeemaction = RewardRedeemAction::where('activity_type', 'checkout')->first();
                    if($redeemaction){
                        if($user){
                            $point = $rewardPoint;
                            $user->point_balance = $user->point_balance - $point;
                            if($user->save()){
                                $rewardlog = new RewardPointLog;
                                $rewardlog->user_id = $user->id;
                                $rewardlog->activity_id = $redeemaction->id;
                                $rewardlog->activity_type = 'Redeemed';
                                $rewardlog->activity = 'OrderPlaced';
                                $rewardlog->earned = 0;
                                $rewardlog->spent = $point;
                                $rewardlog->activity_str = 'Spent '. $point .' Reward Points for the order '.$order->code.'';
                                $rewardlog->purpose_id = $order->id;
                                $rewardlog->purpose_str = $order->code;
                                $rewardlog->save();
                            }
                        }
                    }
                }
            }

            $combined_order->grand_total += $order->grand_total;

            if (strpos($request->payment_type, "manual_payment_") !== false) { // if payment type like  manual_payment_1 or  manual_payment_25 etc)

                $order->manual_payment = 1;
                $order->save();

            }

            $order->save();

            // Adjust Ordered Product Stocks
            logOrder($order, 'created');
            event(new OrderPlaced($order));
            if (json_decode($order->shipping_address)?->phone ?? false) {
                CourierSuccessRateJob::dispatch(json_decode($order->shipping_address)?->phone ?? '');
            }
        }
        $combined_order->save();



        Cart::where('user_id', $request->user_id)->delete();

        if (
            $request->payment_type == 'cash_on_delivery'
            || $request->payment_type == 'wallet'
            || strpos($request->payment_type, "manual_payment_") !== false // if payment type like  manual_payment_1 or  manual_payment_25 etc
        ) {
            NotificationUtility::sendOrderPlacedNotification($order);
        }


        trackOrder($order->id, $request->all());
        return response()->json([
            'combined_order_id' => $combined_order->id,
            'result' => true,
            'message' => ('Your order has been placed successfully')
        ]);
    }

    public function updatePaymentStatus(Request $request)
    {
        $order = Order::where('id', $request->order_id)->where('user_id', $request->user_id)->first();
        if(!$order){
            return response()->json([
                'result' => false,
                'message' => ('Invalid order information')
            ]);
        }


        $order->payment_status_viewed = '0';
        $order->save();

        foreach ($order->orderDetails as $key => $orderDetail) {
            $orderDetail->payment_status = $request->status;
            $orderDetail->save();
        }

        $status = $request->status;
        foreach ($order->orderDetails as $key => $orderDetail) {
            if ($orderDetail->payment_status != $request->status) {
                $status = 'unpaid';
            }
        }

        $order->payment_status = $status;
        $order->save();


        if ($order->payment_status == 'paid' && $order->commission_calculated == 0) {
            calculateCommissionAffilationClubPoint($order);
        }

        //sends Notifications to user
        NotificationUtility::sendNotification($order, $request->status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order updated !";
            $status = str_replace("_", "", $order->payment_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }
        logOrder($order, 'payment_status');
        return response()->json([
            'result' => true,
            'message' => ('Payment status updated successfully')
        ]);
    }
}
