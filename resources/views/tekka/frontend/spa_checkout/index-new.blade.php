@extends(config('app.theme').'frontend.layouts.app')

@section('meta')
<x-seo />
@endsection

@section('content')
@php
    $countries = Cache::remember('frontend_countries', now()->addDay(), function() {
        return \App\Models\Country::latest()->where('status', 1)->get();
    });
    $country_id = $countries->first()?->id ?? "";
    $userField = auth()->check() ? 'user_id' : 'temp_user_id';
    $hasDefaultAddress = \App\Models\Address::where($userField, auth()->check() ? auth()->id() : session()->get('temp_user_id'))
        ->where('set_default', 1)
        ->exists();
    $user_addresses = \App\Models\Address::with('user', 'area', 'city', 'country', 'state')
        ->where($userField, auth()->check() ? auth()->id() : session()->get('temp_user_id'));
    if ($hasDefaultAddress) {
        $user_addresses = $user_addresses->orderBy('set_default', 'desc')->get();
    } else {
        $user_addresses = $user_addresses->orderBy('created_at', 'desc')->get();
    }
    $cartAddress = $user_addresses->where('id', $carts->first()->address_id)->first();
    $defaultAddress = $hasDefaultAddress ? $user_addresses->where('set_default', 1)->first() : null;
    // $address_id = $hasDefaultAddress ? ($defaultAddress->id ?? 0) : 0;
    $address_id = $cartAddress->id ?? $defaultAddress->id ?? 0;
    $address_type = $hasDefaultAddress ? ($defaultAddress->address_type ?? 'Home') : 'Home';
@endphp
<style>
    .loading-overlay2 {
    display: flex;
    background: rgba(255, 255, 255, 0.7);
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    top: 0;
    z-index: 9998;
    align-items: center;
    justify-content: center;
    }
</style>
<section class="checkout-page" x-data="app({{ json_encode($defaultAddress) }})" x-cloak>
    <div class="container text-left">
        <div class="row gutters-5 px-lg-8 flex-column-reverse flex-lg-row">
            <div class="delivaryaddress-wrapper col-12 col-lg-6 px-xl-3 py-3">
                @if($carts)
                    <div class="mb-2" id="shipping_info">
                        <div class="card mb-0 shadow-none border-0 rounded">
                            <div class="card-body delivery-address px-0 py-2">
                                @include(config('app.theme').'frontend.spa_checkout.shipping_info', [
                                    'user_addresses' => $user_addresses,
                                    'shipping_charge' => $shipping_charge,
                                ])
                            </div>
                        </div>
                    </div>
                    <div class="mb-1" id="delivery_info_spa">
                        @include(config('app.theme').'frontend.spa_checkout.delivery_info', [
                            'user_addresses' => $user_addresses,
                            'shipping_charge' => $shipping_charge
                        ])
                    </div>

                    <div class="mb-2" id="payment_info">
                        @include(config('app.theme').'frontend.spa_checkout.payment_select')
                    </div>
                @endif
            </div>
            <div class="col-12 col-lg-6 px-xl-3 py-4 checkout-right-side">
                <div class="mb-2" id="cart-summary">
                    @include(config('app.theme').'frontend.spa_checkout.view_cart')
                </div>
            </div>
        </div>
    </div>
    <!-- Loading indicator -->
    <div class="loading-overlay2" x-show="isLoading">
        <span class="fas fa-spinner fa-3x fa-spin"></span>
    </div>
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="otp-verification-modal" tabindex="-1" role="dialog" aria-labelledby="otpVerificationModalLabel" aria-hidden="true" x-cloak>
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Verify Your OTP</h5>
                    <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-1">
                        <input type="hidden" name="guest_phone" value="">
                        <input type="text" id="otp_code" class="form-control" placeholder="Enter OTP" required x-model="otpCode">
                    </div>
                    <div class="text-right">
                        <button
                            type="button"
                            id="resend_otp_button"
                            class="btn btn-link p-0"
                            x-show="timeover"
                            @click="resendOTP"
                            x-cloak
                        >
                            Resend OTP
                        </button>

                        <span x-show="!timeover" x-cloak>
                            Resend OTP in <span x-text="timerFormatted"></span> seconds
                        </span>
                    </div>
                    <div class="form-group text-center">
                        <button type="button" id="verify_otp_button" class="btn btn-dark" @click="verifyOTP()">Verify</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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

