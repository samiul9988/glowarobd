@extends('backend.layouts.app')
@section('content')
@if (@$filter_date == '')
    <div class="alert alert-info">
        Note: Current statistics coming based on today.
    </div>
@endif
<div class="row mb-3">
    <div class="col d-flex flex-row-reverse">
        <form class="form-inline" action="{{ route('admin.dashboard') }}" id="sort_orders" method="GET">
            <div class="form-group mr-2">
                <input type="text" class="aiz-date-range form-control" value="@if(isset($filter_date)){{$filter_date}}@endif" name="filter_date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off" style="min-width:205px">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
            </div>
        </form>
    </div>
</div>
<div class="row gutters-10">
    <div class="col-lg-4">
        <div class="row gutters-10">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0 fs-14">{{ ('Employee Details') }}</h6>
                    </div>
                    <div class="card-body">
                        <p>
                            <strong>{{ ('Name') }}:</strong> {{ $report['name'] }}<br>
                            <strong>{{ ('Email') }}:</strong> {{ $report['email'] }}<br>
                            <strong>{{ ('Phone') }}:</strong> {{ $report['phone'] }}<br>
                            <strong>{{ ('Role') }}:</strong> {{ $report['role'] }}<br>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
                    <div class="px-3 pt-3">
                        <div class="opacity-50">
                            <span class="fs-12 d-block">{{ ('Managed') }}</span>
                            {{ ('Order') }}
                        </div>
                        <div class="h3 fw-700 mb-3">
                            {{ data_get($report, 'managed_orders_count', 0) }}
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                        <path fill="rgba(255,255,255,0.3)" fill-opacity="1"
                            d="M0,128L34.3,112C68.6,96,137,64,206,96C274.3,128,343,224,411,250.7C480,277,549,235,617,213.3C685.7,192,754,192,823,181.3C891.4,171,960,149,1029,117.3C1097.1,85,1166,43,1234,58.7C1302.9,75,1371,149,1406,186.7L1440,224L1440,320L1405.7,320C1371.4,320,1303,320,1234,320C1165.7,320,1097,320,1029,320C960,320,891,320,823,320C754.3,320,686,320,617,320C548.6,320,480,320,411,320C342.9,320,274,320,206,320C137.1,320,69,320,34,320L0,320Z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="col-lg-4">
        <div class="row gutters-10">
            <div class="col-12">
                <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
                    <div class="px-3 pt-3">
                        <div class="opacity-50">
                            <span class="fs-12 d-block">{{ ('Managed') }}</span>
                            {{ ('Order') }}
                        </div>
                        <div class="h3 fw-700 mb-3">
                            {{ data_get($report, 'managed_orders_count', 0) }}
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                        <path fill="rgba(255,255,255,0.3)" fill-opacity="1"
                            d="M0,128L34.3,112C68.6,96,137,64,206,96C274.3,128,343,224,411,250.7C480,277,549,235,617,213.3C685.7,192,754,192,823,181.3C891.4,171,960,149,1029,117.3C1097.1,85,1166,43,1234,58.7C1302.9,75,1371,149,1406,186.7L1440,224L1440,320L1405.7,320C1371.4,320,1303,320,1234,320C1165.7,320,1097,320,1029,320C960,320,891,320,823,320C754.3,320,686,320,617,320C548.6,320,480,320,411,320C342.9,320,274,320,206,320C137.1,320,69,320,34,320L0,320Z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="col-12">
                <div class="bg-grad-4 text-white rounded-lg mb-4 overflow-hidden">
                    <div class="px-3 pt-3">
                        <div class="opacity-50">
                            <span class="fs-12 d-block">{{ ('Called') }}</span>
                            {{ ('Customer') }}
                        </div>
                        <div class="h3 fw-700 mb-3">
                            {{ data_get($report, 'call_count', 0) }}
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                        <path fill="rgba(255,255,255,0.3)" fill-opacity="1"
                            d="M0,128L34.3,112C68.6,96,137,64,206,96C274.3,128,343,224,411,250.7C480,277,549,235,617,213.3C685.7,192,754,192,823,181.3C891.4,171,960,149,1029,117.3C1097.1,85,1166,43,1234,58.7C1302.9,75,1371,149,1406,186.7L1440,224L1440,320L1405.7,320C1371.4,320,1303,320,1234,320C1165.7,320,1097,320,1029,320C960,320,891,320,823,320C754.3,320,686,320,617,320C548.6,320,480,320,411,320C342.9,320,274,320,206,320C137.1,320,69,320,34,320L0,320Z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
    </div> --}}
    <div class="col-lg-4">
        <div class="row gutters-10">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0 fs-14">{{ ('Order Logs') }}</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="order-logs-pie" class="w-100" height="305"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row gutters-10">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <canvas id="call-logs-graph" class="w-100" height="500"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script>
        AIZ.plugins.chart('#order-logs-pie', {
            type: 'doughnut',
            data: {
                labels: [
                    '{{ ('Viewed') }}',
                    '{{ ('Created') }}',
                    '{{ ('Updated') }}',
                    '{{ ('Deleted') }}',
                    '{{ ('Packaged') }}'
                ],
                datasets: [{
                    data: [
                        {{ data_get($report, 'view_count', 0) }},
                        {{ data_get($report, 'create_count', 0) }},
                        {{ data_get($report, 'update_count', 0) }},
                        {{ data_get($report, 'delete_count', 0) }},
                        {{ data_get($report, 'package_count', 0) }}
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
                }
            }
        });

        // AIZ.plugins.chart('#call-logs-pie', {
        //     type: 'doughnut',
        //     data: {
        //         labels: [
        //             '{{ ('Website') }}',
        //             '{{ ('POS') }}',
        //             '{{ ('Android') }}',
        //             '{{ ('iOS') }}'
        //         ],
        //         datasets: [{
        //             data: [
        //                 {{ \App\Models\Order::where('delivery_status' , '!=', 'cancelled')->where('order_source', 'WEBSITE')->count() }},
        //                 {{ \App\Models\Order::where('delivery_status' , '!=', 'cancelled')->where('order_source', 'POS')->count() }},
        //                 {{ \App\Models\Order::where('delivery_status' , '!=', 'cancelled')->where('order_source', 'Android')->count() }},
        //                 {{ \App\Models\Order::where('delivery_status' , '!=', 'cancelled')->where('order_source', 'IOS')->count() }}
        //             ],
        //             backgroundColor: [
        //                 "#fd3995",
        //                 "#34bfa3",
        //                 "#5d78ff",
        //                 '#fdcb6e',
        //                 '#d35400',
        //                 '#8e44ad',
        //                 '#006442',
        //                 '#4D8FAC',
        //                 '#CA6924',
        //                 '#C91F37'
        //             ]
        //         }]
        //     },
        //     options: {
        //         cutoutPercentage: 70,
        //         legend: {
        //             labels: {
        //                 fontFamily: 'Montserrat',
        //                 boxWidth: 10,
        //                 usePointStyle: true
        //             },
        //             onClick: function() {
        //                 return '';
        //             },
        //             position: 'bottom'
        //         }
        //     }
        // });

        AIZ.plugins.chart('#call-logs-graph', {
            type: 'bar',
            data: {
                labels: [
                    @foreach ($report['callLogs'] as $key => $callLog)
                        '{{ $callLog->created_at->format('d M Y \a\t h:m a') }}',
                    @endforeach
                ],
                datasets: [{
                    label: '{{ ('Call Logs') }}',
                    data: [
                        @foreach ($report['callLogs'] as $key => $$callLog)
                            {{ $callLog->duration ?? 0 }},
                        @endforeach

                    ],
                    backgroundColor: [
                        @foreach ($report['callLogs'] as $key => $callLog)
                            'rgba(55, 125, 255, 0.4)',
                        @endforeach
                    ],
                    borderColor: [
                        @foreach ($report['callLogs'] as $key => $callLog)
                            'rgba(55, 125, 255, 1)',
                        @endforeach
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                tooltips: {
                    enabled: true,
                    mode: 'index',
                    callbacks: {
                        label: function(tooltipItem, data) {
                            @foreach ($report['callLogs'] as $key => $callLog)
                               if (tooltipItem.index == {{ $key }}) {
                                   return [
                                       '{{ ucwords(str_replace('_',' ',$callLog->status ?? 'N/A')) }}',
                                       '{{ ('Duration') }}: ' + tooltipItem.yLabel + ' sec'
                                   ];
                               }
                            @endforeach
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        gridLines: {
                            color: '#f2f3f8',
                            zeroLineColor: '#f2f3f8'
                        },
                        ticks: {
                            fontColor: "#8b8b8b",
                            fontFamily: 'Poppins',
                            fontSize: 10,
                            beginAtZero: true
                        }
                    }],
                    xAxes: [{
                        gridLines: {
                            color: '#f2f3f8'
                        },
                        ticks: {
                            fontColor: "#8b8b8b",
                            fontFamily: 'Poppins',
                            fontSize: 10
                        }
                    }]
                },
                legend: {
                    labels: {
                        fontFamily: 'Poppins',
                        boxWidth: 10,
                        usePointStyle: true
                    },
                    onClick: function() {
                        return '';
                    },
                }
            }
        });
    </script>
@endsection