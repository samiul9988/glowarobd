@extends('backend.layouts.app')

@section('content')
<style>
    th{
        text-align: left;
        padding: 0 5px;
    }
    td{
        text-align: left;
        padding: 0 5px;
    }
</style>
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="d-flex justify-content-between gutters-10">
        <div class="col-md-8">
            <div class="align-items-center">
                <div>
                    <div class="mr-2">
                        @if ($supplier->logo != null)
                            <img src="{{ uploaded_asset($supplier->logo) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/logoipsum.png') }}';" width="200">
                        @else
                            <img src="{{ static_asset('assets/img/logoipsum.png') }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/logoipsum.png') }}';" width="100">
                        @endif
                    </div>
                    <h3 class="m-0 my-1 mr-2">{{ ($supplier->name)}}</h3>
                </div>
            </div>
            @if($supplier->address != null)
                <span>{{ $supplier->address }}</span>
            @endif
        </div>
        <div class="col-md-4 d-flex justify-content-end">
            <table class="table-bordered w-100">
                <tbody>
                    <tr>
                        <th colspan="2" class="text-white bg-secondary">Account Summary</th>
                    </tr>
                    <tr>
                        <th>Total Purchase: </th>
                        <td>{{ single_price($supplier->total_purchase ?? 0) }}</td>
                    </tr>
                    <tr>
                        <th>Total Payments: </th>
                        <td>{{ single_price($supplier->total_paid ?? 0) }}</td>
                    </tr>
                    <tr>
                        <th>Advance Payments: </th>
                        <td>{{ single_price($advancePayments) }}</td>
                    </tr>
                    <tr>
                        <th>Total Returned Amount: </th>
                        <td>{{ single_price($supplier->returned_amount ?? 0) }}</td>
                    </tr>
                    <tr>
                        <th>Total Due Balance: </th>
                        <td>{{ single_price($total_due) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="btn-group btn-sm" role="group" aria-label="Basic example">
                <a href="{{ request()->fullUrlWithQuery(['history' => 'purchase', 'page' => 1]) }}" class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center @if(request('history') !== 'returned') active @endif">
                    Purchase History
                </a>
                <a href="{{ request()->fullUrlWithQuery(['history' => 'returned', 'page' => 1]) }}" class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center @if(request('history') === 'returned') active @endif">
                    Return History
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="md">Date</th>
                    <th>{{ request('history', 'purchase') === 'purchase' ? 'Purchase Order Number' : 'Return Order Number' }}</th>
                    <th data-breakpoints="md">Seller / Creator</th>
                    <th>Num. of Products</th>
                    <th data-breakpoints="md">Amount</th>
                    @if (request('history', 'purchase') === 'purchase')
                        <th data-breakpoints="md">Payment Status</th>
                    @endif
                    <th data-breakpoints="md">Options</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $key => $record)
                <tr>
                    <td>{{ date('d-m-Y', $record['date']) }}</td>
                    <td>
                        <a href="{{ $record['link'] }}" title="{{ ('See Invoice') }}">{{ $record['number'] }}</a>
                    </td>
                    <td>
                        {{ $record['seller_name'] }}
                    </td>
                    <td>
                        {{ $record['num_products'] }}
                        <small class="d-block text-secondary">Total Qty: {{ $record['total_qty'] }}</small>
                    </td>
                    <td>
                        <strong>{{ single_price($record['amount']) }}</strong> <br />
                        @if($record['amount'] < $record['total_qty'])
                        <span class="text-sm text-secondary">Due: {{single_price($record['amount'] - $record['total_qty'])}}</span>
                        @endif
                    </td>
                    @if (request('history', 'purchase') === 'purchase')
                        <td>
                            @php
                                $statusColor = match(strtolower($record['payment_status'] ?? '')) {
                                    'paid' => 'success',
                                    'partial' => 'warning',
                                    'unpaid' => 'secondary',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="w-100 badge badge-pill badge-{{ $statusColor }}">{{ $record['payment_status'] }}</span>
                            {{-- @if($purchaseOrder->total_payment <= 0)
                                <span class="w-100 badge badge-pill badge-secondary">Unpaid</span>
                            @elseif($purchaseOrder->total_payment > 0 && $purchaseOrder->total_payment < $purchaseOrder->grand_total)
                                <span class="w-100 badge badge-pill badge-warning">Partial</span>
                            @elseif($purchaseOrder->total_payment >= $purchaseOrder->grand_total)
                                <span class="w-100 badge badge-pill badge-success">Paid</span>
                            @else
                                <span class="w-100 badge badge-pill badge-secondary">Unpaid</span>
                            @endif --}}
                        </td>
                    @endif
                    <td>
                        <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ $record['link'] }}" title="{{ ('View') }}">
                            <i class="las la-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="aiz-pagination">
            {{ $collection->appends(request()->input())->links() }}
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