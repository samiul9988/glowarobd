@extends('backend.layouts.app')

@section('content')
@php
    $order = $returnRequest->order;
    $shipping_address = json_decode($order->shipping_address, true) ?? [];
    $sub_total = 0;
@endphp
<div class="row gutters-5">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="row w-100">
                    <div class="col-4 d-flex flex-wrap align-items-center">
                        <div class="d-flex justify-content-start align-items-center">
                            <a href="{{ route('return-orders.index') }}" class="mr-2 btn btn-soft-secondary btn-icon btn-circle btn-sm" title="Go To Pending Return Orders">
                                <i class="las la-long-arrow-alt-left"></i>
                            </a>
                            <h1 class="h2 fs-16 mb-0">{{ ('Order Return Details') }}</h1>
                        </div>
                    </div>
                    <div class="col-4 d-flex justify-content-center flex-wrap align-items-center">
                        <span class="h2 fs-16 mb-0">
                            <strong>{{ $returnRequest->is_partial ? 'Partial' : 'Full' }} Return</strong>
                        </span>
                    </div>
                    <div class="col-4 p-0">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row gutters-5 align-items-center">
                    <div class="col-md-9">
                        <span class="alert alert-info d-block mb-0" style="padding: 11px !important;">
                            <strong>Reason:</strong> {{ $returnRequest->reason }}
                        </span>
                    </div>
                    <div class="col-md-3 ml-auto">
                        <select class="form-control aiz-selectpicker" id="update_status">
                            @if($returnRequest->status == 'pending')
                                <option value="pending" selected>Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            @elseif($returnRequest->status == 'processing' && $returnRequest->approved_at && $returnRequest->approved_at->gt(now()->subDay()))
                                <option value="processing" selected>Processing</option>
                                <option value="pending">Pending</option>
                            @else
                                <option value="{{ $returnRequest->status }}" selected disabled>
                                    {{ ucfirst($returnRequest->status) }}
                                </option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="row gutters-5 mt-3">
                            <div class="col-md-4 text-center text-md-left">
                                <address>
                                    <span class="d-block">
                                        <strong class="text-main">
                                            {{ $shipping_address['name'] ?? '' }}
                                        </strong>
                                    </span>
                                    @if($shipping_address['phone'] ?? '')
                                        <span class="d-block">
                                            <a href="tel:{{ $shipping_address['phone'] }}">{{ $shipping_address['phone'] }}</a>
                                        </span>
                                    @endif
                                    @if($shipping_address['email'] ?? '')
                                        <span class="d-block">
                                            <a href="mailto:{{ $shipping_address['email'] }}">{{ $shipping_address['email'] }}</a>
                                        </span>
                                    @endif
                                    {{ $shipping_address['address'] ?? '' }},

                                    City: {{ $shipping_address['city'] ?? '' }},
                                    Area: {{ $shipping_address['area'] ?? '' }},
                                    @if($shipping_address['postal_code'] ?? '')
                                    Postal Code: {{ $shipping_address['postal_code'] }}
                                    @endif
                                    {{ $shipping_address['country'] ?? '' }}
                                </address>
                            </div>
                            <div class="col-md-4 text-center d-none d-md-block">
                                {!! order_payment_status($order) !!}
                            </div>
                            <div class="col-md-4 ml-auto">
                                <table class="float-right">
                                    <tbody>
                                        <tr>
                                            <td class="text-main text-bold">{{ ('Order #')}}</td>
                                            <td class="text-right text-info text-bold">	{{ $order->code }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-main text-bold">{{ ('Order Status')}}</td>
                                            <td class="text-right">
                                                <span class="badge badge-inline badge-info">{{ (ucfirst(str_replace('_', ' ', $order->delivery_status))) }}</span>
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
                                        @if($order->delivery_date!='')
                                        <tr>
                                            <td class="text-main text-bold">{{ ('Delivery Date')}}</td>
                                            <td class="text-right">{{ date('d-m-Y', $order->delivery_date) }} ({{ date('l', $order->delivery_date) }})</td>
                                        </tr>
                                        @endif
                                        @if(@$order->allOrderDetails[0]->shippingMethod->name!='')
                                        <tr>
                                            <td class="text-main text-bold">{{ ('Shipping method')}}:</td>
                                            <td class="text-right">
                                                {{ @$order->allOrderDetails[0]->shippingMethod->name }}
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
                                            <th width="10%" data-breakpoints="lg" class="text-center">{{ ('Photo')}}</th>
                                            <th class="text-uppercase">{{ ('Description')}}</th>
                                            <th class="min-col text-center text-uppercase">{{ ('Qty')}}</th>
                                            <th class="min-col text-center text-uppercase">{{ ('Price')}}</th>
                                            <th class="min-col text-center text-uppercase">{{ ('Total')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order->allOrderDetails as $key => $orderDetail)
                                            @php
                                                $returnItem = $returnRequest->items->where('order_item_id', $orderDetail->id)->first();
                                                if($returnItem && $returnRequest->is_partial && $returnRequest->status == 'approved') {
                                                    $originalQuantity = $orderDetail->quantity + $returnItem->quantity;
                                                }else{
                                                    $originalQuantity = $orderDetail->quantity;
                                                }
                                                // dd($originalQuantity);
                                            @endphp
                                            <tr class="{{ $returnItem ? 'bg-soft-danger' : 'bg-soft-success' }}">
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
                                                        <strong><a href="{{ to_frontend(route('product', $orderDetail->product->slug)) }}" target="_blank" class="text-muted">{{ $orderDetail->product->name }}</a></strong>
                                                        <small>{{ $orderDetail->variation }}</small>
                                                    @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                                        <strong><a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank" class="text-muted">{{ $orderDetail->product->name }}</a></strong>
                                                    @else
                                                        <strong>{{ ('Product Unavailable') }}</strong>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($returnItem)
                                                        @if(!$returnRequest->is_partial)
                                                            <span class="text-danger">
                                                                {{ $originalQuantity }}
                                                            </span>
                                                        @elseif($returnItem->quantity >= $originalQuantity)
                                                            {{-- Full return of this item --}}
                                                            <span class="text-danger">
                                                                <del>{{ $originalQuantity }}</del>
                                                            </span>
                                                            <span class="d-block text-danger">
                                                                <small class="font-weight-bold">FULLY RETURNED</small>
                                                            </span>
                                                        @else
                                                            {{-- Partial return of this item --}}
                                                            {{ $originalQuantity }}
                                                            <span class="d-block text-danger">
                                                                <del>{{ $returnItem->quantity }}</del>
                                                            </span>
                                                            <span class="d-block text-success">
                                                                <strong>{{ $originalQuantity - $returnItem->quantity }}</strong>
                                                            </span>
                                                        @endif
                                                    @else
                                                        {{-- Item not in return request --}}
                                                        {{ $originalQuantity }}
                                                        <span class="d-block text-success">
                                                            <small class="font-weight-bold">NOT RETURNED</small>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        if ($returnItem) {
                                                            $unit_price = $returnItem->unit_price;
                                                        } else {
                                                            $unit_price = $orderDetail->price / max($originalQuantity,1);
                                                        }
                                                    @endphp
                                                    {{ single_price($unit_price) }}
                                                </td>
                                                <td class="text-center">
                                                    @if($returnItem)
                                                        @if(!$returnRequest->is_partial)
                                                            <span class="text-danger">
                                                                {{ single_price($orderDetail->price) }}
                                                            </span>
                                                        @elseif($returnItem->quantity == $originalQuantity)
                                                            <span class="d-block text-danger">
                                                                <del>{{ single_price($originalQuantity * $unit_price) }}</del>
                                                            </span>
                                                            <span class="d-block text-success">
                                                                <strong>{{ single_price(0) }}</strong>
                                                            </span>
                                                        @else
                                                            {{-- Partial return - add remaining amount to subtotal --}}
                                                            @php
                                                                if($originalQuantity <= 0) {
                                                                    // $unit_price = 0;
                                                                    $remaining_amount = 0;
                                                                } else {
                                                                    $unit_price *= $originalQuantity;
                                                                    $remaining_amount = $unit_price * ($originalQuantity - $returnItem->quantity);
                                                                }
                                                                $sub_total += $remaining_amount;
                                                            @endphp
                                                            <span class="d-block text-danger">
                                                                <del>{{ single_price(max($orderDetail->price, $remaining_amount)) }}</del>
                                                            </span>
                                                            <span class="d-block text-success">
                                                                {{ single_price(min($remaining_amount, $orderDetail->price)) }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        {{-- Item not in return request - add full price to subtotal --}}
                                                        @php
                                                            $sub_total += $orderDetail->price;
                                                        @endphp
                                                        {{ single_price($orderDetail->price) }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Requested By</h4>
                                <div>
                                    <span class="d-block"><strong>{{ $returnRequest->user_id === 0 ? 'Pathao' : $returnRequest->user->name }}</strong></span>
                                    <span class="d-block">At {{ $returnRequest->created_at->format('d F Y, h:i A') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="clearfix float-right">
                                    @include('backend.return_orders.table-footer')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
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
@endsection
@section('script')
<script>
    $(document).ready(function(){
        getOrderLogs();
        async function getOrderLogs() {
            await $.ajax({
                url: '{{ route('orders.get-order-logs', $returnRequest->order_id) }}',
                type: 'GET',
                success: function(response) {
                    $('#order-log-list').html(response.view || '');
                },
                error: function() {
                    console.error('Error fetching order logs');
                }
            });
        }
        $('#update_status').on('change', function(){
            const status = $(this).val();
            const id = "{{ $returnRequest->id }}";
            if(status == "{{ $returnRequest->status }}") {
                return;
            }
            $.post('{{ route("return-orders.update-status") }}', {_token:'{{ csrf_token() }}', id:id, status:status}, function(response){
                if(response.success) {
                    showAlert('success', 'Return request status has been updated successfully', "{{ route('return-orders.index') }}");
                }
                else{
                    showAlert('danger', 'Request failed!');
                    $(this).val("{{ $returnRequest->status }}");
                    $(this).selectpicker('refresh');
                }
            });
        });
    });
</script>
@endsection
