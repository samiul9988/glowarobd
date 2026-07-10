@extends('backend.layouts.app')

@section('content')
    <div class="row mb-2">
        <div class="col">
            <h5 class="mb-md-0 h5">{{ 'All Templates' }}</h5>
        </div>
        <div class="col">
            <a href="{{ route('templates.create') }}" class="btn btn-success btn-sm float-right">
                <i class="las la-plus"></i> {{ 'Create Template' }}
            </a>
        </div>
    </div>
    <div class="card">
        <form class="" action="" id="sort_orders" method="GET">
            <div class="card-header row gutters-5">
                <div class="dropdown mb-2 mb-md-0">
                    <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                        {{ 'Bulk Action' }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="javascript:;" onclick="bulk_change_status()">
                            <i class="las la-sync-alt"></i>
                            {{ 'Change Status' }}
                        </a>
                    </div>
                </div>

                <div class="col-lg-2">
                    <div class="form-group mb-0">
                        <select class="form-control aiz-selectpicker" name="type" data-live-search="true">
                            <option value="">{{ 'Filter by type' }}</option>
                            @foreach (\App\Enums\TemplateTypes::options() as $key => $value)
                                <option value="{{ $key }}" {{ @$type === $key ? 'selected' : '' }}>
                                    {{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group mb-0">
                        <select class="form-control" name="status">
                            <option value="">{{ 'Filter by status' }}</option>
                            <option value="1" @if (@$status == 1) selected @endif>{{ 'Active' }}
                            </option>
                            <option value="0" @if (@$status === 0) selected @endif>{{ 'Inactive' }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group mb-0">
                        <input type="text" class="aiz-date-range form-control" value="{{ @$date }}"
                            name="date" placeholder="{{ 'Filter by date' }}" data-format="DD-MM-Y"
                            data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control" id="search"
                            name="search"@isset($search) value="{{ $search }}" @endisset
                            placeholder="{{ 'Search ...' }}">
                    </div>
                </div>
                <div class="col-auto">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">{{ 'Filter' }}</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th width="5%">
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-all">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </th>
                            <th>{{ 'Name' }}</th>
                            <th data-breakpoints="md">{{ 'Type' }}</th>
                            <th data-breakpoints="md">{{ 'Status' }}</th>
                            <th data-breakpoints="md">{{ 'Created At' }}</th>
                            <th class="text-center" width="15%">{{ 'Options' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($templates as $key => $template)
                            <tr>
                                <td>
                                    <div class="form-group">
                                        <div class="aiz-checkbox-inline">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" class="check-one" name="id[]"
                                                    value="{{ $template->id }}">
                                                <span class="aiz-square-check"></span>
                                            </label>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ Str::limit($template->name, 50) }}
                                </td>
                                <td>
                                    {{ $template->type->label() }}
                                </td>
                                <td>
                                    <span
                                        class="badge badge-inline badge-{{ $template->status ? 'success' : 'danger' }} font-weight-bold">{{ ucfirst($template->status ? 'Active' : 'Inactive') }}</span>
                                </td>
                                <td>
                                    By <b>{{ $template->createdBy->name ?? 'N/A' }}</b><br>
                                    At <b>{{ $template->created_at->format('d-m-Y h:i A') }}</b>
                                </td>
                                <td class="text-center">
                                    <a class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                        href="{{ route('templates.edit', $template->id) }}" title="{{ 'Edit' }}">
                                        <i class="las la-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $templates->appends(request()->input())->links() }}
                </div>
            </div>
        </form>
    </div>
@endsection
@section('modal')
    @include('modals.delete_modal')
    @include('modals.bulk_delete_modal')
    {{-- Change Status Modal --}}
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        {{ 'Choose status' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="min-height: 400px">
                    <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                        id="update_status">
                        <option value="draft" selected>{{ 'Draft' }}</option>
                        <option value="published" selected>{{ 'Published' }}</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="change_status()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        $(document).on("change", ".check-all", function() {
            if (this.checked) {
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
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                $('#exampleModal').modal('hide');
                showAlert('error', '{{ 'Please select at least one item' }}');
                return;
            }
            let status = $('#update_status').val();

            $.ajax({
                type: "POST",
                url: "{{ route('notices.bulk-status-update') }}",
                data: {
                    ids: ids,
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#exampleModal').modal('hide');
                        showAlert('success', response.message, window.location.href);
                    } else {
                        $('#exampleModal').modal('hide');
                        showAlert('error', response.message || '{{ 'Something went wrong' }}');
                    }
                },
                error: function(xhr) {
                    $('#exampleModal').modal('hide');
                    showAlert('error', xhr.responseJSON.message);
                }
            });
        }

        function bulk_delete() {
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                $('#bulk_delete-modal').modal('hide');
                showAlert('error', '{{ 'Please select at least one item' }}');
                return;
            }
            $.ajax({
                type: "POST",
                url: "{{ route('notices.bulk-delete') }}",
                data: {
                    ids: ids,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#bulk_delete-modal').modal('hide');
                        showAlert('success', response.message, window.location.href);
                    } else {
                        $('#bulk_delete-modal').modal('hide');
                        showAlert('error', response.message || '{{ 'Something went wrong' }}');
                    }
                },
                error: function(xhr) {
                    $('#bulk_delete-modal').modal('hide');
                    showAlert('error', xhr.responseJSON.message);
                }
            });
        }

        function bulk_change_status() {
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                showAlert('error', '{{ 'Please select at least one item' }}');
                return;
            }
            $('#exampleModal').modal('show');
        }

        function bulk_delete_modal() {
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                showAlert('error', '{{ 'Please select at least one item' }}');
                return;
            }
            $('#bulk_delete-modal').modal('show');
        }
    </script>
@endsection
