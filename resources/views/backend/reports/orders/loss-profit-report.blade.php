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
                <h5 class="mb-md-0 h6">Order Loss/Profit Report</h5>
            </div>
        </div>
        <div class="card-header row gutters-5 justify-content-start">
            <div class="col-md-2 mb-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control-sm form-control" value="{{ request()->date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-auto mb-2">
                <div class="form-group mb-0 mt-0">
                    <button type="submit" class="btn btn-sm btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table aiz-table mb-0" id="theTable">
                <thead>
                    <tr>
                        <td colspan="2" class="text-left font-weight-bold">
                            Total Expenses: <a href="{{ route('accounts.reports.expense_report', ['date' => request()->date]) }}" target="_blank" class="text-danger">{{ single_price(data_get($summary, 'total_expenses', 0)) }}</a>
                        </td>
                        <td colspan="7" class="text-right font-weight-bold">Total Sales: {{ single_price($summary['grand_selling']) }} - (Total Purchase: {{ single_price(data_get($summary, 'total_purchase', 0)) }} + Total Delivery {{ single_price(data_get($summary, 'grand_delivery', 0)) }}) = Total {{ data_get($summary, 'grand_profit', 0) < 0 ? 'Loss' : 'Profit' }} <span class="{{ data_get($summary, 'grand_profit', 0) < 0 ? 'text-danger' : 'text-success' }}">{{ single_price(abs(data_get($summary, 'grand_profit', 0))) }} {{ data_get($summary, 'grand_profit', 0) < 0 ? '(-)' : '(+)' }}</span></td>
                    </tr>
                    <tr>
                        <td colspan="9" class="text-center font-weight-bold h6">
                            @php
                                $finalProfit = data_get($summary, 'grand_profit', 0) - data_get($summary, 'total_expenses', 0);
                            @endphp
                            Final {{ $finalProfit < 0 ? 'Loss' : 'Profit' }} = <span class="{{ $finalProfit < 0 ? 'text-danger' : 'text-success' }}">{{ single_price(abs($finalProfit)) }} {{ $finalProfit < 0 ? '(-)' : '(+)' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>#</th>
                        <th>Order Number</th>
                        <th class="text-center">Products</th>
                        <th class="text-center">Purchase Total</th>
                        <th class="text-center">Selling Total</th>
                        <th class="text-center">Delivery Fee</th>
                        <th class="text-center">Discounts</th>
                        <th class="text-center">Profit / Loss</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($orders as $key => $order)
                        @php
                            $discounts = $order->coupon_discount + $order->reward_point_discount;
                            $profit = $order->grand_total - ($order->total_purchase + $order->delivery_fee);
                        @endphp
                        <tr>
                            <td>{{ $orders->firstItem() + $key }}</td>
                            <td>
                                <a href="{{ route('all_orders.show', encrypt($order->id)) }}" target="_blank">
                                    #{{ $order->code }}
                                </a>
                            </td>
                            <td class="text-center">{{ $order->total_items }}</td>
                            <td class="text-center">{{ single_price($order->total_purchase) }}</td>
                            <td class="text-center">{{ single_price($order->grand_total) }}</td>
                            <td class="text-center">{{ single_price($order->delivery_fee) }}</td>
                            <td class="text-center">{{ single_price($discounts) }}</td>
                            <td class="text-center">
                                <span class="{{ $profit < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ single_price(abs($profit)) }} {{ $profit < 0 ? '(-)' : '(+)' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach

                    <tr>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center font-weight-bold">{{ single_price($summary['total_purchase']) }}</td>
                        <td class="text-center font-weight-bold">
                            {{ single_price($summary['grand_selling']) }}
                        </td>
                        <td class="text-center font-weight-bold">{{ single_price($summary['grand_delivery']) }}</td>
                        <td class="text-center font-weight-bold">{{ single_price($summary['grand_discounts']) }}</td>
                        <td class="text-center font-weight-bold">
                            <span class="{{ $summary['grand_profit'] < 0 ? 'text-danger' : 'text-success' }}">
                                {{ single_price(abs($summary['grand_profit'])) }} {{ $summary['grand_profit'] < 0 ? '(-)' : '(+)' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $orders->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>
@endsection
