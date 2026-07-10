<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\ShippingZone;
use App\Models\User;
use Auth;

class CartController extends Controller
{
    public function summary($user_id)
    {
        $items = Cart::where('user_id', $user_id)->get();

        if ($items->isEmpty()) {
            return response()->json([
                'sub_total' => format_price(0.00),
                'tax' => format_price(0.00),
                'shipping_cost' => format_price(0.00),
                'shipping_disciunt' => [],
                'discount' => format_price(0.00),
                'grand_total' => format_price(0.00),
                'grand_total_value' => 0.00,
                'coupon_code' => "",
                'coupon_applied' => false,
            ]);
        }

        $sum = 0.00;
        $subtotal = 0.00;
        $tax = 0.00;

        $user_info = ($user_id != '') ? User::with('customeringroup.group')->find($user_id) : null;
        foreach ($items as $cartItem) {
            $product = Product::find($cartItem->product_id);
            $product_stock = $product->stocks->where('variant', $cartItem['variation'])->first();

            $cartItem->price = getMinimumPriceByVariant($product, $product_stock, 'app', $cartItem->quantity, $user_info);
            $item_sum = 0.00;
            $item_sum += ($cartItem->price + $cartItem->tax) * $cartItem->quantity;
            $item_sum += $cartItem->shipping_cost - $cartItem->discount;
            $sum +=  $item_sum  ;   //// 'grand_total' => $request->g

            $subtotal += $cartItem->price * $cartItem->quantity;
            $tax += $cartItem->tax * $cartItem->quantity;

            // Check for coupon validity
            if ($cartItem['coupon_code'] != NULL || $cartItem['shipping_cost'] == null || $cartItem['shipping_cost'] == 'null') {
                $coupon = Coupon::where('status', 1)->where('code', $cartItem['coupon_code'])->first();
                if($coupon){
                    if (strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date) {
                        $cartItem['discount'] = $cartItem['discount'];
                    }else{
                        $cartItem['discount'] = 0;
                    }
                }
            }
        }

        $sDiscount = array();
        $shippingDiscountAmount = PHP_INT_MAX;
        if(check_shipping_discount()){
            $addressInfo = Address::find($items->first()->address_id);
            $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
            $sDiscount = check_shipping_discount_carts($items, $matchZone->id ?? null);
            $shippingDiscountAmount = getDiscountShippingCharge($items, $matchZone->id ?? null);
        }

        $shipping_cost = min($shippingDiscountAmount, $items->sum('shipping_cost'));
        if($sDiscount && $subtotal >= $sDiscount['min_amount']){
            $shipping_cost = $sDiscount['amount'];
            $sum = $sum - $items->sum('shipping_cost') + $shipping_cost;
        } elseif ($shipping_cost < $items->sum('shipping_cost')) {
            $sum = $sum - $items->sum('shipping_cost') + $shipping_cost;
        }

        return response()->json([
            'sub_total' => format_price($subtotal),
            'tax' => format_price($tax),
            'shipping_cost' => format_price($shipping_cost),
            'shipping_discount' => [
                'amount' => (string) ($sDiscount['amount'] ?? 0),
                'status' => $sDiscount['status'] ?? false,
                'min_amount' => (string) ($sDiscount['min_amount'] ?? 0),
            ],
            'discount' => format_price($items->sum('discount')),
            'grand_total' => format_price($sum),
            'grand_total_value' => convert_price($sum),
            'coupon_code' => $items[0]->coupon_code,
            'coupon_applied' => $items[0]->coupon_applied == 1,
        ]);


    }

