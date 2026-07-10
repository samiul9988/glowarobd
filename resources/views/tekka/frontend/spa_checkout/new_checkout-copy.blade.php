@extends(config('app.theme') . 'frontend.layouts.app')

@section('meta')
    <x-seo />
@endsection

<style>
    /* Modern Checkout Styles */
    .checkout-wrapper {
        background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
        min-height: 100vh;
        padding: 40px 0;
    }

    .checkout-header {
        background: white;
        border-radius: 16px;
        padding: 25px 30px;
        margin-bottom: 30px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .checkout-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .checkout-steps {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
    }

    .step-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #6c757d;
    }

    .step-item.active {
        color: #007bff;
        font-weight: 600;
    }

    .step-number {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 13px;
    }

    .step-item.active .step-number {
        background: #007bff;
        color: white;
    }

    .section-card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
    }

    .section-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
    }

    /* Address Cards */
    .address-card {
        position: relative;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .address-card:hover {
        border-color: #007bff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,123,255,0.1);
    }

    .address-card input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .address-card input[type="radio"]:checked + .address-content {
        border-color: #007bff;
    }

    .address-card.selected {
        border-color: #007bff;
        background: #f8f9ff;
    }

    .address-badge {
        display: inline-block;
        padding: 6px 16px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    .address-info {
        color: #495057;
        line-height: 1.8;
    }

    .address-name {
        font-weight: 600;
        color: #1a1a2e;
        font-size: 16px;
        margin-bottom: 8px;
    }

    .address-edit-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border: none;
    }

    .address-edit-btn:hover {
        background: #007bff;
        color: white;
    }

    /* New Address Form */
    .new-address-form {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 25px;
    }

    .form-group label {
        font-weight: 600;
        color: #1a1a2e;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.1);
    }

    /* Shipping Methods */
    .shipping-option {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .shipping-option:hover {
        border-color: #007bff;
        transform: translateX(4px);
    }

    .shipping-option.selected {
        border-color: #007bff;
        background: #f8f9ff;
    }

    .shipping-option input[type="radio"] {
        width: 20px;
        height: 20px;
        accent-color: #007bff;
    }

    .shipping-logo {
        width: 60px;
        height: 60px;
        object-fit: contain;
        border-radius: 8px;
        background: white;
        padding: 8px;
    }

    .shipping-info {
        flex: 1;
    }

    .shipping-name {
        font-weight: 600;
        color: #1a1a2e;
        font-size: 16px;
        margin-bottom: 4px;
    }

    .shipping-price {
        color: #28a745;
        font-weight: 700;
        font-size: 15px;
    }

    /* Payment Methods */
    .payment-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .payment-option {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .payment-option:hover {
        border-color: #007bff;
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0,123,255,0.15);
    }

    .payment-option.selected {
        border-color: #007bff;
        background: #f8f9ff;
    }

    .payment-option input[type="radio"] {
        display: none;
    }

    .payment-logo {
        height: 40px;
        object-fit: contain;
        margin-bottom: 12px;
    }

    .payment-name {
        font-size: 13px;
        font-weight: 600;
        color: #495057;
    }

    /* Order Summary */
    .order-summary {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        position: sticky;
        top: 100px;
    }

    .cart-item {
        display: flex;
        gap: 15px;
        padding: 15px;
        border: 2px solid #f0f0f0;
        border-radius: 12px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .cart-item:hover {
        border-color: #007bff;
        box-shadow: 0 4px 12px rgba(0,123,255,0.1);
    }

    .cart-item-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
    }

    .cart-item-details {
        flex: 1;
    }

    .cart-item-name {
        font-weight: 600;
        color: #1a1a2e;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .cart-item-price {
        color: #007bff;
        font-weight: 700;
        font-size: 16px;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
    }

    .qty-btn {
        width: 32px;
        height: 32px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        color: #495057;
    }

    .qty-btn:hover {
        border-color: #007bff;
        color: #007bff;
        background: #f8f9ff;
    }

    .qty-input {
        width: 50px;
        text-align: center;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 6px;
        font-weight: 600;
    }

    .order-total {
        border-top: 2px solid #f0f0f0;
        padding-top: 20px;
        margin-top: 20px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 15px;
    }

    .total-row.grand-total {
        font-size: 20px;
        font-weight: 700;
        color: #1a1a2e;
        padding-top: 15px;
        border-top: 2px solid #f0f0f0;
        margin-top: 15px;
    }

    .checkout-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 16px;
        margin-top: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .checkout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    .add-address-btn {
        padding: 10px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .add-address-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    /* Wallet Section */
    .wallet-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 25px;
        color: white;
        text-align: center;
        margin-top: 20px;
    }

    .wallet-balance {
        font-size: 32px;
        font-weight: 700;
        margin: 15px 0;
    }

    .wallet-btn {
        background: white;
        color: #667eea;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .wallet-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .wallet-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .checkout-header h1 {
            font-size: 22px;
        }

        .checkout-steps {
            flex-wrap: wrap;
        }

        .section-card {
            padding: 20px;
        }

        .payment-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .order-summary {
            position: static;
            margin-top: 20px;
        }
    }

    @media (max-width: 480px) {
        .payment-grid {
            grid-template-columns: 1fr;
        }

        .cart-item {
            flex-direction: column;
        }

        .cart-item-image {
            width: 100%;
            height: 150px;
        }
    }
</style>

@section('content')
    <div class="checkout-wrapper">
        <div class="container">
            <!-- Checkout Header -->
            <div class="checkout-header">
                <h1>
                    <i class="las la-shopping-cart"></i> Checkout
                </h1>
                <div class="checkout-steps">
                    <div class="step-item active">
                        <span class="step-number">1</span>
                        <span>Address</span>
                    </div>
                    <i class="las la-arrow-right"></i>
                    <div class="step-item active">
                        <span class="step-number">2</span>
                        <span>Shipping</span>
                    </div>
                    <i class="las la-arrow-right"></i>
                    <div class="step-item active">
                        <span class="step-number">3</span>
                        <span>Payment</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column - Checkout Forms -->
                <div class="col-lg-7">
                    <!-- Address Section -->
                    <div class="section-card">
                        <div class="section-header">
                            <h2 class="section-title">
                                <span class="section-icon">
                                    <i class="las la-map-marker"></i>
                                </span>
                                Delivery Address
                            </h2>
                            @if (Auth::check() && $addresses->count() > 0)
                                <button class="add-address-btn" onclick="toggleAddressForm()">
                                    <i class="las la-plus"></i> Add New
                                </button>
                            @endif
                        </div>

                        <div id="addressList">
                            @if ($selectedAddress)
                                <div class="address-card selected" onclick="selectAddress(this, {{ $selectedAddress->id }})">
                                    <input type="radio" name="address_id" value="{{ $selectedAddress->id }}" checked>
                                    <div class="address-content">
                                        <span class="address-badge">{{ $selectedAddress->address_type }}</span>
                                        <div class="address-name">{{ $selectedAddress->name }}</div>
                                        <div class="address-info">
                                            <i class="las la-phone"></i> {{ $selectedAddress->phone }}<br>
                                            <i class="las la-map-marked-alt"></i> {{ $selectedAddress->address }}<br>
                                            <i class="las la-city"></i> {{ $selectedAddress->area->name }}, {{ $selectedAddress->city->name }}, {{ $selectedAddress->state->name }}
                                        </div>
                                    </div>
                                    <button class="address-edit-btn" onclick="editAddress({{ $selectedAddress->id }})">
                                        <i class="las la-edit"></i>
                                    </button>
                                </div>
                            @endif

                            @forelse ($addresses as $address)
                                @continue($selectedAddress && $address->id == $selectedAddress->id)
                                <div class="address-card" onclick="selectAddress(this, {{ $address->id }})">
                                    <input type="radio" name="address_id" value="{{ $address->id }}">
                                    <div class="address-content">
                                        <span class="address-badge">{{ $address->address_type }}</span>
                                        <div class="address-name">{{ $address->name }}</div>
                                        <div class="address-info">
                                            <i class="las la-phone"></i> {{ $address->phone }}<br>
                                            <i class="las la-map-marked-alt"></i> {{ $address->address }}<br>
                                            <i class="las la-city"></i> {{ $address->area->name }}, {{ $address->city->name }}, {{ $address->state->name }}
                                        </div>
                                    </div>
                                    <button class="address-edit-btn" onclick="editAddress({{ $address->id }})">
                                        <i class="las la-edit"></i>
                                    </button>
                                </div>
                            @empty
                                <div class="new-address-form">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="name" placeholder="John Doe">
                                                <small id="name-error" class="text-danger"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                                <input type="tel" class="form-control" id="phone" placeholder="+880 1XXX-XXXXXX">
                                                <small id="phone-error" class="text-danger"></small>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="address">Street Address <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="address" rows="3" placeholder="House/Flat no., Street, Area"></textarea>
                                                <small id="address-error" class="text-danger"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="state_id">Division <span class="text-danger">*</span></label>
                                                <select class="form-control" id="state_id">
                                                    <option value="">Select Division</option>
                                                    <option value="1">Dhaka</option>
                                                    <option value="2">Chittagong</option>
                                                    <option value="3">Rajshahi</option>
                                                </select>
                                                <small id="state_id-error" class="text-danger"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="city">City <span class="text-danger">*</span></label>
                                                <select class="form-control" id="city">
                                                    <option value="">Select City</option>
                                                </select>
                                                <small id="city-error" class="text-danger"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="area">Area <span class="text-danger">*</span></label>
                                                <select class="form-control" id="area">
                                                    <option value="">Select Area</option>
                                                </select>
                                                <small id="area-error" class="text-danger"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Shipping Section -->
                    <div class="section-card">
                        <div class="section-header">
                            <h2 class="section-title">
                                <span class="section-icon">
                                    <i class="las la-shipping-fast"></i>
                                </span>
                                Shipping Method
                            </h2>
                        </div>

                        <div id="shippingMethods">
                            @foreach ($shippingMethods as $index => $shippingMethod)
                                <div class="shipping-option {{ $index == 0 ? 'selected' : '' }}" onclick="selectShipping(this)">
                                    <input type="radio" name="shipping_method" value="{{ $shippingMethod['id'] ?? $index }}" {{ $index == 0 ? 'checked' : '' }}>
                                    <img src="{{ uploaded_asset($shippingMethod['logo']) }}" alt="{{ $shippingMethod['name'] }}" class="shipping-logo">
                                    <div class="shipping-info">
                                        <div class="shipping-name">{{ $shippingMethod['name'] }}</div>
                                        <div class="shipping-price">
                                            {{ $shippingMethod['price'] == 0 ? 'Free Delivery' : single_price($shippingMethod['price']) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Payment Section -->
                    <div class="section-card">
                        <div class="section-header">
                            <h2 class="section-title">
                                <span class="section-icon">
                                    <i class="las la-credit-card"></i>
                                </span>
                                Payment Method
                            </h2>
                        </div>

                        <div class="payment-grid">
                            @if (get_setting('cash_payment') == 1)
                                @php
                                    $digital = 0;
                                    $cod_on = 1;
                                    foreach ($carts as $cartItem) {
                                        $product = $cartItem->product;
                                        if ($product['digital'] == 1) {
                                            $digital = 1;
                                        }
                                        if ($product['cash_on_delivery'] == 0) {
                                            $cod_on = 0;
                                        }
                                    }
                                @endphp
                                @if ($digital != 1 && $cod_on == 1)
                                    <div class="payment-option selected" onclick="selectPayment(this)">
                                        <input type="radio" name="payment_option" value="cash_on_delivery" checked>
                                        <img src="{{ static_asset('assets/img/cards/cod.png') }}" alt="COD" class="payment-logo">
                                        <div class="payment-name">Cash on Delivery</div>
                                    </div>
                                @endif
                            @endif

                            @if (get_setting('sslcommerz_payment') == 1)
                                <div class="payment-option" onclick="selectPayment(this)">
                                    <input type="radio" name="payment_option" value="sslcommerz">
                                    <img src="{{ static_asset('assets/img/cards/sslcommerz.png') }}" alt="SSLCommerz" class="payment-logo">
                                    <div class="payment-name">SSLCommerz</div>
                                </div>
                            @endif

                            @if (get_setting('bkash') == 1)
                                <div class="payment-option" onclick="selectPayment(this)">
                                    <input type="radio" name="payment_option" value="bkash">
                                    <img src="{{ static_asset('assets/img/cards/bkash-trans.png') }}" alt="bKash" class="payment-logo">
                                    <div class="payment-name">bKash</div>
                                </div>
                            @endif

                            @if (get_setting('nagad') == 1)
                                <div class="payment-option" onclick="selectPayment(this)">
                                    <input type="radio" name="payment_option" value="nagad">
                                    <img src="{{ static_asset('assets/img/cards/nagad.png') }}" alt="Nagad" class="payment-logo">
                                    <div class="payment-name">Nagad</div>
                                </div>
                            @endif

                            @if (get_setting('aamarpay') == 1)
                                <div class="payment-option" onclick="selectPayment(this)">
                                    <input type="radio" name="payment_option" value="aamarpay">
                                    <img src="{{ static_asset('assets/img/cards/aamarpay.png') }}" alt="Aamarpay" class="payment-logo">
                                    <div class="payment-name">Aamarpay</div>
                                </div>
                            @endif
                        </div>

                        @if (Auth::check() && get_setting('wallet_system') == 1)
                            <div class="wallet-section">
                                <div style="opacity: 0.9;">
                                    <i class="las la-wallet" style="font-size: 40px;"></i>
                                </div>
                                <div style="opacity: 0.9; margin-top: 10px;">Wallet Balance</div>
                                <div class="wallet-balance">{{ single_price(Auth::user()->balance) }}</div>
                                @if (Auth::user()->balance < $total)
                                    <button type="button" class="wallet-btn" disabled>
                                        <i class="las la-exclamation-circle"></i> Insufficient Balance
                                    </button>
                                @else
                                    <button type="button" class="wallet-btn" onclick="useWallet()">
                                        <i class="las la-check-circle"></i> Pay with Wallet
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column - Order Summary -->
                <div class="col-lg-5">
                    <div class="order-summary">
                        <h3 class="section-title mb-4">
                            <span class="section-icon">
                                <i class="las la-receipt"></i>
                            </span>
                            Order Summary
                        </h3>

                        <!-- Cart Items -->
                        <div id="cartItems">
                            <!-- Sample Cart Items for Testing -->
                            <div class="cart-item">
                                <img src="https://via.placeholder.com/80" alt="Product" class="cart-item-image">
                                <div class="cart-item-details">
                                    <div class="cart-item-name">Premium Wireless Headphones</div>
                                    <div class="cart-item-price">৳2,500</div>
                                    <div class="quantity-control">
                                        <button class="qty-btn" onclick="decreaseQty(this)">−</button>
                                        <input type="number" class="qty-input" value="1" min="1" readonly>
                                        <button class="qty-btn" onclick="increaseQty(this)">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="cart-item">
                                <img src="https://via.placeholder.com/80" alt="Product" class="cart-item-image">
                                <div class="cart-item-details">
                                    <div class="cart-item-name">Smart Watch Series 5</div>
                                    <div class="cart-item-price">৳3,200</div>
                                    <div class="quantity-control">
                                        <button class="qty-btn" onclick="decreaseQty(this)">−</button>
                                        <input type="number" class="qty-input" value="2" min="1" readonly>
                                        <button class="qty-btn" onclick="increaseQty(this)">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Total -->
                        <div class="order-total">
                            <div class="total-row">
                                <span>Subtotal</span>
                                <span id="subtotal">৳8,900</span>
                            </div>
                            <div class="total-row">
                                <span>Shipping</span>
                                <span id="shipping" class="text-success">Free</span>
                            </div>
                            <div class="total-row">
                                <span>Tax (5%)</span>
                                <span id="tax">৳445</span>
                            </div>
                            <div class="total-row grand-total">
                                <span>Total</span>
                                <span id="grandTotal">৳9,345</span>
                            </div>
                        </div>

                        <button class="checkout-btn" onclick="proceedCheckout()">
                            <i class="las la-lock"></i> Place Order Securely
                        </button>

                        <div style="text-align: center; margin-top: 15px; color: #6c757d; font-size: 13px;">
                            <i class="las la-shield-alt"></i> Secure checkout powered by SSL
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Address Selection
    function selectAddress(element, addressId) {
        document.querySelectorAll('.address-card').forEach(card => {
            card.classList.remove('selected');
        });
        element.classList.add('selected');
        element.querySelector('input[type="radio"]').checked = true;
        console.log('Selected Address ID:', addressId);
    }

    // Edit Address
    function editAddress(addressId) {
        event.stopPropagation();
        console.log('Edit Address ID:', addressId);
        // Add your edit address logic here
        alert('Edit address functionality - Address ID: ' + addressId);
    }

    // Toggle Address Form
    function toggleAddressForm() {
        alert('Add new address form will open here');
        // Add your form toggle logic here
    }

    // Shipping Selection
    function selectShipping(element) {
        document.querySelectorAll('.shipping-option').forEach(option => {
            option.classList.remove('selected');
        });
        element.classList.add('selected');
        element.querySelector('input[type="radio"]').checked = true;

        // Update shipping cost in summary
        const shippingText = element.querySelector('.shipping-price').textContent;
        document.getElementById('shipping').textContent = shippingText;
        updateGrandTotal();
    }

    // Payment Selection
    function selectPayment(element) {
        document.querySelectorAll('.payment-option').forEach(option => {
            option.classList.remove('selected');
        });
        element.classList.add('selected');
        element.querySelector('input[type="radio"]').checked = true;

        const paymentMethod = element.querySelector('input[type="radio"]').value;
        console.log('Selected Payment Method:', paymentMethod);
    }

    // Quantity Controls
    function increaseQty(button) {
        const input = button.previousElementSibling;
        input.value = parseInt(input.value) + 1;
        updateCartTotal();
    }

    function decreaseQty(button) {
        const input = button.nextElementSibling;
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
            updateCartTotal();
        }
    }

    // Update Cart Total
    function updateCartTotal() {
        let subtotal = 0;
        document.querySelectorAll('.cart-item').forEach(item => {
            const price = parseInt(item.querySelector('.cart-item-price').textContent.replace(/[^0-9]/g, ''));
            const qty = parseInt(item.querySelector('.qty-input').value);
            subtotal += price * qty;
        });

        document.getElementById('subtotal').textContent = '৳' + subtotal.toLocaleString();
        updateGrandTotal();
    }

    // Update Grand Total
    function updateGrandTotal() {
        const subtotal = parseInt(document.getElementById('subtotal').textContent.replace(/[^0-9]/g, ''));
        const shippingText = document.getElementById('shipping').textContent;
        const shipping = shippingText.includes('Free') ? 0 : parseInt(shippingText.replace(/[^0-9]/g, ''));
        const tax = Math.round(subtotal * 0.05);

        document.getElementById('tax').textContent = '৳' + tax.toLocaleString();
        const grandTotal = subtotal + shipping + tax;
        document.getElementById('grandTotal').textContent = '৳' + grandTotal.toLocaleString();
    }

    // Use Wallet
    function useWallet() {
        if (confirm('Are you sure you want to pay with your wallet?')) {
            console.log('Processing wallet payment...');
            alert('Wallet payment processing...');
            // Add your wallet payment logic here
        }
    }

    // Proceed Checkout
    function proceedCheckout() {
        const selectedAddress = document.querySelector('input[name="address_id"]:checked');
        const selectedShipping = document.querySelector('input[name="shipping_method"]:checked');
        const selectedPayment = document.querySelector('input[name="payment_option"]:checked');

        if (!selectedAddress) {
            alert('Please select a delivery address');
            return;
        }

        if (!selectedShipping) {
            alert('Please select a shipping method');
            return;
        }

        if (!selectedPayment) {
            alert('Please select a payment method');
            return;
        }

        console.log('Order Details:', {
            address: selectedAddress.value,
            shipping: selectedShipping.value,
            payment: selectedPayment.value
        });

        alert('Order placed successfully! (This is a test)');
        // Add your checkout logic here
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        updateCartTotal();
        console.log('Checkout page initialized');
    });
</script>
@endpush
