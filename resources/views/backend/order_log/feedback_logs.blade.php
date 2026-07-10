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
                <h5 class="mb-md-0 h6">{{ ('Feedback Logs') }}</h5>
            </div>

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ @$filter_date }}" name="filter_date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    @php
                        $events = ['called', 'viewed', 'created', 'updated', 'deleted', 'packaged', 'ticket', 'feedback'];
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
                        @foreach ($staffs as $employee)
                            <option value="{{ $employee['id'] }}" @if (@$staff == $employee['id']) selected @endif>{{ ucfirst($employee['name']) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" @isset($search) value="{{ $search }}" @endisset placeholder="{{ ('Search...') }}">
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
                    <th>Customer Info</th>
                    <th width="20%">Details</th>
                    <th>Order Info</th>
                    <th class="text-center">Rating & Satisfaction</th>
                    <th>Call Info</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>
                            @if($log->callLog?->reference)
                                @if($log->callLog?->reference?->banned == 1)
                                    <i class="fa fa-ban text-danger" aria-hidden="true"></i>
                                @endif
                                    <span class="d-block font-weight-bold" title="{{ ($log->callLog?->reference?->email_verified_at ?? null) ? 'Verified' : 'Not Verified' }}">
                                        {{ ucwords($log->callLog?->reference?->name) }}
                                        @if ($log->callLog?->reference?->email_verified_at)
                                            <i class="las la-check-circle text-success font-weight-bold"></i>
                                        @else
                                            <i class="las la-times-circle text-danger font-weight-bold"></i>
                                        @endif
                                        @if($log->callLog?->reference?->customeringroup?->group)
                                            @php
                                                $class = match($log->callLog?->reference?->customeringroup?->group?->group_name) {
                                                    'New User' => 'text-secondary',
                                                    'Regular User' => 'text-info',
                                                    'Premium User' => 'text-success',
                                                    'Platinam User' => 'text-primary',
                                                    default => 'text-muted',
                                                };
                                            @endphp
                                            <span class="{{ $class }} fs-10"> ({{ $log->callLog?->reference?->customeringroup?->group?->group_name }})</span>
                                        @endif
                                    </span>
                                @if(filled($log->callLog?->reference?->email))
                                    <span class="d-block">
                                        {{ $log->callLog?->reference?->email }}
                                    </span>
                                @endif
                                @if(filled($log->callLog?->reference?->phone))
                                    <span class="d-block">{{ $log->callLog?->reference?->phone }}</span>
                                @endif
                                @if(filled($log->callLog?->reference?->meta('customer_label')) && is_array($log->callLog?->reference?->meta('customer_label')))
                                    <span class="d-block mt-1">
                                        @foreach ($log->callLog?->reference?->meta('customer_label') as $label)
                                            <span class="badge badge-{{ \App\Enums\CustomerLabels::getLabelGroup($label) }} badge-inline">{{ \App\Enums\CustomerLabels::getLabel($label) }}</span>
                                        @endforeach
                                    </span>
                                @endif
                            @else
                                @php
                                    $shipping_info = json_decode($log->order?->shipping_address, true) ?? [];
                                @endphp
                                <span class="d-block font-weight-bold">
                                    {{ ucwords($shipping_info['name'] ?? 'N/A') }}
                                </span>
                                @if(filled($shipping_info['email'] ?? null))
                                    <span class="d-block">{{ $shipping_info['email'] }}</span>
                                @endif
                                @if(filled($shipping_info['phone'] ?? null))
                                    <span class="d-block">{{ $shipping_info['phone'] }}</span>
                                @endif
                                <span class="text-danger fs-10"> <i class="las la-exclamation-circle text-danger"></i> Unregistered/Deleted User</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted font-weight-bold d-block fs-12">
                                <div class="d-flex align-items-center justify-content-start">
                                    {{ ('Rider Behavior') }}: {{ intval(data_get($log->feedback, 'rider_behavior', 0)) }}
                                    @if(data_get($log->feedback, 'rider_behavior_note'))
                                        @include('components.tooltip', [
                                            'title' => data_get($log->feedback, 'rider_behavior_note'),
                                            'class' => 'text-dark ml-2 font-weight-bold fs-18',
                                            'position' => 'top',
                                        ])
                                    @endif
                                </div>
                            </span>
                            <span class="text-muted font-weight-bold d-block fs-12">
                                <div class="d-flex align-items-center justify-content-start">
                                    Packaging: {{ intval(data_get($log->feedback, 'packaging', 0)) }}
                                    @if(data_get($log->feedback, 'packaging_note'))
                                        @include('components.tooltip', [
                                            'title' => data_get($log->feedback, 'packaging_note'),
                                            'class' => 'text-dark ml-2 font-weight-bold fs-18',
                                            'position' => 'top',
                                        ])
                                    @endif
                                </div>
                            </span>
                            <span class="text-muted font-weight-bold d-block fs-12">
                                <div class="d-flex align-items-center justify-content-start">
                                    {{ ('CS Behavior') }}: {{ intval(data_get($log->feedback, 'cs_behavior', 0)) }}
                                    @if(data_get($log->feedback, 'cs_behavior_note'))
                                        @include('components.tooltip', [
                                            'title' => data_get($log->feedback, 'cs_behavior_note'),
                                            'class' => 'text-dark ml-2 font-weight-bold fs-18',
                                            'position' => 'top',
                                        ])
                                    @endif
                                </div>
                            </span>
                            <span class="text-muted font-weight-bold d-block fs-12">
                                <div class="d-flex align-items-center justify-content-start">
                                    {{ ('Delivery Time') }}: {{ intval(data_get($log->feedback, 'delivery_time', 0)) }}
                                    @if(data_get($log->feedback, 'delivery_time_note'))
                                        @include('components.tooltip', [
                                            'title' => data_get($log->feedback, 'delivery_time_note'),
                                            'class' => 'text-dark ml-2 font-weight-bold fs-18',
                                            'position' => 'top',
                                        ])
                                    @endif
                                </div>
                            </span>
                            <span class="text-muted font-weight-bold d-block fs-12">
                                <div class="d-flex align-items-center justify-content-start">
                                    {{ ('Product Quality') }}: {{ intval(data_get($log->feedback, 'products_rating', 0)) }}
                                    @if(data_get($log->feedback, 'product_quality_note'))
                                        @include('components.tooltip', [
                                            'title' => data_get($log->feedback, 'product_quality_note'),
                                            'class' => 'text-dark ml-2 font-weight-bold fs-18',
                                            'position' => 'top',
                                        ])
                                    @endif
                                </div>
                            </span>

                            @if($log->note)
                                <span class="text-info font-weight-bold mt-2">
                                    Note: {{ $log->note }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($log->order)
                                <a class="font-weight-bold" target="_blank" href="{{ route('all_orders.show', encrypt($log->order_id)) }}">
                                    <span class="d-block">#{{ $log->order->code }}</span>
                                    <span class="d-block text-muted">Order Date - {{ $log->order->created_at->format('d F Y') }}</span>
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($log->callLog?->reference?->meta('satisfaction') != null)
                                <span class="badge-inline badge badge-primary p-2 w-auto font-weight-bold">
                                    Rating: {{ $log->rating }}
                                </span>
                                <span class="badge-inline badge badge-success p-2 w-auto font-weight-bold">
                                    Satisfaction: {{ $log->callLog?->reference?->meta('satisfaction') }}%
                                </span>
                            @else
                                <span class="badge badge-secondary p-2 w-auto font-weight-bold">
                                    {{ ('Not Rated') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="d-block">
                                <strong>Status:</strong> {{ $log->callLog?->status ? \App\Enums\CallStatus::getStatusName($log->callLog?->status) : 'Unknown' }}
                                @if ($log->callLog?->rescheduled_at)
                                    <span class="d-block">At <strong class="text-danger">{{ $log->callLog?->rescheduled_at->format('d M h:i A') }}</strong></span>
                                @endif
                            </span>
                            <span class="d-block"><strong>Note:</strong> {{ ucfirst($log->callLog?->note) }}</span>
                            <span class="d-block"><strong>Call Duration:</strong> {{ $log->callLog?->duration ?? 0 }} min</span>
                            <span class="d-block"><strong>Called By:</strong> <strong class="text-primary">{{ ucfirst($log->callLog?->caller?->name ?? 'Unknown') }}</strong> at <strong class="text-danger">{{ $log->callLog?->created_at?->format('d-m-Y h:i A') }}</strong></span>
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