    public function getList($user_id)
    {
        $owner_ids = Cart::where('user_id', $user_id)->select('owner_id')->groupBy('owner_id')->pluck('owner_id')->toArray();
        $currency_symbol = currency_symbol();
        $shops = [];

        $user_info = ($user_id != '') ? User::with('customeringroup.group')->find($user_id) : null;
        if (!empty($owner_ids)) {
            foreach ($owner_ids as $owner_id) {
                $shop = array();
                $shop_items_raw_data = Cart::where('user_id', $user_id)->where('owner_id', $owner_id)->get()->toArray();
                $shop_items_data = array();
                if (!empty($shop_items_raw_data)) {
                    foreach ($shop_items_raw_data as $shop_items_raw_data_item) {
                        $product = Product::where('id', $shop_items_raw_data_item["product_id"])->first();
                        $product_stock = $product->stocks->where('variant', $shop_items_raw_data_item['variation'])->first();

                        $shop_items_raw_data_item['price'] = getMinimumPriceByVariant($product, $product_stock, 'app', $shop_items_raw_data_item["quantity"], $user_info);
                        $shop_items_data_item["id"] = intval($shop_items_raw_data_item["id"]) ;
                        $shop_items_data_item["owner_id"] =intval($shop_items_raw_data_item["owner_id"]) ;
                        $shop_items_data_item["user_id"] =intval($shop_items_raw_data_item["user_id"]) ;
                        $shop_items_data_item["product_id"] =intval($shop_items_raw_data_item["product_id"]);
                        $shop_items_data_item["product_name"] = $product->name;
                        $shop_items_data_item["min_order_amount"] = (double)$product->min_order_amount;
                        //$shop_items_data_item["discount_type"] = $product->discount_type;
                        $shop_items_data_item["product_thumbnail_image"] = api_asset($product->thumbnail_img);
                        $shop_items_data_item["variation"] = $shop_items_raw_data_item["variation"];
                        $shop_items_data_item["price"] =(double) $shop_items_raw_data_item['price'];
                        $shop_items_data_item["base_price"] =(double) $product->unit_price ?? (double) $shop_items_raw_data_item['price'];
                        $shop_items_data_item["currency_symbol"] = $currency_symbol;
                        $shop_items_data_item["tax"] =(double) $shop_items_raw_data_item["tax"];
                        $shop_items_data_item["shipping_cost"] =(double) $shop_items_raw_data_item["shipping_cost"];
                        $shop_items_data_item["shipping_type"] = $shop_items_raw_data_item["shipping_type"];
                        $shop_items_data_item["shipping_method"] = $shop_items_raw_data_item["shipping_method"];
                        $shop_items_data_item["quantity"] =intval($shop_items_raw_data_item["quantity"]) ;
                        $shop_items_data_item["lower_limit"] = intval($product->min_qty) ;
                        $shop_items_data_item["upper_limit"] = intval($product->stocks->where('variant', $shop_items_raw_data_item['variation'])->first()->qty) ;

                        $shop_items_data[] = $shop_items_data_item;

                    }
                }


                $shop_data = Shop::where('user_id', $owner_id)->first();
                if ($shop_data) {
                    $shop['name'] = $shop_data->name;
                    $shop['owner_id'] =(int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;
                } else {
                    $shop['name'] = "Inhouse";
                    $shop['owner_id'] =(int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;
                }
                $shops[] = $shop;
            }
        }

        //dd($shops);

        return response()->json($shops);
    }

    public function getListWithDelivery($user_id, $address_id)
    {
        $owner_ids = Cart::where('user_id', $user_id)->select('owner_id')->groupBy('owner_id')->pluck('owner_id')->toArray();
        $currency_symbol = currency_symbol();
        $shops = [];

        $addressInfo = \App\Models\Address::findOrfail($address_id);
        $matchZone = \App\Models\ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id])->first();

        $shippingMethods = NULL;
        $shippingCharge = null;
        $shippingDiscount = \App\Models\ShippingDiscount::where('status', 1)
                ->where('start_date', '<=', strtotime(date('d-m-Y')))
                ->where('end_date', '>=', strtotime(date('d-m-Y')));
        if($matchZone){
            $shippingDiscount = $shippingDiscount->where('zone_id',$matchZone->id)->first();
            if($matchZone->rates!==NULL){
                $shippingMethods = json_decode($matchZone->rates);
            }
        }else{
            $matchZone = \App\Models\ShippingZone::where('rest_of_the_world',1)->first();
            $shippingDiscount = $shippingDiscount->where('zone_id',$matchZone->id)->first();
            if($matchZone && $matchZone->rates!==NULL)
            {
                $shippingMethods = json_decode($matchZone->rates);
            }
        }


        $user_info = ($user_id != '') ? User::with('customeringroup.group')->find($user_id) : null;
        $cartTotal = 0;
        if (!empty($owner_ids)) {
            foreach ($owner_ids as $owner_id) {
                $shop = array();
                $shop_items = Cart::where('user_id', $user_id)->where('owner_id', $owner_id)->get();
                $discountShippingCharge = getDiscountShippingCharge($shop_items, $matchZone->id ?? null);
                $shop_items_raw_data = $shop_items->toArray();
                $shop_items_data = array();
                if (!empty($shop_items_raw_data)) {
                    foreach ($shop_items_raw_data as $shop_items_raw_data_item) {
                        $product = Product::where('id', $shop_items_raw_data_item["product_id"])->first();
                        $product_stock = $product->stocks->where('variant', $shop_items_raw_data_item['variation'])->first();

                        $shop_items_raw_data_item['price'] = getMinimumPriceByVariant($product, $product_stock, 'app', $shop_items_raw_data_item["quantity"], $user_info);
                        $shop_items_data_item["id"] = intval($shop_items_raw_data_item["id"]) ;
                        $shop_items_data_item["owner_id"] =intval($shop_items_raw_data_item["owner_id"]) ;
                        $shop_items_data_item["user_id"] =intval($shop_items_raw_data_item["user_id"]) ;
                        $shop_items_data_item["product_id"] =intval($shop_items_raw_data_item["product_id"]) ;
                        $shop_items_data_item["product_name"] = $product->name;
                        $shop_items_data_item["product_thumbnail_image"] = api_asset($product->thumbnail_img);
                        $shop_items_data_item["variation"] = $shop_items_raw_data_item["variation"];
                        $shop_items_data_item["price"] =(double) $shop_items_raw_data_item['price'];
                        $shop_items_data_item["base_price"] =(double) $product->unit_price ?? (double) $shop_items_raw_data_item['price'];
                        $shop_items_data_item["currency_symbol"] = $currency_symbol;
                        $shop_items_data_item["tax"] =(double) $shop_items_raw_data_item["tax"];
                        $shop_items_data_item["shipping_cost"] =(double) $shop_items_raw_data_item["shipping_cost"];
                        $shop_items_data_item["shipping_type"] = $shop_items_raw_data_item["shipping_type"];
                        $shop_items_data_item["shipping_method"] = $shop_items_raw_data_item["shipping_method"];
                        $shop_items_data_item["quantity"] =intval($shop_items_raw_data_item["quantity"]) ;
                        $shop_items_data_item["lower_limit"] = intval($product->min_qty) ;
                        $shop_items_data_item["upper_limit"] = intval($product->stocks->where('variant', $shop_items_raw_data_item['variation'])->first()->qty) ;

                        $shop_items_data[] = $shop_items_data_item;

                        $cartTotal += $shop_items_raw_data_item['price'] * $shop_items_raw_data_item["quantity"];
                    }
                }

                $shop_data = Shop::where('user_id', $owner_id)->first();

                // dd($matchZone, $shippingDiscount, $shippingDiscount?->threshold_amount, $cartTotal);
                $shippingMethodItems=[];
                if($shippingMethods!=NULL):
                    foreach($shippingMethods as $k=>$v):
                        if($shippingDiscount && $shippingDiscount->threshold_amount <= $cartTotal){
                            $shippingCharge = $shippingDiscount->s_charge;
                        }else{
                            $shippingCharge = $v->price;
                        }
                        $shipping_method = \App\Models\ShippingMethod::findOrFail($v->id);
                        $shippingMethodItems[]=[
                            'name'=>'shipping_method_'.(int) $owner_id,
                            'value'=>$v->id,
                            'method_name'=>$shipping_method->name,
                            'method_price'=>(string) min($discountShippingCharge, get_shipping_price($shippingCharge, $user_id)),
                            'method_logo'=>uploaded_asset($shipping_method->logo)
                        ];
                    endforeach;
                endif;

                $pickUpPoints=[];
                foreach (\App\Models\PickupPoint::where('pick_up_status',1)->get() as $key => $pick_up_point):
                    $pickUpPoints[]=[
                        'name'=>'pickup_point_id_'.(int) $owner_id,
                        'value'=>$pick_up_point->id,
                        'pickup_name'=>$pick_up_point->name,
                        'pickup_phone'=>$pick_up_point->phone
                    ];
                endforeach;

                if ($shop_data) {
                    $shop['name'] = $shop_data->name;
                    $shop['owner_id'] =(int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;
                    $shop['shipping_type'][] = [
                        'name'=>'shipping_type_'.(int) $owner_id,
                        'value'=>'home_delivery',
                        'methods'=>$shippingMethodItems,
                        'pickUpPoints'=>[],
                    ];
                } else {
                    $shop['name'] = "Inhouse";
                    $shop['owner_id'] =(int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;

                    $shop['shipping_type'][] = [
                        'name'=>'shipping_type_'.(int) $owner_id,
                        'value'=>'home_delivery',
                        'methods'=>$shippingMethodItems,
                        'pickUpPoints'=>[],
                    ];

                    if (\App\Models\BusinessSetting::where('type', 'pickup_point')->first()->value == 1):
                        $shop['shipping_type'][] = [
                            'name'=>'shipping_type_'.(int) $owner_id,
                            'value'=>'pickup_point',
                            'methods'=>[],
                            'pickUpPoints'=>$pickUpPoints
                        ];
                    endif;
                }
                $shops[] = $shop;
            }
        }

        //dd($shops);

        return response()->json($shops);
    }


    public function add(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $variant = $request->variant;
        $tax = 0;
        $previouscartqty = 0;
        // if ($variant == '')
        //     $price = $product->unit_price;
        // else {
        //     $product_stock = $product->stocks->where('variant', $variant)->first();
        //     $price = $product_stock->price;
        // }
        // 'user_id' => $request->user_id,
        //     'owner_id' => $product->user_id,
        //     'product_id' => $request->id,
        //     'variation' => $variant
        $previouscart = Cart::where('user_id',$request->user_id)->where('owner_id',$product->user_id)->where('product_id',$request->id)->where('variation',$variant)->first();
        if($previouscart){
            $previouscartqty = $previouscart->quantity;
        }
        $product_stock = $product->stocks->where('variant', $variant)->first();
        $price = $product_stock->price;
        $group_price = $product_stock->price;

        // Pre-order
        $carts = array();
        $carts = Cart::where('user_id', $request->user_id)->get();
        if(count($carts)>0){
            if((has_preorder_product_to_cart($carts) && !check_preorder_product($product)) || (has_regular_product_to_cart($carts) && check_preorder_product($product))){
                return response()->json(['result' => false, 'message' => ('You can not add regular products & pre-order products in a single order!')], 200);
            }
        }

        $flash_deal_check = check_flash_deal_product($product);
            if($flash_deal_check){
                $quantity = $product->flash_deal_product->quantity;
                if($product->max_qty>0){
                    if($product->max_qty <= $product->flash_deal_product->quantity){
                        $quantity = $product->max_qty;
                    }else{
                        $quantity = $product->flash_deal_product->quantity;
                    }
                    $outofstockmsg = true;
                }else{
                    $quantity = $product->flash_deal_product->quantity;
                }
            }else{
                $quantity = $product_stock->qty;
                if($product->max_qty>0){
                if($product->max_qty <= $product_stock->qty){
                    $quantity = $product->max_qty;
                }else{
                    $quantity = $product_stock->qty;
                }
                $outofstockmsg = true;
            }else{
                $quantity = $product_stock->qty;
            }
            }

            //Pre-order
            $preorder_check = check_preorder_product($product);
            if($preorder_check){
                $quantity = $product->preorder_max_qty - preorder_product_count($product);
            }

            if($quantity < $request->quantity+$previouscartqty){
                return response()->json(['result' => false, 'message' => ("Maximum")." {$quantity} ".("quantity can be added for a single order!")], 200);
            }

        //discount calculation based on flash deal and regular discount
        //calculation of taxes
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

        // Customer group discount section
        if(isset($request->user_id) && $request->user_id!=''){
            $user_info = User::findOrFail($request->user_id);
            if($user_info->customeringroup){
                $discount_status = $user_info->customeringroup->group->discount_status;
                $start_date = $user_info->customeringroup->group->start_date;
                $end_date = $user_info->customeringroup->group->end_date;
                $cur_date = strtotime(date('Y-m-d H:i:s'));
                if($discount_status==1 && $cur_date >= $start_date && $cur_date <= $end_date){
                    if ($user_info->customeringroup->group->discount_type == 'percent') {
                        $group_price -= ($group_price * $user_info->customeringroup->group->discount) / 100;
                    } elseif ($user_info->customeringroup->group->discount_type == 'amount') {
                        $group_price -= $user_info->customeringroup->group->discount;
                    }
                    if ($discount_applicable) {
                        if($price < $group_price){
                            $price = $price;
                        }else{
                            $price = $group_price;
                        }
                    }else{
                        $price = $group_price;
                    }
                }
            }
        }

        // Customer group discount section
        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }

        if ($product->min_qty > $request->quantity) {
            return response()->json(['result' => false, 'message' => ("Minimum")." {$product->min_qty} ".("item(s) should be ordered")], 200);
        }

        if($preorder_check){
            $stock = $product->preorder_max_qty - preorder_product_count($product);
        }else{
            $stock = $product->stocks->where('variant', $variant)->first()->qty;
        }

        $variant_string = $variant != null && $variant != "" ? ("for")." ($variant)" : "";
        if ($stock < $request->quantity && $product->allow_stock_out_purchases == 0) {
            if ($stock == 0) {
                return response()->json(['result' => false, 'message' => "Stock out"], 200);
            } else {
                return response()->json(['result' => false, 'message' => ("Only") ." {$stock} ".("item(s) are available")." {$variant_string}"], 200);
            }
        }

        $user_info = ($request->user_id != '') ? User::with('customeringroup.group')->find($request->user_id) : null;
        $minprice = getMinimumPriceByVariant($product, $product_stock, 'app', $request->quantity, $user_info);

        Cart::updateOrCreate([
            'user_id' => $request->user_id,
            'owner_id' => $product->user_id,
            'product_id' => $request->id,
            'variation' => $variant
        ], [
            'price' => $minprice,
            'tax' => $tax,
            'shipping_cost' => 0,
            'quantity' => DB::raw("quantity + $request->quantity")
        ]);

        if(\App\Utility\NagadUtility::create_balance_reference($request->cost_matrix) == false){
            return response()->json(['result' => false, 'message' => 'Cost matrix error' ]);
        }

        return response()->json([
            'result' => true,
            'message' => ('Product added to cart successfully')
        ]);
    }

    public function changeQuantity(Request $request)
    {
        $cart = Cart::find($request->id);
        if ($cart != null) {

            $product_stock = $cart->product->stocks->where('variant', $cart->variation)->first();
            if ($product_stock->qty >= $request->quantity) {
                if ($cart->product->max_qty > 0 && $cart->product->max_qty < $request->quantity) {
                    return response()->json(['result' => false, 'message' => ("Maximum")." {$cart->product->max_qty} ".("quantity can be added for a single order!")], 200);
                }
                $cart->update([
                    'quantity' => $request->quantity
                ]);

                return response()->json(['result' => true, 'message' => ('Cart updated')], 200);
            } else {
                if($cart->product->allow_stock_out_purchases == 1){
                    if ($cart->product->max_qty > 0 && $cart->product->max_qty < $request->quantity) {
                        return response()->json(['result' => false, 'message' => ("Maximum")." {$cart->product->max_qty} ".("quantity can be added for a single order!")], 200);
                    }
                    $cart->update([
                        'quantity' => $request->quantity
                    ]);

                    return response()->json(['result' => true, 'message' => ('Cart updated')], 200);
                }
                return response()->json(['result' => false, 'message' => ('Maximum available quantity reached')], 200);
            }
        }

        return response()->json(['result' => false, 'message' => ('Something went wrong')], 200);
    }

    public function process(Request $request)
    {
        $cart_ids = explode(",", $request->cart_ids);
        $cart_quantities = explode(",", $request->cart_quantities);

        if (!empty($cart_ids)) {
            $i = 0;
            foreach ($cart_ids as $cart_id) {
                $cart_item = Cart::with('product.stocks')->where('id', $cart_id)->first();
                if (!$cart_item) {
                    continue;
                }
                $product = $cart_item->product;
                if ($product->min_qty > $cart_quantities[$i]) {
                    return response()->json(['result' => false, 'message' => ("Minimum")." {$product->min_qty} ".("item(s) should be ordered for")." {$product->name}"], 200);
                } elseif ($product->max_qty > 0 && $product->max_qty < $cart_quantities[$i]) {
                    return response()->json(['result' => false, 'message' => ("Maximum")." {$product->max_qty} ".("quantity can be added for a single order for")." {$product->name}!"], 200);
                }

                //Pre-order
                $preorder_check = check_preorder_product($product);
                if($preorder_check){
                    $stock = $product->preorder_max_qty - preorder_product_count($product);
                }else{
                    $stock = $cart_item->product->stocks->where('variant', $cart_item->variation)->first()->qty;
                }
                $variant_string = $cart_item->variation != null && $cart_item->variation != "" ? " ($cart_item->variation)" : "";
                if ($stock >= $cart_quantities[$i]) {
                    $cart_item->update([
                        'quantity' => $cart_quantities[$i]
                    ]);

                } else {
                    if ($stock == 0) {
                        if($product->allow_stock_out_purchases == 1){
                            $cart_item->update([
                                'quantity' => $cart_quantities[$i]
                            ]);
                        }else{
                            return response()->json(['result' => false, 'message' => ("No item is available for")." {$product->name}{$variant_string},".("remove this from cart")], 200);
                        }
                    } else {
                        if($product->allow_stock_out_purchases == 1){
                            $cart_item->update([
                                'quantity' => $cart_quantities[$i]
                            ]);
                        }else{
                            return response()->json(['result' => false, 'message' => ("Only")." {$stock} ".("item(s) are available for")." {$product->name}{$variant_string}"], 200);
                        }
                    }
                }

                $i++;
            }

            return response()->json(['result' => true, 'message' => ('Cart updated')], 200);

        } else {
            return response()->json(['result' => false, 'message' => ('Cart is empty')], 200);
        }


    }

    public function destroy(int $id)
    {
        $cart = Cart::withoutGlobalScopes()->find($id);
        if (! $cart) {
            return response()->json(['result' => false, 'message' => 'Product not found in cart']);
        }
        $shippingCost = $cart->shipping_cost ?? 0;
        $userField = $cart->user_id ? 'user_id' : 'temp_user_id';
        $userId = $cart->{$userField};
        DB::transaction(function () use ($cart, $shippingCost, $userField, $userId) {
            $cart->delete();

            if ($shippingCost > 0) {
                $query = Cart::withoutGlobalScopes()
                    ->where($userField, $userId);

                (clone $query)->update([
                    'shipping_cost' => 0,
                ]);

                (clone $query)->orderBy('id', 'asc')
                    ->limit(1)
                    ->update([
                        'shipping_cost' => $shippingCost,
                    ]);
            }
        });

        $this->store_delivery_info(new Request([
            'user_field' => $userField,
            'user_id' => $userId,
        ]));

        return response()->json(['result' => true, 'message' => 'Product is successfully removed from your cart'], 200);
    }

    public function check_min_order_amount(Request $request)
    {
        //return intval($request->user_id);
        return response()->json(get_total_cart_amount_check(intval($request->user_id)));

    }

    public function store_delivery_info(Request $request)
    {
        $carts = Cart::where('user_id', $request->user_id)->get();

        if($carts->isEmpty()) {
            return response()->json(['result' => false, 'message' => 'Cart is empty' ]);
        }

        $addressId = $carts->first()->address_id ?? null;
        if (! $addressId) {
            return response()->json(['result' => false, 'message' => 'Address not found']);
        }

        $shipping_info = Address::where('id', $addressId)->first();
        $total = 0;
        $tax = 0;
        $shipping = 0;
        $subtotal = 0;

        $shippingCalByOwner = [];

        $dShippingAmount = PHP_INT_MAX;
        if(check_shipping_discount()){
            $addressInfo = Address::find($carts->first()->address_id);
            $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
            $sDiscount = check_shipping_discount_carts($carts, $matchZone->id ?? null);
            if(!empty($sDiscount) && $sDiscount['status']){
                $cartAmount = 0;
                foreach($carts as $cart){
                    $cartAmount += $cart->price * $cart->quantity;
                }
                if($cartAmount >= $sDiscount['min_amount']){
                    $dShippingAmount = $sDiscount['amount'];
                }
            }
        }

        if ($carts && count($carts) > 0) {
            foreach ($carts as $key => $cartItem) {
                $product = \App\Models\Product::find($cartItem['product_id']);
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

                $cartItem->save();

                $cartItem['shipping_cost'] = 0;
                if ($cartItem['shipping_type'] == 'home_delivery') {
                    // Add this condition so single time shipping charge for product owner wise
                    if(!in_array($cartItem['owner_id'],$shippingCalByOwner)){
                        $shippingCalByOwner[]=$cartItem['owner_id'];
                        // $cartItem['shipping_cost'] = getShippingCost($carts, $key);
                        $prevShip = getShippingCost($carts, $key);
                        $dShipping = min($prevShip, $dShippingAmount);
                        $cartItem['shipping_cost'] = abs($dShipping);
                    }
                }


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
                    if (!$cartItem['shipping_cost'] ||
                            $cartItem['shipping_cost'] == null ||
                            $cartItem['shipping_cost'] == 'null') {

                        $cartItem['shipping_cost'] = 0;
                    }
                }

                $shipping += $cartItem['shipping_cost'];
                $cartItem->save();

            }
            $total = $subtotal + $tax + $shipping;
            return response()->json(['result' => true, 'message' => 'Success' ], 200);

        } else {
            return response()->json(['result' => false, 'message' => 'Cart is empty' ]);
        }
    }
}
