@extends('backend.layouts.app')

@section('content')
@if(blank(request()->date))
<div class="alert alert-info">
    Note: This report is generated for last 7 days by default.
</div>
@endif
<div class="row  gutters-5 mb-2">
    <div class="col-md-6  ">
        <div class="card h-100" >
            <div  class=" mx-auto" style="max-width:320px;width:100%;">
                <div class="card-header justify-content-center">
                    <h6 class="mb-0 fs-14">Ratio by User Types</h6>
                </div>
                <div class="card-body">
                    <div id="cancel-ratio-by-user-types-loading" class="d-flex justify-content-center align-items-center"
                        style="height: 305px !important; font-size: 20px !important;">
                        <i class="las la-circle-notch la-spin la-3x text-info"></i>
                    </div>

                    <canvas id="cancel-ratio-by-user-types" class="d-none" height="305"></canvas>
                </div>

            </div>
        </div>
    </div>
    <div class="col-md-6 mx-auto justify-content-center ">
        <div class="card h-100">
            <div class=" mx-auto" style="max-width:360px;width:100%;">
                <div class="card-header justify-content-center">
                    <h6 class="mb-0 fs-14">Ratio by Reasons</h6>
                </div>
                <div class="card-body">
                    <div id="cancel-ratio-by-reasons-loading" class="d-flex justify-content-center align-items-center"
                        style="height: 305px !important; font-size: 20px !important;">
                        <i class="las la-circle-notch la-spin la-3x text-info"></i>
                    </div>

                    <canvas id="cancel-ratio-by-reasons" class="d-none" height="305"></canvas>
                </div>

            </div>
        </div>
    </div>
</div>


