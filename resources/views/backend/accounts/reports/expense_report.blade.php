@extends('backend.layouts.app')

@section('content')
    @if (blank(request()->date))
        <div class="alert alert-info">
            Note: This report is generated for last 7 days by default.
        </div>
    @endif

    <div class="row gutters-5 mb-2">
        <div class="col-md-12">
            <div class="card h-100">

                <!-- REMOVE mx-auto -->
                <div class="w-100">

                    <div class="card-header justify-content-center">
                        <h6 class="mb-0 fs-14 text-center">
                            Expense Ratio By Heads
                        </h6>
                    </div>

                    <div class="card-body">

                        <div id="expense-ratio-by-heads-loading" class="d-flex justify-content-center align-items-center"
                            style="height: 400px;">
                            <i class="las la-circle-notch la-spin la-3x text-info"></i>
                        </div>

                        <!-- Wrap canvas -->
                        <div style="position: relative; height: 400px; width: 100%;">
                            <canvas id="expense-ratio-by-heads" class="d-none"></canvas>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <form class="" action="" id="sort_orders" method="GET">
            <div class="card-header">
                <div class="col px-0">
                    <h5 class="mb-md-0 h6">Expense Report</h5>
                </div>
            </div>
            <div class="card-header row gutters-5 justify-content-start">
                <div class="col-md-2 mb-2">
                    <div class="form-group mb-0">
                        <input type="text" class="aiz-date-range form-control-sm form-control"
                            value="{{ request()->date }}" name="date" placeholder="{{ 'Filter by date' }}"
                            data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <div class="form-group mb-0">
                        <select name="head" id="head" class="form-control form-control-sm aiz-selectpicker"
                            data-live-search="true" data-placeholder="Select Head">
                            <option value="">Select Head</option>
                            @foreach ($heads as $head)
                                <option value="{{ $head->head }}" @if (request()->head == $head->head) selected @endif>
                                    {{ $head->head }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-auto mb-2">
                    <div class="form-group mb-0 mt-0">
                        <button type="submit" class="btn btn-sm btn-primary">{{ 'Filter' }}</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table aiz-table mb-0" id="theTable">
                    <thead>
                        <tr>
                            <td colspan="2" class="text-left font-weight-bold">
                                Total Expenses: <span
                                    class="text-danger">{{ single_price($expenses->sum('debit') ?? 0) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th class="text-center">Voucher Number</th>
                            <th class="text-center">Heads</th>
                            <th class="text-center">Debit</th>
                            <th class="text-center">Debitted By</th>
                            <th class="text-left">Note</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $totalDebit = 0;
                        @endphp
                        @foreach ($expenses as $key => $expense)
                            <tr>
                                <td>{{ 1 + $key }}</td>
                                <td>{{ date('d-m-Y', strtotime($expense->date)) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('accounts.vouchers.show', $expense->vno) }}" target="_blank">
                                        #{{ $expense->vno }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <span class="text-success font-weight-bold d-block text-capitalize">
                                        Parent Head: {{ $expense->particular?->parent_head ?? 'N/A' }}
                                    </span>
                                    <span class="text-primary font-weight-bold d-block text-capitalize">
                                        Sub Head: {{ $expense->particular?->sub_head ?? 'N/A' }}
                                    </span>
                                    <span class="text-info font-weight-bold d-block text-capitalize">
                                        Head: {{ $expense->particular?->head ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">{{ single_price($expense->debit) }}</td>
                                <td class="text-center">
                                    <span class="font-weight-bold">
                                        {{ $expense->user?->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-left">
                                    {{ Str::limit($expense->note ?: 'N/A', 50) }}
                                    @if ($expense->note && strlen($expense->note) > 50)
                                        @include('components.tooltip', [
                                            'title' => $expense->note
                                        ])
                                    @endif
                                </td>
                            </tr>
                            @php
                                $totalDebit += $expense->debit;
                            @endphp
                        @endforeach

                        <tr>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center font-weight-bold">{{ single_price($totalDebit) }}</td>
                            <td class="text-center"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            function fetchExpenseReportChartData() {
                const dateRange = $('input[name=date]').val();
                $('#expense-ratio-by-heads-loading').removeClass('d-none');
                $('#expense-ratio-by-heads').addClass('d-none');

                $.ajax({
                    url: "{{ route('accounts.reports.expense_report_chart') }}",
                    type: 'GET',
                    data: {
                        date: dateRange
                    },
                    success: function(response) {
                        if (response.success) {
                            renderGraph('test', response.groups);
                        } else {
                            AIZ.plugins.notify('error', 'Something Went Wrong!');
                            console.error('Failed to fetch expense report chart data.');
                        }
                    },
                    error: function(xhr, status, error) {
                        AIZ.plugins.notify('error', 'Server Error!');
                        console.error('An error occurred while fetching the data.');
                    }
                });
            }

            function renderGraph(type, data) {
                let chartContainerId = 'expense-ratio-by-heads';
                const sortedData = [...data].sort(
                    (a, b) => parseFloat(b.percentage) - parseFloat(a.percentage)
                );

                const sortedLabels = sortedData.map(item => item.head);
                const sortedValues = sortedData.map(item => parseFloat(item.percentage));
                const sortedAmounts = sortedData.map(item => item.total_debit);

                const backgroundColors = sortedLabels.map((_, i) => {
                    const hue = (i * 25) % 360;
                    return `hsl(${hue}, 70%, 55%)`;
                });

                if (window[`${chartContainerId}Chart`]) {
                    window[`${chartContainerId}Chart`].destroy();
                }

                const ctx = document.getElementById(chartContainerId).getContext('2d');
                window[`${chartContainerId}Chart`] = new Chart(ctx, {
                    type: 'horizontalBar',
                    data: {
                        labels: sortedLabels,
                        datasets: [{
                            label: 'Percentage of Expenses',
                            data: sortedValues,
                            backgroundColor: backgroundColors,
                            borderWidth: 0,
                            barThickness: 35,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: false
                        },
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, chartData) {
                                    const index = tooltipItem.index;
                                    return [
                                        'Percentage: ' + sortedValues[index].toFixed(2) + '%',
                                        'Amount: ' + sortedAmounts[index]
                                    ];
                                }
                            }
                        },
                        scales: {
                            xAxes: [{
                                ticks: {
                                    min: 0,
                                    max: 100,
                                    stepSize: 10,
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Percentage of Expenses (%)',
                                    fontStyle: 'bold'
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    autoSkip: false,
                                    fontSize: 11
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Expense Categories',
                                    fontStyle: 'bold'
                                }
                            }]
                        }
                    }
                });

                $('#expense-ratio-by-heads-loading')
                    .addClass('d-none')
                    .removeClass('d-flex');
                $('#expense-ratio-by-heads').removeClass('d-none');
            }

            fetchExpenseReportChartData();
        });
    </script>
@endsection
