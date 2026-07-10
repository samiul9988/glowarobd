@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{ ('All Logs')}}</h1>
		</div>
	</div>
</div>
@if (is_null(@$search) && is_null(@$event) && is_null(@$staff))
    <div class="alert alert-info">
        <strong>Note:</strong> Select an event or a staff or type order number to filter logs.
    </div>
@endif
@if (is_null(@$filter_date))
    <div class="alert alert-info">
        <strong>Note:</strong> This statistics coming based on today.
    </div>
@endif
<div class="card">
    <form action="{{ route('log-report.index') }}" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('Order Logs') }}</h5>
            </div>

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ @$filter_date }}" name="filter_date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    @php
                        $events = ['called', 'viewed', 'created', 'updated', 'deleted', 'packaged', 'ticket'];
                        if(get_setting('enable_crm_module') == 1){
                            $events[] = 'feedback';
                        }
                    @endphp
                    <select class="form-control" name="event" id="event">
                        <option value="">{{ ('Filter by event') }}</option>
                        @foreach ($events as $ev)
                            <option value="{{ $ev }}" @if (@$event == $ev) selected @endif>{{ ucfirst($ev) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <select class="form-control aiz-selectpicker" name="staff" id="staff" data-live-search="true">
                        <option value="">{{ ('Filter by Staff') }}</option>
                        <option value="0" @if (@$staff === "0") selected @endif>Pathao</option>
                        @foreach ($staffs as $employee)
                            <option value="{{ $employee['id'] }}" @if (@$staff == $employee['id']) selected @endif>{{ ucfirst($employee['name']) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" @isset($search) value="{{ $search }}" @endisset placeholder="{{ ('Type order number') }}">
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
                    <th>Order Code</th>
                    <th>Customer Info</th>
                    {{-- <th>Created At</th> --}}
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $key => $log)
                    <tr>
                        <td width="5%">{{ ($key+1) + ($logs->currentPage() - 1)*$logs->perPage() }}</td>
                        <td width="15%">
                            @if(data_get($log, 'order_code'))
                                <a class="fw-600" target="_blank" href="{{ route('all_orders.show', encrypt($log['order_id'])) }}" data-order="{{ $log['order_id'] }}">#{{ $log['order_code'] }}</a>
                            @else
                                <span class="text-danger"><strong>Order Deleted</strong></span>
                            @endif
                        </td>
                        <td>
                            @if(data_get($log, 'order_code'))
                                <strong>{{ data_get($log, 'shipping_address.name') }}</strong>
                                <br>
                                @if(data_get($log, 'shipping_address.email'))
                                    {{ data_get($log, 'shipping_address.email') }}
                                    <br>
                                @endif
                                {{ data_get($log, 'shipping_address.phone') }}
                            @else
                                <span class="text-danger"><strong>Order Deleted</strong></span>
                            @endif
                        </td>
                        {{-- <td width="20%">{{ $log['created_at'] }}</td> --}}
                        <td width="40%">
                            @if(!is_null($log['status']))
                                <strong>{{ ucwords(str_replace('_',' ',$log['status'])) }}</strong>
                                <br>
                            @endif
                            <strong>Note: </strong>{{ ucfirst($log['message'] ?: 'N/A') }}
                            @if(!is_null($log['duration']))
                                <br>
                                <strong>Duration: </strong>{{ $log['duration'] }}
                            @endif
                            {{-- @if(!is_null($log['status'])) --}}
                                <br>
                                by {{ $log['staff_name'] }} at {{ $log['created_at'] }}
                            {{-- @endif --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $logs->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection
