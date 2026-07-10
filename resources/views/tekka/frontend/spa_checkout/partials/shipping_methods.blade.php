<div class="shipping-grid" id="shipping-methods-container">
    @foreach ($shippingMethods as $index => $shippingMethod)
        <div class="shipping-option {{ $index === 0 ? 'selected' : '' }}">
            <input type="radio" name="shipping_method" value="{{ $shippingMethod['id'] ?? $index }}"
                {{ $index === 0 ? 'checked' : '' }} required>
            <div class="shipping-icon">
                <img src="{{ $shippingMethod['logo'] }}" alt="{{ $shippingMethod['name'] }}"
                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
            </div>
            <div class="shipping-name">{{ $shippingMethod['name'] }}</div>
            <div class="shipping-price {{ $shippingMethod['price'] == 0 ? 'free' : '' }}">
                {{ $shippingMethod['price'] == 0 ? 'FREE' : single_price($shippingMethod['price']) }}
            </div>
            <div class="shipping-check">
                <i class="las la-check"></i>
            </div>
        </div>
    @endforeach
</div>
