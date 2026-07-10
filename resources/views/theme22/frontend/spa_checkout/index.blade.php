@extends(config('app.theme').'frontend.layouts.app')

@section('content')
@php
    $countriesfilePath = storage_path('app/public/countries/countries.json');
    if (file_exists($countriesfilePath)) {
        $jsonData = file_get_contents($countriesfilePath);
        $countries = collect(json_decode($jsonData, true));
    } else {
        $countries = collect(\App\Models\Country::all());
    }
    $country_id = $countries->where("status", 1)->sortByDesc('created_at')->first()->id ?? "";
    $user_addresses = collect(\App\Models\Address::with('user', 'area', 'city', 'country', 'state')->where("user_id", auth()->user()->id)->orderBy('set_default', 'desc')->get());
    $defaultAddress = $user_addresses->where('set_default', 1)->first() ?? $user_addresses->sortBy('id')->first();

    $address_id = optional($defaultAddress)->id ?? 0;
    $address_type = optional($defaultAddress)->address_type ?? '';
@endphp
<script defer src="https://unpkg.com/alpinejs@3.2.4/dist/cdn.min.js"></script>
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
<section class="my-4" x-data="app({{ json_encode($defaultAddress) }})" x-cloak>
    <div class="container text-left">
        <div class="row gutters-5">
            <div class="col-12">
                <div class="mb-2" id="cart-summary">
                    @include(config('app.theme').'frontend.spa_checkout.view_cart')
                </div>
                @if($carts)
                {{--<div class="mb-2" id="cart_summary">
                    @include(config('app.theme').'frontend.spa_checkout.cart_summary')
                </div>
                <div class="mb-2" id="output_summary">
                    <span x-text="address_id"></span>
                </div>--}}
                <div class="mb-2" id="shipping_info">
                    <div class="card mb-0 shadow-sm border-0 rounded">
                        <div class="card-header p-3">
                            <h5 class="fs-16 fw-600 mb-0">{{ ('Choose Shipping Address') }}</h5>
                        </div>
                        <div class="card-body">
                            @include(config('app.theme').'frontend.spa_checkout.shipping_info', ['user_addresses' => $user_addresses])
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
        </div>
    </div>
    <!-- Loading indicator -->
    <div class="loading-overlay2" x-show="isLoading">
        <span class="fas fa-spinner fa-3x fa-spin"></span>
    </div>
</section>

<!-- Address Modals -->
@include(config('app.theme').'frontend.spa_checkout.address_modal', ['countries' => $countries])

@if (get_setting('google_tagmanager'))
    <script type="text/javascript">
        dataLayer.push({ ecommerce: null });
        dataLayer.push({
            event    : "begin_checkout",
            ecommerce: {
                items: [@foreach ($carts as $cart){
                    item_name     : "{{@$cart->product->name}}",
                    item_id       : "{{@$cart->product->id}}",
                    price         : "{{@$cart->price }}",
                    item_brand    : "{{@$cart->product->brand->name ?? ''}}",
                    item_category : "{{@$cart->product->category->name ?? ''}}",
                    item_list_name: "",
                    item_list_id  : "",
                    index         : 0,
                    quantity      : "{{@$cart->quantity ?? '' }}",
                },@endforeach]
            }
        });
    </script>
@endif
<script type="text/javascript">
    window.onload = function () {
        saveAddress();
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
            couponApplied: false,

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
                    }
                });
                this.isLoading = false;
            },

            saveDeliveryInfo(el) {
                this.isLoading = true;

                var input_name = $(el).attr('name');
                var input_value = $(el).val();

                var formData = $(this.deliveryForm).find(`input[name!=${input_name}]`).serialize();

                formData += "&address_id=" + encodeURIComponent(this.address_id);
                formData += `&${input_name}=` + encodeURIComponent(input_value);

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
                    }
                });
                this.isLoading = false;
            },

            add_new_address(){
                $('#new-address-modal').modal('show');
                this.address_type = 'Home';
                this.get_states(this.country_id);
            },

            edit_address(address) {
                this.isEdit = true;
                var url = '{{ route("addresses.edit", ":id") }}';
                url = url.replace(':id', address);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: url + '?isFromSpa=true',
                    type: 'GET',
                    success: function (response) {
                        this.address_type = response.data.address_data.address_type;
                        this.address_id = response.data.address_data.id;
                        this.country_id = response.data.address_data.country_id;
                        this.state_id = response.data.address_data.state_id;
                        this.city_id = response.data.address_data.city;
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
                    }
                });
                this.handleAIZPlugin();
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

            applyCoupon(e, coupon_code = '') {
                let $this = this;
                if(coupon_code.length > 0){
                    $this.coupon_code = coupon_code;
                }
                if($this.coupon_code == ''){
                    AIZ.plugins.notify('danger', 'Please enter coupon code');
                    return;
                }
                if($this.couponApplied){
                    AIZ.plugins.notify('danger', 'Coupon already applied');
                    return;
                }
                $('#coupon-apply').prop('disabled', true);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: "{{route('checkout.apply_coupon_code')}}?code=" + $this.coupon_code,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data, textStatus, jqXHR) {
                        AIZ.plugins.notify(data.response_message.response, data.response_message.message);
                        if (data.response_message.response == 'danger') {
                            $('#coupon_alert_invalid').show();
                        } else {
                            $this.couponApplied = true;
                        }
                        $("#cart-summary").html(data.html);
                    },
                    error: function(xhr, status, error) {
                        AIZ.plugins.notify(xhr.responseJSON.type || 'danger', xhr.responseJSON.message || 'Something went wrong!');
                    }
                });
                $('#coupon-apply').prop('disabled', false);
            },

            removeCoupon(e){
                let $this = this;
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: "{{route('checkout.remove_coupon_code')}}?code=" + $this.coupon_code,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data, textStatus, jqXHR) {
                        $("#cart-summary").html(data);
                        $this.couponApplied = false;
                    }
                })
            },

            applyMaxRewardPoints(e){
                $('#reawrd_point').val($(e).val());
                this.aplicable_reward_point = $(e).val();
                console.log(this.aplicable_reward_point);
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
                    console.log($(target2).find('input[type="radio"]').length)
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
                    if ($('#agree_checkbox').is(":checked")) {
                        $(el).prop('disabled', false);
                        var minordererror = `{{ @$minordercheck['error'] }}`;
                        if (minordererror == 1) {
                            AIZ.plugins.notify('danger', '{{ @$minordercheck["error_message"] }}');
                            $(el).prop('disabled', false);
                        } else {
                            $('#checkout-form').submit();
                        }
                    } else {
                        AIZ.plugins.notify('danger', '{{ ("You need to agree with our policies") }}');
                        $(el).prop('disabled', false);
                    }
                }
            },

            toggleManualPaymentData(id) {
                if (typeof id != 'undefined') {
                    $('#manual_payment_description').parent().removeClass('d-none');
                    $('#manual_payment_description').html($('#manual_payment_info_' + id).html());
                }
            },

            handleOnlinePayment(el){
                $('#manual_payment_description').parent().addClass('d-none');
                toggleManualPaymentData($('input[name=payment_option]:checked').data('id'));
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
                await $.post('{{ route("cart.updateQuantity") }}', {
                    _token   :  AIZ.data.csrf,
                    id       :  key,
                    quantity :  value
                }, function(data){
                    updateNavCart(data.nav_cart_view,data.cart_count);
                    $('#cart-summary').html(data.cart_view);
                    // AIZ.plugins.notify('success', '{{ ("Cart has been updated") }}');
                });
                await this.saveAddress();
            }
        }
    }
</script>
@endsection
