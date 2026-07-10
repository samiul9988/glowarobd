<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Address;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Category;
use App\Models\CouponUsage;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use App\Mail\PaymentInfoMail;
use App\Models\CombinedOrder;
use App\Utility\PayfastUtility;
use App\Utility\PayhereUtility;
use App\Models\RewardRedeemAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Utility\NotificationUtility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Models\CouponCustomerAssignment;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaytmController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\InstamojoController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\PublicSslCommerzPaymentController;

class CheckoutController extends Controller
{
    public $auth_id;
    public $is_added_dp;
    public function __construct()
    {
        $this->is_added_dp = false;
        // $this->middleware('auth');
        // $this->middleware(function ($request, $next) {
        //     $this->id = Auth::user()->id;
        //     $this->auth_id = $this->id;
        // });
    }

    //check the selected payment gateway and redirect to that controller accordingly
    public function checkout(Request $request)
    {
        if($request->address_id == 0 || empty($request->address_id)){
            flash(('Please Select or Add Shipping Address.'))->warning();
            return back();
        }

        if ($request->payment_option != null) {
            (new OrderController)->store($request);

            $request->session()->put('payment_type', 'cart_payment');

            if ($request->session()->get('combined_order_id') != null) {
                if ($request->payment_option == 'paypal') {
                    $paypal = new PaypalController;
                    return $paypal->getCheckout();
                } elseif ($request->payment_option == 'stripe') {
                    $stripe = new StripePaymentController;
                    return $stripe->stripe();
                } elseif ($request->payment_option == 'sslcommerz') {
                    $sslcommerz = new PublicSslCommerzPaymentController;
                    return $sslcommerz->index($request);
                } elseif ($request->payment_option == 'instamojo') {
                    $instamojo = new InstamojoController;
                    return $instamojo->pay($request);
                } elseif ($request->payment_option == 'razorpay') {
                    $razorpay = new RazorpayController;
                    return $razorpay->payWithRazorpay($request);
                } elseif ($request->payment_option == 'payku') {
                    return (new PaykuController)->create($request);
                } elseif ($request->payment_option == 'voguepay') {
                    $voguePay = new VoguePayController;
                    return $voguePay->customer_showForm();
                } elseif ($request->payment_option == 'ngenius') {
                    $ngenius = new NgeniusController();
                    return $ngenius->pay();
                } elseif ($request->payment_option == 'iyzico') {
                    $iyzico = new IyzicoController();
                    return $iyzico->pay();
                } elseif ($request->payment_option == 'nagad') {
                    $nagad = new NagadController;
                    return $nagad->getSession();
                } elseif ($request->payment_option == 'bkash') {
                    $bkash = new BkashController;
                    return $bkash->pay();
                } elseif ($request->payment_option == 'aamarpay') {
                    $aamarpay = new AamarpayController;
                    return $aamarpay->index();
                } elseif ($request->payment_option == 'flutterwave') {
                    $flutterwave = new FlutterwaveController();
                    return $flutterwave->pay();
                } elseif ($request->payment_option == 'mpesa') {
                    $mpesa = new MpesaController();
                    return $mpesa->pay();
                } elseif ($request->payment_option == 'paystack') {
                    if (addon_is_activated('otp_system') && !Auth::user()->email) {
                        flash(('Your email should be verified before order'))->warning();
                        return redirect()->route('cart')->send();
                    }
                    $paystack = new PaystackController;
                    return $paystack->redirectToGateway($request);
                } elseif ($request->payment_option == 'payhere') {
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));

                    $combined_order_id = $combined_order->id;
                    $amount = $combined_order->grand_total;
                    $first_name = json_decode($combined_order->shipping_address)->name;
                    $last_name = 'X';
                    $phone = json_decode($combined_order->shipping_address)->phone;
                    $email = json_decode($combined_order->shipping_address)->email;
                    $address = json_decode($combined_order->shipping_address)->address;
                    $city = json_decode($combined_order->shipping_address)->city;

                    return PayhereUtility::create_checkout_form($combined_order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
                } elseif ($request->payment_option == 'payfast') {
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));

                    $combined_order_id = $combined_order->id;
                    $amount = $combined_order->grand_total;

                    return PayfastUtility::create_checkout_form($combined_order_id, $amount);
                } elseif ($request->payment_option == 'paytm') {
                    if (Auth::user()->phone == null) {
                        flash('Please add phone number to your profile')->warning();
                        return redirect()->route('profile');
                    }

                    $paytm = new PaytmController;
                    return $paytm->index();
                } else if ($request->payment_option == 'authorizenet') {
                    $authorize_net = new AuthorizeNetController();
                    return $authorize_net->pay();
                } elseif ($request->payment_option == 'cash_on_delivery') {
                    flash(("Your order has been placed successfully"))->success();
                    return redirect()->route('order_confirmed');
                } elseif ($request->payment_option == 'wallet') {
                    $user = Auth::user();
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
                    if ($user->balance >= $combined_order->grand_total) {
                        $user->balance -= $combined_order->grand_total;
                        $user->save();
                        return $this->checkout_done($request->session()->get('combined_order_id'), null);
                    }
                } else {
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
                    foreach ($combined_order->orders as $order) {
                        $order->manual_payment = 1;
                        $order->save();
                    }
                    flash(('Your order has been placed successfully. Please submit payment information from purchase history'))->success();
                    return redirect()->route('order_confirmed');
                }
            }
        } else {
            flash(('Select Payment Option.'))->warning();
            return back();
        }
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

            $method = $order->payment_type;
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
                flash(('Payment failed. Please try again.'))->error();
            }

            $this->sendMail($order, $method);
        }

        Session::put('combined_order_id', $combined_order_id);
        // flash('Your order has been placed successfully')->success();
        return redirect()->route('order_confirmed');
    }

    public function checkout_doneapi($combined_order_id, $payment)
    {
        $combined_order = CombinedOrder::findOrFail($combined_order_id);
        foreach ($combined_order->orders as $key => $order) {
            $order = Order::findOrFail($order->id);

            // Newly added code for store payment details
            $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
            $pinv = "PAY-" . date('Ymd') . ($inv_counting + 1);

            $method = $order->payment_type;
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
                flash(('Payment failed. Please try again.'))->error();
            }
            $this->sendMail($order, $method);
        }
        Session::put('combined_order_id', $combined_order_id);
        return redirect()->route('aamarpay.done');
    }

    private function sendMail($order, $method)
    {
        $email = null;
        $shipping = json_decode($order->shipping_address, true);
        if($order->user_id && $order?->user?->email) {
            $email = $order->user->email;
        } else {
            $email = $shipping['email'] ?? null;
        }

        if($email){
            $info = [
                'customer' => ucwords($shipping['name'] ?? 'Customer'),
                'order_id' => $order->code,
                'payment_method' => ucfirst($method),
                'amount' => single_price(get_order_grand_total($order)),
                'payment_date' => date('d F Y'),
                'status' => ucfirst($order->payment_status ?? 'Paid'),
            ];
            try{
                if($order->payment_status !== 'unpaid'){
                    Mail::to($email)->queue(new \App\Mail\PaymentInfoMail($info));
                }
            } catch (\Exception $e) {
                Log::error('Mail sending failed after payment received for Order ID: '.$order->id.' - '.$e->getMessage());
            }
        }
    }

    public function get_shipping_info(Request $request)
    {
        if (Auth::check()) {
            $last_order = CombinedOrder::latest()->where('user_id', Auth::user()->id)->first();
            if ($last_order && $last_order->id === Session::get('combined_order_id')) {
                return redirect()->route('order_confirmed');
            }
        }

        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        $carts = Cart::with('product.stocks', 'product.productprices', 'product.brand', 'product.category')
            ->whereNotNull($request->user_field)
            ->where($request->user_field, $userId)
            ->get();

        $minordercheck = get_total_cart_amount_check($userId, $carts, !Auth::check() && get_setting('guest_order_activation') == 1);
        if($minordercheck['error'] == 1){
            flash($minordercheck['error_message'])->error();
            return redirect()->route('cart');
        }

        foreach ($carts as $key => $item) {
            if(check_discount_product_from_cart($item->product_id) == true){
                $item->discount = 0;
                $item->coupon_code = null;
                $item->save();
                // foreach($carts as $value){
                //     $get_single_product = Cart::findOrFail($value->id);
                //     $get_single_product->discount = 0;
                //     $get_single_product->coupon_code = null;
                //     $get_single_product->save();
                // }
            }
        }

        $shipping_charge = $carts->sum('shipping_cost');
        $discountShippingCharge = PHP_INT_MAX;
        if(!$carts->isEmpty()){
            $sDiscount = array();
            if(check_shipping_discount()){
                $addressInfo = Address::find($carts->first()->address_id) ?? Address::where($request->user_field, $userId)->orderBy('set_default', 'desc')->first();
                $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
                if ($matchZone && $shipping_charge == 0) {
                    $rates = json_decode($matchZone->rates, true);
                    if (!empty($rates)) {
                        usort($rates, function ($a, $b) {
                            return $a['price'] <=> $b['price'];
                        });
                        $shipping_charge = $rates[0]['price'] ?? 0;
                        $firstCart = $carts->first();
                        $firstCart->shipping_cost = $shipping_charge;
                        $firstCart->shipping_method = $rates[0]['id'] ?? null;
                        $firstCart->shipping_type = 'home_delivery';
                        $firstCart->save();
                    }
                }
                $sDiscount = check_shipping_discount_carts($carts, $matchZone->id ?? null);
                // $sDiscount = check_shipping_discount_product($pIDS, $matchZone->id ?? null);
                $discountShippingCharge = getDiscountShippingCharge($carts, $matchZone->id ?? null);
            }
        }else{
            $sDiscount = array();
        }

        // dd($addressInfo, $matchZone);
        $shipping_charge = min($shipping_charge, $discountShippingCharge);

        if ($carts && count($carts) > 0) {

            if(get_setting('spa_checkout') == 1){
                return view('frontend.spa_checkout.index', compact('carts', 'sDiscount', 'shipping_charge'));
            }else{
                return view('frontend.shipping_info', compact('carts', 'sDiscount', 'shipping_charge'));
            }
        }

        flash(('Your cart is empty'))->success();
        return redirect()->route('home');
    }

    public function continueAsGuest()
    {
        session()->put('agree_guest_checkout', true);
        return 1;
    }

    public function store_shipping_info(Request $request)
    {
        if ($request->address_id == null) {
            flash(("Please add shipping address"))->warning();
            return back();
        }
        $address = Address::find($request->address_id);
        if (!$address) {
            flash(("Address not found"))->warning();
            return back();
        }

        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        $carts = Cart::with('product.stocks', 'product.productprices')->where($request->user_field, $userId)->get();

        $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$address->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
        $rates = [];
        if ($matchZone) {
            $rates = json_decode($matchZone->rates, true);
        }

        $firstCartId = optional($carts->first())->id;
        foreach ($carts as $key => $cart) {
            if($cart->product->discount != null){
                // remove coupon from session
                // Session::forget('coupon_code');
            }
            $cart->address_id = $request->address_id;
            if ($cart->id === $firstCartId && !empty($rates)) {
                $cart->shipping_cost   = $rates[0]['price'] ?? null;
                $cart->shipping_type   = 'home_delivery';
                $cart->shipping_method = $rates[0]['id'] ?? null;
            }
            $cart->save();
        }

        if(get_setting('spa_checkout') == 1){
            return $this->store_delivery_info($request, $carts);
        }else{
            return view('frontend.delivery_info', compact('carts'));
        }
    }

    public function chechIfDiscountProductAdded(){
        $carts_products = Cart::with("product")->where('user_id', Auth::user()->id)->get();
        foreach ($carts_products as $key => $cartItem) {
            if($cartItem->product->discount != null){
                return $this->is_added_dp = true;
            }
        }
    }

    public function store_delivery_info(Request $request, $carts = null)
    {
        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        if(empty($carts)){
            $carts = Cart::with('product.stocks', 'product.productprices')->where($request->user_field, $userId)->get();
        }

        if($carts->isEmpty()) {
            flash(('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        if(get_setting('spa_checkout') == 1){
            $shipping_info = Address::where('id', $request->address_id)->first();
            if(empty($shipping_info)){
                $shipping_info = Address::where([$request->user_field => $userId, 'set_default' => 1])->first() ?? Address::where($request->user_field, $userId)->first();
            }
        }else{
            $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
        }

        $total = 0;
        $tax = 0;
        $shipping = 0;
        $subtotal = 0;

        $shippingCalByOwner = [];

        $dShippingAmount = PHP_INT_MAX;
        $discountShippingCharge = PHP_INT_MAX;
        $sDiscount = array();
        if(check_shipping_discount()){
            $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$shipping_info->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
            $discountShippingCharge = getDiscountShippingCharge($carts, $matchZone->id ?? null);

            // $sDiscount = check_shipping_discount_product($pIDS, $matchZone->id ?? null);
            $sDiscount = check_shipping_discount_carts($carts, $matchZone->id ?? null);
            if(!empty($sDiscount) && $sDiscount['status']){
                $cartAmount = 0;
                foreach($carts as $cart){
                    $cartAmount += $cart->price * $cart->quantity;
                }
                if($cartAmount >= $sDiscount['min_amount']){
                    $dShippingAmount = min($sDiscount['amount'], $discountShippingCharge);
                }
            }
        }

        if ($carts && count($carts) > 0) {
            $shipping_charge = min($carts->sum('shipping_cost'), $discountShippingCharge);

            foreach ($carts as $key => $cartItem) {
                $product = $cartItem->product;
                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $subtotal += $cartItem['price'] * $cartItem['quantity'];

                if ($request['shipping_type_' . $product->user_id] == 'pickup_point') {
                    $cartItem['shipping_type'] = 'pickup_point';
                    $cartItem['pickup_point'] = $request['pickup_point_id_' . $product->user_id];
                } else {
                    $cartItem['shipping_type'] = 'home_delivery';
                }

                if($cartItem['shipping_type']=='home_delivery'){
                    if ($request['shipping_method_' . $product->user_id] !== NULL) {
                        $cartItem['shipping_method'] = $request['shipping_method_' . $product->user_id];
                    }
                }

                $cartItem['shipping_cost'] = 0;
                if ($cartItem['shipping_type'] == 'home_delivery') {
                    // Add this condition so single time shipping charge for product owner wise
                    if(!in_array($cartItem['owner_id'],$shippingCalByOwner)){
                        $shippingCalByOwner[]=$cartItem['owner_id'];
                        // $cartItem['shipping_cost'] = getShippingCost($carts, $key);
                        $prevShip = getShippingCost($carts, $key);
                        $dShipping = min($prevShip, $dShippingAmount, $discountShippingCharge);
                        $cartItem['shipping_cost'] = abs($dShipping);
                    }
                }

                // dd($dShippingAmount);

                if(isset($cartItem['shipping_cost']) && is_array(json_decode($cartItem['shipping_cost'], true))) {

                    foreach(json_decode($cartItem['shipping_cost'], true) as $shipping_region => $val) {
                        if($shipping_info['city'] == $shipping_region) {
                            // $cartItem['shipping_cost'] = (double)($val);
                            $cartItem['shipping_cost'] = min((double)($dShippingAmount), (double)($val));
                            break;
                        } else {
                            $cartItem['shipping_cost'] = 0;
                        }
                    }
                } else {
                    if (!$cartItem['shipping_cost'] || $cartItem['shipping_cost'] == null || $cartItem['shipping_cost'] == 'null') {
                        $cartItem['shipping_cost'] = 0;
                    }
                }

                $shipping += $cartItem['shipping_cost'];

                // Check for coupon validity
                if ($cartItem['coupon_code'] != NULL || $cartItem['shipping_cost'] == null || $cartItem['shipping_cost'] == 'null') {
                    $coupon = Coupon::where('status', 1)->where('code', $cartItem['coupon_code'])->first();
                    if($coupon){
                        if (strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date) {
                            $cartItem['discount'] = $cartItem['discount'];
                        }else{
                            $cartItem['discount'] = 0;
                            $cartItem['coupon_code'] = NULL;
                            $cartItem['coupon_applied'] = 0;
                        }
                    }
                }
                $cartItem->save();
            }

            $total = $subtotal + $tax + $shipping;
            $minordercheck = get_total_cart_amount_check($userId, $carts, !Auth::check() && get_setting('guest_order_activation') == 1);

            if (get_setting('spa_checkout') == 1) {
                $user_addresses = Address::with('user', 'area', 'city', 'country', 'state')->whereNotNull($request->user_field)->where($request->user_field, $userId)->orderBy('set_default', 'desc')->get();
                $returnCartSummaryHTML = view('frontend.spa_checkout.view_cart', compact('carts', 'shipping_info', 'total', 'minordercheck', 'sDiscount', 'shipping_charge'))->render();
                $returnDeliveryInfo = view('frontend.spa_checkout.delivery_info', compact('user_addresses', 'carts', 'shipping_info', 'total', 'minordercheck', 'sDiscount', 'shipping_charge'))->render();
                return response()->json(array('html' => $returnCartSummaryHTML, 'deliveryHTML' => $returnDeliveryInfo));
            } else {
                return view('frontend.payment_select', compact('carts', 'shipping_info', 'total', 'minordercheck', 'sDiscount', 'shipping_charge'));
            }

        } else {
            flash(('Your Cart was empty'))->warning();
            return redirect()->route('home');
        }
    }

    public function apply_coupon_code(Request $request)
    {
        $coupon = Coupon::where('status', 1)->where('code', $request->code)->first();
        if(!$coupon) {
            return response()->json([
                'type' => 'danger',
                'message' => ('Invalid coupon code')
            ], 404);
        }

        $response = is_coupon_valid($coupon, Auth::check() ? Auth::id() : null);

        // dd($response);

        if($response['status'] === false){
            return response()->json([
                'type' => 'danger',
                'message' => $response['message']
            ], $response['code']);
        }

        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        $couponApplied = false;
        $response_message = array();
        $carts_products = Cart::where($request->user_field, $userId)->get();
        $coupon_apply = 'Yes';
        $discountShippingCharge = PHP_INT_MAX;

        // Group Discount Section
        if(Auth::check() && Auth::user()->customeringroup){
            $carts = Cart::where($request->user_field, $userId)->get();
            if(!$carts->isEmpty()){
                $sDiscount = array();
                if(check_shipping_discount()){
                    $addressInfo = Address::find($carts->first()->address_id);
                    $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
                    $discountShippingCharge = getDiscountShippingCharge($carts, $matchZone->id ?? null);
                    $sDiscount = check_shipping_discount_carts($carts, $matchZone->id ?? null);
                }
            }else{
                $sDiscount = array();
            }

            $discount_status = Auth::check() ? Auth::user()->customeringroup->group->discount_status : null;
            $start_date = Auth::check() ? Auth::user()->customeringroup->group->start_date : null;
            $end_date = Auth::check() ? Auth::user()->customeringroup->group->end_date : null;
            $cur_date = strtotime(date('Y-m-d H:i:s'));
            if($discount_status==1 && $cur_date >= $start_date && $cur_date <= $end_date && $coupon->force_apply == 0){
                $coupon_apply = 'No';
                $response_message['response'] = 'danger';
                $response_message['message'] = ("You already have a group discount!");
                $response_message['type'] = 'coupon';
                $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
                $shipping_charge = min($carts->sum('shipping_cost'), $discountShippingCharge);
                if (get_setting('spa_checkout') == 1){
                    $returnHTML = view('frontend.spa_checkout.view_cart', compact('coupon', 'carts', 'shipping_info', 'response_message', 'sDiscount', 'couponApplied', 'shipping_charge'))->render();
                }else{
                    $returnHTML = view('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info', 'sDiscount', 'couponApplied', 'shipping_charge'))->render();
                }
                return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
            }
        }
        // Group Discount Section

        foreach ($carts_products as $key => $item){
            if(check_discount_product_from_cart($item->product_id) == true && $coupon->force_apply == 0){
                $carts = Cart::where('user_id', Auth::user()->id)->get();
                if(!$carts->isEmpty()){
                    $sDiscount = array();
                    if(check_shipping_discount()){
                        $addressInfo = Address::find($carts->first()->address_id);
                        $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
                        $discountShippingCharge = getDiscountShippingCharge($carts, $matchZone->id ?? null);
                        $sDiscount = check_shipping_discount_carts($carts, $matchZone->id ?? null);
                    }
                }else{
                    $sDiscount = array();
                }
                $response_message['response'] = 'danger';
                $response_message['message'] = ("Coupon Can't Be Applied On Discounted Products Or With Discounted Product");
                $response_message['type'] = 'coupon';
                $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
                $shipping_charge = min($carts->sum('shipping_cost'), $discountShippingCharge);
                if (get_setting('spa_checkout') == 1){
                    $returnHTML = view('frontend.spa_checkout.view_cart', compact('coupon', 'carts', 'shipping_info', 'response_message', 'sDiscount', 'couponApplied', 'shipping_charge'))->render();
                }else{
                    $returnHTML = view('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info', 'sDiscount', 'couponApplied', 'shipping_charge'))->render();
                }
                return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
            }
        }

        if ($coupon != null) {
            if (strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date) {
                $pass=true;
                if($coupon->usage_limit=='single'){
                    if(CouponUsage::where('user_id', Auth::user()->id)->where('coupon_id', $coupon->id)->first() !== null)
                        $pass = false;
                }else{
                    // for multiple
                }
                if ($pass) {
                    $coupon_details = json_decode($coupon->details);
                    $carts = Cart::where('user_id', Auth::user()->id)
                                    ->where('owner_id', $coupon->user_id)
                                    ->get();

                    if ($coupon->type == 'cart_base') {
                        $subtotal = 0;
                        $tax = 0;
                        $shipping = 0;
                        foreach ($carts as $key => $cartItem) {
                            $subtotal += $cartItem['price'] * $cartItem['quantity'];
                            //$tax += $cartItem['tax'] * $cartItem['quantity'];
                            //$shipping += $cartItem['shipping_cost'];
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
                        }else{
                            $response_message['response'] = 'danger';
                            $response_message['message'] = ("Minimum order amount needed to apply this coupon!");
                            $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
                            $sDiscount = array();
                            $shipping_charge = $carts->sum('shipping_cost');
                            if (get_setting('spa_checkout') == 1){
                                $returnHTML = view('frontend.spa_checkout.view_cart', compact('coupon', 'carts', 'shipping_info', 'sDiscount', 'shipping_charge'))->render();
                            } else {
                                $returnHTML = view('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info', 'sDiscount', 'shipping_charge'))->render();
                            }

                            return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
                        }
                    } elseif ($coupon->type == 'product_base') {
                        $coupon_discount = 0;
                        foreach ($carts as $key => $cartItem) {
                            foreach ($coupon_details as $key => $coupon_detail) {
                                if ($coupon_detail->product_id == $cartItem['product_id']) {
                                    if ($coupon->discount_type == 'percent') {
                                        $coupon_discount += ($cartItem['price'] * $coupon->discount / 100) * $cartItem['quantity'];
                                    } elseif ($coupon->discount_type == 'amount') {
                                        $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                    }
                                }
                            }
                        }
                    }

                    Cart::where('user_id', Auth::user()->id)
                        ->where('owner_id', $coupon->user_id)
                        ->update([
                            'discount' => $coupon_discount / count($carts),
                            'coupon_code' => $request->code,
                            'coupon_applied' => 1
                        ]);

                    $response_message['response'] = 'success';
                    $response_message['message'] = ('Coupon Successfully Applied');
                    $response_message['type'] = 'coupon';
                    $couponApplied = true;
                } else {
                    $response_message['response'] = 'warning';
                    $response_message['message'] = ('You already used this coupon!');
                    $response_message['type'] = 'coupon';
                }

            } else {
                $response_message['response'] = 'warning';
                $response_message['message'] = ('Coupon expired!');
                $response_message['type'] = 'coupon';
            }
        } else {
            $response_message['response'] = 'danger';
            $response_message['message'] = ('Invalid coupon!');
            $response_message['type'] = 'coupon';
        }

        $carts = Cart::where('user_id', Auth::user()->id)->get();
        if(!$carts->isEmpty()){
            $sDiscount = array();
            if(check_shipping_discount()){
                $addressInfo = Address::find($carts->first()->address_id);
                $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
                $discountShippingCharge = getDiscountShippingCharge($carts, $matchZone->id ?? null);
                $sDiscount = check_shipping_discount_carts($carts, $matchZone->id ?? null);
            }
        }else{
            $sDiscount = array();
        }
        $shipping_info = Address::where('id', $carts[0]['address_id'])->first() ?? null;

        $shipping_charge = min($carts->sum('shipping_cost'), $discountShippingCharge);
        if (get_setting('spa_checkout') == 1){
            $returnHTML = view('frontend.spa_checkout.view_cart', compact('coupon', 'carts', 'shipping_info', 'response_message', 'sDiscount', 'couponApplied', 'shipping_charge'))->render();
        }else{
            $returnHTML = view('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info', 'sDiscount', 'couponApplied', 'shipping_charge'))->render();
        }
        // $returnHTML = view('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info'))->render();
        return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
    }

    public function remove_coupon_code(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        Cart::where($request->user_field, $userId)
            ->update([
                'discount' => 0.00,
                'coupon_code' => '',
                'coupon_applied' => 0
            ]);

        $coupon = Coupon::where('status', 1)->where('code', $request->code)->first();
        $carts = Cart::where($request->user_field, $userId)->get();
        $discountShippingCharge = PHP_INT_MAX;
        if(!$carts->isEmpty()){
            $sDiscount = array();
            if(check_shipping_discount()){
                $addressInfo = Address::find($carts->first()->address_id);
                $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
                $discountShippingCharge = getDiscountShippingCharge($carts, $matchZone->id ?? null);
                $sDiscount = check_shipping_discount_carts($carts, $matchZone->id ?? null);
            }
        }else{
            $sDiscount = array();
        }

        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
        $shipping_charge = min($carts->sum('shipping_cost'), $discountShippingCharge);
        if (get_setting('spa_checkout') == 1){
            return view('frontend.spa_checkout.view_cart', compact('coupon', 'carts', 'shipping_info', 'sDiscount', 'shipping_charge'));
        }else{
            return view('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info', 'sDiscount', 'shipping_charge'));
        }
    }

    public function apply_club_point(Request $request) {
        if (addon_is_activated('club_point')){

            $point = $request->point;

            if(Auth::user()->point_balance >= $point) {
                $request->session()->put('club_point', $point);
                flash(('Point has been redeemed'))->success();
            }
            else {
                flash(('Invalid point!'))->warning();
            }
        }
        return back();
    }

    public function remove_club_point(Request $request) {
        $request->session()->forget('club_point');
        return back();
    }

    public function order_confirmed()
    {
        // dd(Session::get('combined_order_id'));
        $combined_order_id = Session::get('combined_order_id');
        if($combined_order_id == null){
            flash(translate('Your order already has been placed.'))->warning();
            return redirect()->route('purchase_history.index');
        }
        $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));

        Cart::where('user_id', $combined_order->user_id)
                ->delete();

        //Session::forget('club_point');
        Session::forget('combined_order_id');
        Session::forget('reward_point_discount');
        Session::forget('applied_reward_point');

        foreach($combined_order->orders as $order){
            NotificationUtility::sendOrderPlacedNotification($order);
        }

        return view('frontend.order_confirmed', compact('combined_order'));
    }

    public function apply_reward_point(Request $request) {
        if (get_setting('reward_point_system') == 1){
            $carts = Cart::where('user_id', Auth::user()->id)->get();

            if(!$carts->isEmpty()){
                $sDiscount = array();
                if(check_shipping_discount()){
                    $addressInfo = Address::find($carts->first()->address_id);
                    $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
                    $sDiscount = check_shipping_discount_carts($carts, $matchZone->id ?? null);
                }
            }else{
                $sDiscount = array();
            }

            $redeemactiontype = $request->has('redeem_reward_type') ? $request->redeem_reward_type : 'checkout';
            $redeemaction = RewardRedeemAction::where('activity_type', $redeemactiontype)->first();

            $point = $request->point;

            $response_message = array();

            $currentDateTime = new DateTime();
            $timestamp = auth()->user()->reward_point_expires_at;

            if((Auth::user()->point_balance >= $point) && ($currentDateTime <= new DateTime ($timestamp))) {
                $rewardamount = convert_point_to_amount($redeemaction, $point);
                $request->session()->put('reward_point_discount', $rewardamount);
                $request->session()->put('applied_reward_point', $point);

                $carts = Cart::where('user_id', Auth::user()->id)->get();
                $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

                $response_message['response'] = 'success';
                $response_message['message'] = ("Point has been redeemed!");
                $response_message['type'] = "reward_point";

                // $returnHTML = view('frontend.partials.cart_summary', compact('carts', 'shipping_info'))->render();
                if (get_setting('spa_checkout') == 1){
                    $returnHTML = view('frontend.spa_checkout.view_cart', compact('carts', 'shipping_info', 'sDiscount'))->render();
                }else{
                    $returnHTML = view('frontend.partials.cart_summary', compact('carts', 'shipping_info', 'sDiscount'))->render();
                }
                return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML, 'success' => true,));
            }else {
                $response_message['response'] = 'warning';
                $response_message['message'] = ("Given invalid point");

                return response()->json([
                    'response_message' => $response_message,
                    'success' => false,
                ]);
            }
        }
    }

    public function remove_reward_point(Request $request) {
        $request->session()->forget('reward_point_discount');
        $request->session()->forget('applied_reward_point');

        $carts = Cart::where('user_id', Auth::user()->id)->get();
        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
        if(!$carts->isEmpty()){
            $sDiscount = array();
            if(check_shipping_discount()){
                $addressInfo = Address::find($carts->first()->address_id);
                $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
                $sDiscount = check_shipping_discount_carts($carts, $matchZone->id ?? null);
            }
        }else{
            $sDiscount = array();
        }

        $response_message = array();
        $response_message['response'] = 'success';
        $response_message['message'] = ("Point has been removed!");
        $response_message['type'] = "reward_point";

        // $returnHTML = view('frontend.partials.cart_summary', compact('carts', 'shipping_info'))->render();
        if (get_setting('spa_checkout') == 1){
            $returnHTML = view('frontend.spa_checkout.view_cart', compact('carts', 'shipping_info', 'sDiscount'))->render();
        }else{
            $returnHTML = view('frontend.partials.cart_summary', compact('carts', 'shipping_info', 'sDiscount'))->render();
        }
        return response()->json(array('response_message' => $response_message, 'html'=>$returnHTML));
    }

    public function spa_checkout(Request $request){

        $carts = Cart::where('user_id', Auth::user()->id)->get();
        if($carts->isEmpty()) {
            flash(('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        if ($carts && count($carts) > 0) {
            $shipping_charge = $carts->sum('shipping_cost');
            return view('frontend.spa_checkout.index', compact('carts', 'shipping_charge'));
        } else {
            flash(('Your Cart was empty'))->warning();
            return redirect()->route('home');
        }
    }
}
