<?php

namespace App\Http\Controllers\Api\V3;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function applyOld(Request $request)
    {
        $coupon = Coupon::where('status', 1)->where('code', $request->code)->first();

        if ($coupon != null && strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date && CouponUsage::where($request->user_field, $request->user_id)->where('coupon_id', $coupon->id)->first() == null) {
            $couponDetails = json_decode($coupon->details);
            if ($coupon->type == 'cart_base') {
                $sum = Cart::where($request->user_field, $request->user_id)->sum('price');
                if ($sum > $couponDetails->min_buy) {
                    if ($coupon->discount_type == 'percent') {
                        $couponDiscount =  ($sum * $coupon->discount) / 100;
                        if ($couponDiscount > $couponDetails->max_discount) {
                            $couponDiscount = $couponDetails->max_discount;
                        }
                    } elseif ($coupon->discount_type == 'amount') {
                        $couponDiscount = $coupon->discount;
                    }
                    if ($this->isCouponAlreadyApplied($request->user_id, $coupon->id)) {
                        return response()->json([
                            'success' => false,
                            'message' => ('The coupon is already applied. Please try another coupon')
                        ]);
                    } else {
                        return response()->json([
                            'success' => true,
                            'discount' => (double) $couponDiscount
                        ]);
                    }
                }
            } elseif ($coupon->type == 'product_base') {
                $couponDiscount = 0;
                $cartItems = Cart::where($request->user_field, $request->user_id)->get();
                foreach ($cartItems as $key => $cartItem) {
                    foreach ($couponDetails as $key => $couponDetail) {
                        if ($couponDetail->product_id == $cartItem->product_id) {
                            if ($coupon->discount_type == 'percent') {
                                $couponDiscount += $cartItem->price * $coupon->discount / 100;
                            } elseif ($coupon->discount_type == 'amount') {
                                $couponDiscount += $coupon->discount;
                            }
                        }
                    }
                }
                if ($this->isCouponAlreadyApplied($request->user_id, $coupon->id)) {
                    return response()->json([
                        'success' => false,
                        'message' => ('The coupon is already applied. Please try another coupon')
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'discount' => (float) $couponDiscount,
                        'message' => ('Coupon code applied successfully')
                    ]);
                }
            } elseif ($coupon->type == 'shipping_charge') {
                $couponDiscount = 0;
                $cartItems = Cart::where($request->user_field, $request->user_id)->get();
                $shippingCost = $cartItems->sum('shipping_cost');
                $cartTotal = $cartItems->sum(function ($cartItem) {
                    return $cartItem['price'] * $cartItem['quantity'];
                });
                if ($cartTotal < $couponDetails->min_buy) {
                    $needToBuyMore = $couponDetails->min_buy - $cartTotal;
                    return response()->json([
                        'success' => false,
                        'message' => 'Add more ' . single_price($needToBuyMore) . ' to apply this coupon!'
                    ]);
                }
                if ($coupon->discount_type === 'percent') {
                    $couponDiscount = ($shippingCost * $coupon->discount) / 100;
                    $couponDiscount = min($couponDiscount, $couponDetails->max_discount);
                } else {
                    $couponDiscount = $coupon->discount;
                }

                if ($this->isCouponAlreadyApplied($request->user_id, $coupon->id)) {
                    return response()->json([
                        'success' => false,
                        'message' => ('The coupon is already applied. Please try another coupon')
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'discount' => (float) $couponDiscount,
                        'message' => ('Coupon code applied successfully')
                    ]);
                }
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => ('The coupon is invalid')
            ]);
        }
    }

    public function apply(Request $request)
    {
        $coupon = Coupon::valid()->where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'The coupon is invalid'
            ]);
        }

        $validation = is_coupon_valid($coupon, $request->user_id, $request->header('source', 'app'));
        if (!$validation['status']) {
            return response()->json([
                'success' => false,
                'message' => $validation['message']
            ]);
        }

        if (CouponUsage::where($request->user_field, $request->user_id)
            ->where('coupon_id', $coupon->id)
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon already used'
            ]);
        }

        if ($this->isCouponAlreadyApplied($request->user_id, $coupon->id)) {
            return response()->json([
                'success' => false,
                'message' => 'The coupon is already applied. Please try another coupon'
            ]);
        }

        $cartItems = Cart::where($request->user_field, $request->user_id)->get();
        $cartTotal = $cartItems->sum('price');

        $details = json_decode($coupon->details);
        $discount = 0;

        if ($coupon->type === 'cart_base') {
            if ($cartTotal <= $details->min_buy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum purchase requirement not met'
                ]);
            }

            if ($coupon->discount_type === 'percent') {
                $discount = ($cartTotal * $coupon->discount) / 100;
                if ($discount > $details->max_discount) {
                    $discount = $details->max_discount;
                }
            } else {
                $discount = $coupon->discount;
            }
        } elseif ($coupon->type === 'product_base') {
            $productIds = collect($details)->pluck('product_id')->toArray();
            $eligibleItems = $cartItems->whereIn('product_id', $productIds);

            foreach ($eligibleItems as $item) {
                if ($coupon->discount_type === 'percent') {
                    $discount += ($item->price * $coupon->discount) / 100;
                } else {
                    $discount += $coupon->discount;
                }
            }
        } elseif ($coupon->type === 'shipping_charge') {
            $shippingCost = $cartItems->sum('shipping_cost');
            $cartTotal = $cartItems->sum(function ($cartItem) {
                return $cartItem['price'] * $cartItem['quantity'];
            });
            if ($cartTotal < $details->min_buy) {
                $needToBuyMore = $details->min_buy - $cartTotal;
                return response()->json([
                    'success' => false,
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

        return response()->json([
            'success' => true,
            'discount' => (float) $discount,
            'message' => 'Coupon applied successfully'
        ]);
    }

    protected function isCouponAlreadyApplied($userId, $couponId) {
        $userField = 'user_id';
        if (Str::startsWith($userId, 'tmp_')) {
            $userField = 'temp_user_id';
        }
        return CouponUsage::where([$userField => $userId, 'coupon_id' => $couponId])->count() > 0;
    }
}
