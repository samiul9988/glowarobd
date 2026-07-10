<?php
namespace App\Http\Controllers;

use App\Http\Resources\V3\GiftOfferCollection;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponCustomerAssignment;
use App\Models\CouponUsage;
use App\Models\Customeringroup;
use App\Models\GiftOffer;
use App\Models\GiftOfferItem;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NewCheckoutController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        $allCarts = Cart::withoutGlobalScopes()
            ->with('product.stocks', 'product.productprices', 'product.brand', 'product.category')
            ->whereNotNull($request->user_field)
            ->where($request->user_field, $userId)
            ->get();

        $carts = $allCarts->where('cart_type', 'regular');
        if ($carts->isEmpty()) {
            flash('Your cart is empty')->error();
            return redirect()->route('cart');
        }

        $minordercheck = get_total_cart_amount_check($userId, $carts, !Auth::check() && get_setting('guest_order_activation') == 1);
        if ($minordercheck['error'] == 1) {
            flash($minordercheck['error_message'])->error();
            return redirect()->route('cart');
        }

        foreach ($carts as $item) {
            if (check_discount_product_from_cart($item->product_id) == true) {
                $item->discount = 0;
                $item->coupon_code = null;
                $item->save();
            }
        }

        // Default cart shipping charge
        $cartShippingCharge = (float) ($carts->sum('shipping_cost') ?? 0);

        $addresses = Address::with('area:id,name', 'city:id,name', 'state:id,name')->where($request->user_field, $userId)->orderBy('set_default', 'desc')->get();

        // Default shipping zone
        $shipping_zone = ShippingZone::where('rest_of_the_world', 1)->first();
        $selectedAddress = null;
        if ($addresses->isNotEmpty()) {
            $selectedAddress = $addresses->where('set_default', 1)->first() ?? $addresses->first();
            $areaId = $selectedAddress->area_id;
            $shipping_zone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$areaId])->first() ?? $shipping_zone;
        }

        $sDiscount = check_shipping_discount_carts($carts, $shipping_zone->id ?? null);
        $discountShippingCharge = getDiscountShippingCharge($carts, $shipping_zone->id ?? null);

        $rates = json_decode($shipping_zone->rates, true);

        // Get shipping methods details
        $shippingMethodIds = collect($rates)->pluck('id')->unique()->toArray();
        $shippingMethods = ShippingMethod::whereIn('id', $shippingMethodIds)->get()->map(function ($method) use ($rates, $discountShippingCharge) {
            $rate = collect($rates)->firstWhere('id', $method->id);
            $price = (float) ($rate['price'] ?? 0);
            return [
                'id' => $method->id,
                'name' => $method->name,
                'logo' => uploaded_asset($method->logo),
                'price' => min($price, $discountShippingCharge),
            ];
        })->toArray();

        // Sort shipping methods by price
        usort($shippingMethods, function ($a, $b) {
            return $a['price'] <=> $b['price'];
        });

        $shippingMessage = '';
        if (!empty($sDiscount) && data_get($sDiscount, 'status', false) == true) {
            $minAmount = data_get($sDiscount, 'min_amount', 0);
            if ($minAmount <= $carts->sum(fn($cart) => $cart->price * $cart->quantity)) {
                $shippingMessage = ucwords('You are enjoying free shipping on this order!');
            } else {
                $remaining = single_price($minAmount - $carts->sum(fn($cart) => $cart->price * $cart->quantity));
                $shippingMessage = ucwords('Add more ' . $remaining . ' to get free shipping on this order!');
            }
        }

        $cartShippingCharge = min($shippingMethods[0]['price'] ?? 0, $discountShippingCharge);

        $carts = $allCarts; // Pass all carts (including gift offer items) to the view
        return view('frontend.spa_checkout.new_checkout', compact('carts', 'cartShippingCharge', 'addresses', 'shippingMethods', 'selectedAddress', 'shippingMessage'));
    }

    public function validateCarts(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        $carts = Cart::withoutGlobalScopes()
            ->with('product.stocks', 'product.productprices', 'product.brand', 'product.category')
            ->whereNotNull($request->user_field)
            ->where($request->user_field, $userId)
            ->get();

        $isValid = true;
        $giftCarts = $carts->where('cart_type', '!=', 'regular');
        $regularCarts = $carts->where('cart_type', 'regular');
        if ($giftCarts->isNotEmpty()) {
            $giftOfferItemIds = $giftCarts->pluck('gift_offer_item_id')->unique();
            $giftOfferItems = GiftOfferItem::with('giftOffer.conditions')->whereIn('id', $giftOfferItemIds)->get()->keyBy('id');
            foreach ($giftOfferItems as $giftOfferItem) {
                $giftOffer = $giftOfferItem->giftOffer;
                if ($giftOffer && $giftOffer->offer_type === 'cart') {
                    $cartTotal = $regularCarts->sum(function ($cart) {
                        return ($cart->price * $cart->quantity);
                    });
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
            $carts->whereIn('id', $giftCarts->pluck('id'))->each(function ($cart) {
                $cart->delete();
            });
        }
    }

    private function getCarts(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        return Cart::withoutGlobalScopes()
            ->with('product.stocks', 'product.productprices', 'product.brand', 'product.category')
            ->whereNotNull($request->user_field)
            ->where($request->user_field, $userId)
            ->get();
    }

    private function getShippingMessage(Request $request, $carts)
    {
        // $carts = $carts->where('cart_type', 'regular');

        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        // Default shipping zone
        $shipping_zone = ShippingZone::where('rest_of_the_world', 1)->first();
        if ($request->address_id) {
            $address = Address::find($request->address_id);
            if ($address) {
                $areaId = $address->area_id;
                $shipping_zone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$areaId])->first() ?? $shipping_zone;
            }
        } elseif ($request->area_id) {
            $areaId = $request->area_id;
            $shipping_zone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$areaId])->first() ?? $shipping_zone;
        } else {
            $addresses = Address::with('area:id,name', 'city:id,name', 'state:id,name')->where($request->user_field, $userId)->orderBy('set_default', 'desc')->get();

            if ($addresses->isNotEmpty()) {
                $selectedAddress = $addresses->where('set_default', 1)->first() ?? $addresses->first();
                $areaId = $selectedAddress->area_id;
                $shipping_zone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$areaId])->first() ?? $shipping_zone;
            }
        }

        $sDiscount = check_shipping_discount_carts($carts, $shipping_zone->id ?? null);
        $shippingMessage = '';
        if (!empty($sDiscount) && data_get($sDiscount, 'status', false) == true) {
            $minAmount = data_get($sDiscount, 'min_amount', 0);
            if ($minAmount <= $carts->sum(fn($cart) => $cart->price * $cart->quantity)) {
                $shippingMessage = 'You are enjoying free shipping on this order!';
            } else {
                $remaining = single_price($minAmount - $carts->sum(fn($cart) => $cart->price * $cart->quantity));
                $shippingMessage = 'Add more ' . $remaining . ' to get free shipping on this order!';
            }
        }
        return $shippingMessage;
    }

    public function getCartsView(Request $request)
    {
        $this->validateCarts($request);

        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        $carts = $this->getCarts($request);

        $shippingMessage = $this->getShippingMessage($request, $carts);
        return view('frontend.spa_checkout.partials.cart_items', compact('carts', 'shippingMessage'))->render();
    }

    public function getCartSummaryView(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        $allCarts = $this->getCarts($request);
        $coupon = $allCarts->firstWhere('coupon_applied', 1)->coupon_code ?? null;

        if ($coupon) {
            $allCarts = $this->validateCoupon($allCarts, $coupon);
        }

        $regularCarts = $allCarts->where('cart_type', 'regular');
        $otherCarts = $allCarts->where('cart_type', '!=', 'regular');

        $subtotal = $regularCarts->sum(fn($cart) => $cart->price * $cart->quantity);
        $giftOfferTotal = $otherCarts->sum(fn($cart) => $cart->price * $cart->quantity);
        $cartShippingCharge = (float) ($regularCarts->first()?->shipping_cost ?? 0);
        $tax = $regularCarts->sum(fn($cart) => $cart->tax * $cart->quantity);
        $discount = $regularCarts->sum('discount'); // Calculate discount if needed
        $coupon = $regularCarts->firstWhere('coupon_applied', 1)->coupon_code ?? null;
        $total = $subtotal + $giftOfferTotal + $cartShippingCharge + $tax - $discount;

        $totalSavings = 0;
        $allCarts->each(function ($cart) use (&$totalSavings) {
            $productPrice = $cart->product?->stocks?->first()?->price ?? $cart->product?->web_price ?? 0;
            $savings = max(0, ($productPrice - $cart->price) * $cart->quantity);
            $totalSavings += $savings;
        });

        return view('frontend.spa_checkout.partials.cart_summary', compact('subtotal', 'giftOfferTotal', 'cartShippingCharge', 'tax', 'discount', 'coupon', 'total', 'totalSavings'))->render();
    }

    public function validateCoupon(Collection $carts, string $couponCode): Collection
    {
        $coupon = Coupon::where('status', 1)->where('code', $couponCode)->first();
        $cartTotal = $carts->sum(fn($cart) => $cart->price * $cart->quantity);

        if (!$coupon) {
            return $this->resetCoupon($carts);
        }
        $details = json_decode($coupon->details, true);
        $isInvalid = now()->timestamp < $coupon->start_date ||
            now()->timestamp > $coupon->end_date ||
            ($details['min_buy'] ?? 0) > $cartTotal;

        if ($isInvalid) {
            return $this->resetCoupon($carts);
        }

        $discountAmount = 0;
        if ($coupon->type === 'cart_base') {
            $discountAmount = match ($coupon->discount_type) {
                'percent' => ($cartTotal * $coupon->discount) / 100,
                'amount' => $coupon->discount,
                default => 0
            };
        } elseif ($coupon->type === 'product_base') {
            $eligibleProducts = collect($details)->pluck('product_id')->toArray();
            $eligibleItems = $carts->whereIn('product_id', $eligibleProducts);
            foreach ($eligibleItems as $item) {
                $itemTotal = $item->price * $item->quantity;

                $discountAmount += match ($coupon->discount_type) {
                    'percent' => ($itemTotal * $coupon->discount) / 100,
                    'amount'  => $coupon->discount,
                    default   => 0,
                };
            }
        } elseif ($coupon->type == 'shipping_charge') {
            $shippingCost = $carts->sum('shipping_cost');
            $discountAmount = match ($coupon->discount_type) {
                'percent' => ($shippingCost * $coupon->discount) / 100,
                'amount' => $coupon->discount,
                default => 0
            };
        }
        $discountAmount = min($discountAmount, $details['max_discount'] ?? $cartTotal);

        $targetCart = $carts->firstWhere('cart_type', 'regular');
        if ($targetCart) {
            $targetCart->discount = $discountAmount;
            $targetCart->coupon_code = $coupon->code;
            $targetCart->coupon_applied = 1;
            $targetCart->save();
        }

        return $carts;
    }

    private function resetCoupon(Collection $carts): Collection
    {
        Cart::whereIn('id', $carts->pluck('id'))
            ->update([
                'discount' => 0,
                'coupon_applied' => 0,
                'coupon_code' => null,
            ]);
        $carts->each(function ($cart) {
            $cart->discount = 0;
            $cart->coupon_applied = 0;
            $cart->coupon_code = null;
        });
        return $carts;
    }

    public function getShippingMethods(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        $carts = $this->getCarts($request);

        // Default shipping zone
        $shipping_zone = ShippingZone::where('rest_of_the_world', 1)->first();
        if ($request->address_id) {
            $address = Address::find($request->address_id);
            if ($address) {
                $areaId = $address->area_id;
                $shipping_zone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$areaId])->first() ?? $shipping_zone;
            }
        } elseif ($request->area_id) {
            $areaId = $request->area_id;
            $shipping_zone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$areaId])->first() ?? $shipping_zone;
        } else {
            $addresses = Address::with('area:id,name', 'city:id,name', 'state:id,name')->where($request->user_field, $userId)->orderBy('set_default', 'desc')->get();

            if ($addresses->isNotEmpty()) {
                $selectedAddress = $addresses->where('set_default', 1)->first() ?? $addresses->first();
                $areaId = $selectedAddress->area_id;
                $shipping_zone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$areaId])->first() ?? $shipping_zone;
            }
        }

        $sDiscount = check_shipping_discount_carts($carts, $shipping_zone->id ?? null);
        $discountShippingCharge = getDiscountShippingCharge($carts, $shipping_zone->id ?? null);

        $rates = json_decode($shipping_zone->rates, true);

        // Get shipping methods details
        $shippingMethodIds = collect($rates)->pluck('id')->unique()->toArray();
        $shippingMethods = ShippingMethod::whereIn('id', $shippingMethodIds)->get()->map(function ($method) use ($rates, $discountShippingCharge) {
            $rate = collect($rates)->firstWhere('id', $method->id);
            $price = (float) ($rate['price'] ?? 0);
            return [
                'id' => $method->id,
                'name' => $method->name,
                'logo' => uploaded_asset($method->logo),
                'price' => min($price, $discountShippingCharge),
            ];
        })->toArray();

        // Sort shipping methods by price
        usort($shippingMethods, function ($a, $b) {
            return $a['price'] <=> $b['price'];
        });

        if ($carts->isNotEmpty() && !empty($shippingMethods)) {
            $firstCart = $carts->first();

            $shippingMethodId = data_get($shippingMethods, '0.id');
            $shippingPrice = (float) data_get($shippingMethods, '0.price', 0);
            $shippingCharge = (float) $discountShippingCharge;

            $minShippingCost = min($shippingPrice, $shippingCharge);

            $updateData = [];

            // Update shipping method only if changed
            if ($firstCart->shipping_method != $shippingMethodId) {
                $updateData['shipping_method'] = $shippingMethodId;
            }

            // Update shipping cost only if changed
            if ((float) $firstCart->shipping_cost !== $minShippingCost) {
                $updateData['shipping_cost'] = $minShippingCost;
            }

            if (!empty($updateData)) {
                $firstCart->update($updateData);
            }
        }

        return view('frontend.spa_checkout.partials.shipping_methods', compact('shippingMethods'))->render();
    }

    public function getPaymentMethods(Request $request)
    {
        // You can implement logic to fetch available payment methods based on the request data
        // For now, let's return a static view
        return view('frontend.spa_checkout.partials.payment_methods')->render();
    }

    public function getAvailableCoupons()
    {
        $groupId = null;
        $existingCouponIds = [];
        $availableCoupons = collect([]);
        $isGuestUser = !Auth::check();

        if (!$isGuestUser) {
            $assignedCoupons = CouponCustomerAssignment::with('coupon')
                ->where('customer_id', Auth::id())
                ->whereHas('coupon', function ($query) {
                    $query->valid();
                })
                ->get();

            $availableCoupons = $assignedCoupons->filter(function ($assignment) {
                if ('single' === strtolower($assignment->coupon->usage_limit)) {
                    return 0 === $assignment->is_used;
                }
                return true;
            });

            // Existing coupon IDs
            $existingCouponIds = $availableCoupons
                ->pluck('coupon.id')
                ->filter()
                ->unique()
                ->toArray();

            $groupId = Customeringroup::where('user_id', Auth::id())
                ->latest()
                ->value('customer_groups_id');
        }

        $groupCoupons = Coupon::valid()
            ->featured()
            ->forGroup()
            ->when($isGuestUser, function ($query) {
                $query->featured();
            })
            ->when($groupId, function ($query) use ($groupId) {
                $query->whereJsonContains('group_ids', $groupId);
            })
            ->when(!empty($existingCouponIds), function ($query) use ($existingCouponIds) {
                $query->whereNotIn('id', $existingCouponIds);
            })
            ->get()
            ->each(function ($coupon) use (&$availableCoupons) {
                $availableCoupons->push((object) [
                    'coupon'      => $coupon,
                    'expire_date' => $coupon->end_date,
                ]);
            });

        $existingCouponIds = array_merge(
            $existingCouponIds,
            $groupCoupons->pluck('id')->toArray()
        );

        // Public coupons
        $publicCoupons = Coupon::valid()
            ->featured()
            ->forAll()
            ->when(!empty($existingCouponIds), function ($query) use ($existingCouponIds) {
                $query->whereNotIn('id', $existingCouponIds);
            })
            ->get()
            ->each(function ($coupon) use (&$availableCoupons) {
                $availableCoupons->push((object) [
                    'coupon'      => $coupon,
                    'expire_date' => $coupon->end_date,
                ]);
            });

        $cart = Cart::withoutGlobalScopes()->where('user_id', Auth::id())->where('coupon_applied', 1)->first();
        return response()->json([
            'success' => true,
            'coupons' => $availableCoupons,
            'coupons_view' => $availableCoupons->isEmpty() ? '' : view('frontend.spa_checkout.partials.coupons', [
                'coupons' => $availableCoupons,
                'appliedCouponCode' => $cart?->coupon_code ?? null,
            ])->render(),
        ]);
    }

    public function getGiftOffersView(Request $request)
    {
        $carts = $this->getCarts($request);
        $regularCarts = $carts->where('cart_type', 'regular');

        if ($regularCarts->isEmpty()) {
            return response()->json([
                'success' => true,
                'offers_view' => ''
            ]);
        }

        $regularCartTotal = $regularCarts->sum(fn($cart) => $cart->price * $cart->quantity);
        $productIds = $regularCarts->pluck('product_id')->unique()->toArray();
        $eligableOffers = GiftOffer::with([
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
            ->whereNotIn('id', $eligableOffers->pluck('id')->toArray())
            ->whereHas('items', fn($query) => $query->where('available_qty', '>', 0)) // Must have atleast one valid item
            ->orderBy('min_cart_amount', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $offersView = '';
        if ($eligableOffers->isNotEmpty() || $otherOffers->isNotEmpty()) {
            $offersView = view('frontend.spa_checkout.partials.gift_offers', [
                'offers' => (new GiftOfferCollection($eligableOffers))->toArray($request)['data'],
                'invalidOffers' => (new GiftOfferCollection($otherOffers))->toArray($request)['data'],
                'carts' => $carts,
            ])->render();
        }

        return response()->json([
            'success' => true,
            'offers_view' => $offersView
        ]);
    }

    public function getCheckoutSummary(Request $request)
    {
        return response()->json([
            'success' => true,
            'carts_view' => $this->getCartsView($request),
            'cart_summary_view' => $this->getCartSummaryView($request),
            'shipping_methods_view' => $this->getShippingMethods($request),
            'payment_methods_view' => $this->getPaymentMethods($request),
        ]);
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

        $carts = $this->getCarts($request); // Get all carts with product details

        $regularCarts = $carts->where('cart_type', 'regular');
        $regularCartTotal = $regularCarts->sum(function ($cart) {
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

        $cart = Cart::withoutGlobalScopes()->updateOrCreate([
            $request->user_field => Auth::check() ? Auth::id() : $request->temp_user_id,
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
                'original_price' => $cart->product->unit_price,
                'original_price_formatted' => single_price($cart->product->unit_price),
                'save_amount' => single_price(max(0, ($cart->product->unit_price - $cart->price) * $cart->quantity))
            ]
        ], 200);
    }

    protected function getAvailableQuantity($product, $productStock): int
    {
        $flashDealCheck = check_flash_deal_product($product);

        if ($flashDealCheck) {
            $quantity = $product->flash_deal_product->quantity;
            if ($product->max_qty > 0) {
                $quantity = min($product->max_qty, $quantity);
            }
            return $quantity;
        }

        // Pre-order check
        $preorderCheck = check_preorder_product($product);
        if ($preorderCheck) {
            return $product->preorder_max_qty - preorder_product_count($product);
        }

        $quantity = $productStock->qty ?? 0;
        if ($product->max_qty > 0) {
            $quantity = min($product->max_qty, $quantity);
        }

        return $quantity;
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|integer',
            'is_plus' => 'required|boolean',
        ]);
        $cartId = $request->input('cart_id');
        $isPlus = $request->input('is_plus');
        $cart = Cart::withoutGlobalScopes()->with('product.stocks', 'product.productprices')->find($cartId);

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        }

        if ($cart->cart_type !== 'regular') {
            return $this->updateQuantityForGiftOfferItem($cart, $isPlus);
        }

        $product = $cart->product;
        $productStock = $product->stocks->where('variant', $cart->variation)->first();

        if (!$productStock) {
            $productStock = $product->stocks->first();
        }

        // Get available quantity
        $availableQty = $this->getAvailableQuantity($product, $productStock);

        // Calculate new quantity
        $newQuantity = $isPlus ? $cart->quantity + 1 : $cart->quantity - 1;

        // Validate minimum quantity
        if ($newQuantity < $product->min_qty) {
            return response()->json([
                'success' => false,
                'message' => "Minimum quantity is {$product->min_qty}",
                'min_qty' => $product->min_qty,
            ], 400);
        }

        // Validate stock availability
        if ($newQuantity > $availableQty && $product->allow_stock_out_purchases == 0) {
            return response()->json([
                'success' => false,
                'message' => "Only {$availableQty} items available in stock",
                'available_qty' => $availableQty,
            ], 400);
        }

        // Calculate new price based on quantity
        $userData = Auth::check() ? Auth::user()->load('customeringroup.group') : null;
        $newPrice = getMinimumPriceByVariant($product, $productStock, 'web', $newQuantity, $userData);

        $cart->quantity = $newQuantity;
        $cart->price = $newPrice;
        $cart->save();

        return response()->json([
            'success' => true,
            'message' => 'Quantity updated successfully',
        ]);
    }

    public function updateQuantityForGiftOfferItem(Cart $cart, bool $isPlus)
    {
        $cart->loadMissing('giftOffer');
        $cart->loadMissing('giftOfferItem');
        $giftOffer = $cart->giftOffer;
        $giftOfferItem = $cart->giftOfferItem;

        if (!$giftOfferItem || !$giftOffer || !$giftOffer->isValid()) {
            $cart->delete(); // Remove invalid cart item
            return response()->json([
                'success' => false,
                'message' => 'Gift offer item is not valid or has expired',
            ], 400);
        }

        $product = $cart->product;
        $productStock = $product->stocks->where('variant', $cart->variation)->first();

        if (!$productStock) {
            $productStock = $product->stocks->first();
        }

        $availableQty = $productStock->qty ?? 0;
        if ($availableQty <= 0) {
            $giftOfferItem->available_qty = 0;
            $giftOfferItem->save();
            $cart->delete(); // Remove cart item if out of stock
            return response()->json([
                'success' => false,
                'message' => "This gift item is out of stock",
            ], 400);
        }

        // Calculate new quantity
        $newQuantity = $isPlus ? $cart->quantity + 1 : $cart->quantity - 1;
        if ($isPlus && $newQuantity > $availableQty) {
            return response()->json([
                'success' => false,
                'message' => "Only {$availableQty} items available for this gift offer",
                'available_qty' => $availableQty,
            ], 400);
        }

        if ($isPlus && $cart->quantity >= $giftOffer->max_qty_per_order) {
            return response()->json([
                'success' => false,
                'message' => "You have already added the maximum allowed gift items quantity to your cart.",
            ], 400);
        }

        // Validate quantity
        if ($newQuantity <= 0) {
            $cart->delete(); // Remove cart item if quantity is negative
            return response()->json([
                'success' => true,
                'message' => "Gift item removed from cart",
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
        $cart->quantity = $newQuantity;
        $cart->save();

        return response()->json([
            'success' => true,
            'message' => 'Quantity updated successfully',
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|integer',
        ]);
        $cartId = $request->input('cart_id');
        $cart = Cart::withoutGlobalScopes()->find($cartId);

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
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

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_id' => $cartId,
        ]);
    }

    public function onAreaChange(Request $request): JsonResponse
    {
        $request->validate([
            'area_id' => 'required|integer',
        ]);

        return response()->json([
            'success' => true,
            'shipping_methods_view' => $this->getShippingMethods($request)
        ]);
    }

    public function apply_coupon_code(Request $request)
    {
        $coupon = Coupon::where('status', 1)->where('code', $request->code)->first();
        if (!$coupon) {
            return response()->json([
                'type' => 'danger',
                'message' => ('Invalid coupon code')
            ], 404);
        }

        $response = is_coupon_valid($coupon, Auth::check() ? Auth::id() : null, 'web');

        if ($response['status'] === false) {
            return response()->json([
                'success' => false,
                'message' => $response['message']
            ], $response['code']);
        }

        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        $couponApplied = false;
        $carts_products = Cart::where($request->user_field, $userId)->get();

        // Group Discount Section
        if (Auth::check() && Auth::user()->customeringroup) {
            $discount_status = Auth::check() ? Auth::user()->customeringroup->group->discount_status : null;
            $start_date = Auth::check() ? Auth::user()->customeringroup->group->start_date : null;
            $end_date = Auth::check() ? Auth::user()->customeringroup->group->end_date : null;
            $cur_date = strtotime(date('Y-m-d H:i:s'));
            if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date && $coupon->force_apply == 0) {
                return response()->json([
                    'success' => false,
                    'message' => "You already have a group discount!",
                ], 400);
            }
        }
        // Group Discount Section

        foreach ($carts_products as $key => $item) {
            if (check_discount_product_from_cart($item->product_id) == true && $coupon->force_apply == 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Coupon Can't Be Applied On Discounted Products Or With Discounted Product!",
                ], 400);
            }
        }

        if ($coupon != null) {
            if (strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date) {
                $pass = true;
                if ($coupon->usage_limit == 'single') {
                    if (CouponUsage::where($request->user_field, $userId)->where('coupon_id', $coupon->id)->first() !== null)
                        $pass = false;
                } else {
                    // for multiple
                }
                if ($pass) {
                    $coupon_details = json_decode($coupon->details);
                    $carts = Cart::where($request->user_field, $userId)
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
                        } else {
                            $needToBuyMore = $coupon_details->min_buy - $sum;
                            return response()->json([
                                'success' => false,
                                'message' => 'Add more ' . single_price($needToBuyMore) . ' to apply this coupon!'
                            ], 400);
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
                    } elseif ($coupon->type == 'shipping_charge') {
                        $shippingCost = $carts->sum('shipping_cost');
                        $cartTotal = $carts->sum(function ($cartItem) {
                            return $cartItem['price'] * $cartItem['quantity'];
                        });
                        if ($cartTotal < $coupon_details->min_buy) {
                            $needToBuyMore = $coupon_details->min_buy - $cartTotal;
                            return response()->json([
                                'success' => false,
                                'message' => 'Add more ' . single_price($needToBuyMore) . ' to apply this coupon!'
                            ], 400);
                        }
                        if ($coupon->discount_type === 'percent') {
                            $coupon_discount = ($shippingCost * $coupon->discount) / 100;
                            $coupon_discount = min($coupon_discount, $coupon_details->max_discount);
                        } else {
                            $coupon_discount = $coupon->discount;
                        }
                    }

                    Cart::where($request->user_field, $userId)
                        ->where('owner_id', $coupon->user_id)
                        ->update([
                            'discount' => 0.00,
                            'coupon_code' => '',
                            'coupon_applied' => 0
                        ]);

                    $cart = Cart::where($request->user_field, $userId)
                        ->where('owner_id', $coupon->user_id)
                        ->first();
                    if ($cart) {
                        $cart->update([
                            'discount' => $coupon_discount,
                            'coupon_code' => $request->code,
                            'coupon_applied' => 1
                        ]);
                        $message = 'Coupon Successfully Applied';
                        $couponApplied = true;
                    } else {
                        $message = 'No eligible items in cart for this coupon!';
                    }
                } else {
                    $message = 'You already used this coupon!';
                }

            } else {
                $message = 'Coupon expired!';
            }
        } else {
            $message = 'Invalid coupon!';
        }

        return response()->json([
            'success' => $couponApplied ?? false,
            'message' => $message ?? 'Coupon application failed!',
        ], ($couponApplied ?? false) ? 200 : 400);
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
        return response()->json([
            'success' => true,
            'message' => "Coupon removed successfully!",
        ], 200);
    }
}
