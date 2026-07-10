<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Collection;

class CouponService
{
    /**
     * Apply coupon on carts
     */
    public function apply(Collection $carts, string $couponCode, bool $refresh = false): Collection
    {
        $coupon = Coupon::where('status', 1)->where('code', $couponCode)->first();

        if (!$coupon) {
            return $this->reset($carts);
        }

        $details = json_decode($coupon->details, true);

        if (!$this->isValid($coupon, $details, $carts)) {
            return $this->reset($carts);
        }

        $discountAmount = $this->calculateDiscount(
            $coupon,
            $details,
            $carts
        );

        $discountAmount = min(
            $discountAmount,
            $details['max_discount'] ?? $this->cartTotal($carts)
        );

        $this->persistCoupon(
            $carts,
            $coupon,
            $discountAmount
        );

        if ($refresh) {
            return $this->reloadCarts($carts);
        }

        return $carts;
    }

    /**
     * Validate coupon
     */
    protected function isValid(
        Coupon $coupon,
        array $details,
        Collection $carts
    ): bool {

        $cartTotal = $this->cartTotal($carts);

        return !(
            now()->timestamp < $coupon->start_date ||
            now()->timestamp > $coupon->end_date ||
            ($details['min_buy'] ?? 0) > $cartTotal
        );
    }

    /**
     * Calculate cart total
     */
    protected function cartTotal(Collection $carts): float
    {
        return $carts->sum(function ($cart) {
            return $cart->price * $cart->quantity;
        });
    }

    /**
     * Calculate discount
     */
    protected function calculateDiscount(
        Coupon $coupon,
        array $details,
        Collection $carts
    ): float {

        $cartTotal = $this->cartTotal($carts);

        if ($coupon->type === 'cart_base') {

            return match ($coupon->discount_type) {
                'percent' => ($cartTotal * $coupon->discount) / 100,
                'amount' => $coupon->discount,
                default => 0,
            };
        }

        if ($coupon->type === 'product_base') {

            $eligibleProducts = collect($details)
                ->pluck('product_id')
                ->toArray();

            $eligibleItems = $carts->whereIn(
                'product_id',
                $eligibleProducts
            );

            $discount = 0;

            foreach ($eligibleItems as $item) {

                $itemTotal = $item->price * $item->quantity;

                $discount += match ($coupon->discount_type) {
                    'percent' => ($itemTotal * $coupon->discount) / 100,
                    'amount' => $coupon->discount,
                    default => 0,
                };
            }

            return $discount;
        }

        if ($coupon->type == 'shipping_charge') {
            $shippingCost = $carts->sum('shipping_cost');
            $cartTotal = $carts->sum(function ($cartItem) {
                return $cartItem['price'] * $cartItem['quantity'];
            });
            if ($cartTotal < data_get($details, 'min_buy', 0)) {
                return 0;
            }
            if ($coupon->discount_type === 'percent') {
                $discount = ($shippingCost * $coupon->discount) / 100;
                $discount = min($discount, $details['max_discount'] ?? PHP_FLOAT_MAX);
            } else {
                $discount = $coupon->discount;
            }
            return $discount;
        }

        return 0;
    }

    /**
     * Save coupon information
     */
    protected function persistCoupon(
        Collection $carts,
        Coupon $coupon,
        float $discountAmount
    ): void {

        $targetCart = $carts->firstWhere(
            'cart_type',
            'regular'
        );

        if (!$targetCart) {
            return;
        }

        $targetCart->discount = $discountAmount;
        $targetCart->coupon_code = $coupon->code;
        $targetCart->coupon_applied = 1;

        $targetCart->save();
    }

    protected function reloadCarts(Collection $carts): Collection
    {
        return Cart::withoutGlobalScopes()
            ->with([
                'product.stocks',
                'product.productprices',
                'product.brand',
                'product.category'
            ])
            ->whereIn('id', $carts->pluck('id'))
            ->get();
    }

    /**
     * Reset coupon data
     */
    public function reset(Collection $carts): Collection
    {
        $cartIds = $carts->pluck('id');

        Cart::whereIn('id', $cartIds)->update([
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
}
