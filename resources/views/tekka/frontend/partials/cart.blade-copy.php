@php
if(auth()->user() != null) {
    $user_id = Auth::user()->id;
    $cart = auth()->user()->carts()->with('product')->get();
} else {
    $temp_user_id = Session()->get('temp_user_id');
    if($temp_user_id) {
        $cart = \App\Models\Cart::where('temp_user_id', $temp_user_id)->get();
    }
}

@endphp
<a href="javascript:void(0)" class="d-flex align-items-center text-reset h-100  flex-column" data-toggle="dropdown" data-display="static">
    <!-- <i class="la la-cart-plus la-2x"></i> -->
     <!-- <span class="la-cart-plus">
        </span> -->
        <svg width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9.75 25.375C10.7165 25.375 11.5 24.5915 11.5 23.625C11.5 22.6585 10.7165 21.875 9.75 21.875C8.7835 21.875 8 22.6585 8 23.625C8 24.5915 8.7835 25.375 9.75 25.375Z" fill="white"/>
            <path d="M21.125 25.375C22.0915 25.375 22.875 24.5915 22.875 23.625C22.875 22.6585 22.0915 21.875 21.125 21.875C20.1585 21.875 19.375 22.6585 19.375 23.625C19.375 24.5915 20.1585 25.375 21.125 25.375Z" fill="white"/>
            <path d="M5.625 7.875H25.25L22.3626 17.9808C22.2582 18.3464 22.0374 18.6681 21.7338 18.8971C21.4302 19.1261 21.0603 19.25 20.68 19.25H10.195C9.81473 19.25 9.44479 19.1261 9.14118 18.8971C8.83758 18.6681 8.61683 18.3464 8.51236 17.9808L4.55632 4.13462C4.50408 3.95179 4.39371 3.79095 4.24191 3.67645C4.0901 3.56194 3.90513 3.5 3.71499 3.5H1.875" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
         </svg>

    <span class="flex-grow-1 ml-1">
        @if(isset($cart) && count($cart) > 0)
            <span class="badge badge-secondary badge-inline badge-pill cart-count">
                {{ count($cart)}}
            </span>
        @else
            <span class="badge badge-secondary badge-inline badge-pill cart-count">0</span>
        @endif
        <!-- <span class="nav-box-text d-none d-xl-block">{{ ('Cart')}}</span> -->
    </span>
</a>
<div class="dropdown-menu dropdown-menu-right dropdown-menu-lg p-0 stop-propagation">

    @if(isset($cart) && count($cart) > 0)
        <div class="p-3 fs-15 fw-600 p-3 border-bottom">
            {{ ('Cart Items')}}
        </div>
        <ul class="h-250px overflow-auto c-scrollbar-light list-group list-group-flush">
            @php
                $total = 0;
            @endphp
            @foreach($cart as $key => $cartItem)
                @php
                    $product = $cartItem->product;
                    $product_stock = collect($product->stocks)->where('variant', $cartItem->variation)->first();
                    $cartItem['price'] = getMinimumPriceByVariant($product, $product_stock, 'web', $cartItem['quantity'], $currentlyAuthenticatedUser);
                    $total = $total + $cartItem['price'] * $cartItem['quantity'];

                    $current_price = $cartItem['price'] + $cartItem['tax'];
                    $unit_price = $product->unit_price + $cartItem['tax'];
                    $saved_money = ($unit_price * $cartItem['quantity']) - ($current_price * $cartItem['quantity']);
                @endphp
                @if ($product != null)
                    <li class="list-group-item">
                        <span class="d-flex align-items-center">
                            <a href="{{ route('product', $product->slug) }}" class="text-reset d-flex align-items-center flex-grow-1">
                                <img
                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                    data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                                    class="img-fit lazyload size-60px rounded"
                                    alt="{{  $product->getTranslation('name')  }}"
                                >
                                <span class="minw-0 pl-2 flex-grow-1">
                                    <span class="fw-600 mb-1 text-truncate-2">
                                        {{ $product->getTranslation('name') }}
                                    </span>
                                    @if(!empty($product_stock->variant))
                                    <span class="text-secondary">{{ $product_stock->variant }}</span>
                                    @endif
                                    <span class="">{{ $cartItem['quantity'] }}x</span>
                                    <span class="">{{ single_price($cartItem['price']) }}</span>
                                </span>
                            </a>
                            <span class="">
                                <button onclick="removeFromCart({{ $cartItem['id'] }})" class="btn btn-sm btn-icon stop-propagation">
                                    <i class="la la-close"></i>
                                </button>
                            </span>
                        </span>
                    </li>

                    @if(env('GOOGLE_TAG_MANAGE') == 'ON')
                    <script>
                        dataLayer.push({ ecommerce: null });
                        dataLayer.push({
                            event    : "add_to_cart",
                            ecommerce: {
                                items: [{
                                    item_name     : "{{$product->name}}",
                                    item_id       : "{{$product->id}}",
                                    price         : "{{ single_price($cartItem['price']) }}",
                                    item_brand    : "{{$product->brand->name ?? ''}}",
                                    item_category : "{{$product->category->name ?? ''}}",
                                    item_list_name: "",
                                    item_list_id  : "",
                                    index         : 0,
                                    quantity      : "{{ $cartItem['quantity'] }}",

                                }]
                            }
                        });
                        </script>
                    @endif


                @endif
            @endforeach
        </ul>
        <div class="px-3 py-2 fs-15 border-top d-flex justify-content-between">
            <span class="opacity-60">{{ ('Subtotal')}}</span>
            <span class="fw-600">{{ single_price($total) }}</span>
        </div>
        <div class="px-3 py-2 text-center border-top">
            <ul class="list-inline mb-0">
                <li class="list-inline-item">
                    <a href="{{ route('cart') }}" class="btn btn-soft-primary btn-sm">
                        {{ ('View cart')}}
                    </a>
                </li>
                @if (Auth::check())
                <li class="list-inline-item">
                    <a href="{{ route('checkout.shipping_info') }}" class="btn btn-black-white btn-sm rounded-md">
                        {{ ('Checkout')}}
                    </a>
                </li>
                @endif
            </ul>
        </div>
    @else
        <div class="text-center p-3">
            <i class="las la-frown la-3x opacity-60 mb-3"></i>
            <h3 class="h6 fw-700">{{ ('Your Cart is empty')}}</h3>
        </div>
    @endif

</div>


