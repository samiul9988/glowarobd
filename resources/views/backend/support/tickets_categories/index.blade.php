@extends('backend.layouts.app')
@section('content')
<div class="row gutters-10">
    {{-- <div class="col">
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
    </div> --}}
</div>
<div class="card">
    <form class="" action="{{ route('ticket_categories.index') }}" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('All Ticket Categories') }}</h5>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{ ('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    {{-- <a class="dropdown-item" href="javascript:;" onclick="bulk_delete_modal()"> {{ ('Delete selection')}}</a> --}}
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
                                <option value="1">{{ ('Active') }}</option>
                                <option value="0">{{ ('Inactive') }}</option>
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
                    @php
                        $status = is_null($status) ? '' : (string)$status;
                    @endphp

                    <select class="form-control" name="status">
                        <option value="" @if($status === '') selected @endif>{{ ('Filter by status') }}</option>
                        <option value="1" @if($status === '1') selected @endif>{{ ('Active') }}</option>
                        <option value="0" @if($status === '0') selected @endif>{{ ('Inactive') }}</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-3 mb-2 mb-lg-0">
                <div class="form-group mb-0">
                    <select class="form-control aiz-selectpicker" data-live-search="true" name="parent" id="parent">
                        <option value="">{{ ('Filter by parent category') }}</option>
                        @foreach (\App\Models\TicketCategory::active()->whereNull('parent_id')->pluck('name', 'id') as $id => $name)
                            <option value="{{ $id }}" @if (@$parent_id == $id) selected @endif>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            {{-- <div class="col-lg-2 mb-2 mb-lg-0">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ @$date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div> --}}
            <div class="col-lg-3 mb-2 mb-lg-0">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" value="{{ @$search }}" placeholder="{{ ('Search by name ...') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-end align-items-center">
                <div>
                    <a href="{{ route('ticket_categories.create') }}" class="btn btn-success btn-sm float-right">
                        <i class="las la-plus"></i> {{ ('Create Ticket Category') }}
                    </a>
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
                        <th>{{ ('Name') }}</th>
                        <th>{{ ('Parent Category') }}</th>
                        <th>{{ ('Status') }}</th>
                        <th data-breakpoints="md">{{ ('Created At') }}</th>
                        <th class="text-center" width="15%">{{ ('options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td>
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-one" name="id[]" value="{{$category->id}}">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td title="{{ $category->description }}">
                                {{ limit_text(ucfirst($category->name ?? 'N/A')) }}
                            </td>
                            <td>{{ limit_text(ucfirst($category->parent->name ?? 'N/A')) }}</td>
                            <td>
                                <span class="badge badge-inline badge-{{ $category->status == 1 ? 'success' : 'danger' }} font-weight-bold">
                                    {{ strtoupper($category->status == 1 ? 'Active' : 'Inactive') }}
                                </span>
                            </td>
                            <td>
                                {{ $category->created_at->format('d-m-Y \a\t h:i A') }}
                            </td>
                            <td class="text-center">
                                <a class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                    href="{{ route('ticket_categories.edit', $category->id) }}"
                                    title="{{ ('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $categories->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>
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
            let ids = data.getAll('id[]');
            if(ids.length === 0) {
                showAlert('warning', 'Please select at least one category.');
                return;
            }
            data.append('status', $('#update_status').val());
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('ticket_categories.bulk_status')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response.success) {
                        $('#exampleModal').modal('hide');
                        showAlert('success', response.message, window.location.href);
                    }else {
                        showAlert('danger', response.message);
                    }
                },
                error: function (xhr) {
                    var response = xhr.responseJSON;
                    if (response && response.message) {
                        showAlert('error', response.message);
                    } else {
                        showAlert('error', 'Server Error!!');
                    }
                }
            });
        }
    </script>
@endsection
