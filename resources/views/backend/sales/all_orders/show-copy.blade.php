@extends('backend.layouts.app')

@section('content')
{{-- @dd($order->toArray()) --}}
<div class="card">
    <div class="card-header">
        <h1 class="h2 fs-16 mb-0">{{ ('Order Details') }}</h1>
        @if ($order->delivery_status == 'processing')
            <div class="ml-auto">
                <div class="d-flex justify-content-center align-items-center">
                    <h5 id="timer">00:00:00</h5>
                    <button id="unlockAndExit" class="ml-2 btn btn-soft-secondary btn-icon btn-circle btn-sm" title="Unlock And Exit">
                        <i class="las la-lock-open"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-7">
                <div class="row gutters-5">
                    <div class="col text-center text-md-left">
                    </div>
                    @php
                    $delivery_status = $order->delivery_status;
                    $payment_status = $order->payment_status;
                    //dd($order->id);
                    @endphp

                    <!--Assign Delivery Boy-->
                    @if (addon_is_activated('delivery_boy'))
                        <div class="col-md-3 ml-auto">
                            <label for="assign_deliver_boy">{{ ('Assign Deliver Boy')}}</label>
                            @if($delivery_status == 'pending' || $delivery_status == 'confirmed' || $delivery_status == 'picked_up')
                            <select class="form-control aiz-selectpicker" data-live-search="true" data-minimum-results-for-search="Infinity" id="assign_deliver_boy">
                                <option value="">{{ ('Select Delivery Boy')}}</option>
                                @foreach($delivery_boys as $delivery_boy)
                                <option value="{{ $delivery_boy->id }}" @if($order->assign_delivery_boy == $delivery_boy->id) selected @endif>
                                    {{ $delivery_boy->name }}
                                </option>
                                @endforeach
                            </select>
                            @else
                                <input type="text" class="form-control" value="{{ optional($order->delivery_boy)->name }}" disabled>
                            @endif
                        </div>
                    @endif

                    <div class="col-md-3 ml-auto">
                        <label for=update_payment_status"">{{ ('Payment Status')}}</label>
                        <select class="form-control aiz-selectpicker"  data-minimum-results-for-search="Infinity" id="update_payment_status">
                            <option value="unpaid" @if ($payment_status == 'unpaid') selected @endif>{{ ('Unpaid')}}</option>
                            <option value="paid" @if ($payment_status == 'paid') selected @endif>{{ ('Paid')}}</option>
                            <option value="partial" @if ($payment_status == 'partial') selected @endif>{{ ('Partial')}}</option>
                        </select>
                    </div>
                    <div class="col-md-3 ml-auto">
                        <label for=update_delivery_status"">{{ ('Delivery Status')}}</label>
                        @if($delivery_status != 'delivered' && $delivery_status != 'cancelled')
                            <select class="form-control aiz-selectpicker"  data-minimum-results-for-search="Infinity" id="update_delivery_status">
                                @if($order->delivery_status == 'pending')
                                    <option value="pending" @if ($delivery_status == 'pending') selected @endif>{{ ('Pending')}}</option>
                                    <option value="processing" @if ($delivery_status == 'processing') selected @endif>{{ ('Processing')}}</option>
                                @else
                                    <option value="pending" @if ($delivery_status == 'pending') selected @endif>{{ ('Pending')}}</option>
                                    <option value="processing" @if ($delivery_status == 'processing') selected @endif>{{ ('Processing')}}</option>
                                    <option value="confirmed" @if ($delivery_status == 'confirmed') selected @endif>{{ ('Confirmed')}}</option>
                                    <option value="picked_up" @if ($delivery_status == 'picked_up') selected @endif>{{ ('Picked Up')}}</option>
                                    <option value="on_the_way" @if ($delivery_status == 'on_the_way') selected @endif>{{ ('On The Way')}}</option>
                                    <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>{{ ('Delivered')}}</option>
                                    <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>{{ ('Cancel')}}</option>
                                @endif
                            </select>
                        @else
                            <input type="text" class="form-control" value="{{ $delivery_status }}" disabled>
                        @endif
                    </div>
                </div>
                <div class="row gutters-5 mt-5">
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
                            @if(json_decode($order->shipping_address)->postal_code!='')
                            Postal Code: {{ json_decode($order->shipping_address)->postal_code }}<br>
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
                        {!! order_payment_status($order) !!}
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
                                        @if($delivery_status == 'delivered')
                                        <span class="badge badge-inline badge-success">{{ (ucfirst(str_replace('_', ' ', $delivery_status))) }}</span>
                                        @else
                                        <span class="badge badge-inline badge-info">{{ (ucfirst(str_replace('_', ' ', $delivery_status))) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-main text-bold">{{ ('Order Date')}}	</td>
                                    <td class="text-right">{{ date('d-m-Y h:i A', $order->date) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-main text-bold">
                                        {{ ('Total amount')}}
                                    </td>
                                    <td class="text-right">
                                        {{ single_price($order->grand_total) }}
                                    </td>
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
                <hr class="new-section-sm bord-no">
                <div class="row">
                    <div class="col-lg-12 table-responsive">
                        <table class="table table-bordered aiz-table invoice-summary">
                            <thead>
                                <tr class="bg-trans-dark">
                                    <th data-breakpoints="lg" class="min-col">#</th>
                                    <th width="10%" class="text-center">{{ ('Photo')}}</th>
                                    <th class="text-uppercase">{{ ('Description')}}</th>
                                    {{-- <th data-breakpoints="lg" class="text-uppercase">{{ ('Delivery Type')}}</th> --}}
                                    <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{ ('Qty')}}</th>
                                    <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{ ('Price')}}</th>
                                    <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{ ('Total')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->orderDetails as $key => $orderDetail)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td class="text-center">
                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                            <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                        @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                            <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                        @else
                                            <strong>{{ ('N/A') }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                            <strong><a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank" class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
                                            <small>{{ $orderDetail->variation }}</small>
                                        @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                            <strong><a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank" class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
                                        @else
                                            <strong>{{ ('Product Unavailable') }}</strong>
                                        @endif
                                    </td>
                                    {{-- <td>
                                        @if ($orderDetail->shipping_type != null && $orderDetail->shipping_type == 'home_delivery')
                                        {{ ('Home Delivery') }}
                                        @elseif ($orderDetail->shipping_type == 'pickup_point')

                                        @if ($orderDetail->pickup_point != null)
                                        {{ $orderDetail->pickup_point->getTranslation('name') }} ({{ ('Pickup Point') }})
                                        @else
                                        {{ ('Pickup Point') }}
                                        @endif
                                        @endif
                                    </td> --}}
                                    <td class="text-center">
                                        {{-- @if($orderDetail->quantity > 1)
                                            <span class="rounded-circle bg-danger" style="font-size: 23px; width: 30px; height: 30px; display: flex; justify-content: center; text-align: center; margin: 0 auto; padding: 5px; line-height: 1;color: white;">{{ $orderDetail->quantity }}</span>
                                        @else --}}
                                            {{ $orderDetail->quantity }}
                                        {{-- @endif --}}
                                    </td>
                                    <td class="text-center">{{ single_price($orderDetail->price/$orderDetail->quantity) }}</td>
                                    <td class="text-center">{{ single_price($orderDetail->price) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="clearfix float-left">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ ('Sub Total')}} :</strong>
                                </td>
                                <td>
                                    {{ single_price($order->orderDetails->sum('price')) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ ('Tax')}} :</strong>
                                </td>
                                <td>
                                    {{ single_price($order->orderDetails->sum('tax')) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ ('Shipping')}} :</strong>
                                </td>
                                <td>
                                    {{ single_price($order->orderDetails->sum('shipping_cost')) }}
                                </td>
                            </tr>
                            @if($order->coupon_discount>0 || $order->reward_point_discount>0)
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ ('Discount')}} @if(@$order->orderDetails[0]->coupon_code!=NULL) ({{ $order->orderDetails[0]->coupon_code }}) @endif :</strong>
                                </td>
                                <td>
                                    {{ single_price($order->coupon_discount) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ ('Reward point discount')}} @if(@$order->orderDetails[0]->reward_point_discount!=NULL) ({{ $order->orderDetails[0]->reward_point_discount }}) @endif :</strong>
                                </td>
                                <td>
                                    {{ single_price($order->reward_point_discount) }}
                                </td>
                            </tr>
                            @endif


                            @php
                                $paidAmount = $order->payments?->sum('amount') ?? 0;
                                $total = $order->orderDetails->sum('price') + $order->orderDetails->sum('tax') + $order->orderDetails->sum('shipping_cost') - ($order->coupon_discount + $order->reward_point_discount + $paidAmount);
                            @endphp
                            @if($order->payment_status != 'unpaid')
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ ('Paid Amount')}} :</strong>
                                </td>
                                <td>
                                    {{ single_price($paidAmount) }} (-)
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ ('NET TOTAL')}} :</strong>
                                </td>
                                <td class="text-muted h5">
                                    {{ single_price($total) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-right no-print">
                        @if(Auth::user()->user_type == 'admin' && $order->delivery_status == 'processing')
                            <a href="{{route('invoice.edit', $order->id)}}" type="button" class="btn btn-icon btn-soft-success"><i class="las la-edit"></i></a>
                        @endif
                        <a href="{{ route('invoice.download', $order->id) }}" type="button" class="btn btn-icon btn-light" target="_blank"><i class="las la-print"></i></a>
                        @if($order->payment_status != 'unpaid' && $order->payments->isNotEmpty())
                            <a href="javascript:void(0);" onclick="showPayHistory()"><i class="las la-eye"></i> View Payment Histroy</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="row" id="sortable">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0 h6">{{ ('Call Logs') }}</h5>
                                @if($order->delivery_status == 'processing')
                                    <div class="ml-auto">
                                        <span id="call-duration">00:00:00</span>
                                        <a role="button" id="start-call-timer" class="btn btn-soft-secondary btn-icon btn-sm btn-circle" title="{{ ('Start Call') }}">
                                            <i class="las la-tty"></i>
                                        </a>
                                        <a role="button" id="end-call-timer" class="btn btn-soft-danger btn-icon btn-sm btn-circle" title="{{ ('End Call') }}" style="display: none;">
                                            <i class="las la-phone-volume"></i>
                                        </a>
                                        <a role="button" id="create-call-log" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ ('Add Call Log') }}">
                                            <i class="las la-plus"></i>
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12 table-responsive">
                                        <table class="table table-bordered aiz-table invoice-summary">
                                            <thead>
                                                <tr class="bg-trans-dark">
                                                    <th data-breakpoints="lg" class="min-col">#</th>
                                                    <th class="">{{ ('Status')}}</th>
                                                    <th class="">{{ ('Note')}}</th>
                                                    <th data-breakpoints="lg" class="min-col">{{ ('Duration')}}</th>
                                                    <th width="10%" class="text-center">{{ ('Added By')}}</th>
                                                    <th width="10%" class="text-center">{{ ('Action')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($order->callLogs as $key => $callLog)
                                                @php
                                                    $data = [
                                                            'status' => $callLog->status ? ucwords(str_replace('_', ' ', $callLog->status)) : 'N/A',
                                                            'note' => ucfirst($callLog->note),
                                                            'duration' => $callLog->duration ? $callLog->duration.' '.translate(Str::plural('minute', $callLog->duration)) : 'N/A',
                                                            'created_at' => $callLog->created_at->format('d-m-Y h:i A'),
                                                            'user' => $callLog->user?->name ?? 'N/A',
                                                        ];
                                                @endphp
                                                <tr>
                                                    <td>{{ $key+1 }}</td>
                                                    <td>
                                                        {{ $callLog->status ? ucwords(str_replace('_', ' ', $callLog->status)) : 'N/A' }}
                                                    </td>
                                                    <td>
                                                        {{ Str::limit(ucfirst($callLog->note), 25) }}
                                                    </td>
                                                    <td>
                                                        @if($callLog->duration != null)
                                                            {{ $callLog->duration }} {{ (Str::plural('min', $callLog->duration)) }}
                                                        @else
                                                            {{ ('N/A') }}
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($callLog->user != null)
                                                            {{ $callLog->user->name . ' at ' . $callLog->created_at->format('d-m-Y h:i A') }}
                                                        @else
                                                            {{ ('N/A') }}
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <a role="button" class="view-btn btn btn-soft-info btn-icon btn-circle btn-sm" title="{{ ('View') }}" data-log="{{ json_encode($data) }}">
                                                            <i class="las la-info-circle"></i>
                                                        </a>
                                                        @if($callLog->user != null && $callLog->user->id == auth()->user()->id)
                                                        <a href="#"
                                                            class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                                            data-href="{{ route('call-logs.destroy', $callLog->id) }}"
                                                            title="{{ ('Delete') }}">
                                                            <i class="las la-trash"></i>
                                                        </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0 h6">{{ ('Recent Orders') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="accordion" id="accordionExample">
                                            @foreach ($recentOrders as $index => $recentOrder)
                                            <div class="card">
                                              <div class="card-header" id="heading-{{ $index }}">
                                                    <h5 class="mb-0">
                                                        <button class="btn btn-link {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-toggle="collapse" data-target="#collapse-{{ $index }}" aria-expanded="true" aria-controls="collapse-{{ $index }}">
                                                            Order #{{ $recentOrder['code'] }} - {{ single_price($recentOrder->grand_total) }}
                                                        </button>
                                                        {!! order_status_badge($recentOrder) !!} {!! payment_status_badge($recentOrder) !!}
                                                    </h5>
                                                    <div class="float-right">
                                                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('all_orders.show', encrypt($recentOrder->id))}}" target="_blank" title="{{ ('View Invoice') }}"><i class="las la-eye"></i></a>
                                                        @if(Auth::user()->user_type == 'admin' && $order->delivery_status == 'processing' && $recentOrder->delivery_status == 'processing')
                                                            <a href="{{route('invoice.edit', $recentOrder->id)}}" title="{{ ('Edit') }}" class="btn btn-icon btn-soft-success btn-circle btn-sm" target="_blank"><i class="las la-edit"></i></a>
                                                        @endif
                                                    </div>
                                              </div>

                                              <div id="collapse-{{ $index }}" class="collapse {{ $index == 0 ? 'show' : '' }}" aria-labelledby="heading-{{ $index }}" data-parent="#accordionExample">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <ul class="list-unstyled">
                                                                <li class="mb-1"><strong>Order #</strong> <span class="text-info">{{ $recentOrder['code'] }}</span></li>
                                                                <li class="mb-1"><strong>Order Date:</strong> {{ date('d-m-Y h:i A', $recentOrder->date) }}</li>
                                                                @if($recentOrder['payment_status'] == 'paid')
                                                                <li class="mb-1"><strong>Payment Method:</strong> {{ strtoupper($recentOrder['payment_type']) }}</li>
                                                                @endif
                                                                <li class="mb-1"><strong>Order Status:</strong> {!! order_status_badge($recentOrder) !!}</li>
                                                                <li class="mb-1"><strong>Payment Status:</strong> {!! payment_status_badge($recentOrder) !!}</li>
                                                                <li class="mb-1"><strong>Order Source:</strong>
                                                                    <span class="badge badge-inline badge-success">
                                                                        {{strtoupper($recentOrder->order_source)}}
                                                                    </span>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <ul class="list-unstyled">
                                                                @php
                                                                    $shipping_address = json_decode($recentOrder->shipping_address);
                                                                @endphp
                                                                <li class="mb-1"><strong>Name:</strong> {{ $shipping_address->name }}</li>
                                                                <li class="mb-1"><strong>Phone:</strong> {{ $shipping_address->phone }}</li>
                                                                @if($shipping_address->email!='')
                                                                    <li class="mb-1"><strong>Email:</strong> {{ $shipping_address->email }}</li>
                                                                @endif
                                                                <li class="mb-1"><strong>Address:</strong>
                                                                    {{ $shipping_address->address }}
                                                                    City: {{ $shipping_address->city }},
                                                                    Area: {{ $shipping_address->area }},
                                                                    @if($shipping_address->postal_code!='')
                                                                    Postal Code: {{ $shipping_address->postal_code }}<br>
                                                                    @endif
                                                                    {{ $shipping_address->country }}
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    {{-- <hr class="new-section-sm bord-no"> --}}
                                                    <div class="row mt-3">
                                                        <div class="col-lg-12 table-responsive">
                                                            <table class="table table-bordered aiz-table invoice-summary">
                                                                <thead>
                                                                    <tr class="bg-trans-dark">
                                                                        <th data-breakpoints="lg" class="min-col">#</th>
                                                                        <th width="10%" class="text-center">{{ ('Photo')}}</th>
                                                                        <th class="text-uppercase">{{ ('Description')}}</th>
                                                                        <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{ ('Qty')}}</th>
                                                                        <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{ ('Price')}}</th>
                                                                        <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{ ('Total')}}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($recentOrder->orderDetails as $key => $orderDetail)
                                                                    <tr>
                                                                        <td>{{ $key+1 }}</td>
                                                                        <td class="text-center">
                                                                            @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                                                                <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                                                            @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                                                                <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                                                            @else
                                                                                <strong>{{ ('N/A') }}</strong>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                                                                <strong><a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank" class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
                                                                                <small>{{ $orderDetail->variation }}</small>
                                                                            @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                                                                <strong><a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank" class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
                                                                            @else
                                                                                <strong>{{ ('Product Unavailable') }}</strong>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center">
                                                                            {{ $orderDetail->quantity }}
                                                                        </td>
                                                                        <td class="text-center">{{ single_price($orderDetail->price/$orderDetail->quantity) }}</td>
                                                                        <td class="text-center">{{ single_price($orderDetail->price) }}</td>
                                                                    </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix float-right">
                                                        <table class="table">
                                                            <tbody>
                                                                <tr>
                                                                    <td>
                                                                        <strong class="text-muted">{{ ('Sub Total')}} :</strong>
                                                                    </td>
                                                                    <td class="text-muted ">
                                                                        {{ single_price($recentOrder->orderDetails->sum('price')) }}
                                                                    </td>
                                                                </tr>
                                                                @if($recentOrder->coupon_discount>0 || $recentOrder->reward_point_discount>0)
                                                                <tr>
                                                                    <td>
                                                                        <strong class="text-muted">{{ ('Coupon')}} @if(@$recentOrder->orderDetails[0]->coupon_code!=NULL) ({{ $recentOrder->orderDetails[0]->coupon_code }}) @endif :</strong>
                                                                    </td>
                                                                    <td class="text-muted ">
                                                                        {{ single_price($recentOrder->coupon_discount) }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <strong class="text-muted">{{ ('Reward point discount')}} @if(@$recentOrder->orderDetails[0]->reward_point_discount!=NULL) ({{ $recentOrder->orderDetails[0]->reward_point_discount }}) @endif :</strong>
                                                                    </td>
                                                                    <td class="text-muted ">
                                                                        {{ single_price($recentOrder->reward_point_discount) }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <strong class="text-muted">{{ ('GRAND TOTAL')}} :</strong>
                                                                    </td>
                                                                    <td class="text-muted h5">
                                                                        {{ single_price($recentOrder->orderDetails->sum('price') - ($recentOrder->coupon_discount + $recentOrder->reward_point_discount)) }}
                                                                    </td>
                                                                </tr>
                                                                @endif
                                                                <tr>
                                                                    <td>
                                                                        <strong class="text-muted">{{ ('Tax')}} :</strong>
                                                                    </td>
                                                                    <td class="text-muted ">
                                                                        {{ single_price($recentOrder->orderDetails->sum('tax')) }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <strong class="text-muted">{{ ('Shipping')}} :</strong>
                                                                    </td>
                                                                    <td class="text-muted ">
                                                                        {{ single_price($recentOrder->orderDetails->sum('shipping_cost')) }}
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>
                                                                        <strong class="text-muted">{{ ('NET TOTAL')}} :</strong>
                                                                    </td>
                                                                    <td class="h6">
                                                                        {{ single_price($recentOrder->grand_total) }}
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    @if($recentOrder->isLocked())
                                                    <div class="alert alert-warning mt-3">
                                                        <i class="fas fa-lock"></i> This order is locked by seller (ID: {{ $recentOrder['locked_by'] }}) at {{ $recentOrder['locked_at'] }}
                                                    </div>
                                                    @endif
                                                </div>
                                              </div>
                                            </div>
                                            @endforeach
                                          </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('modal')
@include('modals.partial_payment_history_modal', ['order' => $order, 'payments' => $order->payments])
@if($order->delivery_status == 'processing')
    @include('modals.delete_modal')
    <div id="call-log-create-modal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ ('New Call Log') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <form id="order-call-logs-create-form" action="{{ route('call-logs.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="reference" value="order">
                        <input type="hidden" name="reference_id" value="{{ $order->id }}">
                        <div class="form-group mb-3">
                            <label for="status">{{ ('Status') }}</label>
                            <div class="input-group">
                                <select class="form-control" name="status" id="status">
                                    <option value="">{{ ('Select Status') }}</option>
                                    <option value="call_received" @if (old('status') == 'call_received') selected @endif>{{ ('Call Received') }}</option>
                                    <option value="no_response" @if (old('status') == 'no_response') selected @endif>{{ ('No Response') }}</option>
                                    <option value="order_hold" @if (old('status') == 'order_hold') selected @endif>{{ ('Order Hold') }}</option>
                                    <option value="call_me_later" @if (old('status') == 'call_me_later') selected @endif>{{ ('Call Me Later') }}</option>
                                </select>
                            </div>
                            <div class="text-danger" style="display: none" id="status_error"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="note">{{ ('Note') }}<span class="text-danger"> *</span></label>
                            <div class="input-group">
                                <textarea type="text" class="form-control" name="note" id="note" placeholder="{{ ('Note') }}" rows="3" required>{{ old('note') }}</textarea>
                            </div>
                            <div class="text-danger" style="display: none" id="note_error"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="duration">{{ ('Call Duration') }}</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="duration" id="duration" placeholder="{{ ('Duration in minutes e.g. 5 or 8.2') }}" value="{{ old('duration') }}">
                            </div>
                            <div class="text-danger" style="display: none" id="duration_error"></div>
                        </div>
                    </form>
                    <div class="form-group mb-3 text-right">
                        <button id="clear-btn" class="btn btn-secondary">{{ ('Clear') }}</button>
                        <button id="submit-btn" type="submit" class="btn btn-primary">{{ ('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div id="call-log-details-modal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ ('Call Log Details') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                {{-- Content --}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script type="text/javascript">
        let deliveryStatus = '{{ $order->delivery_status }}'; // Get the delivery status
        let callDuration = {{ old('duration', 0) }}; // Initialize call duration variable
        let checkInterval;
        let timerInterval;
        let callDurationInterval;
        let remainingTime = {{ $order->unlockIn() }};

        function showPayHistory(){
            $("#partial-payment-history-modal").modal("show");
        }

        $('#unlockAndExit').on('click', function() {
            $.ajax({
                url: '{{ route("orders.unlock", ":id") }}'.replace(':id', {{ $order->id }}),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect_url;
                    } else {
                        AIZ.plugins.notify('danger', response.message);
                    }
                },
                error: function() {
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
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
                const formattedTime =
                    `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                $('#call-duration').text(formattedTime);
            }, 1000);
        });

        // End Call button click handler
        $('#end-call-timer').click(function() {
            clearInterval(callDurationInterval);  // Stop the timer

            // Parse the displayed time (HH:MM:SS)
            const timeText = $('#call-duration').text();
            const [hours, minutes, seconds] = timeText.split(':').map(Number);

            // Calculate total minutes in decimal format (e.g., 1.12 for 01:12, 115.22 for 01:55:22)
            const totalMinutes = (hours * 60) + minutes + (seconds / 100);
            callDuration = parseFloat(totalMinutes.toFixed(2));  // Store as float (e.g., 1.12, 115.22)

            // Reset UI
            $('#call-duration').text('00:00:00');
            $(this).hide();
            $('#start-call-timer').show();

            console.log('Call duration stored:', callDuration);  // Example: 1.12, 0.06, 115.22
        });

        // Function to format seconds into HH:MM:SS
        function formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            return [
                hours.toString().padStart(2, '0'),
                minutes.toString().padStart(2, '0'),
                secs.toString().padStart(2, '0')
            ].join(':');
        }

        // Function to update the timer display
        function updateTimer() {
            $('#timer').text(formatTime(remainingTime));

            if (remainingTime <= 60){
                $('#timer').css('color', 'red');
            } else {
                $('#timer').css('color', 'black');
            }
            if (remainingTime <= 1) {
                extendLock(); // Call extendLock when 1 second remains
            } else {
                remainingTime--;
            }
        }

        // Function to extend the lock
        function extendLock() {
            $.ajax({
                url: '{{ route("orders.extend-lock", ":id") }}'.replace(':id', {{ $order->id }}),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        remainingTime = response.unlock_in;
                        clearInterval(timerInterval);
                        timerInterval = setInterval(updateTimer, 1000);
                    } else {
                        // Lock expired, redirect or refresh
                        window.location.reload();
                    }
                },
                error: function() {
                    // Retry if there was an error
                    setTimeout(extendLock, 1000);
                }
            });
        }

        if(deliveryStatus == 'processing'){
            // Start the timer countdown
            timerInterval = setInterval(updateTimer, 1000);

            updateTimer();
        }

        function resetForm(){
            $('#note').val('');
            $('#duration').val('');
            $('#note_error').val('');
        }

        $('#create-call-log').on('click', function(){
            resetForm();
            if(!$('#end-call-timer').is(':hidden')){
                $('#end-call-timer').click();
            }
            $('#duration').val(callDuration);
            var modal = $('#call-log-create-modal');
            modal.modal('show');
        });

        $('#submit-btn').on('click', function(){
            var note = $('#note').val();
            var duration = $('#duration').val();

            if(note == ''){
                $('#note_error').text('{{ ('Note is required') }}').show();
                // return false;
            }

            $('#order-call-logs-create-form').submit();
        })

        $(document).on('click', '.view-btn', function(){
            // alert('clicked');
            var log = $(this).data('log');
            console.log(log);
            var modal = $('#call-log-details-modal');
            modal.find('.modal-body').html(`
                <p><strong>{{ ('Status') }}:</strong> ${log.status}</p>
                <p><strong>{{ ('Note') }}:</strong> ${log.note}</p>
                <p><strong>{{ ('Duration') }}:</strong> ${log.duration}</p>
                <p><strong>{{ ('Called By') }}:</strong> ${log.user} at ${log.created_at}</p>
            `);
            modal.modal('show');
        })

        $('#assign_deliver_boy').on('change', function(){
            var order_id = {{ $order->id }};
            var delivery_boy = $('#assign_deliver_boy').val();
            $.post('{{ route('orders.delivery-boy-assign') }}', {
                _token          :'{{ @csrf_token() }}',
                order_id        :order_id,
                delivery_boy    :delivery_boy
            }, function(data){
                if(data === 403){
                    AIZ.plugins.notify('danger', "{{ ('You can not edit this order') }}");
                    window.location.reload();
                }else{
                    AIZ.plugins.notify('success', '{{ ('Delivery boy has been assigned') }}');
                }
            });
        });

        $('#update_delivery_status').on('change', function(){
            var order_id = {{ $order->id }};
            var status = $('#update_delivery_status').val();
            $.post('{{ route('orders.update_delivery_status') }}', {
                _token:'{{ @csrf_token() }}',
                order_id:order_id,
                status:status
            }, function(data){
                if(data === 403){
                    AIZ.plugins.notify('danger', "{{ ('You can not edit this order') }}");
                    window.location.reload();
                }else{
                    AIZ.plugins.notify('success', '{{ ('Delivery status has been updated') }}');
                }
            });
        });

        $('#update_payment_status').on('change', function(){
            var order_id = {{ $order->id }};
            var status = $('#update_payment_status').val();
            $.post('{{ route('orders.update_payment_status') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,status:status}, function(data){
                if(data === 403){
                    AIZ.plugins.notify('danger', "{{ ('You can not edit this order') }}");
                    window.location.reload();
                }else{
                    AIZ.plugins.notify('success', '{{ ('Payment status has been updated') }}');
                }
            });
        });
    </script>
@endsection
