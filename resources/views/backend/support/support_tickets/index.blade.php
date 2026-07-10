@extends('backend.layouts.app')
@php
    $issues = ['All', 'General Query', 'Refund Issue', 'Authenticity Issue', 'Skincare Suggestion', 'Exchange Product', 'Product Query', 'Restock Reminder', 'Others'];
    $currentStatus = 'opened';
@endphp
@section('content')
<div class="row gutters-10">
    {{-- <div class="col">
        <a href="{{ request()->fullUrlWithQuery(['priority' => 'low']) }}">
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
    </div> --}}
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
<div class="row gutters-10">
    <div class="col">
        <a href="{{ route('tickets.admin_index', ['status'=>'open']) }}">
            <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
                <div class="px-3 pt-3">
                    <div class="opacity-50">
                        <span class="fs-12 d-block">{{ ('Open') }}</span>
                    </div>
                    <div class="h3 fw-700 mb-3">
                        {{ data_get($statusWiseCount, 'open', 0) }}
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="{{ route('tickets.admin_index', ['status'=>'working']) }}">
            <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
                <div class="px-3 pt-3">
                    <div class="opacity-50">
                        <span class="fs-12 d-block">{{ ('Working') }}</span>
                    </div>
                    <div class="h3 fw-700 mb-3">
                        {{ data_get($statusWiseCount, 'working', 0) }}
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="{{ route('tickets.admin_index', ['status'=>'closed']) }}">
            <div class="bg-grad-4 text-white rounded-lg mb-4 overflow-hidden">
                <div class="px-3 pt-3">
                    <div class="opacity-50">
                        <span class="fs-12 d-block">{{ ('Closed') }}</span>
                    </div>
                    <div class="h3 fw-700 mb-3">
                        {{ data_get($statusWiseCount, 'closed', 0) }}
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
<div class="card">
    <form class="" action="{{ route('tickets.admin_index') }}" id="sort_orders" method="GET">
        <div class="card-header">
            <div>
                <h5 class="mb-md-0 h6">{{ ('All Tickets') }}</h5>
            </div>
            <div>
                <a href="{{ route('tickets.create') }}" class="btn btn-success btn-sm float-right">
                    <i class="las la-plus"></i> {{ ('Create Ticket') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 mb-2">
                    <div class="row gutters-5">
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

                        <div class="col-lg-2 mb-2 mb-lg-0">
                            <div class="form-group mb-0">
                                <select class="form-control" name="status">
                                    <option value="">{{ ('Filter by status') }}</option>
                                    <option value="open" @if($status == 'open') selected @endif>{{ ('Open') }}</option>
                                    <option value="working" @if($status == 'working') selected @endif>{{ ('Working') }}</option>
                                    <option value="closed" @if($status == 'closed') selected @endif>{{ ('Closed') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-2 mb-lg-0">
                            <div class="form-group mb-0">
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="employee" id="employee">
                                    <option value="">{{ ('Filter by employee') }}</option>
                                    @foreach ($staffs as $staff)
                                        <option value="{{ $staff->id }}" @if($employee == $staff->id) selected @endif>
                                            {{ $staff->name . (Auth::id() == $staff->id ? ' (Me)' : '') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-2 mb-lg-0">
                            <div class="form-group mb-0">
                                <input type="text" class="aiz-date-range form-control" value="{{ @$date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $allCategories = \App\Models\TicketCategory::active()
                    ->with('parent:id,name', 'childsWithTicketsCount')
                    ->withCount('tickets')
                    ->orderBy('tickets_count', 'desc')
                    ->get();

                    $selectedCategory = $allCategories->where('id', @$category_id)->first();
                    // dd($selectedCategory);

                    if($selectedCategory && $selectedCategory->parent_id){
                        $filteredCategories = $allCategories->where('parent_id', $selectedCategory->parent_id);
                    } elseif(@$category_id) {
                        $filteredCategories = $allCategories->where('parent_id', $category_id);
                    } else{
                        $filteredCategories = $allCategories->whereNotNull('parent_id');
                    }
                    // $filteredCategories = @$category_id ? $allCategories->where('parent_id', $category_id) : $allCategories->whereNotNull('parent_id');
                @endphp
                <div class="col-12">
                    <div class="row gutters-5">
                        <div class="col-lg-5 mb-2 mb-lg-0">
                            <div class="form-group mb-0">
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="category" id="category">
                                    <option value="">{{ ('Filter by category') }}</option>
                                    @foreach ($filteredCategories as $item)
                                        <option value="{{ $item->id }}" @if (@$category_id == $item->id) selected @endif>
                                            <span class="badge badge-info badge-inline">{{ '['.$item->tickets_count.'] ' }}</span>{{ $item->name . ($item->parent_id ? ' ('.$item->parent->name.')' : '') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-5 mb-2 mb-lg-0">
                            <div class="form-group mb-0">
                                <input type="text" class="form-control" id="search" name="search"@isset($search) value="{{ $search }}" @endisset placeholder="{{ ('Type customer name, phone') }}">
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $parentCategories = $allCategories->whereNull('parent_id');
            @endphp
            <hr>
            <div class="row d-none d-md-block mt-md-2">
                <div class="col-12">
                    <div class="row gutters-5">
                        @foreach ($parentCategories as $parentCategory)
                            <div class="col-auto mb-2">
                                <a href="{{ route('tickets.admin_index', ['category' => $parentCategory->id]) }}" class="btn btn-secondary btn-sm {{ (@$category_id == $parentCategory->id || $selectedCategory?->parent_id == $parentCategory->id) ? 'active' : '' }}">
                                    {{ $parentCategory->name }}
                                    <span class="badge badge-primary badge-inline">
                                        {{ $parentCategory->tickets_count + ($parentCategory->childsWithTicketsCount?->sum('tickets_count') ?? 0) }}
                                    </span>
                                </a>
                            </div>
                        @endforeach
                    </div>
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
                        <th>{{ ('Customer') }}</th>
                        <th>{{ ('Category') }}</th>
                        <th data-breakpoints="sm">{{ ('Related') }}</th>
                        <th data-breakpoints="sm">{{ ('Priority')}}</th>
                        <th>{{ ('Status') }}</th>
                        <th data-breakpoints="md">{{ ('Opening Date') }}</th>
                        <th data-breakpoints="md">{{ ('Closing Date') }}</th>
                        <th data-breakpoints="md">{{ ('Active Time') }}</th>
                        <th data-breakpoints="md">{{ ('Assign To') }}</th>
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
                            <td>
                                @if ($ticket->user && $ticket->user->user_type == 'customer')
                                    <a class="font-weight-bold" href="{{ route('customers.details', $ticket->user_id) }}" target="_blank">
                                        {{ $ticket->name ?? 'Customer' }}
                                    </a>
                                @else
                                    {{ $ticket->name ?? 'Customer' }}
                                @endif
                                @if($ticket->phone)
                                    <br>{{ $ticket->phone }}
                                @endif
                            </td>
                            <td>{{ limit_text(ucwords($ticket->category->name ?? 'N/A')) }}</td>
                            <td>
                                @if ($ticket->order)
                                    <a class="font-weight-bold" href="{{ route('orders.show', encrypt($ticket->order->id)) }}" target="_blank">
                                        #{{ $ticket->order->code }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
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
                                {{ $ticket->created_at->format('d-m-Y') }} <br>
                                At {{ $ticket->created_at->format('h:i A') }} <br>
                                By <span class="font-weight-bold">{{ $ticket->user->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if(is_null($ticket->closed_at))
                                    <span class="badge badge-inline badge-danger font-weight-bold">Not Closed Yet</span>
                                @else
                                    {{ \Carbon\Carbon::parse($ticket->closed_at)->format('d-m-Y \a\t h:i A') }}
                                @endif
                            </td>
                            <td>
                                {{-- @if(is_null($ticket->closed_at))
                                    --
                                @else --}}
                                    @php
                                        $closedAt = \Carbon\Carbon::parse(is_null($ticket->closed_at) ? now() : $ticket->closed_at);
                                        $createdAt = \Carbon\Carbon::parse($ticket->created_at);

                                        $diff = $createdAt->diffInMinutes($closedAt);
                                        $hours = floor($diff / 60);
                                        $minutes = $diff % 60;
                                    @endphp
                                    @if($hours > 0)
                                        {{ $hours }} {{ $hours > 1 ? 'Hours' : 'Hour' }}
                                    @endif
                                    {{ $minutes }} {{ $minutes > 1 ? 'Minutes' : 'Minute' }}
                                {{-- @endif --}}
                            </td>
                            <td>
                                {{ $ticket->assignedTo->name ?? 'Unassigned' }}
                            </td>
                            <td class="text-right">
                                <a href="{{route('tickets.admin_show', encrypt($ticket->id))}}" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ ('View Details') }}">
                                    <i class="las la-eye"></i>
                                </a>
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
        data.append('status', $('#update_status').val());
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{route('bulk-ticket-status')}}",
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
            url: "{{ route('bulk-ticket-delete') }}",
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
