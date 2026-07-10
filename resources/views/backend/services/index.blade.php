@extends('backend.layouts.app')
@php
    $issues = ['General Query', 'Refund Issue', 'Authenticity Issue', 'Skincare Suggestion', 'Exchange Product', 'Product Query', 'Restock Reminder', 'Others'];
    $currentStatus = 'opened';
@endphp
@section('content')
<div class="row gutters-10">
    <div class="col">
        <a href="{{ url()->current() }}?priority=low">
            <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
                <div class="px-3 pt-3">
                    <div class="opacity-50">
                        <span class="fs-12 d-block">{{ ('Low') }}</span>
                    </div>
                    <div class="h3 fw-700 mb-3">
                        {{ data_get($priorityWiseCount, 'low', 0) }}
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="{{ url()->current() }}?priority=medium">
            <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
                <div class="px-3 pt-3">
                    <div class="opacity-50">
                        <span class="fs-12 d-block">{{ ('Medium') }}</span>
                    </div>
                    <div class="h3 fw-700 mb-3">
                        {{ data_get($priorityWiseCount, 'medium', 0) }}
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="{{ url()->current() }}?priority=high">
            <div class="bg-grad-4 text-white rounded-lg mb-4 overflow-hidden">
                <div class="px-3 pt-3">
                    <div class="opacity-50">
                        <span class="fs-12 d-block">{{ ('High') }}</span>
                    </div>
                    <div class="h3 fw-700 mb-3">
                        {{ data_get($priorityWiseCount, 'high', 0) }}
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="{{ url()->current() }}?priority=critical">
            <div class="bg-grad-1 text-white rounded-lg mb-4 overflow-hidden">
                <div class="px-3 pt-3">
                    <div class="opacity-50">
                        <span class="fs-12 d-block">{{ ('Critical') }}</span>
                    </div>
                    <div class="h3 fw-700 mb-3">
                        {{ data_get($priorityWiseCount, 'critical', 0) }}
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
<div class="card">
    <form class="" action="{{ route('services.index', $issue) }}" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('All Orders') }}</h5>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{ ('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="javascript:;" onclick="bulk_delete_modal()"> {{ ('Delete selection')}}</a>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#exampleModal">
                        <i class="las la-sync-alt"></i>
                        {{ ('Change Status')}}
                    </a>
                </div>
            </div>

            {{-- Change Status Modal --}}
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">
                                {{ ('Choose an status')}}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" style="min-height: 400px">
                            <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity" id="update_status">
                                <option value="open">{{ ('Open') }}</option>
                                <option value="working">{{ ('Working') }}</option>
                                <option value="closed">{{ ('Closed') }}</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="change_status()">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <select class="form-control" name="source">
                        <option value="">{{ ('Filter by order source') }}</option>
                        @foreach ($order_sources ?? [] as $key => $order_source)
                            @if(strlen(trim($order_source)))
                                <option value="{{ strtolower($order_source) }}" @if(strtolower($order_source) == strtolower($source)) selected @endif>{{ strtoupper($order_source) }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ @$date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"@isset($search) value="{{ $search }}" @endisset placeholder="{{ ('Type customer name or phone') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group btn-sm" role="group" aria-label="Basic example">
                    @foreach ($issues as $key => $value)
                        <a href="{{ route('services.index', Str::slug($value)) }}" class="btn btn-secondary btn-sm @if ($issue == Str::slug($value)) active @endif">
                            {{ $value }} <span class="w-auto badge badge-primary">{{ data_get($issueWiseCount, Str::slug($value), 0) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th data-breakpoints="md">{{ ('Customer') }}</th>
                        <th data-breakpoints="md">{{ ('Subject') }}</th>
                        <th data-breakpoints="md">{{ ('Priority')}}</th>
                        <th data-breakpoints="md">{{ ('Status') }}</th>
                        <th data-breakpoints="md">{{ ('Opening Date') }}</th>
                        <th class="text-right" width="15%">{{ ('options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $ticket)
                        <tr>
                            <td>
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-one" name="id[]" value="{{$ticket->id}}">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $ticket->name }}<br>{{ $ticket->phone }}</td>
                            <td>{{ limit_text($ticket->subject) }}</td>
                            <td>
                                @php
                                    $class = match($ticket->priority) {
                                        'low' => 'secondary',
                                        'medium' => 'info',
                                        'high' => 'warning',
                                        'critical' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge badge-inline badge-{{ $class }} font-weight-bold">
                                    {{ strtoupper($ticket->priority) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $class = match($ticket->status) {
                                        'open' => 'warning',
                                        'working' => 'info',
                                        'closed' => 'secondary',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge badge-inline badge-{{ $class }} font-weight-bold">
                                    {{ strtoupper($ticket->status) }}
                                </span>
                            </td>
                            <td>
                                {{ $ticket->created_at->format('d-m-Y \a\t h:i A') }}
                            </td>
                            <td class="text-right">
                                <span class="badge badge-inline badge-success">VIEW</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $tickets->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>
@endsection
@section('modal')
    @include('modals.bulk_delete_modal')
@endsection
@section('script')
    <script type="text/javascript">
        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }
        });

    function change_status() {
        var data = new FormData($('#sort_orders')[0]);
        data.append('status', $('#update_delivery_status').val());
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{route('bulk-order-status')}}",
            type: 'POST',
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (response) {
                if(response == 1) {
                    location.reload();
                }
            }
        });
    }

    function bulk_delete() {
        var data = new FormData($('#sort_orders')[0]);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{route('bulk-order-delete')}}",
            type: 'POST',
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (response) {
                if(response == 1) {
                    location.reload();
                }
            }
        });
    }
    function bulk_delete_modal(){
        $('#bulk_delete-modal').modal('show');
    }
    </script>
@endsection
