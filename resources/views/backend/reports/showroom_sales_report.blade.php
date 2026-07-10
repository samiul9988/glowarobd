@extends('backend.layouts.app')

@section('content')
@if(blank(request()->date))
<div class="alert alert-info">
    Note: This report is generated for last 7 days by default.
</div>
@endif

<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header">
            <div class="col px-0">
                <h5 class="mb-md-0 h6">Showroom Sales Report</h5>
            </div>
        </div>
        <div class="card-header row gutters-5 justify-content-start">
            <div class="col-md-2 mb-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control-sm form-control" value="{{ request()->date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-md-2 mb-2">
                <div class="form-group mb-0">
                    <select name="payment_status" id="payment_status" class="form-control form-control-sm aiz-selectpicker" data-live-search="true" data-placeholder="Select Payment Status">
                        <option value="">Select Payment Status</option>
                        <option value="paid" @if(request()->payment_status == 'paid') selected @endif>Paid</option>
                        <option value="unpaid" @if(request()->payment_status == 'unpaid') selected @endif>Unpaid</option>
                        <option value="partial" @if(request()->payment_status == 'partial') selected @endif>Partial</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2 mb-2">
                <div class="form-group mb-0">
                    <select name="payment_method" id="payment_method" class="form-control form-control-sm aiz-selectpicker" data-live-search="true" data-placeholder="Select Payment Method">
                        <option value="" class="text-capitalize">Select Payment Method</option>
                        <option value="bank" class="text-capitalize" {{ request()->payment_method == 'bank' ? 'selected' : '' }}>Bank</option>
                        <option value="card" class="text-capitalize" {{ request()->payment_method == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="bKash" class="text-capitalize" {{ request()->payment_method == 'bKash' ? 'selected' : '' }}>bKash</option>
                        <option value="cash" class="text-capitalize" {{ request()->payment_method == 'cash' ? 'selected' : '' }}>Cash In Hand</option>
                    </select>
                </div>
            </div>
            <div class="col-auto mb-2">
                <div class="form-group mb-0 mt-0">
                    <button type="submit" class="btn btn-sm btn-primary">{{ ('Filter') }}</button>
                    <a href="{{ route('sales_report.showroom') }}" class="btn btn-sm btn-secondary">Clear</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table aiz-table mb-0" id="theTable">
                <thead>
                    <tr>
                        <td colspan="3" class="text-left font-weight-bold">
                            Total Paid: <span class="text-success">{{ single_price(data_get($summary, 'paid_order_amount', 0)) }}</span>
                        </td>
                        <td colspan="3" class="text-right font-weight-bold">
                            Total Unpaid: <span class="text-danger">{{ single_price(data_get($summary, 'unpaid_order_amount', 0)) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>#</th>
                        <th>Order Info</th>
                        <th>Customer</th>
                        <th class="text-center">Order Amount</th>
                        <th class="text-center">Payment Status</th>
                        <th class="text-center">Payment Method</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($orders as $key => $order)
                        <tr>
                            <td>
                                {{ $key + 1 + ($orders->currentPage() - 1) * $orders->perPage() }}
                            </td>
                            <td >
                                <span class="d-block font-weight-bold">
                                    <a href="{{ route('all_orders.show', encrypt($order->id)) }}" target="_blank">
                                        #{{ $order->code }}
                                    </a>
                                </span>
                                <span class="d-block font-weight-bold text-muted">
                                    Date: {{ date('d-m-Y', strtotime($order->created_at)) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $shippingInfo = json_decode($order->shipping_address, true);
                                @endphp
                                <span class="d-block font-weight-bold">
                                    {{ ucwords($shippingInfo['name'] ?? 'Guest') }}
                                </span>
                                @if ($shippingInfo['phone'] ?? false)
                                    <span class="d-block">
                                        {{ $shippingInfo['phone'] }}
                                    </span>
                                @endif
                                @if ($shippingInfo['email'] ?? false)
                                    <span class="d-block">
                                        {{ $shippingInfo['email'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center font-weight-bold">
                                {{ single_price($order->grand_total) }}
                            </td>
                            <td class="text-center">
                                @php
                                    $badge = match($order->payment_status) {
                                        'paid' => 'badge-success',
                                        'unpaid' => 'badge-danger',
                                        'partial' => 'badge-info',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge badge-inline {{ $badge }}">
                                        {{ strtoupper($order->payment_status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @forelse ($order->payments as $payment)
                                    <span class="d-block font-weight-bold">
                                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                        {{ single_price($payment->amount) }}
                                    </span>
                                    @if (strtolower($payment->payment_method) === 'card' && is_numeric($payment->remarks))
                                        STAN: {{ $payment->remarks }}
                                    @endif
                                    @if(!$loop->last)
                                        <hr class="my-1 mx-auto" style="max-width: 100px; border-style: dashed;">
                                    @endif
                                @empty
                                    <span class="d-block font-weight-bold text-muted">
                                        N/A
                                    </span>
                                @endforelse
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                @if($orders)
                    {{ $orders->appends(request()->input())->links() }}
                @endif
            </div>
        </div>
    </form>
</div>
@endsection
