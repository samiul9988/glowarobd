@extends('backend.layouts.app')

@section('content')

<section class="">
    <form class="" action="" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row gutters-5">
            <div id="product_list" class="col-7 d-none">
                <div class="row gutters-5 mb-3">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="form-group mb-0">
                            <input class="form-control form-control-lg" type="text" name="keyword" placeholder="{{ ('Search by Product Name/Barcode') }}" onkeyup="filterProducts()">
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <select name="poscategory" class="form-control form-control-lg aiz-selectpicker" data-live-search="true" onchange="filterProducts()">
                            <option value="">{{ ('All Categories') }}</option>
                            @foreach ($categories as $key => $category)
                                <option value="category-{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-6">
                        <select name="brand"  class="form-control form-control-lg aiz-selectpicker" data-live-search="true" onchange="filterProducts()">
                            <option value="">{{ ('All Brands') }}</option>
                            @foreach ($brands as $key => $brand)
                                <option value="{{ $brand->id }}">{{ $brand->getTranslation('name') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="aiz-pos-product-list c-scrollbar-light">
                    <div class="d-flex flex-wrap justify-content-center" id="product-list">

                    </div>
                    <div id="load-more" class="text-center">
                        <div class="fs-14 d-inline-block fw-600 btn btn-soft-primary c-pointer" onclick="loadMoreProduct()">{{ ('Loading..') }}</div>
                    </div>
                </div>
            </div>
            <div id="order_details" class="col-md col mb-5">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="border-bottom pb-3">
                            <div class="d-none flex-grow-1">
                                <select name="user_id" class="form-control aiz-selectpicker pos-customer" data-live-search="true" onchange="getShippingAddress()">
                                    <option value="">{{ ('Walk In Customer')}}</option>
                                    @foreach ($customers as $key => $customer)
                                        <option value="{{ $customer->user_id }}" data-contact="{{ @$customer->user->email }}" @if($order->user_id == @$customer->user->id) selected @endif>{{ @$customer->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row gutters-5">
                                <div class="col text-center text-md-left">
                                    <address>
                                        <strong class="text-main">
                                            {{ @json_decode($order->shipping_address)->name }}
                                            {!! group_identity($order->user_id, 'image')!!}
                                        </strong>
                                        <br>
                                        <a href="tel:{{ @json_decode($order->shipping_address)->phone }}">{{ @json_decode($order->shipping_address)->phone }}</a><br>
                                        @if(@json_decode($order->shipping_address)->email!='')
                                        Email: {{ @json_decode($order->shipping_address)->email }}<br>
                                        @endif
                                        {{ json_decode($order->shipping_address)->address }},

                                        City: {{ json_decode($order->shipping_address)->city }},
                                        Area: {{ @json_decode($order->shipping_address)->area }},
                                        @if(@json_decode($order->shipping_address)->postal_code!='')
                                        Postal Code: {{ @json_decode($order->shipping_address)->postal_code }}<br>
                                        @endif
                                        {{ @json_decode($order->shipping_address)->country }}
                                    </address>
                                    @if ($order->manual_payment && is_array(json_decode($order->manual_payment_data, true)))
                                    <br>
                                    <strong class="text-main">{{ ('Payment Information') }}</strong><br>
                                    {{ ('Name') }}: {{ json_decode($order->manual_payment_data)->name }}, {{ ('Amount') }}: {{ single_price(json_decode($order->manual_payment_data)->amount) }}, {{ ('TRX ID') }}: {{ json_decode($order->manual_payment_data)->trx_id }}
                                    <br>
                                    <a href="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}" target="_blank"><img src="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}" alt="" height="100"></a>
                                    @endif
                                </div>
                                <div class="col text-center">
                                    <div class="d-block">
                                        <button type="button" class="btn btn-icon btn-soft-dark ml-3 mr-0" data-target="#new-customer" data-toggle="modal">
                                            <i class="las la-truck"></i>
                                        </button>
                                        <button type="button" id="toggle_products" class="btn btn-icon btn-soft-dark ml-3 mr-0">
                                            <i class="las la-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col ml-auto">
                                    <table class="float-right">
                                        <tbody>
                                            <tr>
                                                <td class="text-main text-bold">{{ ('Order #')}}</td>
                                                <td class="text-right text-info text-bold">	{{ $order->code }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-main text-bold">{{ ('Order Status')}}</td>
                                                <td class="text-right">
                                                    @if($order->delivery_status == 'delivered')
                                                    <span class="badge badge-inline badge-success">{{ (ucfirst(str_replace('_', ' ', $order->delivery_status))) }}</span>
                                                    @else
                                                    <span class="badge badge-inline badge-info">{{ (ucfirst(str_replace('_', ' ', $order->delivery_status))) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-main text-bold">{{ ('Order Date')}}	</td>
                                                <td class="text-right">{{ date('d-m-Y h:i A', $order->date) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-main text-bold">{{ ('Payment method')}}</td>
                                                <td class="text-right">{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-main text-bold">{{ ('Delivery type')}}</td>
                                                <td class="text-right">
                                                    @if ($order->orderDetails[0]->shipping_type != null && $order->orderDetails[0]->shipping_type == 'home_delivery')
                                                    {{ ('Home Delivery') }}
                                                    @elseif ($order->orderDetails[0]->shipping_type == 'pickup_point')

                                                    @if ($order->orderDetails[0]->pickup_point != null)
                                                    {{ $order->orderDetails[0]->pickup_point->getTranslation('name') }} ({{ ('Pickup Point') }})
                                                    @else
                                                    {{ ('Pickup Point') }}
                                                    @endif
                                                    @endif
                                                </td>
                                            </tr>
                                            @if($order->delivery_date!='')
                                            <tr>
                                                <td class="text-main text-bold">{{ ('Delivery Date')}}</td>
                                                <td class="text-right">{{ date('d-m-Y', $order->delivery_date) }} ({{ date('l', $order->delivery_date) }})</td>
                                            </tr>
                                            @endif
                                            @if(@$order->orderDetails[0]->shippingMethod->name!='')
                                            <tr>
                                                <td class="text-main text-bold">{{ ('Shipping method')}}:</td>
                                                <td class="text-right">
                                                    {{ @$order->orderDetails[0]->shippingMethod->name }}
                                                </td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div id="cart-details">
                            <div class="aiz-pos-cart-list mb-4 mt-3 c-scrollbar-light">
                                @php
                                    $subtotal = 0;
                                    $tax = 0;
                                @endphp
                                @if ($orderItems)
                                    <ul class="list-group list-group-flush">
                                    @forelse ($orderItems as $key => $cartItem)
                                        @php
                                            $subtotal += $cartItem['price']*$cartItem['quantity'];
                                            $tax += $cartItem['tax']*$cartItem['quantity'];
                                            $stock = \App\Models\ProductStock::find($cartItem['stock_id']);
                                        @endphp
                                        <li class="list-group-item py-0 pl-2">
                                            <div class="row gutters-5 align-items-center">
                                                <div class="col-auto w-60px">
                                                    <div class="row no-gutters align-items-center flex-column aiz-plus-minus">
                                                        <button class="btn col-auto btn-icon btn-sm fs-15" type="button" data-id="{{ $cartItem['product_id'] }}" data-type="plus" data-field="qty-{{ $key }}">
                                                            <i class="las la-plus"></i>
                                                        </button>
                                                        <input type="text" name="qty-{{ $key }}" id="qty-{{ $key }}" class="col border-0 text-center flex-grow-1 fs-16 input-number inputCart-{{$cartItem['product_id']}}" placeholder="1" value="{{ $cartItem['quantity'] }}" min="{{ $stock->product->min_qty }}" max="{{ $stock->qty }}" onchange="updateQuantity({{ $cartItem->id }}, this.value)" data-edit="true">
                                                        <button class="btn col-auto btn-icon btn-sm fs-15" type="button" data-id="{{ $cartItem['product_id'] }}" data-type="minus" data-field="qty-{{ $key }}">
                                                            <i class="las la-minus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="text-truncate-2">{{ $cartItem->name }}</div>
                                                    <span class="span badge badge-inline fs-12 badge-soft-secondary">{{ $cartItem['variation'] }}</span>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="fs-12 opacity-60">{{ single_price($cartItem['price']) }} x {{ $cartItem['quantity'] }}</div>
                                                    <div class="fs-15 fw-600">{{ single_price($cartItem['price']*$cartItem['quantity']) }}</div>
                                                </div>
                                                <div class="col-auto">
                                                    <button type="button" class="btn btn-circle btn-icon btn-sm btn-soft-danger ml-2 mr-0" onclick="removeFromCart({{ $cartItem->id }})">
                                                        <i class="las la-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="list-group-item">
                                            <div class="text-center">
                                                <i class="las la-frown la-3x opacity-50"></i>
                                                <p>{{ ('No Product Added') }}</p>
                                            </div>
                                        </li>
                                    @endforelse
                                    </ul>
                                @else
                                    <div class="text-center">
                                        <i class="las la-frown la-3x opacity-50"></i>
                                        <p>{{ ('No Product Added') }}</p>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                                    <span>{{ ('Sub Total')}}</span>
                                    <span>{{ single_price($subtotal) }}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                                    <span>{{ ('Tax')}}</span>
                                    <span>{{ single_price($tax) }}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                                    <span>{{ ('Shipping')}}</span>
                                    <span>{{ single_price($orderItems[0]->shipping_cost) }}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                                    <span>{{ ('Discount')}}</span>
                                    <span>{{ single_price($order->coupon_discount) }}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                                    <span>{{ ('Reward point discount')}}</span>
                                    <span>{{ single_price($order->reward_point_discount) }}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-600 fs-18 border-top pt-2">
                                    <span>{{ ('Total')}}</span>
                                    <span>{{ single_price($subtotal+$tax+$orderItems[0]->shipping_cost - ($order->coupon_discount + $order->reward_point_discount)) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pos-footer mar-btm">
                    <div class="d-flex flex-column flex-md-row justify-content-between">
                        <div class="d-flex">
                            <div class="mr-3 ml-0">
                                <button type="button" class="btn btn-outline-dark btn-styled" data-target="#shipping-information" data-toggle="modal">
                                    Shipping
                                </button>
                                <div id="shipping-information" class="modal fade" role="dialog">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-zoom" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bord-btm">
                                                <h4 class="modal-title h6">{{ ('Shipping Information')}}</h4>
                                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <form id="shipping_form">
                                                <div class="modal-body bg-light">
                                                    <input type="hidden" name="orderId" value="{{ $originalId }}">
                                                    <div class="form-group">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" id="inlineCheckbox1" name="shipping_type" value="home_delivery" checked>
                                                            <label class="form-check-label" for="inlineCheckbox1">Home Delivery</label>
                                                        </div>
                                                        @if (\App\Models\BusinessSetting::where('type', 'pickup_point')->first()->value == 1)
                                                        {{--  <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" id="inlineCheckbox2" name="shipping_type" value="pickup_point">
                                                            <label class="form-check-label" for="inlineCheckbox2">Pickup Point</label>
                                                        </div>
                                                        --}}
                                                        @endif
                                                    </div>

                                                    @php $shipping_method = \App\Models\ShippingMethod::where('status',1); @endphp
                                                    <div class="form-group">
                                                        <label for="formGroupExampleInput">Shipping Method</label>
                                                        <select id="shipping_method" class="form-control" name="shipping_method">
                                                            @foreach($shipping_method->get() as $method)
                                                            <option value="{{ $method->id }}" @if($orderItems[0]->shipping_method == $method->id) selected @endif>{{ $method->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="formGroupExampleInput2">Shipping Cost</label>
                                                        <input type="number" min="0" placeholder="Amount" name="shipping" class="form-control" value="{{ $orderItems[0]->shipping_cost ?? Session::get('invoice.shipping', 0) }}" required onchange="setShipping()">
                                                    </div>

                                                </div>
                                            </form>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-styled btn-base-3" data-dismiss="modal" id="close-button">{{ ('Close')}}</button>
                                                <button type="button" class="btn btn-primary btn-styled btn-base-1" data-dismiss="modal" onclick="setShipping()">{{ ('Confirm')}}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown dropup">
                                <button class="btn btn-outline-dark btn-styled dropdown-toggle" type="button" data-toggle="dropdown">
                                    {{ ('Discount')}}
                                </button>
                                <div class="dropdown-menu p-3 dropdown-menu-lg">
                                    <div class="input-group">
                                        <input type="number" min="0" placeholder="Amount" name="discount" class="form-control" value="{{ Session::get('invoice.discount', 0) }}" required onchange="setDiscount()">
                                        <div class="input-group-append">
                                            <span class="input-group-text">{{ ('Flat') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="my-2 my-md-0">
                            <button type="button" class="btn btn-primary btn-block" onclick="orderConfirmation()">{{ ('Update Order') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

@endsection

@section('modal')
    <!-- Address Modal -->
    <div id="new-customer" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header bord-btm">
                    <h4 class="modal-title h6">{{ ('Shipping Address')}}</h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="shipping_form">
                    <input type="hidden" name="orderId" value="{{ $originalId }}">
                    <div class="modal-body" id="shipping_address" style="max-height: 75vh;">


                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-styled btn-base-3" data-dismiss="modal" id="close-button">{{ ('Close')}}</button>
                    <button type="button" class="btn btn-primary btn-styled btn-base-1" id="confirm-address">{{ ('Confirm')}}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- new address modal -->
    <div id="new-address-modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header bord-btm">
                    <h4 class="modal-title h6">{{ ('Shipping Address')}}</h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <form class="form-horizontal" action="{{ route('addresses.store') }}" method="POST" enctype="multipart/form-data">
                	@csrf
                    <div class="modal-body">
                        <input type="hidden" name="customer_id" id="set_customer_id" value="">
                        <div class="form-group">
                            <div class=" row">
                                <label class="col-sm-2 control-label" for="name">{{ ('Name')}}</label>
                                <div class="col-sm-10">
                                    <input type="text" placeholder="{{ ('Name')}}" id="name" name="name" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class=" row">
                                <label class="col-sm-2 control-label" for="address">{{ ('Address')}}</label>
                                <div class="col-sm-10">
                                    <textarea placeholder="{{ ('Address')}}" id="address" name="address" class="form-control" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" @if(\App\Models\Country::where('status', 1)->count() == 1) style="display:none" @endif>
                            <div class="row">
                                <label class="col-sm-2 control-label">{{ ('Country')}}</label>
                                <div class="col-sm-10">
                                    <select class="form-control aiz-selectpicker" data-live-search="true" data-placeholder="{{ ('Select your country') }}" name="country_id" required>
                                        @if(\App\Models\Country::where('status', 1)->count()>1)
                                            <option value="">{{ ('Select your country') }}</option>
                                            @foreach (\App\Models\Country::where('status', 1)->get() as $key => $country)
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        @else
                                            @foreach (\App\Models\Country::where('status', 1)->get() as $key => $country)
                                                <option value="{{ $country->id }}" selected>{{ $country->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-2 control-label">
                                    <label>{{ ('State')}}</label>
                                </div>
                                <div class="col-sm-10">
                                    <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="state_id" required>

                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-2">
                                    <label>{{ ('City')}}</label>
                                </div>
                                <div class="col-sm-10">
                                    <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="city_id" required>

                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-2">
                                    <label>{{ ('Area')}}</label>
                                </div>
                                <div class="col-sm-10">
                                    <select class="form-control aiz-selectpicker" data-live-search="true" name="area_id" required>

                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class=" row">
                                <label class="col-sm-2 control-label" for="postal_code">{{ ('Postal code')}}</label>
                                <div class="col-sm-10">
                                    <input type="number" min="0" placeholder="{{ ('Postal code')}}" id="postal_code" name="postal_code" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class=" row">
                                <label class="col-sm-2 control-label" for="phone">{{ ('Phone')}}</label>
                                <div class="col-sm-10">
                                    <input type="number" min="0" placeholder="{{ ('Phone')}}" id="phone" name="phone" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-styled btn-base-3" data-dismiss="modal">{{ ('Close')}}</button>
                        <button type="submit" class="btn btn-primary btn-styled btn-base-1">{{ ('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="order-confirm" class="modal fade">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom modal-xl">
            <div class="modal-content" id="variants">
                <div class="modal-header bord-btm">
                    <h4 class="modal-title h6">{{ ('Order Summary')}}</h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body" id="order-confirmation">
                    <div class="p-4 text-center">
                        <i class="las la-spinner la-spin la-3x"></i>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-styled btn-base-3" data-dismiss="modal">{{ ('Close')}}</button>
                    <button type="button" onclick="submitOrder('cash')" class="btn btn-styled btn-base-1 btn-primary">{{ ('Comfirm Update')}}</button>
                </div>
            </div>
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection


@section('script')
    <script type="text/javascript">

        var products = null;

        $(document).ready(function(){
            // $('body').addClass('side-menu-closed');
            // $('#product_list').hide();
            $('#product-list').on('click','.add-plus:not(.c-not-allowed)',function(){
                var stock_id = $(this).data('stock-id');
                var orderId = '{{ $originalId }}';
                $.post('{{ route('invoice.addToCart') }}',{_token:AIZ.data.csrf, stock_id:stock_id, orderId:orderId}, function(data){
                    if(data.success == 1){
                        updateCart(data.view);
                    }else{
                        AIZ.plugins.notify('danger', data.message);
                    }

                });
            });
            // filterProducts();
            getShippingAddress();
        });

        $('#toggle_products').click(function (e){
            $('#product_list').toggleClass('d-none');
            filterProducts();
        });

        $("#confirm-address").click(function (e){
            e.preventDefault();
            var data = new FormData($('#shipping_form')[0]);
            var error=0;
            if($('#shipping_form input[name="name"]').val()==''){
                error++;
                AIZ.plugins.notify('danger', "Name can not be blank.");
            }

            /*if($('#shipping_form input[name="email"]').val()==''){
                error++;
                AIZ.plugins.notify('danger', "Email can not be blank.");
            }*/

            if($('#shipping_form textarea[name="address"]').val()==''){
                error++;
                AIZ.plugins.notify('danger', "Address can not be blank.");
            }

            if($('#shipping_form select[name="country_id"]').val()==''){
                error++;
                AIZ.plugins.notify('danger', "Country can not be blank.");
            }

            if($('#shipping_form select[name="state_id"]').val()==''){
                error++;
                AIZ.plugins.notify('danger', "State can not be blank.");
            }

            if($('#shipping_form select[name="city_id"]').val()==''){
                error++;
                AIZ.plugins.notify('danger', "City can not be blank.");
            }

            if($('#shipping_form select[name="area_id"]').val()==''){
                error++;
                AIZ.plugins.notify('danger', "Area can not be blank.");
            }

            if($('#shipping_form input[name="phone"]').val()==''){
                error++;
                AIZ.plugins.notify('danger', "Phone can not be blank.");
            }

            if(error==0){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': AIZ.data.csrf
                    },
                    method: "POST",
                    url: "{{route('invoice.set-shipping-address')}}",
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (data, textStatus, jqXHR) {
                        $('#new-customer').modal('hide')
                    }
                })
            }

        });

        function updateCart(data){
            $('#cart-details').html(data);
            AIZ.extra.plusMinus();
        }

        function filterProducts(){
            var keyword = $('input[name=keyword]').val();
            var category = $('select[name=poscategory]').val();
            var brand = $('select[name=brand]').val();
            $.get('{{ route('invoice.search_product') }}',{keyword:keyword, category:category, brand:brand}, function(data){
                products = data;
                $('#product-list').html(null);
                setProductList(data);
            });
        }

        function loadMoreProduct(){
            if(products != null && products.links.next != null){
                $('#load-more').find('.btn').html('{{ ('Loading..') }}');
                $.get(products.links.next,{}, function(data){
                    products = data;
                    setProductList(data);
                });
            }
        }

        function setProductList(data){
            for (var i = 0; i < data.data.length; i++) {
                $('#product-list').append(
                    `<div class="w-140px w-xl-180px w-xxl-210px mx-2">
                        <div class="card bg-white c-pointer product-card hov-container">
                            <div class="position-relative">
                                <span class="absolute-top-left mt-1 ml-1 mr-0">
                                    ${data.data[i].qty > 0
                                        ? `<span class="badge badge-inline badge-success fs-13">{{ ('In stock') }}`
                                        : `<span class="badge badge-inline badge-danger fs-13">{{ ('Out of stock') }}` }
                                    : ${data.data[i].qty}</span>
                                </span>
                                ${data.data[i].variant != null
                                    ? `<span class="badge badge-inline badge-warning absolute-bottom-left mb-1 ml-1 mr-0 fs-13 text-truncate">${data.data[i].variant}</span>`
                                    : '' }
                                <img src="${data.data[i].thumbnail_image }" class="card-img-top img-fit h-120px h-xl-180px h-xxl-210px mw-100 mx-auto" >
                            </div>
                            <div class="card-body p-2 p-xl-3">
                                <div class="text-truncate fw-600 fs-14 mb-2">${data.data[i].name}</div>
                                <div class="">
                                    ${data.data[i].price != data.data[i].base_price
                                        ? `<del class="mr-2 ml-0">${data.data[i].base_price}</del><span>${data.data[i].price}</span>`
                                        : `<span>${data.data[i].base_price}</span>`
                                    }
                                </div>
                            </div>
                            <div class="add-plus absolute-full rounded overflow-hidden hov-box ${data.data[i].qty <= 0 ? 'c-not-allowed' : '' }" data-stock-id="${data.data[i].stock_id}">
                                <div class="absolute-full bg-dark opacity-50">
                                </div>
                                <i class="las la-plus absolute-center la-6x text-white"></i>
                            </div>
                        </div>
                    </div>`
                );
            }
            if (data.links.next != null) {
                $('#load-more').find('.btn').html('{{ ('Load More.') }}');
            }
            else {
                $('#load-more').find('.btn').html('{{ ('Nothing more found.') }}');
            }
        }

        function removeFromCart(key){
            var orderId =  '{{ $originalId }}';
            $.post('{{ route('invoice.removeFromCart') }}', {_token:AIZ.data.csrf, key:key, orderId:orderId}, function(data){
                updateCart(data);
            });
        }

        function addToCart(product_id, variant, quantity){
            $.post('{{ route('invoice.addToCart') }}',{_token:AIZ.data.csrf, product_id:product_id, variant:variant, quantity, quantity}, function(data){
                $('#cart-details').html(data);
                $('#product-variation').modal('hide');
            });
        }

        function updateQuantity(key, value){
            // $('.loading-overlay').css('display', 'flex');
            var orderId =  '{{ $originalId }}';
            $.post('{{ route('invoice.updateQuantity') }}',{_token:AIZ.data.csrf, key:key, quantity: value, orderId: orderId}, function(data){
                if(data.success == 1){
                    updateCart(data.view);
                    // $('.loading-overlay').hide();
                }else{
                    AIZ.plugins.notify('danger', data.message);
                    // $('.loading-overlay').hide();
                }
            });
        }

        function setDiscount(){
            var discount = $('input[name=discount]').val();
            var orderId =  '{{ $originalId }}';
            $.post('{{ route('invoice.setDiscount') }}',{_token:AIZ.data.csrf, discount:discount, orderId:orderId}, function(data){
                updateCart(data);
            });
        }

        function setShipping(){
            var shippingMethod = $('#shipping_method').find(":selected").val();
            var shipping = $('input[name=shipping]').val();
            var type = $('input[name=shipping_type]').val();
            var orderId =  '{{ $originalId }}';
            if(shipping=='')
                shipping = 0;
            $.post('{{ route('invoice.setShipping') }}',{_token:AIZ.data.csrf, type:type, method:shippingMethod, shipping:shipping, orderId:orderId}, function(data){
                updateCart(data);
            });
        }

        function getShippingAddress(){
            var orderId =  '{{ $originalId }}';
            $.post('{{ route('invoice.getShippingAddress') }}',{_token:AIZ.data.csrf, id:$('select[name=user_id]').val(), orderId:orderId }, function(data){
                $('#shipping_address').html(data);
                if($('#shipping_address [name=country_id]').find('option').length==1){
                    var country_id = $('[name=country_id]').val();
                    if(country_id!='')
                        get_states(country_id);
                }

            });
        }

        function add_new_address(){
            var customer_id = $('#customer_id').val();
            $('#set_customer_id').val(customer_id);
            $('#new-address-modal').modal('show');
            $("#close-button").click();

            if($('#new-address-modal [name=country_id]').find('option').length==1){
                var country_id = $('[name=country_id]').val();
                if(country_id!='')
                    get_states(country_id);
            }

        }

        function orderConfirmation(){
            var orderId =  '{{ $originalId }}';
            $('#order-confirmation').html(`<div class="p-4 text-center"><i class="las la-spinner la-spin la-3x"></i></div>`);
            $('#order-confirm').modal('show');
            $.post('{{ route('invoice.getOrderSummary') }}',{_token:AIZ.data.csrf, orderId:orderId}, function(data){
                $('#order-confirmation').html(data);
            });
        }
        function submitOrder(payment_type){
            var orderId =  '{{ $originalId }}';

            $.post('{{ route('invoice.order_update') }}',{
                _token      :   AIZ.data.csrf,
                orderId     :   orderId
            }, function(data){
                if(data.success == 1){
                    AIZ.plugins.notify('success', data.message );
                    location.reload();
                }
                else{
                    AIZ.plugins.notify('danger', data.message );
                }
            });

        }


        //address
        $(document).on('change', '[name=country_id]', function() {
            var country_id = $(this).val();
            get_states(country_id);
        });

        $(document).on('change', '[name=state_id]', function() {
            var state_id = $(this).val();
            get_city(state_id);
        });

        $(document).on('change', '[name=city_id]', function() {
            var city_id = $(this).val();
            get_area(city_id);
        });

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

        function get_city(state_id) {
            $('[name="city"]').html("");
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('get-city')}}",
                type: 'POST',
                data: {
                    state_id: state_id
                },
                success: function (response) {
                    var obj = JSON.parse(response);
                    if(obj != '') {
                        $('[name="city_id"]').html(obj);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }

        function get_area(city_id) {
            $('[name="area"]').html("");
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('get-area')}}",
                type: 'POST',
                data: {
                    city_id: city_id
                },
                success: function (response) {
                    var obj = JSON.parse(response);
                    if(obj != '') {
                        $('[name="area_id"]').html(obj);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }
    </script>
@endsection
