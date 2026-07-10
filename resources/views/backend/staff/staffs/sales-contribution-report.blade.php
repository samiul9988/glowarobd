@extends('backend.layouts.app')

@section('content')
    @php
        use \Illuminate\Support\Number;
        $employees = Cache::remember('coupon_staffs', now()->addHours(6), function () {
            return \App\Models\User::where('user_type', 'staff')->pluck('name', 'id')->toArray();
        });
    @endphp
    @if (is_null(@$filter_date))
        <div class="alert alert-info">
            <strong>Note:</strong> This statistics coming based on last 7 days.
        </div>
    @endif
    <div class="card">
        <form action="{{ route('sales-contribution-reports.index') }}" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-md-0 h6">{{ ('Sales Contribution Report') }}</h5>
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
                        <th width="20%">Staff Name</th>
                        <th>Role</th>
                        <th class="text-center">Feedback Count <span class="text-success" data-toggle="tooltip"
                                data-title="{{ ucfirst(Number::spell($summary['total_feedbacks'] ?? 0)) }}">({{ $summary['total_feedbacks'] ?? 0 }})</span></th>
                        <th class="text-center">Coupon Assigned <span class="text-success" data-toggle="tooltip"
                                data-title="{{ ucfirst(Number::spell($summary['total_assigned_coupons'] ?? 0)) }}">({{ $summary['total_assigned_coupons'] ?? 0 }})</span></th>
                        <th class="text-center">Order Count <span class="text-success" data-toggle="tooltip"
                                data-title="{{ ucfirst(Number::spell($summary['total_orders'] ?? 0)) }}">({{ $summary['total_orders'] ?? 0 }})</span></th>
                        <th class="text-center">Order Amount <span class="text-success" data-toggle="tooltip"
                                data-title="{{ ucfirst(Number::spell(round($summary['total_order_amount'] ?? 0))) }}">({{ single_price($summary['total_order_amount'] ?? 0) }})</span></th>
                    </tr>
                </thead>
                <tbody id="report_table">
                    @foreach($staffs as $key => $staff)
                        @php
                            $query = '?filter_date=' . $filter_date;
                            $route = route('log-report.index');
                        @endphp
                        <tr>
                            <td width="5%">{{ ($key+1)  }}</td>
                            <td width="20%">
                                {{ $staff['name'] }}
                            </td>
                            <td>
                                {{ $staff['role'] }}
                            </td>
                            <td class="text-center">
                                @if ($staff['feedback_count'] > 0)
                                    <a target="_blank" href="{{ $route.$query.'&staff='.$staff['id'].'&event=feedback' }}">
                                        {{ $staff['feedback_count'] }}
                                    </a>
                                @else
                                    {{ $staff['feedback_count'] }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if($staff['assigned_coupon_count'] > 0)
                                    <a href="{{ route('coupon-assigned.details', ['staff' => $staff['id'], 'filter_date' => $filter_date]) }}" class="font-weight-bold" target="_blank">{{ $staff['assigned_coupon_count'] }}</a>
                                @else
                                    {{ $staff['assigned_coupon_count'] }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if($staff['used_coupon_count'] > 0)
                                    <a href="{{ route('sales-contribution-reports.details', ['staff' => $staff['id'], 'filter_date' => $filter_date]) }}" class="font-weight-bold" target="_blank">{{ $staff['used_coupon_count'] }}</a>
                                @else
                                    {{ $staff['used_coupon_count'] }}
                                @endif
                            </td>
                            <td class="text-center {{ $staff['order_amount'] > 0 ? 'font-weight-bold' : '' }}">
                                {{ single_price($staff['order_amount']) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('modal')
    <div class="modal fade" id="orders_modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h6">{{ ('Orders')}}</h5>
                    <button type="button" class="close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                <div class="row gutters-5" id="order_list">
                    <div class="col-12 text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">{{ ('Loading...')}}</span>
                        </div>
                    </div>
                </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Close')}}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $().ready(function(){
            const dateRange = @json($filter_date);
            $('#report_table').on('click', '.show_orders', function() {
                var staff_id = $(this).data('staff');
                $('#order_list').html('<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">{{ ('Loading...')}}</span></div></div>');
                // fetchOrders(staff_id, dateRange);
                $('#orders_modal').modal('show');
            });

            function fetchOrders(staff_id, dateRange) {
                $.ajax({
                    url: '#',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        staff: staff_id,
                        filter_date: dateRange
                    },
                    success: function(data) {
                        let html = ``;
                        if(data.orders.length > 0){
                            data.orders.forEach((order, index) => {
                                html += `
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ ('Order ID') }}</th>
                                                <th>{{ ('Customer') }}</th>
                                                <th>{{ ('Date') }}</th>
                                                <th>{{ ('Amount') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><a href="${order.link}" target="_blank">#${order.code}</a></td>
                                                <td>
                                                    <span class="d-block">${order.customer_info.name}</span>
                                                    <span class="d-block">${order.customer_info.email}</span>
                                                    <span class="d-block">${order.customer_info.phone}</span>
                                                </td>
                                                <td>${order.date}</td>
                                                <td>${order.amount}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                `;
                            });
                        } else {
                            html = `<div class="col-12 text-center">{{ ('No orders found.')}}</div>`;
                        }
                        $('#order_list').html(html);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        $('#order_list').html('<div class="col-12 text-center text-danger">{{ ('Something went wrong')}}</div>');
                    }
                });
            }
        });
    </script>
@endsection
