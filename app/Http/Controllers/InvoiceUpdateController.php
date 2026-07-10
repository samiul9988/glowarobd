<?php

namespace App\Http\Controllers;

use App\Events\ProductStockAffected;
use App\Http\Resources\PosProductCollection;
use App\Models\ACCBank;
use App\Models\Address;
use App\Models\Area;
use App\Models\Barcode;
use App\Models\Brand;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ShippingZone;
use App\Models\State;
use App\Models\User;
use App\Utility\CategoryUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceUpdateController extends Controller
{
    public function updateShippingInfo(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'area_id' => 'required|exists:areas,id',
                'city_id' => 'required|exists:cities,id',
                'state_id' => 'required|exists:states,id',
                'phone' => 'required|string|max:11|min:11',
                'name' => 'required|string|min:2|max:50',
                'address' => 'required|string|min:2|max:150',
            ], [
                'area_id.required' => 'The area field is required.',
                'city_id.required' => 'The city field is required.',
                'state_id.required' => 'The state field is required.',
                'area_id.exists' => 'The selected area is invalid.',
                'city_id.exists' => 'The selected city is invalid.',
                'state_id.exists' => 'The selected state is invalid.',
                'phone.regex' => 'The phone number must be 11 digits.',
                'name.min' => 'The name must be at least 2 characters.',
                'address.min' => 'The address must be at least 2 characters.',
            ]);

            $order = Order::find($request->order_id);
            $area = Area::with('city.state')->find($request->area_id);
            if ($request->city_id != $area->city?->id || $request->state_id != $area->city?->state?->id) {
                return response()->json(['error' => 'Invalid city or state selected for the chosen area'], 422);
            }

            $shippingAddress = json_decode($order->shipping_address, true);

            $shippingAddress['name'] = $request->name;
            $shippingAddress['phone'] = $request->phone;
            $shippingAddress['address'] = $request->address;
            $shippingAddress['area'] = $area->name;
            $shippingAddress['city'] = $area->city->name ?? $shippingAddress['city'];
            $shippingAddress['state'] = $area->city->state->name ?? $shippingAddress['state'];

            $order->shipping_address = json_encode($shippingAddress);
            $order->save();

            return response()->json(['success' => true, 'message' => 'Shipping information updated successfully']);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => collect($e->errors())->flatten()
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while updating shipping information', 'error' => $e->getMessage()], 500);
        }
    }

    public function index(int $id)
    {
        $order = Order::with('payments')->findOrFail($id);

        if (! in_array(Auth::user()->user_type, ['admin', 'staff'])) {
            abort(403, 'You are not authorized to edit this order.');
        } if ($order->delivery_status != 'processing') {
            abort(403, 'You can only edit orders that are in the processing status.');
        } if (hasGiftItem($order)) {
            abort(403, 'This order contains gift items and cannot be edited.');
        }

        unlock_all_orders_except($order);
        if($order && $order->isLocked() && $order->lockedBy && $order->lockedBy->id != auth()->user()->id){
            abort(403, 'This order is locked by '. $order->lockedBy->name. ' and unlock in '. round($order->unlockIn() / 60, 2) . ' ' . Str::plural('minute', round($order->unlockIn() / 60, 2)));
        }
        $order->lock(Auth::user());

        Session::put('invoice.discount', $order->coupon_discount);

        $orderItems = $order->orderDetails;
        foreach($orderItems as $index => $item){
            $productData =  Product::find($item->product_id);
            $stockData =  ProductStock::where(['product_id' => $item->product_id, 'variant' => $item->variation ?? ''])->first();
            $source = match(strtolower($order->order_source)){
                'android' => 'app',
                'ios' => 'app',
                default => 'web',
            };

            $item->stock_id = $stockData->id;
            $item->name = $productData->name;
            // $item->price = home_discounted_base_price_by_stock_id_non_converted($stockData->id);
            $item->price = getMinimumPriceByVariant($productData, $stockData, $source, 1, null);
            $item->base_price = home_base_price_by_stock_id($stockData->id);
            $item->addedBy = $productData->user_id;
        }

        return view('backend.invoices.invoice_edit', compact('orderItems', 'order'));
    }

    public function search(Request $request)
    {
        if ($request->mode === 'advance' && get_setting('enable_meilisearch') == 1) {
            return $this->meilisearch($request);
        }

        if(Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff'){
            // $products = ProductStock::join('products','product_stocks.product_id', '=', 'products.id')->select('products.*','product_stocks.id as stock_id','product_stocks.variant','product_stocks.price as stock_price', 'product_stocks.qty as stock_qty', 'product_stocks.image as stock_image')->orderBy('products.created_at', 'desc');
            $products = Product::published()->where('added_by', 'admin');
        }
        else {
            // $products = ProductStock::join('products','product_stocks.product_id', '=', 'products.id')->where('user_id', Auth::user()->id)->where('published', '1')->select('products.*','product_stocks.id as stock_id','product_stocks.variant','product_stocks.price as stock_price', 'product_stocks.qty as stock_qty', 'product_stocks.image as stock_image')->orderBy('products.created_at', 'desc');
            $products = Product::published()->where('user_id', Auth::user()->id);
        }

        if($request->category != null){
            $arr = explode('-', $request->category);
            if($arr[0] == 'category'){
                $category_ids = CategoryUtility::children_ids($arr[1]);
                $category_ids[] = $arr[1];
                $products = $products->whereIn('category_id', $category_ids);
            }
        }

        if($request->brand != null){
            $products = $products->where('brand_id', $request->brand);
        }

        if ($request->keyword != null) {
            $products = $products->where('name', 'like', '%'.$request->keyword.'%')->orWhere('barcode', $request->keyword);
        }

        /*$p = $products->get();

        dd($p);*/

        $stocks = new PosProductCollection($products->paginate(16));
        $stocks->appends(['keyword' =>  $request->keyword,'category' => $request->category, 'brand' => $request->brand]);
        return $stocks;
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
        $stock = ProductStock::find($request->stock_id);
        $product = $stock->product;

        $data = array();
        // $data['stock_id'] = $request->stock_id;
        $data['product_id'] = $product->id;
        $data['variation'] = $stock->variant;
        $data['quantity'] = $product->min_qty;

        $order = Order::with('payments')->find($request->orderId);
        $orderSource = match(strtolower($order->order_source)){
            'android' => 'app',
            'ios' => 'app',
            default => 'web',
        };
        $orderItems = OrderDetail::where('order_id', $request->orderId)->get();
        $productExists = $orderItems->where('product_id', $product->id)->first();
        if($productExists){
            $newRequest = new Request([
                'key' => $productExists->id,
                'orderId' => $productExists->order_id,
                'quantity' => $productExists->quantity + 1
            ]);
            return $this->updateQuantity($newRequest);
        }
        if($orderItems){
            foreach($orderItems as $index => $item){
                $productData =  Product::find($item->product_id);
                $stockData =  ProductStock::where(['product_id' => $item->product_id, 'variant' => $item->variation ?? ''])->first();
                $item->stock_id = $stockData->id;
                $item->name = $productData->name;
                // $item->price = home_discounted_base_price_by_stock_id_non_converted($stockData->id);
                $item->price = getMinimumPriceByVariant($productData, $stockData, $orderSource, 1, null);
                $item->base_price = home_discounted_base_price_by_stock_id($stockData->id);
                $item->addedBy = $productData->user_id;
            }
        }

        if($stock->qty < $product->min_qty){
            return array('success' => 0, 'message' => ("This product doesn't have enough stock for minimum purchase quantity ").$product->min_qty, 'view' => view('backend.invoices.cart', compact('order','orderItems'))->render());
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

        $data['price'] = $price;
        $data['tax'] = $tax;

        if($orderItems){
            $foundInCart = false;
            $cart = collect();
            $isSameSeller = true;
            $addedBy = $product->user_id;

            foreach ($orderItems as $key => $cartItem){
                if($cartItem->addedBy !== $addedBy){
                    return array('success' => 0, 'message' => ("You can't add product from multiple sellers."), 'view' => view('backend.invoices.cart', compact('order','orderItems'))->render());
                }
                if($cartItem->product_id == $product->id && $cartItem->stock_id == $stock->id){
                    $foundInCart = true;
                    $loop_product = Product::find($cartItem->product_id);
                    $product_stock = $loop_product->stocks->where('variant', $cartItem->variation)->first();
                    $orderItem = OrderDetail::find($cartItem->id);

                    if($product_stock->qty >= ($cartItem->quantity + 1)){
                        $cartItem->quantity += 1;
                        $orderItem->quantity += 1;
                        $product_stock->qty -= 1;
                        $product_stock->save();
                        $orderItem->save();

                        $isAddition = false;
                        // Store Stock Transaction
                        $transaction = [
                            'product_id'    => (int)$loop_product->id,
                            'variant'       => empty($product_stock->variant) ? null : $product_stock->variant,
                            'sku'           => $product_stock->sku ?? null,
                            'qty'           => 1,
                            'isAddition'    => ($isAddition) ? 1 : 0,
                            'isSubtraction' => ($isAddition) ? 0 : 1,
                            'purpose'       => 'sales',
                            'purpose_id'    => $order->id,
                            'note'          => 'On Update Added, Ref. Code = '.$order->code ?? 'Unknown'.''
                        ];
                        // Trigger The Event
                        event(new ProductStockAffected($transaction));
                    }else{
                        return array('success' => 0, 'message' => ("This product doesn't have more stock."), 'view' => view('backend.invoices.cart', compact('order','orderItems'))->render());
                    }
                }
            }

            if (!$foundInCart) {
                $order_detail = new OrderDetail;
                $order_detail->order_id = $order->id;
                $order_detail->seller_id = $product->user_id;
                $order_detail->product_id = $product->id;
                $order_detail->variation = empty($stock->variant) ? null : $stock->variant;
                $order_detail->price = getMinimumPriceByVariant($product, $stock, $orderSource, 1, null);
                $order_detail->tax = $tax;

                $order_detail->quantity = $product->min_qty;
                $order_detail->save();

                $stock->qty -= $product->min_qty;
                $stock->save();

                // Store Stock Transaction
                $isAddition = false;
                $transaction = [
                    'product_id'    => (int)$product->id,
                    'variant'       => empty($stock->variant) ? null : $stock->variant,
                    'sku'           => $stock->sku ?? null,
                    'qty'           => abs($product->min_qty),
                    'isAddition'    => ($isAddition) ? 1 : 0,
                    'isSubtraction' => ($isAddition) ? 0 : 1,
                    'purpose'       => 'order_update',
                    'purpose_id'    => $order->id ?? 0,
                    'note'          => 'On Update Added, Ref. Code = '.$order->code ?? 'Unknown'.''
                ];
                // Trigger The Event
                event(new ProductStockAffected($transaction));
            }
        }else{
            $order_detail = new OrderDetail;
            $order_detail->order_id = $order->id;
            $order_detail->seller_id = $product->user_id;
            $order_detail->product_id = $product->id;
            $order_detail->variation = empty($stock->variant) ? null : $stock->variant;
            $order_detail->price = getMinimumPriceByVariant($product, $stock, $orderSource, 1, null);
            $order_detail->tax = $tax;

            $order_detail->quantity = $product->min_qty;
            $order_detail->save();

            $stock->qty -= $product->min_qty;
            $stock->save();

            // Store Stock Transaction
            $isAddition = false;
            $transaction = [
                'product_id'    => (int)$product->id,
                'variant'       => empty($stock->variant) ? null : $stock->variant,
                'sku'           => $stock->sku ?? null,
                'qty'           => abs($product->min_qty),
                'isAddition'    => ($isAddition) ? 1 : 0,
                'isSubtraction' => ($isAddition) ? 0 : 1,
                'purpose'       => 'order_update',
                'purpose_id'    => $order->id ?? 0,
                'note'          => 'On Update Added, Ref. Code = '.$order->code ?? 'Unknown'.''
            ];
            // Trigger The Event
            event(new ProductStockAffected($transaction));
        }

        $orderItems = OrderDetail::where('order_id', $request->orderId)->get();
        if($orderItems){
            foreach($orderItems as $index => $item){
                $productData =  Product::find($item->product_id);
                $stockData =  ProductStock::where(['product_id' => $item->product_id, 'variant' => $item->variation ?? ''])->first();
                $item->stock_id = $stockData->id;
                $item->name = $productData->name;
                // $item->price = home_discounted_base_price_by_stock_id_non_converted($stockData->id);
                $item->price = getMinimumPriceByVariant($productData, $stockData, $orderSource, 1, null);
                $item->base_price = home_discounted_base_price_by_stock_id($stockData->id);
                $item->addedBy = $productData->user_id;
            }
        }

        order_affected($order);
        return array('success' => 1, 'message' => '', 'view' => view('backend.invoices.cart', compact('order','orderItems'))->render());
    }

    //updated the quantity for a cart item
    public function updateQuantity(Request $request)
    {
        // dd($request->all());
        $order = Order::with('payments')->find($request->orderId);

        // dd($request->all());
        $orderItem = OrderDetail::find($request->key);

        $product_stock =  ProductStock::where(['product_id' => $orderItem->product_id, 'variant' => $orderItem->variation ?? ''])->first();

        if($request->quantity > $orderItem->quantity){

            if ($product_stock->qty < 1) {
                return array('success' => 0, 'message' => 'The requested quantity is not available for this product');
                exit;
            }
            $orderItem->price = ($orderItem->price / $orderItem->quantity) * ($orderItem->quantity + 1);
            $orderItem->quantity += 1;
            $orderItem->save();

            $product_stock->qty -= 1;
            $product_stock->save();

            // Store Stock Transaction
            $isAddition = false;
            $transaction = [
                'product_id'    => (int)$orderItem->product_id,
                'variant'       => empty($product_stock->variant) ? null : $product_stock->variant,
                'sku'           => $product_stock->sku ?? null,
                'qty'           => 1,
                'isAddition'    => ($isAddition) ? 1 : 0,
                'isSubtraction' => ($isAddition) ? 0 : 1,
                'purpose'       => 'order_update',
                'purpose_id'    => $orderItem->order_id ?? 0,
                'note'          => 'On Update Increase Qty, Ref. ID = '.$orderItem->order_id ?? 'Unknown'.''
            ];
            // Trigger The Event
            event(new ProductStockAffected($transaction));

        }elseif($request->quantity < $orderItem->quantity){
            $orderItem->price = ($orderItem->price / $orderItem->quantity) * ($orderItem->quantity - 1 != 0 ? $orderItem->quantity - 1 : 1);
            $orderItem->quantity -= 1;
            $orderItem->save();

            $product_stock->qty += 1;
            $product_stock->save();

            // Store Stock Transaction
            $isAddition = true;
            $transaction = [
                'product_id'    => (int)$orderItem->product_id,
                'variant'       => empty($product_stock->variant) ? null : $product_stock->variant,
                'sku'           => $product_stock->sku ?? null,
                'qty'           => 1,
                'isAddition'    => ($isAddition) ? 1 : 0,
                'isSubtraction' => ($isAddition) ? 0 : 1,
                'purpose'       => 'order_update',
                'purpose_id'    => $orderItem->order_id ?? 0,
                'note'          => 'On Update Decrease Qty, Ref. ID = '.$orderItem->order_id ?? 'Unknown'.''
            ];
            // Trigger The Event
            event(new ProductStockAffected($transaction));
        }

        $orderItems = OrderDetail::where('order_id', $request->orderId)->get();
        foreach($orderItems as $index => $item){
            $productData =  Product::find($item->product_id);
            $stockData =  ProductStock::where(['product_id' => $item->product_id, 'variant' => empty($item->variation) ? '' : $item->variation])->first();
            $source = match(strtolower($order->order_source)){
                'android' => 'app',
                'ios' => 'app',
                default => 'web',
            };

            $item->stock_id = $stockData->id;
            $item->name = $productData->name;
            // $item->price = home_discounted_base_price_by_stock_id_non_converted($stockData->id);
            $item->price = getMinimumPriceByVariant($productData, $stockData, $source, 1, null);
            $item->base_price = home_discounted_base_price_by_stock_id($stockData->id);
            $item->addedBy = $productData->user_id;
        }

        $order->grand_total = get_order_grand_total($order);
        $order->save();
        order_affected($order);

        return array('success' => 1, 'message' => '', 'view' => view('backend.invoices.cart', compact('order','orderItems'))->render());
    }

    // Partial payment
    public function partialPayment(Request $request)
    {
        try{
            $bank_info = ACCBank::find($request->bank);
            $order = Order::with('payments')->findOrFail($request->orderId);
            $totalAmountPaid = ($order->payments?->sum('amount') ?? 0) + $request->amount;

            $orderItems = OrderDetail::where('order_id', $order->id)->get();
            $subtotal = $orderItems->sum('price');
            $tax = $orderItems->sum('tax');
            $shipping_cost = $orderItems[0]->shipping_cost;
            $grand_total = $subtotal + $tax + $shipping_cost - ($order->coupon_discount + $totalAmountPaid);
            $original_grand_total = $order->grand_total;

            // dd($bank_info->toArray(), $order->toArray());
            $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
            $pinv = "PAY-" . date('Ymd') . ($inv_counting + 1);

            $pdetails = [
                'payment_method' => $request->method,
                'bank_type' => $request->bank_type,
                'bank_info' => $bank_info['bank_name'] ?? null,
                'payment_amount' => number_format($request->amount, 2),
            ];

            DB::beginTransaction();
            $payment = new Payment;
            $payment->invoice_no = $pinv;
            $payment->date = date('Y-m-d');
            $payment->payable_id = $order->user_id ?? $order->guest_id;
            $payment->payable_type = User::class;
            $payment->reference_id = $order->id;
            $payment->reference_type = Order::class;
            $payment->seller_id = null;
            $payment->amount = $request->amount;
            $payment->payment_details = json_encode($pdetails);
            $payment->payment_method = $request->method;
            $payment->txn_code = null;
            $payment->user_id = auth()->user()?->id ?? null;
            $payment->remarks = $request->note;
            $payment->save();

            // $order->payment_type = $request->method;
            $order->payment_status = $totalAmountPaid < $original_grand_total ? 'partial' : 'paid';
            $order->due_amount = max(0, $original_grand_total - $totalAmountPaid); // Ensure due amount is not negative
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => ('Payment added successfully'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //removes from Cart
    public function removeFromCart(Request $request)
    {
        $orderItem = OrderDetail::find($request->key);



        // Check if the order has only one item
        // if($orderItems->count() == 1){
        //     return -1;
        // }
        if($orderItem){
            $product = Product::find($orderItem->product_id);
            $product_variation = $orderItem->variation;
            $itemQty = $orderItem->quantity;
            $product_stock = $product->stocks->where('variant', $product_variation)->first();

            if(OrderDetail::destroy($request->key)){
                $product_stock->qty += $itemQty;
                $product_stock->save();

                // Store Stock Transaction
                $isAddition = true;
                $transaction = [
                    'product_id'    => (int)$orderItem->product_id,
                    'variant'       => empty($product_stock->variant) ? null : $product_stock->variant,
                    'sku'           => $product_stock->sku ?? null,
                    'qty'           => (int)$itemQty,
                    'isAddition'    => ($isAddition) ? 1 : 0,
                    'isSubtraction' => ($isAddition) ? 0 : 1,
                    'purpose'       => 'order_update',
                    'purpose_id'    => $orderItem->order_id ?? 0,
                    'note'          => 'On Update Removed, Ref. ID = '.$orderItem->order_id ?? 'Unknown'.''
                ];
                // Trigger The Event
                event(new ProductStockAffected($transaction));
            }
        }
        $order = Order::with('payments')->find($request->orderId);
        $orderItems = OrderDetail::where('order_id', $request->orderId)->get();
        if($orderItems){
            foreach($orderItems as $index => $item){
                $productData =  Product::find($item->product_id);
                $stockData =  ProductStock::where(['product_id' => $item->product_id, 'variant' => $item->variation ?? ''])->first();
                $source = match(strtolower($order->order_source)){
                    'android' => 'app',
                    'ios' => 'app',
                    default => 'web',
                };
                $item->stock_id = $stockData->id;
                $item->name = $productData->name;
                // $item->price = home_discounted_base_price_by_stock_id_non_converted($stockData->id);
                $item->price = getMinimumPriceByVariant($productData, $stockData, $source, 1, null);
                $item->base_price = home_discounted_base_price_by_stock_id($stockData->id);
                $item->addedBy = $productData->user_id;
            }
        }

        order_affected($order);
        // dd($order->toArray(), $orderItems->toArray());
        return view('backend.invoices.cart', compact('order', 'orderItems'));
    }

    //Shipping Address for admin
    public function getShippingAddress(Request $request){
        // dd($request->all());
        $user_id = $request->id;
        $order = Order::find($request->orderId);
        $addresss = json_decode($order->shipping_address);
        if($user_id == ''){
            return view('backend.invoices.guest_shipping_address');
        }
        else{
            return view('backend.invoices.shipping_address', compact('user_id', 'addresss'));
        }
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
        $order = Order::find($request->orderId);

        $order->shipping_address = json_encode($shipping_info);
        $order->save();

        $request->session()->put('invoice.shipping_info', $shipping_info);

        return 1;
    }

    //set Discount
    public function setDiscount(Request $request){
        // dd($request->all());
        $order = Order::with('payments')->find($request->orderId);

        if($request->discount >= 0){
            $order->coupon_discount = $request->discount;
            $order->grand_total -= $request->discount;
            // dd($order->grand_total);
            $order->save();

            Session::put('invoice.discount', $request->discount);
        }
        $orderItems = OrderDetail::where('order_id', $request->orderId)->get();
        foreach($orderItems as $index => $item){
            $productData =  Product::find($item->product_id);
            $stockData =  ProductStock::where(['product_id' => $item->product_id, 'variant' => $item->variation ?? ''])->first();
            $source = match(strtolower($order->order_source)){
                'android' => 'app',
                'ios' => 'app',
                default => 'web',
            };

            $item->stock_id = $stockData->id;
            $item->name = $productData->name;
            // $item->price = home_discounted_base_price_by_stock_id_non_converted($stockData->id);
            $item->price = getMinimumPriceByVariant($productData, $stockData, $source, 1, null);
            $item->base_price = home_discounted_base_price_by_stock_id($stockData->id);
            $item->addedBy = $productData->user_id;
        }
        return view('backend.invoices.cart', compact('order', 'orderItems'));
    }

    //set Shipping Cost
    public function setShipping(Request $request){
        $order = Order::with('payments')->find($request->orderId);

        if($request->shipping != null){
            Session::put('invoice.shipping', $request->shipping);
        }
        $orderItems = OrderDetail::where('order_id', $request->orderId)->get();
        foreach($orderItems as $index => $item){
            if($index == 0){
                $item->shipping_cost = $request->shipping;
                $item->shipping_type = $request->type;
                $item->shipping_method = $request->method;
                $item->save();
            }else{
                $item->shipping_cost = 0;
                $item->save();
            }

            $productData =  Product::find($item->product_id);
            $stockData =  ProductStock::where(['product_id' => $item->product_id, 'variant' => $item->variation ?? ''])->first();
            $source = match(strtolower($order->order_source)){
                'android' => 'app',
                'ios' => 'app',
                default => 'web',
            };

            $item->stock_id = $stockData->id;
            $item->name = $productData->name;
            // $item->price = home_discounted_base_price_by_stock_id_non_converted($stockData->id);
            $item->price = getMinimumPriceByVariant($productData, $stockData, $source, 1, null);
            $item->base_price = home_discounted_base_price_by_stock_id($stockData->id);
            $item->addedBy = $productData->user_id;
        }
        return view('backend.invoices.cart', compact('order', 'orderItems'));
    }

    //order summary
    public function get_order_update_summary(Request $request){
        $order = Order::with('payments')->find($request->orderId);
        $orderItems = OrderDetail::where('order_id', $request->orderId)->get();
        foreach($orderItems as $index => $item){
            $productData = Product::find($item->product_id);
            $stockData = ProductStock::where(['product_id' => $item->product_id, 'variant' => $item->variation ?? ''])->first();
            $source = match(strtolower($order->order_source)){
                'android' => 'app',
                'ios' => 'app',
                default => 'web',
            };

            $item->stock_id = $stockData->id;
            $item->name = $productData->name;
            // $item->price = home_discounted_base_price_by_stock_id_non_converted($stockData->id);
            $item->price = getMinimumPriceByVariant($productData, $stockData, $source, 1, null);
            $item->base_price = home_discounted_base_price_by_stock_id($stockData->id);
            $item->addedBy = $productData->user_id;
        }
        $shippingInfo = json_decode($order->shipping_address);
        return view('backend.invoices.order_summary', compact('order', 'orderItems', 'shippingInfo'));
    }

    //order place
    public function order_update(Request $request){
        // dd($request->all());
        $order = Order::with('payments')->find($request->orderId);
        $paidAmount = $order->payments?->sum('amount') ?? 0;

        $subtotal = 0;
        $tax = 0;

        $orderItems = OrderDetail::where('order_id', $request->orderId)->get();
        foreach ($orderItems as $key => $cartItem){
            $product_stock = ProductStock::where(['product_id' => $cartItem->product_id, 'variant' => $cartItem->variation ?? ''])->first();
            $product = Product::find($cartItem['product_id']);
            $product_variation = $product_stock->variant;

            $lastPurchaseItem = $product->getLastPurchaseOrderItemByVariant($product_variation);
            if ($lastPurchaseItem) {
                $lastPurchasePrice = $lastPurchaseItem->price;
            } else {
                $lastPurchasePrice = 0;
            }

            $cartItem->last_purchase_price = $lastPurchasePrice > 0 ? $lastPurchasePrice : $cartItem['price'];
            $cartItem->save();

            $subtotal += $cartItem['price'];
            $tax += $cartItem['tax']*$cartItem['quantity'];

            if($cartItem['quantity'] > $product_stock->qty+1){
                return array('success' => 0, 'message' => $product->name.' ('.$product_variation.') '.(" just stock outs."));
            }
        }
        $grandTotal = $subtotal + $tax + $orderItems[0]->shipping_cost - $order->coupon_discount;
        $order->grand_total = $grandTotal;
        if($paidAmount > 0 && $grandTotal > $paidAmount){
            $order->payment_status = 'partial';
            $order->due_amount = $grandTotal - $paidAmount;
        }elseif($paidAmount > 0 && $grandTotal <= $paidAmount){
            $order->payment_status = 'paid';
            $order->due_amount = 0;
        }else{
            $order->payment_status = 'unpaid';
            $order->due_amount = $grandTotal;
        }

        if($order->save()){
            Session::forget('invoice.shipping_info');
            Session::forget('invoice.shipping');
            Session::forget('invoice.discount');
            Session::forget('invoice.cart');
            logOrder($order, 'updated');
            return array('success' => 1, 'message' => ('Order Updated Successfully.'));
        }
    }
}
