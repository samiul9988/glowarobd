@if ($carts && count($carts) > 0)
    <div class="p-0 p-lg-0  text-left">
        <div>
            @if (!request()->routeIs('cart'))
                <div class="p-0 mb-4 text-center fs-14">
                    @if ($shipping_charge > 0 && ($sDiscount && $sDiscount['status'] && $sDiscount['min_amount'] > 0))
                        @php
                            $cartTotal = $carts->sum(function ($cartItem) {
                                return ($cartItem['price'] + $cartItem['tax']) * $cartItem['quantity'];
                            });
                            $sDiff = $sDiscount['min_amount'] - $cartTotal;
                        @endphp
                        <span class="text-success text-capitalize">
                            @if ($sDiff > 0)
                                Add More
                                <strong>{{ single_price($sDiff) }}</strong> to Get
                                <strong>{{ single_price($sDiscount['amount']) }}</strong> Shipping Fee Offer
                            @else
                                You're Enjoying <strong>{{ single_price($sDiscount['amount']) }}</strong> Shipping Fee
                                Offer
                            @endif
                        </span>
                        <span class="link link--style-3 text-capitalize" data-toggle="tooltip" data-placement="top"
                            title="Discount will not applicable if non discounted product added on cart">
                            <i class="las la-exclamation-circle"></i>
                        </span>
                    @elseif ($shipping_charge == 0)
                        <span class="text-success text-capitalize">
                            <strong>You are enjoying free shipping on this order!</strong>
                        </span>
                    @endif
                </div>
            @endif
            <ul class="list-group list-group-flush">
                @php
                    $total = 0;
                    $totalerror = 0;
                    $message = '';
                    $minorderamontarray = [0];
                @endphp
                @foreach ($carts as $key => $cartItem)
                    @php
                        $product = $cartItem->product;
                        $product_stock = collect($cartItem->product->stocks)
                            ->where('variant', $cartItem['variation'])
                            ->first();
                        if (!$product_stock) {
                            $product_stock = collect($cartItem->product->stocks)->first();
                        }
                        $cartItem['price'] = getMinimumPriceByVariant(
                            $product,
                            $product_stock,
                            'web',
                            $cartItem['quantity'],
                            $currentlyAuthenticatedUser,
                        );
                        $flash_deal_check = check_flash_deal_product($product);
                        $total = $total + ($cartItem['price'] + $cartItem['tax']) * $cartItem['quantity'];
                        $product_name_with_choice = $product->getTranslation('name');
                        if ($cartItem['variation'] != null) {
                            $product_name_with_choice =
                                $product->getTranslation('name') . ' - ' . $cartItem['variation'];
                        }
                        if ($product->min_order_amount != 0) {
                            $message =
                                translate('Minimum order amount for this product is ') .
                                single_price($product->min_order_amount);
                            $minorderamontarray[] = $product->min_order_amount;
                        }

                        $max_limit = $product_stock->qty;

                        if ($flash_deal_check) {
                            $quantity = $product->flash_deal_product->quantity;
                            if ($product->max_qty > 0) {
                                if ($product->max_qty <= $product->flash_deal_product->quantity) {
                                    $max_limit = $product->max_qty;
                                } else {
                                    $max_limit = $product->flash_deal_product->quantity;
                                }
                            } else {
                                $max_limit = $product->flash_deal_product->quantity;
                            }
                        } else {
                            $quantity = $product_stock->qty;
                            if ($product->max_qty > 0 && $product->max_qty <= $product_stock->qty) {
                                $max_limit = $product->max_qty;
                            } else {
                                $max_limit = $product_stock->qty;
                            }
                        }

                        $preorder_check = check_preorder_product($product);
                        if ($preorder_check) {
                            $max_limit = $product->preorder_max_qty - preorder_product_count($product);
                        }

                        $savedAmount =
                            $product->unit_price * $cartItem['quantity'] - $cartItem['price'] * $cartItem['quantity'];

                    @endphp
                    <li class="list-group-item px-0  ">
                        <div class="row flex-nowrap mx-0">
                            <div class="col-2 col-sm-1 m-0 p-0">
                                <span class="d-flex  align-items-center justify-content-center cartImage ml-1 ml-xxl-2">
                                    <img src="{{ uploaded_asset($product->thumbnail_img) }}" class=" cart_product_img"
                                        alt="{{ $product->getTranslation('name') }}"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                </span>
                            </div>
                            <div class="col-10 col-sm-11 pr-0">
                                <div class="d-flex  justify-content-between align-items-start">
                                    <div class="pl-1 pr-2">
                                        <span class="product-name">{{ $product->name }}
                                        </span>
                                        <!-- static  variant -->
                                        <div class="selected-variant py-1 opacity-80 fs-16">
                                            {{ $cartItem['variation'] }}
                                        </div>
                                        <!-- static variant -->
                                    </div>
                                    <div class=" my-lg-0">
                                        @php
                                            $current_price = $cartItem['price'] + $cartItem['tax'];
                                            $unit_price = $product->unit_price + $cartItem['tax'];
                                            $saved_money =
                                                $unit_price * $cartItem['quantity'] -
                                                $current_price * $cartItem['quantity'];
                                        @endphp
                                        <span
                                            class="fw-600 fs-16 product-price">{{ single_price($current_price * $cartItem['quantity']) }}</span>
                                        @if ($unit_price > $current_price && $saved_money > 0)
                                            <del
                                                class="text-danger fs-12 product-unit-price">{{ single_price($unit_price) }}</del>
                                            <span
                                                class="text-reset badge badge-inline badge-soft-secondary product-saved-price">Saved
                                                {{ single_price($saved_money) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex  justify-content-between align-items-center pt-1 ">
                                    <div class="">
                                        @if ($cartItem['digital'] != 1 && $product->auction_product == 0)
                                            <div class="row no-gutters align-items-center aiz-plus-minus mr-2 ml-0">
                                                <button class="btn col-auto btn-icon btn-sm btn-circle btn-light"
                                                    type="button" data-id="{{ $cartItem['id'] }}" data-type="minus"
                                                    data-field="quantity[{{ $cartItem['id'] }}]"
                                                    @click="decreaseQuantity({{ $cartItem['id'] }})">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" name="quantity[{{ $cartItem['id'] }}]"
                                                    class="input-number inputCart-{{ $cartItem['id'] }}"
                                                    placeholder="1" value="{{ $cartItem['quantity'] }}"
                                                    min="{{ $product->min_qty }}" max="{{ $max_limit }}"
                                                    onchange="updateQuantity({{ $cartItem['id'] }}, this)">
                                                <button class="btn col-auto btn-icon btn-sm btn-circle btn-light"
                                                    type="button" data-id="{{ $cartItem['id'] }}" data-type="plus"
                                                    data-field="quantity[{{ $cartItem['id'] }}]"
                                                    @click="increaseQuantity({{ $cartItem['id'] }})">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        @elseif($product->auction_product == 1)
                                            <span class="fw-600 fs-16">1</span>
                                        @endif
                                    </div>
                                    <div class="text-start mr-md-4">
                                        <a href="javascript:void(0)"
                                            onclick="removeFromCartView(event, {{ $cartItem['id'] }})"
                                            class="removeBtn">
                                            <i class="far fa-trash-alt fs-18 d-md-none"></i>
                                            <span class="d-none d-md-block">
                                                <i class="far fa-trash-alt fs-18"></i>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{--
                            <div class="col-lg col-4 order-1 order-lg-0 my-3 my-lg-0">
                                <span class="opacity-60 fs-12 d-block d-lg-none">{{ ('Price')}}</span>
                                <span class="fw-600 fs-16">{{ single_price($cartItem['price']) }}</span>
                            </div>
                            --}}
                        {{--
                            <div class="col-lg col-4 order-2 order-lg-0 my-3 my-lg-0">
                                <span class="opacity-60 fs-12 d-block d-lg-none">{{ ('Tax')}}</span>
                                <span class="fw-600 fs-16">{{ single_price($cartItem['tax']) }}</span>
                            </div>
                            --}}

                    </li>
                    @php
                        $message = '';
                    @endphp
                @endforeach
            </ul>
        </div>

        @if (!request()->routeIs('cart'))
            <div class="py-md-2">
                @include(config('app.theme') . 'frontend.spa_checkout.cart_summary')
            </div>
        @endif


        {{-- <div class="px-3 py-3 mb-2 border-top d-flex justify-content-between">
            <span class="opacity-60 fs-15">{{ ('Subtotal')}}</span>
            <span class="fw-600 fs-17">{{ single_price($total) }}</span>
        </div>

        <div class="row align-items-center mx-0 pb-4">
            <div class="col-md-6 text-center text-md-left order-1 order-md-0">
                <a href="{{ route('home') }}" class="btn btn-link pl-0">
                    <i class="las la-arrow-left"></i>
                    {{ ('Return to shop')}}
                </a>
            </div>
            <div class="col-md-6 text-center text-md-right">
                @if (Auth::check())

                    <a href="javascript:;" class="btn btn-primary fw-600" onclick="checkminorderamount('{{ route('checkout.shipping_info') }}')">
                        {{ ('Continue to Shipping')}}
                    </a>
                @else
                    <button class="btn btn-primary fw-600" onclick="showCheckoutModal()">{{ ('Continue to Shipping')}}</button>
                @endif
            </div>
        </div> --}}
    </div>
@else
    <div class="p-4 rounded">
        <div class="text-center p-3">
            <i class="las la-frown la-3x opacity-60 mb-3"></i>
            <h3 class="h4 fw-700">{{ 'Your Cart is empty' }}</h3>
        </div>
    </div>
@endif

@section('modal')
    <div class="modal fade" id="login-modal">
        <div class="modal-dialog modal-dialog-zoom">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-600">{{ 'Login' }}</h6>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-3">
                        <form class="form-default" role="form" action="{{ route('cart.login.submit') }}" method="POST">
                            @csrf
                            @if (addon_is_activated('otp_system') && env('DEMO_MODE') != 'On')
                                <div class="form-group phone-form-group mb-1">
                                    <input type="tel" id="phone-code"
                                        class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}"
                                        value="{{ old('phone') }}" placeholder="" name="phone" autocomplete="off">
                                </div>

                                <input type="hidden" name="country_code" value="">

                                <div class="form-group email-form-group mb-1 d-none">
                                    <input type="email"
                                        class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        value="{{ old('email') }}" placeholder="{{ translate('Email') }}" name="email"
                                        id="email" autocomplete="off">
                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group text-right">
                                    <button class="btn btn-link p-0 opacity-50 text-reset" type="button"
                                        onclick="toggleEmailPhone(this)">{{ 'Use Email Instead' }}</button>
                                </div>
                            @else
                                <div class="form-group">
                                    <input type="email"
                                        class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        value="{{ old('email') }}" placeholder="{{ translate('Email') }}"
                                        name="email" id="email" autocomplete="off">
                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div class="form-group">
                                <input type="password"
                                    class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}"
                                    placeholder="{{ 'Password' }}" name="password" id="password">
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span class=opacity-60>{{ translate('Remember Me') }}</span>
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                                <div class="col-6 text-right">
                                    <a href="{{ route('password.request') }}"
                                        class="text-reset opacity-60 fs-14">{{ 'Forgot password?' }}</a>
                                </div>
                            </div>

                            <div class="mb-5">
                                <button type="submit"
                                    class="btn btn-primary btn-block fw-600">{{ translate('Login') }}</button>
                            </div>
                        </form>

                    </div>
                    <div class="text-center mb-3">
                        <p class="text-muted mb-0">{{ 'Dont have an account?' }}</p>
                        <a href="{{ route('user.registration') }}">{{ 'Register Now' }}</a>
                    </div>
                    @if (get_setting('google_login') == 1 || get_setting('facebook_login') == 1 || get_setting('twitter_login') == 1)
                        <div class="separator mb-3">
                            <span class=" px-3 opacity-60">{{ 'Or Login With' }}</span>
                        </div>
                        <ul class="list-inline social colored text-center mb-3">
                            @if (get_setting('facebook_login') == 1)
                                <li class="list-inline-item">
                                    <a href="{{ route('social.login', ['provider' => 'facebook']) }}" class="facebook">
                                        <i class="lab la-facebook-f"></i>
                                    </a>
                                </li>
                            @endif
                            @if (get_setting('google_login') == 1)
                                <li class="list-inline-item">
                                    <a href="{{ route('social.login', ['provider' => 'google']) }}" class="google">
                                        <i class="lab la-google"></i>
                                    </a>
                                </li>
                            @endif
                            @if (get_setting('twitter_login') == 1)
                                <li class="list-inline-item">
                                    <a href="{{ route('social.login', ['provider' => 'twitter']) }}" class="twitter">
                                        <i class="lab la-twitter"></i>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        function removeFromCartView(e, key) {
            e.preventDefault();
            removeFromCart(key);
        }

        function updateQuantity(key, element) {
            $.post('{{ route('cart.updateQuantity') }}', {
                _token: AIZ.data.csrf,
                id: key,
                quantity: element.value
            }, function(data) {
                updateNavCart(data.nav_cart_view, data.cart_count);
                $('#cart-summary').html(data.cart_view);
            });
        }

        function showCheckoutModal() {
            $('#login-modal').modal();
        }

        function checkminorderamount(url) {
            $.post('{{ route('cart.minordercheck') }}', {
                _token: AIZ.data.csrf
            }, function(data) {
                if (data.error == 1) {
                    //$('.error_message').show().html(data.error_message);
                    AIZ.plugins.notify('danger', data.error_message);
                } else {
                    location.href = url;
                }
            });
        }

        // Country Code
        var isPhoneShown = true,
            countryData = window.intlTelInputGlobals.getCountryData(),
            input = document.querySelector("#phone-code");

        for (var i = 0; i < countryData.length; i++) {
            var country = countryData[i];
            if (country.iso2 == 'bd') {
                country.dialCode = '88';
            }
        }

        var iti = intlTelInput(input, {
            separateDialCode: true,
            utilsScript: "{{ static_asset('assets/js/intlTelutils.js') }}?1590403638580",
            onlyCountries: @php echo json_encode(\App\Models\Country::where('status', 1)->pluck('code')->toArray()) @endphp,
            customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
                if (selectedCountryData.iso2 == 'bd') {
                    return "01xxxxxxxxx";
                }
                return selectedCountryPlaceholder;
            }
        });

        var country = iti.getSelectedCountryData();
        $('input[name=country_code]').val(country.dialCode);

        input.addEventListener("countrychange", function(e) {
            // var currentMask = e.currentTarget.placeholder;

            var country = iti.getSelectedCountryData();
            $('input[name=country_code]').val(country.dialCode);

        });

        function toggleEmailPhone(el) {
            if (isPhoneShown) {
                $('.phone-form-group').addClass('d-none');
                $('.email-form-group').removeClass('d-none');
                $('input[name=phone]').val(null);
                isPhoneShown = false;
                $(el).html('{{ 'Use Phone Instead' }}');
            } else {
                $('.phone-form-group').removeClass('d-none');
                $('.email-form-group').addClass('d-none');
                $('input[name=email]').val(null);
                isPhoneShown = true;
                $(el).html('{{ 'Use Email Instead' }}');
            }
        }
    </script>
@endsection
