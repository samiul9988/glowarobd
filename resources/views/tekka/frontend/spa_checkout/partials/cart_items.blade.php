{{-- Shipping Offer Message --}}
@if ($carts->isNotEmpty() && isset($shippingMessage) && $shippingMessage)
<div id="shipping-offer-message" class="text-center text-success mb-2 fs-15 text-capitalize">
    {{ $shippingMessage ?? '' }}
</div>
@endif
<!-- Cart Items -->
<div class="cart-items-list" id="cart-items-list">
    @each(config('app.theme') . 'frontend.spa_checkout.partials.cart_item', $carts, 'cart', config('app.theme') . 'frontend.spa_checkout.partials.cart_item_empty')
</div>
