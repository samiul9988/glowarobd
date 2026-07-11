<?php

namespace App\Http\Controllers;

use App\Events\OrderPlaced;
use App\Events\ProductStockAffected;
use App\Http\Resources\DefaultPosProductCollection;
use App\Http\Resources\PosProductCollection;
use App\Http\Resources\V3\GiftOfferCollection;
use App\Jobs\CourierSuccessRateJob;
use App\Mail\InvoiceEmailManager;
use App\Models\ACCBank;
use App\Models\Addon;
use App\Models\Address;
use App\Models\Area;
use App\Models\Barcode;
use App\Models\BusinessSetting;
use App\Models\City;
use App\Models\Color;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\CouponCustomerAssignment;
use App\Models\CouponUsage;
use App\Models\GiftOffer;
use App\Models\GiftOfferItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OtpConfiguration;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Shop;
use App\Models\State;
use App\Models\User;
use App\Utility\CategoryUtility;
use DivisionByZeroError;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use PDF;

class PosController extends Controller
{
    public function index()
    {
        // Log::info('Log created');
        if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            return view('pos.index');
        }
        else {
            if (get_setting('pos_activation_for_seller') == 1) {
                return view('pos.frontend.seller.pos.index');
            }
            else {
                flash(('POS is disable for Sellers!!!'))->error();
                return back();
            }
        }
    }

    public function searchOld(Request $request)
    {
        if ($request->mode === 'advance' && get_setting('enable_meilisearch') == 1) {
            return $this->meilisearch($request);
        }

        if(Auth::user() && Auth::user()->user_type === 'admin' || Auth::user()->user_type === 'staff'){
            $products = ProductStock::join('products','product_stocks.product_id', '=', 'products.id')
                        ->where('products.added_by', 'admin')
                        ->where('products.published', 1)
                        ->select('products.*','product_stocks.id as stock_id','product_stocks.variant','product_stocks.price as stock_price', 'product_stocks.qty as stock_qty', 'product_stocks.image as stock_image')
                        ->orderBy('products.created_at', 'desc');
            // $products = Product::where('added_by', 'admin')->where('published', '1');
        }
        else {
            $products = ProductStock::join('products','product_stocks.product_id', '=', 'products.id')
                        ->join('product_taxes','product_stocks.product_id', '=', 'product_taxes.product_id')
                        ->where('user_id', Auth::user()->id)
                        ->where('published', 1)
                        ->select('products.*','product_stocks.id as stock_id','product_stocks.variant','product_stocks.price as stock_price', 'product_stocks.qty as stock_qty', 'product_stocks.image as stock_image')
                        ->orderBy('products.created_at', 'desc');
            // $products = Product::where('user_id', Auth::user()->id)->where('published', '1');
        }

        if($request->category != null){
            $arr = explode('-', $request->category);
            if($arr[0] == 'category'){
                $category_ids = CategoryUtility::children_ids($arr[1]);
                $category_ids[] = $arr[1];
                $products = $products->whereIn('products.category_id', $category_ids);
            }
        }

        if($request->brand != null){
            $products = $products->where('products.brand_id', $request->brand);
        }

        if ($request->keyword != null) {
            $barcode = Barcode::where('code', $request->keyword)->first();
            $decodedBarcode = null;
            if ($barcode) {
                $decodedBarcode = \App\Helpers\BarcodeHelper::decode($barcode->value);
            }
            if ($decodedBarcode && isset($decodedBarcode['product_id'])) {
                $products = $products->where('products.id', $decodedBarcode['product_id']);
            } else {
                $products = $products->where('products.name', 'like', '%'.$request->keyword.'%')->orWhere('products.barcode', $request->keyword);
            }
        }

        $stocks = new DefaultPosProductCollection($products->paginate(18));
        $stocks->appends(['keyword' =>  $request->keyword,'category' => $request->category, 'brand' => $request->brand]);
        return $stocks;
    }

    public function search(Request $request)
    {
        if ($request->mode === 'advance' && get_setting('enable_meilisearch') == 1) {
            return $this->meilisearch($request);
        }

        $keyword = trim($request->keyword);
        $filterById = null;
        if (filled($keyword)) {
            $barcode = Barcode::where('code', $keyword)->first();
            $decodedBarcode = null;
            if ($barcode) {
                $decodedBarcode = \App\Helpers\BarcodeHelper::decode($barcode->value);
            }
            if ($decodedBarcode && isset($decodedBarcode['product_id'])) {
                $filterById = $decodedBarcode['product_id'];
            } else {
                $filterById = null;
            }
        }

        $products = Product::published()->latest('created_at');

        if (Auth::check() && Auth::user()?->user_type === 'admin' || Auth::user()?->user_type === 'staff') {
            $products->where('added_by', 'admin');
        } else {
            $products->where('user_id', Auth::id());
        }

        if ($filterById) {
            $products->where('id', $filterById);
        } else {
            $products->where(function($query) use ($keyword) {
                $query->where('name', 'like', '%'.$keyword.'%')
                      ->orWhere('barcode', $keyword);
            });
        }

        if(filled($request->category)){
            $arr = explode('-', $request->category);
            if($arr[0] == 'category'){
                $category_ids = CategoryUtility::children_ids($arr[1]);
                $category_ids[] = $arr[1];
                $products->whereIn('category_id', $category_ids);
            }
        }

        if(filled($request->brand)){
            $products->where('brand_id', $request->brand);
        }

        $products = new PosProductCollection($products->paginate(18));

        $products->additional(['keyword' => $request->keyword, 'category' => $request->category, 'brand' => $request->brand]);
        return $products;
    }

    // Using MeiliSearch
    public function meilisearch(Request $request)
    {
        $keyword = trim($request->keyword);
        $filterById = null;
        if (filled($keyword)) {
            $barcode = Barcode::where('code', $keyword)->first();
            $decodedBarcode = null;
            if ($barcode) {
                $decodedBarcode = \App\Helpers\BarcodeHelper::decode($barcode->value);
            }
            if ($decodedBarcode && isset($decodedBarcode['product_id'])) {
                $filterById = $decodedBarcode['product_id'];
            } else {
                $filterById = null;
            }
        }

        $products = Product::search($request->keyword ?: '');

        if (Auth::check() && Auth::user()?->user_type === 'admin' || Auth::user()?->user_type === 'staff') {
            $products = $products->where('added_by', 'admin');
        } else {
            $products = $products->where('user_id', Auth::id());
        }

        if ($filterById) {
            $products = $products->where('id', $filterById);
        }

        if(filled($request->category)){
            $arr = explode('-', $request->category);
            if($arr[0] == 'category'){
                $category_ids = CategoryUtility::children_ids($arr[1]);
                $category_ids[] = $arr[1];
                $products = $products->whereIn('category_id', $category_ids);
            }
        }

        if(filled($request->brand)){
            $products = $products->where('brand_id', $request->brand);
        }

        $products = $products->orderBy('created_at', 'desc');

        $products = new PosProductCollection($products->paginate(18));

        $products->additional(['keyword' => $request->keyword, 'category' => $request->category, 'brand' => $request->brand]);
        return $products;
    }

    public function addToCart(Request $request)
    {
        $stock = ProductStock::with('product')->find($request->stock_id);
        $product = $stock->product;

        $data = [
            'id' => $product->id,
            'stock_id' => $request->stock_id,
            'variant' => $stock->variant,
            'quantity' => max($product->min_qty ?? 1, 1),
            'type' => 'regular',
            'gift_offer_id' => null,
            'gift_offer_item_id' => null,
        ];

        if($stock->qty < $data['quantity']){
            return [
                'success' => 0,
                'message' => "This product doesn't have enough stock for minimum purchase quantity " . $data['quantity'],
                'view' => view('pos.cart')->render()
            ];
        }

        $tax = 0;
        $price = $stock->price;

        // discount calculation
        $discount_applicable = false;
        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        }
        elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
        }
        if ($discount_applicable) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'amount'){
                $price -= $product->discount;
            }
        }

        //tax calculation
        foreach ($product->taxes as $product_tax) {
            if($product_tax->tax_type == 'percent'){
                $tax += ($price * $product_tax->tax) / 100;
            }
            elseif($product_tax->tax_type == 'amount'){
                $tax += $product_tax->tax;
            }
        }

        $data['price'] = getMinimumPriceByVariant($product, $stock, 'web', $product->min_qty, null);
        $data['tax'] = $tax;

        // if($request->session()->has('pos.cart')){
        if(Session::has('pos.cart')){
            $foundInCart = false;
            $cart = collect();

            // foreach ($request->session()->get('pos.cart') as $key => $cartItem){
            foreach (Session::get('pos.cart') as $key => $cartItem){
                if($cartItem['id'] == $product->id && $cartItem['stock_id'] == $stock->id){
                    $foundInCart = true;
                    $loop_product = Product::with('stocks', 'productprices')->find($cartItem['id']);
                    $product_stock = $loop_product->stocks->where('variant', $cartItem['variant'])->first();

                    if($product_stock->qty >= ($cartItem['quantity'] + 1)){
                        $cartItem['quantity'] += 1;
                    }else{
                        return array('success' => 0, 'message' => ("This product doesn't have more stock."), 'view' => view('pos.cart')->render());
                    }
                }
                $cart->push($cartItem);
            }

            if (!$foundInCart) {
                $cart->push($data);
            }
            // $request->session()->put('pos.cart', $cart);
            Session::put('pos.cart', $cart);
        }
        else{
            $cart = collect([$data]);
            // $request->session()->put('pos.cart', $cart);
            Session::put('pos.cart', $cart);
        }

        // $request->session()->put('pos.cart', $cart);
        Session::put('pos.cart', $cart);

        return array('success' => 1, 'message' => '', 'view' => view('pos.cart')->render());
    }

    public function partialPayment(Request $request)
    {
        // $payments = collect($request->session()->get('pos.payments', []));
        $payments = collect(Session::get('pos.payments', []));
        $totalAmountPaid = $payments->sum('amount') ?? 0;
        $data = [
            'amount' => (float) $request->input('amount'),
            'method' => $request->input('method'),
            'bank_type' => $request->input('bank_type'),
            'bank' => $request->input('bank'),
            'note' => $request->input('note'),
        ];
        $payments->push($data);
        $totalAmountPaid += (float) $request->input('amount');
        // $request->session()->put('pos.payments', $payments);
        // $request->session()->put('pos.total_paid', $totalAmountPaid);
        Session::put('pos.payments', $payments);
        Session::put('pos.total_paid', $totalAmountPaid);
        return [
            'success' => 1,
            'message' => ('Payment added successfully.'),
            'cart_view' => view('pos.cart')->render(),
            'view' => view('pos.order_summary', [
                'lastOrderDate' => $this->getLastOrderDate(),
                'giftOffersView' => $this->getGiftOffersView()
            ])->render()
        ];
    }

    public function removePaidAmount(Request $request)
    {
        Session::forget('pos.payments');
        Session::forget('pos.total_paid');
        return [
            'success' => 1,
            'message' => ('Payment has been removed successfully.'),
            'cart_view' => view('pos.cart')->render(),
            'view' => view('pos.order_summary', [
                'lastOrderDate' => $this->getLastOrderDate(),
                'giftOffersView' => $this->getGiftOffersView()
            ])->render()
        ];
    }

    // Helper method to check session health
    public function checkSessionHealth(Request $request)
    {
        $sessionData = [
            'session_id' => session()->getId(),
            'cart_exists' => Session::has('pos.cart'),
            'cart_items' => Session::has('pos.cart') ? count(Session::get('pos.cart')) : 0,
            'session_driver' => config('session.driver'),
            'session_lifetime' => config('session.lifetime'),
        ];

        return response()->json([
            'success' => 1,
            'session_data' => $sessionData
        ]);
    }

    //updated the quantity for a cart item
    public function updateQuantity(Request $request)
    {
        // $cart = $request->session()->get('pos.cart', collect([]));
        $cart = Session::get('pos.cart', collect([]));
        $cart = $cart->map(function ($object, $key) use ($request) {
            if($key == $request->key){
                $product = Product::with('stocks')->find($object['id']);
                $product_stock = $product->stocks->where('id', $object['stock_id'])->first();

                if($product_stock->qty >= $request->quantity){
                    $object['quantity'] = $request->quantity;
                }else{
                    return array('success' => 0, 'message' => ("This product doesn't have more stock."), 'view' => view('pos.cart')->render());
                }
            }
            return $object;
        });
        $request->session()->put('pos.cart', $cart);
        // Session::put('pos.cart', $cart);

        return array('success' => 1, 'message' => '', 'view' => view('pos.cart')->render());
    }

    public function updateGiftQuantity(Request $request)
    {
        $carts = Session::get('pos.cart', collect([]));

        $key = $request->input('key');
        $action = $request->input('action');

        $cart = $carts->get($key);

        if (!$cart || $cart['type'] !== 'gift') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid cart item',
            ], 400);
        }

        $giftOfferItem = GiftOfferItem::with('giftOffer')->find($cart['gift_offer_item_id']);
        $giftOffer = $giftOfferItem->giftOffer;

        if (!$giftOfferItem || !$giftOffer || !$giftOffer->isValid()) {
            $carts->forget($key);
            return response()->json([
                'success' => false,
                'message' => 'Gift offer item is not valid or has expired',
            ], 400);
        }

        $product = Product::with('stocks')->find($cart['id']);

        if (! $product) {
            $giftOfferItem->available_qty = 0;
            $giftOfferItem->save();
            $carts->forget($key); // Remove cart item if product doesn't exist
            return response()->json([
                'success' => false,
                'message' => "This gift item is no longer available",
            ], 400);
        }

        $productStock = $product->stocks->where('variant', $cart['variant'])->first();
        if (!$productStock) {
            $productStock = $product->stocks->first();
        }

        $availableQty = $productStock->qty ?? 0;
        if ($availableQty <= 0) {
            $giftOfferItem->available_qty = 0;
            $giftOfferItem->save();
            $carts->forget($key); // Remove cart item if out of stock
            return response()->json([
                'success' => false,
                'message' => "This gift item is out of stock",
            ], 400);
        }

        // Calculate new quantity
        $newQuantity = $action === 'inc' ? $cart['quantity'] + 1 : $cart['quantity'] - 1;
        if ($action === 'inc' && $newQuantity > $availableQty) {
            return response()->json([
                'success' => false,
                'message' => "Only {$availableQty} items available for this gift offer",
                'available_qty' => $availableQty,
            ], 400);
        }

        if ($action === 'inc' && $cart['quantity'] >= $giftOffer->max_qty_per_order) {
            return response()->json([
                'success' => false,
                'message' => "You have already added the maximum allowed gift items quantity to your cart.",
            ], 400);
        }

        // Validate quantity
        if ($newQuantity <= 0) {
            $carts->forget($key); // Remove cart item if quantity is negative
            return response()->json([
                'success' => true,
                'message' => "Gift item removed from cart",
                'view' => view('pos.order_summary', [
                    'lastOrderDate' => $this->getLastOrderDate(),
                    'giftOffersView' => $this->getGiftOffersView()
                ])->render(),
            ]);
        }

        if ($newQuantity > $giftOfferItem->available_qty) {
            return response()->json([
                'success' => false,
                'message' => "Only {$giftOfferItem->available_qty} items available for this gift offer",
                'available_qty' => $giftOfferItem->available_qty,
            ], 400);
        }

        // Update cart and gift offer item usage
        $cart['quantity'] = $newQuantity;
        $carts->put($key, $cart);
        Session::put('pos.cart', $carts);

        return response()->json([
            'success' => true,
            'message' => 'Gift quantity updated',
            'view' => view('pos.order_summary', [
                'lastOrderDate' => $this->getLastOrderDate(),
                'giftOffersView' => $this->getGiftOffersView()
            ])->render(),
        ]);
    }

    //removes from Cart
    public function removeFromCart(Request $request)
    {
        if(Session::has('pos.cart')){
            $carts = Session::get('pos.cart', collect([]));
            $carts->forget($request->key);

            $cartTotal = $carts->sum(fn($cart) => ($cart['price'] + ($cart['tax'] ?? 0)) * $cart['quantity']);
            if(Session::has('pos.discount') && Session::get('pos.discount') > $cartTotal){
                Session::forget('pos.discount');
            }

            if (Session::has('pos.coupon_code') && Session::has('pos.coupon_discount') && Session::get('pos.coupon_discount') > 0) {
                $coupon = Coupon::where('status', 1)->where('code', Session::get('pos.coupon_code'))->first();
                if ($coupon) {
                    $response = $this->calculateCouponDiscount($coupon, $carts);
                    if (!$response['success'] || $response['discount'] > $cartTotal) {
                        Session::forget('pos.coupon_code');
                        Session::forget('pos.coupon_discount');
                    }
                    Session::put('pos.coupon_discount', $response['discount']);
                } else {
                    Session::forget('pos.coupon_code');
                    Session::forget('pos.coupon_discount');
                }
            }

            Session::put('pos.cart', $carts);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
            'view' => $request->boolean('isGift') ? view('pos.order_summary', [
                'lastOrderDate' => $this->getLastOrderDate(),
                'giftOffersView' => $this->getGiftOffersView()
            ])->render() : view('pos.cart')->render()
        ]);
    }

    //Shipping Address for admin
    public function getShippingAddress(Request $request){
        $user_id = $request->id;
        $phone = $request->phone ?? '';
        $isGuest = $request->guest ?? false;
        if($user_id == ''){
            return view('pos.guest_shipping_address');
        }
        else{
            return view('pos.shipping_address', compact('user_id', 'phone', 'isGuest'));
        }
    }

    public function getCustomerAddress(Request $request){
        // $states = Cache::remember('states', 60 * 24, function () {
        //     return State::pluck('id','name')->toArray();
        // });

        // $cities = Cache::remember('cities', 60 * 24, function () {
        //     return City::pluck('id','name')->toArray();
        // });

        // $areas = Cache::remember('areas', 60 * 24, function () {
        //     return Area::pluck('id','name')->toArray();
        // });

        $phone = $request->phone;
        $orders = Order::whereJsonContains('shipping_address', ['phone' => $phone])
            ->select('delivery_status', 'shipping_address')
            ->latest()
            ->get();

        if($orders->isEmpty()){
            Session::put('pos.success_rate', [
                'total' => 0,
                'delivered' => 0,
                'returned' => 0,
                'ratio' => 0,
            ]);
            return response()->json([
                'ssuccess' => 1,
                'message' => ('No address found for this phone number. Please fill the form manually.'),
            ]);
        }
        $addresses = [];
        $tempAddresses = [];
        foreach($orders as $order){
            $shipping_address = json_decode($order->shipping_address, true) ?? [];
            $address = data_get($shipping_address, 'address', '');
            $state = data_get($shipping_address, 'state', '');
            $city = data_get($shipping_address, 'city', '');
            $area = data_get($shipping_address, 'area', '');
            $key = strtolower($address . $state . $city . $area);
            if(in_array($key, $tempAddresses)) continue;
            $tempAddresses[] = $key;
            $addresses[] = [
                'name' => data_get($shipping_address, 'name', ''),
                'address' => $address,
                'country' => data_get($shipping_address, 'country', ''),
                'state' => $state,
                'city' => $city,
                'area'=> $area,
                // 'state_id' => isset($states[$state]) ? $states[$state] : $state,
                // 'city_id'=> isset($cities[$city]) ? $cities[$city] : $city,
                // 'area_id' => isset($areas[$area]) ? $areas[$area] : $area,
                'state_id' => State::active()->where('name', $state)->value('id') ?? $state,
                'city_id'=> City::active()->where('name', $city)->value('id') ?? $city,
                'area_id' => Area::active()->where('name', $area)->value('id') ?? $area,
            ];
        }

        $totalOrders = $orders->count();
        $deliveredOrders = $orders->where('delivery_status', 'delivered')->count();
        $returnedOrders = $orders->where('delivery_status', 'returned')->count();

        try{
            $successRate = ($returnedOrders / $deliveredOrders) * 100;
        }catch(DivisionByZeroError $e){
            $successRate = 0;
        }catch (Exception $e) {
            $successRate = 0;
        }

        if($deliveredOrders > 0 && $successRate <= 0){
            $successRate = 100;
        }elseif($successRate >= 100){
            $successRate = 0;
        }
        Session::put('pos.success_rate', [
            'total' => $totalOrders,
            'delivered' => $deliveredOrders,
            'returned' => $returnedOrders,
            'ratio' => $successRate,
        ]);
        return response()->json([
            'success' => 1,
            'view' => view('pos.customers_all_addresses', compact('addresses'))->render(),
            'message' => ($orders->count() . ' Addresses found for this phone number.'),
            'success_rate' => $successRate,
        ]);
    }

    public function getRecentOrders(Request $request)
    {
        $recentOrders = Cache::remember('recent_orders_'.$request->phone, 60 * 24, function () use ($request) {
            return Order::with('orderDetails.product', 'user', 'payments')
                ->whereJsonContains('shipping_address', ['phone' => $request->phone])
                ->latest()
                ->limit(5)
                ->get();
        });

        return response()->json([
            'success' => true,
            'view' => view('backend.components.recent-orders-list', compact('recentOrders'))->render(),
        ]);
    }

    // Store Call Log
    public function storeCallLog(Request $request)
    {
        $callLog = [
            'note' => $request->note,
            'duration' => $request->duration ?? 0,
            'status' => strtolower($request->status),
        ];

        Session::put('pos.call_log', $callLog);

        return response()->json([
            'success' => 1,
            'message' => ('Call log has been saved successfully.'),
        ]);
    }

    //Shipping Address for seller
    public function getShippingAddressForSeller(Request $request){
        $user_id = $request->id;
        if($user_id == ''){
            return view('pos.frontend.seller.pos.guest_shipping_address');
        }
        else{
            return view('pos.frontend.seller.pos.shipping_address', compact('user_id'));
        }
    }

    public function set_shipping_address(Request $request) {
        if($request->address_id == null && (!filled($request->state_id) || !filled($request->city_id) || !filled($request->area_id))){
            $select = [];
            if(!filled($request->state_id)){
                $select[] = 'state';
            }
            if(!filled($request->city_id)){
                $select[] = 'city';
            }
            if(!filled($request->area_id)){
                $select[] = 'area';
            }
            $select = implode(', ', $select);
            return response()->json([
                'success' => 0,
                'message' => ('Please select '.$select),
            ]);
        }
        if ($request->address_id != null) {
            $address = Address::findOrFail($request->address_id);
            $data['name'] = $address->user->name;
            $data['email'] = $address->user->email;
            $data['address'] = $address->address;
            $data['country'] = $address->country->name;
            $data['state'] = $address->state->name;
            $data['city'] = $address->city->name;
            $data['area'] = $address->area->name;
            $data['postal_code'] = $address->postal_code;
            $data['phone'] = $address->phone;
        } else {
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['address'] = $request->address;
            $data['country'] = Country::find($request->country_id)->name;
            $data['state'] = State::find($request->state_id)->name;
            $data['city'] = City::find($request->city_id)->name;
            $data['area'] = Area::find($request->area_id)->name;
            $data['postal_code'] = $request->postal_code;
            $data['phone'] = $request->phone;
        }

        $shipping_info = $data;
        // $request->session()->put('pos.shipping_info', $shipping_info);
        Session::put('pos.shipping_info', $shipping_info);
        Session::put('pos.phoneNumber', $request->phone);

        return response()->json([
            'success' => 1,
            'message' => ('Shipping address has been set successfully.'),
            'phone' => $data['phone'] ?? $request->phone,
        ]);
    }

    //set Discount
    public function setDiscount(Request $request){
        if($request->discount >= 0){
            Session::put('pos.discount', $request->discount);
        }
        return view('pos.cart');
    }

    //set Shipping Cost
    public function setShipping(Request $request){
        if($request->shipping != null){
            Session::put('pos.shipping', $request->shipping);
        }
        return view('pos.cart');
    }

    private function calculateCouponDiscount(Coupon $coupon, Collection $carts): array
    {
        if (!$coupon) {
            return [
                'success' => false,
                'message' => 'Invalid coupon code.',
            ];
        }

        $couponDetails = json_decode($coupon->details, true);

        $isExpired = now()->timestamp < $coupon->start_date
            || now()->timestamp > $coupon->end_date;

        if ($isExpired) {
            return [
                'success' => false,
                'message' => 'Coupon is expired or inactive.',
            ];
        }

        $cartTotal = $carts->sum(fn ($item) => $item['price'] * $item['quantity']);

        $discountAmount = 0;

        if ($coupon->type === 'cart_base') {
            $minBuy = (float) ($couponDetails['min_buy'] ?? 0);
            if ($cartTotal < $minBuy) {
                return [
                    'success' => false,
                    'message' => 'Buy at least ' . $minBuy . ' to use this coupon.',
                ];
            }

            $discountAmount = match ($coupon->discount_type) {
                'percent' => ($cartTotal * $coupon->discount) / 100,
                'amount'  => $coupon->discount,
                default   => 0,
            };

        } elseif ($coupon->type === 'product_base') {
            $eligibleProducts = collect($couponDetails)
                ->pluck('product_id')
                ->toArray();

            $eligibleItems = $carts->whereIn('product_id', $eligibleProducts);

            if ($eligibleItems->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Coupon is not applicable to selected products.',
                ];
            }

            foreach ($eligibleItems as $item) {
                $itemTotal = $item['price'] * $item['quantity'];

                $discountAmount += match ($coupon->discount_type) {
                    'percent' => ($itemTotal * $coupon->discount) / 100,
                    'amount'  => $coupon->discount,
                    default   => 0,
                };
            }
        } elseif ($coupon->type == 'shipping_charge') {
            $shippingCost = $carts->sum('shipping_cost');
            $cartTotal = $carts->sum(function ($cartItem) {
                return $cartItem['price'] * $cartItem['quantity'];
            });
            if ($cartTotal < data_get($couponDetails, 'min_buy', 0)) {
                $needToBuyMore = data_get($couponDetails, 'min_buy', 0) - $cartTotal;
                return [
                    'success' => false,
                    'message' => 'Add more ' . single_price($needToBuyMore) . ' to apply this coupon!'
                ];
            }
            $discountAmount = match ($coupon->discount_type) {
                'percent' => ($shippingCost * $coupon->discount) / 100,
                'amount'  => $coupon->discount,
                default   => 0,
            };
        }

        $discountAmount = min(
            $discountAmount,
            $couponDetails['max_discount'] ?? $cartTotal
        );

        if ($discountAmount <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid coupon discount.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Coupon applied successfully.',
            'discount' => $discountAmount,
            'coupon' => $coupon,
        ];
    }

    public function applyCoupon(Request $request)
    {
        $carts = Session::get('pos.cart', collect());

        if ($carts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty. Please add products to apply a coupon.'
            ]);
        }

        $coupon = Coupon::where('status', 1)->where('code', $request->code)->first();

        if ($coupon && $request->user_id) {
            $validation = is_coupon_valid($coupon, $request->user_id, 'web');
            if (!$validation['status']) {
                return response()->json([
                    'success' => false,
                    'message' => $validation['message']
                ]);
            }
        }

        if (Session::has('pos.coupon_code') && (!$request->code || !$coupon)) {
            Session::forget(['pos.coupon_discount', 'pos.coupon_code']);
            return response()->json([
                'success' => true,
                'message' => 'Coupon removed successfully.',
                'view' => view('pos.order_summary', ['lastOrderDate' => $this->getLastOrderDate(), 'giftOffersView' => $this->getGiftOffersView()])->render(),
            ]);
        }
        $result = $this->calculateCouponDiscount($coupon, $carts);
        if (!$result['success']) {
            return response()->json($result);
        }

        Session::forget('pos.discount');
        Session::put('pos.coupon_discount', $result['discount']);
        Session::put('pos.coupon_code', $coupon->code);

        $this->validateGiftCarts();

        $lastOrderDate = $this->getLastOrderDate();
        $giftOffersView = $this->getGiftOffersView();
        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully.',
            'view' => view('pos.order_summary', compact('lastOrderDate', 'giftOffersView'))->render(),
        ]);
    }

    private function validateGiftCarts(): void
    {
        $carts = Session::get('pos.cart', collect());

        $isValid = true;
        $giftCarts = $carts->where('type', 'gift');
        $regularCarts = $carts->where('type', 'regular');

        if ($giftCarts->isNotEmpty()) {
            $giftOfferItemIds = $giftCarts->pluck('gift_offer_item_id')->unique();
            $giftOfferItems = GiftOfferItem::with('giftOffer.conditions')->whereIn('id', $giftOfferItemIds)->get()->keyBy('id');
            foreach ($giftOfferItems as $giftOfferItem) {
                $giftOffer = $giftOfferItem->giftOffer;
                if ($giftOffer && $giftOffer->offer_type === 'cart') {
                    $cartTotal = $regularCarts->sum(fn ($cart) => ($cart['price'] * $cart['quantity'])) - Session::get('pos.discount', 0) - Session::get('pos.coupon_discount', 0);

                    if ($giftOffer->min_cart_amount > 0 && $cartTotal < $giftOffer->min_cart_amount) {
                        $isValid = false;
                        break;
                    }
                } else {
                    $conditions = $giftOffer->conditions;
                    $conditionMet = false;
                    $cartProductIds = $regularCarts->pluck('product_id')->toArray();
                    foreach ($conditions as $condition) {
                        if ($condition->condition_type == 'product' && in_array($condition->product_id, $cartProductIds) && $condition->min_qty <= $regularCarts->where('product_id', $condition->product_id)->sum('quantity')) {
                            $conditionMet = true;
                            break;
                        }
                    }
                    if (!$conditionMet) {
                        $isValid = false;
                        break;
                    }
                }
            }
        }

        if (!$isValid) {
            // Remove all gift carts if any of the gift cart item is invalid
            $carts = $carts->reject(fn ($cart) => $cart['type'] === 'gift');
        }

        Session::put('pos.cart', $carts);
    }

    private function validateCouponDiscount(): void
    {
        $couponCode = Session::get('pos.coupon_code');

        if (!$couponCode) { return; }

        if (Session::get('pos.discount', 0) > 0) {
            Session::forget([
                'pos.coupon_code',
                'pos.coupon_discount',
            ]);
            return;
        }

        $coupon = Coupon::where('status', 1)->where('code', $couponCode)->first();
        $carts = Session::get('pos.cart', collect());
        $result = $this->calculateCouponDiscount($coupon, $carts);

        if (!$result['success']) {
            Session::forget([
                'pos.coupon_code',
                'pos.coupon_discount',
            ]);
            return;
        }

        Session::put('pos.coupon_discount', $result['discount']);
    }

    public function getGiftOffersView(): string
    {
        $carts = Session::get('pos.cart', collect());
        $regularCarts = $carts->where('type', 'regular');

        if ($regularCarts->isEmpty()) {
            return '';
        }

        $regularCartTotal = $regularCarts->sum(fn($cart) => $cart['price'] * $cart['quantity']);
        $regularCartTotal -= Session::get('pos.discount', 0) + Session::get('pos.coupon_discount', 0);
        $productIds = $regularCarts->pluck('id')->unique()->toArray();

        $eligibleOffers = GiftOffer::with([
                'items' => function ($query) {
                    $query->where('available_qty', '>', 0);
                },
                'items.product',
                'conditions.product'
            ])
            ->valid()
            ->whereHas('items', fn($query) => $query->where('available_qty', '>', 0)) // Must have atleast one valid item
            ->where(function ($query) use ($regularCartTotal, $productIds) {
                $query->where(function ($q) use ($regularCartTotal) {
                    $q->where('offer_type', 'cart')
                        ->where('min_cart_amount', '<=', $regularCartTotal);
                })
                ->orWhere(function ($q) use ($productIds) {
                    $q->where('offer_type', 'product')
                        ->whereHas('conditions', function ($conditionQuery) use ($productIds) {
                            $conditionQuery->where('condition_type', 'product')
                                ->whereIntegerInRaw('item_id', $productIds);
                        });
                });
            })
            ->orderBy('min_cart_amount', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $otherOffers = GiftOffer::with([
                'items' => function ($query) {
                    $query->where('available_qty', '>', 0);
                },
                'items.product',
                'conditions.product'
            ])
            ->valid()
            ->whereNotIn('id', $eligibleOffers->pluck('id')->toArray())
            ->whereHas('items', fn($query) => $query->where('available_qty', '>', 0)) // Must have atleast one valid item
            ->orderBy('min_cart_amount', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $offersView = '';
        if ($eligibleOffers->isNotEmpty() || $otherOffers->isNotEmpty()) {
            $offersView = view('pos.partials.gift_offers', [
                'offers' => (new GiftOfferCollection($eligibleOffers))->toArray()['data'],
                'invalidOffers' => (new GiftOfferCollection($otherOffers))->toArray()['data'],
                'carts' => $carts,
            ])->render();
        }

        return $offersView;
    }

    public function addGiftToCart(Request $request)
    {
        $giftOffer = GiftOffer::valid()->find($request->offer_id);
        if (!$giftOffer) {
            return response()->json(['success' => false, 'message' => 'Offer not found.'], 404);
        }

        $giftItem = GiftOfferItem::with('product.stocks')->where('gift_offer_id', $request->offer_id)->find($request->item_id);
        if (!$giftItem) {
            return response()->json(['success' => false, 'message' => 'Gift item not found.'], 404);
        }

        $carts = Session::get('pos.cart', collect());

        $regularCarts = $carts->where('type', 'regular');
        $regularCartTotal = $regularCarts->sum(fn ($cart) => $cart['price'] * $cart['quantity']);
        $regularCartTotal -= Session::get('pos.discount', 0) + Session::get('pos.coupon_discount', 0);

        $otherCarts = $carts->where('type', '!=', 'regular');
        if ($otherCarts->count() && !$otherCarts->where('gift_offer_id', $giftOffer->id)->count()) {
            return response()->json(['success' => false, 'message' => 'You have already added a gift item from another offer to your cart.'], 400);
        }

        if ($otherCarts->count()) {
            if ($otherCarts->count() >= $giftOffer->max_item_per_order) {
                return response()->json(['success' => false, 'message' => 'You have already added the maximum allowed gift items to your cart.'], 400);
            } elseif ($otherCarts->sum('quantity') >= $giftOffer->max_qty_per_order) {
                // return response()->json(['success' => false, 'message' => 'You have already added the maximum allowed gift items quantity to your cart.'], 400);
            } elseif ($otherCarts->where('product_id', $giftItem->product_id)->sum('quantity') >= $giftItem->available_qty) {
                return response()->json(['success' => false, 'message' => 'You can only add up to the available quantity of this gift item.'], 400);
            }
        }

        $newQty = $otherCarts->where('product_id', $giftItem->product_id)->sum('quantity') + 1;
        $product = $giftItem->product;
        $productStock = $product->stocks->first();
        $availableQty = $productStock->qty ?? 0;
        if ($availableQty <= 0) {
            $giftItem->available_qty = 0;
            $giftItem->save();
            return response()->json([
                'success' => false,
                'message' => "This gift item is out of stock",
            ], 400);
        } elseif ($newQty > $availableQty) {
            $giftItem->available_qty = max(min($giftItem->available_qty, $availableQty), 0);
            $giftItem->save();
            return response()->json([
                'success' => false,
                'message' => "Only {$availableQty} items available in stock for this gift item",
            ], 400);
        }

        // dd($regularCarts, $otherCarts);
        $conditionMet = false;
        if ($giftOffer->offer_type === 'cart') {
            if ($regularCartTotal >= $giftOffer->min_cart_amount) {
                $conditionMet = true;
            }
        } else {
            $giftOffer->load('conditions');
            $cartProductIds = $regularCarts->pluck('product_id')->toArray();
            // dd($giftOffer->conditions, $cartProductIds, $regularCarts);
            foreach ($giftOffer->conditions as $condition) {
                if ($condition->condition_type == 'product' && in_array($condition->product_id, $cartProductIds) && $condition->min_qty <= $regularCarts->where('product_id', $condition->product_id)->sum('quantity')) {
                    $conditionMet = true;
                    break;
                }
            }
        }

        if (!$conditionMet) {
            return response()->json(['success' => false, 'message' => 'This offer item is not valid for your cart.'], 400);
        }

        $cart = $carts->where('gift_offer_id', $giftOffer->id)
            ->where('gift_offer_item_id', $giftItem->id)
            ->where('product_id', $giftItem->product_id)
            ->first();

        if ($cart) {
            if ($cart['quantity'] >= $giftItem->available_qty) {
                return response()->json(['success' => false, 'message' => 'You have already added the maximum available quantity of this gift item to your cart.'], 400);
            }
            $cart['quantity'] += 1;
            $carts->put($carts->search($cart), $cart);
        } else {
            $cart = [
                'id' => $giftItem->product_id,
                'stock_id' => $productStock->id ?? null,
                'variant' => $productStock->variant ?? '',
                'price' => $giftItem->offer_price,
                'tax' => 0,
                'quantity' => 1,
                'type' => 'gift',
                'gift_offer_id' => $giftOffer->id,
                'gift_offer_item_id' => $giftItem->id,
            ];
            $carts->push($cart);
        }

        Session::put('pos.cart', $carts);

        return response()->json([
            'success' => true,
            'message' => 'Gift item added to cart successfully.',
            'view' => view('pos.order_summary', ['lastOrderDate' => $this->getLastOrderDate(), 'giftOffersView' => $this->getGiftOffersView()])->render(),
        ], 200);
    }

    //order summary
    public function get_order_summary(Request $request){
        // dd(Session::get('pos.cart'));
        $this->validateGiftCarts();
        $this->validateCouponDiscount();
        $lastOrderDate = $this->getLastOrderDate();
        $giftOffersView = $this->getGiftOffersView();
        return view('pos.order_summary', compact('lastOrderDate', 'giftOffersView'));
    }

    private function getLastOrderDate()
    {
        $lastOrderDate = null;
        $phoneNumber = Session::get('pos.phoneNumber','');
        if(filled($phoneNumber)){
            $order = Order::whereJsonContains('shipping_address', ['phone' => $phoneNumber])->latest()->first();
            if ($order) {
                $default = date('d-m-Y h:i A', $order->date);
                $orderDate = \Carbon\Carbon::parse($order->date);
                $now = \Carbon\Carbon::now();

                if ($orderDate->diffInHours($now) < 24) {
                    $formattedDate = 'Today';
                }else{
                    $formattedDate = $orderDate->diffForHumans();
                }

                $lastOrderDate = [
                    'default' => $default,
                    'formatted' => $formattedDate,
                ];
            }
        }
        return $lastOrderDate;
    }

    //order place
    public function order_store_old(Request $request){
        // dd($request->all());
        try {
            DB::beginTransaction();
            // $payments = collect($request->session()->get('pos.payments', []));
            $payments = collect(Session::get('pos.payments', []));
            if(Session::has('pos.cart') && count(Session::get('pos.cart')) > 0){
                $order = new Order;
                $name = '';
                $email = '';
                $address = '';
                $country = '';
                $state = '';
                $city = '';
                $area = '';
                $postal_code = '';
                $phone = '';

                if ($request->user_id == null) {
                    $order->guest_id    = mt_rand(100000, 999999);
                    $name               = $request->name;
                    $email              = $request->email;
                    $address            = $request->address;
                    $country            = @Country::find($request->country)->name;
                    $state              = @State::find($request->state)->name;
                    $city               = @City::find($request->city)->name;
                    $area               = @Area::find($request->area)->name;
                    $postal_code        = $request->postal_code;
                    $phone              = $request->phone;

                    if($address=='' || $country=='' || $state=='' || $city=='' || $area=='' || $phone==''){
                        return array('success' => 0, 'message' => ("Please add name, address, country, state, city, area and phone"));
                    }

                    $order->address_id = isset($request->area) ? intval($request->area) : 0;

                }
                else {
                    $order->user_id = $request->user_id;

                    $user           = User::findOrFail($request->user_id);
                    $name   = $user->name;
                    $email  = $user->email;

                    if($request->shipping_address != null){
                        $address_data   = Address::findOrFail($request->shipping_address);
                        $address        = $address_data->address;
                        $country        = $address_data->country->name;
                        $state          = $address_data->state->name;
                        $city           = $address_data->city->name;
                        $area           = $address_data->area->name;
                        $postal_code    = $address_data->postal_code;
                        $phone          = $address_data->phone;
                    }

                    $order->address_id = isset($address_data) ? $address_data->area->id : intval($request->area) ?? 0;
                }

                $data['name']           = $name;
                $data['email']          = $email;
                $data['address']        = $address;
                $data['country']        = $country;
                $data['state']          = $state;
                $data['city']           = $city;
                $data['area']           = $area;
                $data['postal_code']    = $postal_code;
                $data['phone']          = $phone;

                $order->shipping_address = json_encode($data);

                $order->payment_type = $request->payment_type;
                $order->delivery_viewed = '0';
                $order->payment_status_viewed = '0';
                $order->code = config('app.order_no_prefix').date('YmdHis').rand(10,99);
                $order->date = strtotime('now');
                $order->payment_status = 'unpaid';
                $order->payment_details = $request->payment_type;
                $order->delivery_status = strtolower($request->delivery_status ?? 'pending');
                $order->order_source = strtoupper($request->order_source ?? 'POS');
                if(strtolower($request->order_source) === 'showroom') {
                    $order->delivery_fee = 0;
                }

                $shipping_info = Session::get('pos.shipping_info');
                // dd(Session::get('pos'));
                if($order->save()){
                    $subtotal = 0;
                    $tax = 0;
                    foreach (Session::get('pos.cart') as $key => $cartItem){
                        $product_stock = ProductStock::with('product')->find($cartItem['stock_id']);
                        $product = $product_stock->product;
                        $product_variation = $product_stock->variant;

                        $subtotal += $cartItem['price']*$cartItem['quantity'];
                        $tax += $cartItem['tax']*$cartItem['quantity'];

                        $lastPurchaseItem = $product->getLastPurchaseOrderItemByVariant($product_variation);
                        if ($lastPurchaseItem) {
                            $lastPurchasePrice = $lastPurchaseItem->price;
                        } else {
                            $lastPurchasePrice = 0;
                        }

                        if(intval($cartItem['quantity']) > $product_stock->qty){
                            $order->delete();
                            return array('success' => 0, 'message' => $product->name.' ('.$product_variation.') '.(" just stock outs."));
                        }
                        else {
                            $product_stock->qty = floatval($product_stock->qty) - floatval($cartItem['quantity']);
                            if($product_stock->save()){
                                // Log::info('Stock Updated-'.$product_stock->qty);
                            }else{
                                Log::info('Stock Not Updated-'.$product_stock->qty);
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
                                'purpose_id'    => $order->id,
                                'note'          => 'New POS Sales, Ref. ID = '.$order->code ?? 'Unknown'.''
                            ];
                            // Trigger The Event
                            event(new ProductStockAffected($transaction));
                        }

                        $order_detail = new OrderDetail;
                        $order_detail->order_id  =$order->id;
                        $order_detail->seller_id = $product->user_id;
                        $order_detail->product_id = $product->id;
                        $order_detail->payment_status = 'unpaid';
                        $order_detail->variation = empty($product_variation) ? null : $product_variation;
                        $order_detail->price = $cartItem['price'] * $cartItem['quantity'];
                        $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                        $order_detail->quantity = $cartItem['quantity'];
                        $order_detail->shipping_type = $request->shipping_type;
                        $order_detail->shipping_method = $request->shipping_method;

                        if (Session::get('pos.shipping', 0) >= 0 && count(Session::get('pos.cart') ?? []) > 0) {
                            $order_detail->shipping_cost = Session::get('pos.shipping', 0)/count(Session::get('pos.cart'));
                        }
                        else {
                            $order_detail->shipping_cost = 0;
                        }

                        $order_detail->last_purchase_price = $lastPurchasePrice > 0 ? $lastPurchasePrice : $cartItem['price'];

                        $order_detail->save();

                        $product->num_of_sale++;
                        $product->save();
                    }

                    $order->grand_total = $subtotal + $tax + Session::get('pos.shipping', 0);

                    if(Session::has('pos.discount') && Session::get('pos.discount', 0) > 0){
                        $order->grand_total -= Session::get('pos.discount', 0) ?? 0;
                        $order->coupon_discount = Session::get('pos.discount', 0) ?? 0;
                    }

                    $order->payment_status = 'unpaid';
                    $order->seller_id = $product->user_id;
                    $order->save();

                    $array['view'] = 'emails.invoice';
                    $array['subject'] = 'Your order has been placed - '.$order->code;
                    $array['from'] = env('MAIL_FROM_ADDRESS');
                    $array['order'] = $order;

                    $admin_products = array();
                    $seller_products = array();

                    foreach ($order->orderDetails as $key => $orderDetail){
                        if($orderDetail->product->added_by == 'admin'){
                            array_push($admin_products, $orderDetail->product->id);
                        }
                        else{
                            $product_ids = array();
                            if(array_key_exists($orderDetail->product->user_id, $seller_products)){
                                $product_ids = $seller_products[$orderDetail->product->user_id];
                            }
                            array_push($product_ids, $orderDetail->product->id);
                            $seller_products[$orderDetail->product->user_id] = $product_ids;
                        }
                    }

                    foreach($seller_products as $key => $seller_product){
                        try {
                            Mail::to(User::find($key)->email)->queue(new InvoiceEmailManager($array));
                        } catch (\Exception $e) {

                        }
                    }

                    //sends email to customer with the invoice pdf attached
                    $toEmail = Session::get('pos.shipping_info')['email'] ?? null;
                    if($toEmail){
                        try {
                            Mail::to($toEmail)->queue(new InvoiceEmailManager($array));
                        } catch (\Exception $e) {

                        }
                    }

                    if($request->user_id != NULL){
                        if (Addon::where('unique_identifier', 'club_point')->first() != null && Addon::where('unique_identifier', 'club_point')->first()->activated) {
                            $clubpointController = new ClubPointController;
                            $clubpointController->processClubPoints($order);
                        }
                    }

                    foreach($payments as $payable){
                        $bank_info = ACCBank::find($payable['bank']);

                        $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
                        $pinv = "PAY-" . date('Ymd') . ($inv_counting + 1);

                        $pdetails = [
                            'payment_method' => $payable['method'],
                            'bank_type' => $payable['bank_type'],
                            'bank_info' => $bank_info['bank_name'] ?? null,
                            'payment_amount' => $payable['amount'],
                        ];

                        $payment = new Payment;
                        $payment->invoice_no = $pinv;
                        $payment->date = date('Y-m-d');
                        $payment->payable_id = $order->user_id ?? $order->guest_id;
                        $payment->payable_type = User::class;
                        $payment->reference_id = $order->id;
                        $payment->reference_type = Order::class;
                        $payment->seller_id = null;
                        $payment->amount = $payable['amount'];
                        $payment->payment_details = json_encode($pdetails);
                        $payment->payment_method = $payable['method'];
                        $payment->txn_code = null;
                        $payment->user_id = auth()->user()?->id ?? null;
                        $payment->remarks = $payable['note'];
                        $payment->save();
                    }
                    if($payments->isNotEmpty()){
                        $totalAmountPaid = $payments->sum('amount') ?? 0;
                        $grand_total = get_order_grand_total($order);
                        if($totalAmountPaid > 0){
                            $order->payment_status = $totalAmountPaid < $grand_total ? 'partial' : 'paid';
                        }else{
                            $order->payment_status = 'unpaid';
                        }
                        $order->due_amount = max(0, $grand_total - $totalAmountPaid); // Ensure due amount is not negative
                        $order->save();
                    }

                    // Store Call Logs
                    $callLog = Session::get('pos.call_log', []);
                    if(!empty($callLog)){
                        $order->addCallLog([
                            'status' => $callLog['status'] ?? 'unknown',
                            'note' => $callLog['note'] ?? '',
                            'called_by' => auth()->user()->id,
                            'duration' => $callLog['duration'] ?? 0,
                        ]);
                    }
                    event(new OrderPlaced($order));
                    calculateCommissionAffilationClubPoint($order);

                    DB::commit();

                    (new OrderController)->sendEmail($order);
                    if (json_decode($order->shipping_address)?->phone ?? false) {
                        CourierSuccessRateJob::dispatch(json_decode($order->shipping_address)?->phone ?? '');
                    }
                    Session::forget('pos.shipping_info');
                    Session::forget('pos.shipping');
                    Session::forget('pos.discount');
                    Session::forget('pos.cart');
                    Session::forget('pos.payments');
                    Session::forget('pos.total_paid');
                    Session::forget('pos.success_rate');
                    Session::forget('pos.phoneNumber');
                    Session::forget('pos.call_log');
                    Session::forget('pos.last_order_date');

                    logOrder($order, 'created');
                   return array('success' => 1, 'message' => ('Order Completed Successfully.'), 'order_id' => $order->id);
                }
                else {
                    DB::rollback();
                    return array('success' => 0, 'message' => ('Please input customer information.'));
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('Error:'.$e->getMessage());
            return array('success' => 0, 'message' => $e->getMessage());
        }

        return array('success' => 0, 'message' => ("Please select a product."));
    }

    public function order_store(Request $request){
        // dd($request->all());
        try {
            $carts = Session::get('pos.cart', collect());

            $regularCarts = $carts->where('type', 'regular');
            if($regularCarts->isEmpty()) {
                Session::forget('pos.cart');
                return ['success' => 0, 'message' => "Cart is empty."];
            }

            DB::beginTransaction();
            $user = existsOrCreateUser($request->all());

            if (!$user) {
                DB::rollback();
                return ['success' => 0, 'message' => "Unable to find or create user with provided information."];
            }

            $data = [];
            if (!is_null($request->shipping_address)) {
                $address_data = Address::find($request->shipping_address);
                if (!$address_data) {
                    return ['success' => 0, 'message' => "Invalid shipping address."];
                }
                $data['address'] = $address_data->address;
                $data['country'] = $address_data->country?->name;
                $data['state'] = $address_data->state?->name;
                $data['city'] = $address_data->city?->name;
                $data['area'] = $address_data->area?->name;
                $data['phone'] = $address_data->phone;
            } else {
                $data['address'] = $request->address;
                $data['country'] = Country::find($request->country)?->name;
                $data['state'] = State::find($request->state)?->name;
                $data['city'] = City::find($request->city)?->name;
                $data['area'] = Area::find($request->area)?->name;
                $data['phone'] = $request->phone;
            }

            if (empty(array_filter($data))) {
                DB::rollback();
                return ['success' => 0, 'message' => "Please add name, address, country, state, city, area and phone"];
            }

            $data['postal_code'] = @$address_data->postal_code ?? $request->postal_code ?? '';
            $data['name'] = @$address_data->name ?? $request->name ?? $user->name;
            $data['email'] = @$address_data->email ?? $request->email ?? $user->email;

            $discountBy = 'manual';
            $discountAmount = Session::get('pos.discount', 0) ?? 0;
            $coupon = null;
            if (Session::has('pos.coupon_code')) {
                $coupon = Coupon::where('status', 1)->where('code', Session::get('pos.coupon_code'))->first();
                if (!$coupon) {
                    Session::forget([
                        'pos.coupon_code',
                        'pos.coupon_discount',
                    ]);
                } else {
                    $result = $this->calculateCouponDiscount($coupon, $carts);

                    if ($result['success']){
                        $discountBy = 'coupon';
                        $discountAmount = $result['discount'] ?? Session::get('pos.coupon_discount', 0) ?? 0;
                    }
                }
            }

            // dd($request->all(), $user->toArray(), $data);
            $order = new Order;
            $order->user_id = $user->id;
            $order->address_id = isset($address_data) && $address_data->area?->id ?
                $address_data->area?->id :
                intval($request->area) ?? 0;
            $order->shipping_address = json_encode($data);
            $order->payment_type = $request->payment_type;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            $order->code = config('app.order_no_prefix').date('YmdHis').rand(10,99);
            $order->date = strtotime('now');
            $order->payment_status = 'unpaid';
            $order->payment_details = $request->payment_type;
            $order->delivery_status = strtolower($request->delivery_status ?? 'pending');
            $order->order_source = strtoupper($request->order_source ?? 'POS');
            $order->coupon_discount = $discountAmount ?? 0;
            $order->gift_offer_total = $carts->where('type', 'gift')->sum(fn($cart) => ($cart['price'] + $cart['tax']) * $cart['quantity']);
            if(strtolower($request->order_source) === 'showroom') {
                $order->delivery_fee = 0;
            }

            if (!$order->save()) {
                DB::rollback();
                return ['success' => 0, 'message' => "Please input customer information."];
            }

            $subtotal = 0;
            $tax = 0;
            $admin_products = [];
            $seller_products = [];
            $stockIds = $carts->pluck('stock_id')->unique()->toArray();
            $productStocks = ProductStock::with('product')->whereIn('id', $stockIds)->get()->keyBy('id');
            foreach ($carts as $index => $cartItem) {
                $product_stock = $productStocks->get($cartItem['stock_id']);
                if (!$product_stock) {
                    DB::rollback();
                    return ['success' => 0, 'message' => "Product stock not found for cart item."];
                }
                $product = $product_stock->product;
                if (!$product) {
                    DB::rollback();
                    return ['success' => 0, 'message' => "Invalid product in cart."];
                }
                $product_variation = $product_stock->variant;

                $subtotal += $cartItem['price']*$cartItem['quantity'];
                $tax += $cartItem['tax']*$cartItem['quantity'];

                $lastPurchaseItem = $product->getLastPurchaseOrderItemByVariant($product_variation);
                if ($lastPurchaseItem) {
                    $lastPurchasePrice = $lastPurchaseItem->price;
                } else {
                    $lastPurchasePrice = 0;
                }

                $giftOfferItem = null;
                if (isset($cartItem['gift_offer_item_id'])) {
                    $giftOfferItem = GiftOfferItem::find($cartItem['gift_offer_item_id']);
                }

                $cartQuantity = (int) $cartItem['quantity'];
                $availableQty = (int) $product_stock->qty;

                if ($cartQuantity > $availableQty) {
                    DB::rollback();
                    return ['success' => 0, 'message' => $product->name.' ('.$product_variation.') '.' just stock outs.'];
                } else {
                    if ($cartItem['type'] === 'gift' && $giftOfferItem) {
                        $isGiftOutOfStock = $cartQuantity > $availableQty && $cartQuantity > $giftOfferItem->available_qty;
                        if ($isGiftOutOfStock) {
                            DB::rollback();
                            return ['success' => 0, 'message' => 'The requested quantity is not available for gift offer item '.$product->name];
                        }
                        $giftOfferItem->available_qty = max(0, $giftOfferItem->available_qty - $cartQuantity);
                        $giftOfferItem->used_qty += $cartQuantity;
                        $giftOfferItem->save();
                    }
                    $product_stock->qty -= $cartQuantity;
                    if(!$product_stock->save()){
                        Log::info('Stock Not Updated-'.$product_stock->qty);
                    }

                    $isAddition = false;
                    // Store Stock Transaction
                    $transaction = [
                        'product_id'    => (int)$product->id,
                        'variant'       => empty($product_stock->variant) ? null : $product_stock->variant,
                        'sku'           => $product_stock->sku ?? null,
                        'qty'           => abs($cartQuantity),
                        'isAddition'    => ($isAddition) ? 1 : 0,
                        'isSubtraction' => ($isAddition) ? 0 : 1,
                        'purpose'       => 'sales',
                        'purpose_id'    => $order->id,
                        'note'          => 'New POS Sales, Ref. ID = ' . ( $order->code ?? 'Unknown')
                    ];
                    // Trigger The Event
                    event(new ProductStockAffected($transaction));
                }

                $order_detail = new OrderDetail;
                $order_detail->order_id  =$order->id;
                $order_detail->seller_id = $product->user_id;
                $order_detail->product_id = $product->id;
                $order_detail->payment_status = 'unpaid';
                $order_detail->variation = empty($product_variation) ? null : $product_variation;
                $order_detail->price = $cartItem['price'] * $cartQuantity;
                $order_detail->tax = $cartItem['tax'] * $cartQuantity;
                $order_detail->quantity = $cartQuantity;
                $order_detail->shipping_type = $request->shipping_type;
                $order_detail->shipping_method = $request->shipping_method;
                $order_detail->shipping_cost = $index == 0 ? Session::get('pos.shipping', 0) : 0;
                $order_detail->gift_offer_id = $cartItem['gift_offer_id'] ?? null;
                $order_detail->gift_offer_item_id = $cartItem['gift_offer_item_id'] ?? null;
                $order_detail->product_type = isset($cartItem['gift_offer_id']) && $cartItem['type'] === 'gift' ? 'gift' : 'regular';

                $order_detail->last_purchase_price = $lastPurchasePrice > 0 ? $lastPurchasePrice : $cartItem['price'];

                $order_detail->save();

                $product->num_of_sale += $cartQuantity;
                $product->save();

                if($product->added_by == 'admin'){
                    array_push($admin_products, $product->id);
                }
                else{
                    $product_ids = [];
                    if(array_key_exists($product->user_id, $seller_products)){
                        $product_ids = $seller_products[$product->user_id];
                    }
                    array_push($product_ids, $product->id);
                    $seller_products[$product->user_id] = $product_ids;
                }
            }

            $order->grand_total = $subtotal + $tax + Session::get('pos.shipping', 0) - $discountAmount;
            $order->due_amount = $order->grand_total;
            $order->payment_status = 'unpaid';
            $order->seller_id = $product->user_id;
            $order->save();

            $payments = collect(Session::get('pos.payments', []));

            if($payments->isNotEmpty()){
                foreach($payments as $payable) {
                    $bank_info = ACCBank::find($payable['bank']);

                    $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
                    $pinv = "PAY-" . date('Ymd') . ($inv_counting + 1);

                    $pdetails = [
                        'payment_method' => $payable['method'],
                        'bank_type' => $payable['bank_type'],
                        'bank_info' => $bank_info['bank_name'] ?? null,
                        'payment_amount' => $payable['amount'],
                    ];

                    $payment = new Payment;
                    $payment->invoice_no = $pinv;
                    $payment->date = date('Y-m-d');
                    $payment->payable_id = $order->user_id ?? $order->guest_id;
                    $payment->payable_type = User::class;
                    $payment->reference_id = $order->id;
                    $payment->reference_type = Order::class;
                    $payment->seller_id = null;
                    $payment->amount = $payable['amount'];
                    $payment->payment_details = json_encode($pdetails);
                    $payment->payment_method = $payable['method'];
                    $payment->txn_code = null;
                    $payment->user_id = auth()->user()?->id ?? null;
                    $payment->remarks = $payable['note'];
                    $payment->save();
                }
                $totalAmountPaid = $payments->sum('amount') ?? 0;
                $grand_total = get_order_grand_total($order);
                if($totalAmountPaid > 0){
                    $order->payment_status = $totalAmountPaid < $grand_total ? 'partial' : 'paid';
                }else{
                    $order->payment_status = 'unpaid';
                }
                $order->due_amount = max(0, $grand_total - $totalAmountPaid); // Ensure due amount is not negative
                $order->save();
            }

            $callLog = Session::get('pos.call_log', []);
            if(!empty($callLog)){
                $order->addCallLog([
                    'status' => $callLog['status'] ?? 'unknown',
                    'note' => $callLog['note'] ?? '',
                    'called_by' => auth()->user()->id,
                    'duration' => $callLog['duration'] ?? 0,
                ]);
            }
            event(new OrderPlaced($order));
            calculateCommissionAffilationClubPoint($order);

            if($discountBy === 'coupon' && $coupon) {
                $coupon_usage = new CouponUsage;
                $coupon_usage->user_id = $user->id;
                $coupon_usage->coupon_id = $coupon->id;
                $coupon_usage->order_id = $order->id;
                $coupon_usage->ref_id = $coupon->assigned_to ?? Auth::id();
                $coupon_usage->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('Error place order in POS:'.$e->getMessage());
            return array('success' => 0, 'message' => 'Something went wrong.', 'error' => $e->getMessage());
        }

        try {
            $array['view'] = 'emails.invoice';
            $array['subject'] = 'Your order has been placed - '.$order->code;
            $array['from'] = config('mail.from.address');
            $array['order'] = $order;

            $emailReceivers = [];
            if (!empty($seller_products)) {
                $userIds = array_filter(array_keys($seller_products));
                $emailReceivers = User::whereIn('id', $userIds)
                    ->whereNotNull('email')
                    ->select('email')
                    ->distinct()
                    ->pluck('email')
                    ->toArray();
            }
            $toEmail = Session::get('pos.shipping_info')['email'] ?? null;
            if ($toEmail) {
                $emailReceivers[] = $toEmail;
            }

            foreach($emailReceivers as $email){
                try {
                    Mail::to($email)->queue(new InvoiceEmailManager($array));
                } catch (\Exception $e) {

                }
            }

            (new OrderController)->sendEmail($order);
            if (json_decode($order->shipping_address)?->phone ?? false) {
                CourierSuccessRateJob::dispatch(json_decode($order->shipping_address)?->phone ?? '');
            }
        } catch (\Exception $e) {
            Log::info('Error sending email for order:'.$e->getMessage());
        } finally {
            logOrder($order, 'created');

            Session::forget(['pos.shipping_info', 'pos.shipping', 'pos.discount',
                'pos.cart', 'pos.payments', 'pos.total_paid', 'pos.success_rate',
                'pos.phoneNumber', 'pos.call_log', 'pos.last_order_date',
                'pos.coupon_code', 'pos.coupon_discount']);

            return [
                'success' => 1,
                'order_id' => $order->id,
                'message' => 'Order Completed Successfully.'
            ];
        }
    }

    public function getOrderSummary(Request $request)
    {
        $carts = Session::get('pos.cart', collect([]));

        if ($carts->isEmpty()) {
            return ['success' => 0, 'message' => "Cart is empty."];
        }

        $shipping_cost = Session::get('pos.shipping', 0) ?? 0;
        $discount = max(Session::get('pos.discount', 0), Session::get('pos.coupon_discount', 0), 0);
        $total_paid = Session::get('pos.total_paid', 0) ?? 0;
        $payments = Session::get('pos.payments', collect([]));

        $cartProducts = Product::query()
            ->whereIn('id', $carts->pluck('id'))
            ->get()
            ->keyBy('id');

        $isDefaultType = $request->input('type') === 'default';

        $products = $carts->map(function ($cartItem) use ($cartProducts, $isDefaultType) {
            $product = $cartProducts->get($cartItem['id']);
            if (! $product || ($isDefaultType && $cartItem['type'] === 'gift')) {
                return null;
            }
            $data = [
                'name'     => $product->name,
                'price'    => $cartItem['price'],
                'quantity' => $cartItem['quantity'],
                'tax'      => $cartItem['tax'],
            ];

            if (! $isDefaultType) {
                $data['isGift'] = $cartItem['type'] === 'gift';
            }
            return $data;
        })->filter()->values()->toArray();

        $subtotal = $carts->sum(fn ($item) => $item['price'] * $item['quantity']);
        $tax = $carts->sum(fn ($item) => $item['tax'] * $item['quantity']);

        $paid_amount = $payments->sum('amount') ?? 0;

        $grand_total = $subtotal + $tax + $shipping_cost - $discount - $paid_amount;

        $summary = generate_order_summary($products, $subtotal, $tax, $shipping_cost, $discount, $paid_amount, $grand_total);

        return [
            'success' => 1,
            'message' => 'Order summary generated successfully.',
            'summary' => $summary,
        ];
    }

    public function getCustomerSuccessRate(Request $request) {
        $view = Cache::remember('customer_success_rate_'.$request->phone, now()->addDay(), function () {
            return view('backend.components.pos-customer-success-rate')->render();
        });
        return response()->json([
            'success' => 1,
            'view' => $view,
        ]);
    }

    public function resetCart()
    {
        Session::forget('pos.cart');
        Session::forget('pos.shipping_info');
        Session::forget('pos.shipping');
        Session::forget('pos.discount');
        Session::forget('pos.payments');
        Session::forget('pos.total_paid');
        Session::forget('pos.success_rate');
        Session::forget('pos.phoneNumber');
        Session::forget('pos.call_log');
        Session::forget('pos.last_order_date');

        return response()->json([
            'success' => 1,
            'message' => ('Cart has been reset successfully.'),
            'view' => view('pos.cart')->render(),
        ]);
    }

    public function searchCustomer(Request $request)
    {
        $term = $request->get('q');

        $customers = User::query()
            ->active()
            ->where('user_type', 'customer');

        if (filled($term)) {
            $customers->whereAny(
                ['name', 'phone', 'email'],
                'like',
                '%' . $term . '%'
            );
        }

        $customers = $customers->limit(1000)->get(['id', 'name', 'phone', 'email']);
        return response()->json([
            'total' => $customers->count(),
            'users' => $customers->map(function ($user) {
                $phone = trim(str_replace(['-', '+', ' '], '', $user->phone ?? ''));
                if (str_starts_with($phone, '880')) {
                    $phone = str_replace('880', '0', $phone);
                }
                $contact = strlen($phone) == 11 ? $phone : ($user->email ?? '');
                return [
                    'id'      => $user->id,
                    'name'    => ucwords($user->name) . ($contact ? " ({$contact})" : ''),
                    'contact' => $contact,
                    'ctype'   => strlen($phone) == 11 ? 'phone' : ($user->email ? 'email' : ''),
                    // 'ct' => $user->phone ?? $user->email,
                ];
            })
        ]);
    }

    public function pos_activation()
    {
        return view('pos.pos_activation');
    }
}
