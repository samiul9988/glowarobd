@extends('backend.layouts.app')
@php
    $role = Auth::user()->staff?->role?->name ?? '';
@endphp
@section('content')
{{-- @dd($order->callLogs->where('status', 'shipment_failed')->count()) --}}
<div class="row gutters-5">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <div class="row w-100">
                    <div class="col-4 d-flex flex-wrap align-items-center">
                        <div class="d-flex justify-content-start align-items-center">
                            <a href="{{ route('all_orders.status', $order->delivery_status) }}" class="mr-2 btn btn-soft-secondary btn-icon btn-circle btn-sm" title="Go To {{ ucfirst($order->delivery_status) }} Orders">
                                <i class="las la-long-arrow-alt-left"></i>
                            </a>
                            <h1 class="h2 fs-16 mb-0">{{ ('Order Details') }}</h1>
                        </div>
                    </div>
                    <div class="col-4 d-flex justify-content-center flex-wrap align-items-center">
                        <span class="h2 fs-16 mb-0">
                            <strong>{{ group_identity($order->user_id) }}</strong>
                        </span>
                    </div>
                    <div class="col-4 p-0">
                        @if (!$pendingReturnRequest && in_array(strtolower($order->delivery_status), ['processing']))
                            <div class="d-flex justify-content-end align-items-center">
                                <h5 id="timer" class="mb-0">00:00:00</h5>
                                <button id="unlockAndExit" class="ml-2 btn btn-soft-secondary btn-icon btn-circle btn-sm" title="Unlock And Exit">
                                    <i class="las la-lock-open"></i>
                                </button>
                                @if($order->payment_status != 'paid')
                                <button type="button" title="Add Payment" onclick="paynow()" class="ml-4 btn btn-outline-dark btn-styled btn-sm">{{ ('Pay Now') }}</button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(hasGiftItem($order))
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="las la-exclamation-triangle"></i>
                                This order contains gift items. You can't edit/modify this order, as it may affect the gift items associated with this order. If you need to make changes, please cancel the order and create a new one.
                            </div>
                        </div>
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <div class="row gutters-5">
                            <div class="col text-center text-md-left">
                                @if($isPartialDelivered)
                                    <span class="fs-20 font-weight-bold text-muted">
                                        PARTIAL DELIVERED
                                    </span>
                                @endif
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

                            @if($pendingReturnRequest)
                                <div class="col-12">
                                    <span class="alert alert-danger p-2 d-block text-center">
                                        This order has a pending return request. You cannot take any action on this order until the return request is resolved. @if(Auth::user()->user_type == 'admin' || in_array('manage_return_orders', json_decode(Auth::user()->staff?->role?->permissions ?? '[]') ?? [])) <a href="{{ route('return-orders.show', encrypt($pendingReturnRequest->id)) }}" class="alert-link">Click here</a> to view the return request. @endif
                                    </span>
                                </div>
                            @else
                                @if(in_array(strtolower($order->delivery_status), ['processing']))
                                    <div class="col-md-3 ml-auto">
                                        <label for=update_payment_status"">{{ ('Payment Status')}}</label>
                                        <select class="form-control aiz-selectpicker"  data-minimum-results-for-search="Infinity" id="update_payment_status">
                                            <option value="unpaid" @if ($payment_status == 'unpaid') selected @endif>{{ ('Unpaid')}}</option>
                                            @if($order->allPayments->count() > 0)
                                                @if($order->allPayments->sum('amount') >= get_order_grand_total($order))
                                                    <option value="paid" @if ($payment_status == 'paid') selected @endif>{{ ('Paid')}}</option>
                                                @else
                                                    <option value="partial" @if ($payment_status == 'partial') selected @endif>{{ ('Partial')}}</option>
                                                @endif
                                            @endif
                                        </select>
                                    </div>
                                @endif
                                <div class="col-md-3 ml-auto">
                                    <label for="update_delivery_status">{{ ('Delivery Status')}}</label>
                                    @if($delivery_status != 'delivered' && $delivery_status != 'cancelled')
                                        <select class="form-control aiz-selectpicker"  data-minimum-results-for-search="Infinity" id="update_delivery_status">
                                            <option value="{{ $delivery_status }}" selected>{{ucfirst(translate($delivery_status))}}</option>
                                            @if($delivery_status == 'hold' && $order->callLogs->where('status', 'shipment_failed')->count())
                                                <option value="picked_up" @if ($delivery_status == 'picked_up') selected @endif>{{ ('Picked Up')}}</option>
                                            @else
                                                @foreach(statusWiseOrderStatuses($delivery_status) as $status)
                                                    <option value="{{ $status }}">{{ucfirst(translate($status))}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    @else
                                        <input type="text" class="form-control" value="{{ ucfirst($delivery_status) }}" disabled>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="row gutters-5 mt-3">
                            <div class="col text-center text-md-left">
                                @php
                                    $shippingAddress = json_decode($order->shipping_address, true);
                                @endphp
                                <address>
                                    <strong class="text-main">
                                        {{ ucfirst($shippingAddress['name'] ?? 'N/A') }}
                                        {!! group_identity($order->user_id, 'image') !!}
                                    </strong>
                                    <br>
                                    <a href="tel:{{ $shippingAddress['phone'] ?? '' }}">
                                        {{ $shippingAddress['phone'] ?? '' }}
                                    </a>
                                    <br>
                                    @if(isset($shippingAddress['email']) && !empty($shippingAddress['email']))
                                        Email: {{ $shippingAddress['email'] ?? '' }}<br>
                                    @endif
                                    {{ $shippingAddress['address'] ?? '' }},

                                    City: {{ $shippingAddress['city'] ?? '' }},
                                    Area: {{ $shippingAddress['area'] ?? '' }},

                                    @if(isset($shippingAddress['postal_code']) && !empty($shippingAddress['postal_code']))
                                        Postal Code: {{ $shippingAddress['postal_code'] ?? '' }}<br>
                                    @endif
                                    {{ $shippingAddress['country'] ?? '' }}
                                    @if ($order->guest_order)
                                        <br><span class="badge badge-inline badge-info">Guest Order</span>
                                    @endif

                                    @if($order->delivery_status === 'processing')
                                        <span id="editShippingAddress" role="button" tabindex="0" data-info="{{ json_encode($shippingAddress) }}" data-toggle="tooltip" data-title="Edit Shipping Address" class="text-success fs-16">
                                            <i class="las la-map-marker-alt"></i>
                                        </span>
                                    @endif
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
                                <div>
                                    {!! order_payment_status($order) !!}
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
                                                @php
                                                    $class = match($delivery_status) {
                                                        'delivered' => 'success',
                                                        'returned' => 'danger',
                                                        'cancelled' => 'danger',
                                                        default => 'info'
                                                    };
                                                @endphp
                                                <span class="badge badge-inline badge-{{ $class }}">{{ (ucfirst(str_replace('_', ' ', $delivery_status))) }}</span>
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
                                                {{ single_price(get_order_grand_total($order)) }}
                                                {{-- {{ single_price($order->grand_total) }} --}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-main text-bold">{{ ('Payment method')}}</td>
                                            <td class="text-right">{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-main text-bold">{{ ('Order Source')}}</td>
                                            <td class="text-right">
                                                <span class="badge badge-inline badge-success">{{ strtoupper($order->order_source) }}</span>
                                            </td>
                                        </tr>
                                        @includeIf('com')
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
                                            <th width="10%" class="text-center" data-breakpoints="lg">{{ ('Photo')}}</th>
                                            <th class="text-uppercase">{{ ('Description')}}</th>
                                            {{-- <th data-breakpoints="lg" class="text-uppercase">{{ ('Delivery Type')}}</th> --}}
                                            <th class="min-col text-center text-uppercase">{{ ('Qty')}}</th>
                                            <th class="min-col text-center text-uppercase">{{ ('Price')}}</th>
                                            <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{ ('Total')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order->orderDetails as $key => $orderDetail)
                                            @if ($orderDetail->quantity < 1)
                                                @continue
                                            @endif
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td class="text-center">
                                                    @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                                        <a href="{{ to_frontend(route('product', $orderDetail->product->slug)) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                                    @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                                        <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                                    @else
                                                        <strong>{{ ('N/A') }}</strong>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                                        <strong>
                                                            <a href="{{ to_frontend(route('product', $orderDetail->product->slug)) }}" target="_blank" class="text-muted">
                                                                {{ $orderDetail->product->name }}
                                                                @if ($orderDetail->product_type !== 'regular')
                                                                    <small class="text-success">({{ ucfirst($orderDetail->product_type) }})</small>
                                                                    @includeIf('components.tooltip', [
                                                                        'title' => $orderDetail->giftOffer ? $orderDetail->giftOffer->title : 'Gift offer has been deleted'
                                                                    ])
                                                                @endif
                                                            </a>
                                                        </strong>
                                                        <small>{{ $orderDetail->variation }}</small>
                                                    @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                                        <strong>
                                                            <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank" class="text-muted">
                                                                {{ $orderDetail->product->name }}
                                                                @if ($orderDetail->product_type !== 'regular')
                                                                    <small class="text-success">({{ ucfirst($orderDetail->product_type) }})</small>
                                                                    @includeIf('components.tooltip', [
                                                                        'title' => $orderDetail->giftOffer ? $orderDetail->giftOffer->title : 'Gift offer has been deleted'
                                                                    ])
                                                                @endif
                                                            </a>
                                                        </strong>
                                                    @else
                                                        <strong>{{ ('Product Unavailable') }}</strong>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    {{ $orderDetail->quantity }}
                                                </td>
                                                <td class="text-center">{{ $orderDetail->price > 0 ? single_price($orderDetail->price/$orderDetail->quantity) : 'FREE' }}</td>
                                                <td class="text-center">
                                                    {{ $orderDetail->price > 0 ? single_price($orderDetail->price) : 'FREE' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
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
                                            @if($order->payment_status != 'unpaid' && $paidAmount > 0)
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
                                                <td class="h6">
                                                    <span class="total-amount" data-total="{{ max(0, $total) }}">{{ single_price(max(0, $total)) }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="text-right no-print">
                                        @if((!hasGiftItem($order) && (Auth::user()->user_type == 'admin' || in_array('processing_orders', json_decode(Auth::user()->staff?->role?->permissions ?? '[]') ?? [])) && $order->delivery_status == 'processing' && !$pendingReturnRequest))
                                            <a href="{{route('invoice.edit', $order->id)}}" type="button" class="btn btn-icon btn-soft-success"><i class="las la-edit"></i></a>
                                        @endif
                                        <a href="{{ route('invoice.download', $order->id) }}" type="button" class="btn btn-icon btn-light" target="_blank"><i class="las la-print"></i></a>
                                        @if($order->payment_status != 'unpaid' && $order->payments->isNotEmpty())
                                            <a href="javascript:void(0);" onclick="showPayHistory()"><i class="las la-eye"></i> View Payment Histroy</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="row">
                                    @if(!$pendingReturnRequest && (Auth::user()->user_type == 'admin' || in_array('processing_orders', json_decode(Auth::user()->staff?->role?->permissions ?? '[]') ?? [])) && in_array($delivery_status, ['pending', 'processing', 'hold', 'confirmed']))
                                        <div class="col-12">
                                            <div class="float-right">
                                                <button type="button" class="btn btn-sm btn-light" title="Add Note" data-toggle="modal" data-target="#order-note-create-modal">Add Note</button>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-12 mt-2">
                                        <div id="note-list">
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
    <div class="col-md-5">
        <div class="row" id="sortable">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Recent Orders') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if(get_setting('enable_courier_success_rate') == 1)
                                <div class="col-lg-12" id="courier-success-rate">
                                    @include('backend.components.customer-success-rate-preloader')
                                </div>
                            @endif
                            <div class="col-lg-12" id="customer-success-rate">
                                @include('backend.components.customer-success-rate-preloader')
                            </div>
                            @if(in_array($order->delivery_status, ['processing', 'pending', 'confirmed']))
                                <div class="col-lg-12">
                                    <div class="accordion" id="accordionExample">
                                        @include('backend.components.recent-orders-list-preloader')
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Call Logs') }}</h5>
                        @if(!$pendingReturnRequest && $order->delivery_status == 'processing')
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
                            <div class="col-lg-12 table-responsive" id="call-log-list">
                                {{-- Loading animation --}}
                                <div class="text-center my-2">
                                    <i class="las la-spinner la-spin la-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Order Logs') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12 table-responsive" id="order-log-list">
                                <div class="text-center my-2">
                                    <i class="las la-spinner la-spin la-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .sortable-ghost {
        opacity: 0.5;
        background: #c8ebfb;
    }

    .sortable-chosen {
        background-color: #f0f0f0;
    }

    .sortable-drag {
        opacity: 1;
        background-color: #e6f7ff;
    }
</style>
@endsection
@section('modal')
    @include('modals.partial_payment_history_modal', ['order' => $order, 'payments' => $order->payments])
    @if($order->delivery_status == 'processing')
        @include('modals.delete_modal')
        {{-- Call Log Create Modal Start --}}
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
                                <label for="status">{{ ('Status') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-control" name="status" id="status">
                                        <option value="">{{ ('Select Status') }}</option>
                                        @foreach (getHoldStatuses() as $value => $text)
                                            @if ($value == 'shipment_failed')
                                                @continue
                                            @endif
                                            <option value="{{ $value }}" @if (old('status') == $value) selected @endif>{{ (ucwords($text)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="text-danger" style="display: none" id="status_error"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="note">{{ ('Note') }}</label>
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
        {{-- Call Log Create Modal End --}}
    @endif

    @if((Auth::user()->user_type == 'admin' || in_array('processing_orders', json_decode(Auth::user()->staff?->role?->permissions ?? '[]') ?? [])) && in_array($delivery_status, ['pending', 'processing', 'hold', 'confirmed']))
        {{-- Order Notes Create Modal Start --}}
        <div id="order-note-create-modal" class="modal fade">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title h6">{{ ('New Note') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="orderNote">{{ ('Note') }}</label>
                            <div class="input-group">
                                <textarea rows="4" class="form-control" name="orderNote" id="orderNote" placeholder="{{ ('Enter a note here') }}">{{ old('orderNote') }}</textarea>
                            </div>
                            <div class="text-danger" style="display: none" id="orderNote_error"></div>
                        </div>
                        <div class="form-group mb-3 text-right">
                            <button type="button" onclick="addNote()" class="btn btn-primary">{{ ('Save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Order Notes Create Modal End --}}
    @endif

    @include('modals.partial_pay_modal')

    {{-- Call Log Details Modal Start --}}
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

    {{-- Hold Status Modal Start --}}
    <div id="hold-status-modal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ ('Choose Hold Reason') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <form id="hold-status-form" action="">
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <div class="form-group mb-3">
                            <label for="status">{{ ('Status') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-control" name="hold_status" id="hold_status">
                                    <option value="">{{ ('Select Status') }}</option>
                                    <option value="out_of_stock">{{ ('Out Of Stock') }}</option>
                                    <option value="bkash_advance_payment">{{ ('Bkash Advance Payment') }}</option>
                                    <option value="re_schedule">{{ ('Re-Schedule') }}</option>
                                    <option value="others">{{ ('Others') }}</option>
                                </select>
                            </div>
                            <div class="text-danger" style="display: none" id="hold_status_error"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="note">{{ ('Note') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <textarea type="text" class="form-control" name="hold_note" id="hold_note" placeholder="{{ ('Note') }}" rows="3" required>{{ old('hold_note') }}</textarea>
                            </div>
                            <div class="text-danger" style="display: none" id="hold_note_error"></div>
                        </div>
                    </form>
                    <div class="form-group mb-3 text-right">
                        <button id="save-hold-reason" onclick="saveHoldReason()" class="btn btn-primary">{{ ('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Hold Status Modal End --}}

    {{-- Success Rate Summary Modal --}}
    <div class="modal fade" id="success-rate-summary-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning" style="min-height: 45px !important;">
                    <h5 class="modal-title h6" id="success-rate-summary-modal-title">Courier Success Rate Summary</h5>
                    <button type="button" class="close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ ('Courier')}}</th>
                                <th class="text-center">{{ ('Total')}}</th>
                                <th class="text-center">{{ ('Delivered')}}</th>
                                <th class="text-center">{{ ('Returned')}}</th>
                                <th data-breakpoints="lg" class="text-center">{{ ('Success Rate')}}</th>
                            </tr>
                        </thead>
                        <tbody id="success-rate-summary-data">
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">{{ ('Loading...')}}</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">{{ ('Cancel')}}</button>
                </div>
            </div>
        </div>
    </div>

    @if($order->delivery_status === 'processing')
        <div class="modal fade" id="editShippingModal" tabindex="-1" role="dialog" aria-labelledby="editShippingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editShippingModalLabel">Edit Shipping Address</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Name -->
                        <div class="form-group">
                            <label for="shipping_name">Name</label>
                            <input type="text" class="form-control" name="name" id="shipping_name" value="{{ $shippingAddress['name'] ?? '' }}" required>
                            <small class="form-text text-danger shipping-input-error" id="shipping_name_error"></small>
                        </div>

                        <!-- Phone -->
                        <div class="form-group">
                            <label for="shipping_phone">Phone</label>
                            <input type="tel" class="form-control" name="phone" id="shipping_phone" value="{{ $shippingAddress['phone'] ?? '' }}" required>
                            <small class="form-text text-danger shipping-input-error" id="shipping_phone_error"></small>
                        </div>

                        <!-- Address -->
                        <div class="form-group">
                            <label for="shipping_address">Address</label>
                            <textarea class="form-control" name="address" id="shipping_address" rows="2" required>{{ $shippingAddress['address'] ?? '' }}</textarea>
                            <small class="form-text text-danger shipping-input-error" id="shipping_address_error"></small>
                        </div>
                        <div class="form-group">
                            <label for="shipping_state">State</label>
                            <select name="state_id" id="shipping_state" class="form-control aiz-selectpicker" data-live-search="true" required>
                                <option value="">Select State</option>
                            </select>
                            <small class="form-text text-danger shipping-input-error" id="shipping_state_error"></small>
                        </div>
                        <div class="form-group">
                            <label for="shipping_city">City</label>
                            <select name="city_id" id="shipping_city" class="form-control aiz-selectpicker" data-live-search="true" required>
                                <option value="">Select City</option>
                            </select>
                            <small class="form-text text-danger shipping-input-error" id="shipping_city_error"></small>
                        </div>
                        <div class="form-group">
                            <label for="shipping_area">Area</label>
                            <select name="area_id" id="shipping_area" class="form-control aiz-selectpicker" data-live-search="true" required>
                                <option value="">Select Area</option>
                            </select>
                            <small class="form-text text-danger shipping-input-error" id="shipping_area_error"></small>
                        </div>

                        <div class="bg-soft-danger border border-sm p-3 rounded rounded-sm" id="shipping_errors" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button id="saveShippingChanges" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('script')
    <script src="{{ asset('assets/js/Sortable.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            getNotes(); // Fetch notes on page load
            getCallLogs(); // Fetch call logs on page load
            getOrderLogs();
            getCustomerSuccessRate(); // Fetch customer success rate on page load
            @if (get_setting('enable_courier_success_rate') == 1)
                courierSuccessRate();
            @endif
            @if(in_array($order->delivery_status, ['processing', 'pending', 'confirmed']))
                getRecentOrders(); // Fetch recent orders on page load
            @endif
            new Sortable(document.getElementById('sortable'), {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onStart: function(evt) {
                    // console.log('Sorting started');
                },
                onEnd: function(evt) {
                    // console.log('Item moved from position', evt.oldIndex, 'to', evt.newIndex);
                },
                onUpdate: function(evt) {
                    // console.log('New order:', Array.from(evt.from.children).map(el => el.textContent.trim()));
                }
            });
        });
    </script>

    @if($order->delivery_status === 'processing')
        <script>
            $(document).ready(function() {
                let currentShippingInfo = {
                    'name': "{{ $shippingAddress['name'] ?? '' }}",
                    'address': "{{ $shippingAddress['address'] ?? '' }}",
                    'state': "{{ $shippingAddress['state'] ?? '' }}",
                    'city': "{{ $shippingAddress['city'] ?? '' }}",
                    'area': "{{ $shippingAddress['area'] ?? '' }}",
                    'phone': "{{ $shippingAddress['phone'] ?? '' }}"
                };

                $('#editShippingAddress').on('click', function() {
                    $('.shipping-input-error').text('');
                    $('#shipping_errors').html('').hide();
                    $('#shipping_name').val(currentShippingInfo.name);
                    $('#shipping_address').val(currentShippingInfo.address);
                    $('#shipping_phone').val(currentShippingInfo.phone);
                    get_states();
                    $('#editShippingModal').modal('show');
                });

                $('#saveShippingChanges').on('click', function() {
                    const newShippingInfo = {
                        'order_id': "{{ $order->id }}",
                        'name': $('#shipping_name').val().trim(),
                        'address': $('#shipping_address').val().trim(),
                        'state_id': $('#shipping_state').val(),
                        'city_id': $('#shipping_city').val(),
                        'area_id': $('#shipping_area').val(),
                        'phone': $('#shipping_phone').val().trim()
                    };

                    let isValid = true;

                    if (newShippingInfo.name === '') {
                        $('#shipping_name_error').text('Name is required');
                        isValid = false;
                    } else {
                        $('#shipping_name_error').text('');
                    }

                    if (newShippingInfo.phone === '') {
                        $('#shipping_phone_error').text('Phone is required');
                        isValid = false;
                    } else {
                        $('#shipping_phone_error').text('');
                    }

                    if (newShippingInfo.address === '') {
                        $('#shipping_address_error').text('Address is required');
                        isValid = false;
                    } else {
                        $('#shipping_address_error').text('');
                    }

                    if (newShippingInfo.state_id === '') {
                        $('#shipping_state_error').text('State is required');
                        isValid = false;
                    } else {
                        $('#shipping_state_error').text('');
                    }

                    if (newShippingInfo.city_id === '') {
                        $('#shipping_city_error').text('City is required');
                        isValid = false;
                    } else {
                        $('#shipping_city_error').text('');
                    }

                    if (newShippingInfo.area_id === '') {
                        $('#shipping_area_error').text('Area is required');
                        isValid = false;
                    } else {
                        $('#shipping_area_error').text('');
                    }

                    if (!isValid) {
                        return;
                    }

                    let button = $(this);
                    button.prop('disabled', true).html(`<i class="las la-spinner la-spin"></i> Saving...`);
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{route('invoice.updateShippingInfo')}}",
                        type: 'POST',
                        data: newShippingInfo,
                        success: function (response) {
                            if (response.success) {
                                AIZ.plugins.notify('success', response.message || 'Shipping information updated.');
                                window.location.reload();
                                $('#editShippingModal').modal('hide');
                            } else {
                                AIZ.plugins.notify('danger', response.message || 'Something went wrong.');
                                button.prop('disabled', false).html('Save Changes');
                            }
                        },
                        error: function (xhr, status, error) {
                            button.prop('disabled', false).html('Save Changes');
                            if (xhr.status === 422) {
                                let errorHtml = '';
                                $.each(xhr.responseJSON.errors, function(key, value) {
                                    errorHtml += '<span class="text-danger d-block">- ' + value + '</span>';
                                });
                                $('#shipping_errors').html(errorHtml).show();
                            } else {
                                $('#shipping_errors').html('').hide();
                                AIZ.plugins.notify('danger', 'Server error occurred.');
                                console.error(xhr, status, error);
                            }
                        }
                    });
                });

                $('#shipping_state').on('change', function() {
                    var state_id = $(this).val();
                    get_city(state_id);
                });

                $('#shipping_city').on('change', function() {
                    var city_id = $(this).val();
                    get_area(city_id);
                });

                function get_states(country_id = '') {
                    $('#shipping_state').html("");
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{route('get-state')}}",
                        type: 'POST',
                        data: {
                            country_id: country_id,
                            selected: currentShippingInfo.state
                        },
                        success: function (response) {
                            var obj = JSON.parse(response);
                            if(obj != '') {
                                $('#shipping_state').html(obj);
                                AIZ.plugins.bootstrapSelect('refresh');

                                let selectedState = $('#shipping_state').val();
                                if (selectedState) {
                                    get_city(selectedState);
                                }
                            }
                        }
                    });
                }

                function get_city(state_id = '') {
                    $('#shipping_city').html("");
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{route('get-city')}}",
                        type: 'POST',
                        data: {
                            state_id: state_id,
                            selected: currentShippingInfo.city
                        },
                        success: function (response) {
                            var obj = JSON.parse(response);
                            if(obj != '') {
                                $('#shipping_city').html(obj);
                                AIZ.plugins.bootstrapSelect('refresh');

                                let selectedCity = $('#shipping_city').val();
                                if (selectedCity) {
                                    get_area(selectedCity);
                                }
                            }
                        }
                    });
                }

                function get_area(city_id = '') {
                    $('#shipping_area').html("");
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{route('get-area')}}",
                        type: 'POST',
                        data: {
                            city_id: city_id,
                            selected: currentShippingInfo.area
                        },
                        success: function (response) {
                            var obj = JSON.parse(response);
                            if(obj != '') {
                                $('#shipping_area').html(obj);
                                AIZ.plugins.bootstrapSelect('refresh');
                            }
                        }
                    });
                }
            });
        </script>
    @endif

    <script type="text/javascript">
        let deliveryStatus = '{{ $order->delivery_status }}'; // Get the delivery status
        let callDuration = {{ old('duration', 0) }}; // Initialize call duration variable
        let checkInterval;
        let timerInterval;
        let callDurationInterval;
        let remainingTime = {{ $order->unlockIn() }};

        async function getRecentOrders() {
            await $.ajax({
                url: '{{ route('orders.get-recent-orders', $order->id) }}',
                type: 'GET',
                success: function(response) {
                    $('#accordionExample').html(response.view || '');
                },
                error: function() {
                    console.error('Error fetching recent orders');
                    $('#accordionExample').html('');
                }
            });
        }
        async function getCustomerSuccessRate() {
            await $.ajax({
                url: '{{ route('orders.get-customer-success-rate', $order->id) }}',
                type: 'GET',
                success: function(response) {
                    $('#customer-success-rate').html(response.view || '');
                },
                error: function() {
                    console.error('Error fetching customer success rate');
                    $('#customer-success-rate').html('');
                }
            });
        }
        async function courierSuccessRate() {
            const phone = `{{ @json_decode($order->shipping_address)->phone ?? '' }}`;
            await $.ajax({
                url: `{{ route('get-courier-success-rate') }}`,
                data: {
                    phone: phone
                },
                type: 'GET',
                success: function(response) {
                    $('#courier-success-rate').html(response.view || '');
                },
                error: function() {
                    console.error('Error fetching courier success rate');
                    $('#courier-success-rate').html('');
                }
            });
        }
        $('#courier-success-rate').on('click', '.success-rate-summary', function() {
            const summary = $(this).data('summary');
            let rows = ``;
            Object.entries(summary).forEach(([courier, item]) => {
                rows += `
                    <tr>
                        <td>${courier}</td>
                        <td class="text-center">${item.total_parcels || 0}</td>
                        <td class="text-center">${item.delivered_parcels || 0}</td>
                        <td class="text-center">${item.returned_parcels || 0}</td>
                        <td class="text-center font-weight-bold">${item.success_rate || 0}%</td>
                    </tr>
                `;
            });
            $('#success-rate-summary-data').html(rows);
            $('#success-rate-summary-modal').modal('show');
        });
        async function getCallLogs() {
            await $.ajax({
                url: '{{ route('orders.get-call-logs', $order->id) }}',
                type: 'GET',
                success: function(response) {
                    $('#call-log-list').html(response.view || '');
                },
                error: function() {
                    console.error('Error fetching call logs');
                }
            });
        }
        async function getOrderLogs() {
            await $.ajax({
                url: '{{ route('orders.get-order-logs', $order->id) }}',
                type: 'GET',
                success: function(response) {
                    $('#order-log-list').html(response.view || '');
                },
                error: function() {
                    console.error('Error fetching order logs');
                }
            });
        }

        async function getNotes(){
            await $.ajax({
                url: '{{ route('orders.get-notes', $order->id) }}',
                type: 'GET',
                success: function(response) {
                    $('#note-list').html(response);
                },
                error: function() {
                    console.error('Error fetching notes');
                }
            });
        }
        function addNote(){
            var note = $('#orderNote').val();
            if (note.length < 1) {
                $('#orderNote_error').text('{{ ('Please enter a note') }}').show();
                return;
            } else if (note.length > 255) {
                $('#orderNote_error').text('{{ ('Note is too long') }}').show();
                return;
            } else if (note.length < 5) {
                $('#orderNote_error').text('{{ ('Note is too short') }}').show();
                return;
            } else {
                $('#orderNote_error').hide();
            }
            var order_id = '{{ $order->id }}';
            $.ajax({
                url: '{{ route('orders.add-note') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: order_id,
                    note: note,
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        // AIZ.plugins.notify('success', response.message);
                        getNotes();
                        $('#orderNote').val(''); // Clear the input field
                        $('#order-note-create-modal').modal('hide');
                    } else {
                        showAlert('error', response.message);
                        // AIZ.plugins.notify('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('error', '{{ ('Something went wrong') }}');
                    // AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }
        function deleteNote(index){
            $.ajax({
                url: '{{ route('orders.delete-note') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    index: index,
                    id: '{{ $order->id }}',
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        // AIZ.plugins.notify('success', response.message);
                        getNotes();
                    } else {
                        showAlert('error', response.message);
                        // AIZ.plugins.notify('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('error', '{{ ('Something went wrong') }}');
                    // AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }

        function paynow(e) {
            var url = $(this).data("href");
            $("#partial-pay-modal").modal("show");
            $("#partial-payment-link").attr("href", url);

            window.dispatchEvent(new CustomEvent('set-amount', {
                detail: calculateTotal()
            }));
        }

        function calculateTotal(){
            let total = parseFloat($('.total-amount').data('total'));
            return total;
        }

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
                    console.log(response);
                    if (response.success) {
                        window.location.href = response.redirect_url;
                    } else {
                        showAlert('error', response.message);
                        // AIZ.plugins.notify('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('error', '{{ ('Something went wrong') }}');
                    // AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
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

            // console.log('Call duration stored:', callDuration);  // Example: 1.12, 0.06, 115.22
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
                    AIZ.plugins.notify('danger', 'Failed to extend lock.');
                    window.location.reload();
                }
            });
        }

        @if(!$pendingReturnRequest)
            if(deliveryStatus == 'processing'){
                // Start the timer countdown
                timerInterval = setInterval(updateTimer, 1000);

                updateTimer();
            }
        @endif

        function resetForm(){
            $('#status').val('');
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

        $('#call-log-list').on('click', '.delete-call-log', function (e) {
            e.preventDefault();
            const url = $(this).data('href');

            Swal.fire({
                title: 'Are You Sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete It!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'GET',
                        url: url,
                        success: function (response) {
                            if (response.success) {
                                Swal.fire('Deleted', 'Call log has been deleted.', 'success');
                                getCallLogs();
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error!', '{{ ('Something went wrong') }}', 'error');
                        }
                    });
                }
            });
        });

        $('#submit-btn').on('click', async function(e){
            const $this = $(this);
            var status = $('#status').val();
            if(status == ''){
                $('#status_error').html('{{ ('Status is required') }}').show();
                return;
            }

            $this.prop('disabled', true);
            const data = $('#order-call-logs-create-form').serializeArray();
            await $.ajax({
                type: 'POST',
                url: '{{ route('call-logs.store') }}',
                data: data,
                success: function (response) {
                    if (response.success) {
                        $('#call-log-create-modal').modal('hide');
                        Swal.fire('Success', 'Call log has been created.', 'success');
                        getCallLogs();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                    $this.prop('disabled', false);
                },
                error: function () {
                    Swal.fire('Error!', '{{ ('Something went wrong') }}', 'error');
                    $this.prop('disabled', false);
                }
            });
        });

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
                    showAlert('error', "{{ ('You can not edit this order') }}", window.location.href);
                    // AIZ.plugins.notify('danger', "{{ ('You can not edit this order') }}");
                    // window.location.reload();
                }else{
                    showAlert('success', '{{ ('Delivery boy has been assigned') }}', window.location.href);
                    // AIZ.plugins.notify('success', '{{ ('Delivery boy has been assigned') }}');
                }
            });
        });

        $('#update_delivery_status').on('change', async function() {
            const status = $(this).val();
            const originalStatus = '{{ $order->delivery_status }}';

            if (status === 'hold') {
                $('#hold-status-modal').modal('show');
            } else if (status === 'cancelled') {
                try {
                    const cancellationReason = await takeReason();
                    if (cancellationReason.trim() !== '') {
                        updateDeliveryStatus(cancellationReason);
                    } else {
                        $(this).val(originalStatus);
                        $(this).selectpicker('refresh');
                    }
                } catch (error) {
                    console.error('Error getting cancellation reason:', error);
                    $(this).val(originalStatus);
                    $(this).selectpicker('refresh');
                }
            } else {
                updateDeliveryStatus(); // For other status changes
            }
        });

        function saveHoldReason(){
            var hold_status = $('#hold_status').val();
            var hold_note = $('#hold_note').val();

            if(hold_status == ''){
                $('#hold_status_error').html('{{ ('Status is required') }}').show();
                return;
            }else{
                $('#hold_status_error').hide();
            }
            if(hold_note == ''){
                $('#hold_note_error').html('{{ ('Note is required') }}').show();
                return;
            }else{
                $('#hold_note_error').hide();
            }

            $.post('{{ route('call-logs.store') }}', {
                reference: 'order',
                reference_id: '{{ $order->id }}',
                status: hold_status,
                note: hold_note,
            }, function(data){
                if(data.success){
                    $('#hold-status-modal').modal('hide');
                    $('#hold_status').val('');
                    $('#hold_note').val('');
                    updateDeliveryStatus();
                }else{
                    showAlert('error', data.message || '{{ ('Something went wrong') }}');
                    // AIZ.plugins.notify('danger', data.message || '{{ ('Something went wrong') }}');
                }
            });
        }

        function updateDeliveryStatus(cancellationReason = '') {
            var order_id = {{ $order->id }};
            var status = $('#update_delivery_status').val();
            var old_status = '{{ $order->delivery_status }}';
            var url = `{{ route('all_orders.status', ':status') }}`.replace(':status', old_status);
            $.post('{{ route('orders.update_delivery_status') }}', {
                _token:'{{ @csrf_token() }}',
                order_id:order_id,
                status:status,
                reason: cancellationReason
            }, function(data){
                if(data === 403){
                    showAlert('error', "{{ ('You can not edit this order') }}", old_status == 'processing' ? url : window.location.href);
                    // AIZ.plugins.notify('danger', "{{ ('You can not edit this order') }}");
                } else {
                    showAlert('success', '{{ ('Delivery status has been updated') }}', old_status == 'processing' ? url : window.location.href);
                    // AIZ.plugins.notify('success', '{{ ('Delivery status has been updated') }}');
                }
            });
        }

        $('#update_payment_status').on('change', function(){
            var order_id = {{ $order->id }};
            var status = $('#update_payment_status').val();
            $.post('{{ route('orders.update_payment_status') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,status:status}, function(data){
                if(data === 403){
                    showAlert('error', "{{ ('You can not edit this order') }}", window.location.href);
                    // AIZ.plugins.notify('danger', "{{ ('You can not edit this order') }}");
                    // window.location.reload();
                }else{
                    showAlert('success', '{{ ('Payment status has been updated') }}', window.location.href);
                    // AIZ.plugins.notify('success', '{{ ('Payment status has been updated') }}');
                }
            });
        });
    </script>
@endsection
