@extends('backend.layouts.app')
@php
    $smsTypes = Cache::remember('sms_types', now()->addHours(3), function () {
        return App\Models\SmsLog::distinct()->pluck('type')->filter()->values()->all();
    });
@endphp
@section('content')
    <div class="card">
        <form class="" action="" id="sort_orders" method="GET">
            <div class="card-header">
                <div class="col px-0">
                    <h5 class="mb-md-0 h6">SMS Log Report</h5>
                </div>
            </div>
            <div class="card-header row gutters-5 justify-content-start">
                <div class="col-md-2 mb-2">
                    <div class="form-group mb-0">
                        <input type="text" class="aiz-date-range form-control-sm form-control"
                            value="{{ request()->date }}" name="date" placeholder="Filter By Date"
                            data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="type" data-live-search="true">
                        <option value="">Filter By SMS Type</option>
                        @foreach ($smsTypes as $type)
                            <option value="{{ $type }}" @if (request()->type === $type) selected @endif>
                                {{ ucwords(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <input type="text" placeholder="Search by phone number..." id="phone" name="phone" value="{{ request('phone') }}" class="form-control form-control-sm">
                </div>

                <div class="col-auto mb-2">
                    <div class="form-group mb-0 mt-0">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        <button type="button" class="btn btn-sm btn-secondary"
                            onclick="window.location.href='{{ route('admin.smsLogReport.index') }}'">Clear</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="text-muted font-weight-bold mb-2">
                    Showing {{ $smsLogs->count() }} of {{ $smsLogs->total() }} entries
                </div>
                <table class="table aiz-table mb-0" id="theTable">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="20%">Sms Type</th>
                            <th width="20%">Sent To</th>
                            <th>Content</th>
                            <th width="15%">Sent At</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($smsLogs as $log)
                            <tr>
                                <td class="text-center">{{ $loop->iteration + (($smsLogs->currentPage() - 1) * $smsLogs->perPage()) }}</td>
                                <td>{{ $log->sms_type }}</td>
                                <td>
                                    <span class="text-muted d-block">Name: {{ $log->user->name }}</span>
                                    <span class="text-muted d-block">Phone: {{ $log->phone ?: $log->user->phone }}</span>
                                </td>
                                <td>
                                    {{ Str::limit(preg_replace('/\d/', '*', $log->body), 130) }}
                                </td>
                                <td>{{ $log->created_at->format('d-m-Y h:i a') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $smsLogs->appends(request()->input())->links() }}
                </div>
            </div>
        </form>
    </div>
@endsection
