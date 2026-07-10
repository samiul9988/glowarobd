<?php

namespace App\Http\Controllers\Api\V3;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function summary($user_id)
    {
        $source = request()->header('source', 'app'); // 'app' or 'web'

        $userField = 'user_id';
        if (Str::startsWith($user_id, 'tmp')) {
            $userField = 'temp_user_id';
        }

        // $items = Cart::where($userField, $user_id)->get();
        $items = Cart::withoutGlobalScopes()
            ->with('product.stocks', 'product.productprices', 'product.brand', 'product.category')
            ->whereNotNull($userField)
            ->where($userField, $user_id)
            ->get();

        $items = validateCarts($items);

        if ($items->isEmpty()) {
            return response()->json([
                'sub_total' => format_price(0.00),
                'calculable_sub_total' => 0.00,
                'grand_sub_total' => format_price(0.00),
                'calculable_grand_sub_total' => 0.00,
                'tax' => format_price(0.00),
                'shipping_cost' => format_price(0.00),
                'shipping_discount' => [],
                'discount' => format_price(0.00),
                'grand_total' => format_price(0.00),
                'grand_total_value' => 0.00,
                'coupon_code' => '',
                'coupon_applied' => false,
            ]);
        }

        $sum = 0.00;
        $subtotal = 0.00;
        $tax = 0.00;
        $cartTotal = $items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $user_info = Str::startsWith($user_id, 'tmp') ? null : User::with('customeringroup.group')->find($user_id);
        foreach ($items as $index => $cartItem) {
            if ($cartItem->cart_type === 'regular') {
                $product = $cartItem->product;
                if (! $product) {
                    $cartItem->delete();
                    continue;
                }
                $product_stock = $product->stocks?->where('variant', $cartItem['variation'])->first();
                $cartItem->price = getMinimumPriceByVariant($product, $product_stock, $source, $cartItem->quantity, $user_info);
            }
            // Validate Coupon Discount
            if ($index === 0 && !empty($cartItem->coupon_code)) {
                $coupon = Coupon::where('status', 1)->where('code', $cartItem->coupon_code)->first();
                if (!$coupon) {
                    $cartItem->discount = 0;
                    $cartItem->coupon_applied = 0;
                    $cartItem->coupon_code = null;
                } else {
                    $couponDetails = json_decode($coupon->details, true);
                    $isInvalid = now()->timestamp < $coupon->start_date ||
                        now()->timestamp > $coupon->end_date ||
                        ($couponDetails['min_buy'] ?? 0) > $cartTotal;
                    if ($isInvalid) {
                        $cartItem->discount = 0;
                        $cartItem->coupon_applied = 0;
                        $cartItem->coupon_code = null;
                    } else {
                        $discountAmount = 0;
                        if ($coupon->type === 'cart_base') {
                            $discountAmount = match ($coupon->discount_type) {
                                'percent' => ($cartTotal * $coupon->discount) / 100,
                                'amount' => $coupon->discount,
                                default => 0
                            };
                        } elseif ($coupon->type === 'product_base') {
                            $eligibleProducts = collect($couponDetails)->pluck('product_id')->toArray();
                            $eligibleItems = $items->whereIn('product_id', $eligibleProducts);
                            foreach ($eligibleItems as $eligibleItem) {
                                $itemTotal = $eligibleItem->price * $eligibleItem->quantity;

                                $discountAmount += match ($coupon->discount_type) {
                                    'percent' => ($itemTotal * $coupon->discount) / 100,
                                    'amount'  => $coupon->discount,
                                    default   => 0,
                                };
                            }
                        } elseif ($coupon->type === 'shipping_charge') {
                            $shippingCost = $items->sum('shipping_cost');
                            $discountAmount = match ($coupon->discount_type) {
                                'percent' => ($shippingCost * $coupon->discount) / 100,
                                'amount'  => $coupon->discount,
                                default   => 0,
                            };
                        }
                        $cartItem->discount = min($discountAmount, $couponDetails['max_discount'] ?? $cartTotal);
                        $cartItem->coupon_applied = 1;
                        $cartItem->coupon_code = $coupon->code;
                    }
                }

                $cartItem->save();
            }
            $item_sum = 0.00;
            $item_sum += ($cartItem->price + $cartItem->tax) * $cartItem->quantity;
            $item_sum += $cartItem->shipping_cost - $cartItem->discount;
            $sum += $item_sum;

            $subtotal += $cartItem->price * $cartItem->quantity;
            $tax += $cartItem->tax * $cartItem->quantity;
        }

        $sDiscount = [];
        $shippingDiscountAmount = PHP_INT_MAX;
        if (check_shipping_discount()) {
            $addressInfo = Address::find($items->first()->address_id);
            $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
            $sDiscount = check_shipping_discount_carts($items, $matchZone->id ?? null);
            $shippingDiscountAmount = getDiscountShippingCharge($items, $matchZone->id ?? null);
        }

        $shipping_cost = min($shippingDiscountAmount, $items->sum('shipping_cost'));
        if ($sDiscount && $subtotal >= $sDiscount['min_amount']) {
            $shipping_cost = $sDiscount['amount'];
            $sum = $sum - $items->sum('shipping_cost') + $shipping_cost;
        } elseif ($shipping_cost < $items->sum('shipping_cost')) {
            $sum = $sum - $items->sum('shipping_cost') + $shipping_cost;
        }

        $regularSubtotal = $items->where('cart_type', 'regular')->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'sub_total' => format_price($regularSubtotal),
            'calculable_sub_total' => number_format($regularSubtotal, 2),
            'grand_sub_total' => format_price($subtotal),
            'calculable_grand_sub_total' => number_format($subtotal, 2),
            'tax' => format_price($tax),
            'shipping_cost' => format_price($shipping_cost),
            'shipping_discount' => [
                'amount' => (string) ($sDiscount['amount'] ?? 0),
                'status' => $sDiscount['status'] ?? false,
                'min_amount' => (string) ($sDiscount['min_amount'] ?? 0),
            ],
            'discount' => format_price($items->sum('discount')),
            'grand_total' => format_price($sum),
            // 'grand_total_value' => (float) number_format(convert_price($sum), 2),
            'grand_total_value' => (float) convert_price($sum),
            'coupon_code' => $items[0]->coupon_code,
            'coupon_applied' => $items[0]->coupon_applied == 1,
        ]);
    }

    public function getList($user_id)
    {
        $source = request()->header('source', 'app'); // 'app' or 'web'
        $userField = 'user_id';
        if (Str::startsWith($user_id, 'tmp')) {
            $userField = 'temp_user_id';
        }

        $owner_ids = Cart::withoutGlobalScopes()
            ->where($userField, $user_id)
            ->distinct()
            ->pluck('owner_id')
            ->toArray();

        $currency_symbol = currency_symbol();
        $shops = [];

        $user_info = Str::startsWith($user_id, 'tmp') ? null : User::with('customeringroup.group')->find($user_id);
        if (! empty($owner_ids)) {
            foreach ($owner_ids as $owner_id) {
                $shop = [];
                $carts = Cart::withoutGlobalScopes()
                    ->where($userField, $user_id)
                    ->where('owner_id', $owner_id)
                    ->get();
                $shop_items_raw_data = validateCarts($carts)->toArray();
                $shop_items_data = [];
                if (! empty($shop_items_raw_data)) {
                    foreach ($shop_items_raw_data as $shop_items_raw_data_item) {
                        $product = Product::with('stocks')->where('id', $shop_items_raw_data_item['product_id'])->first();
                        if ($shop_items_raw_data_item['cart_type'] === 'regular') {
                            $product_stock = $product->stocks->where('variant', $shop_items_raw_data_item['variation'])->first();
                            $shop_items_raw_data_item['price'] = getMinimumPriceByVariant($product, $product_stock, $source, $shop_items_raw_data_item['quantity'], $user_info);
                        }
                        $shop_items_data_item['id'] = intval($shop_items_raw_data_item['id']);
                        $shop_items_data_item['cart_type'] = $shop_items_raw_data_item['cart_type'];
                        $shop_items_data_item['owner_id'] = intval($shop_items_raw_data_item['owner_id']);
                        $shop_items_data_item['user_id'] = intval($shop_items_raw_data_item['user_id']);
                        $shop_items_data_item['product_id'] = intval($shop_items_raw_data_item['product_id']);
                        $shop_items_data_item['product_name'] = $product->name;
                        $shop_items_data_item['product_slug'] = $product->slug;
                        $shop_items_data_item['min_order_amount'] = (float) $product->min_order_amount;
                        // $shop_items_data_item["discount_type"] = $product->discount_type;
                        $shop_items_data_item['product_thumbnail_image'] = api_asset($product->thumbnail_img);
                        $shop_items_data_item['variation'] = $shop_items_raw_data_item['variation'];
                        $shop_items_data_item['price'] = (float) $shop_items_raw_data_item['price'];
                        $shop_items_data_item['base_price'] = (float) $product->unit_price ?? (float) $shop_items_raw_data_item['price'];
                        $shop_items_data_item['currency_symbol'] = $currency_symbol;
                        $shop_items_data_item['tax'] = (float) $shop_items_raw_data_item['tax'];
                        $shop_items_data_item['shipping_cost'] = (float) $shop_items_raw_data_item['shipping_cost'];
                        $shop_items_data_item['shipping_type'] = $shop_items_raw_data_item['shipping_type'];
                        $shop_items_data_item['shipping_method'] = $shop_items_raw_data_item['shipping_method'];
                        $shop_items_data_item['quantity'] = intval($shop_items_raw_data_item['quantity']);
                        $shop_items_data_item['lower_limit'] = intval($product->min_qty);
                        $shop_items_data_item['upper_limit'] = intval($product->stocks->where('variant', $shop_items_raw_data_item['variation'])->first()->qty);

                        $shop_items_data[] = $shop_items_data_item;

                    }
                }

                $shop_data = Shop::where('user_id', $owner_id)->first();
                if ($shop_data) {
                    $shop['name'] = $shop_data->name;
                    $shop['owner_id'] = (int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;
                } else {
                    $shop['name'] = 'Inhouse';
                    $shop['owner_id'] = (int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;
                }
                $shops[] = $shop;
            }
        }

        // dd($shops);

        return response()->json($shops);
    }

    public function getListWithDelivery($user_id, $address_id)
    {
        $source = request()->header('source', 'app'); // 'app' or 'web'

        $userField = 'user_id';
        $isGuest = false;
        if (Str::startsWith($user_id, 'tmp')) {
            $userField = 'temp_user_id';
            $isGuest = true;
        }

        $owner_ids = Cart::withoutGlobalScopes()
            ->where($userField, $user_id)
            ->distinct()
            ->pluck('owner_id')
            ->toArray();

        $currency_symbol = currency_symbol();
        $shops = [];

        $addressInfo = \App\Models\Address::find($address_id);
        if (! $addressInfo) {
            return response()->json(['success' => false, 'message' => 'Address not found'], 404);
        }
        $matchZone = \App\Models\ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id])->first();

        $shippingMethods = null;
        $shippingCharge = null;
        $shippingDiscount = \App\Models\ShippingDiscount::where('status', 1)
            ->where('start_date', '<=', strtotime(date('d-m-Y')))
            ->where('end_date', '>=', strtotime(date('d-m-Y')));
        if ($matchZone) {
            $shippingDiscount = $shippingDiscount->where('zone_id', $matchZone->id)->first();
            if ($matchZone->rates !== null) {
                $shippingMethods = json_decode($matchZone->rates);
            }
        } else {
            $matchZone = \App\Models\ShippingZone::where('rest_of_the_world', 1)->first();
            $shippingDiscount = $shippingDiscount->where('zone_id', $matchZone->id)->first();
            if ($matchZone && $matchZone->rates !== null) {
                $shippingMethods = json_decode($matchZone->rates);
            }
        }

        $user_info = $isGuest ? null : User::with('customeringroup.group')->find($user_id);
        $cartTotal = 0;
        if (! empty($owner_ids)) {
            foreach ($owner_ids as $owner_id) {
                $shop = [];
                $shop_items = Cart::withoutGlobalScopes()->where($userField, $user_id)->where('owner_id', $owner_id)->get();
                $shop_items = validateCarts($shop_items);
                $discountShippingCharge = getDiscountShippingCharge($shop_items, $matchZone->id ?? null);
                $shop_items_raw_data = $shop_items->toArray();
                $shop_items_data = [];
                if (! empty($shop_items_raw_data)) {
                    foreach ($shop_items_raw_data as $shop_items_raw_data_item) {
                        $product = Product::with('stocks')->where('id', $shop_items_raw_data_item['product_id'])->first();
                        if ($shop_items_raw_data_item['cart_type'] === 'regular') {
                            $product_stock = $product->stocks->where('variant', $shop_items_raw_data_item['variation'])->first();
                            $shop_items_raw_data_item['price'] = getMinimumPriceByVariant($product, $product_stock, $source, $shop_items_raw_data_item['quantity'], $user_info);
                        }
                        $shop_items_data_item['id'] = intval($shop_items_raw_data_item['id']);
                        $shop_items_data_item['cart_type'] = $shop_items_raw_data_item['cart_type'];
                        $shop_items_data_item['owner_id'] = intval($shop_items_raw_data_item['owner_id']);
                        $shop_items_data_item['user_id'] = intval($shop_items_raw_data_item['user_id']);
                        $shop_items_data_item['product_id'] = intval($shop_items_raw_data_item['product_id']);
                        $shop_items_data_item['product_name'] = $product->name;
                        $shop_items_data_item['product_thumbnail_image'] = api_asset($product->thumbnail_img);
                        $shop_items_data_item['variation'] = $shop_items_raw_data_item['variation'];
                        $shop_items_data_item['price'] = (float) $shop_items_raw_data_item['price'];
                        $shop_items_data_item['base_price'] = (float) $product->unit_price ?? (float) $shop_items_raw_data_item['price'];
                        $shop_items_data_item['currency_symbol'] = $currency_symbol;
                        $shop_items_data_item['tax'] = (float) $shop_items_raw_data_item['tax'];
                        $shop_items_data_item['shipping_cost'] = (float) $shop_items_raw_data_item['shipping_cost'];
                        $shop_items_data_item['shipping_type'] = $shop_items_raw_data_item['shipping_type'];
                        $shop_items_data_item['shipping_method'] = $shop_items_raw_data_item['shipping_method'];
                        $shop_items_data_item['quantity'] = intval($shop_items_raw_data_item['quantity']);
                        $shop_items_data_item['lower_limit'] = intval($product->min_qty);
                        $shop_items_data_item['upper_limit'] = intval($product->stocks->where('variant', $shop_items_raw_data_item['variation'])->first()->qty);

                        $shop_items_data[] = $shop_items_data_item;

                        $cartTotal += $shop_items_raw_data_item['price'] * $shop_items_raw_data_item['quantity'];
                    }
                }

                $shop_data = Shop::where('user_id', $owner_id)->first();

                // dd($matchZone, $shippingDiscount, $shippingDiscount?->threshold_amount, $cartTotal);
                $shippingMethodItems = [];
                if ($shippingMethods != null) {
                    foreach ($shippingMethods as $k => $v) {
                        if ($shippingDiscount && $shippingDiscount->threshold_amount <= $cartTotal) {
                            $shippingCharge = $shippingDiscount->s_charge;
                        } else {
                            $shippingCharge = $v->price;
                        }
                        $shipping_method = \App\Models\ShippingMethod::find($v->id);
                        if (! $shipping_method) {
                            continue;
                        }
                        $shippingMethodItems[] = [
                            'name' => 'shipping_method_'.(int) $owner_id,
                            'value' => $v->id,
                            'method_name' => $shipping_method->name,
                            'method_price' => (string) min($discountShippingCharge, get_shipping_price($shippingCharge, $isGuest ? null : $user_id)),
                            'method_logo' => uploaded_asset($shipping_method->logo),
                        ];
                    }
                }

                $pickUpPoints = [];
                foreach (\App\Models\PickupPoint::where('pick_up_status', 1)->get() as $key => $pick_up_point) {
                    $pickUpPoints[] = [
                        'name' => 'pickup_point_id_'.(int) $owner_id,
                        'value' => $pick_up_point->id,
                        'pickup_name' => $pick_up_point->name,
                        'pickup_phone' => $pick_up_point->phone,
                    ];
                }

                if ($shop_data) {
                    $shop['name'] = $shop_data->name;
                    $shop['owner_id'] = (int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;
                    $shop['shipping_type'][] = [
                        'name' => 'shipping_type_'.(int) $owner_id,
                        'value' => 'home_delivery',
                        'methods' => $shippingMethodItems,
                        'pickUpPoints' => [],
                    ];
                } else {
                    $shop['name'] = 'Inhouse';
                    $shop['owner_id'] = (int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;

                    $shop['shipping_type'][] = [
                        'name' => 'shipping_type_'.(int) $owner_id,
                        'value' => 'home_delivery',
                        'methods' => $shippingMethodItems,
                        'pickUpPoints' => [],
                    ];

                    if (\App\Models\BusinessSetting::where('type', 'pickup_point')->first()->value == 1) {
                        $shop['shipping_type'][] = [
                            'name' => 'shipping_type_'.(int) $owner_id,
                            'value' => 'pickup_point',
                            'methods' => [],
                            'pickUpPoints' => $pickUpPoints,
                        ];
                    }
                }
                $shops[] = $shop;
            }
        }

        // dd($shops);

        return response()->json($shops);
    }

    public function add(Request $request)
    {
        $product = Product::with('stocks', 'flash_deal_product')->find($request->id);
        if (! $product) {
            return response()->json(['result' => false, 'message' => 'Product not found'], 404);
        } elseif (! $product->published) {
            return response()->json(['result' => false, 'message' => 'Product is for view only'], 404);
        }
        $variant = $request->variant;
        $source = $request->header('source', 'app'); // 'app' or 'web'
        $tax = 0;
        $previouscartqty = 0;

        $previouscart = Cart::where($request->user_field, $request->user_id)
            ->where('owner_id', $product->user_id)
            ->where('product_id', $request->id)
            ->where('variation', $variant)
            ->first();
        if ($previouscart) {
            $previouscartqty = $previouscart->quantity;
        }
        $product_stock = $product->stocks->where('variant', $variant)->first();
        $price = $product_stock->price;
        $group_price = $product_stock->price;

        // Pre-order
        $carts = [];
        $carts = Cart::where($request->user_field, $request->user_id)->get();
        $isPreorder = check_preorder_product($product);
        if (count($carts) > 0) {
            if ((has_preorder_product_to_cart($carts) && ! $isPreorder) || (has_regular_product_to_cart($carts) && $isPreorder)) {
                return response()->json(['result' => false, 'message' => ('You can not add regular products & pre-order products in a single order!')], 200);
            }
        }

        $outofstockmsg = false;
        $flash_deal_check = check_flash_deal_product($product);
        if($flash_deal_check && $product->flash_deal_product?->quantity <= 0) {
            remove_from_flashdeal($product->flash_deal_product?->flash_deal_id ?? 0, $product->id);
            $product->refresh();
        }

        if ($flash_deal_check && $product->flash_deal_product?->quantity > 0) {
            $baseQty = $product->flash_deal_product->quantity;
        } else {
            $baseQty = $product_stock->qty;
        }

        $outofstockmsg = $product->max_qty > 0;

        $quantity = $product->max_qty > 0
            ? min($product->max_qty, $baseQty)
            : $baseQty;


        // Pre-order
        if ($isPreorder) {
            $quantity = $product->preorder_max_qty - preorder_product_count($product);
        }

        if ($quantity < $request->quantity + $previouscartqty) {
            return response()->json([
                'result' => false,
                'message' => ('Maximum')." {$quantity} ".('quantity can be added for a single order!'),
            ], 200);
        }

        // discount calculation based on flash deal and regular discount
        // calculation of taxes
        $discount_applicable = false;
        $now = strtotime(date('d-m-Y H:i:s'));
        if ($product->discount_start_date == null || ($now >= $product->discount_start_date && $now <= $product->discount_end_date)) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $price -= $product->discount;
            }
        }

        // Check customer group discount section only if not a guest user
        $user_info = null;
        if (! Str::startsWith($request->user_id, 'tmp_')) {
            $user_info = User::with('customeringroup.group')->find($request->user_id);
        }
        if ($user_info && $user_info->customeringroup) {
            $discount_status = $user_info->customeringroup->group->discount_status;
            $start_date = $user_info->customeringroup->group->start_date;
            $end_date = $user_info->customeringroup->group->end_date;
            $cur_date = strtotime(date('Y-m-d H:i:s'));
            if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                if ($user_info->customeringroup->group->discount_type == 'percent') {
                    $group_price -= ($group_price * $user_info->customeringroup->group->discount) / 100;
                } elseif ($user_info->customeringroup->group->discount_type == 'amount') {
                    $group_price -= $user_info->customeringroup->group->discount;
                }
                if ($discount_applicable) {
                    if ($price < $group_price) {
                        $price = $price;
                    } else {
                        $price = $group_price;
                    }
                } else {
                    $price = $group_price;
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
            return response()->json(['result' => false, 'message' => ('Minimum')." {$product->min_qty} ".('item(s) should be ordered')], 200);
        }

        if ($isPreorder) {
            $stock = $product->preorder_max_qty - preorder_product_count($product);
        } else {
            $stock = $product->stocks->where('variant', $variant)->first()->qty;
        }

        $variant_string = $variant != null && $variant != '' ? ('for')." ($variant)" : '';
        if ($stock < $request->quantity && $product->allow_stock_out_purchases == 0) {
            if ($stock == 0) {
                return response()->json(['result' => false, 'message' => 'Stock out'], 200);
            } else {
                return response()->json(['result' => false, 'message' => ('Only')." {$stock} ".('item(s) are available')." {$variant_string}"], 200);
            }
        }

        $minprice = getMinimumPriceByVariant($product, $product_stock, $source, $request->quantity, $user_info);
        // dd($product_stock, $user_info, getMinimumPriceByVariant($product, $product_stock, $source, $request->quantity, $user_info));

        $cart = Cart::updateOrCreate([
            $request->user_field => $request->user_id,
            'owner_id' => $product->user_id,
            'product_id' => $request->id,
            'variation' => $variant,
        ], [
            'price' => $minprice,
            'tax' => $tax,
            'shipping_cost' => 0,
            'quantity' => DB::raw("quantity + $request->quantity"),
        ]);

        if (\App\Utility\NagadUtility::create_balance_reference($request->cost_matrix) == false) {
            return response()->json(['result' => false, 'message' => 'Cost matrix error']);
        }

        return response()->json([
            'result' => true,
            'message' => ('Product added to cart successfully'),
            // 'cart' => $cart
        ]);
    }

    public function updateQuantityForGiftOfferItem(Cart $cart, int $newQuantity)
    {
        // Validate quantity
        if ($newQuantity <= 0) {
            $cart->delete(); // Remove cart item if quantity is negative

            return response()->json([
                'result' => true,
                'message' => 'Gift item removed from cart',
            ]);
        }

        $cart->loadMissing('giftOffer');
        $cart->loadMissing('giftOfferItem');
        $giftOffer = $cart->giftOffer;
        $giftOfferItem = $cart->giftOfferItem;

        if (! $giftOfferItem || ! $giftOffer || ! $giftOffer->isValid()) {
            $cart->delete(); // Remove invalid cart item

            return response()->json([
                'result' => false,
                'message' => 'Gift offer item is not valid or has expired',
            ], 400);
        }

        $product = $cart->product;
        $productStock = $product->stocks->where('variant', $cart->variation)->first();

        if (! $productStock) {
            $productStock = $product->stocks->first();
        }

        $availableQty = $productStock->qty ?? 0;
        if ($availableQty <= 0) {
            $giftOfferItem->available_qty = 0;
            $giftOfferItem->save();
            $cart->delete(); // Remove cart item if out of stock

            return response()->json([
                'result' => false,
                'message' => 'This gift item is out of stock',
            ], 400);
        }

        // Calculate new quantity
        $isPlus = $newQuantity > $cart->quantity;
        if ($isPlus && $newQuantity > $availableQty) {
            return response()->json([
                'result' => false,
                'message' => "Only {$availableQty} items available for this gift offer",
                'available_qty' => $availableQty,
            ], 400);
        }

        if ($isPlus && $cart->quantity >= $giftOffer->max_qty_per_order) {
            return response()->json([
                'result' => false,
                'message' => 'You have already added the maximum allowed gift items quantity to your cart.',
            ], 400);
        }

        if ($newQuantity > $giftOfferItem->available_qty) {
            return response()->json([
                'result' => false,
                'message' => "Only {$giftOfferItem->available_qty} items available for this gift offer",
                'available_qty' => $giftOfferItem->available_qty,
            ], 400);
        }

        // Update cart and gift offer item usage
        $cart->quantity = $newQuantity;
        $cart->save();

        return response()->json([
            'result' => true,
            'message' => 'Cart Updated',
        ]);
    }

    public function changeQuantity(Request $request)
    {
        $cart = Cart::withoutGlobalScopes()
            ->with('product.stocks', 'product.productprices')
            ->find($request->id);
        if (! $cart) {
            return response()->json([
                'result' => false,
                'message' => 'Cart item not found',
            ], 404);
        }

        // Vaildate gift offer cart
        if ($cart->cart_type !== 'regular') {
            return $this->updateQuantityForGiftOfferItem($cart, $request->quantity);
        }

        // Validate regular cart
        if ($cart->product->stocks->where('variant', $cart->variation)->first()->qty >= $request->quantity) {
            $cart->update([
                'quantity' => $request->quantity,
            ]);

            return response()->json(['result' => true, 'message' => ('Cart updated')], 200);
        } else {
            if ($cart->product->allow_stock_out_purchases == 1) {
                $cart->update([
                    'quantity' => $request->quantity,
                ]);

                return response()->json(['result' => true, 'message' => ('Cart updated')], 200);
            }

            return response()->json(['result' => false, 'message' => ('Maximum available quantity reached')], 200);
        }
    }

    public function process(Request $request)
    {
        $cart_ids = array_filter(explode(',', $request->cart_ids));
        $cart_quantities = explode(',', $request->cart_quantities);

        if (empty($cart_ids) || count($cart_ids) == 0 || count($cart_ids) != count($cart_quantities)) {
            return response()->json(['result' => false, 'message' => 'Cart is empty'], 200);
        }

        $carts = Cart::withoutGlobalScopes()
            ->with(['product.stocks', 'product.productprices'])
            ->whereIn('id', $cart_ids)
            ->get()
            ->keyBy('id');

        $update = 0;
        foreach ($cart_ids as $index => $cart_id) {
            $quantity = (int) ($cart_quantities[$index] ?? 0);

            if (! $quantity || ! isset($carts[$cart_id])) {
                continue;
            }

            $cart_item = $carts[$cart_id];
            $product = $cart_item->product;

            if (! $product) {
                continue;
            }

            /** Gift Offer Handling */
            if ($cart_item->cart_type != 'regular') {
                return response()->json([
                    'result' => false,
                    'message' => 'Only regular cart items can be processed',
                ], 400);
            }

            if ($product->min_qty > $quantity) {
                return response()->json([
                    'result' => false,
                    'message' => "Minimum {$product->min_qty} item(s) should be ordered for {$product->name}",
                ]);
            }

            /** Stock Calculation */
            if (check_preorder_product($product)) {
                $stock = $product->preorder_max_qty - preorder_product_count($product);
            } else {
                $stockModel = $product->stocks
                    ->where('variant', $cart_item->variation)
                    ->first();
                $stock = $stockModel->qty ?? 0;
            }

            $variant_string = $cart_item->variation
                ? " ({$cart_item->variation})"
                : '';

            /** Stock Validation */
            if ($stock >= $quantity || $product->allow_stock_out_purchases == 1) {
                $cart_item->update([
                    'quantity' => $quantity,
                ]);
            } else {
                if ($stock == 0) {
                    return response()->json([
                        'result' => false,
                        'message' => "No item is available for {$product->name}{$variant_string}, remove this from cart",
                    ]);
                }

                return response()->json([
                    'result' => false,
                    'message' => "Only {$stock} item(s) are available for {$product->name}{$variant_string}",
                ]);
            }
            $update++;
        }

        return response()->json([
            'result' => $update > 0,
            'message' => $update > 0 ? 'Cart updated' : 'Cart is empty',
        ]);
    }

    public function destroy(int $id)
    {
        $cart = Cart::withoutGlobalScopes()->find($id);
        if (! $cart) {
            return response()->json(['result' => false, 'message' => 'Product not found in cart'], 404);
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
        // return intval($request->user_id);
        return response()->json(get_total_cart_amount_check(intval($request->user_id)));

    }

    public function store_delivery_info(Request $request)
    {
        $carts = Cart::withoutGlobalScopes()
            ->where($request->user_field, $request->user_id)
            ->get();

        if ($carts->isEmpty()) {
            return response()->json(['result' => false, 'message' => 'Cart is empty']);
        }

        $addressId = $carts->first()->address_id ?? null;
        if (! $addressId) {
            return response()->json(['result' => false, 'message' => 'Address not found'], 404);
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

            if ($request['shipping_type_'.$product->user_id] == 'pickup_point') {
                $cartItem['shipping_type'] = 'pickup_point';
                $cartItem['pickup_point'] = $request['pickup_point_id_'.$product->user_id];
            } else {
                $cartItem['shipping_type'] = 'home_delivery';
            }

            if ($cartItem['shipping_type'] == 'home_delivery') {
                if ($request['shipping_method_'.$product->user_id] !== null) {
                    $cartItem['shipping_method'] = $request['shipping_method_'.$product->user_id];
                }
            }

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

        // $total = $subtotal + $tax + $shipping;
        return response()->json(['result' => true, 'message' => 'Success'], 200);
    }
}
