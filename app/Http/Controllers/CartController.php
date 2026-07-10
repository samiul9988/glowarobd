<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Address;
use App\Models\Product;
use App\Models\Category;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->all());
        $categories = Category::all();
        if(auth()->user() != null) {
            $user_id = Auth::user()->id;
            if($request->session()->get('temp_user_id')) {
                Cart::where('user_id', $user_id)->delete();
                Cart::where('temp_user_id', $request->session()->get('temp_user_id'))
                        ->update(
                                [
                                    'user_id' => $user_id,
                                    'temp_user_id' => null
                                ]
                );

                Session::forget('temp_user_id');
            }
            $carts = Cart::with('product')->where('user_id', $user_id)->get();
            $cartsgroup = Cart::where('user_id', $user_id)->groupBy('product_id')->sum('quantity');

            $totalcartamount = get_total_cart_amount_check($user_id, $carts);
        } else {
            $totalcartamount = 0;
            $temp_user_id = $request->session()->get('temp_user_id');
            // $carts = Cart::where('temp_user_id', $temp_user_id)->get();
            $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->get() : [] ;
        }

        return view('frontend.view_cart', compact('categories', 'carts', 'totalcartamount'));
    }

    public function showCartModal(Request $request)
    {
        $product = Product::with('stocks', 'productprices')->find($request->id);
        return view('frontend.partials.addToCart', compact('product'));
    }

    public function showCartModalAuction(Request $request)
    {
        $product = Product::find($request->id);
        return view('auction.frontend.addToCartAuction', compact('product'));
    }

    public function addToCart(Request $request)
    {
        $product = Product::with('category', 'brand', 'stocks', 'productprices', 'flash_deal_product.flash_deals')->find($request->id);

        $carts = array();
        $data = array();

        if(auth()->user() != null) {
            $user_id = Auth::user()->id;
            $data['user_id'] = $user_id;
            $carts = Cart::with('product.brand','product.category')->where('user_id', $user_id)->get();
        } else {
            if($request->session()->get('temp_user_id')) {
                $temp_user_id = $request->session()->get('temp_user_id');
            } else {
                $temp_user_id = bin2hex(random_bytes(10));
                $request->session()->put('temp_user_id', $temp_user_id);
            }
            $data['temp_user_id'] = $temp_user_id;
            $carts = Cart::with('product.brand','product.category')->where('temp_user_id', $temp_user_id)->get();
        }

        if (! $product || ! $product->published) {
            return array(
                'status' => 0,
                'cart_count' => count($carts),
                'modal_view' => view('frontend.partials.minsubscriptionorder', [ 'min_qty' => 0 ])->render(),
                'nav_cart_view' => view('frontend.partials.cart')->render(),
                'is' =>'',
            );
        }

        $data['product_id'] = $product->id;
        $data['owner_id'] = $product->user_id;

        $str = '';
        $tax = 0;

        if(!check_subscription_product_cart($request, $carts)){
            return array(
                'status' => 0,
                'cart_count' => count($carts),
                'modal_view' => view('frontend.partials.minsubscriptionorder', [ 'min_qty' => $product->min_qty ])->render(),
                'nav_cart_view' => view('frontend.partials.cart')->render(),
                'is' =>'',
            );
        }

        // return has_preorder_product_to_cart;
        if(count($carts)>0){
            if((has_preorder_product_to_cart($carts) && !check_preorder_product($product)) || (has_regular_product_to_cart($carts) && check_preorder_product($product))){
                return array(
                    'status' => 0,
                    'cart_count' => count($carts),
                    'modal_view' => view('frontend.partials.preOrderWarning')->render(),
                    'nav_cart_view' => view('frontend.partials.cart')->render(),
                    'has' =>has_preorder_product_to_cart($carts),
                    'is' => check_preorder_product($product)
                );
            }
        }

        if($product->auction_product == 0){
            if($product->digital != 1 && $request->quantity < $product->min_qty) {
                return array(
                    'status' => 0,
                    'cart_count' => count($carts),
                    'modal_view' => view('frontend.partials.minQtyNotSatisfied', [ 'min_qty' => $product->min_qty ])->render(),
                    'nav_cart_view' => view('frontend.partials.cart')->render(),
                    'subscription_day' =>'',
                );
            }

            //check the color enabled or disabled for the product
            if($request->has('color')) {
                $str = $request['color'];
            }

            if ($product->digital != 1) {
                //Gets all the choice values of customer choice option and generate a string like Black-S-Cotton
                foreach (json_decode($product->choice_options) as $key => $choice) {
                    if($str != null){
                        $str .= '-'.str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                    }
                    else{
                        $str .= str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                    }
                }
            }

            $data['variation'] = $str;

            $product_stock = collect($product->stocks)->where('variant', $str)->first();
            if(!$product_stock) {
                $product_stock = collect($product->stocks)->first();
            }
            $price = $product_stock->price;
            $group_price = $product_stock->price;

            /* if($product->wholesale_product){
                $wholesalePrice = $product_stock->wholesalePrices->where('min_qty', '<=', $request->quantity)->where('max_qty', '>=', $request->quantity)->first();
                if($wholesalePrice){
                    $price = $wholesalePrice->price;
                    $group_price = $wholesalePrice->price;
                }
            } */

            //$quantity = $product_stock->qty;
            $outofstockmsg = false;
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
            if(count($product->productprices)>0){
                $productprices = $product->productprices->where('start_qty', '<=', $request->quantity)->where('end_qty', '>=', $request->quantity)->first();
                if($productprices){
                    $price = $productprices->price;
                }
                //dd($price);
            }

            $quantity = $product_stock->qty;
            //Pre-order
            $preorder_check = check_preorder_product($product);
            if($preorder_check){
                $quantity = $product->preorder_max_qty - preorder_product_count($product);
            }

            if(($quantity < $request['quantity']) && $product->allow_stock_out_purchases == 0){
                return array(
                    'status' => 0,
                    'cart_count' => count($carts),
                    'modal_view' => view('frontend.partials.outOfStockCart', compact('outofstockmsg', 'quantity'))->render(),
                    'nav_cart_view' => view('frontend.partials.cart')->render(),
                    'subscription_day' =>'',
                );
            }

            if($request['subscriptioin_date']!=''){
                $data['subscription_day'] = @implode(',',$request['subscriptioin_date']);
                $now = new \DateTime( 'now');
                $start = new \DateTime( $now->format('m/d/Y'));
                $nexmonth = date('m/d/Y',strtotime('+ 30 days'));
                $end = new \DateTime( $now->format($nexmonth));
                $interval = new \DateInterval('P1D');
                $period = new \DatePeriod( $start, $interval, $end );
                $day = explode(',',$data['subscription_day']);
                $daycount = 0;
                foreach( $period as $date ){
                for($i=0;$i<count($day);$i++):
                    if($date->format('w') == intval($day[$i])){
                        $daycount++;
                    }
                endfor;
                }
                if($quantity < $request['quantity']*$daycount){
                    return array(
                        'status' => 0,
                        'cart_count' => count($carts),
                        'modal_view' => view('frontend.partials.outOfStocksubscribeCart', [ 'total_qty' => $request['quantity']*$daycount ])->render(),
                        'nav_cart_view' => view('frontend.partials.cart')->render(),
                        'subscription_day' =>'',
                    );
                }
            }

            //discount calculation
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
            /* if(isset(Auth::user()->id)){
                if(Auth::user()->customeringroup){
                    $discount_status = Auth::user()->customeringroup->group->discount_status;
                    $start_date = Auth::user()->customeringroup->group->start_date;
                    $end_date = Auth::user()->customeringroup->group->end_date;
                    $cur_date = strtotime(date('Y-m-d H:i:s'));
                    if($discount_status==1 && $cur_date >= $start_date && $cur_date <= $end_date){
                        if (Auth::user()->customeringroup->group->discount_type == 'percent') {
                            $group_price -= ($group_price * Auth::user()->customeringroup->group->discount) / 100;
                        } elseif (Auth::user()->customeringroup->group->discount_type == 'amount') {
                            $group_price -= Auth::user()->customeringroup->group->discount;
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

            //calculation of taxes
            foreach ($product->taxes as $product_tax) {
                if($product_tax->tax_type == 'percent'){
                    $tax += ($price * $product_tax->tax) / 100;
                }
                elseif($product_tax->tax_type == 'amount'){
                    $tax += $product_tax->tax;
                }
            } */
            $userData = $this->currentlyAuthenticatedUser ?? auth()->check() ? auth()->user()->load('customeringroup.group') : null;
            $minprice = getMinimumPriceByVariant($product, $product_stock, 'web', $request['quantity'], $userData);

            $data['quantity'] = $request['quantity'];
            $data['price'] = $minprice;
            $data['tax'] = $tax;
            //$data['shipping'] = 0;
            $data['shipping_cost'] = 0;
            $data['product_referral_code'] = null;
            $data['cash_on_delivery'] = $product->cash_on_delivery;
            $data['digital'] = $product->digital;
            if($request['subscriptioin_date']!='')
                $data['subscription_day'] = @implode(',',$request['subscriptioin_date']);
            else
                $data['subscription_day'] = '';

            if ($request['quantity'] == null){
                $data['quantity'] = 1;
            }

            if(Cookie::has('referred_product_id') && Cookie::get('referred_product_id') == $product->id) {
                $data['product_referral_code'] = Cookie::get('product_referral_code');
            }

            if($carts && count($carts) > 0){
                $foundInCart = false;

                foreach ($carts as $key => $cartItem){
                    $product = $cartItem->product;
                    if($product->auction_product == 1){
                        return array(
                            'status' => 0,
                            'cart_count' => count($carts),
                            'modal_view' => view('frontend.partials.auctionProductAlredayAddedCart')->render(),
                            'nav_cart_view' => view('frontend.partials.cart')->render(),
                            'subscription_day' =>'',
                        );
                    }

                    if($cartItem->product_id == $request->id) {
                        $product_stock = $product->stocks->where('variant', $str)->first();
                        //$quantity = $product_stock->qty;
                        //Pre-order
                        $preorder_check = check_preorder_product($product);
                        if(!$preorder_check){
                            if(($quantity < $cartItem['quantity'] + $request['quantity']) && $product->allow_stock_out_purchases == 0){
                                return array(
                                    'status' => 0,
                                    'cart_count' => count($carts),
                                    'modal_view' => view('frontend.partials.outOfStockCart', compact('outofstockmsg', 'quantity'))->render(),
                                    'nav_cart_view' => view('frontend.partials.cart')->render(),
                                    'subscription_day' =>'',
                                );
                            }
                        }
                        if(($str != null && $cartItem['variation'] == $str) || $str == null){
                            $foundInCart = true;

                            $cartItem['quantity'] += $request['quantity'];

                            if($product->wholesale_product){
                                $wholesalePrice = $product_stock->wholesalePrices->where('min_qty', '<=', $request->quantity)->where('max_qty', '>=', $request->quantity)->first();
                                if($wholesalePrice){
                                    $price = $wholesalePrice->price;
                                }
                            }

                            $cartItem['price'] = $price;

                            $cartItem->save();
                        }
                    }
                }
                if (!$foundInCart) {
                    Cart::create($data);
                }
            }
            else{
                Cart::create($data);
            }

            return array(
                'status' => 1,
                'cart_count' => count($carts),
                'modal_view' => view('frontend.partials.addedToCart', compact('product', 'data'))->render(),
                'nav_cart_view' => view('frontend.partials.cart')->render(),
                'subscription_day' =>$data['subscription_day'],
                'item' => [
                    'item_name'=> $product->name,
                    'item_id'=> $product->id,
                    'price'=> $minprice,
                    'item_brand'=> $product->brand?->name ?? '',
                    'item_category'=> $product->category?->name ?? '',
                    'item_list_name'=>'',
                    'item_list_id'=>'',
                    'index'=> 0,
                    'quantity'=> $request['quantity'] ?? 1,
                ]
            );
        }
        else{
            $price = $product->bids->max('amount');

            foreach ($product->taxes as $product_tax) {
                if($product_tax->tax_type == 'percent'){
                    $tax += ($price * $product_tax->tax) / 100;
                }
                elseif($product_tax->tax_type == 'amount'){
                    $tax += $product_tax->tax;
                }
            }

            $data['quantity'] = 1;
            $data['price'] = $price;
            $data['tax'] = $tax;
            $data['shipping_cost'] = 0;
            $data['product_referral_code'] = null;
            $data['cash_on_delivery'] = $product->cash_on_delivery;
            $data['digital'] = $product->digital;

            if(count($carts) == 0){
                Cart::create($data);
            }

            return array(
                'status' => 1,
                'cart_count' => count($carts),
                'modal_view' => view('frontend.partials.addedToCart', compact('product', 'data'))->render(),
                'nav_cart_view' => view('frontend.partials.cart')->render(),
            );
        }
    }

    public function addGiftToCart(Request $request)
    {
        $giftOffer = \App\Models\GiftOffer::valid()->find($request->offer_id);
        if (!$giftOffer) {
            return response()->json(['success' => false, 'message' => 'Offer not found.'], 404);
        }

        $giftItem = \App\Models\GiftOfferItem::with('product')->where('gift_offer_id', $request->offer_id)->find($request->item_id);
        if (!$giftItem) {
            return response()->json(['success' => false, 'message' => 'Gift item not found.'], 404);
        }

        $userId = auth()->check() ? auth()->id() : $request->session()->get('temp_user_id');
        $userField = auth()->check() ? 'user_id' : 'temp_user_id';
        $carts = Cart::withoutGlobalScopes()->where($userField, $userId)->get();

        $regularCarts = $carts->where('cart_type', 'regular');
        $regularCartTotal = $regularCarts->sum(function($cart) {
            return $cart->price * $cart->quantity;
        });

        $otherCarts = $carts->where('cart_type', '!=', 'regular');
        if ($otherCarts->count() && !$otherCarts->where('gift_offer_id', $giftOffer->id)->count()) {
            return response()->json(['success' => false, 'message' => 'You have already added a gift item from another offer to your cart.'], 400);
        }


        if ($otherCarts->count()) {
            if ($otherCarts->count() >= $giftOffer->max_item_per_order) {
                return response()->json(['success' => false, 'message' => 'You have already added the maximum allowed gift items to your cart.'], 400);
            } elseif ($otherCarts->sum('quantity') >= $giftOffer->max_qty_per_order) {
                return response()->json(['success' => false, 'message' => 'You have already added the maximum allowed gift items quantity to your cart.'], 400);
            } elseif ($otherCarts->where('product_id', $giftItem->product_id)->sum('quantity') >= $giftItem->available_qty) {
                return response()->json(['success' => false, 'message' => 'You can only add up to the available quantity of this gift item.'], 400);
            }
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

        $cart = Cart::withoutGlobalScopes()->updateOrCreate([
            $userField => $userId,
            'product_id' => $giftItem->product_id,
            'gift_offer_id' => $giftOffer->id,
            'gift_offer_item_id' => $giftItem->id,
            'cart_type' => 'gift',
        ], [
            'owner_id' => $giftItem->product->user_id,
            'variation' => '',
            'quantity' => $otherCarts->where('product_id', $giftItem->product_id)->sum('quantity') + 1,
            'price' => $giftItem->offer_price,
            'shipping_type' => '',
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Gift item added to cart successfully.',
            'cart_item' => [
                'id' => $cart->id,
                'product_id' => $cart->product_id,
                'name' => $cart->product->name,
                'thumbnail' => uploaded_asset($cart->product->thumbnail_img),
                'variation' => $cart->variation,
                'quantity' => $cart->quantity,
                'price' => $cart->price,
                'price_formatted' => single_price($cart->price),
                'original_price' => $cart->product->web_price,
                'original_price_formatted' => single_price($cart->product->web_price),
                'save_amount' => single_price(max(0, ($cart->product->web_price - $cart->price) * $cart->quantity))
            ]
        ], 200);
    }

    //removes from Cart
    public function removeFromCart(Request $request)
    {
        Cart::destroy($request->id);
        if(auth()->user() != null) {
            $user_id = Auth::user()->id;
            $carts = Cart::where('user_id', $user_id)->get();
        } else {
            $temp_user_id = $request->session()->get('temp_user_id');
            $carts = Cart::where('temp_user_id', $temp_user_id)->get();
        }

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

        $shipping_charge = min($carts->sum('shipping_cost'), $discountShippingCharge);
        if(get_setting('spa_checkout') == 1){
            return array(
                'cart_count' => count($carts),
                'cart_view' => view('frontend.spa_checkout.view_cart', compact('carts', 'sDiscount', 'shipping_charge'))->render(),
                'nav_cart_view' => view('frontend.partials.cart')->render(),
            );
        }else{
            return array(
                'cart_count' => count($carts),
                'cart_view' => view('frontend.partials.cart_details', compact('carts', 'sDiscount', 'shipping_charge'))->render(),
                'nav_cart_view' => view('frontend.partials.cart')->render(),
            );
        }
    }

    //updated the quantity for a cart item
    public function updateQuantity(Request $request)
    {
        $cartItem = Cart::findOrFail($request->id);

        if($cartItem['id'] == $request->id){
            $product = Product::find($cartItem['product_id']);
            $product_stock = $product->stocks->where('variant', $cartItem['variation'])->first();
            $flash_deal_check = check_flash_deal_product($product);
            //$quantity = $product_stock->qty;
            $price = $product_stock->price;

            if ($flash_deal_check) {
                $quantity = $product->flash_deal_product->quantity;
                if($product->max_qty>0){
                    if($product->max_qty <= $product->flash_deal_product->quantity){
                        $quantity = $product->max_qty;
                    }else{
                        $quantity = $product->flash_deal_product->quantity;
                    }
                }else{
                    $quantity = $product->flash_deal_product->quantity;
                }
            } else {
                $quantity = $product_stock->qty;
                if ($product->max_qty>0) {
                    if ($product->max_qty <= $product_stock->qty) {
                        $max_limit = $product->max_qty;
                    } else {
                        $max_limit = $product_stock->qty;
                    }
                } else {
                    $max_limit = $product_stock->qty;
                }
            }

            //Pre-order
            $preorder_check = check_preorder_product($product);
            if($preorder_check){
                $quantity = $product->preorder_max_qty - preorder_product_count($product);
            }

            if($quantity >= $request->quantity) {
                if($request->quantity >= $product->min_qty){
                    $cartItem['quantity'] = $request->quantity;
                    $cartItem['price'] = getMinimumPriceByVariant($product, $product_stock, 'web', $request->quantity, $this->currentlyAuthenticatedUser);
                }
            }

            if($product->wholesale_product){
                $wholesalePrice = $product_stock->wholesalePrices->where('min_qty', '<=', $request->quantity)->where('max_qty', '>=', $request->quantity)->first();
                if($wholesalePrice){
                    $price = $wholesalePrice->price;
                }
            }

            $cartItem->save();
        }

        $userId = auth()->check() ? auth()->id() : $request->session()->get('temp_user_id');
        $userField = auth()->check() ? 'user_id' : 'temp_user_id';
        $carts = Cart::where($userField, $userId)->get();

        $sDiscount = array();

        $discountShippingCharge = PHP_INT_MAX;
        if(check_shipping_discount()){
            $discountShippingCharge = getDiscountShippingCharge($carts, null);
            $sDiscount = check_shipping_discount_carts($carts, null);
        }

        $shipping_charge = min($carts->sum('shipping_cost'), $discountShippingCharge);
        return array(
            'cart_count' => count($carts),
            'cart_view' => ($request->has('isCartPage') && $request->isCartPage) ? view('frontend.partials.cart_details', compact('carts', 'quantity'))->render() : ((get_setting('spa_checkout') == 1) ? view('frontend.spa_checkout.view_cart', compact('carts', 'quantity', 'sDiscount', 'shipping_charge'))->render() : view('frontend.partials.cart_details', compact('carts', 'quantity'))->render()),
            'nav_cart_view' => view('frontend.partials.cart')->render(),
        );
    }

    public function minordercheck(Request $request)
    {
        $user_id = Auth::check() ? Auth::id() : $request->session()->get('temp_user_id');
        return get_total_cart_amount_check($user_id, null, !Auth::check() && get_setting('guest_order_activation') == 1);
    }
}
