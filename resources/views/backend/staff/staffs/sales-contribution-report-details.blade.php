@extends('backend.layouts.app')

@section('content')
    @php
        $employees = Cache::remember('coupon_staffs', now()->addHours(6), function () {
            return \App\Models\User::where('user_type', 'staff')->pluck('name', 'id')->toArray();
        });
    @endphp
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">{{ ('Sales Contribution Details')}}</h1>
            </div>
        </div>
    </div>
    @if (is_null(@$filter_date))
        <div class="alert alert-info">
            <strong>Note:</strong> This statistics coming based on last 7 days.
        </div>
    @endif
    <div class="card">
        <form action="{{ route('sales-contribution-reports.details') }}" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    @if(@$staff_id && isset($employees[$staff_id]))
                        <h5 class="mb-md-0 h6">{{ ('Sales Contribution -') }} {{ $employees[$staff_id] }}</h5>
                    @else
                        <h5 class="mb-md-0 h6">{{ ('Sales Contribution - All Staffs') }}</h5>
                    @endif
                </div>

                <div class="col-lg-2">
                    <div class="form-group mb-0">
                        <input type="text" class="aiz-date-range form-control form-control-sm" value="{{ @$filter_date }}" name="filter_date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group mb-0">
                        <select class="form-control form-control-sm aiz-selectpicker" name="staff" id="staff" data-live-search="true">
                            <option value="">{{ ('Filter by Staff') }}</option>
                            @foreach ($employees as $id => $name)
                                <option value="{{ $id }}" @if (@$staff_id == $id) selected @endif>
                                    {{ ucfirst($name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group mb-0">
                        <input type="text" name="search" id="search" class="form-control form-control-sm" placeholder="Search by order ID or customer info" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-auto">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        <button type="button" class="btn btn-sm btn-success" id="export">Export</button>
                    </div>
                </div>
            </div>
        </form>
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th data-breakpoints="lg" width="5%">#</th>
                        <th width="20%">Order ID</th>
                        <th class="text-center">Date</th>
                        <th class="text-center">Order Source</th>
                        <th>Customer Info</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Coupon</th>
                        <th class="text-center">Discount</th>
                        <th class="text-center">
                            Order Amount
                            <span class="d-block text-info font-weight-bold fs-10">After Discount</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="report_table">
                    @foreach($usageCoupons as $key => $details)
                        @php
                            $shipping_info = json_decode($details->order->shipping_address, true);
                        @endphp
                        <tr>
                            <td width="5%">{{ ($key+1) + ($usageCoupons->currentPage() - 1)*$usageCoupons->perPage() }}</td>
                            <td width="20%">
                                <a href="{{ route('all_orders.show', encrypt($details->order->id)) }}" class="font-weight-bold" target="_blank">
                                    #{{ $details->order->code }}
                                </a>
                            </td>
                            <td class="text-center">
                                {{ $details->order->created_at->format('d-m-Y h:i A') }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-inline badge-success font-weight-bold">{{ strtoupper($details->order->order_source ?? 'N/A') }}</span>
                            </td>
                            <td>
                                <span class="d-block">{{ data_get($shipping_info, 'name') }}</span>
                                <span class="d-block">{{ data_get($shipping_info, 'email') }}</span>
                                <span class="d-block">{{ data_get($shipping_info, 'phone') }}</span>
                            </td>
                            <td class="text-center">
                                {!! order_status_badge($details->order) !!}
                            </td>
                            <td class="text-center">
                                {{ $details->coupon->code ?? 'N/A' }}
                            </td>
                            <td class="text-center">
                                {{ single_price($details->order->coupon_discount ?? 0) }}
                            </td>
                            <td class="text-center">
                                {{ single_price($details->order->grand_total ?? 0) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $usageCoupons->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('#export').on('click', function() {
            let url = new URL(window.location.href);
            url.searchParams.set('export', '1');
            window.open(url.toString(), '_blank');
        });
    </script>
@endsection
