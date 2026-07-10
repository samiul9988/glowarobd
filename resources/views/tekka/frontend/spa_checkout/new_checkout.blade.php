@extends(config('app.theme') . 'frontend.layouts.app')

@section('meta')
    <x-seo />

    <link rel="stylesheet" href="{{ static_asset('assets/tekka/frontend/css/checkout.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/tekka/frontend/css/coupons.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/tekka/frontend/css/gift-offers.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/slider/swiper-bundle.min.css') }}"/>
    <script src="{{ static_asset('assets/slider/swiper-bundle.min.js') }}"></script>
@endsection

@section('content')
    <!-- Loading Overlay -->
    @include('components.loader', ['id' => 'loading'])


    <div class="checkout-page">
        <div class="container">

            <div class="row g-4">
                <div class="col-lg-7 order-lg-1 order-2 mt-4 mt-lg-0">
                    <!-- Shipping Address Section -->
                    <div class="checkout-section" style="overflow: visible; position: relative; z-index: 99;">
                        @includeIf(config('app.theme') . 'frontend.spa_checkout.partials.shipping_addresses', [
                            'addresses' => $addresses,
                            'selectedAddress' => $selectedAddress ?? null,
                        ])
                    </div>

                    <!-- Delivery Method Section -->
                    <div class="checkout-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <span class="section-icon">
                                    <i class="las la-truck"></i>
                                </span>
                                Delivery Method
                            </h2>
                        </div>
                        <div class="section-body" id="shipping-methods-wrapper">
                            <div class="shipping-skeleton-wrapper">
                                <div class="shipping-skeleton">
                                    <div class="skeleton-icon"></div>
                                    <div class="skeleton-text"></div>
                                    <div class="skeleton-price"></div>
                                </div>
                                <div class="shipping-skeleton">
                                    <div class="skeleton-icon"></div>
                                    <div class="skeleton-text"></div>
                                    <div class="skeleton-price"></div>
                                </div>
                                <div class="shipping-skeleton">
                                    <div class="skeleton-icon"></div>
                                    <div class="skeleton-text"></div>
                                    <div class="skeleton-price"></div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Payment Method Section -->
                    <div class="checkout-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <span class="section-icon">
                                    <i class="las la-credit-card"></i>
                                </span>
                                Payment Method
                            </h2>
                        </div>
                        <div class="section-body" id="payment-methods-wrapper">
                            @includeIf(config('app.theme') . 'frontend.spa_checkout.partials.payment_methods')
                        </div>
                    </div>

                    <!-- Order Note -->
                    <div class="checkout-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <span class="section-icon">
                                    <i class="las la-sticky-note"></i>
                                </span>
                                Order Note
                            </h2>
                        </div>
                        <div class="section-body" id="order-note-wrapper">
                            <div class="form-floating-custom">
                                <label for="order_note">Add a note to your order (optional)</label>
                                <textarea class="form-control" id="order_note" rows="3" placeholder="Any specific instructions for delivery?"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Place Order Section -->
                    <div class="checkout-section d-lg-none">
                        <div class="place-order-section">
                            <div class="form-group form-check d-flex" style="align-items: flex-start; gap: 4px;">
                                <input type="checkbox" class="form-check-input aiz-checkbox" name="agree" id="agree_checkbox_" required checked>
                                <label class="form-check-label fw-400 fs-14" for="agree_checkbox_">
                                    <span>
                                        I agree to the
                                        <a class="fw-600 text-dark" href="{{ route('privacypolicy') }}">
                                            Privacy Policy
                                        </a>
                                        and
                                        <a class="fw-600 text-dark" href="{{ route('terms') }}">
                                            Terms &amp; Conditions
                                        </a>
                                    </span>
                                </label>
                            </div>
                            <button type="submit" class="btn-place-order place-order-btn">
                                <i class="las la-lock"></i>
                                Place Order
                            </button>
                            <div class="security-badges">
                                <span class="security-badge">
                                    <i class="las la-shield-alt"></i>
                                    Secure Checkout
                                </span>
                                <span class="security-badge">
                                    <i class="las la-lock"></i>
                                    SSL Encrypted
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Order Summary -->
                <div class="col-lg-5 order-lg-2 order-1">
                    <div class="order-summary-card">
                        <div class="summary-header">
                            <h3 class="summary-title">
                                <i class="las la-shopping-bag"></i>
                                Order Summary
                            </h3>
                        </div>
                        <div class="summary-body">
                            {{-- Coupons --}}
                            <div id="available-coupons">
                            </div>

                            {{-- Cart Items --}}
                            <div id="cart-items-wrapper">
                                <div class="text-center my-5">
                                    <i class="las la-spinner la-spin fs-24"></i>
                                </div>
                            </div>

                            {{-- Gift Offers --}}
                            <div id="giftOffersContainer">
                            </div>

                            {{-- Cart Summary --}}
                            <div id="cart-summary-wrapper">
                            </div>
                        </div>

                        <!-- Place Order (Desktop Only) -->
                        <div class="place-order-section d-none d-lg-block">
                            <div class="form-group form-check d-flex" style="align-items: flex-start; gap: 4px;">
                                <input type="checkbox" class="form-check-input aiz-checkbox" name="agree" id="agree_checkbox" required checked>
                                <label class="form-check-label fw-400 fs-14" for="agree_checkbox">
                                    <span>
                                        I agree to the
                                        <a class="fw-600 text-dark" href="{{ route('privacypolicy') }}">
                                            Privacy Policy
                                        </a>
                                        and
                                        <a class="fw-600 text-dark" href="{{ route('terms') }}">
                                            Terms &amp; Conditions
                                        </a>
                                    </span>
                                </label>
                            </div>
                            <button type="submit" class="btn-place-order place-order-btn">
                                <i class="las la-lock"></i>
                                Place Order
                            </button>
                            <div class="security-badges">
                                <span class="security-badge">
                                    <i class="las la-shield-alt"></i>
                                    Secure Checkout
                                </span>
                                <span class="security-badge">
                                    <i class="las la-lock"></i>
                                    SSL Encrypted
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        @guest
            @if(!session()->get('agree_guest_checkout'))
                <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content" style="border-radius: 12px; border: none; padding: 20px;">
                            <div class="modal-header" style="border-bottom: none; padding-bottom: 0; flex-direction: column; align-items: center;">
                                <div style="color: #1e1e1e;font-weight: 600;font-size: 16px;margin-bottom: 13px;">
                                    <span style="color: #ffc107;font-size: 16px;margin-right: 1px;">⭐</span>
                                    <span>Unlock Your Member Benefits!</span>
                                </div>
                            </div>
                            <div class="modal-body" style="padding-top: 0;text-align: center;">
                                <h2 style="color: #000;font-size: 20px;font-weight: 700;margin-bottom: 10px;">
                                    Do you want to checkout as a guest?
                                </h2>
                                <p style="color: #1e1e1e;font-size: 13px;margin-bottom: 30px;">
                                    Log In for faster checkout, and easy order tracking!
                                </p>

                                <div style="display: flex;justify-content: center;gap: 10px;fle;/* flex-wrap: wrap; */">
                                    <button type="button" class="btn" style="background-color: rgb(26, 26, 26);color: white;border: none;padding: 9px 35px;border-radius: 6px;font-weight: 600;font-size: 14px;transition: background-color 0.3s;" onmouseover="this.style.backgroundColor='#000'" onmouseout="this.style.backgroundColor='#1a1a1a'" onclick="gotoLoginPage()">
                                        Log In
                                    </button>
                                    <button type="button" class="btn" style="background-color: rgb(245, 245, 245);color: rgb(51, 51, 51);border: 1px solid rgb(221, 221, 221);padding: 9px 8px;border-radius: 6px;font-weight: 600;font-size: 14px;transition: background-color 0.3s;" onmouseover="this.style.backgroundColor='#e8e8e8'" onmouseout="this.style.backgroundColor='#f5f5f5'" onclick="continueAsGuest()">
                                        Continue as Guest
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endguest
@endsection

@section('modal')
    <div class="modal fade" id="address-modal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="address-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="address-modal-title">Create Address</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="address-form" method="POST">
                        @csrf
                        <div class="row gutters-5">
                            <div class="col-12 col-md-6">
                                <div class="form-floating-custom">
                                    <label for="input-name">Full Name <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="input-name"
                                        placeholder="Enter your full name" name="name" required>
                                    <small id="input-name-error" class="form-error"></small>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating-custom">
                                    <label for="input-phone">Phone Number <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="input-phone"
                                        placeholder="01xxxxxxxxx" name="phone" required>
                                    <small id="input-phone-error" class="form-error"></small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating-custom">
                                    <label for="input-address">Street Address <span class="required">*</span></label>
                                    <textarea class="form-control" id="input-address" rows="3" placeholder="House no, Street, Area" name="address" required></textarea>
                                    <small id="input-address-error" class="form-error"></small>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating-custom">
                                    <label for="input-country">Country</label>
                                    <input type="text" class="form-control" id="input-country" name="country" value="Bangladesh" readonly
                                        disabled>
                                </div>
                            </div>
                            <input type="hidden" id="input-address-id" value="">
                            <div class="col-12 col-md-6">
                                <div class="form-floating-custom">
                                    <label for="input-state_id">Division <span class="required">*</span></label>
                                    <select class="form-control form-select aiz-selectpicker" data-live-search="true"
                                        name="state_id" id="input-state_id" required>
                                        <option value="" disabled selected>Select Division</option>
                                    </select>
                                    <small id="input-state_id-error" class="form-error"></small>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating-custom">
                                    <label for="input-city">City <span class="required">*</span></label>
                                    <select class="form-control form-select aiz-selectpicker" data-live-search="true"
                                        name="city_id" id="input-city" required>
                                        <option value="" disabled selected>Select City</option>
                                    </select>
                                    <small id="input-city-error" class="form-error"></small>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating-custom">
                                    <label for="input-area">Area <span class="required">*</span></label>
                                    <select class="form-control form-select aiz-selectpicker" data-live-search="true"
                                        name="area_id" id="input-area" required>
                                        <option value="" disabled selected>Select Area</option>
                                    </select>
                                    <small id="input-area-error" class="form-error"></small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info" id="address-submit-btn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="otp-verification-modal" tabindex="-1" role="dialog" aria-labelledby="otpVerificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Verify Your Phone</h5>
                    <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-center text-muted mb-3">
                        We've sent a verification code to your phone number. Please enter it below.
                    </p>
                    <div class="form-group mb-3">
                        <input type="hidden" name="guest_phone" value="">
                        <input type="text" id="otp_code" class="form-control form-control-lg text-center"
                            placeholder="Enter OTP" required name="otpCode" maxlength="6"
                            style="letter-spacing: 8px; font-size: 24px;">
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span id="otp-timer-wrapper" style="display: none;">
                            Resend in <span id="otp-timer" class="font-weight-bold">59</span>s
                        </span>
                        <button
                            type="button"
                            id="resend_otp_button"
                            class="btn btn-link p-0"
                            onclick="resendOTP()"
                            disabled
                        >
                            Resend OTP
                        </button>
                    </div>
                    <div class="form-group text-center mb-0">
                        <button type="button" id="verify_otp_button" class="btn btn-dark btn-block" onclick="verifyOTP()">
                            <i class="las la-check-circle mr-1"></i> Verify & Place Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        window.addEventListener('load', function () {
            if ($('#checkoutModal').length) {
                $('#checkoutModal').modal('show');
            }
        });
        function gotoLoginPage() {
            window.location.href = "{{ route('user.login') }}?redirect={{ encrypt(route('checkout.shipping_info')) }}";
        }
        function continueAsGuest() {
            $('#checkoutModal').modal('hide');
            $.get("{{ route('checkout.continue_as_guest') }}", function(data) {
                console.log(data);
            });
        }

        (function() {
            'use strict';

            // CONFIGURATION
            const CONFIG = {
                routes: {
                    // Views Routes
                    checkoutSummary: '{{ route('checkout.summary') }}',
                    cartsView: '{{ route('checkout.view.carts') }}',
                    cartSummaryView: '{{ route('checkout.view.cart_summary') }}',
                    shippingMethodsView: '{{ route('checkout.view.shipping_methods') }}',
                    paymentMethodsView: '{{ route('checkout.view.payment_methods') }}',
                    giftOffersView: '{{ route('checkout.view.gift_offers') }}',
                    // Location routes
                    getState: '{{ route('get-state') }}',
                    getCity: '{{ route('get-city') }}',
                    getArea: '{{ route('get-area') }}',
                    // Address routes
                    addAddress: '{{ route('addresses.store') }}',
                    updateAddress: '{{ route('addresses.update', ':id') }}',
                    // Legacy routes
                    getShippingMethods: '{{ route('checkout.get_shipping_methods') }}',
                    applyCoupon: '{{ route('checkout.ajax.apply_coupon_code') }}',
                    removeCoupon: '{{ route('checkout.ajax.remove_coupon_code') }}',
                    addGiftToCart: '{{ route('cart.addGiftToCart') }}',
                    // Coupons
                    getAvailableCoupons: '{{ route('get_available_coupons') }}',
                    // New AJAX checkout routes
                    ajax: {
                        updateQuantity: '{{ route('checkout.ajax.update_quantity') }}',
                        removeItem: '{{ route('checkout.ajax.remove_item') }}',
                        shippingMethods: '{{ route('checkout.ajax.shipping_methods') }}',
                        cartSummary: '{{ route('checkout.ajax.cart_summary') }}',
                        checkoutData: '{{ route('checkout.ajax.checkout_data') }}',
                        updateAddress: '{{ route('checkout.ajax.update_address') }}',
                        areaChange: '{{ route('checkout.ajax.area_change') }}',
                        shippingMethodChange: '{{ route('checkout.ajax.shipping_method_change') }}',

                    }
                },
                csrfToken: $('meta[name="csrf-token"]').attr('content'),
                debounceDelay: 300,
                placeholderImage: '{{ static_asset('assets/img/placeholder.jpg') }}'
            };

            // STATE MANAGEMENT
            const state = {
                selectedAddressId: null,
                selectedShippingMethod: null,
                selectedPaymentMethod: null,
                selectedAreaId: null,
                newAddressData: {
                    name: '',
                    phone: '',
                    address: '',
                    state_id: null,
                    city_id: null,
                    area_id: null
                },
                isLoading: false
            };

            // DOM ELEMENTS CACHE
            const DOM = {
                // Views Wrappers
                cartItemsWrapper: null,
                cartSummaryWrapper: null,
                shippingMethodsWrapper: null,
                paymentMethodsWrapper: null,
                // Selectors
                addressCards: null,
                shippingOptions: null,
                paymentOptions: null,
                // Form elements
                stateSelect: null,
                citySelect: null,
                areaSelect: null,
                // Address form elements
                addressForm: null,
                addressFormStateSelect: null,
                addressFormCitySelect: null,
                addressFormAreaSelect: null,
                // Cart elements
                cartItemRemoveBtns: null,
                qtyBtns: null,
                cartItemsList: null,
                // Summary elements
                summarySubtotal: null,
                summaryShipping: null,
                summaryDiscount: null,
                summaryTax: null,
                summaryGiftOffer: null,
                summaryTotal: null,
                mobileTotalAmount: null,
                // Coupon elements
                couponContainer: null,
                // Loading
                loadingOverlay: null,
                shippingOfferMessage: null,
                shippingMethodsContainer: null,
                giftOffersContainer: null,
                // Order placement elements
                placeOrderBtn: null,
            };

            // UTILITY FUNCTIONS
            function getHeaders() {
                return {
                    'X-CSRF-TOKEN': CONFIG.csrfToken
                };
            }

            function showLoading(show = true) {
                state.isLoading = show;
                if (DOM.loadingOverlay && DOM.loadingOverlay.length) {
                    if (show) {
                        DOM.loadingOverlay.show();
                    } else {
                        DOM.loadingOverlay.hide();
                    }
                }
            }

            function refreshSelectPicker() {
                if (typeof AIZ !== 'undefined' && AIZ.plugins && AIZ.plugins.bootstrapSelect) {
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            }

            function notify(type, message) {
                if (typeof AIZ !== 'undefined' && AIZ.plugins && AIZ.plugins.notify) {
                    AIZ.plugins.notify(type, message);
                } else {
                    console.log(`[${type}] ${message}`);
                }
            }

            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            // Get current context data for AJAX requests
            function getRequestContext() {
                return {
                    address_id: state.selectedAddressId,
                    area_id: state.selectedAreaId || DOM.areaSelect?.val() || null,
                    shipping_method_id: state.selectedShippingMethod
                };
            }

            // AJAX HANDLERS
            async function ajaxRequest(url, data = {}, method = 'POST') {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        headers: getHeaders(),
                        url: url,
                        type: method,
                        data: data,
                        success: function(response) {
                            resolve(response);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', error);
                            const errorMessage = xhr.responseJSON?.message || error;
                            reject({
                                error: errorMessage,
                                xhr: xhr
                            });
                        }
                    });
                });
            }

            // ==========================================
            // FETCHING VIEWS
            // ==========================================
            async function getCartsView() {
                try {
                    const response = await ajaxRequest(CONFIG.routes.cartsView, {}, 'GET');
                    if (response) {
                        if (DOM.cartItemsWrapper) {
                            DOM.cartItemsWrapper.html(response);
                            if ($(document).find('.cart-item').length === 0) {
                                showLoading(true);
                                window.location.href = "{{ route('home') }}";
                            }
                            // attachCartItemEventListeners();
                        }
                    } else {
                        notify('warning', response.message || 'Failed to load cart items');
                    }
                } catch (error) {
                    notify('danger', error.error || 'Failed to load cart items');
                }
            }

            async function getCartSummaryView() {
                try {
                    const response = await ajaxRequest(CONFIG.routes.cartSummaryView, {}, 'GET');
                    if (response) {
                        if (DOM.cartSummaryWrapper) {
                            DOM.cartSummaryWrapper.html(response);
                        }
                    } else {
                        notify('warning', response.message || 'Failed to load cart summary');
                    }
                } catch (error) {
                    notify('danger', error.error || 'Failed to load cart summary');
                }
            }

            async function getShippingMethodsView(payloads = {}) {
                try {
                    const response = await ajaxRequest(CONFIG.routes.shippingMethodsView, payloads, 'GET');
                    if (response) {
                        if (DOM.shippingMethodsWrapper) {
                            DOM.shippingMethodsWrapper.html(response);
                            attachShippingMethodEventListeners();
                        }
                    } else {
                        notify('warning', response.message || 'Failed to load shipping methods');
                    }
                } catch (error) {
                    notify('danger', error.error || 'Failed to load shipping methods');
                }
            }

            function attachShippingMethodEventListeners() {
                // Re-cache shipping options after dynamic load
                DOM.shippingOptions = $('.shipping-option');

                // Get initially checked shipping method
                const initiallyChecked = $('input[name="shipping_method"]:checked').val();
                if (initiallyChecked) {
                    state.selectedShippingMethod = initiallyChecked;
                }

                console.log('Shipping methods attached. Initial selection:', state.selectedShippingMethod);
            }

            async function getGiftOffers() {
                try {
                    const response = await ajaxRequest(CONFIG.routes.giftOffersView, {}, 'GET');
                    if (response.success) {
                        if (response.offers_view.length === 0) {
                            DOM.giftOffersContainer.html('').fadeOut(300);
                            return;
                        }
                        DOM.giftOffersContainer.html(response.offers_view || '').fadeIn(300);
                    } else {
                        notify('warning', response.message || 'Failed to load gift offers');
                    }
                } catch (error) {
                    notify('danger', error.error || 'Failed to load gift offers');
                }
            }

            async function getAvailableCouponsView() {
                try {
                    const response = await ajaxRequest(CONFIG.routes.getAvailableCoupons, {}, 'GET');
                    if (response) {
                        if (DOM.couponContainer) {
                            DOM.couponContainer.html(response.coupons_view || '');
                            AIZ.plugins.slickCarousel();
                        }
                    } else {
                        console.error(response);
                    }
                } catch (error) {
                    console.error(error.error || 'Failed to load available coupons');
                }
            }

            // ==========================================
            // CART OPERATIONS (Dynamic Update)
            // ==========================================

            async function updateCartQuantity(cartId, isPlus, buttonElement) {
                if (!cartId || state.isLoading) return;

                const qtyValueElement = buttonElement.siblings('.qty-value');
                const currentValue = parseInt(qtyValueElement.text()) || 1;

                // Validate quantity limits
                if (!isPlus && currentValue <= 1) {
                    notify('warning', 'Minimum quantity is 1');
                    return;
                }

                buttonElement.prop('disabled', true);
                showLoading(true);

                // Optimistic UI update
                qtyValueElement.text(isPlus ? currentValue + 1 : currentValue - 1);

                try {
                    const context = getRequestContext();
                    const response = await ajaxRequest(CONFIG.routes.ajax.updateQuantity, {
                        cart_id: cartId,
                        is_plus: isPlus,
                        ...context
                    });

                    if (response.success) {
                        window.dispatchEvent(new CustomEvent('cart-updated'));
                        notify('success', 'Quantity updated');
                    } else {
                        // Revert optimistic update
                        qtyValueElement.text(currentValue);
                        notify('warning', response.message || 'Failed to update quantity');
                    }
                } catch (error) {
                    // Revert optimistic update
                    qtyValueElement.text(currentValue);
                    notify('danger', error.error || 'Failed to update quantity');
                } finally {
                    buttonElement.prop('disabled', false);
                    showLoading(false);
                }
            }

            async function removeFromCart(cartId) {
                if (!cartId || state.isLoading) return;

                showLoading(true);

                try {
                    const context = getRequestContext();
                    const response = await ajaxRequest(CONFIG.routes.ajax.removeItem, {
                        cart_id: cartId,
                        ...context
                    });

                    if (response.success) {
                        window.dispatchEvent(new CustomEvent('cart-updated'));
                        notify('success', 'Item removed from cart');
                    } else {
                        notify('danger', response.message || 'Failed to remove item');
                    }
                } catch (error) {
                    notify('danger', error.error || 'Failed to remove item');
                } finally {
                    showLoading(false);
                }
            }

            // ==========================================
            // UI UPDATE FUNCTIONS
            // ==========================================

            function renderEmptyCart() {
                DOM.cartItemsList.html(`
                    <div class="empty-cart">
                        <div class="empty-cart-icon">
                            <i class="las la-shopping-cart"></i>
                        </div>
                        <div class="empty-cart-title">Your cart is empty</div>
                        <div class="empty-cart-text">Add some products to continue</div>
                    </div>
                `);
            }

            // ==========================================
            // SHIPPING METHODS
            // ==========================================

            function showShippingSkeleton() {
                const container = DOM.shippingMethodsWrapper;
                container.html(`
                    <div class="shipping-skeleton-wrapper">
                        <div class="shipping-skeleton">
                            <div class="skeleton-icon"></div>
                            <div class="skeleton-text"></div>
                            <div class="skeleton-price"></div>
                        </div>
                        <div class="shipping-skeleton">
                            <div class="skeleton-icon"></div>
                            <div class="skeleton-text"></div>
                            <div class="skeleton-price"></div>
                        </div>
                        <div class="shipping-skeleton">
                            <div class="skeleton-icon"></div>
                            <div class="skeleton-text"></div>
                            <div class="skeleton-price"></div>
                        </div>
                    </div>
                `);
            }

            function renderShippingMethods(methods) {
                const container = DOM.shippingMethodsContainer;

                if (!methods || methods.length === 0) {
                    container.html(
                        '<div class="shipping-empty"><i class="las la-exclamation-circle"></i><p>No delivery methods available</p></div>'
                    );
                    return;
                }

                let html = '';
                methods.forEach((method, index) => {
                    const isSelected = index === 0 ? 'selected' : '';
                    const isChecked = index === 0 ? 'checked' : '';
                    const priceClass = method.price == 0 ? 'free' : '';
                    const priceText = method.price == 0 ? 'FREE' : method.formatted_price || `৳${method.price}`;

                    html += `
                        <div class="shipping-option ${isSelected}" onclick="selectShippingOption(this)">
                            <input type="radio" name="shipping_method" value="${method.id || index}" ${isChecked} required>
                            <div class="shipping-icon">
                                <img src="${method.logo || ''}" alt="${method.name}" onerror="this.onerror=null;this.src='${CONFIG.placeholderImage}';">
                            </div>
                            <div class="shipping-name">${method.name}</div>
                            <div class="shipping-price ${priceClass}">${priceText}</div>
                            <div class="shipping-check">
                                <i class="las la-check"></i>
                            </div>
                        </div>
                    `;
                });

                container.html(html);

                // Update state with first selected method
                if (methods.length > 0) {
                    state.selectedShippingMethod = methods[0].id || 0;
                }

                // Re-cache DOM elements
                DOM.shippingOptions = $('.shipping-option');
            }

            async function getShippingMethods(addressId = null, areaId = null) {
                const targetAreaId = areaId || state.selectedAreaId || DOM.areaSelect?.val() || null;

                if (!addressId && !targetAreaId) {
                    return;
                }

                // Show skeleton loader
                showShippingSkeleton();

                try {
                    const response = await ajaxRequest(CONFIG.routes.ajax.shippingMethods, {
                        address_id: addressId,
                        area_id: targetAreaId
                    }, 'GET');

                    DOM.shippingOfferMessage.text(response.shipping_message || '');

                    if (response.success && response.shipping_methods) {
                        renderShippingMethods(response.shipping_methods);
                    } else {
                        DOM.shippingMethodsContainer.html(
                            '<div class="shipping-empty"><i class="las la-exclamation-circle"></i><p>No delivery methods available for this area</p></div>'
                        );
                    }
                } catch (error) {
                    DOM.shippingMethodsContainer.html(
                        '<div class="shipping-empty error"><i class="las la-times-circle"></i><p>Error loading delivery methods</p></div>'
                    );
                    console.error('Error getting shipping methods:', error);
                }
            }

            // Global function for shipping option click
            window.selectShippingOption = async function(element) {
                const $el = $(element);
                $el.find('input[type="radio"]').prop('checked', true);
                $('.shipping-option').removeClass('selected');
                $el.addClass('selected');

                const newShippingMethodId = $el.find('input[type="radio"]').val();

                if (state.selectedShippingMethod !== newShippingMethodId) {
                    state.selectedShippingMethod = newShippingMethodId;
                    // Update summary when shipping method changes
                    await onShippingMethodChange(newShippingMethodId);
                }
            };

            async function onShippingMethodChange(shippingMethodId) {
                showLoading(true);

                try {
                    const context = getRequestContext();
                    const response = await ajaxRequest(CONFIG.routes.ajax.shippingMethodChange, {
                        shipping_method_id: shippingMethodId,
                        ...context
                    });

                    if (response.success) {
                        dispatchEvent(new CustomEvent('cart-summary-updated'));
                    }
                } catch (error) {
                    console.error('Error updating shipping method:', error);
                } finally {
                    showLoading(false);
                }
            }

            // ==========================================
            // ADDRESS HANDLERS
            // ==========================================

            async function onAddressChange(addressId) {
                if (!addressId || state.isLoading) return;

                state.selectedAddressId = addressId;
                showLoading(true);

                try {
                    await getShippingMethodsView({
                        address_id: addressId
                    });
                    await getCartSummaryView();
                } catch (error) {
                    notify('danger', error.error || 'Failed to update address');
                } finally {
                    showLoading(false);
                }
            }

            async function onAreaChange(areaId) {
                if (!areaId || state.isLoading) return;

                state.selectedAreaId = areaId;
                showLoading(true);

                try {
                    await getShippingMethodsView({
                        area_id: areaId
                    });
                    await getCartSummaryView();
                } catch (error) {
                    notify('danger', error.error || 'Failed to update area');
                } finally {
                    showLoading(false);
                }
            }

            // ==========================================
            // LOCATION HANDLERS (Division/City/Area)
            // ==========================================

            async function getStates(countryId = '') {
                DOM.stateSelect.html('<option value="" disabled selected>Loading...</option>');
                DOM.citySelect.html('<option value="" disabled selected>Select Division First</option>');
                DOM.areaSelect.html('<option value="" disabled selected>Select City First</option>');

                DOM.addressFormStateSelect.html('<option value="" disabled selected>Loading...</option>');
                DOM.addressFormCitySelect.html('<option value="" disabled selected>Select Division First</option>');
                DOM.addressFormAreaSelect.html('<option value="" disabled selected>Select City First</option>');

                refreshSelectPicker();

                try {
                    const response = await ajaxRequest(CONFIG.routes.getState, {
                        country_id: countryId
                    });
                    const options = JSON.parse(response);
                    if (options) {
                        DOM.stateSelect.html(options);
                        DOM.addressFormStateSelect.html(options);
                        refreshSelectPicker();
                    }
                } catch (error) {
                    DOM.stateSelect.html('<option value="" disabled selected>Error loading divisions</option>');
                    DOM.addressFormStateSelect.html('<option value="" disabled selected>Error loading divisions</option>');
                    refreshSelectPicker();
                    console.error('Error loading states:', error);
                }
            }

            async function getCities(stateId) {
                if (!stateId) return;

                DOM.citySelect.html('<option value="" disabled selected>Loading...</option>');
                DOM.addressFormCitySelect.html('<option value="" disabled selected>Loading...</option>');
                DOM.areaSelect.html('<option value="" disabled selected>Select City First</option>');
                DOM.addressFormAreaSelect.html('<option value="" disabled selected>Select City First</option>');
                refreshSelectPicker();

                try {
                    const response = await ajaxRequest(CONFIG.routes.getCity, {
                        state_id: stateId
                    });
                    const options = JSON.parse(response);
                    if (options) {
                        DOM.citySelect.html(options);
                        DOM.addressFormCitySelect.html(options);
                        refreshSelectPicker();
                    }
                } catch (error) {
                    DOM.citySelect.html('<option value="" disabled selected>Error loading cities</option>');
                    DOM.addressFormCitySelect.html('<option value="" disabled selected>Error loading cities</option>');
                    refreshSelectPicker();
                    console.error('Error loading cities:', error);
                }
            }

            async function getAreas(cityId) {
                if (!cityId) return;

                DOM.areaSelect.html('<option value="" disabled selected>Loading...</option>');
                DOM.addressFormAreaSelect.html('<option value="" disabled selected>Loading...</option>');
                refreshSelectPicker();

                try {
                    const response = await ajaxRequest(CONFIG.routes.getArea, {
                        city_id: cityId
                    });
                    const options = JSON.parse(response);
                    if (options) {
                        DOM.areaSelect.html(options);
                        DOM.addressFormAreaSelect.html(options);
                        refreshSelectPicker();
                    }
                } catch (error) {
                    DOM.areaSelect.html('<option value="" disabled selected>Error loading areas</option>');
                    DOM.addressFormAreaSelect.html('<option value="" disabled selected>Error loading areas</option>');
                    refreshSelectPicker();
                    console.error('Error loading areas:', error);
                }
            }

            // ==========================================
            // COUPON HANDLERS
            // ==========================================

            async function applyCoupon(couponCode) {
                if (!couponCode || couponCode.trim() === '') {
                    notify('warning', 'Please enter a coupon code');
                    return false;
                }

                showLoading(true);

                let success = false;
                try {
                    const response = await ajaxRequest(CONFIG.routes.applyCoupon, {
                        code: couponCode.trim()
                    });
                    if (response.success) {
                        dispatchEvent(new CustomEvent('cart-summary-updated'));
                        notify('success', response.message || 'Coupon applied successfully');
                        success = true;
                    } else {
                        success = false;
                        notify('danger', response.message || 'Invalid coupon code');
                    }
                } catch (error) {
                    success = false;
                    notify('danger', error.error || 'Failed to apply coupon');
                } finally {
                    showLoading(false);
                }
                return success;
            }

            async function removeCoupon() {
                showLoading(true);

                try {
                    await ajaxRequest(CONFIG.routes.removeCoupon);
                    dispatchEvent(new CustomEvent('cart-summary-updated'));
                    notify('success', 'Coupon removed');
                } catch (error) {
                    notify('danger', error.error || 'Failed to remove coupon');
                } finally {
                    showLoading(false);
                }
            }

            // ==========================================
            // SELECTION HANDLERS
            // ==========================================

            function initAddressSelection() {
                DOM.addressCards.each(function() {
                    const radio = $(this).find('input[type="radio"]');
                    if (radio.prop('checked')) {
                        $(this).addClass('selected');
                        state.selectedAddressId = radio.val();
                    }
                });
            }

            function initShippingSelection() {
                // Re-query shipping options in case they were loaded dynamically
                const shippingOptions = $('.shipping-option');
                shippingOptions.each(function() {
                    const radio = $(this).find('input[type="radio"]');
                    if (radio.prop('checked')) {
                        $(this).addClass('selected');
                        state.selectedShippingMethod = radio.val();
                    }
                });
                console.log('Init shipping selection:', state.selectedShippingMethod);
            }

            function initPaymentSelection() {
                // Re-query payment options in case they were loaded dynamically
                const paymentOptions = $('.payment-option');
                paymentOptions.each(function() {
                    const radio = $(this).find('input[type="radio"]');
                    if (radio.prop('checked')) {
                        $(this).addClass('selected');
                        state.selectedPaymentMethod = radio.val();
                    }
                });
                console.log('Init payment selection:', state.selectedPaymentMethod);
            }

            // ==========================================
            // EVENT BINDINGS
            // ==========================================

            function bindLocationEvents() {
                DOM.stateSelect.on('change', function() {
                    const stateId = $(this).val();
                    getCities(stateId);
                });

                DOM.citySelect.on('change', function() {
                    const cityId = $(this).val();
                    getAreas(cityId);
                });

                DOM.areaSelect.on('change', function() {
                    const areaId = $(this).val();
                    state.selectedAreaId = areaId;
                    onAreaChange(areaId);
                });

                DOM.addressFormStateSelect.on('change', function() {
                    const stateId = $(this).val();
                    getCities(stateId);
                });

                DOM.addressFormCitySelect.on('change', function() {
                    const cityId = $(this).val();
                    getAreas(cityId);
                });

            }

            function bindCartEvents() {
                // Remove from cart (using event delegation for dynamic elements)
                $(document).on('click', '.cart-item-remove', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const cartId = $(this).data('id');
                    removeFromCart(cartId);
                });

                // Quantity buttons (using event delegation for dynamic elements)
                $(document).on('click', '.qty-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const cartId = $(this).data('id');
                    const isPlus = $(this).hasClass('qty-plus') ? 1 : 0;
                    updateCartQuantity(cartId, isPlus, $(this));
                });
            }

            function bindCouponEvents() {
                // Apply/Remove coupon
                $(document).on('click', '.apply-coupon', async function() {
                    const couponCode = $(this).data('coupon');
                    if (!couponCode) {
                        notify('warning', 'Invalid coupon code');
                        return;
                    }
                    $(document).find('#coupon_code').val(couponCode);
                    $(this).text('Applying...').attr('disabled', true);
                    const success = await applyCoupon(couponCode);
                    if (success) {
                        $(this).text('Applied').addClass('applied').removeClass('apply-coupon');
                    } else {
                        $(this).text('Apply Code').attr('disabled', false);
                    }
                });

                $(document).on('click', '.btn-apply-coupon', async function() {
                    const couponCode = $(document).find('#coupon_code').val();
                    const action = $(this).data('action');
                    $(this).attr('disabled', true);
                    if (action === 'apply') {
                        const success = await applyCoupon(couponCode);
                        if (!success) {
                            $(this).attr('disabled', false);
                        }
                    } else if (action === 'remove') {
                        removeCoupon();
                    }
                });

                // Apply coupon on Enter key
                $(document).on('keypress', '#coupon_code', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        const couponCode = $(this).val();
                        applyCoupon(couponCode);
                    }
                });
            }

            function bindShippingSelection() {
                // Use event delegation for dynamically loaded shipping options
                $(document).off('click', '.shipping-option').on('click', '.shipping-option', async function() {
                    // Update UI
                    $('.shipping-option').removeClass('selected');
                    $(this).addClass('selected');
                    $(this).find('input[type="radio"]').prop('checked', true);

                    // Get selected method ID
                    const selectedMethodId = $(this).find('input[type="radio"]').val();

                    // Only trigger change if different from current selection
                    if (state.selectedShippingMethod !== selectedMethodId) {
                        state.selectedShippingMethod = selectedMethodId;
                        console.log('Shipping method changed to:', selectedMethodId);
                        // Update summary when shipping method changes
                        await onShippingMethodChange(selectedMethodId);
                    }
                });

                // Also listen for radio change events (for accessibility)
                $(document).off('change', 'input[name="shipping_method"]').on('change', 'input[name="shipping_method"]', async function() {
                    const selectedMethodId = $(this).val();
                    const $shippingOption = $(this).closest('.shipping-option');

                    // Update UI
                    $('.shipping-option').removeClass('selected');
                    $shippingOption.addClass('selected');

                    if (state.selectedShippingMethod !== selectedMethodId) {
                        state.selectedShippingMethod = selectedMethodId;
                        console.log('Shipping method radio changed to:', selectedMethodId);
                        await onShippingMethodChange(selectedMethodId);
                    }
                });

                // Set initial selection from DOM
                const initiallyChecked = $('input[name="shipping_method"]:checked').val();
                if (initiallyChecked && !state.selectedShippingMethod) {
                    state.selectedShippingMethod = initiallyChecked;
                }
                console.log('Shipping selection bound. Current:', state.selectedShippingMethod);
            }

            function bindSelectionEvents() {
                // Address selection
                $(document).on('click', '.address-card', function() {
                    DOM.addressCards.removeClass('selected');
                    $(this).addClass('selected');
                    $(this).find('input[type="radio"]').prop('checked', true);
                    const addressId = $(this).find('input[type="radio"]').val();

                    if (state.selectedAddressId !== addressId) {
                        onAddressChange(addressId);
                    }
                });

                function resetFormInputs() {
                    $('#input-address-id').val('');
                    $('#input-name').val('');
                    $('#input-phone').val('');
                    $('#input-address').val('');
                    $('#input-state_id').val('');
                    $('#input-city').val('');
                    $('#input-area').val('');
                }

                // Address edit button (stop propagation to prevent card click)
                $(document).on('click', '.address-edit-btn', async function(e) {
                    e.stopPropagation();
                    resetFormInputs();
                    const address = $(this).data('address');
                    if (!address) return;

                    showLoading(true);

                    await getStates(); // Ensure states are loaded before opening modal
                    await getCities(address.state_id); // Load cities for the address's state
                    await getAreas(address.city_id); // Load areas for the address's city

                    console.log(address);
                    $('#input-address-id').val(address.id);
                    $('#input-name').val(address.name);
                    $('#input-phone').val(address.phone);
                    $('#input-address').val(address.address);
                    $('#input-state_id').val(address.state_id);
                    $('#input-city').val(address.city_id);
                    $('#input-area').val(address.area_id);

                    refreshSelectPicker();
                    // open address edit modal
                    $('#address-modal').modal('show');
                    showLoading(false);
                });

                $(document).on('click', '.btn-add-address', async function() {
                    showLoading(true);
                    resetFormInputs();
                    await getStates(); // Ensure states are loaded before opening modal

                    refreshSelectPicker();
                    // open address add modal
                    $('#address-modal').modal('show');
                    showLoading(false);
                });

                $(document).on('click', '#address-submit-btn', function(e) {
                    e.stopPropagation();
                    const addressId = $('#input-address-id').val();
                    let url = '';
                    if (addressId) {
                        url = CONFIG.routes.updateAddress.replace(':id', addressId);
                    } else {
                        url = CONFIG.routes.addAddress;
                    }

                    const payloads = {
                        name: $('#input-name').val(),
                        phone: $('#input-phone').val(),
                        address: $('#input-address').val(),
                        state_id: $('#input-state_id').val(),
                        city_id: $('#input-city').val(),
                        area_id: $('#input-area').val()
                    };

                    if (payloads.name.trim() === '' || payloads.phone.trim() === '' || payloads.address.trim() === '' ||
                        !payloads.state_id || !payloads.city_id || !payloads.area_id) {
                        notify('warning', 'Please fill all required fields');
                        return;
                    }

                    showLoading(true);
                    $('#address-form').attr('action', url);
                    $('#address-form').submit();
                });

                // Shipping selection is handled by bindShippingSelection with event delegation

                // Payment selection - use event delegation for dynamic elements
                $(document).off('click', '.payment-option').on('click', '.payment-option', function() {
                    $('.payment-option').removeClass('selected');
                    $(this).addClass('selected');
                    $(this).find('input[type="radio"]').prop('checked', true);
                    state.selectedPaymentMethod = $(this).find('input[type="radio"]').val();
                    console.log('Payment method selected:', state.selectedPaymentMethod);
                });

                // Also listen for radio change events
                $(document).off('change', 'input[name="payment_option"]').on('change', 'input[name="payment_option"]', function() {
                    const $paymentOption = $(this).closest('.payment-option');
                    $('.payment-option').removeClass('selected');
                    $paymentOption.addClass('selected');
                    state.selectedPaymentMethod = $(this).val();
                    console.log('Payment method radio changed:', state.selectedPaymentMethod);
                });
            }

            function bindGiftOfferSelection() {
                $(document).on('click', '#giftOffersHeader', function() {
                    $('#giftOffersContent').slideToggle(300);
                    $(this).find('i.fas.fa-chevron-up, i.fas.fa-chevron-down').toggleClass('fa-chevron-up fa-chevron-down');
                });

                $(document).on('click', '.gift-item-btn-ac', function() {
                    const offerCard = $(this).closest('.offer-card');
                    const offerId = offerCard.data('offer-id');
                    const itemCard = $(this).closest('.gift-item-row');
                    const itemId = itemCard.data('item-id');
                    if (!offerId || !itemId) {
                        notify('warning', 'Invalid offer or item selection');
                        return;
                    }

                    addGiftToCart(offerId, itemId);
                });
            }

            async function bindOrderPlacementEvents() {
                $(document).on('click', '.place-order-btn', async function() {
                    // const cartItemsCount = DOM.cartItemsList.find('.cart-item').length;
                    // if (cartItemsCount === 0) {
                    //     notify('warning', 'Your cart is empty, redirecting to homepage to add products');
                    //     showLoading(true);
                    //     window.location.href = '{{ route("home") }}';
                    //     return;
                    // }
                    const isAgreeChecked = $('#agree_checkbox').is(':checked') || $('#agree_checkbox_').is(':checked');
                    if (!isAgreeChecked) {
                        notify('warning', 'Please agree to the Privacy Policy and Terms & Conditions');
                        return;
                    }

                    // Check if we need to validate new address form or existing address
                    const hasAddressCards = $('.address-card').length > 0;
                    const hasNewAddressForm = DOM.addressForm && DOM.addressForm.length > 0;

                    // Get selected address ID (from address cards)
                    const selectedAddressId = $('input[name="address_id"]:checked').val();

                    // Validate address
                    if (hasNewAddressForm && !hasAddressCards) {
                        // New address form validation (no saved addresses)
                        const addressPayload = {
                            name: $('#name').val().trim(),
                            phone: $('#phone').val().trim(),
                            address: $('#address').val().trim(),
                            country: $('#country').val(),
                            state_id: $('#state_id').val(),
                            city_id: $('#city').val(),
                            area_id: $('#area').val()
                        };

                        if (addressPayload.name.length < 2) {
                            notify('warning', 'Please enter a valid name');
                            $('#name').focus();
                            return;
                        }
                        const phoneRegex =  /^(?:\+?88)?01[3-9]\d{8}$/; // Bangladesh phone number regex
                        if (addressPayload.phone.length < 11 || !phoneRegex.test(addressPayload.phone)) {
                            notify('warning', 'Please enter a valid phone number');
                            $('#phone').focus();
                            return;
                        }
                        if (addressPayload.address.length < 5) {
                            notify('warning', 'Please enter a valid address');
                            $('#address').focus();
                            return;
                        }
                        if (!addressPayload.state_id || !addressPayload.city_id || !addressPayload.area_id) {
                            notify('warning', 'Please select all required fields');
                            $('#state_id').focus();
                            return;
                        }

                        // Store address data in state for order submission
                        state.newAddressData = addressPayload;
                    } else if (hasAddressCards && !selectedAddressId) {
                        // Has address cards but none selected
                        notify('warning', 'Please select a shipping address');
                        return;
                    }

                    // Get shipping method (from radio button)
                    const selectedShippingMethod = $('input[name="shipping_method"]:checked').val();
                    if (!selectedShippingMethod) {
                        notify('warning', 'Please select a delivery method');
                        // Scroll to shipping section
                        $('html, body').animate({
                            scrollTop: $('#shipping-methods-wrapper').offset().top - 100
                        }, 500);
                        return;
                    }

                    // Get payment method (from radio button)
                    const selectedPaymentMethod = $('input[name="payment_option"]:checked').val();
                    if (!selectedPaymentMethod) {
                        notify('warning', 'Please select a payment method');
                        // Scroll to payment section
                        $('html, body').animate({
                            scrollTop: $('#payment-methods-wrapper').offset().top - 100
                        }, 500);
                        return;
                    }

                    // Update state with current selections
                    state.selectedAddressId = selectedAddressId || null;
                    state.selectedShippingMethod = selectedShippingMethod;
                    state.selectedPaymentMethod = selectedPaymentMethod;

                    console.log('Order placement data:', {
                        address_id: state.selectedAddressId,
                        new_address: hasNewAddressForm && !hasAddressCards ? state.newAddressData : null,
                        shipping_method: state.selectedShippingMethod,
                        payment_method: state.selectedPaymentMethod
                    });

                    // Submit order flow
                    await submitOrder();
                });
            }

            // ==========================================
            // ORDER SUBMISSION & OTP VERIFICATION
            // ==========================================

            // OTP Timer State
            const otpState = {
                timer: 59,
                timeover: false,
                interval: null,
                phoneNumber: null
            };

            function getTimerFormatted() {
                return otpState.timer < 10 ? '0' + otpState.timer : otpState.timer;
            }

            function startTimer() {
                otpState.timer = 59;
                otpState.timeover = false;

                if (otpState.interval) {
                    clearInterval(otpState.interval);
                }

                otpState.interval = setInterval(() => {
                    if (otpState.timer > 1) {
                        otpState.timer--;
                        $('#otp-timer').text(getTimerFormatted());
                    } else {
                        otpState.timeover = true;
                        clearInterval(otpState.interval);
                        $('#resend_otp_button').prop('disabled', false);
                        $('#otp-timer-wrapper').hide();
                    }
                }, 1000);
            }

            function stopTimer() {
                if (otpState.interval) {
                    clearInterval(otpState.interval);
                    otpState.interval = null;
                }
            }

            async function submitOrder() {
                const isGuest = {{ Auth::check() ? 'false' : 'true' }};
                const hasAddressCards = $('.address-card').length > 0;
                const hasNewAddressForm = DOM.addressForm && DOM.addressForm.length > 0;

                // Get phone number for OTP
                let phoneNumber = state.newAddressData.phone || null;
                // For guest users, require OTP verification
                if (isGuest && phoneNumber) {
                    otpState.phoneNumber = phoneNumber;
                    await sendVerificationCode(phoneNumber);
                } else {
                    // For logged in users, directly place order
                    await placeOrder();
                }
            }

            async function sendVerificationCode(phone) {
                showLoading(true);

                try {
                    const response = await ajaxRequest('{{ route("guest.validate_data") }}', {
                        phone: phone,
                        name: state.newAddressData.name || '',
                        address: state.newAddressData.address || ''
                    });

                    if (response.success) {
                        notify('success', response.message || 'Verification code sent to your phone');
                        // Store phone in hidden field and show OTP modal
                        $('input[name="guest_phone"]').val(phone);
                        $('#otp_code').val('');
                        $('#otp-verification-modal').modal('show');

                        // Start timer
                        $('#otp-timer-wrapper').show();
                        $('#resend_otp_button').prop('disabled', true);
                        startTimer();
                    } else {
                        notify('danger', response.message || 'Failed to send verification code');
                    }
                } catch (error) {
                    notify('danger', error.error || 'Failed to send verification code');
                } finally {
                    showLoading(false);
                }
            }

            async function verifyOTP() {
                const otp = $('#otp_code').val().trim();

                if (otp === '') {
                    notify('danger', 'Please enter the OTP code');
                    return;
                }

                if (otp.length < 4) {
                    notify('danger', 'Please enter a valid OTP code');
                    return;
                }

                $('#verify_otp_button').prop('disabled', true).text('Verifying...');

                try {
                    const response = await ajaxRequest('{{ route("guest.verify_phone") }}', {
                        verification_code: otp,
                        phone: $('input[name="guest_phone"]').val()
                    });

                    if (response.success) {
                        notify('success', response.message || 'Phone verified successfully');
                        $('#otp-verification-modal').modal('hide');
                        stopTimer();

                        // Now place the order
                        await placeOrder();
                    } else {
                        notify('danger', response.message || 'Invalid OTP code');
                    }
                } catch (error) {
                    notify('danger', error.error || 'Failed to verify OTP');
                } finally {
                    $('#verify_otp_button').prop('disabled', false).text('Verify');
                }
            }

            async function resendOTP() {
                const phone = $('input[name="guest_phone"]').val();

                if (!phone) {
                    notify('danger', 'Phone number not found');
                    return;
                }

                $('#resend_otp_button').prop('disabled', true).text('Resending...');

                try {
                    const response = await ajaxRequest('{{ route("guest.resend_code") }}', {
                        phone: phone
                    });

                    if (response.success) {
                        notify('success', response.message || 'OTP sent successfully');
                        $('#otp-timer-wrapper').show();
                        startTimer();
                    } else {
                        notify('danger', response.message || 'Failed to resend OTP');
                        $('#resend_otp_button').prop('disabled', false);
                    }
                } catch (error) {
                    notify('danger', error.error || 'Failed to resend OTP');
                    $('#resend_otp_button').prop('disabled', false);
                } finally {
                    $('#resend_otp_button').text('Resend OTP');
                }
            }

            async function placeOrder() {
                showLoading(true);

                const hasAddressCards = $('.address-card').length > 0;
                const hasNewAddressForm = DOM.addressForm && DOM.addressForm.length > 0;

                // Prepare order data
                const orderData = {
                    payment_option: state.selectedPaymentMethod,
                    shipping_method: state.selectedShippingMethod,
                    note: $('#order_note').val().trim() || null
                };

                // Add address data
                if (state.selectedAddressId) {
                    orderData.address_id = state.selectedAddressId;
                } else if (hasNewAddressForm && !hasAddressCards) {
                    // Add new address fields individually
                    orderData.name = state.newAddressData.name;
                    orderData.phone = state.newAddressData.phone;
                    orderData.address = state.newAddressData.address;
                    orderData.state_id = state.newAddressData.state_id;
                    orderData.city_id = state.newAddressData.city_id;
                    orderData.area_id = state.newAddressData.area_id;
                }

                console.log('Placing order with data:', orderData);

                // Submit as form request
                submitOrderAsForm(orderData);
            }

            function submitOrderAsForm(orderData) {
                // Create a hidden form and submit it
                const form = $('<form>', {
                    method: 'POST',
                    action: '{{ route("payment.checkout") }}'
                });

                // Add CSRF token
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: CONFIG.csrfToken
                }));

                // Add all order data
                Object.keys(orderData).forEach(key => {
                    if (orderData[key] !== null && orderData[key] !== undefined) {
                        form.append($('<input>', {
                            type: 'hidden',
                            name: key,
                            value: orderData[key]
                        }));
                    }
                });

                // Append to body and submit
                $('body').append(form);
                form.submit();
            }

            // Expose OTP functions globally for onclick handlers
            window.verifyOTP = verifyOTP;
            window.resendOTP = resendOTP;

            function addGiftToCart(offerId, itemId) {
                showLoading(true);

                ajaxRequest(CONFIG.routes.addGiftToCart, {
                    offer_id: offerId,
                    item_id: itemId
                }).then(response => {
                    if (response.success) {
                        dispatchEvent(new CustomEvent('cart-updated'));
                        notify('success', response.message || 'Gift added to cart');
                    } else {
                        notify('danger', response.message || 'Failed to add gift to cart');
                    }
                }).catch(error => {
                    notify('danger', error.error || 'Failed to add gift to cart');
                }).finally(() => {
                    showLoading(false);
                });
            }

            // ==========================================
            // INITIALIZATION
            // ==========================================

            function initDOM() {
                // Views Wrappers
                DOM.cartItemsWrapper = $('#cart-items-wrapper');
                DOM.cartSummaryWrapper = $('#cart-summary-wrapper');
                DOM.shippingMethodsWrapper = $('#shipping-methods-wrapper');
                DOM.paymentMethodsWrapper = $('#payment-methods-wrapper');
                // Selection elements
                DOM.addressCards = $('.address-card');
                DOM.shippingOptions = $('.shipping-option');
                DOM.paymentOptions = $('.payment-option');

                // Form elements
                DOM.stateSelect = $('#state_id');
                DOM.citySelect = $('#city');
                DOM.areaSelect = $('#area');

                // Address form elements
                DOM.addressForm = $('.new-address-form');
                DOM.addressFormStateSelect = $('#input-state_id');
                DOM.addressFormCitySelect = $('#input-city');
                DOM.addressFormAreaSelect = $('#input-area');

                // Cart elements
                DOM.cartItemRemoveBtns = $('.cart-item-remove');
                DOM.qtyBtns = $('.qty-btn');
                DOM.cartItemsList = $('#cart-items-list');

                // Loading overlay
                DOM.loadingOverlay = $('#loading');

                // Shipping offer message
                DOM.shippingOfferMessage = $('#shipping-offer-message');
                DOM.shippingMethodsContainer = $('#shipping-methods-container');
                DOM.giftOffersContainer = $('#giftOffersContainer');
                DOM.couponContainer = $('#available-coupons');

                // Order placement elements (no longer needed as we use event delegation)
                // DOM.placeOrderBtn = $('.place-order-btn');

                DOM.body = $('body');
            }

            async function init() {
                // Initialize DOM cache
                initDOM();

                // Initialize selections
                initAddressSelection();
                initShippingSelection();
                initPaymentSelection();

                // Bind events
                bindSelectionEvents();
                bindLocationEvents();
                bindShippingSelection();
                bindCartEvents();
                bindCouponEvents();
                bindOrderPlacementEvents();
                bindGiftOfferSelection();

                // Load initial states if address form is visible
                if (DOM.stateSelect.length > 0 && DOM.addressCards.length === 0) {
                    await getStates();
                }

                await getShippingMethodsView();
                await getAvailableCouponsView();
                await getCartsView();
                await getCartSummaryView();
                // await getPaymentMethodsView();

                await getGiftOffers();
            }

            // EXPOSE GLOBAL FUNCTIONS (if needed for external access)
            window.CheckoutPage = {
                removeFromCart: removeFromCart,
                updateQuantity: updateCartQuantity,
                applyCoupon: applyCoupon,
                removeCoupon: removeCoupon,
                getShippingMethods: getShippingMethods
            };

            // Initialize on document ready
            $(document).ready(function() {
                init();
                window.addEventListener('cart-updated', async () => {
                    await getCartsView();
                    await getShippingMethodsView();
                    await getCartSummaryView();
                    await getGiftOffers();
                });
                window.addEventListener('cart-summary-updated', async () => {
                    await getAvailableCouponsView();
                    await getCartSummaryView();
                });
            });

        })();
    </script>
@endsection
