@extends('backend.layouts.app')

@section('content')
<div class="row mb-2">
    <div class="col">
        <h5 class="mb-md-0 h5">{{ ('Find Orders') }}</h5>
    </div>
    <div class="col">
        <a href="{{ route('tickets.create') }}" class="btn btn-success btn-sm float-right">
            <i class="las la-plus"></i> {{ ('Create Ticket') }}
        </a>
    </div>
</div>
<div class="card">
    <form class="" action="{{ route('services.manage') }}" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col-lg-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="invoice" name="invoice" value="{{ @$invoice }}" placeholder="{{ ('Order invoice number') }}">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="customer" name="customer" value="{{ @$customer }}" placeholder="{{ ('Customer phone or name') }}">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ @$date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <select class="form-control" name="source">
                        <option value="">{{ ('Filter by order source') }}</option>
                        @foreach ($order_sources ?? [] as $key => $order_source)
                            @if(strlen(trim($order_source)))
                                <option value="{{ strtolower($order_source) }}" @if(strtolower($order_source) == strtolower($source)) selected @endif>{{ strtoupper($order_source) }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>{{ ('Order Code') }}</th>
                        <th data-breakpoints="md">{{ ('Customer') }}</th>
                        <th data-breakpoints="md">{{ ('Amount') }}</th>
                        <th data-breakpoints="md">{{ ('Delivery Status') }}</th>
                        <th data-breakpoints="lg">{{ ('Order Source')}}</th>
                        <th data-breakpoints="lg">{{ ('Date')}}</th>
                        <th class="text-right" width="15%">{{ ('options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('all_orders.show', encrypt($order->id)) }}" target="_blank" class="font-weight-bold">#{{ $order->code }}</a>
                            </td>
                            <td>
                                @php
                                    $shipping_address = json_decode($order->shipping_address, true);
                                @endphp
                                @if ($order->user_id)
                                    <a href="{{ route('customers.details', $order->user_id) }}" target="_blank" class="font-weight-bold">{{ data_get($shipping_address, 'name') }}</a>
                                @else
                                    {{ data_get($shipping_address, 'name') }}
                                @endif
                                <br>
                                {{ data_get($shipping_address, 'phone') }}
                            </td>
                            <td>{{ single_price(get_order_grand_total($order)) }}</td>
                            <td>
                                @php
                                    switch(strtolower($order->delivery_status)) {
                                        case 'pending':
                                            $badge_class = 'warning';
                                            break;
                                        case 'delivered':
                                        case 'confirmed':  // Same code for both values
                                            $badge_class = 'success';
                                            break;
                                        case 'processing':
                                        case 'hold':
                                            $badge_class = 'info';
                                            break;
                                        case 'cancelled':
                                            $badge_class = 'danger';
                                            break;
                                        default:
                                            $badge_class = 'primary';
                                            break;
                                    }
                                @endphp
                                <span class="badge badge-inline badge-{{ $badge_class }} font-weight-bold">
                                    {{ strtoupper(str_replace('_',' ',$order->delivery_status)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-inline badge-success">{{strtoupper($order->order_source)}}</span>
                            </td>
                            <td>{{ date('d-m-Y', strtotime($order->created_at)) }}</td>
                            <td class="text-right">
                                <a href="{{ route('tickets.create', $order->id) }}"><span class="badge badge-inline badge-danger font-weight-bold">CREATE</span></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                @if ($orders->isNotEmpty())
                    {{ $orders->appends(request()->input())->links() }}
                @endif
            </div>
        </div>
    </form>
</div>
@endsection