<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header">
            <div class="col px-0">
                <h5 class="mb-md-0 h6">{{ ('Order Cancellation Report') }}</h5>
            </div>
            <div class="col-auto">
                <div class="alert alert-info mb-0 py-2">
                    Total Cancellation: <strong>{{ $cancellations->total() }}</strong>
                </div>
            </div>
        </div>
        <div class="card-header row gutters-5 justify-content-start">
            <div class="col-md-2 mb-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control-sm form-control" value="{{ request()->date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="source" name="source">
                    <option value="">{{ ('Order Source') }}</option>
                    <option value="android" @if (strtolower($order_source)=='android') selected @endif>{{ ('Android') }}</option>
                    <option value="IOS" @if (strtolower($order_source)=='ios') selected @endif>{{ ('iOS') }}</option>
                    <option value="website" @if (strtolower($order_source)=='website') selected @endif>{{ ('Website') }}</option>
                    <option value="POS" @if (strtolower($order_source)=='pos') selected @endif>{{ ('POS') }}</option>
                    <option value="merchant" @if (strtolower($order_source)=='merchant') selected @endif>{{ ('Merchant') }}</option>
                    <option value="showroom" @if (strtolower($order_source)=='showroom') selected @endif>{{ ('Showroom') }}</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="cancelled_by" name="cancelled_by">
                    <option value="">{{ ('Cancelled By') }}</option>
                    @foreach ($user_types as $user_type)
                        <option value="{{ $user_type }}" @if (strtolower($cancelled_by)==$user_type) selected @endif>{{ ucfirst($user_type) }}</option>
                    @endforeach
                </select>
            </div>
            {{-- <div class="col-lg-2 mb-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" @isset($search) value="{{ $search }}" @endisset placeholder="{{ ('Type Order code & customer') }}">
                </div>
            </div> --}}
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
                        <th>{{ ('#') }}</th>
                        <th>{{ ('Order Info') }}</th>
                        <th>{{ ('Customer') }}</th>
                        <th data-breakpoints="sm">{{ ('Order Source')}}</th>
                        <th>{{ ('Reason') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($cancellations as $key => $cancellation)
                        @php
                            $shipping_info = json_decode($cancellation->order?->shipping_address, true);
                        @endphp
                        <tr>
                            <td>{{ ($key+1) + ($cancellations->currentPage() - 1) * $cancellations->perPage() }}</td>
                            <td>
                                <a href="{{route('all_orders.show', encrypt($cancellation->order_id))}}" target="_blank">
                                    {{ $cancellation->order?->code }}
                                </a>
                                <span class="d-block">{{ $cancellation->order?->created_at->format('d-m-Y h:i A') }}</span>
                            </td>
                            <td>
                                <span class="d-block">{{ $shipping_info['name'] ?? 'N/A' }}</span>
                                @if(filled($shipping_info['email'] ?? ''))
                                    <span class="d-block">{{ $shipping_info['email'] }}</span>
                                @endif
                                @if(filled($shipping_info['phone'] ?? ''))
                                    <span class="d-block">{{ $shipping_info['phone'] }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-inline badge-success">
                                    {{ ucfirst($cancellation->order?->order_source) }}
                                </span>
                            </td>
                            <td>
                                <span class="d-block">{{ ucfirst($cancellation->reason) }}</span>
                                <span class="d-block text-muted">- by <strong>{{ ucfirst($cancellation->user_type === 'customer' ? 'customer' : $cancellation->cancelledBy?->name) }}</strong> at {{ $cancellation->created_at->format('d-m-Y h:i A') }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $cancellations->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
    <script>
        getRatioCharts();
        function getRatioCharts(){
            const dateRange = $('input[name=date]').val();
            $('#cancel-ratio-by-user-types-loading').removeClass('d-none');
            $('#cancel-ratio-by-reasons-loading').removeClass('d-none');
            $('#cancel-ratio-by-user-types').addClass('d-none');
            $('#cancel-ratio-by-reasons').addClass('d-none');

            $.ajax({
                url: '{{ route("admin.orderCancellationReport.ratio") }}',
                type: 'GET',
                data: {
                    date: dateRange
                },
                success: function(response) {
                    if (response.success) {
                        renderGraph('ratioByUserTypes', response.ratioByUserTypes);
                        renderGraph('ratioByReasons', response.ratioByReasons);
                    } else {
                        AIZ.plugins.notify('error', 'Something Went Wrong!');
                        console.error('Failed to fetch cancellation report ratio data.');
                    }
                },
                error: function(xhr, status, error) {
                    AIZ.plugins.notify('error', 'Server Error!');
                    console.error('An error occurred while fetching the data.');
                }
            });
        }

        function renderGraph(type, data) {
            let chartContainerId = '';
            if (type === 'ratioByUserTypes') {
                chartContainerId = 'cancel-ratio-by-user-types';
            } else {
                chartContainerId = 'cancel-ratio-by-reasons';
            }

            const labels = Object.keys(data);
            const values = Object.values(data);
            const backgroundColors = [
                "#fd3995",
                "#34bfa3",
                "#5d78ff",
                "#fdcb6e",
                "#d35400",
                "#8e44ad",
                "#006442",
                "#4D8FAC",
                "#CA6924",
                "#C91F37"
            ];

            // Destroy previous chart if exists
            if (window[`${chartContainerId}Chart`]) {
                window[`${chartContainerId}Chart`].destroy();
            }

            const ctx = document.getElementById(chartContainerId).getContext('2d');
            window[`${chartContainerId}Chart`] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: backgroundColors.slice(0, labels.length)
                    }]
                },
                options: {
                    cutoutPercentage: 70,
                    legend: {
                        labels: {
                            fontFamily: 'Montserrat',
                            boxWidth: 10,
                            usePointStyle: true
                        },
                        onClick: function() {
                            return '';
                        },
                        position: 'bottom'
                    }
                }
            });

            // Toggle loading/visibility
            $(`#${chartContainerId}-loading`).addClass('d-none').removeClass('d-flex');
            $(`#${chartContainerId}`).removeClass('d-none');
        }
    </script>
@endsection
