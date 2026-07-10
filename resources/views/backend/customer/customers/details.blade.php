@extends('backend.layouts.app')

@section('content')
<style>
    th{
        text-align: left;
        padding-right: 5px;
    }
    td{
        text-align: left;
    }
</style>
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="d-flex justify-content-between gutters-10">
        <div class="col-md-6">
            <div class="align-items-center">
                <div class="d-flex">
                    <div class="mr-2">
                        @if ($customer->avatar_original != null)
                            <img src="{{ uploaded_asset($customer->avatar_original) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';" width="40">
                        @else
                            <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="image rounded-circle" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';" width="40">
                        @endif
                    </div>
                    <h3 class="m-0 my-1 mr-2">{{ ($customer->name)}}</h3>
                    @if($customer->customer_group != NULL)
                    <div class="mt-1 text-center text-success justify-content-center" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="{{ $customer->customer_group->group->group_name }}" data-content="{{ @$customer->customer_group->group->message }}">
                        @if($customer->customer_group->group->group_image != '')
                            <img width="30" title="{{ $customer->customer_group->group->group_name }}" src="{{ uploaded_asset($customer->customer_group->group->group_image)}}"  alt="{{ $customer->customer_group->group->group_name }}">
                        @else
                            {!! $customer->customer_group->group->group_icon !!}
                        @endif
                    </div>
                    @else
                    <div class="mt-1 text-center text-success justify-content-center" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="{{ $customer->customer_group->group->group_name }}" data-content="{{ @$customer->customer_group->group->message }}">
                    @if($customer->customer_group->group->group_image != '')
                            <img width="30" src="{{ uploaded_asset(@$customer->customer_group->group->group_image)}}" title="{{ $customer->customer_group->group->group_name }}" alt="{{ $customer->customer_group->group->group_name }}">
                        @else
                            {!! $customer->customer_group->group->group_icon !!}
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @if($customer->addresses != null)
                @php
                    $address = $customer->addresses->where('set_default', 1)->first() ?? $customer->addresses->first();
                @endphp
                @if($address != null)
                    <ul class="mt-2 list-unstyled mb-0">
                        <li><span>{{ ('Address') }} : {{ $address->address }}</span></li>
                        <li><span>{{ ('Country') }} : {{ $address->country?->name ?? '' }}</span></li>
                        <li><span>{{ ('State') }} : {{ $address->state?->name ?? '' }}</span></li>
                        <li><span>{{ ('City') }} : {{ $address->city?->name ?? '' }}</span></li>
                        <li><span>{{ ('Postal Code') }} : {{ $address->postal_code }}</span></li>
                        <li><span>{{ ('Phone') }} : {{ $address->phone }}</span></li>
                    </ul>
                @endif
            @endif
        </div>
        <div class="col-md-6 d-flex justify-content-end">
            <table>
                <tbody>
                    <tr>
                        <th>Gender: </th>
                        <td>{{ ucfirst($customer->gender ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <th>Email: </th>
                        <td>{{ isset($customer->email) ? $customer->email : 'NULL'}}</td>
                    </tr>
                    <tr>
                        <th>Date of birth: </th>
                        <td>{{ is_null($customer->date_of_birth) ? 'NULL' : \Carbon\Carbon::parse($customer->date_of_birth)->format('j M, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Balance: </th>
                        <td>{{single_price($customer->balance)}}</td>
                    </tr>
                    <tr>
                        <th>Recent Login: </th>
                        <td>
                            @php
                            if($customer->recent_login != NULL){
                                $mydate = $customer->recent_login;
                                $result = Carbon::createFromFormat('Y-m-d H:i:s', $mydate)->diffForHumans('now');
                                echo $result;
                            }
                            @endphp
                        </td>
                    </tr>
                    <tr>
                        <th>Customer Group: </th>
                        <td>{{ $customer->customer_group->group->group_name }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row gutters-10">
    <div class="col-6 col-md-2">
        <div class="bg-grad-1 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                @php
                    $user_id = $customer->id;
                    $deliveredCount = \App\Models\Order::where('user_id', $user_id)->where('delivery_status', 'delivered')->get()->count();
                @endphp
                @if($deliveredCount > 0)
                <div class="h3 fw-700">
                    {{ @$deliveredCount }} {{ ('Order(s)') }}
                </div>
                @else
                <div class="h3 fw-700">
                    0 {{ ('Order') }}
                </div>
                @endif
                <div class="opacity-50">{{ ('Delivered') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                @php
                    $user_id = $customer->id;
                    $pendingCount = \App\Models\Order::where('user_id', $user_id)->where('delivery_status', 'pending')->get()->count();
                @endphp
                @if($pendingCount > 0)
                <div class="h3 fw-700">
                    {{ @$pendingCount }} {{ ('Order(s)') }}
                </div>
                @else
                <div class="h3 fw-700">
                    0 {{ ('Order') }}
                </div>
                @endif
                <div class="opacity-50">{{ ('Pending') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                @php
                    $user_id = $customer->id;
                    $confirmedCount = \App\Models\Order::where('user_id', $user_id)->where('delivery_status', 'confirmed')->get()->count();
                @endphp
                @if($confirmedCount > 0)
                <div class="h3 fw-700">
                    {{ @$confirmedCount }} {{ ('Order(s)') }}
                </div>
                @else
                <div class="h3 fw-700">
                    0 {{ ('Order') }}
                </div>
                @endif
                <div class="opacity-50">{{ ('Confirmed') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="bg-grad-1 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                @php
                    $user_id = $customer->id;
                    $picked_upCount = \App\Models\Order::where('user_id', $user_id)->where('delivery_status', 'picked_up')->get()->count();
                @endphp
                @if($picked_upCount > 0)
                <div class="h3 fw-700">
                    {{ @$picked_upCount }} {{ ('Order(s)') }}
                </div>
                @else
                <div class="h3 fw-700">
                    0 {{ ('Order') }}
                </div>
                @endif
                <div class="opacity-50">{{ ('Picked Up Order') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                @php
                    $user_id = $customer->id;
                    $on_the_wayCount = \App\Models\Order::where('user_id', $user_id)->where('delivery_status', 'on_the_way')->get()->count();
                @endphp
                @if($on_the_wayCount > 0)
                <div class="h3 fw-700">
                    {{ @$on_the_wayCount }} {{ ('Order(s)') }}
                </div>
                @else
                <div class="h3 fw-700">
                    0 {{ ('Order') }}
                </div>
                @endif
                <div class="opacity-50">{{ ('On the way') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                @php
                    $user_id = $customer->id;
                    $cancelledCount = \App\Models\Order::where('user_id', $user_id)->where('delivery_status', 'cancelledCount')->get()->count();
                @endphp
                @if($cancelledCount > 0)
                <div class="h3 fw-700">
                    {{ @$cancelledCount }} {{ ('Order(s)') }}
                </div>
                @else
                <div class="h3 fw-700">
                    0 {{ ('Order') }}
                </div>
                @endif
                <div class="opacity-50">{{ ('Cancelled') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row gutters-10">
    <div class="col-md-4" onclick="products();" style="cursor: pointer;">
        <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                @php
                    $allOrders = \App\Models\Order::where('user_id', $user_id)->get();
                    $total = 0;
                    foreach ($allOrders as $key => $order) {
                        $total += count($order->orderDetails);
                    }
                @endphp
                <div class="h3 fw-700">{{ $total }} {{ ('Product(s)') }}</div>
                <div class="d-flex justify-content-between">
                    <div class="opacity-50">{{ ('ordered') }}</div>
                    <div class="opacity-50">{{ ('View Details') }} <i class="las la-arrow-circle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4" onclick="wishlists();" style="cursor: pointer;">
        <div class="bg-grad-1 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                <div class="h3 fw-700">{{ count($customer->wishlists)}} {{ ('Product(s)') }}</div>
                <div class="d-flex justify-content-between">
                    <div class="opacity-50">{{ ('in wishlist') }}</div>
                    <div class="opacity-50">{{ ('View Details') }} <i class="las la-arrow-circle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4" onclick="carts();" style="cursor: pointer;">
        <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                @php
                    $user_id = $customer->id;
                    $cart = \App\Models\Cart::where('user_id', $user_id)->get();
                @endphp
                @if(count($cart) > 0)
                <div class="h3 fw-700">
                    {{ count($cart) }} {{ ('Product(s)') }}
                </div>
                @else
                <div class="h3 fw-700">
                    0 {{ ('Product') }}
                </div>
                @endif
                <div class="d-flex justify-content-between">
                    <div class="opacity-50">{{ ('in cart') }}</div>
                    <div class="opacity-50">{{ ('View Details') }} <i class="las la-arrow-circle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    @if(get_setting('reward_point_system') == 1)
        <div class="col-md-4" onclick="reward_point();" style="cursor: pointer;">
            <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
                <div class="p-3">

                    @if($customer->point_balance > 0)
                        <div class="h3 fw-700">
                            {{ number_format($customer->point_balance) }} {{ ('Points') }}
                        </div>
                    @else
                        <div class="h3 fw-700">
                            0 {{ ('point') }}
                        </div>
                    @endif
                    <div class="d-flex justify-content-between">
                        <div class="opacity-50">{{ ('left') }}</div>
                        <div class="opacity-50">{{ ('View Details') }} <i class="las la-arrow-circle-right"></i></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>


<div class="card">
    <form class="" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col text-center text-md-left">
                <h5 class="mb-md-0 h6">{{ ('Purchase History') }}</h5>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ ('Type Order code & hit Enter') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg">{{ ('Order Date')}}</th>
                    <th data-breakpoints="lg">{{ ('Order Code')}}</th>
                    <th data-breakpoints="lg">{{ ('Num. of Products')}}</th>
                    <th data-breakpoints="lg">{{ ('Amount')}}</th>
                    <th data-breakpoints="lg">{{ ('Delivery Status')}}</th>
                    <th data-breakpoints="lg">{{ ('Payment Method')}}</th>
                    <th data-breakpoints="lg">{{ ('Payment Status')}}</th>
                    @if (addon_is_activated('refund_request'))
                        <th>{{ ('Refund')}}</th>
                    @endif
                    <th data-breakpoints="lg">{{ ('Order Source')}}</th>
                    <th class="text-right" width="15%">{{ ('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td> {{ date('d-m-Y', $order->date) }} </td>
                        <td> {{ $order->code }} </td>
                        <td> {{ $order->orderDetails->count() }} </td>
                        <td> {{ single_price($order->grand_total) }} </td>
                        <td>
                            @php
                                $status = $order->delivery_status;
                            @endphp
                            {{ (ucfirst(str_replace('_', ' ', $status))) }}
                        </td>
                        <td>
                            {{ (ucfirst(str_replace('_', ' ', $order->payment_type))) }}
                        </td>
                        <td>
                            @if ($order->payment_status == 'paid')
                            <span class="badge badge-inline badge-success">{{ ('Paid')}}</span>
                            @else
                            <span class="badge badge-inline badge-danger">{{ ('Unpaid')}}</span>
                            @endif
                        </td>

                        @if (addon_is_activated('refund_request'))
                            <td>
                                @if (count($order->refund_requests) > 0)
                                    {{ count($order->refund_requests) }} {{ ('Refund') }}
                                @else
                                    {{ ('No Refund') }}
                                @endif
                            </td>
                        @endif

                        <td>
                            <span class="badge badge-inline badge-success">{{$order->order_source}}</span>
                        </td>

                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('all_orders.show', encrypt($order->id))}}" target="_blank" title="{{ ('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ route('invoice.download', $order->id) }}" target="_blank" title="{{ ('Download Invoice') }}">
                                <i class="las la-download"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('orders.destroy', $order->id)}}" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<!-- Wishlists Modal -->
<div class="modal fade" id="wishlists">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6">{{ ('Wishlist')}}</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
            <div class="row gutters-5">
            @forelse ($wishlists as $key => $wishlist)
                @if ($wishlist->product != null)
                    <div class="col-md-4 col-6" id="wishlist_{{ $wishlist->id }}">
                        <div class="card mb-2 shadow-sm">
                            <div class="card-body">
                                <a href="{{ to_frontend(route('product', $wishlist->product->slug)) }}" class="d-block mb-3" target="_blank">
                                    <img src="{{ uploaded_asset($wishlist->product->thumbnail_img) }}" class="img-fit h-140px h-md-200px" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                </a>

                                <h5 class="fs-14 mb-0 lh-1-5 fw-600 text-truncate-2">
                                    <a href="{{ to_frontend(route('product', $wishlist->product->slug)) }}" class="text-reset" target="_blank">{{ $wishlist->product->getTranslation('name') }}</a>
                                </h5>
                                <div class="rating rating-sm mb-1">
                                    {!! renderStarRating($wishlist->product->rating) !!}
                                </div>
                                <div class=" fs-14">
                                    @if(home_base_price($wishlist->product) != home_discounted_base_price($wishlist->product))
                                        <del class="opacity-60 mr-1">{{ home_base_price($wishlist->product) }}</del>
                                    @endif
                                        <span class="fw-600 text-primary">{{ home_discounted_base_price($wishlist->product) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="col">
                    <div class="text-center bg-white p-4 rounded shadow">
                        <img class="mw-100 h-200px" src="{{ static_asset('assets/img/nothing.svg') }}" alt="Image">
                        <h5 class="mb-0 h5 mt-3">{{ ("There isn't anything added yet")}}</h5>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="aiz-pagination">
            {{ $wishlists->links() }}
        </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- // Cart Modal -->
<div class="modal fade" id="carts">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6">{{ ('Cart')}}</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if( $carts && count($carts) > 0 )
                <div class="row">
                    <div class="col-12 mx-auto">
                        <div class="shadow-sm bg-white p-3 p-lg-0 rounded text-left">
                            <div class="mb-4">
                                <div class="row d-none d-lg-flex border-bottom mb-3 p-3 bg-primary text-white mx-0">
                                    <div class="col-md-5 fw-600">{{ ('Product')}}</div>
                                    <div class="col fw-600">{{ ('Price')}}</div>
                                    <div class="col fw-600">{{ ('Tax')}}</div>
                                    <div class="col fw-600">{{ ('Total')}}</div>
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
                                            if(home_price($product) != home_discounted_price($product)){
                                                $cartItem['price'] = home_discounted_price($product);
                                            }else{
                                                $cartItem['price'] = home_discounted_price($product);
                                            }
                                            $absolutePrice = (int) preg_replace("/[^A-Za-z0-9\.]/", "", $cartItem['price'] );
                                            $product_stock = $product->stocks->where('variant', $cartItem['variation'])->first();
                                            $flash_deal_check = check_flash_deal_product($product);
                                            $total = $total + ($absolutePrice + $cartItem['tax']) * $cartItem['quantity'];
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

                                        //     echo count($minorderamontarray);
                                        // dd(max($minorderamontarray));
                                        @endphp
                                        <li class="list-group-item px-0 px-lg-3">
                                            <div class="row gutters-5">
                                                <div class="col-lg-5 d-flex">
                                                    <span class="mr-2 ml-0">
                                                        <a href="{{ to_frontend(route('product', $product->slug)) }}" target="_blank">
                                                        <img
                                                            src="{{ uploaded_asset($product->thumbnail_img) }}"
                                                            class="img-fit size-60px rounded cart_product_img"
                                                            alt="{{ $product->getTranslation('name')  }}"
                                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                        >
                                                        </a>
                                                    </span>
                                                    <a href="{{ to_frontend(route('product', $product->slug)) }}" target="_blank">
                                                        <span class="fs-14 opacity-60">{{ $product_name_with_choice }} <br> {{ $message }}</span>
                                                    </a>
                                                </div>

                                                <div class="col-lg col-4 order-1 order-lg-0 my-3 my-lg-0">
                                                    <span class="opacity-60 fs-12 d-block d-lg-none">{{ ('Price')}}</span>
                                                    <span class="fw-600 fs-16">{{ single_price($absolutePrice) }}</span>
                                                </div>
                                                <div class="col-lg col-4 order-2 order-lg-0 my-3 my-lg-0">
                                                    <span class="opacity-60 fs-12 d-block d-lg-none">{{ ('Tax')}}</span>
                                                    <span class="fw-600 fs-16">{{ single_price($cartItem['tax']) }}</span>
                                                </div>
                                                <div class="col-lg col-4 order-3 order-lg-0 my-3 my-lg-0">
                                                    <span class="opacity-60 fs-12 d-block d-lg-none">{{ ('Total')}}</span>
                                                    <span class="fw-600 fs-16 text-primary">{{ single_price(($absolutePrice + $cartItem['tax']) * $cartItem['quantity']) }}</span>
                                                </div>
                                            </div>
                                        </li>
                                        @php
                                            $message = '';
                                        @endphp
                                    @endforeach
                                </ul>
                            </div>

                            <div class="px-3 py-3 mb-2 border-top d-flex justify-content-between">
                                <span class="opacity-60 fs-15">{{ ('Subtotal')}}</span>
                                <span class="fw-600 fs-17">{{ single_price($total) }}</span>
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
                                    <h3 class="h4 fw-700">{{ ('This Customers cart is empty')}}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- // Products Modal -->
<div class="modal fade" id="products">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6">{{ ('Ordered Products')}}</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm aiz-table mb-0">
                    <thead>
                        <tr>
                            <th data-breakpoints="lg">{{ ('Order Date')}}</th>
                            <th data-breakpoints="lg">{{ ('Order Code')}}</th>
                            <th data-breakpoints="lg">{{ ('Product')}}</th>
                            <th data-breakpoints="lg">{{ ('Qty.')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            @foreach($order->orderDetails as $key => $orderDetail)
                            <tr>
                                <td> {{ date('d-m-Y', $order->date) }} </td>
                                <td> {{ $order->code }} </td>
                                <td>{{ $orderDetail->product->name ?? '' }} @if($orderDetail->variation != null) ({{ $orderDetail->variation }}) @endif</td>
                                <td class="">{{ @$orderDetail->quantity }}</td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
            </div>
        </div>
    </div>
</div>


<!-- // reward point Modal -->
<div class="modal fade" id="reward_point">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6">{{ ('Reward point history')}}</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if( count($rewardPointLogs) > 0 )
                <div class="row">
                    <div class="col-12 mx-auto">
                        @foreach ($rewardPointLogs as $rewardPointLog)

                            <div class="point-lister">
                                <div style="padding: 15px; @if($loop->odd) background:#ddd; @else  background:#f2f2f2; @endif">
                                    <div class="text">{{$rewardPointLog->activity_str}}</div>
                                    <div class="sub-text"><i>{{$rewardPointLog->created_at->diffForHumans()}}</i></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @else
                    <div class="row">
                        <div class="col-xl-8 mx-auto">
                            <div class="shadow-sm bg-white p-4 rounded">
                                <div class="text-center p-3">
                                    <i class="las la-frown la-3x opacity-60 mb-3"></i>
                                    <h3 class="h4 fw-700">{{ ('Your Reward points log is empty now')}}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
            </div>
        </div>
    </div>
</div>


@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        $(function() {
            $('[data-toggle="popover"]').popover()
        })
        function sort_orders(el){
            $('#sort_orders').submit();
        }
        function wishlists()
        {
            $('#wishlists').modal('show', {backdrop: 'static'});
        }
        function carts()
        {
            $('#carts').modal('show', {backdrop: 'static'});
        }
        function products()
        {
            $('#products').modal('show', {backdrop: 'static'});
        }
        function reward_point()
        {
            $('#reward_point').modal('show', {backdrop: 'static'});
        }
    </script>
@endsection
