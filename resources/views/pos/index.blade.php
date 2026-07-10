@extends('backend.layouts.app')
@php
    $categories = Cache::remember('filter_categories', now()->addDay(), function () {
        return \App\Models\Category::pluck('name', 'id')->toArray();
    });

    $brands = Cache::remember('filter_brands', now()->addDay(), function () {
        return \App\Models\Brand::pluck('name', 'id')->toArray();
    });

    // $walkin_customers = Cache::remember('pos_walkin_customers', now()->addHour(), function () {
    //     return \App\Models\User::where('user_type', 'customer')
    //         ->select('id','name','email','phone')
    //         ->take(1000)
    //         ->inRandomOrder()
    //         ->get();
    //     // return \App\Models\Customer::with('user:id,name,email')->take(1000)->get();
    // });

    $shipping_countries = Cache::remember('shipping_countries', now()->addDay(), function () {
        return \App\Models\Country::where('status', 1)->select('id','name')->get();
    });

    $shipping_countries_count = $shipping_countries->count();
@endphp

@push('cus_css')
    <link rel="stylesheet" href="{{ static_asset('assets/tekka/frontend/css/gift-offers.css') }}">
@endpush

@section('content')
<div class="overlay opacity-40" style="cursor: wait !important; display: none; z-index: 9999;" id="busy"></div>
<section class="mb-4">
    <form class="" action="" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row gutters-5">
            <div class="col-md">
                <div class="px-lg-4 px-0 row gutters-5 mb-3">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="input-group">
                            <input type="text" name="keyword" id="keyword" class="form-control form-control-lg" placeholder="Search by Product Name/Barcode">
                            <div class="input-group-append" data-toggle="tooltip" data-title="Search Term" data-placement="top">
                                <button class="btn btn-outline-secondary dropdown-toggle" id="search-term-dropdown" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Default</button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item search-option active" href="#" data-mode="default">Default</a>
                                    @if(get_setting('enable_meilisearch') == 1)
                                        <a class="dropdown-item search-option" href="#" data-mode="advance">Advance</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        {{-- <div class="form-group mb-0">
                            <input class="form-control form-control-lg" type="text" name="keyword" id="keyword" placeholder="{{ ('Search by Product Name/Barcode') }}">
                        </div> --}}
                    </div>
                    <div class="col-md-3 col-6">
                        <select name="poscategory" class="form-control form-control-lg aiz-selectpicker" data-live-search="true" onchange="filterProducts()">
                            <option value="">{{ ('All Categories') }}</option>
                            @foreach ($categories as $id => $name)
                                <option value="category-{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-6">
                        <select name="brand"  class="form-control form-control-lg aiz-selectpicker" data-live-search="true" onchange="filterProducts()">
                            <option value="">{{ ('All Brands') }}</option>
                            @foreach ($brands as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
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
            <div class="col-md-auto w-md-350px w-lg-400px w-xl-500px">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex border-bottom pb-3">
                            <div class="flex-grow-1">
                                {{-- <select name="user_id" class="form-control aiz-selectpicker pos-customer" data-live-search="true" data-select="customer" onchange="getShippingAddress()">
                                    <option value="">{{ ('Walk In Customer')}}</option>
                                    @foreach ($walkin_customers as $key => $user)
                                        <option value="{{ $user->id }}" @if(filled($user->phone)) data-contact="{{ $user->phone }}" data-ctype="phone" @elseif(filled($user->email)) data-contact="{{ $user->email }}" data-ctype="email" @endif>
                                            {{ ucwords($user->name) }}
                                        </option>
                                    @endforeach
                                </select> --}}

                                <select name="user_id"
                                        id="customers"
                                        class="form-control aiz-selectpicker pos-customer"
                                        data-live-search="true"
                                        onchange="getShippingAddress()">

                                    <option value="">Walk In Customer</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-icon btn-soft-dark ml-2 mr-0" data-target="#new-customer" data-toggle="modal">
								<i class="las la-truck"></i>
							</button>
                            <button type="button" class="btn btn-icon btn-soft-danger ml-1 mr-0" title="Reset Cart" onclick="resetCart()">
								<i class="las la-sync"></i>
							</button>
                        </div>

                        <div id="cart-details">
                            <div class="aiz-pos-cart-list mb-4 mt-3 c-scrollbar-light">
                                @php
                                    $subtotal = 0;
                                    $tax = 0;
                                    $carts = Session::get('pos.cart', collect());
                                    $stockIds = $carts->pluck('stock_id')->unique()->toArray();
                                    $stocks = \App\Models\ProductStock::whereIn('id', $stockIds)->get()->keyBy('id');
                                @endphp
                                <ul class="list-group list-group-flush">
                                    @forelse ($carts->where('type', 'regular') as $key => $cartItem)
                                        @php
                                            $subtotal += $cartItem['price']*$cartItem['quantity'];
                                            $tax += $cartItem['tax']*$cartItem['quantity'];
                                            $stock = $stocks->get($cartItem['stock_id']);
                                        @endphp
                                        <li class="list-group-item py-0 pl-2">
                                            <div class="row gutters-5 align-items-center">
                                                <div class="col-auto w-60px">
                                                    <div class="row no-gutters align-items-center flex-column aiz-plus-minus">
                                                        <button class="btn col-auto btn-icon btn-sm fs-15" type="button" data-id="{{ $cartItem['id'] }}" data-type="plus" data-field="qty-{{ $key }}">
                                                            <i class="las la-plus"></i>
                                                        </button>
                                                        <input type="text" name="qty-{{ $key }}" id="qty-{{ $key }}" class="col border-0 text-center flex-grow-1 fs-16 input-number inputCart-{{$cartItem['id']}}" placeholder="1" value="{{ $cartItem['quantity'] }}" min="{{ $stock->product->min_qty }}" max="{{ $stock->qty }}" onchange="updateQuantity({{ $key }})">
                                                        <button class="btn col-auto btn-icon btn-sm fs-15" type="button" data-id="{{ $cartItem['id'] }}" data-type="minus" data-field="qty-{{ $key }}">
                                                            <i class="las la-minus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="text-truncate-2">{{ $stock->product->name }}</div>
                                                    <span class="span badge badge-inline fs-12 badge-soft-secondary">{{ $cartItem['variant'] }}</span>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="fs-12 opacity-60">{{ single_price($cartItem['price']) }} x {{ $cartItem['quantity'] }}</div>
                                                    <div class="fs-15 fw-600">{{ single_price($cartItem['price']*$cartItem['quantity']) }}</div>
                                                </div>
                                                <div class="col-auto">
                                                    <button type="button" class="btn btn-circle btn-icon btn-sm btn-soft-danger ml-2 mr-0" onclick="removeFromCart({{ $key }})">
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
                                    <span>{{ single_price(Session::get('pos.shipping', 0)) }}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                                    <span>{{ ('Discount')}}</span>
                                    <span>{{ single_price(Session::get('pos.discount', 0)) }}</span>
                                </div>
                                @if(Session::get('pos.total_paid', 0))
                                <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                                    <span>{{ ('Paid Amount')}}</span>
                                    <span>
                                        <a type="button" title="{{ ('Remove Paid Amount') }}" class="text-danger" onclick="removePaidAmount()">
                                            <i class="las la-times-circle"></i>
                                        </a>
                                        {{ single_price(Session::get('pos.total_paid', 0)) }} (-)
                                    </span>
                                </div>
                                @endif
                                @php
                                    $total = $subtotal + $tax + Session::get('pos.shipping', 0) - Session::get('pos.discount', 0) - Session::get('pos.total_paid', 0);
                                @endphp
                                <div class="d-flex justify-content-between border-top pt-2">
                                    <span class="fw-600 fs-18">{{ ('Total')}}</span>
                                    {{-- @if (Session::has('pos.cart') && Session::get('pos.cart')->count() > 0)
                                    <button type="button" class="btn btn-sm btn-outline-dark btn-styled copy_summary">
                                        Copy Summary
                                    </button>
                                    @endif --}}
                                    <span class="fw-600 fs-18 total-amount" data-total="{{ max($total, 0) }}">{{ single_price(max($total, 0)) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pos-footer mar-btm">
                    <div class="d-flex flex-column flex-md-row justify-content-between">
                        <div class="d-flex justify-content-end align-items-center w-100">
                            <div class="mr-1">
                                <button type="button" class="btn btn-sm btn-outline-dark btn-styled copy_summary">
                                    Copy Summary
                                </button>
                            </div>
                            <div class="mr-1">
                                <button type="button" class="btn btn-outline-dark btn-sm w-100 btn-styled" data-target="#shipping-information" data-toggle="modal">
                                    Shipping
                                </button>
                                <div id="shipping-information" class="modal fade" role="dialog">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-zoom" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bord-btm">
                                                <h4 class="modal-title h6">{{ ('Shipping Information')}}</h4>
                                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <form id="shipping_amount_form">
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" id="inlineCheckbox1" name="shipping_type" value="home_delivery" checked>
                                                            <label class="form-check-label" for="inlineCheckbox1">Home Delivery</label>
                                                        </div>
                                                        {{-- @if (get_setting('pickup_point') == 1)
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" id="inlineCheckbox2" name="shipping_type" value="pickup_point">
                                                                <label class="form-check-label" for="inlineCheckbox2">Pickup Point</label>
                                                            </div>
                                                        @endif --}}
                                                    </div>

                                                    @php
                                                        $shipping_methods = Cache::remember('shipping_methods', now()->addDay(), function () {
                                                            return \App\Models\ShippingMethod::where('status', 1)->get();
                                                        });
                                                    @endphp
                                                    <div class="form-group">
                                                        <label for="formGroupExampleInput">Shipping Method</label>
                                                        <select class="form-control" name="shipping_method">
                                                            @foreach($shipping_methods as $method)
                                                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="shipping_amount">Shipping Cost</label>
                                                        <input type="number" min="0" placeholder="Amount" name="shipping" id="shipping_amount" class="form-control" value="{{ Session::get('pos.shipping', 0) }}" required onchange="setShipping()">
                                                    </div>

                                                </div>
                                            </form>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-styled btn-base-3" data-dismiss="modal" id="close-button">{{ ('Close')}}</button>
                                                <button type="button" class="btn btn-primary btn-styled btn-base-1" data-dismiss="modal">{{ ('Confirm')}}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown dropup mr-1">
                                <button class="btn btn-outline-dark btn-styled dropdown-toggle w-100 btn-sm" type="button" data-toggle="dropdown">
                                    {{ ('Discount')}}
                                </button>
                                <div class="dropdown-menu p-3 dropdown-menu-lg">
                                    <div class="input-group">
                                        <input type="number" min="0" placeholder="Amount" name="discount" id="discount_amount" class="form-control" value="{{ Session::get('pos.discount', 0) }}" required onchange="setDiscount()">
                                        <div class="input-group-append">
                                            <span class="input-group-text">{{ ('Flat') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="mr-1">
                                <button type="button" onclick="paynow()" class="btn w-100 btn-sm btn-outline-dark btn-styled">{{ ('Pay Now') }}</button>
                            </div> --}}
                            <div class="">
                                <button type="button" class="btn btn-sm btn-primary btn-block" onclick="orderConfirmation()">{{ ('Place Order') }}</button>
                            </div>
                        </div>
                        {{-- <div class="my-2 my-md-0">
                            <button type="button" class="btn btn-primary btn-block" onclick="orderConfirmation()">{{ ('Place Order') }}</button>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
<style>
    .button-wrapper{
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 5px;
    }
    @media screen  and (max-width: 1200px) {
        .button-wrapper{
            grid-template-columns: repeat(2, 1fr);
        }

    }
</style>
@include('backend.components.offcanvas', [
    'header' => 'History',
    'title' => 'Recent Orders',
    'show' => Session::has('pos.phoneNumber')
])
@endsection

@section('modal')
    @include('modals.partial_pay_modal', [
        'from' => 'pos'
    ])

    {{-- Call Log Create Modal Start --}}
    <div id="call-log-create-modal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6 mr-2">{{ ('New Call Log') }}</h4>
                    <a role="button" id="start-call-timer" class="btn btn-soft-secondary btn-icon btn-sm btn-circle" title="{{ ('Start Call') }}">
                        <i class="las la-tty"></i>
                    </a>
                    <a role="button" id="end-call-timer" class="btn btn-soft-danger btn-icon btn-sm btn-circle" title="{{ ('End Call') }}" style="display: none;">
                        <i class="las la-phone-volume"></i>
                    </a>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="status">{{ ('Status') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select class="form-control" name="status" id="call_log_status">
                                <option value="">{{ ('Select Status') }}</option>
                                @foreach (getHoldStatuses() as $value => $text)
                                    @if ($value == 'shipment_failed')
                                        @continue
                                    @endif
                                    <option value="{{ $value }}">{{ (ucwords($text)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="text-danger" style="display: none" id="call_log_status_error"></div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="note">{{ ('Note') }}</label>
                        <div class="input-group">
                            <textarea type="text" class="form-control" name="note" id="call_log_note" placeholder="{{ ('Note') }}" rows="3" required></textarea>
                        </div>
                        <div class="text-danger" style="display: none" id="call_log_note_error"></div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="duration">{{ ('Call Duration') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="duration" id="call_log_duration" placeholder="{{ ('Duration in minutes e.g. 5 or 8.2') }}" value="00.00" readonly>
                        </div>
                        <div class="text-danger" style="display: none" id="call_log_duration_error"></div>
                    </div>
                    <div class="form-group mb-3 text-right">
                        <button id="clear-call-log-form" class="btn btn-secondary">{{ ('Clear') }}</button>
                        <button id="store-call-log" type="submit" class="btn btn-primary">{{ ('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Call Log Create Modal End --}}

    <!-- Address Modal -->
    <div id="new-customer" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header bord-btm">
                    <h4 class="modal-title h6">{{ ('Shipping Address')}}</h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="shipping_form">
                    <div class="modal-body" id="shipping_address">

                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" id="close-button">{{ ('Close')}}</button>
                    <button type="button" class="btn btn-danger btn-sm" id="reset-shipping-address-form-button">{{ ('Reset')}}</button>
                    <button type="button" class="btn btn-primary btn-sm" id="confirm-address">{{ ('Confirm')}}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Existing Address Modal -->
    <div id="existing-address-modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-lg modal-dialog-centered modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header bord-btm">
                    <h4 class="modal-title h6">{{ ('Existing Addresses')}}</h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body" id="existing_addresses">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">{{ ('Close')}}</button>
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
                                <label class="col-sm-2 control-label" for="name">Name</label>
                                <div class="col-sm-10">
                                    <input type="text" placeholder="Name" id="name" name="name" class="form-control" required>
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
                        <div class="form-group" @if($shipping_countries_count==1) style="display:none" @endif>
                            <div class=" row">
                                <label class="col-sm-2 control-label">{{ ('Country')}}</label>
                                <div class="col-sm-10">
                                    <select class="form-control aiz-selectpicker" data-live-search="true" data-placeholder="{{ ('Select your country') }}" name="country_id" required>
                                        @if($shipping_countries_count>1)
                                            <option value="">{{ ('Select your country') }}</option>
                                        @endif
                                        @foreach ($shipping_countries as $key => $country)
                                            <option value="{{ $country->id }}" {{ $shipping_countries_count == 1 ? 'selected' : '' }}>{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-2 control-label"">
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
                                <label class="col-sm-2 control-label" for="phone">{{ ('Phone')}}</label>
                                <div class="col-sm-10">
                                    <input type="number" min="0" placeholder="{{ ('Phone')}}" id="phone" name="phone" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
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
                <div class="modal-footer" id="order-confirmation-menu">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" onclick="submitOrder('cash')" class="btn btn-primary btn-sm">Confirm</button>
                </div>
            </div>
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection


@section('script')
    <script src="{{ static_asset('assets/js/jquery-ui.js') }}"></script>
    <script type="text/javascript">
        let isLoading = false;
        let phoneNumber = '{{ Session::get('pos.phoneNumber', '') }}';
        let delivery_status = 'pending';
        let order_source = 'POS';
        let callDuration = '00.00';
        let checkInterval;
        let timerInterval;
        let callDurationInterval;
        let products = null;
        let searchMode = 'default';

        function busy() {
            $('#busy').show();
        }
        function free() {
            $('#busy').hide();
        }

        $(document).ready(function(){
            $('body').addClass('side-menu-closed');
            $('#product-list').on('click','.add-plus:not(.c-not-allowed)',function(){
                var stock_id = $(this).data('stock-id');
                busy();
                $.post('{{ route('pos.addToCart') }}',{_token:AIZ.data.csrf, stock_id:stock_id}, function(data){
                    if(data.success == 1){
                        updateCart(data.view);
                    }else{
                        showAlert('error', data.message);
                        // AIZ.plugins.notify('danger', data.message);
                    }
                    free();
                });
            });
            $('.search-option').on('click', function(e) {
                e.preventDefault();
                $('.search-option').removeClass('active');
                $(this).addClass('active');
                searchMode = $(this).data('mode');
                $('#search-term-dropdown').text($(this).text());
            });

            filterProducts(searchMode);

            getShippingAddress();

            if(phoneNumber) {
                getCustomerSuccessRate();
                getRecentOrders();
            }

            $(document).on('click', '.copy_summary', function(e){
                e.preventDefault();
                copySummaryToClipboard();
            });

            $(document).on('click', '.copy_final_summary', function(e){
                e.preventDefault();
                copySummaryToClipboard('final');
            });

            function copySummaryToClipboard(type = 'default') {
                busy();
                $.ajax({
                    method: "GET",
                    url: '{{ route('pos.get_order_summary') }}',
                    data: {
                        type: type
                    },
                    success: function (response) {
                        if (response.success) {
                            var $temp = $("<textarea>");
                            $("body").append($temp);
                            $temp.val(response.summary).select();
                            try {
                                document.execCommand("copy");
                                showAlert('success', 'Order summary copied to clipboard');
                                // AIZ.plugins.notify('success', '{{ ('Order summary copied to clipboard') }}');
                            } catch (err) {
                                showAlert('error', 'Oops, unable to copy');
                                // AIZ.plugins.notify('danger', '{{ ('Oops, unable to copy') }}');
                            }
                            $temp.remove();
                        } else {
                            showAlert('error', response.message || 'Failed to get order summary');
                            // AIZ.plugins.notify('danger', response.message || '{{ ('Failed to get order summary') }}');
                        }
                    },
                    error: function (xhr, status, error) {
                        showAlert('error', 'Failed to get order summary');
                        // AIZ.plugins.notify('danger', '{{ ('Failed to get order summary') }}');
                    },
                    complete: function() {
                        free();
                    }
                });
            }
        });

        $('#reset-shipping-address-form-button').on('click', function() {
            $('#shipping_form')[0].reset();
            AIZ.plugins.bootstrapSelect('refresh');
        });

        function getCustomerSuccessRate() {
            busy();
            $.ajax({
                method: "GET",
                url: "{{ route('pos.getCustomerSuccessRate') }}",
                data: {phone: phoneNumber},
                success: function (response) {
                    if(response.view){
                        offcanvasContent(response.view, 'success-rate');
                        showFloatingButton(true);
                    } else {
                        offcanvasContent('', 'success-rate');
                    }
                    free();
                },
                error: function (xhr, status, error) {
                    AIZ.plugins.notify('danger', 'Error fetching customer success rate.');
                    console.error("Error fetching customer success rate:", error);
                    free();
                }
            });
        }

        function resetCart() {
            busy();
            $.ajax({
                method: "GET",
                url: "{{ route('pos.resetCart') }}",
                success: function (response) {
                    updateCart(response.view);
                    showFloatingButton(false);
                    showAlert('success', '{{ ('Cart has been reset successfully') }}');
                    $('#shipping_form')[0].reset();
                    $('#shipping_amount').val(0);
                    $('#discount_amount').val(0);
                    AIZ.plugins.bootstrapSelect('refresh');
                    free();
                },
                error: function (xhr, status, error) {
                    console.error("Error resetting cart:", error);
                    AIZ.plugins.notify('danger', '{{ ('Failed to reset cart') }}');
                    free();
                }
            });
        }

        function resetForm(){
            $('#call_log_status').val('');
            $('#call_log_note').val('');
            $('#call_log_duration').val('00.00');
            $('#call_log_note_error').val('');
        }

        $('#order-confirmation').on('change', '#change-delivery-status', function() {
            delivery_status = $(this).val();
        });
        $('#order-confirmation').on('change', '#change-order-source', function() {
            order_source = $(this).val();
            if(order_source.toLowerCase() === 'showroom') {
                $('#change-delivery-status').html(`<option value="delivered" selected>Delivered</option>`);
                $('#order-confirmation-menu').html(`
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" onclick="submitOrder('cash')" class="btn btn-sm btn-primary">Comfirm</button>
                    <button type="button" onclick="submitOrder('cash', true)" class="btn btn-sm btn-success">Comfirm & Print</button>
                `);
                delivery_status = 'delivered';
            } else {
                delivery_status = 'pending';
                $('#change-delivery-status').html(`<option value="pending" selected>Pending</option>
                            <option value="processing">Processing</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="hold">Hold</option>`);
                $('#order-confirmation-menu').html(`
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" onclick="submitOrder('cash')" class="btn btn-sm btn-primary">Comfirm</button>
                `);
            }
        });
        let existingCoupon = '{{ Session::get('coupon', '') }}';
        $('#order-confirmation').on('change', '#select-coupon', function() {
            const couponCode = $(this).val();
            // if(couponCode === existingCoupon) { return }

            busy();
            $.ajax({
                method: "POST",
                url: "{{ route('pos.applyCoupon') }}",
                data: {
                    _token: AIZ.data.csrf,
                    code: couponCode
                },
                success: function (response) {
                    if(response.success){
                        existingCoupon = couponCode;
                        $('#order-confirmation').html(response.view);
                        $('#discount_amount').val(0).trigger('change');
                        showAlert('success', response.message || '{{ ('Coupon applied successfully') }}');
                    }else{
                        $('#select-coupon').val(existingCoupon);
                        showAlert('error', response.message || '{{ ('Failed to apply coupon') }}');
                    }
                },
                error: function (xhr, status, error) {
                    $('#select-coupon').val(existingCoupon);
                    console.error("Error applying coupon:", error);
                    AIZ.plugins.notify('danger', '{{ ('Failed to apply coupon') }}');
                },
                complete: function() {
                    free();
                }
            });
        });
        $(document).on('click', '#giftOffersHeader', function() {
            $('#giftOffersContent').slideToggle(300);
            $(this).find('i.las.la-angle-up, i.las.la-angle-down').toggleClass('la-angle-up la-angle-down');
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

            console.log("Offer ID:", offerId, "Item ID:", itemId);
            addGiftToCart(offerId, itemId);
        });
        $(document).on('click', '.gift-item-qty', function() {
            const key = $(this).data('key');
            const action = $(this).data('action');
            if ($(this).hasClass('disabled')) {
                return;
            }

            if (key && action) {
                updateGiftQuantity(key, action);
            }
            console.log('Key:', key, 'Action:', action);
        })
        $(document).on('click', '.remove-gift-item', function() {
            const key = $(this).data('key');
            if (key) {
                removeFromCart(key, true);
            }
        })
        function updateGiftQuantity(key, action) {
            busy();
            $.ajax({
                method: "POST",
                url: "{{ route('pos.updateGiftQuantity') }}",
                data: {
                    _token: AIZ.data.csrf,
                    key: key,
                    action: action
                },
                success: function (response) {
                    if(response.success){
                        $('#order-confirmation').html(response.view);
                        showAlert('success', response.message || 'Gift quantity updated');
                    }else{
                        showAlert('error', response.message || 'Failed to update gift quantity');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error updating quantity: ", error);
                    const errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to update gift quantity';
                    AIZ.plugins.notify('danger', errorMessage);
                },
                complete: function() {
                    free();
                }
            });
        }
        function addGiftToCart(offerId, itemId) {
            busy();
            $.ajax({
                method: "POST",
                url: "{{ route('pos.addGiftToCart') }}",
                data: {
                    _token: AIZ.data.csrf,
                    offer_id: offerId,
                    item_id: itemId
                },
                success: function (response) {
                    if(response.success){
                        $('#order-confirmation').html(response.view);
                        showAlert('success', response.message || 'Gift added to cart successfully');
                    }else{
                        showAlert('error', response.message || 'Failed to add gift to cart');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error adding gift to cart:", error);
                    AIZ.plugins.notify('danger', 'Failed to add gift to cart');
                },
                complete: function() {
                    free();
                }
            });
        }
        $('#order-confirmation').on('click', '#create-call-log-btn', function() {
            resetForm();
            if(!$('#end-call-timer').is(':hidden')){
                $('#end-call-timer').click();
            }
            $('#call_log_duration').val(callDuration);
            var modal = $('#call-log-create-modal');
            modal.modal('show');
        });
        $('#clear-call-log-form').on('click', function(){
            resetForm();
        });
        $('#store-call-log').on('click', function(){
            var status = $('#call_log_status').val();
            var note = $('#call_log_note').val();
            var duration = $('#call_log_duration').val();

            if(status == ''){
                $('#call_log_status_error').html('Status is required').show();
                return;
            }

            console.log('Storing Call Logs:', {
                status: status,
                note: note,
                duration: duration
            });

            busy();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': AIZ.data.csrf
                },
                method: "POST",
                url: "{{ route('pos.storeCallLog') }}",
                data: {
                    status: status,
                    note: note,
                    duration: duration,
                },
                success: function (response) {
                    if(response.success){
                        $('#call-log-create-modal').modal('hide');
                        showAlert('success', '{{ ('Call log saved successfully') }}');
                        resetForm();
                    }else{
                        showAlert('error', response.message || '{{ ('Failed to save call log') }}');
                    }
                    free();
                },
                error: function (xhr, status, error) {
                    console.error("Error storing call log:", error);
                    AIZ.plugins.notify('danger', '{{ ('Failed to save call log') }}');
                    free();
                }
            });
        });

        // Start Call button click handler
        $('#start-call-timer').click(function() {
            $(this).hide();
            $('#end-call-timer').show();

            let totalSeconds = 0;
            callDurationInterval = setInterval(function() {
                totalSeconds++;

                // Format as HH:MM:SS
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                // Display formatted time
                const formattedTime = `${minutes.toString().padStart(2, '0')}.${seconds.toString().padStart(2, '0')}`;
                $('#call_log_duration').val(formattedTime);
            }, 1000);
        });

        // End Call button click handler
        $('#end-call-timer').click(function() {
            clearInterval(callDurationInterval);  // Stop the timer

            callDuration = $('#call_log_duration').val();

            $(this).hide();
            $('#start-call-timer').show();
        });

        $('#shipping_address').on('input', 'input[name="phone"]', function() {
            var phone = normalizePhoneNumber($(this).val());
            $(this).val(phone); // Update the input field
            if (phone.length == 11) {
                busy();
                $.ajax({
                    method: "GET",
                    url: "{{ route('pos.getCustomerAddress') }}",
                    data: {phone: phone},
                    success: function (response) {
                        if(response.view){
                            $('#existing_addresses').html(response.view);
                            $('#existing-address-modal').modal('show');
                        }else{
                            showAlert('error', response.message || 'No address found for this phone number.');
                        }
                        free();
                    },
                    error: function (xhr, status, error) {
                        AIZ.plugins.notify('danger', 'Error fetching existing addresses.');
                        console.error("Error fetching customer address:", error);
                        free();
                    }
                });
            }
        });

        $('#existing_addresses').on('click', '.address-info', function() {
            let info = $(this).data('info');
            console.log('Selected Address Info:', info);
            $('#shipping_form input[name="name"]').val(info.name);
            $('#shipping_form textarea[name="address"]').val(info.address);
            $('#shipping_form select[name="area_id"]').val(info.area_id);
            $('#shipping_form select[name="state_id"]').val(info.state_id);
            $('#shipping_form select[name="city_id"]').val(info.city_id);
            AIZ.plugins.bootstrapSelect('refresh');

            $('#existing-address-modal').modal('hide');
        })

        $("#confirm-address").click(function (e){
            e.preventDefault();
            var error=0;
            if($('#shipping_form input[name="name"]').val()==''){
                error++;
                // showAlert('error', '{{ ('Name can not be blank.') }}');
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

            var phone = normalizePhoneNumber($('#shipping_form input[name="phone"]').val());

            if (phone.startsWith('+')) {
                phone = phone.substring(1); // Remove the '+' sign
            }
            if (phone.startsWith('88')) {
                phone = phone.substring(2); // Remove '88' country code
            }
            if(phone==''){
                error++;
                AIZ.plugins.notify('danger', "Phone can not be blank.");
            }else if(phone.length != 11){
                error++;
                AIZ.plugins.notify('danger', "Phone number must be 11 digits.");
            }
            $('#shipping_form input[name="phone"]').val(phone); // Update the input field

            var data = new FormData($('#shipping_form')[0]);
            if(error==0){
                busy();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': AIZ.data.csrf
                    },
                    method: "POST",
                    url: "{{route('pos.set-shipping-address')}}",
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (response, textStatus, jqXHR) {
                        if(response.success){
                            $('#new-customer').modal('hide');
                            phoneNumber = $('#shipping_form input[name="phone"]').val();
                            getCustomerSuccessRate();
                            getRecentOrders();
                        }else{
                            AIZ.plugins.notify('danger', response.message || 'Failed to set shipping address.');
                        }
                        free();
                    },
                    error: function (xhr, status, error) {
                        AIZ.plugins.notify('danger', 'Error setting shipping address.');
                        console.error("Error setting shipping address:", error);
                        free();
                    }
                })
            }
        });

        function getRecentOrders() {
            busy();
            $.ajax({
                method: "GET",
                url: "{{ route('pos.getRecentOrders') }}",
                data: {phone: phoneNumber},
                success: function (response) {
                    if(response.view){
                        offcanvasContent(response.view, 'recent-orders');
                        showFloatingButton(true);
                    }else{
                        offcanvasContent('', 'recent-orders');
                    }
                    free();
                },
                error: function (xhr, status, error) {
                    AIZ.plugins.notify('danger', 'Error fetching recent orders.');
                    console.error("Error fetching recent orders:", error);
                    free();
                }
            });
        }

        function updateCart(data){
            $('#cart-details').html(data);
            AIZ.extra.plusMinus();
        }

        $().ready(function(){
            const debouncedSearch = debounce(filterProducts, 500);
            $('#keyword').on('input', function() {
                debouncedSearch();
            });
            filterCustomers();
        });

        function delaySearch(func, delay) {
            let timeout;
            return function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, arguments), delay);
            };
        }

        $(document).on('shown.bs.select', '#customers', function () {
            const $select = $(this);
            const $searchInput = $select
                .closest('.bootstrap-select')
                .find('.bs-searchbox input');

            $searchInput.off('input').on('input', delaySearch(function () {
                const keyword = this.value;

                if (keyword.length != 0 && keyword.length < 2) return;
                filterCustomers(keyword);
            }, 300));
        });

        function filterCustomers(keyword = '') {
            $.get('{{ route('pos.customers.search') }}', { q: keyword }, function (res) {
                const users = res.users;
                let $select = $('#customers');
                // Keep walk-in option
                $select.find('option:not(:first)').remove();

                if (!users || users.length === 0) return;
                users.forEach(user => {
                    const option = new Option(user.name, user.id, false, false);

                    $(option)
                        .attr('data-contact', user.contact)
                        .attr('data-ctype', user.ctype);

                    $select.append(option);
                });
                $select.selectpicker('refresh');
            });
        }

        function filterProducts(){
            busy();
            var category = $('select[name=poscategory]').val();
            var brand = $('select[name=brand]').val();
            var keyword = $('input[name=keyword]').val();
            $.get('{{ route('pos.search_product') }}',{keyword:keyword, category:category, brand:brand, mode:searchMode}, function(data){
                products = data;
                $('#product-list').html(null);
                setProductList(data);
            }).always(function() {
                free();
            });
        }

        function loadMoreProduct(){
            isLoading = true;
            if(products != null && products.links.next != null){
                $('#load-more').find('.btn').html('{{ ('Loading..') }}');
                var category = $('select[name=poscategory]').val();
                var brand = $('select[name=brand]').val();
                var keyword = $('input[name=keyword]').val();
                $.get(products.links.next,{mode: searchMode, keyword:keyword, category:category, brand:brand}, function(data){
                    products = data;
                    setProductList(data);
                    isLoading = false;
                });
            }
        }

        function getCopyInfo(data){
            let normalPrice = data.base_price || 0;
            let discountPrice = data.price || 0;
            let info = '🛍 Product Name : ' + data.name + "\n\n";
            info += '💰 Price: ' + normalPrice + "\n";
            if(normalPrice != discountPrice){
                info += '🔥 Discount Price: ' + discountPrice + "\n";
            }
            info += "\n" + '🛒 Order Now : ' + data.url + "\n";

            return info;
        }

        // Add scroll event listener to the container
        $('.aiz-pos-product-list').on('scroll', function() {
            const $container = $(this);
            const scrollPosition = $container.scrollTop() + $container.innerHeight();
            const scrollThreshold = $container[0].scrollHeight - 100;

            if (scrollPosition >= scrollThreshold && !isLoading && products.links.next != null) {
                loadMoreProduct();
            }
        });

        function setProductList(data){
            for (var i = 0; i < data.data.length; i++) {
                let copyInfo = getCopyInfo(data.data[i]);
                $('#product-list').append(
                    `<div class="w-140px w-xl-180px w-xxl-180px mx-0 mr-2">
                        <div class="card bg-white c-pointer product-card hov-container mb-2" title="${data.data[i].name}">
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
                                <img src="${data.data[i].thumbnail_image }" data-src="${data.data[i].thumbnail_image }" alt="${data.data[i].name}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';" class="card-img-top img-fit h-120px h-xl-180px h-xxl-210px mw-100 mx-auto" >
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
                        <div class="text-center mb-2" title="Copy Product Info">
                            <span role="button" class="w-auto badge badge-pill badge-info copy-product-info" data-info="${copyInfo}">COPY</span>
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

        function removeFromCart(key, isGift = false){
            busy();
            $.ajax({
                method: "POST",
                url: '{{ route('pos.removeFromCart') }}',
                data: {_token:AIZ.data.csrf, key:key, isGift: isGift},
                success: function(response) {
                    if (response.success) {
                        if (!isGift) {
                            updateCart(response.view);
                        } else {
                            $('#order-confirmation').html(response.view);
                        }
                        AIZ.plugins.notify('success', response.message || 'Item removed from cart successfully');
                    } else {
                        AIZ.plugins.notify('danger', response.message || 'Failed to remove item from cart');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error removing item from cart:", error);
                    AIZ.plugins.notify('danger', '{{ ('Failed to remove item from cart') }}');
                },
                complete: function() {
                    free();
                }
            });
            // $.post('{{ route('pos.removeFromCart') }}', {_token:AIZ.data.csrf, key:key}, function(data){
            //     updateCart(data);
            //     free();
            // });
        }

        function addToCart(product_id, variant, quantity){
            busy();
            $.post('{{ route('pos.addToCart') }}',{_token:AIZ.data.csrf, product_id:product_id, variant:variant, quantity, quantity}, function(data){
                $('#cart-details').html(data);
                $('#product-variation').modal('hide');
                free();
            });
        }

        function updateQuantity(key){
            busy();
            $.post('{{ route('pos.updateQuantity') }}',{_token:AIZ.data.csrf, key:key, quantity: $('#qty-'+key).val()}, function(data){
                if(data.success == 1){
                    updateCart(data.view);
                }else{
                    showAlert('error', data.message);
                    // AIZ.plugins.notify('danger', data.message);
                }
                free();
            });
        }

        function setDiscount(){
            busy();
            var discount = $('input[name=discount]').val();
            $.post('{{ route('pos.setDiscount') }}',{_token:AIZ.data.csrf, discount:discount}, function(data){
                updateCart(data);
                free();
            });
        }

        function setShipping(){
            busy();
            var shipping = $('input[name=shipping]').val() || 0;
            $.post('{{ route('pos.setShipping') }}',{_token:AIZ.data.csrf, shipping:shipping}, function(data){
                $('#shipping-information').modal('hide');
                updateCart(data);
                free();
            });
        }

        function getShippingAddress(){
            $.post('{{ route('pos.getShippingAddress') }}',{_token:AIZ.data.csrf, id:$('select[name=user_id]').val()}, function(data){
                $('#shipping_address').html(data);
                // console.log($('#shipping_address [name=country_id]').find('option').length)
                if($('#shipping_address [name=country_id]').find('option').length==1){
                    var country_id = $('[name=country_id]').val();
                    if(country_id!=''){
                        get_states(country_id);
                        get_city();
                        get_area();
                    }
                }

            });
        }

        function add_new_address(){
            var customer_id = $('#customer_id').val();
            $('#set_customer_id').val(customer_id);
            $('#new-address-modal').modal('show');
            $("#close-button").click();

            // console.log($('#new-address-modal [name=country_id]').find('option').length)
            if($('#new-address-modal [name=country_id]').find('option').length==1){
                var country_id = $('[name=country_id]').val();
                if(country_id!='')
                    get_states(country_id);
            }

        }

        function orderConfirmation(){
            delivery_status = 'pending';
            order_source = 'POS';
            $('#order-confirmation-menu').html(`
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" onclick="submitOrder('cash')" class="btn btn-primary btn-sm">Comfirm</button>
                `);
            let $modal = $('#order-confirm');
            $modal.draggable({
                handle: ".modal-header",
            });
            $('#order-confirmation').html(`<div class="p-4 text-center"><i class="las la-spinner la-spin la-3x"></i></div>`);
            $('#order-confirm').modal('show');
            $.post('{{ route('pos.getOrderSummary') }}',{_token:AIZ.data.csrf}, function(data){
                $('#order-confirmation').html(data);
            });
        }

        function submitOrder(payment_type, print = false){
            busy();
            var user_id = $('select[name=user_id]').val();
            var name = $('input[name=name]').val();
            var email = $('input[name=email]').val();
            var address = $('textarea[name=address]').val();
            var country = $('select[name=country_id]').val();
            var state = $('select[name=state_id]').val();
            var city = $('select[name=city_id]').val();
            var area = $('select[name=area_id]').val();
            var postal_code = $('input[name=postal_code]').val();
            var phone = $('input[name=phone]').val();
            var shipping = $('input[name=shipping]:checked').val();
            var discount = $('input[name=discount]').val();
            var shipping_address = $('input[name=address_id]:checked').val();
            var shipping_type = $('input[name="shipping_type"]').val()
            var shipping_method = $('select[name="shipping_method"]').val()

            $.ajax({
                url: '{{ route('pos.order_place') }}',
                method: 'POST',
                data: {
                    _token: AIZ.data.csrf,
                    user_id: user_id,
                    name: name,
                    email: email,
                    address: address,
                    country: country,
                    state: state,
                    city: city,
                    area: area,
                    postal_code: postal_code,
                    phone: phone,
                    shipping_address: shipping_address,
                    payment_type: payment_type,
                    shipping: shipping,
                    discount: discount,
                    shipping_type: shipping_type,
                    shipping_method: shipping_method,
                    delivery_status: delivery_status,
                    order_source: order_source
                },
                success: function (data) {
                    free();
                    if (data.success == 1) {
                        $('#order-confirm').modal('hide');
                        let invoiceUrl = `{{ route('invoice.pos_invoice_label', ':id') }}`.replace(':id', data.order_id);
                        showAlert('success', data.message, window.location.href);

                        if (order_source.toLowerCase() === 'showroom' && print) {
                            window.open(invoiceUrl, '_blank');
                        }
                    } else {
                        free();
                        showAlert('error', data.message);
                    }
                },
                error: function (xhr, status, error) {
                    free();
                    if (xhr.status === 422) {
                        // Laravel validation error
                        let errors = xhr.responseJSON.errors;
                        let messages = Object.values(errors).flat().join('<br>');
                        showAlert('error', messages);
                    } else {
                        // Other errors (500, 404, etc.)
                        showAlert('error', xhr.responseJSON?.message || 'Something went wrong, please try again.');
                    }
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

        function get_states(country_id = '') {
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
                    if (response.success) {
                        $('[name="state_id"]').html(response.html);
                        AIZ.plugins.bootstrapSelect('refresh');
                    } else {
                        $('[name="state_id"]').html("");
                        $('[name="city_id"]').html("");
                        $('[name="area_id"]').html("");
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }

        function get_city(state_id = '') {
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
                    if (response.success) {
                        $('[name="city_id"]').html(response.html);
                        AIZ.plugins.bootstrapSelect('refresh');
                    } else {
                        $('[name="city_id"]').html("");
                        $('[name="area_id"]').html("");
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }

        function get_area(city_id = '') {
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
                    if (response.success) {
                        $('[name="area_id"]').html(response.html);
                        AIZ.plugins.bootstrapSelect('refresh');
                    } else {
                        $('[name="area_id"]').html("");
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }

        // PaymentScript
        function paynow(e) {
            var url = $(this).data("href");
            $("#partial-pay-modal").modal("show");
            $("#partial-payment-link").attr("href", url);

            window.dispatchEvent(new CustomEvent('set-amount', {
                detail: calculateTotal()
            }));
        }

        // Remove Payment
        function removePaidAmount() {
            busy();
            $.get('{{ route('pos.removePaidAmount') }}', function(data){
                if(data.success){
                    $('#order-confirmation').html(data.view);
                    // updateCart(data.view);
                    AIZ.plugins.notify('success', data.message || '{{ ('Paid amount removed successfully') }}');
                }else{
                    AIZ.plugins.notify('danger', data.message || '{{ ('Something went wrong') }}');
                }
                free();
            });
        }

        function calculateTotal(){
            let total = parseFloat($('.final-total-amount').data('total'));
            return total;
        }
    </script>
@endsection
