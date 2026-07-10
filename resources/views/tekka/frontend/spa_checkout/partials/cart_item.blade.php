<div class="cart-item">
    <div class="cart-item-image">
        <img src="{{ uploaded_asset($cart->product->thumbnail_img) }}" alt="{{ $cart->product->name }}"
            onerror="this.error=null; this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
    </div>
    <div class="cart-item-details">
        <div class="cart-item-name">{{ $cart->product->name }}</div>
        @if ($cart->variation)
            <div class="cart-item-variant">{{ $cart->variation }}</div>
        @endif
        <div class="cart-item-price">
            @if ($cart->price == 0)
                FREE
            @else
                {{ single_price($cart->price) }}
            @endif
            @php
                $productPrice = $cart->product->stocks?->first()?->price ?? $cart->product->web_price;
                $savings = ($productPrice - $cart->price) * $cart->quantity;
            @endphp
            @if ($productPrice > $cart->price)
                <del class="text-danger fs-12">{{ single_price($productPrice) }}</del>
            @endif
        </div>

        @if ($savings > 0)
            <div class="badge badge-inline badge-soft-success cart-item-savings">
                Save {{ single_price($savings) }}
            </div>
        @endif
    </div>
    <div class="cart-item-qty">
        <button type="button" class="cart-item-remove" data-id="{{ $cart->id }}">
            <i class="las la-times"></i>
        </button>
        <div class="qty-controls">
            <button type="button" class="qty-btn qty-minus" data-id="{{ $cart->id }}">
                <i class="las la-minus"></i>
            </button>
            <span class="qty-value">{{ $cart->quantity }}</span>
            <button type="button" class="qty-btn qty-plus" data-id="{{ $cart->id }}">
                <i class="las la-plus"></i>
            </button>
        </div>
    </div>
</div>
