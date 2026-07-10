@if( $carts && count($carts) > 0 )
    <div class="shadow-sm bg-white p-3 p-lg-0 rounded text-left">
        <div>
            <div class="row d-none d-lg-flex border-bottom mb-3 p-3 bg-primary text-white mx-0">
                <div class="col-md-4 fw-600">{{ translate('Product')}}</div>
                <div class="col fw-600">{{ translate('Price')}}</div>
                <div class="col fw-600">{{ translate('Quantity')}}</div>
                <div class="col fw-600 text-center">{{ translate('Total')}}</div>
                {{--<div class="col-auto fw-600">{{ translate('Remove')}}</div>--}}
            </div>
            <ul class="list-group list-group-flush">
                @php
                    $total = 0;
                    $totalerror = 0;
                    $message = '';
                    $minorderamontarray = [0];

                @endphp
                @foreach ($carts as $key => $cartItem)
                    @php
                        $product = \App\Models\Product::find($cartItem['product_id']);
                        $product_stock = $product->stocks->where('variant', $cartItem['variation'])->first();
                        $cartItem['price'] = getMinimumPriceByVariant($product, $product_stock, 'web', $cartItem['quantity'], $currentlyAuthenticatedUser);
                        $flash_deal_check = check_flash_deal_product($product);
                        $total = $total + ($cartItem['price'] + $cartItem['tax']) * $cartItem['quantity'];
                        $product_name_with_choice = $product->getTranslation('name');
                        if ($cartItem['variation'] != null) {
                            $product_name_with_choice = $product->getTranslation('name').' - '.$cartItem['variation'];
                        }
                        if($product->min_order_amount!=0){
                            $message = translate('Minimum order amount for this product is ').single_price($product->min_order_amount);
                            $minorderamontarray[]=$product->min_order_amount;
                        }


                        $max_limit = $product_stock->qty;

                        if($flash_deal_check){

                            $quantity = $product->flash_deal_product->quantity;
                            if($product->max_qty>0){
                                if($product->max_qty <= $product->flash_deal_product->quantity){
                                    $max_limit = $product->max_qty;
                                }else{
                                    $max_limit = $product->flash_deal_product->quantity;
                                }
                            }else{
                                $max_limit = $product->flash_deal_product->quantity;
                            }
                        }else{
                            $quantity = $product_stock->qty;
                            if($product->max_qty>0 && $product->max_qty <= $product_stock->qty){
                                $max_limit = $product->max_qty;
                            }else{
                                $max_limit = $product_stock->qty;
                            }
                        }

                        $preorder_check = check_preorder_product($product);
                        if($preorder_check){
                            $max_limit = $product->preorder_max_qty - preorder_product_count($product);
                        }

                        $savedAmount = (($product->unit_price * $cartItem['quantity']) - ($cartItem['price'] * $cartItem['quantity']));

                    @endphp
                    <li class="list-group-item px-0 px-lg-3">
                        <div class="row gutters-5">
                            <div class="col-md-4 d-flex">
                                <span class="mr-2 ml-0">
                                    <img
                                        src="{{ uploaded_asset($product->thumbnail_img) }}"
                                        class="img-fit size-60px rounded cart_product_img"
                                        alt="{{ $product->getTranslation('name')  }}"
                                    >
                                </span>
                                <span class="fs-14">
                                    {{ $product_name_with_choice }} 
                                    <br> {{ $message }} 
                                </span>
                            </div>

                            <div class="col-lg col-4 order-1 order-lg-0 my-3 my-lg-0">
                                <span class="opacity-60 fs-12 d-block d-lg-none">{{ translate('Price')}}</span>
                                <span class="fw-600 fs-16">{{ single_price($cartItem['price']) }}</span>
                                <del class="text-danger fs-12">{{ single_price($product->unit_price) }}</del>
                                @if($savedAmount > 0)
                                    <span class="badge badge-inline badge-soft-secondary">You saved {{ single_price($savedAmount) }}</span>
                                @endif
                            </div>
                            {{--<div class="col-lg col-4 order-2 order-lg-0 my-3 my-lg-0">
                                <span class="opacity-60 fs-12 d-block d-lg-none">{{ translate('Tax')}}</span>
                                <span class="fw-600 fs-16">{{ single_price($cartItem['tax']) }}</span>
                            </div>--}}

                            <div class="col-lg text-left col-6 order-4 order-lg-0">
                                @if($cartItem['digital'] != 1 && $product->auction_product == 0)
                                    <div class="row no-gutters align-items-center aiz-plus-minus mr-2 ml-0">
                                        <button class="btn col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-id="{{ $cartItem['id'] }}" data-type="minus" data-field="quantity[{{ $cartItem['id'] }}]">
                                            <i class="las la-minus"></i>
                                        </button>
                                        <input type="number" name="quantity[{{ $cartItem['id'] }}]" class="col border-0 text-center flex-grow-1 fs-16 input-number inputCart-{{ $cartItem['id'] }}" placeholder="1" value="{{ $cartItem['quantity'] }}" min="{{ $product->min_qty }}" max="{{ $max_limit }}" onchange="updateQuantity({{ $cartItem['id'] }}, this)">
                                        <button class="btn col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-id="{{ $cartItem['id'] }}" data-type="plus" data-field="quantity[{{ $cartItem['id'] }}]">
                                            <i class="las la-plus"></i>
                                        </button>
                                    </div>
                                @elseif($product->auction_product == 1)
                                    <span class="fw-600 fs-16">1</span>
                                @endif
                            </div>
                            <div class="col-lg col-6 text-center order-3 order-lg-0 my-3 my-lg-0">
                                <span class="opacity-60 fs-12 d-block d-lg-none">{{ translate('Total')}}</span>
                                <span class="fw-600 fs-16 text-primary">{{ single_price(($cartItem['price'] + $cartItem['tax']) * $cartItem['quantity']) }}</span>
                            </div>
                            {{--<div class="col-lg-auto col-6 order-5 order-lg-0 text-right">
                                <a href="javascript:void(0)" onclick="removeFromCartView(event, {{ $cartItem['id'] }})" class="btn btn-icon btn-sm btn-soft-danger btn-circle">
                                    <i class="las la-trash"></i>
                                </a>
                            </div>--}}
                        </div>
                    </li>
                    @php
                        $message = '';
                    @endphp
                @endforeach
                <a href="{{ route('cart') }}" class="my-2 text-center text-secondary fw-700">Update Cart</a>
            </ul>
        </div>

        <div class="px-3 py-3 mb-2 border-top d-flex justify-content-between">
            <span class="opacity-60 fs-15">{{translate('Subtotal')}}</span>
            <span class="fw-600 fs-17">{{ single_price($total) }}</span>
        </div>

        {{-- <div class="row align-items-center mx-0 pb-4">
            <div class="col-md-6 text-center text-md-left order-1 order-md-0">
                <a href="{{ route('home') }}" class="btn btn-link pl-0">
                    <i class="las la-arrow-left"></i>
                    {{ translate('Return to shop')}}
                </a>
            </div>
            <div class="col-md-6 text-center text-md-right">
                @if(Auth::check())

                    <a href="javascript:;" class="btn btn-primary fw-600" onclick="checkminorderamount('{{ route('checkout.shipping_info') }}')">
                        {{ translate('Continue to Shipping')}}
                    </a>
                @else
                    <button class="btn btn-primary fw-600" onclick="showCheckoutModal()">{{ translate('Continue to Shipping')}}</button>
                @endif
            </div>
        </div> --}}
    </div>