<!-- Address Modals -->
@include(config('app.theme').'frontend.spa_checkout.address_modal', ['countries' => $countries])
@php

    try{
        // Calculate total price of the cart
        $cartArray = is_array($carts) ? $carts : $carts->toArray();
        $cartTotal = array_sum(array_map(function($cart) {
            return $cart['price'] * $cart['quantity'];
        }, $cartArray));
    }catch(\Exception $e){
        $cartTotal = 0;
    }finally{
        $cartTotal = number_format($cartTotal, 2);
    }
    // dd($cartArray, $cartTotal);
@endphp
@if (get_setting('google_tagmanager'))
    <script type="text/javascript">
        dataLayer.push({ ecommerce: null });
        dataLayer.push({
            event    : "begin_checkout",
            ecommerce: {
                currency: "{{ data_get(get_system_default_currency(),'code','') }}",
                value: "{{ $cartTotal }}",
                items: [
                    @foreach ($carts as $cart)
                    {
                        item_name     : "{{$cart->product->name}}",
                        item_id       : "{{$cart->product->id}}",
                        price         : "{{ $cart->price }}",
                        item_brand    : "{{$cart->product->brand->name ?? ''}}",
                        item_category : "{{$cart->product->category->name ?? ''}}",
                        item_list_name: "",
                        item_list_id  : "",
                        index         : 0,
                        quantity      : "{{ $cart->quantity ?? '' }}",
                    },
                    @endforeach
                ]
            }
        });
    </script>
@endif


