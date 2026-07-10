@extends('backend.layouts.app')
@section('content')
    <div class="alert alert-info" id="dashboard-info-alert">
        Note: Current statistics coming based on last 7 days.
    </div>

    <div class="row mb-3">
        <div class="col-12">
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

    <div id="messages"></div>
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
        {{-- <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fs-14 font-weight-bold">CPC vs Organic</h6>
                </div>
                <div class="card-body">
                    <div id="cpc-vs-organic-graph-loading" class="d-flex justify-content-center align-items-center"
                        style="height: 280px !important; font-size: 20px !important;">
                        <i class="las la-circle-notch la-spin la-3x text-info"></i>
                    </div>

                    <canvas id="cpc-vs-organic-graph" class="w-100 d-none" height="280"></canvas>
                </div>
            </div>
        </div> --}}
        {{-- <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fs-14 font-weight-bold">CPC vs Organic</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="position-relative flex-grow-1">
                            <!-- Skeleton -->
                            <div id="chartSkeleton" class="chart-skeleton"></div>

                            <!-- Chart -->
                            <canvas id="userActivityChart" height="120"></canvas>
                        </div>

                        <!-- GA Style Legend -->
                        <div class="ga-legend ml-4">
                            <div class="legend-item">
                                <span class="dot blue"></span>
                                <div>
                                    <small>30 DAYS</small>
                                    <h4 id="total30">–</h4>
                                </div>
                            </div>

                            <div class="legend-item">
                                <span class="dot green"></span>
                                <div>
                                    <small>7 DAYS</small>
                                    <h4 id="total7">–</h4>
                                </div>
                            </div>

                            <div class="legend-item">
                                <span class="dot yellow"></span>
                                <div>
                                    <small>1 DAY</small>
                                    <h4 id="total1">–</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fs-14 font-weight-bold">Order & Revenue By Source</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="position-relative flex-grow-1" style="max-width: 80rem">
                            <!-- Skeleton -->
                            <div id="orderAndRevenueChartSkeleton" class="chart-skeleton"></div>

                            <!-- Chart -->
                            <canvas id="orderAndRevenueChart" height="120"></canvas>
                        </div>

                        <!-- GA Style Legend -->
                        <div class="ml-4">
                            <div class="legend-item">
                                <span class="dot blue"></span>
                                <div>
                                    <small>Total Revenue</small>
                                    <h5 id="totalRevenue">–</h5>
                                </div>
                            </div>

                            <div class="legend-item">
                                <span class="dot green"></span>
                                <div>
                                    <small>Total Orders</small>
                                    <h5 id="totalOrders">–</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="source-breakdown">
                        <h6 class="mb-3 font-weight-bold fs-13">Source Breakdown</h6>

                        <div id="sourceBreakdownList">
                            <!-- dynamic rows -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .chart-skeleton {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg,
                    #f0f0f0 25%,
                    #e0e0e0 37%,
                    #f0f0f0 63%);
            background-size: 400% 100%;
            animation: shimmer 1.4s ease infinite;
            z-index: 10;
            border-radius: 6px;
        }

        @keyframes shimmer {
            0% {
                background-position: 100% 0;
            }

            100% {
                background-position: 0 0;
            }
        }

        /* .ga-legend {
            width: 100%;
        } */

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }

        .legend-item small {
            color: #777;
            font-size: 11px;
        }

        .legend-item h5 {
            margin: 0;
            font-weight: 600;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .blue {
            background: #1a73e8;
        }

        .green {
            background: #34a853;
        }

        .yellow {
            background: #fbbc05;
        }
    </style>
    <style>
        .source-breakdown {
            padding-top: 5px;
        }

        .source-row {
            display: flex;
            align-items: center;
            margin-bottom: 14px;
        }

        .source-name {
            width: 110px;
            font-size: 13px;
            color: #333;
        }

        .source-values {
            flex: 1;
        }

        .source-values small {
            font-size: 12px;
            color: #666;
        }

        .source-bar {
            height: 6px;
            background: #eee;
            border-radius: 3px;
            margin-top: 6px;
            overflow: hidden;
        }

        .source-bar-fill {
            height: 100%;
            background: #1a73e8;
            border-radius: 3px;
            transition: width 0.4s ease;
        }
    </style>
@endsection

@section('script')
    <script type="text/javascript">
        let filterDate = '';

        regenerateGraphs();

        function chartLoading() {
            $('#cpc-vs-organic-graph').addClass('d-none');
            $('#cpc-vs-organic-graph-loading').removeClass('d-none').addClass('d-flex');

            $('#orderAndRevenueChartSkeleton').show();
        }

        async function loadOrderAnalyticsData() {
            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: '{{ route('admin.order_analytics_graphs.report') }}',
                    data: {
                        date: filterDate
                    }
                });
                showOrderAndRevenueBySourceChart(response.groupByUtmSource);
                // showCpcVsOrganicChart(response.cpcVsOrganic);
            } catch (error) {
                console.error('Error loading order analytics graph:', error);
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
            loadOrderAnalyticsData();
            loadUtmAnalytics();
        }

        function showCpcVsOrganicChart(data) {
            $('#cpc-vs-organic-graph-loading').addClass('d-none').removeClass('d-flex');
            // Destroy previous chart instance if it exists
            if (window.pieChart1) {
                window.pieChart1.destroy();
            }

            const ctx = document.getElementById('cpc-vs-organic-graph').getContext('2d');
            window.pieChart1 = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [
                        `CPC (Amount: ${(data.cpc.total_amount ?? 0).toFixed(2)})`,
                        `Organic (Amount: ${(data.organic.total_amount ?? 0).toFixed(2)})`
                    ],
                    datasets: [{
                        data: [
                            data.cpc.total_orders ?? 0,
                            data.organic.total_orders ?? 0
                        ],
                        backgroundColor: [
                            "#34bfa3",
                            "#fd3995",
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

            $('#cpc-vs-organic-graph').removeClass('d-none');
        }

        function showOrderAndRevenueBySourceChart(data) {
            orderAndRevenueChart.data.labels = data.labels;
            orderAndRevenueChart.data.datasets[0].data = data.datasets[0].data;
            orderAndRevenueChart.data.datasets[1].data = data.datasets[1].data;
            orderAndRevenueChart.update();

            // Update GA-style legend
            $('#totalRevenue').text(formatCurrency(data.legend.revenue));
            $('#totalOrders').text(data.legend.orders);

            updateSourceBreakdown(data.top_sources);

            $('#orderAndRevenueChartSkeleton').fadeOut(200);
        }

        function updateSourceBreakdown(sources) {

            if (!sources || !sources.length) {
                $('#sourceBreakdownList').html('<small class="text-muted">No data available</small>');
                return;
            }

            let maxRevenue = Math.max(...sources.map(s => s.revenue));
            let html = '';

            sources.forEach(src => {

                let percent = maxRevenue > 0 ?
                    Math.round((src.revenue / maxRevenue) * 100) :
                    0;

                html += `
                    <div class="source-row">
                        <div class="source-name">
                            ${src.utm_source ?? 'Unknown'}
                        </div>

                        <div class="source-values">
                            <small>
                                ${src.order_count} orders · ${formatCurrency(src.revenue)}
                            </small>

                            <div class="source-bar">
                                <div class="source-bar-fill" style="width:${percent}%"></div>
                            </div>
                        </div>
                    </div>
                `;
            });

            $('#sourceBreakdownList').html(html);
        }

        function formatCurrency(val) {
            return '৳' + val.toLocaleString();
        }
    </script>

    <script>
        let nctx = document.getElementById('orderAndRevenueChart').getContext('2d');

        let orderAndRevenueChart = new Chart(nctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                        label: 'Revenue',
                        data: [],
                        borderColor: '#1a73e8',
                        borderWidth: 2,
                        fill: false,
                        lineTension: 0.4,
                        pointRadius: 2,
                        yAxisID: 'y-revenue'
                    },
                    {
                        label: 'Orders',
                        data: [],
                        borderColor: '#34a853',
                        borderWidth: 2,
                        fill: false,
                        lineTension: 0.4,
                        pointRadius: 2,
                        yAxisID: 'y-orders'
                    }
                ]
            },
            options: {
                legend: {
                    display: false
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            let label = data.datasets[tooltipItem.datasetIndex].label;
                            let value = tooltipItem.yLabel;

                            if (label === 'Revenue') {
                                return label + ': ৳' + value.toLocaleString();
                            }
                            return label + ': ' + value;
                        }
                    }
                },
                hover: {
                    mode: 'nearest',
                    intersect: false
                },
                scales: {
                    yAxes: [{
                            id: 'y-revenue',
                            position: 'left',
                            ticks: {
                                callback: function(v) {
                                    return '৳' + (v >= 1000 ? (v / 1000).toFixed(2) + 'K' : v);
                                }
                            }
                        },
                        {
                            id: 'y-orders',
                            position: 'right',
                            gridLines: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                precision: 0
                            }
                        }
                    ],
                    xAxes: [{
                        gridLines: {
                            display: false
                        }
                    }]
                },
                plugins: {
                    datalabels: {
                        display: false
                    }
                }
            }
        });
    </script>


    {{-- <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('#chartSkeleton').fadeOut();
            }, 1000);
        });
        var ctx = document.getElementById('userActivityChart').getContext('2d');

        let labels = [];
        for (let i = 1; i <= 31; i++) {
            labels.push(`Jan ${i}`);
        }
        let data1 = [];
        let data2 = [];
        let data3 = [];
        for (let i = 1; i <= 31; i++) {
            data1.push(Math.floor(Math.random() * 1000) + 3000);
            data2.push(Math.floor(Math.random() * 5000) + 20000);
            data3.push(Math.floor(Math.random() * 20000) + 70000);
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: '30 Days',
                        data: data3,
                        borderColor: '#1a73e8',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        lineTension: 0.4
                    },
                    {
                        label: '7 Days',
                        data: data2,
                        borderColor: '#34a853',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        lineTension: 0.4
                    },
                    {
                        label: '1 Day',
                        data: data1,
                        borderColor: '#fbbc05',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        lineTension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false
                        }
                    }],
                    yAxes: [{
                        position: 'right',
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                if (value >= 1000) {
                                    return value / 1000 + 'K';
                                }
                                return value;
                            }
                        },
                        gridLines: {
                            color: '#eee'
                        }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                }
            }
        });
    </script> --}}
@endsection
