@extends(config('app.theme').'frontend.layouts.app')

@section('content')
<section class="pt-5 mb-4">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="row aiz-steps arrow-divider">
                    <div class="col done">
                        <div class="text-center text-success">
                            <i class="la-3x mb-2 las la-shopping-cart"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block ">{{ ('1. My Cart')}}</h3>
                        </div>
                    </div>
                    <div class="col active">
                        <div class="text-center text-primary">
                            <i class="la-3x mb-2 las la-map"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block ">{{ ('2. Shipping info')}}</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="text-center">
                            <i class="la-3x mb-2 opacity-50 las la-truck"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50 ">{{ ('3. Delivery info')}}</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="text-center">
                            <i class="la-3x mb-2 opacity-50 las la-credit-card"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50 ">{{ ('4. Payment')}}</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="text-center">
                            <i class="la-3x mb-2 opacity-50 las la-check-circle"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50 ">{{ ('5. Confirmation')}}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@php
    $userField = auth()->check() ? 'user_id' : 'temp_user_id';
    $user_addresses = \App\Models\Address::with('user', 'area', 'city', 'country', 'state')
        ->where($userField, auth()->check() ? auth()->id() : session()->get('temp_user_id'))
        ->orderBy('set_default', 'desc')
        ->get();
@endphp
<section class="mb-4 gry-bg">
    <div class="container">
        <div class="row cols-xs-space cols-sm-space cols-md-space">
            <div class="col-xxl-8 col-xl-10 mx-auto">
                <form class="form-default" data-toggle="validator" action="{{ route('checkout.store_shipping_infostore') }}" role="form" method="POST">
                    @csrf
                    @if(Auth::check() || get_setting('guest_order_activation') == 1)
                        <div class="shadow-sm bg-white p-4 rounded mb-4">
                            <div class="row gutters-5">
                                @foreach ($user_addresses as $key => $address)
                                    <div class="col-md-6 mb-3">
                                        <label class="aiz-megabox d-block bg-white mb-0">
                                            <input type="radio" name="address_id" value="{{ $address->id }}"
                                            @if ($address->set_default || $loop->first)
                                                checked
                                            @endif required>
                                            <span class="d-flex p-3 aiz-megabox-elem">
                                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                <span class="flex-grow-1 pl-3 text-left">
                                                    <div>
                                                        <span class="opacity-60">{{ ('Address') }}:</span>
                                                        <span class="fw-600 ml-2">{{ $address->address }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ ('Postal Code') }}:</span>
                                                        <span class="fw-600 ml-2">{{ $address->postal_code }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ ('Area') }}:</span>
                                                        <span class="fw-600 ml-2">{{ optional($address->area)->name }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ ('City') }}:</span>
                                                        <span class="fw-600 ml-2">{{ optional($address->city)->name }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ ('State') }}:</span>
                                                        <span class="fw-600 ml-2">{{ optional($address->state)->name }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ ('Country') }}:</span>
                                                        <span class="fw-600 ml-2">{{ optional($address->country)->name }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ ('Phone') }}:</span>
                                                        <span class="fw-600 ml-2">{{ $address->phone }}</span>
                                                    </div>
                                                </span>
                                            </span>
                                        </label>
                                        <div class="dropdown position-absolute right-0 top-0">
                                            <button class="btn bg-gray px-2" type="button" data-toggle="dropdown">
                                                <i class="la la-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item" onclick="edit_address('{{$address->id}}')">
                                                    {{ ('Edit') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <input type="hidden" name="checkout_type" value="logged">
                                <div class="col-md-6 mx-auto mb-3" >
                                    <div class="border p-3 rounded mb-3 c-pointer text-center bg-white h-100 d-flex flex-column justify-content-center" onclick="add_new_address()">
                                        <i class="las la-plus la-2x mb-3"></i>
                                        <div class="alpha-7">{{ ('Add New Address') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row align-items-center">
                        <div class="col-md-6 text-center text-md-left order-1 order-md-0">
                            <a href="{{ route('home') }}" class="btn btn-link">
                                <i class="las la-arrow-left"></i>
                                {{ ('Return to shop')}}
                            </a>
                        </div>
                        <div class="col-md-6 text-center text-md-right">
                            <button type="submit" class="btn btn-primary fw-600">{{ ('Continue to Delivery Info')}}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('modal')
    @include(config('app.theme').'frontend.partials.address_modal')
@endsection

{{-- @dd($carts); --}}
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
@endphp

@if (get_setting('google_tagmanager'))
    <script type="text/javascript">
        dataLayer.push({ ecommerce: null });
        dataLayer.push({
            event    : "begin_checkout",
            ecommerce: {
                currency: "{{ data_get(get_system_default_currency(),'code','BDT') }}",
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
