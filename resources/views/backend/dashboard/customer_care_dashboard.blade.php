@extends('backend.layouts.app')
@php
    $permissions = json_decode(Auth::user()->staff?->role?->permissions ?? '[]', true) ?? [];

    $notices = Cache::remember('latest_published_notices', now()->addHours(4), function () {
        return \App\Models\Notice::published()
            ->visibleFor('staffs')
            ->limit(5)
            ->latest()
            ->select(DB::raw('"notice" as type'), 'id', 'title', 'slug', 'created_at')
            ->get();
    });
    $campaigns = Cache::remember('latest_campaigns', now()->addHours(4), function () {
        return \App\Models\Campaign::active()
            ->limit(5)
            ->latest()
            ->select(DB::raw('"campaign" as type'), 'id', 'title', 'slug', 'created_at')
            ->get();
    });

    $mergedData = collect(array_merge($notices->toArray(), $campaigns->toArray()))
        ->sortByDesc('created_at')
        ->values();
    // dd($notices, $campaigns, $mergedData);
@endphp
@section('content')
    <style>
        .limit-text {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .shortcut:hover {
            color: #e7871a;
            transition: color 0.3s ease;
        }
    </style>

    @include('backend.dashboard.partials.attendance')

    @if (@$filter_date == '')
        <div class="alert alert-info">
            Note: Current statistics coming based on last 7 days.
        </div>
    @endif
    @if (count($dashboards ?? []) > 1)
        <div class="row mb-3">
            <div class="col-lg-12">
                <div class="btn-group btn-sm" role="group" aria-label="Basic example">
                    @foreach ($dashboards ?? [] as $key => $dashboard)
                        <a href="{{ route('admin.dashboard', ['view' => $dashboard]) }}"
                            class="btn btn-secondary mr-2">{{ $key }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    <div class="row mb-3">
        <div class="col d-flex flex-row-reverse">
            <form class="form-inline" action="{{ isset($filter_url) ? $filter_url : route('admin.dashboard') }}"
                id="sort_orders" method="GET">
                @if (isset($view) && !is_null($view))
                    <input type="hidden" name="view" value="{{ $view }}">
                @endif
                <div class="form-group mr-2">
                    <input type="text" class="aiz-date-range form-control"
                        value="@if (isset($filter_date)) {{ $filter_date }} @endif" name="filter_date"
                        placeholder="{{ 'Filter by date' }}" data-format="DD-MM-Y" data-separator=" to "
                        data-advanced-range="true" autocomplete="off" style="min-width:205px">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">{{ 'Filter' }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row gutters-5">
        <div class="col-md-9" id="module-shortcuts">
            @include('backend.components.shortcut-modules-preloader')
        </div>
        <div class="col-md-3">
            <div class="row">
                <div class="col-12">
                    <div class="card bg-soft-warning border rounded shadow-md">
                        <div class="card-header">
                            <h5 class="mb-0 h6">
                                <svg class="text-danger" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24">
                                    <path fill="currentColor" fill-rule="evenodd"
                                        d="m16.219 4.838l2.964 2.967c2.012 2.014 3.018 3.021 2.784 4.107c-.235 1.085-1.567 1.585-4.23 2.586l-1.845.693c-.713.268-1.07.402-1.345.64q-.181.158-.322.352c-.212.297-.313.664-.515 1.4c-.46 1.672-.69 2.508-1.239 2.821c-.23.132-.492.2-.758.2c-.63 0-1.243-.614-2.469-1.84l-1.466-1.468l-1.079-1.08L5.285 14.8c-1.218-1.219-1.827-1.828-1.83-2.455a1.53 1.53 0 0 1 .203-.773c.313-.543 1.143-.772 2.803-1.23c.737-.203 1.105-.304 1.402-.517q.199-.144.36-.332c.236-.278.368-.637.63-1.355l.669-1.823c.987-2.693 1.48-4.04 2.568-4.28s2.102.774 4.129 2.803"
                                        clip-rule="evenodd" opacity=".5" />
                                    <path fill="currentColor"
                                        d="m3.302 21.776l4.476-4.48l-1.079-1.08l-4.476 4.48a.764.764 0 0 0 1.08 1.08" />
                                </svg>
                                Notice & Campaigns
                            </h5>
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                @foreach ($mergedData as $data)
                                    @php
                                        $createdAt = \Carbon\Carbon::parse($data['created_at']);
                                        $isNew = $loop->first || $createdAt->diffInDays(now()) <= 7;
                                    @endphp
                                    <div class="col-12 mb-1 border rounded notices py-2">
                                        <a href="{{ $data['type'] === 'notice' ? route('notices.show', $data['slug']) : route('campaigns.show', $data['slug']) }}"
                                            class="text-decoration-none text-dark" target="_blank">
                                            <span class="d-block font-weight-bold">
                                                {{ Str::limit($data['title'], 50) }}
                                                @if ($isNew)
                                                    <span class="text-danger">(New)</span>
                                                @endif
                                            </span>
                                            <span class="text-muted fs-12">
                                                {{ $createdAt->format('d M Y') }}
                                            </span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gutters-10">
        @if (Auth::user()->user_type == 'admin' || in_array('pending_orders', $permissions))
            <div class="col">
                <div class="bg-grad-1 text-white rounded-lg mb-4 overflow-hidden">
                    <div class="px-3 pt-3">
                        <div class="opacity-50">
                            <span class="fs-12 d-block">{{ 'Pending' }}</span>
                            {{ 'Orders' }}
                        </div>
                        <div class="h3 fw-700 mb-3">
                            {{ data_get($report, 'pending_orders_count', 0) }}
                            <a href="{{ route('all_orders.status', 'pending') }}" class="text-white">
                                <i class="las la-eye fs-16"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (Auth::user()->user_type == 'admin' || in_array('processing_orders', $permissions))
            <div class="col">
                <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
                    <div class="px-3 pt-3">
                        <div class="opacity-50">
                            <span class="fs-12 d-block">{{ 'Processing' }}</span>
                            {{ 'Orders' }}
                        </div>
                        <div class="h3 fw-700 mb-3">
                            {{ data_get($report, 'processing_orders_count', 0) }}
                            <a href="{{ route('all_orders.status', 'processing') }}" class="text-white">
                                <i class="las la-eye fs-16"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (Auth::user()->user_type == 'admin' ||
                any_in_array(['manage_crm', 'crm_customers', 'support_tickets'], $permissions))
            @if (get_setting('enable_crm_module') == 1)
                <div class="col">
                    <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
                        <div class="px-3 pt-3">
                            <div class="opacity-50">
                                <span class="fs-12 d-block">{{ 'Feedback' }}</span>
                                {{ 'Count' }}
                            </div>
                            <div class="h3 fw-700 mb-3">
                                {{ data_get($report, 'feedback_count', 0) }}
                                <a href="#" class="text-white">
                                    <i class="las la-eye fs-16"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col">
                    <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
                        <div class="px-3 pt-3">
                            <div class="opacity-50">
                                <span class="fs-12 d-block">{{ 'Authenticity' }}</span>
                                {{ 'Issue' }}
                            </div>
                            <div class="h3 fw-700 mb-3">
                                {{ data_get($report, 'authenticity_issue_count', 0) }}
                                <a href="{{ route('tickets.admin_index', 'authenticity-issue') }}" class="text-white">
                                    <i class="las la-eye fs-16"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col">
                <div class="bg-grad-4 text-white rounded-lg mb-4 overflow-hidden">
                    <div class="px-3 pt-3">
                        <div class="opacity-50">
                            <span class="fs-12 d-block">{{ 'Skincare' }}</span>
                            {{ 'Suggestion' }}
                        </div>
                        <div class="h3 fw-700 mb-3">
                            {{ data_get($report, 'skincare_suggestion_count', 0) }}
                            <a href="{{ route('tickets.admin_index', 'skincare-suggestion') }}" class="text-white">
                                <i class="las la-eye fs-16"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="row gutters-10">
        <div class="col-lg-4">
            <div class="row gutters-10">
                <div class="col-12">
                    <div class="card shadow-md">
                        <div class="card-header">
                            <h6 class="mb-0 fs-14">{{ 'Employee Details' }}</h6>
                        </div>
                        <div class="card-body">
                            <p>
                                <strong>{{ 'Name' }}:</strong> {{ $report['name'] }}<br>
                                <strong>{{ 'Email' }}:</strong> {{ $report['email'] }}<br>
                                <strong>{{ 'Phone' }}:</strong> {{ $report['phone'] }}<br>
                                <strong>{{ 'Role' }}:</strong> {{ $report['role'] }}<br>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row gutters-10">
                        <div class="col-12">
                            <div class="card shadow-md">
                                <div class="card-header">
                                    <h6 class="mb-0 fs-14">{{ 'Order Logs' }}</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="order-logs-pie" class="w-100" height="305"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-md">
                <div class="card-body">
                    <canvas id="call-logs-graph" class="w-100" height="500"></canvas>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="row gutters-10">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <canvas id="call-logs-graph" class="w-100" height="500"></canvas>
                </div>
            </div>
        </div>
    </div> --}}
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
@php
    $start_date = now()->startOfDay();
    $end_date = now()->endOfDay();

    if (filled($filter_date)) {
        $dates = explode(' to ', $filter_date);
        if (count($dates) === 2) {
            $start_date = Carbon::parse($dates[0])->startOfDay();
            $end_date = Carbon::parse($dates[1])->endOfDay();
        }
    }
    $daysDifference = $start_date->diffInDays($end_date);
    // dd($report)
