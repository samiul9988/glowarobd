<?php

namespace App\Http\Controllers\Api\V3;

use \App\Utility\NotificationUtility;
use App\Events\OrderPlaced;
use App\Events\ProductStockAffected;
use App\Http\Controllers\AffiliateController;
use App\Http\Resources\V3\OrderDetailsResource;
use App\Http\Resources\V3\PurchaseHistoryCollection;
use App\Jobs\CourierSuccessRateJob;
use App\Models\Address;
use App\Models\BusinessSetting;
use App\Models\Cart;
use App\Models\CombinedOrder;
use App\Models\Coupon;
use App\Models\CouponCustomerAssignment;
use App\Models\CouponUsage;
use App\Models\GiftOfferItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RewardPointLog;
use App\Models\RewardRedeemAction;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class OrderController extends Controller
{
    public function store(Request $request, $set_paid = false)
    {
        $source = $request->header('source', 'app') ?? 'web';

        $guestOrder = false;
        $temp_user_id = temp_user_id($request->user_id);
        if($temp_user_id) {
            $guestOrder = true;
            replace_temp_user_id($temp_user_id, $request->user_id);
        }

        $allCarts = Cart::withoutGlobalScopes()
            ->where('user_id', $request->user_id)
            ->get();

        $allCarts = validateCarts($allCarts);

        // Validating shipping cost, if shipping cost is zero then recalculate and update it before placing order
        $firstCart = $allCarts->first();
        $shippingCost = $firstCart->shipping_cost ?? 0;
        if ($firstCart && $shippingCost == 0) {
            Log::channel('custom')->info('Zero Shipping Cost', ['cart' => $firstCart->toArray(), 'grand_total' => $allCarts->sum(fn($cart) => $cart->price * $cart->quantity)]);
            $reCalculatedShippingCost = $this->recalculateShippingCost($allCarts);
            Log::channel('custom')->info('Re-Calculated Shipping Cost: '. $reCalculatedShippingCost);
            if ($reCalculatedShippingCost > 0) {
                $shippingCost = $this->store_delivery_info($allCarts);
            }
        }
        // End of shipping cost validation and update

        $cartItems = $allCarts->where('cart_type', 'regular')->values();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'combined_order_id' => 0,
                'result' => false,
                'message' => 'Cart is Empty'
            ]);
        }

        $cartCouponDiscountData = check_coupon_discount($request->user_id, $allCarts);
        if(!empty($cartCouponDiscountData) && !$cartCouponDiscountData['result']){
            return response()->json([
                'combined_order_id' => 0,
                'result' => false,
                'message' => ($cartCouponDiscountData['message'])
            ]);
        };

        $user = User::with('customeringroup.group')->find($request->user_id) ?? null;

        $address = Address::where('id', $cartItems->first()->address_id)->first();
        $shippingAddress = [];
        if ($address && $user) {
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

        if (empty(array_filter($shippingAddress))) {
            return response()->json([
                'combined_order_id' => 0,
                'result' => false,
                'message' => 'Shipping address is incomplete. Please update shipping address and try again.'
            ]);
        }

        $combined_order = new CombinedOrder;
        $combined_order->user_id = $user->id;
        $combined_order->shipping_address = json_encode($shippingAddress);
        $combined_order->save();

        $seller_products = array();
        foreach ($allCarts as $cartItem) {
            $product_ids = array();
            $product = Product::find($cartItem['product_id']);
            if (isset($seller_products[$product->user_id])) {
                $product_ids = $seller_products[$product->user_id];
            }
            array_push($product_ids, $cartItem);
            $seller_products[$product->user_id] = $product_ids;
        }

        $shippingDiscountAmount = PHP_INT_MAX;
        if (check_shipping_discount()) {
            $matchZone = $address ? ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$address->area_id ?? null])->first() : ShippingZone::where('rest_of_the_world', 1)->first();
            $shippingDiscountAmount = getDiscountShippingCharge($allCarts, $matchZone->id ?? null);
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
            $order->guest_order = $guestOrder;
            $order->payment_status = $set_paid ? 'paid' : 'unpaid';
            $notes = [];
            if(filled($request->note)){
                $notes[] = [
                    'message' => strip_tags($request->note),
                    'created_by' => auth('api')->user()->id ?? $user->id,
                ];
                $order->notes = $notes;
            }
            $order->order_source = strtoupper($request->order_source);
            // Pre-order
            $order->order_type = 'regular';
            if(has_preorder_product_to_cart($cartItems)){
                $order->order_type = 'preorder';
                $order->delivery_status = 'preorder';
            }

            $order->save();

            $subtotal = 0;
            $giftSubtotal = 0;
            $tax = 0;
            $shipping = 0;
            $coupon_discount = 0;

            //Order Details Storing
            foreach ($seller_product as $index => $cartItem) {
                $product = Product::with('stocks', 'flash_deal_product.flash_deals')->find($cartItem['product_id']);
                $giftOfferItem = GiftOfferItem::with('giftOffer')->find($cartItem['gift_offer_item_id']);

                $product_variation = $cartItem['variation'];
                $product_stock = $product->stocks->where('variant', $product_variation)->first();

                $lastPurchaseItem = $product->getLastPurchaseOrderItemByVariant($product_variation);
                if ($lastPurchaseItem) {
                    $lastPurchasePrice = $lastPurchaseItem->price;
                } else {
                    $lastPurchasePrice = 0;
                }

                $isRegularCart = data_get($cartItem, 'cart_type') === 'regular';
                $isDigital = $product->digital == 1;
                $requestedQty = $cartItem['quantity'];
                $availableQty = $product_stock->qty;
                $isOutOfStock = !$isDigital && $requestedQty > $availableQty;

                if ($isRegularCart) {
                    $cartItem['price'] = getMinimumPriceByVariant($product, $product_stock, $source, $requestedQty, $user);
                    $subtotal += $cartItem['price'] * $requestedQty;
                    $coupon_discount += $cartItem['discount'];
                } else if ($giftOfferItem) {
                    $giftSubtotal += $cartItem['price'] * $requestedQty;
                }
                $tax += $cartItem['tax'] * $requestedQty;

                if(!check_preorder_product($product)){
                    if ($isRegularCart && $isOutOfStock && $product->allow_stock_out_purchases == 0) {
                        $order->delete();
                        $combined_order->delete();
                        return response()->json([
                            'combined_order_id' => 0,
                            'result' => false,
                            'message' => ('The requested quantity is not available for ') . $product->name
                        ]);
                    } elseif (!$isDigital) {
                        if (!$isRegularCart && $giftOfferItem) {
                            $isGiftOutOfStock = $requestedQty > $availableQty && $requestedQty > $giftOfferItem->available_qty;
                            if ($isGiftOutOfStock) {
                                $giftOfferItem->available_qty = $availableQty;
                                $giftOfferItem->save();
                                $order->delete();
                                $combined_order->delete();
                                return response()->json([
                                    'combined_order_id' => 0,
                                    'result' => false,
                                    'message' => 'The requested quantity is not available for gift offer item ' . $product->name
                                ]);
                            }
                            $giftOfferItem->available_qty = max(0, $giftOfferItem->available_qty - $requestedQty);
                            $giftOfferItem->used_qty += $requestedQty;
                            $giftOfferItem->save();
                        }
                        $product_stock->qty -= $requestedQty;
                        $product_stock->save();

                        $isFlashDealProduct = check_flash_deal_product($product);
                        $flashDealProduct = $product->flash_deal_product ?? null;
                        if ($isRegularCart && $isFlashDealProduct && $flashDealProduct) {
                            $flashDealProductQuantity = $flashDealProduct->quantity ?? 0;
                            if ($flashDealProductQuantity > 0) {
                                $flashDealProductQuantity -= $requestedQty;
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
                            'qty'           => abs($requestedQty),
                            'isAddition'    => ($isAddition) ? 1 : 0,
                            'isSubtraction' => ($isAddition) ? 0 : 1,
                            'purpose'       => 'sales',
                            'purpose_id'    => $order->id ?? 0,
                            'note'          => 'New ' . $source . ' Sales, Ref. ID = '.$order->code ?? 'Unknown'.''
                        ];
                        // Trigger The Event
                        event(new ProductStockAffected($transaction));
                    }
                }else{
                    if ($isRegularCart && !$isDigital && $requestedQty > ($product->preorder_max_qty - preorder_product_count($product))) {
                        $order->delete();
                        $combined_order->delete();
                        return response()->json([
                            'combined_order_id' => 0,
                            'result' => false,
                            'message' => 'The requested pre-order quantity is not available for ' . $product->name
                        ]);
                    }
                }

                $order_detail = new OrderDetail;
                $order_detail->order_id = $order->id;
                $order_detail->seller_id = $product->user_id;
                $order_detail->product_id = $product->id;
                $order_detail->gift_offer_id = $giftOfferItem ? $giftOfferItem->gift_offer_id : null;
                $order_detail->gift_offer_item_id = $giftOfferItem ? $giftOfferItem->id : null;
                $order_detail->product_type = $giftOfferItem ? 'gift' : 'regular';
                $order_detail->variation = empty($product_variation) ? null : $product_variation;
                $order_detail->price = $cartItem['price'] * $cartItem['quantity'];
                $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                $order_detail->shipping_type = $cartItem['shipping_type'];
                $order_detail->shipping_method = $cartItem['shipping_method'];
                $order_detail->coupon_code = $cartItem['coupon_code'];
                $order_detail->product_referral_code = $cartItem['product_referral_code'];
                $order_detail->shipping_cost = $index == 0 ? min($shippingDiscountAmount, $cartItem['shipping_cost'] ?: $shippingCost ?: 0) : 0;
                $order_detail->last_purchase_price = $lastPurchasePrice > 0 ? $lastPurchasePrice : $cartItem['price'];

                // Pre-order
                $order_detail->delivery_status = 'pending';
                if($isRegularCart && has_preorder_product_to_cart($cartItems)){
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

            $order->gift_offer_total = $giftSubtotal;
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

            $combined_order->grand_total += $order->grand_total + $order->gift_offer_total;

            if (strpos($request->payment_type, "manual_payment_") !== false) {
                // if payment type like  manual_payment_1 or  manual_payment_25 etc)
                $order->manual_payment = 1;
                $order->save();
            }

            $order->save();

            logOrder($order, 'created');

            // Adjust Ordered Product Stocks
            event(new OrderPlaced($order));
            if (json_decode($order->shipping_address)?->phone ?? false) {
                CourierSuccessRateJob::dispatch(json_decode($order->shipping_address)?->phone ?? '');
            }
        }
        $combined_order->save();

        Cart::withoutGlobalScopes()
            ->where('user_id', $request->user_id)
            ->delete();

        if ($request->payment_type == 'cash_on_delivery' || $request->payment_type == 'wallet' || strpos($request->payment_type, "manual_payment_") !== false) {
            NotificationUtility::sendOrderPlacedNotification($order);
        }

        trackOrder($order->id, $request->all());

        return response()->json([
            'combined_order_id' => $combined_order->id,
            'order_id' => $order->id ?? 0,
            'result' => true,
            'message' => 'Your order has been placed successfully'
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
            $status = str_replace("_", "", $order->payment_status);
            $request->merge([
                'device_token' => $order->user->device_token,
                'title' => "Order updated !",
                'text' => " Your order {$order->code} has been {$status}",
                'type' => "order",
                'id' => $order->id,
                'user_id' => $order->user->id,
            ]);
            NotificationUtility::sendFirebaseNotification($request);
        }

        logOrder($order, 'payment_status');
        return response()->json([
            'result' => true,
            'message' => ('Payment status updated successfully')
        ]);
    }

    public function update_order_status(Request $request)
    {
        // dd($request->all());
        try {
            $combined_order_id = $request->combined_order_id;
            $combined_order = CombinedOrder::findOrFail($combined_order_id);
            $order_amount = 0;

            foreach ($combined_order->orders as $key => $order) {
                $order = Order::findOrFail($order->id);
                $order_amount = get_order_grand_total($order);

                // Newly added code for store payment details
                $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
                $pinv = "PAY-" . date('Ymd') . ($inv_counting + 1);

                $method = $request->payment_method;
                $pdetails = [
                    'payment_method' => $method,
                    'bank_type' => $request->bank_type ?? null,
                    'bank_info' => $method,
                    'payment_amount' => $order_amount,
                ];

                DB::beginTransaction();
                $payment = new Payment();
                $payment->invoice_no = $pinv;
                $payment->date = date('Y-m-d');
                $payment->payable_id = $order->user_id;
                $payment->payable_type = User::class;
                $payment->reference_id = $order->id;
                $payment->reference_type = Order::class;
                $payment->seller_id = null;
                $payment->amount = $order_amount;
                $payment->payment_details = json_encode($pdetails);
                $payment->payment_method = $method;
                $payment->txn_code = $request->txn_code;
                $payment->user_id = auth()->user()?->id ?? null;
                $payment->remarks = "Payment for Order #" . $order->code;
                $payment->save();

                $order->payment_status = 'paid';
                $order->due_amount = 0;
                $order->payment_details = $payment;
                $order->save();
                DB::commit();

                calculateCommissionAffilationClubPoint($order);
            }
            return response()->json([
                'success' => true,
                'message' => translate('Order status updated successfully!'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Payment failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function trackOrder(Request $request)
    {
        $order = Order::where('code', $request->code)->first();
        if (!$order) {
            return response()->json([
                'result' => false,
                'message' => 'Order not found'
            ], 404);
        }
        return OrderDetailsResource::make($order);
    }

    private function store_delivery_info($carts): int
    {
        if ($carts->isEmpty()) {
            return 0;
        }

        $addressId = $carts->first()->address_id ?? null;
        if (! $addressId) {
            return 0;
        }

        $shipping_info = Address::where('id', $addressId)->first();
        $total = 0;
        $tax = 0;
        $shipping = 0;
        $subtotal = 0;

        $shippingCalByOwner = [];

        $dShippingAmount = PHP_INT_MAX;

        if (check_shipping_discount()) {
            $addressInfo = Address::find($carts->first()->address_id);
            $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
            $sDiscount = check_shipping_discount_carts($carts, $matchZone->id ?? null);
            if (! empty($sDiscount) && $sDiscount['status']) {
                $cartAmount = $carts->sum(function ($cart) {
                    return $cart->price * $cart->quantity;
                });
                if ($cartAmount >= $sDiscount['min_amount']) {
                    $dShippingAmount = $sDiscount['amount'];
                }
            }
        }

        $uniqueProductIds = $carts->pluck('product_id')->unique();
        $products = \App\Models\Product::whereIn('id', $uniqueProductIds)->get()->keyBy('id');
        foreach ($carts as $key => $cartItem) {
            $product = $products[$cartItem['product_id']];
            $tax += ($cartItem['tax'] * $cartItem['quantity']);
            $subtotal += ($cartItem['price'] * $cartItem['quantity']);
            $cartItem['shipping_type'] = 'home_delivery';
            $cartItem['shipping_method'] = ShippingMethod::where('status', 1)->first()?->id ?? null;

            $cartItem->save();

            $cartItem['shipping_cost'] = 0;
            if ($cartItem['shipping_type'] == 'home_delivery') {
                // Add this condition so single time shipping charge for product owner wise
                if (! in_array($cartItem['owner_id'], $shippingCalByOwner)) {
                    $shippingCalByOwner[] = $cartItem['owner_id'];
                    // $cartItem['shipping_cost'] = getShippingCost($carts, $key);
                    $prevShip = getShippingCost($carts, $key);
                    // $sCalculation = ($prevShip < $dShippingAmount) ? 0 : $prevShip - $dShippingAmount;
                    $dShipping = min($prevShip, $dShippingAmount);
                    $cartItem['shipping_cost'] = abs($dShipping);
                }
            }

            if (isset($cartItem['shipping_cost']) && is_array(json_decode($cartItem['shipping_cost'], true))) {

                foreach (json_decode($cartItem['shipping_cost'], true) as $shipping_region => $val) {
                    if ($shipping_info['city'] == $shipping_region) {
                        // $cartItem['shipping_cost'] = (double)($val);
                        $cartItem['shipping_cost'] = min((float) ($dShippingAmount), (float) ($val));
                        break;
                    } else {
                        $cartItem['shipping_cost'] = 0;
                    }
                }
            } else {
                if (! $cartItem['shipping_cost'] ||
                        $cartItem['shipping_cost'] == null ||
                        $cartItem['shipping_cost'] == 'null') {

                    $cartItem['shipping_cost'] = 0;
                }
            }

            $shipping += $cartItem['shipping_cost'];
            $cartItem->save();

        }

        return $shipping;
    }

    private function recalculateShippingCost($carts):int {
        if ($carts->isEmpty()) { return 0; }

        $addressId = $carts->first()->address_id ?? null;
        if (! $addressId) { return 0; }

        $address = Address::find($addressId);
        $total = 0;
        $tax = 0;
        $shipping = 0;
        $subtotal = 0;

        $shippingCalByOwner = [];

        $dShippingAmount = PHP_INT_MAX;

        if (check_shipping_discount()) {
            $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$address->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
            $sDiscount = check_shipping_discount_carts($carts, $matchZone->id ?? null);
            if (! empty($sDiscount) && $sDiscount['status']) {
                $cartAmount = $carts->sum(function ($cart) {
                    return $cart->price * $cart->quantity;
                });
                if ($cartAmount >= $sDiscount['min_amount']) {
                    $dShippingAmount = $sDiscount['amount'];
                }
            }
        }

        foreach ($carts as $key => $cartItem) {
            $tax += ($cartItem['tax'] * $cartItem['quantity']);
            $subtotal += ($cartItem['price'] * $cartItem['quantity']);
            $cartItem['shipping_type'] = 'home_delivery';
            $cartItem['shipping_method'] = ShippingMethod::where('status', 1)->first()?->id ?? null;

            $cartItem['shipping_cost'] = 0;
            if ($cartItem['shipping_type'] == 'home_delivery') {
                if (! in_array($cartItem['owner_id'], $shippingCalByOwner)) {
                    $shippingCalByOwner[] = $cartItem['owner_id'];
                    $prevShip = getShippingCost($carts, $key);
                    $dShipping = min($prevShip, $dShippingAmount);
                    $cartItem['shipping_cost'] = abs($dShipping);
                }
            }

            if (isset($cartItem['shipping_cost']) && is_array(json_decode($cartItem['shipping_cost'], true))) {
                foreach (json_decode($cartItem['shipping_cost'], true) as $shipping_region => $val) {
                    if ($address->city == $shipping_region) {
                        $cartItem['shipping_cost'] = min((float) ($dShippingAmount), (float) ($val));
                        break;
                    } else {
                        $cartItem['shipping_cost'] = 0;
                    }
                }
            } else {
                if (! $cartItem['shipping_cost'] ||
                        $cartItem['shipping_cost'] == null ||
                        $cartItem['shipping_cost'] == 'null') {
                    $cartItem['shipping_cost'] = 0;
                }
            }

            $shipping += $cartItem['shipping_cost'];
        }

        return $shipping;
    }
}
