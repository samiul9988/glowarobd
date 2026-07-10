<div class="container">
    @if( $carts && count($carts) > 0 )
        <div class="breadcump col-xxl-7 col-xl-8 col-12  mx-auto pl-1">
            <p class="m-0 fs-16 fw-400 pb-2 opacity-80">Home <span><i class="fas fa-angle-right"></i></span> Shopping Cart</p>
            <h3 class="fs-24 fw-600 pb-3 pt-1 text-uppercase">Your Cart</h3>
        </div>
        <div class="row">
            <div class="col-xxl-7 col-xl-8  mx-auto col-12">
                <div class=" p-3 p-lg-0 rounded text-left">

                    <div class="mb-4">
                        {{--
                        <div class="row d-none d-lg-flex border-bottom mb-3 p-3 bg-primary text-white mx-0">
                            <div class="col-md-5 fw-600">{{ ('Product')}}</div>
                            <div class="col fw-600">{{ ('Price')}}</div>
                            <div class="col fw-600">{{ ('Tax')}}</div>
                            <div class="col fw-600">{{ ('Quantity')}}</div>
                            <div class="col fw-600">{{ ('Total')}}</div>
                            <div class="col-auto fw-600">{{ ('Remove')}}</div>
                        </div>
                        --}}
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

                                //     echo count($minorderamontarray);
                                // dd(max($minorderamontarray));
                                @endphp
                                <li class="list-group-item px-0 px-lg-3 ">
                                    <div class="row flex-nowrap ">
                                        <div class="col-2 col-sm-1 m-0 p-0">
                                            <span class="d-flex  align-items-center justify-content-center cartImage">
                                                <img
                                                    src="{{ uploaded_asset($product->thumbnail_img) }}"
                                                    class=" cart_product_img"
                                                    alt="{{ $product->getTranslation('name')  }}"
                                                >
                                            </span>
                                        </div>
                                        <div class="col-10 col-sm-11">
                                            <div class="d-flex  justify-content-between align-items-start">
                                                <div class="px-1">
                                                    <span class="product-name">{{ $product->getTranslation('name') ?? $product_name_with_choice }} <br> {{--{{ $message }}--}}
                                                    </span>
                                                    <!-- static  variant -->
                                                    <div class="selected-variant py-1 opacity-80 fs-16">
                                                        {{--<span>White</span>,
                                                        <span>Size: 44</span>--}}
                                                        <span>{{ $cartItem['variation'] }}</span>
                                                    </div>
                                                    <!-- static variant -->
                                                </div>
                                                @php
                                                            // dd($product, $cartItem)
                                                            $current_price = $cartItem['price'] + $cartItem['tax'];
                                                            $unit_price = $product->unit_price + $cartItem['tax'];
                                                            $saved_money = ($unit_price * $cartItem['quantity']) - ($current_price * $cartItem['quantity']);
                                                        @endphp
                                                <div class=" my-lg-0">
                                                    {{-- <span class="opacity-60 fs-12 d-block d-lg-none">{{ ('Total')}}</span> --}}
                                                    <span class="fw-600 fs-16 product-price">{{ single_price($current_price * $cartItem['quantity']) }}</span>
                                                    @if($unit_price > $current_price && $saved_money > 0)
                                                        <del class="text-danger fs-12 product-unit-price">{{ single_price($unit_price) }}</del>
                                                        <span class="text-reset badge badge-inline badge-soft-secondary product-saved-price">Saved {{ single_price($saved_money) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex  justify-content-between align-items-center pt-1 ">
                                                    <div class="">
                                                        @if($cartItem['digital'] != 1 && $product->auction_product == 0)
                                                            <div class="row no-gutters align-items-center aiz-plus-minus mr-2 ml-0">
                                                                <button class="btn col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-id="{{ $cartItem['id'] }}" data-type="minus" data-field="quantity[{{ $cartItem['id'] }}]">
                                                                <i class="fas fa-minus"></i>
                                                                </button>
                                                                <input type="number" name="quantity[{{ $cartItem['id'] }}]" class="input-number inputCart-{{ $cartItem['id'] }}" placeholder="1" value="{{ $cartItem['quantity'] }}" min="{{ $product->min_qty }}" max="{{ $max_limit }}" onchange="updateQuantity({{ $cartItem['id'] }}, this)">
                                                                <button class="btn col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-id="{{ $cartItem['id'] }}" data-type="plus" data-field="quantity[{{ $cartItem['id'] }}]">
                                                                <i class="fas fa-plus"></i>
                                                                </button>
                                                            </div>
                                                        @elseif($product->auction_product == 1)
                                                            <span class="fw-600 fs-16">1</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-start">
                                                        <a href="javascript:void(0)" onclick="removeFromCartView(event, {{ $cartItem['id'] }})" class="removeBtn">
                                                        Remove
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

                    <div class="px-3 py-3 mb-2 border-top d-flex justify-content-between border-dark">
                        <span class=" fs-18 fw-500">{{ ('Subtotal')}}</span>
                        <span class="fw-600 fs-17">{{ single_price($total) }}</span>
                    </div>

                    <div class="flex-column flex-sm-row d-flex align-items-center mx-0 pb-2 justify-content-end continue-shopping pt-2 pt-sm-5">
                        <div class=" text-center text-md-left order-1 order-md-0">
                            <a href="{{ route('home') }}" class="btn btn-link fs-16 fw-400 pl-0 d-flex align-items-center justify-content-center">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.25 12H3.75" stroke="#1F2029" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10.5 5.25L3.75 12L10.5 18.75" stroke="#1F2029" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                {{ ('Return to shop')}}
                            </a>
                        </div>
                        <div class=" text-center text-md-right continue-shopping-btn">
                            @if(Auth::check())
                                <a href="javascript:;" class="btn fs-16 fw-500" onclick="checkminorderamount('{{ route('checkout.shipping_info') }}')">
                                    {{ ('Continue to Shipping')}}
                                </a>
                            @else
                                <button class="btn fs-16 fw-500" onclick="showCheckoutModal()">{{ ('Continue to Shipping')}}</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="shadow-sm bg-white p-4 rounded">
                    <div class="text-center p-3">
                        <i class="las la-frown la-3x opacity-60 mb-3"></i>
                        <h3 class="h4 fw-700">{{ ('Your Cart is empty')}}</h3>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script type="text/javascript">
    AIZ.extra.plusMinus();
</script>