@else
    <div class="shadow-sm bg-white p-4 rounded">
        <div class="text-center p-3">
            <i class="las la-frown la-3x opacity-60 mb-3"></i>
            <h3 class="h4 fw-700">{{translate('Your Cart is empty')}}</h3>
        </div>
    </div>
@endif

@section('modal')
    <div class="modal fade" id="login-modal">
        <div class="modal-dialog modal-dialog-zoom">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-600">{{ translate('Login')}}</h6>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-3">
                        <form class="form-default" role="form" action="{{ route('cart.login.submit') }}" method="POST">
                            @csrf
                            @if (addon_is_activated('otp_system') && env("DEMO_MODE") != "On")
                                <div class="form-group phone-form-group mb-1">
                                    <input type="tel" id="phone-code" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" value="{{ old('phone') }}" placeholder="" name="phone" autocomplete="off">
                                </div>

                                <input type="hidden" name="country_code" value="">

                                <div class="form-group email-form-group mb-1 d-none">
                                    <input type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email" id="email" autocomplete="off">
                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group text-right">
                                    <button class="btn btn-link p-0 opacity-50 text-reset" type="button" onclick="toggleEmailPhone(this)">{{ translate('Use Email Instead') }}</button>
                                </div>
                            @else
                                <div class="form-group">
                                    <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email" id="email" autocomplete="off">
                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div class="form-group">
                                <input type="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="{{ translate('Password')}}" name="password" id="password">
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span class=opacity-60>{{  translate('Remember Me') }}</span>
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                                <div class="col-6 text-right">
                                    <a href="{{ route('password.request') }}" class="text-reset opacity-60 fs-14">{{ translate('Forgot password?')}}</a>
                                </div>
                            </div>

                            <div class="mb-5">
                                <button type="submit" class="btn btn-primary btn-block fw-600">{{  translate('Login') }}</button>
                            </div>
                        </form>

                    </div>
                    <div class="text-center mb-3">
                        <p class="text-muted mb-0">{{ translate('Dont have an account?')}}</p>
                        <a href="{{ route('user.registration') }}">{{ translate('Register Now')}}</a>
                    </div>
                    @if(get_setting('google_login') == 1 || get_setting('facebook_login') == 1 || get_setting('twitter_login') == 1)
                        <div class="separator mb-3">
                            <span class="bg-white px-3 opacity-60">{{ translate('Or Login With')}}</span>
                        </div>
                        <ul class="list-inline social colored text-center mb-3">
                            @if (get_setting('facebook_login') == 1)
                                <li class="list-inline-item">
                                    <a href="{{ route('social.login', ['provider' => 'facebook']) }}" class="facebook">
                                        <i class="lab la-facebook-f"></i>
                                    </a>
                                </li>
                            @endif
                            @if(get_setting('google_login') == 1)
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
        function removeFromCartView(e, key){
            e.preventDefault();
            removeFromCart(key);
        }

        function updateQuantity(key, element){
            $.post('{{ route('cart.updateQuantity') }}', {
                _token   :  AIZ.data.csrf,
                id       :  key,
                quantity :  element.value
            }, function(data){
                updateNavCart(data.nav_cart_view,data.cart_count);
                $('#cart-summary').html(data.cart_view);
            });
        }

        function showCheckoutModal(){
            $('#login-modal').modal();
        }

        function checkminorderamount(url){
            $.post('{{ route('cart.minordercheck') }}', {
                _token   :  AIZ.data.csrf
            }, function(data){
                if(data.error == 1){
                    //$('.error_message').show().html(data.error_message);
                    AIZ.plugins.notify('danger', data.error_message);
                }else{
                    location.href = url;
                }
                console.log(data);
            });
        }

        // Country Code
        var isPhoneShown = true,
            countryData = window.intlTelInputGlobals.getCountryData(),
            input = document.querySelector("#phone-code");

        for (var i = 0; i < countryData.length; i++) {
            var country = countryData[i];
            if(country.iso2 == 'bd'){
                country.dialCode = '88';
            }
        }

        var iti = intlTelInput(input, {
            separateDialCode: true,
            utilsScript: "{{ static_asset('assets/js/intlTelutils.js') }}?1590403638580",
            onlyCountries: @php echo json_encode(\App\Models\Country::where('status', 1)->pluck('code')->toArray()) @endphp,
            customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
                if(selectedCountryData.iso2 == 'bd'){
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

        function toggleEmailPhone(el){
            if(isPhoneShown){
                $('.phone-form-group').addClass('d-none');
                $('.email-form-group').removeClass('d-none');
                $('input[name=phone]').val(null);
                isPhoneShown = false;
                $(el).html('{{ translate('Use Phone Instead') }}');
            }
            else{
                $('.phone-form-group').removeClass('d-none');
                $('.email-form-group').addClass('d-none');
                $('input[name=email]').val(null);
                isPhoneShown = true;
                $(el).html('{{ translate('Use Email Instead') }}');
            }
        }
    </script>
@endsection