<script type="text/javascript">
    window.addEventListener('load', function () {
        // saveAddress();
        if ($('#checkoutModal').length) {
            $('#checkoutModal').modal('show');
        }
    });
    function gotoLoginPage() {
        window.location.href = "{{ route('user.login') }}?redirect={{ encrypt(route('checkout.shipping_info')) }}";
    }

    function saveAddress(){
        var formData = $('#delivery_info_form').serialize();

        var formdatatoarray = $('#delivery_info_form').serializeArray();
        var indexed_array = {};

        $.map(formdatatoarray, function(n, i){
            indexed_array[n['name']] = n['value'];
        });

        if(indexed_array.address_id == 0){
            $('#address_type_home').addClass('active');
            $('input[name="address_type"][value="Home"]').prop('checked', true);
            AIZ.plugins.notify('danger', 'Please add a shipping address before continue...');
            add_new_address();
        }else{
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('checkout.store_shipping_infostore')}}",
                type: 'POST',
                data: formData,
                success: function(data, textStatus, jqXHR) {
                    $("#cart-summary").html(data.html);
                    $("#delivery_info_spa").html(data.deliveryHTML);
                }
            });
        }

    }

    function add_new_address(){
        $('#new-address-modal').modal('show');

        if($('[name=country_id]').find('option').length==1){
            var country_id = $('[name=country_id]').val();
            if(country_id!=''){
                get_states(country_id);
            }else{
                alert('hello');
            }
        }
    }

    function get_states(country_id) {
        $('[name="state"]').html("");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{route('get-state')}}",
            type: 'POST',
            data: {
                country_id  : country_id
            },
            success: function (response) {
                var obj = JSON.parse(response);
                if(obj != '') {
                    $('[name="state_id"]').html(obj);
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            }
        });
    }

    function continueAsGuest() {
        $('#checkoutModal').modal('hide');
        $.get("{{ route('checkout.continue_as_guest') }}", function(data) {
            console.log(data);
        });
    }

    function app(adressdata = '') {
        return {
            isEdit: false,
            showMore: false,
            address_id: '{{$address_id}}',
            country_id: adressdata ? adressdata.country_id : '{{$country_id}}',
            state_id: '',
            city_id: '',
            address_type: adressdata ? adressdata.address_type : '{{$address_type}}',
            coupon_code: '',
            aplicable_reward_point: 0,
            deliveryForm: document.getElementById('delivery_info_form'),
            isLoading: false,
            newMobileNumber: '',
            editMobileNumber: adressdata ? adressdata.phone : '',
            otpCode: '',
            mustVerifyOTP: '{{ Auth::check() ? false : true }}',
            isLoading: false,
            timer: 59,
            timeover: false,
            interval: null,

            saveAddress(){
                this.isLoading = true;
                var formData = $(this.deliveryForm).serialize();
                formData += "&address_id=" + encodeURIComponent(this.address_id);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('checkout.store_shipping_infostore')}}",
                    type: 'POST',
                    data: formData,
                    success: function(data, textStatus, jqXHR) {
                        $("#cart-summary").html(data.html);
                        $("#delivery_info_spa").html(data.deliveryHTML);
                        AIZ.plugins.notify('success', 'Delivery charge has been updated');
                    },
                    complete: () => {
                        this.isLoading = false;
                    }
                });
            },

            saveDeliveryInfo(el) {
                var input_name = $(el).attr('name');
                var input_value = $(el).val();

                var formData = $(this.deliveryForm).find(`input[name!=${input_name}]`).serialize();

                formData += "&address_id=" + encodeURIComponent(this.address_id);
                formData += `&${input_name}=` + encodeURIComponent(input_value);

                this.isLoading = true;
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('checkout.store_delivery_info')}}",
                    type: 'POST',
                    data: formData,
                    success: function(data, textStatus, jqXHR) {
                        $("#cart-summary").html(data.html);
                        $("#delivery_info_spa").html(data.deliveryHTML);
                        AIZ.plugins.notify('success', 'Delivery charge has been updated');
                    },
                    complete: () => {
                        this.isLoading = false;
                    }
                });
            },

            add_new_address(){
                $('#new-address-modal').modal('show');
                this.address_type = 'Home';
                this.get_states(this.country_id);
            },

            edit_address(address) {
                this.isEdit = true;
                var url = '{{ route("addresses.edit", ":id") }}'.replace(':id', address);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: url + '?isFromSpa=true',
                    type: 'GET',
                    success: function (response) {
                        if (response.success) {
                            this.address_type = response.data.address_data.address_type;
                            this.address_id = response.data.address_data.id;
                            this.country_id = response.data.address_data.country_id;
                            this.state_id = response.data.address_data.state_id;
                            this.city_id = response.data.address_data.city_id;
                            this.editMobileNumber = response.data.address_data.phone;

                            $('#edit_modal_body').html(response.html);
                            $('#editMobileNumber').val(response.data.address_data.phone);
                            $('#edit-address-modal').modal('show');

                            @if(get_setting('google_map') == 1)
                                var lat     = -33.8688;
                                var long    = 151.2195;

                                if(response.data.address_data.latitude && response.data.address_data.longitude) {
                                    lat     = response.data.address_data.latitude;
                                    long    = response.data.address_data.longitude;
                                }
                                initialize(lat, long, 'edit_');
                            @endif
                        } else {
                            AIZ.plugins.notify('danger', response.message ?? 'Something went wrong');
                        }
                    }
                });
                this.handleAIZPlugin();
            },

            remove_address(address) {
                var url = '{{ route("addresses.destroy", ":id") }}'.replace(':id', address);
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (response) {
                        if (response.success) {
                            window.location.reload();
                        } else {
                            AIZ.plugins.notify('danger', response.message ?? 'Something went wrong');
                        }
                    }
                });
            },

            handleCountryChange(e){
                var country_id = $(e).val();
                this.country_id = country_id;
                this.get_states(country_id);
            },

            handleStateChange(e) {
                var state_id = $(e).val();
                this.state_id = $(e).val();
                this.get_city(state_id);
            },

            handleCityChange(e){
                var city_id = $(e).val();
                this.city_id = city_id;
                this.get_area(city_id);
            },

            get_states(country_id) {
                $('[name="state"]').html("");
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('get-state')}}",
                    type: 'POST',
                    data: {
                        country_id  : country_id
                    },
                    success: function (response) {
                        var obj = JSON.parse(response);
                        if(obj != '') {
                            $('[name="state_id"]').html(obj);
                            AIZ.plugins.bootstrapSelect('refresh');
                        }
                    }
                });
            },

            get_city(state_id) {
                $('[name="city_id"]').html("");
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('get-city')}}",
                    type: 'POST',
                    data: {
                        state_id: this.state_id
                    },
                    success: function (response) {
                        var obj = JSON.parse(response);
                        if(obj != '') {
                            $('[name="city_id"]').html(obj);
                            AIZ.plugins.bootstrapSelect('refresh');
                        }
                    }
                });
            },

            get_area(city_id) {
                $('[name="area_id"]').html("");
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('get-area')}}",
                    type: 'POST',
                    data: {
                        city_id: this.city_id
                    },
                    success: function (response) {
                        var obj = JSON.parse(response);
                        if(obj != '') {
                            $('[name="area_id"]').html(obj);
                            AIZ.plugins.bootstrapSelect('refresh');
                        }
                    }
                });
            },

            handleAIZPlugin(){
                AIZ.plugins.bootstrapSelect('refresh');
            },

            applyCoupon(e) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: "{{route('checkout.apply_coupon_code')}}?code=" + this.coupon_code,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data, textStatus, jqXHR) {
                        AIZ.plugins.notify(data.response_message.response, data.response_message.message);
                        if (data.response_message.response == 'danger') {
                            $('#coupon_alert_invalid').show();
                        }
                        $("#cart-summary").html(data.html);
                    }
                })
            },

            removeCoupon(e){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: "{{route('checkout.remove_coupon_code')}}?code=" + this.coupon_code,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data, textStatus, jqXHR) {
                        $("#cart-summary").html(data);
                    }
                })
            },

            applyMaxRewardPoints(e){
                $('#reawrd_point').val($(e).val());
                this.aplicable_reward_point = $(e).val();
            },

            redeemRewardPoint(e){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: "{{route('checkout.apply_reward_point')}}?point="+this.aplicable_reward_point,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data, textStatus, jqXHR) {
                        if (data.success) {
                            AIZ.plugins.notify(data.response_message.response, data.response_message.message);
                            $("#cart-summary").html(data.html);
                        } else {
                            AIZ.plugins.notify(data.response_message.response, data.response_message.message);
                        }
                    }
                })
            },

            removeRewardPoint(e){
                this.aplicable_reward_point = 0;
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: "{{route('checkout.remove_reward_point')}}",
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data, textStatus, jqXHR) {
                        AIZ.plugins.notify(data.response_message.response, data.response_message.message);
                        $("#cart-summary").html(data.html);
                    }
                })
            },

            show_pickup_point(el) {
                var value = $(el).val();
                var target = $(el).data('target');
                var target2 = $(el).data('target2');

                // console.log(value);

                if(value == 'home_delivery'){
                    if(!$(target).hasClass('d-none')){
                        $(target).addClass('d-none');
                        $(target).find('select').removeAttr('required');
                    }

                    if($(target2).hasClass('d-none')){
                        $(target2).removeClass('d-none');
                        $(target2).find('input[type="radio"]').attr('required','required');
                    }

                }else{
                    $(target).removeClass('d-none');
                    $(target2).addClass('d-none');

                    $(target).find('select').attr('required', 'required');
                    $(target2).find('input[type="radio"]').removeAttr('required');
                }
            },

            submitOrder(el){
                $(el).prop('disabled', true);
                var formdatatoarray = $('#checkout-form').serializeArray();
                var indexed_array = {};

                $.map(formdatatoarray, function(n, i){
                    indexed_array[n['name']] = n['value'];
                });

                if(indexed_array.address_id == 0){
                    $('#address_type_home').addClass('active');
                    $('input[name="address_type"][value="Home"]').prop('checked', true);
                    AIZ.plugins.notify('danger', 'Please add a shipping address to complete order...');
                    this.add_new_address();
                    $(el).prop('disabled', false);
                }else{
                    if ($('input[name="agree"]').is(":checked") || $('input[name="agree_sm"]').is(":checked")) {
                        $(el).prop('disabled', false);
                        var minordererror = `{{ @$minordercheck['error'] }}`;
                        if (minordererror == 1) {
                            AIZ.plugins.notify('danger', '{{ @$minordercheck["error_message"] }}');
                            $(el).prop('disabled', false);
                        } else {
                            if (!this.mustVerifyOTP) {
                                this.isLoading = true;
                                $('#checkout-form').submit();
                            } else {
                                let self = this;
                                self.isLoading = true;
                                $.ajax({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    method: "POST",
                                    url: "{{ route('guest.validate_data') }}",
                                    data: {
                                        address_id: indexed_array.address_id
                                    },
                                    success: function(data) {
                                        if (data.success) {
                                            AIZ.plugins.notify('success', data.message);
                                            $('input[name="guest_phone"]').val(data.phone);
                                            self.startTimer();
                                            self.isLoading = false;
                                            $('#otp-verification-modal').modal('show');
                                        } else {
                                            AIZ.plugins.notify('danger', data.message);
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        let errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred. Please try again.';
                                        AIZ.plugins.notify('danger', errorMessage);
                                    }
                                });
                            }
                        }
                    } else {
                        AIZ.plugins.notify('danger', '{{ ("You need to agree with our policies") }}');
                        $(el).prop('disabled', false);
                    }
                }
            },

            get timerFormatted() {
                return this.timer < 10 ? '0' + this.timer : this.timer;
            },

            startTimer() {
                this.timer = 59;
                this.timeover = false;

                if (this.interval) {
                    clearInterval(this.interval);
                }

                this.interval = setInterval(() => {
                    if (this.timer > 1) {
                        this.timer--;
                    } else {
                        this.timeover = true;
                        clearInterval(this.interval);
                    }
                }, 1000);
            },

            verifyOTP() {
                let otp = this.otpCode.trim();
                if (otp === '') {
                    AIZ.plugins.notify('danger', 'Please enter the OTP code.');
                    return;
                } else if (otp.length < 4) {
                    AIZ.plugins.notify('danger', 'Enter a valid OTP code.');
                    return;
                }
                let self = this;
                $('#verify_otp_button').prop('disabled', true).text('Verifying...');
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: "{{ route('guest.verify_phone') }}",
                    data: {
                        verification_code: otp,
                        phone: $('input[name="guest_phone"]').val()
                    },
                    success: function(data) {
                        if (data.success) {
                            $('#otp-verification-modal').modal('hide');
                            AIZ.plugins.notify('success', data.message);
                            self.isLoading = true;
                            $('#checkout-form').submit();
                        } else {
                            AIZ.plugins.notify('danger', data.message);
                        }
                        $('#verify_otp_button').prop('disabled', false).text('Verify');
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred. Please try again.';
                        AIZ.plugins.notify('danger', errorMessage);
                        $('#verify_otp_button').prop('disabled', false).text('Verify');
                    }
                });
            },

            resendOTP() {
                let self = this;
                $('#resend_otp_button').prop('disabled', true).text('Resending...');
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: "{{ route('guest.resend_code') }}",
                    data: {
                        phone: $('input[name="guest_phone"]').val()
                    },
                    success: function(data) {
                        if (data.success) {
                            AIZ.plugins.notify('success', data.message);
                            self.startTimer();
                        } else {
                            AIZ.plugins.notify('danger', data.message);
                        }
                        $('#resend_otp_button').prop('disabled', false).text('Resend OTP');
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred. Please try again.';
                        AIZ.plugins.notify('danger', errorMessage);
                        $('#resend_otp_button').prop('disabled', false).text('Resend OTP');
                    }
                });
            },

            toggleManualPaymentData(id) {
                if (typeof id != 'undefined') {
                    $('#manual_payment_description').parent().removeClass('d-none');
                    $('#manual_payment_description').html($('#manual_payment_info_' + id).html());
                }
            },

            handleOnlinePayment(el){
                $('#manual_payment_description').parent().addClass('d-none');
                this.toggleManualPaymentData($('input[name=payment_option]:checked').data('id'));
            },

            use_wallet() {
                $('input[name=payment_option]').val('wallet');
                if ($('#agree_checkbox').is(":checked")) {
                    $('#checkout-form').submit();
                } else {
                    AIZ.plugins.notify('danger', '{{ ("You need to agree with our policies") }}');
                }
            },

            async increaseQuantity(key){
                var current_val = await $("input[name='quantity["+key+"]']").val();
                await this.updateQuantity(key, parseInt(current_val) + 1);
            },

            async decreaseQuantity(key){
                var current_val = await $("input[name='quantity["+key+"]']").val();
                await this.updateQuantity(key, parseInt(current_val) - 1);
            },

            async updateQuantity(key, value){
                this.isLoading = true;
                await $.post('{{ route("cart.updateQuantity") }}', {
                    _token   :  AIZ.data.csrf,
                    id       :  key,
                    quantity :  value
                }, function(data){
                    updateNavCart(data.nav_cart_view,data.cart_count);
                    $('#cart-summary').html(data.cart_view);
                });
                await this.saveAddress();
            }
        }
    }
</script>
@endsection