@endphp
@section('script')
    {{-- CheckIn CheckOut Script --}}
    @include('backend.dashboard.partials.attendance_script')

    <script>
        $('.notices').hover(
            function() { // mouseenter
                $(this).addClass('shadow-sm').css({
                    'cursor': 'pointer',
                    'opacity': '0.7'
                });
            },
            function() { // mouseleave
                $(this).removeClass('shadow-sm').css({
                    'cursor': 'default',
                    'opacity': '1'
                });
            }
        );
        AIZ.plugins.chart('#order-logs-pie', {
            type: 'doughnut',
            data: {
                labels: [
                    '{{ 'Called' }}',
                    '{{ 'Created' }}',
                    '{{ 'Updated' }}'
                ],
                datasets: [{
                    data: [
                        {{ data_get($report, 'call_count', 0) }},
                        {{ data_get($report, 'create_count', 0) }},
                        {{ data_get($report, 'update_count', 0) }}
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

        AIZ.plugins.chart('#call-logs-graph', {
            type: 'bar',
            data: {
                labels: [
                    @foreach ($report['callLogs'] as $callLog)
                        '{{ \Carbon\Carbon::parse($callLog->period)->format($daysDifference > 30 ? 'M Y' : 'd M Y') }}',
                    @endforeach
                ],
                datasets: [{
                    label: '{{ 'Call Logs' }}',
                    data: [
                        @foreach ($report['callLogs'] as $callLog)
                            {{ $callLog->total }},
                        @endforeach
                    ],
                    backgroundColor: [
                        @foreach ($report['callLogs'] as $callLog)
                            'rgba(55, 125, 255, 0.4)',
                        @endforeach
                    ],
                    borderColor: [
                        @foreach ($report['callLogs'] as $callLog)
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
                            return '{{ 'Total Calls' }}: ' + tooltipItem.yLabel;
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


        $(document).ready(function() {
            getShortcutModules();
        });

        function getShortcutModules() {
            $.ajax({
                url: '{{ route('admin.get_module_shortcuts') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    dashboards: @json($dashboards ?? [])
                },
                success: function(data) {
                    if (data.status) {
                        $('#module-shortcuts').html(data.view);
                    }
                },
                error: function() {
                    console.error('Failed to load shortcut modules.');
                }
            });
        }
    </script>
@endsection
