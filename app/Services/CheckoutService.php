<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Address;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Auth;

class CheckoutService
{
    protected $carts;
    protected $userId;
    protected $userField;

    public function __construct()
    {
        $this->carts = collect();
    }

    /**
     * Set the user context for cart operations
     */
    public function setUserContext($userId, $userField = 'user_id'): self
    {
        $this->userId = $userId;
        $this->userField = $userField;
        return $this;
    }

    /**
     * Get cart items for the user
     */
    public function getCarts($userId = null, $field = null)
    {
        $userId = $userId ?? $this->userId;
        $field = $field ?? $this->userField ?? 'user_id';

        return Cart::with('product.stocks', 'product.productprices', 'product.brand', 'product.category')
            ->whereNotNull($field)
            ->where($field, $userId)
            ->get();
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity(int $cartId, int $quantity, bool $isPlus = true): array
    {
        $cart = Cart::withoutGlobalScopes()->with('product.stocks', 'product.productprices')->find($cartId);

        if (!$cart) {
            return [
                'success' => false,
                'message' => 'Cart item not found',
            ];
        }

        if ($cart->cart_type !== 'regular') {
            return $this->updateQuantityForGiftOfferItem($cart, $quantity, $isPlus);
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
            return [
                'success' => false,
                'message' => "Minimum quantity is {$product->min_qty}",
                'min_qty' => $product->min_qty,
            ];
        }

        // Validate stock availability
        if ($newQuantity > $availableQty && $product->allow_stock_out_purchases == 0) {
            return [
                'success' => false,
                'message' => "Only {$availableQty} items available in stock",
                'available_qty' => $availableQty,
            ];
        }

        // Calculate new price based on quantity
        $userData = Auth::check() ? Auth::user()->load('customeringroup.group') : null;
        $newPrice = getMinimumPriceByVariant($product, $productStock, 'web', $newQuantity, $userData);

        $cart->quantity = $newQuantity;
        $cart->price = $newPrice;
        $cart->save();

        return [
            'success' => true,
            'message' => 'Quantity updated successfully',
            'cart_id' => $cartId,
            'quantity' => $newQuantity,
            'price' => $newPrice,
            'formatted_price' => single_price($newPrice),
        ];
    }

    public function updateQuantityForGiftOfferItem(Cart $cart, int $quantity, bool $isPlus): array
    {
        $cart->loadMissing('giftOffer');
        $cart->loadMissing('giftOfferItem');
        $giftOffer = $cart->giftOffer;
        $giftOfferItem = $cart->giftOfferItem;

        if (!$giftOfferItem || !$giftOffer || !$giftOffer->isValid()) {
            $cart->delete(); // Remove invalid cart item
            return [
                'success' => false,
                'message' => 'Gift offer item is not valid or has expired',
            ];
        }

        if ($cart->quantity >= $giftOffer->max_qty_per_order && $isPlus) {
            return [
                'success' => false,
                'message' => "You have already added the maximum allowed gift items quantity to your cart.",
            ];
        }

        // Calculate new quantity
        $newQuantity = $isPlus ? $cart->quantity + 1 : $cart->quantity - 1;

        // Validate quantity
        if ($newQuantity < 0) {
            return [
                'success' => false,
                'message' => "Quantity cannot be negative",
            ];
        }

        if ($newQuantity > $giftOfferItem->available_qty) {
            return [
                'success' => false,
                'message' => "Only {$giftOfferItem->available_qty} items available for this gift offer",
                'available_qty' => $giftOfferItem->available_qty,
            ];
        }

        // Update cart and gift offer item usage
        $cart->quantity = $newQuantity;
        $cart->save();

        return [
            'success' => true,
            'message' => 'Quantity updated successfully',
            'cart_id' => $cart->id,
            'quantity' => $newQuantity,
            'price' => $giftOfferItem->offer_price,
            'formatted_price' => single_price($giftOfferItem->offer_price),
        ];
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(int $cartId): array
    {
        $cart = Cart::withoutGlobalScopes()->find($cartId);

        if (!$cart) {
            return [
                'success' => false,
                'message' => 'Cart item not found',
            ];
        }

        $cart->delete();

        return [
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_id' => $cartId,
        ];
    }

    /**
     * Get available quantity for a product
     */
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
     * Get shipping zone by area ID
     */
    public function getShippingZone($areaId = null): ?ShippingZone
    {
        if ($areaId) {
            $zone = ShippingZone::whereRaw('FIND_IN_SET(?, area_ids)', [$areaId])->first();
            if ($zone) {
                return $zone;
            }
        }

        return ShippingZone::where('rest_of_the_world', 1)->first();
    }

    /**
     * Get shipping methods for a zone
     */
    public function getShippingMethods($areaId = null, $carts = null): array
    {
        $shippingZone = $this->getShippingZone($areaId);

        if (!$shippingZone) {
            return [];
        }

        $carts = $carts ?? $this->getCarts();
        $discountShippingCharge = $this->getDiscountShippingCharge($carts, $shippingZone->id);

        $rates = json_decode($shippingZone->rates, true) ?? [];
        $shippingMethodIds = collect($rates)->pluck('id')->unique()->toArray();

        $methods = ShippingMethod::whereIn('id', $shippingMethodIds)
            ->get()
            ->map(function ($method) use ($rates, $discountShippingCharge) {
                $rate = collect($rates)->firstWhere('id', $method->id);
                $price = (float)($rate['price'] ?? 0);
                $finalPrice = min($price, $discountShippingCharge);

                return [
                    'id' => $method->id,
                    'name' => $method->name,
                    'logo' => uploaded_asset($method->logo),
                    'price' => $finalPrice,
                    'formatted_price' => single_price($finalPrice),
                ];
            })
            ->toArray();

        // Sort by price
        usort($methods, fn($a, $b) => $a['price'] <=> $b['price']);

        return $methods;
    }

    /**
     * Get shipping discount message
     */
    public function getShippingMessage($carts = null, $shippingZoneId = null): string
    {
        $carts = $carts ?? $this->getCarts();
        $sDiscount = check_shipping_discount_carts($carts, $shippingZoneId);

        if (empty($sDiscount) || !data_get($sDiscount, 'status', false)) {
            return '';
        }

        $minAmount = data_get($sDiscount, 'min_amount', 0);
        $cartTotal = $carts->sum(function ($cart) {
            return ($cart->price * $cart->quantity);
        });

        if ($minAmount <= $cartTotal) {
            return ucwords('You are enjoying free shipping on this order!');
        }

        $remaining = single_price($minAmount - $cartTotal);
        return ucwords("Add more {$remaining} to get free shipping on this order!");
    }

    /**
     * Get discount shipping charge
     */
    public function getDiscountShippingCharge($carts, $shippingZoneId = null): float
    {
        return (float)getDiscountShippingCharge($carts, $shippingZoneId);
    }

    /**
     * Calculate cart subtotal
     */
    public function calculateSubtotal($carts = null): float
    {
        $carts = $carts ?? $this->getCarts();
        return (float)$carts->sum(fn($cart) => $cart->price * $cart->quantity);
    }

    /**
     * Calculate cart tax
     */
    public function calculateTax($carts = null): float
    {
        $carts = $carts ?? $this->getCarts();
        return (float)$carts->sum(fn($cart) => $cart->tax * $cart->quantity);
    }

    /**
     * Calculate shipping charge
     */
    public function calculateShipping($carts = null, $areaId = null): float
    {
        $carts = $carts ?? $this->getCarts();
        $shippingZone = $this->getShippingZone($areaId);

        $shippingMethods = $this->getShippingMethods($areaId, $carts);

        // Get the cheapest shipping method
        if (!empty($shippingMethods)) {
            return (float)$shippingMethods[0]['price'];
        }

        return (float)$carts->sum('shipping_cost');
    }

    /**
     * Get full cart summary
     */
    public function getCartSummary($carts = null, $areaId = null, $shippingMethodId = null): array
    {
        $carts = $carts ?? $this->getCarts();

        $subtotal = $this->calculateSubtotal($carts);
        $tax = $this->calculateTax($carts);

        // Calculate shipping
        $shippingMethods = $this->getShippingMethods($areaId, $carts);
        $shipping = 0;

        if ($shippingMethodId && !empty($shippingMethods)) {
            $selectedMethod = collect($shippingMethods)->firstWhere('id', $shippingMethodId);
            $shipping = $selectedMethod ? (float)$selectedMethod['price'] : 0;
        } elseif (!empty($shippingMethods)) {
            $shipping = (float)$shippingMethods[0]['price'];
        }

        // Calculate discount
        $discount = (float)$carts->first()?->discount ?? 0;

        // Calculate total
        $total = $subtotal + $tax + $shipping - $discount;

        return [
            'subtotal' => $subtotal,
            'subtotal_formatted' => single_price($subtotal),
            'tax' => $tax,
            'tax_formatted' => single_price($tax),
            'shipping' => $shipping,
            'shipping_formatted' => single_price($shipping),
            'discount' => $discount,
            'discount_formatted' => single_price($discount),
            'total' => $total,
            'total_formatted' => single_price($total),
            'item_count' => $carts->count(),
            'quantity_count' => $carts->sum('quantity'),
        ];
    }

    /**
     * Get cart items formatted for display
     */
    public function getCartItems($carts = null): array
    {
        $carts = $carts ?? $this->getCarts();

        return $carts->map(function ($cart) {
            return [
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
                'save_amount' => single_price(max(0, ($cart->product->web_price - $cart->price) * $cart->quantity)),
                'line_total' => $cart->price * $cart->quantity,
                'line_total_formatted' => single_price($cart->price * $cart->quantity),
            ];
        })->toArray();
    }

    /**
     * Get full checkout data for AJAX refresh
     */
    public function getCheckoutData($areaId = null, $shippingMethodId = null): array
    {
        $carts = $this->getCarts();

        if ($carts->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Cart is empty',
                'redirect' => route('cart'),
            ];
        }

        $shippingZone = $this->getShippingZone($areaId);
        $shippingMethods = $this->getShippingMethods($areaId, $carts);
        $summary = $this->getCartSummary($carts, $areaId, $shippingMethodId);
        $shippingMessage = $this->getShippingMessage($carts, $shippingZone->id ?? null);

        return [
            'success' => true,
            'cart_items' => array_merge($this->getCartItems($carts), $this->getCartItems(Cart::withoutGlobalScopes()->withoutRegular()->where($this->userField, $this->userId)->get())),
            'shipping_methods' => $shippingMethods,
            'shipping_message' => $shippingMessage,
            'summary' => $summary,
        ];
    }

    /**
     * Update cart address and get updated checkout data
     */
    public function updateCartAddress(int $addressId): array
    {
        $address = Address::find($addressId);

        if (!$address) {
            return [
                'success' => false,
                'message' => 'Address not found',
            ];
        }

        $carts = $this->getCarts();

        // Update address for all cart items
        foreach ($carts as $cart) {
            $cart->address_id = $addressId;
            $cart->save();
        }

        return $this->getCheckoutData($address->area_id);
    }
}
