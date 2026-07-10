
@if( $carts && count($carts) > 0 )
    <div class="shadow-sm bg-white rounded text-left p-4 p-md-0">
        <div>
            <div class="row d-none d-lg-flex border-bottom mb-3 p-3 bg-primary text-white mx-0">
                <div class="col-md-4 fw-600">{{ translate('Product')}}</div>
                <div class="col fw-600">{{ translate('Price')}}</div>
                <div class="col fw-600">{{ translate('Quantity')}}</div>
                <div class="col text-center fw-600">{{ translate('Total')}}</div>
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
                        $total = $total + ($cartItem['price'] * $cartItem['quantity']) + $cartItem['tax'];
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
                                        class="img-fit size-60px rounded"
                                        alt="{{ $product->getTranslation('name')  }}"
                                    >
                                </span>
                                <span class="fs-14">
                                    {{ $product_name_with_choice }} <br> {{ $message }}
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

                            <div class="col-lg col-6 order-4 order-lg-0">
                                @if($cartItem['digital'] != 1)
                                    <div class="row no-gutters align-items-center aiz-plus-minus mr-2 ml-0">
                                        <button class="btn col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-id="{{ $cartItem['id'] }}" data-type="minus" data-field="quantity[{{ $cartItem['id'] }}]">
                                            <i class="las la-minus"></i>
                                        </button>
                                        <input type="number" name="quantity[{{ $cartItem['id'] }}]" class="col border-0 text-center flex-grow-1 fs-16 input-number inputCart-{{ $cartItem['id'] }}" placeholder="1" value="{{ $cartItem['quantity'] }}" min="{{ $product->min_qty }}" max="{{ $max_limit }}" onchange="updateQuantity({{ $cartItem['id'] }}, this)">
                                        <button class="btn col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-id="{{ $cartItem['id'] }}" data-type="plus" data-field="quantity[{{ $cartItem['id'] }}]">
                                            <i class="las la-plus"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="col-lg col-6 order-3 text-center order-lg-0 my-3 my-lg-0">
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

        <div class="px-3 py-2 mb-4 border-top d-flex justify-content-between">
            <span class="opacity-60 fs-15">{{translate('Subtotal')}}</span>
            <span class="fw-600 fs-17">{{ single_price($total) }}</span>
        </div>
        
        {{-- <div class="row align-items-center mx-0 pb-4">
            <div class="col-md-6 text-center text-md-left order-1 order-md-0">
                <a href="{{ route('home') }}" class="btn btn-link">
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

<script type="text/javascript">
    AIZ.extra.plusMinus();
</script>
