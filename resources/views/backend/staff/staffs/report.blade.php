@extends('backend.layouts.app')

@section('content')

@if (@$filter_date == '')
    <div class="alert alert-info">
        <strong>Note:</strong> Current report coming based on today.
    </div>
@endif
<div class="row gutters-10">
    <div class="col">
        <div class="bg-grad-1 text-white rounded-lg mb-4 overflow-hidden">
            <div class="px-3 pt-3">
                <div class="opacity-50">
                    <span class="fs-12 d-block">{{ ('Calls') }}</span>
                    {{ ('Count') }}
                </div>
                <div class="h3 fw-700 mb-3">
                    {{ data_get($counts, 'call_count', 0) }}
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
            <div class="px-3 pt-3">
                <div class="opacity-50">
                    <span class="fs-12 d-block">{{ ('Tickets') }}</span>
                    {{ ('Count') }}
                </div>
                <div class="h3 fw-700 mb-3">
                    {{ data_get($counts, 'ticket_count', 0) }}
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
            <div class="px-3 pt-3">
                <div class="opacity-50">
                    <span class="fs-12 d-block">{{ ('Create') }}</span>
                    {{ ('Count') }}
                </div>
                <div class="h3 fw-700 mb-3">
                    {{ data_get($counts, 'create_count', 0) }}
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="bg-grad-4 text-white rounded-lg mb-4 overflow-hidden">
            <div class="px-3 pt-3">
                <div class="opacity-50">
                    @if(get_setting('enable_crm_module') == 1)
                        <span class="fs-12 d-block">{{ ('Feedback') }}</span>
                    @else
                        <span class="fs-12 d-block">{{ ('Update') }}</span>
                    @endif
                    {{ ('Count') }}
                </div>
                <div class="h3 fw-700 mb-3">
                    @if(get_setting('enable_crm_module') == 1)
                        {{ data_get($counts, 'feedback_count', 0) }}
                    @else
                        {{ data_get($counts, 'update_count', 0) }}
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="bg-grad-1 text-white rounded-lg mb-4 overflow-hidden">
            <div class="px-3 pt-3">
                <div class="opacity-50">
                    <span class="fs-12 d-block">{{ ('Package') }}</span>
                    {{ ('Count') }}
                </div>
                <div class="h3 fw-700 mb-3">
                    {{ data_get($counts, 'package_count', 0) }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <form action="{{ route('staffs.report') }}" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('Staffs Report') }}</h5>
            </div>

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ @$filter_date }}" name="filter_date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <select class="form-control" name="role" id="role">
                        <option value="">{{ ('Filter by role') }}</option>
                        @foreach ($roles as $id => $name)
                            <option value="{{ $id }}" @if ($id == $role) selected @endif>{{ (ucwords($name)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"@isset($search) value="{{ $search }}" @endisset placeholder="{{ ('Type staff name') }}">
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
                    <th data-breakpoints="lg" width="10%">#</th>
                    <th>Staff Name</th>
                    <th>Role</th>
                    <th>Calls</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Packaged</th>
                    <th>Tickets</th>
                    @if(get_setting('enable_crm_module') == 1)
                        <th>Feedback</th>
                    @endif
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $query = '?filter_date=' . $filter_date;
                    $route = route('log-report.index');
                @endphp
                @foreach($paginatedReports as $report)
                    <tr>
                        <td>{{ $loop->iteration + ($paginatedReports->currentPage() - 1) * $paginatedReports->perPage() }}</td>
                        <td>{{ $report['name'] ?? 'N/A' }}</td>
                        <td>{{ $report['role'] }}</td>
                        <td data-toggle="tooltip" data-title="{{ $report['call_count'] > 0 ? 'Calls ' . $report['call_count'] . ' Customers' : 'No Calls' }}">
                            @if ($report['call_count'] > 0)
                                <a target="_blank" href="{{ $route.$query.'&staff='.$report['id'].'&event=called' }}">
                                    {{ $report['call_count'] }}
                                </a>
                            @else
                                {{ $report['call_count'] }}
                            @endif
                        </td>
                        <td data-toggle="tooltip" data-title="{{ $report['create_count'] > 0 ? 'Created ' . $report['create_count'] . ' Orders' : 'No Orders Created' }}">
                            @if ($report['create_count'] > 0)
                                <a target="_blank" href="{{ $route.$query.'&staff='.$report['id'].'&event=created' }}">
                                    {{ $report['create_count'] }}
                                </a>
                            @else
                                {{ $report['create_count'] }}
                            @endif
                        </td>
                        <td data-toggle="tooltip" data-title="{{ $report['update_count'] > 0 ? 'Updated ' . $report['update_count'] . ' Orders' : 'No Orders Updated' }}">
                            @if ($report['update_count'] > 0)
                                <a target="_blank" href="{{ $route.$query.'&staff='.$report['id'].'&event=updated' }}">
                                    {{ $report['update_count'] }}
                                </a>
                            @else
                                {{ $report['update_count'] }}
                            @endif
                        </td>
                        <td data-toggle="tooltip" data-title="{{ $report['package_count'] > 0 ? 'Packaged ' . $report['package_count'] . ' Orders' : 'No Orders Packaged' }}">
                            @if ($report['package_count'] > 0)
                                <a target="_blank" href="{{ $route.$query.'&staff='.$report['id'].'&event=packaged' }}">
                                    {{ $report['package_count'] }}
                                </a>
                            @else
                                {{ $report['package_count'] }}
                            @endif
                        </td>
                        <td data-toggle="tooltip" data-title="{{ $report['ticket_count'] > 0 ? 'Managed ' . $report['ticket_count'] . ' Tickets' : 'No Tickets Managed' }}">
                            @if ($report['ticket_count'] > 0)
                                <a target="_blank" href="{{ $route.$query.'&staff='.$report['id'].'&event=ticket' }}">
                                    {{ $report['ticket_count'] }}
                                </a>
                            @else
                                {{ $report['ticket_count'] }}
                            @endif
                        </td>
                        @if(get_setting('enable_crm_module') == 1)
                            <td data-toggle="tooltip" data-title="{{ $report['feedback_count'] > 0 ? 'Feedback ' . $report['feedback_count'] . ' Customers' : 'No Feedback Received' }}">
                                @if ($report['feedback_count'] > 0)
                                    <a target="_blank" href="{{ $route.$query.'&staff='.$report['id'].'&event=feedback' }}">
                                        {{ $report['feedback_count'] }}
                                    </a>
                                @else
                                    {{ $report['feedback_count'] }}
                                @endif
                            </td>
                        @endif
                        <td>
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('staffs.report.show', ['id'=>$report['id'], 'filter_date'=>$filter_date]) }}" title="{{ ('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $paginatedReports->appends(request()->except('page'))->links() }}
        </div>
    </div>
</div>

@endsection
