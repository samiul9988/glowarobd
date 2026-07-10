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
        <form action="{{ route('coupon-assigned.details') }}" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-md-0 h6">Coupon Assignment Details</h5>
                </div>

                <div class="col-lg-2">
                    <div class="form-group mb-0">
                        <input type="text" class="aiz-date-range form-control" value="{{ @$filter_date }}" name="filter_date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group mb-0">
                        <select class="form-control aiz-selectpicker" name="staff" id="staff" data-live-search="true">
                            <option value="">{{ ('Filter by Staff') }}</option>
                            @foreach ($employees as $id => $name)
                                <option value="{{ $id }}" @if (@$staff_id == $id) selected @endif>{{ ucfirst($name) }}</option>
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
        </form>
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th data-breakpoints="lg" width="5%">#</th>
                        <th>Customer Info</th>
                        <th>Order Info @include('components.tooltip', ['title' => 'Excluding Returned & Cancelled Orders'])</th>
                        <th>Coupon Info</th>
                        <th>Assigner Info</th>
                    </tr>
                </thead>
                <tbody id="report_table">
                    @foreach($details as $key => $detail)
                        <tr>
                            <td width="5%">{{ ($key+1) + ($details->currentPage() - 1)*$details->perPage() }}</td>
                            <td>
                                <span class="d-block">{{ $detail->customer?->name  ?? 'N/A'}}</span>
                                @if($detail->customer?->email)
                                    <span class="d-block">{{ $detail->customer?->email ?? 'N/A' }}</span>
                                @endif
                                @if($detail->customer?->phone)
                                    <span class="d-block">{{ $detail->customer?->phone ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $orders = $detail->customer?->orders?->where('delivery_status', '!=', 'cancelled')->where('delivery_status', '!=', 'returned');
                                    if($orders) {
                                        $orderCount = $orders->count();
                                        $orderSum = $orders->sum('grand_total');
                                    } else {
                                        $orderCount = 0;
                                        $orderSum = 0;
                                    }
                                @endphp
                                <span class="d-block">
                                    Total Order: {{ $orderCount ?? 0 }}
                                </span>
                                <span class="d-block">
                                    Order Amount: {{ single_price($orderSum ?? 0) }}
                                </span>
                            </td>
                            <td>
                                <span class="d-block">Code: {{ $detail->coupon?->code ?? 'N/A' }}</span>
                                <span class="d-block">
                                    @if($detail->coupon?->discount_type == 'percent')
                                        Discount: {{ $detail->coupon?->discount ?? 0 }}%
                                    @else
                                        Discount: {{ single_price($detail->coupon?->discount ?? 0) }}
                                    @endif
                                </span>
                                <span class="d-block">Valid Till: {{ \Carbon\Carbon::parse($detail->expire_date)->format('d-m-Y') }}</span>
                                <span class="d-block">Expire In: {{ \Carbon\Carbon::parse($detail->expire_date)->diffInDays(\Carbon\Carbon::now()) }} days</span>
                            </td>
                            <td>
                                <span class="d-block">By - {{ $detail->assigner?->name ?? 'N/A' }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $details->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection
