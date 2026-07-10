@extends('backend.layouts.app')
@section('content')
    @include('backend.dashboard.partials.attendance')

    @if (config('mail.from.address') == null && config('mail.password') == null)
        <div class="">
            <div class="alert alert-danger d-flex align-items-center">
                {{ 'Please Configure SMTP Setting to work all email sending functionality' }},
                <a class="alert-link ml-2" href="{{ route('smtp_settings.index') }}">{{ 'Configure Now' }}</a>
            </div>
        </div>
    @endif

    <div class="alert alert-info" id="dashboard-info-alert">
        Note: Current statistics coming based on last 7 days.
    </div>

    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <div class="d-md-flex d-block">
                @if (get_setting('enable_dashboard_cache') == 1 && isset($cached_at))
                    <div class="alert alert-info">
                        This page is cached at <span id="cache-time"></span>. <a role="button" class="text-primary"
                            id="resetCache">Reload</a>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="d-flex justify-content-end">
                <div class="form-group mr-2">
                    <input type="text" class="aiz-date-range form-control" id="filter_date" name="filter_date"
                        placeholder="Filter by date" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true"
                        autocomplete="off" style="min-width:205px">
                </div>
                <div class="form-group">
                    <input type="hidden" name="submit" value="yes" />
                    <button type="submit" class="btn btn-primary" onclick="regenerateGraphs()">Filter</button>
                </div>
            </div>
        </div>
    </div>

    <div id="messages p-4"></div>
    @php
        if (isset($_GET['filter_date']) && $_GET['filter_date'] != '') {
            $filter_date = $_GET['filter_date'];
            $from = date('Y-m-d 00:00:00', strtotime(explode(' to ', $filter_date)[0]));

            $to = date('Y-m-d 23:59:59', strtotime(explode(' to ', $filter_date)[1]));
        } else {
            $from = date('Y-m-d 00:00:00', strtotime('-6 Days'));
            $to = date('Y-m-d 23:59:59');
        }
    @endphp

    <div class="row gutters-10">
        <div class="col-lg-6">
            <div class="row gutters-10">
                <div class="col-6">
                    <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
                        <div class="px-3 pt-3">
                            <div class="opacity-50">
                                <span class="fs-12 d-block">{{ 'Pending' }}</span>
                                {{ 'Order' }}
                            </div>
                            <div class="opacity-50 count-loading" style="font-size: 16px !important;">
                                <i class="las la-circle-notch la-spin la-3x"></i>
                            </div>
                            <div class="h3 fw-700 mb-3 d-none" id="pending-order-count">
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                            <path fill="rgba(255,255,255,0.3)" fill-opacity="1"
                                d="M0,128L34.3,112C68.6,96,137,64,206,96C274.3,128,343,224,411,250.7C480,277,549,235,617,213.3C685.7,192,754,192,823,181.3C891.4,171,960,149,1029,117.3C1097.1,85,1166,43,1234,58.7C1302.9,75,1371,149,1406,186.7L1440,224L1440,320L1405.7,320C1371.4,320,1303,320,1234,320C1165.7,320,1097,320,1029,320C960,320,891,320,823,320C754.3,320,686,320,617,320C548.6,320,480,320,411,320C342.9,320,274,320,206,320C137.1,320,69,320,34,320L0,320Z">
                            </path>
                        </svg>
                    </div>
                </div>
                <div class="col-6">
                    <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
                        <div class="px-3 pt-3">
                            <div class="opacity-50">
                                <span class="fs-12 d-block">{{ 'Total' }}</span>
                                {{ 'Order' }}
                            </div>
                            <div class="opacity-50 count-loading" style="font-size: 16px !important;">
                                <i class="las la-circle-notch la-spin la-3x"></i>
                            </div>
                            <div class="h3 fw-700 mb-3 d-none" id="total-order-count">
                                <i class="las la-circle-notch la-spin la-3x text-info"></i>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                            <path fill="rgba(255,255,255,0.3)" fill-opacity="1"
                                d="M0,128L34.3,112C68.6,96,137,64,206,96C274.3,128,343,224,411,250.7C480,277,549,235,617,213.3C685.7,192,754,192,823,181.3C891.4,171,960,149,1029,117.3C1097.1,85,1166,43,1234,58.7C1302.9,75,1371,149,1406,186.7L1440,224L1440,320L1405.7,320C1371.4,320,1303,320,1234,320C1165.7,320,1097,320,1029,320C960,320,891,320,823,320C754.3,320,686,320,617,320C548.6,320,480,320,411,320C342.9,320,274,320,206,320C137.1,320,69,320,34,320L0,320Z">
                            </path>
                        </svg>
                    </div>
                </div>
                {{-- <div class="col-6">
                        <div class="bg-grad-1 text-white rounded-lg mb-4 overflow-hidden">
                            <div class="px-3 pt-3">
                                <div class="opacity-50">
                                    <span class="fs-12 d-block">{{ ('Total') }}</span>
                                    {{ ('Product category') }}
                                </div>
                                <div class="h3 fw-700 mb-3">
                                    {{ \App\Models\Category::whereBetween('created_at', [$from, $to])->count() }}</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                                <path fill="rgba(255,255,255,0.3)" fill-opacity="1"
                                    d="M0,128L34.3,112C68.6,96,137,64,206,96C274.3,128,343,224,411,250.7C480,277,549,235,617,213.3C685.7,192,754,192,823,181.3C891.4,171,960,149,1029,117.3C1097.1,85,1166,43,1234,58.7C1302.9,75,1371,149,1406,186.7L1440,224L1440,320L1405.7,320C1371.4,320,1303,320,1234,320C1165.7,320,1097,320,1029,320C960,320,891,320,823,320C754.3,320,686,320,617,320C548.6,320,480,320,411,320C342.9,320,274,320,206,320C137.1,320,69,320,34,320L0,320Z">
                                </path>
                            </svg>
                        </div>
                    </div> --}}
                <div class="col-6">
                    <div class="bg-grad-1 text-white rounded-lg mb-4 overflow-hidden">
                        <div class="px-3 pt-3">
                            <div class="opacity-50">
                                <span class="fs-12 d-block">{{ 'Total' }}</span>
                                {{ 'SMS Remainings' }}
                            </div>
                            <div class="opacity-50 count-loading" style="font-size: 16px !important;">
                                <i class="las la-circle-notch la-spin la-3x"></i>
                            </div>
                            <div class="h3 fw-700 mb-3 d-none" id="sms-balance">
                                <i class="las la-circle-notch la-spin la-3x text-info"></i>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                            <path fill="rgba(255,255,255,0.3)" fill-opacity="1"
                                d="M0,128L34.3,112C68.6,96,137,64,206,96C274.3,128,343,224,411,250.7C480,277,549,235,617,213.3C685.7,192,754,192,823,181.3C891.4,171,960,149,1029,117.3C1097.1,85,1166,43,1234,58.7C1302.9,75,1371,149,1406,186.7L1440,224L1440,320L1405.7,320C1371.4,320,1303,320,1234,320C1165.7,320,1097,320,1029,320C960,320,891,320,823,320C754.3,320,686,320,617,320C548.6,320,480,320,411,320C342.9,320,274,320,206,320C137.1,320,69,320,34,320L0,320Z">
                            </path>
                        </svg>
                    </div>
                </div>

                <div class="col-6">
                    <div class="bg-grad-4 text-white rounded-lg mb-4 overflow-hidden">
                        <div class="px-3 pt-3">
                            <div class="opacity-50">
                                <span class="fs-12 d-block">{{ 'New Customer' }}</span>
                                &nbsp;
                            </div>
                            <div class="opacity-50 count-loading" style="font-size: 16px !important;">
                                <i class="las la-circle-notch la-spin la-3x"></i>
                            </div>
                            <div class="h3 fw-700 mb-3 d-none" id="new-customer-count">
                                <i class="las la-circle-notch la-spin la-3x text-info"></i>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                            <path fill="rgba(255,255,255,0.3)" fill-opacity="1"
                                d="M0,128L34.3,112C68.6,96,137,64,206,96C274.3,128,343,224,411,250.7C480,277,549,235,617,213.3C685.7,192,754,192,823,181.3C891.4,171,960,149,1029,117.3C1097.1,85,1166,43,1234,58.7C1302.9,75,1371,149,1406,186.7L1440,224L1440,320L1405.7,320C1371.4,320,1303,320,1234,320C1165.7,320,1097,320,1029,320C960,320,891,320,823,320C754.3,320,686,320,617,320C548.6,320,480,320,411,320C342.9,320,274,320,206,320C137.1,320,69,320,34,320L0,320Z">
                            </path>
                        </svg>
                    </div>
                </div>

                {{-- <div class="col-6">
                        <div class="bg-grad-4 text-white rounded-lg mb-4 overflow-hidden">
                            <div class="px-3 pt-3">
                                <div class="opacity-50">
                                    <span class="fs-12 d-block">{{ ('Total') }}</span>
                                    {{ ('Product brand') }}
                                </div>
                                <div class="h3 fw-700 mb-3">{{ \App\Models\Brand::count() }}</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                                <path fill="rgba(255,255,255,0.3)" fill-opacity="1" d="M0,128L34.3,112C68.6,96,137,64,206,96C274.3,128,343,224,411,250.7C480,277,549,235,617,213.3C685.7,192,754,192,823,181.3C891.4,171,960,149,1029,117.3C1097.1,85,1166,43,1234,58.7C1302.9,75,1371,149,1406,186.7L1440,224L1440,320L1405.7,320C1371.4,320,1303,320,1234,320C1165.7,320,1097,320,1029,320C960,320,891,320,823,320C754.3,320,686,320,617,320C548.6,320,480,320,411,320C342.9,320,274,320,206,320C137.1,320,69,320,34,320L0,320Z"></path>
                            </svg>
                        </div>
                    </div> --}}
            </div>
        </div>

        <div class="col-lg-6">
            <div class="row gutters-10">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 fs-14">{{ 'Paid vs Due' }}</h6>
                        </div>
                        <div class="card-body">
                            <div id="pie-1-loading" class="d-flex justify-content-center align-items-center"
                                style="height: 305px !important; font-size: 20px !important;">
                                <i class="las la-circle-notch la-spin la-3x text-info"></i>
                            </div>

                            <canvas id="pie-1" class="w-100 d-none" height="305"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 fs-14">{{ 'Order Source' }}</h6>
                        </div>
                        <div class="card-body">
                            <div id="pie-2-loading" class="d-flex justify-content-center align-items-center"
                                style="height: 305px !important; font-size: 20px !important;">
                                <i class="las la-circle-notch la-spin la-3x text-info"></i>
                            </div>
                            <canvas id="pie-2" class="w-100 d-none" height="305"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link active text-dark" id="order-total-tab" data-toggle="tab" href="#order-total"
                role="tab" aria-controls="order-total" aria-selected="false">Order summery (total)</a>

            <a class="nav-item nav-link text-dark" id="order-amount-tab" data-toggle="tab" href="#order-amount"
                role="tab" aria-controls="order-amount" aria-selected="false">Order summery (amount)</a>

            <a class="nav-item nav-link text-dark" id="top-selling-tab" data-toggle="tab" href="#top-selling"
                role="tab" aria-controls="top-selling" aria-selected="true">Top 10 selling product</a>

            <a class="nav-item nav-link text-dark" id="low-stock-tab" data-toggle="tab" href="#low-stock"
                role="tab" aria-controls="low-stock" aria-selected="true">Low stock product</a>
        </div>
    </nav>

    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="order-total" role="tabpanel" aria-labelledby="order-total-tab">
            <div class="row gutters-10">
                <div class="col-md-12 order_summary_graph">
                    <div class="card">
                        <div class="card-body">
                            <div id="graph-1-loading" class="d-flex justify-content-center align-items-center"
                                style="height: 500px !important; font-size: 20px !important;">
                                <i class="las la-circle-notch la-spin la-3x text-info"></i>
                            </div>
                            <canvas id="graph-1" class="w-100 bar-chart-height-500 d-none" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="order-amount" role="tabpanel" aria-labelledby="order-amount-tab">
            <div class="row gutters-10">
                <div class="col-md-12 order_amount_graph">
                    <div class="card">
                        <div class="card-body">
                            <canvas id="graph-amount" class="w-100 bar-chart-height-500" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="top-selling" role="tabpanel" aria-labelledby="top-selling-tab">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ '#' }}</th>
                                <th style="width:90%">{{ 'Product Name' }}</th>
                                <th style="width:10%" class="text-right">{{ 'Total (Pcs)' }}</th>
                            </tr>
                        </thead>
                        <tbody id="top-10-selling-products-list">
                            <tr>
                                <td colspan="3" class="text-center">
                                    {{ 'No data found' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="low-stock" role="tabpanel" aria-labelledby="low-stock-tab">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ '#' }}</th>
                                <th style="width:90%">{{ 'Product Name' }}</th>
                                <th style="width:10%" class="text-right">{{ 'Total (Pcs)' }}</th>
                            </tr>
                        </thead>
                        <tbody id="append_low_stock_data"></tbody>
                    </table>


                    <button class="btn btn-primary d-flex m-auto" id="loadMoreLowStockProducts" data-page="2">Load
                        More</button>

                </div>
            </div>
        </div>
    </div>

    <style>
        .bar-chart-height-500 {
            height: 550px !important;
        }
    </style>
@endsection

@section('modal')
    @if (Auth::user()->user_type === 'staff')
        <div class="modal fade" id="check-in-modal" tabindex="-1" role="dialog" aria-labelledby="checkInModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="checkInModalLabel">{{ __('Check In') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="check-in-type">{{ __('Check In Type') }}</label>
                            <select name="check-in-type" id="check-in-type" class="form-control form-control-sm">
                                <option value="regular" selected>Regular</option>
                                <option value="alternative">Alternative</option>
                            </select>
                            <small class="text-danger" id="check-in-type-error"></small>
                        </div>
                        <div id="check-in-alter-date-section" style="display: none;">
                            <div class="form-group mb-2">
                                <label for="check-in-alter-date">
                                    {{ __('Alternative Date') }} *
                                </label>
                                <input type="date" id="check-in-alter-date" class="form-control"
                                    placeholder="{{ __('Select a date') }}">
                                <small class="text-danger" id="check-in-alter-date-error"></small>
                            </div>
                            <div class="form-group mb-0">
                                <label for="check-in-alter-note">
                                    {{ __('Note') }} *
                                </label>
                                <textarea rows="3" id="check-in-alter-note" class="form-control" placeholder="{{ __('Enter a note') }}"></textarea>
                                <small class="text-danger" id="check-in-alter-note-error"></small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-light"
                            data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-sm btn-success" id="btn-confirm-checkin">
                            {{ __('Confirm') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    <script>
        // if (window.Pusher) {
        //     try { Pusher.logToConsole = true; } catch (e) { /* ignore if not supported */ }
        // }
        // window.Pusher = Pusher;
        // window.Echo = new Echo({
        //     broadcaster: 'pusher',
        //     key: '{{ env('VITE_REVERB_APP_KEY') }}',
        //     wsHost: '{{ env('VITE_REVERB_HOST') }}',
        //     wsPort: '{{ env('VITE_REVERB_PORT') ?? 80 }}',
        //     wssPort: '{{ env('VITE_REVERB_PORT') ?? 443 }}',
        //     forceTLS: '{{ env('VITE_REVERB_SCHEME', 'https') }}' === 'https',
        //     disableStats: true,
        //     enabledTransports: ['ws', 'wss'],
        // });

        // $().ready(function() {
        //     window.Echo.channel('test-channel')
        //     .listen('.TestEvent', (e) => {
        //         alert(e.message);
        //     });

        //     triggerEvent();
        // });

        // // Function to trigger event
        // async function triggerEvent() {
        //     $.get('/test-broadcast', function(data) {
        //         console.log('Event triggered:', data);
        //     });
        // }
    </script>

    @include('backend.dashboard.partials.attendance_script')

    <script type="text/javascript">
        let filterDate = '';
        let graph1Chart, graphAmountChart;

        regenerateGraphs();

        function chartLoading() {
            $('#pie-1').addClass('d-none');
            $('#pie-2').addClass('d-none');
            $('#graph-1').addClass('d-none');
            $('#graph-1-loading').removeClass('d-none').addClass('d-flex');
            $('#pie-1-loading').removeClass('d-none').addClass('d-flex');
            $('#pie-2-loading').removeClass('d-none').addClass('d-flex');
        }

        function cardLoading(status) {
            if (status) {
                $('.count-loading').removeClass('d-none');
                $('#pending-order-count').addClass('d-none');
                $('#total-order-count').addClass('d-none');
                $('#sms-balance').addClass('d-none');
                $('#new-customer-count').addClass('d-none');
            } else {
                $('.count-loading').addClass('d-none');
                $('#pending-order-count').removeClass('d-none');
                $('#total-order-count').removeClass('d-none');
                $('#sms-balance').removeClass('d-none');
                $('#new-customer-count').removeClass('d-none');
            }
        }

        async function loadPaidDueChart() {
            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: '{{ route('admin.dashboard.order_payments_chart') }}',
                    data: {
                        date: filterDate
                    }
                });
                showPaidDueChart(response);
            } catch (error) {
                if (error.status === 401) {
                    $('#pie-1-loading').html('<span class="text-danger">Unauthorized access</span>');
                } else {
                    console.error('Error loading paid due chart:', error);
                }
            }
        }

        async function loadOrderSourceChart() {
            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: '{{ route('admin.dashboard.order_source_chart') }}',
                    data: {
                        date: filterDate
                    }
                });
                showOrderSourceChart(response);
            } catch (error) {
                if (error.status === 401) {
                    $('#pie-2-loading').html('<span class="text-danger">Unauthorized access</span>');
                } else {
                    console.error('Error loading order source chart:', error);
                }
            }
        }

        async function loadOrderSummeryGraphData() {
            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: '{{ route('admin.dashboard.order_summary_graphs') }}',
                    data: {
                        date: filterDate
                    }
                });
                showOrderSummaryGraph(response);
            } catch (error) {
                if (error.status === 401) {
                    $('#graph-1-loading').html('<span class="text-danger">Unauthorized access</span>');
                } else {
                    console.error('Error loading order summary graph:', error);
                }
            }
        }

        async function regenerateGraphs() {
            chartLoading(true);
            filterDate = $('#filter_date').val();
            if (filterDate !== '') {
                $('#dashboard-info-alert').fadeOut();
            } else {
                $('#dashboard-info-alert').fadeIn();
            }
            loadPaidDueChart();
            loadOrderSourceChart();
            loadOrderSummeryGraphData();
            getTopSellingProducts()
            getCardsData();
        }

        function showPaidDueChart(data) {
            $('#pie-1-loading').addClass('d-none').removeClass('d-flex');
            // Destroy previous chart instance if it exists
            if (window.pieChart1) {
                window.pieChart1.destroy();
            }

            const ctx = document.getElementById('pie-1').getContext('2d');
            window.pieChart1 = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [
                        'Paid Order',
                        'Due Order'
                    ],
                    datasets: [{
                        data: [
                            data.paid_orders ?? 0,
                            data.due_orders ?? 0
                        ],
                        backgroundColor: [
                            "#fd3995",
                            "#34bfa3",
                            "#5d78ff",
                            '#fdcb6e',
                            '#d35400',
                            '#8e44ad',
                            '#006442',
                            '#4D8FAC',
                            '#CA6924',
                            '#C91F37'
                        ]
                    }]
                },
                options: {
                    cutoutPercentage: 70,
                    legend: {
                        labels: {
                            fontFamily: 'Poppins',
                            boxWidth: 10,
                            usePointStyle: true
                        },
                        onClick: function() {
                            return '';
                        },
                        position: 'bottom'
                    },
                    plugins: {
                        datalabels: {
                            display: true,
                            color: '#ffffff',
                            font: {
                                weight: 'bold',
                                size: 14
                            },
                            formatter: function(value) {
                                if (parseInt(value) === 0) {
                                    return '';
                                }
                                return value;
                            }
                        }
                    }
                }
            });

            $('#pie-1').removeClass('d-none');
        }

        function showOrderSourceChart(data) {
            $('#pie-2-loading').addClass('d-none').removeClass('d-flex');
            // Destroy previous chart instance if it exists
            if (window.pieChart2) {
                window.pieChart2.destroy();
            }

            const ctx = document.getElementById('pie-2').getContext('2d');
            window.pieChart2 = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [
                        'Website',
                        'POS',
                        'Android',
                        'iOS',
                        'Merchant',
                        'Showroom'
                    ],
                    datasets: [{
                        data: [
                            data.counts?.website ?? 0,
                            data.counts?.pos ?? 0,
                            data.counts?.android ?? 0,
                            data.counts?.ios ?? 0,
                            data.counts?.merchant ?? 0,
                            data.counts?.showroom ?? 0
                        ],
                        backgroundColor: [
                            "#fd3995",
                            "#34bfa3",
                            "#5d78ff",
                            '#fdcb6e',
                            '#d35400',
                            '#8e44ad',
                            '#006442',
                            '#4D8FAC',
                            '#CA6924',
                            '#C91F37'
                        ]
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
                    },
                    plugins: {
                        datalabels: {
                            display: true,
                            color: '#ffffff',
                            font: {
                                weight: 'bold',
                                size: 14
                            },
                            formatter: function(value) {
                                if (parseInt(value) === 0) {
                                    return '';
                                }
                                return value;
                            }
                        }
                    }
                }
            });

            $('#pie-2').removeClass('d-none');
        }

        function showOrderSummaryGraph(data) {
            $('#graph-1-loading').addClass('d-none').removeClass('d-flex');

            // Default chart options
            const chartOptions = {
                scales: {
                    y: {
                        grid: {
                            color: '#f2f3f8',
                            drawBorder: false
                        },
                        ticks: {
                            color: "#8b8b8b",
                            font: {
                                family: 'Poppins',
                                size: 10
                            },
                            beginAtZero: true
                        }
                    },
                    x: {
                        grid: {
                            color: '#f2f3f8',
                            drawBorder: false
                        },
                        ticks: {
                            color: "#8b8b8b",
                            font: {
                                family: 'Poppins',
                                size: 10
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                family: 'Poppins'
                            },
                            boxWidth: 10,
                            usePointStyle: true
                        },
                        // Disable legend click functionality
                        onClick: () => {}
                    },
                    datalabels: {
                        display: false,
                        color: '#000000',
                        font: {
                            weight: 'bold',
                            size: 14
                        },
                        formatter: function(value) {
                            if (parseInt(value) === 0) {
                                return '';
                            }
                            return value;
                        }
                    }
                }
            };
            // Destroy existing charts if they exist
            if (graph1Chart) graph1Chart.destroy();
            if (graphAmountChart) graphAmountChart.destroy();

            // Common dataset configuration
            const datasetConfig = {
                backgroundColor: 'rgba(55, 125, 255, 0.4)',
                borderColor: 'rgba(55, 125, 255, 1)',
                borderWidth: 1
            };

            // Render order count graph
            graph1Chart = new Chart(
                document.getElementById('graph-1'), {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            ...datasetConfig,
                            label: '{{ 'Number of Order' }}',
                            data: data.orderCounts
                        }]
                    },
                    options: chartOptions
                }
            );

            // Render order amount graph
            graphAmountChart = new Chart(
                document.getElementById('graph-amount'), {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            ...datasetConfig,
                            label: '{{ 'Total' }}',
                            data: data.orderAmounts
                        }]
                    },
                    options: chartOptions
                }
            );

            $('#graph-1').removeClass('d-none');
        }

        async function getTopSellingProducts() {
            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: '{{ route('admin.dashboard.get_top_selling_product') }}',
                    data: {
                        date: filterDate,
                        limit: 10
                    }
                });
                if (response.status) {
                    $('#top-10-selling-products-list').html(response.view);
                }
            } catch (error) {
                $('#top-10-selling-products-list').html(
                    '<tr><td colspan="3" class="text-center text-danger">Error loading data</td></tr>');
            }
        }

        async function getCardsData() {
            cardLoading(true);
            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: '{{ route('admin.dashboard.get_cards_data') }}',
                    data: {
                        date: filterDate
                    }
                });
                if (response.status) {
                    $('#pending-order-count').text(response.data.pending_order_count);
                    $('#total-order-count').text(response.data.total_order_count);
                    $('#new-customer-count').text(response.data.new_customer_count);
                    $('#sms-balance').text(response.data.sms_balance);

                    cardLoading(false);

                    if (parseInt(response.data.sms_balance || 0) < 500) {
                        Swal.fire({
                            title: 'Low SMS Balance',
                            text: 'Your SMS balance is low. Please recharge soon.',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            } catch (error) {
                $('#pending-order-count').text('--');
                $('#total-order-count').text('--');
                $('#new-customer-count').text('--');
                $('#sms-balance').text('--');
                cardLoading(false);
                console.error('Error loading card data:', error);
            }
        }


        $(document).ready(function() {
            @if (get_setting('enable_dashboard_cache') == 1 && @$cached_at)
                function diffForHumans(timestamp) {
                    let past = new Date(timestamp);
                    let now = new Date();
                    let diffInSeconds = Math.floor((now - past) / 1000);

                    if (diffInSeconds < 60) {
                        return diffInSeconds <= 1 ? "just now" : diffInSeconds + " seconds ago";
                    }

                    let diffInMinutes = Math.floor(diffInSeconds / 60);
                    if (diffInMinutes < 60) {
                        return diffInMinutes === 1 ? "1 minute ago" : diffInMinutes + " minutes ago";
                    }

                    let diffInHours = Math.floor(diffInMinutes / 60);
                    if (diffInHours < 24) {
                        return diffInHours === 1 ? "1 hour ago" : diffInHours + " hours ago";
                    }

                    let diffInDays = Math.floor(diffInHours / 24);
                    if (diffInDays < 30) {
                        return diffInDays === 1 ? "yesterday" : diffInDays + " days ago";
                    }

                    let diffInMonths = Math.floor(diffInDays / 30);
                    if (diffInMonths < 12) {
                        return diffInMonths === 1 ? "1 month ago" : diffInMonths + " months ago";
                    }

                    let diffInYears = Math.floor(diffInMonths / 12);
                    return diffInYears === 1 ? "1 year ago" : diffInYears + " years ago";
                }

                // Auto-update every 30s
                setInterval(() => {
                    $('#cache-time').text(diffForHumans(cachedAt));
                }, 30000);

                let cachedAt = "{{ \Carbon\Carbon::parse($cached_at)->toIso8601String() }}";
                // First render
                $('#cache-time').text(diffForHumans(cachedAt));

                $('#resetCache').on('click', function() {
                    resetCache();
                });

                function resetCache() {
                    $.get('{{ route('admin.dashboard.reset_cache') }}', function(data) {
                        if (data.status) {
                            location.reload();
                        } else {
                            AIZ.plugins.notify('danger', '{{ 'Something went wrong' }}');
                        }
                    });
                }
            @endif

            $.get('{{ route('admin.dashboard_LowStockProducts') }}', {
                _token: '{{ csrf_token() }}'
            }, function(data) {
                $('#append_low_stock_data').html(data);
            });

            $(document).on("click", "#loadMoreLowStockProducts", function() {

                $("#loadMoreLowStockProducts").text('Loading...');
                $("#loadMoreLowStockProducts").attr("disabled", "disabled");

                var pageId = parseInt($("#loadMoreLowStockProducts").data("page"));

                $.get('{{ route('admin.dashboard_LowStockProducts') }}', {
                    _token: '{{ csrf_token() }}',
                    page: pageId
                }, function(data) {
                    if (data == '') {
                        $("#loadMoreLowStockProducts").text('No more data');
                        $("#loadMoreLowStockProducts").attr("disabled", "disabled");
                    } else {
                        $('#append_low_stock_data').append(data);

                        pageId++;

                        $("#loadMoreLowStockProducts").data("page", pageId);

                        $("#loadMoreLowStockProducts").text('Load More');

                        $("#loadMoreLowStockProducts").removeAttr("disabled");

                    }
                });
            });
        });
    </script>
@endsection
