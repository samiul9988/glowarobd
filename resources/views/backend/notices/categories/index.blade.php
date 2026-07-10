@extends('backend.layouts.app')

@section('content')
    <div class="row mb-2">
        <div class="col">
            <h5 class="mb-md-0 h5">{{ ('All Categories') }}</h5>
        </div>
        <div class="col">
            <a href="javascript:;" onclick="showModal('create')" class="btn btn-success btn-sm float-right">
                <i class="las la-plus"></i> {{ ('Create Category') }}
            </a>
        </div>
    </div>
    <div class="card">
        <form class="" action="" id="sort_orders" method="GET">
            <div class="card-header row gutters-5 justify-content-end">
                <div class="dropdown mb-2 mb-md-0">
                    <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                        {{ ('Bulk Action') }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="javascript:;" onclick="bulk_delete_modal()">
                            {{ ('Delete selection') }}</a>
                        <a class="dropdown-item" href="javascript:;" onclick="bulk_change_status()">
                            <i class="las la-sync-alt"></i>
                            {{ ('Change Status') }}
                        </a>
                    </div>
                </div>

                <div class="col-lg-2">
                    <div class="form-group mb-0">
                        <select class="form-control" name="status">
                            <option value="">{{ ('Filter by status') }}</option>
                            <option value="1" @if (@$status == '1') selected @endif>
                                {{ ('Active') }}</option>
                            <option value="0" @if (@$status == '0') selected @endif>
                                {{ ('Inactive') }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control" id="search"
                            name="search"@isset($search) value="{{ $search }}" @endisset
                            placeholder="{{ ('Search ...') }}">
                    </div>
                </div>
                <div class="col-auto">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
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
                            <th>{{ ('Name') }}</th>
                            <th data-breakpoints="md">{{ ('Status') }}</th>
                            <th data-breakpoints="md">{{ ('Notice Count') }}</th>
                            <th data-breakpoints="md">{{ ('Created At') }}</th>
                            <th class="text-center" width="15%">{{ ('options') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $key => $category)
                            <tr>
                                <td>
                                    <div class="form-group">
                                        <div class="aiz-checkbox-inline">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" class="check-one" name="id[]"
                                                    value="{{ $category->id }}">
                                                <span class="aiz-square-check"></span>
                                            </label>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ Str::limit($category->name, 40) }}
                                </td>
                                <td>
                                    <span
                                        class="badge badge-inline badge-{{ $category->status == 1 ? 'success' : 'danger' }} font-weight-bold">{{ ucfirst(translate($category->status == 1 ? 'Active' : 'Inactive')) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('notices.index',['category'=>$category->id]) }}" class="font-weight-bold">{{ $category->notices->count() }}</a>
                                </td>
                                <td>
                                    {{ $category->created_at->format('d-m-Y h:i A') }}
                                </td>
                                <td class="text-center">
                                    <a class="btn btn-soft-success btn-icon btn-circle btn-sm" href="javascript:;" onclick="showModal('edit', {{ json_encode($category) }})" title="{{ ('Edit') }}">
                                        <i class="las la-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                        data-href="{{ route('notice-categories.destroy', $category->id) }}"
                                        @if($category->notices->count() > 0) data-message="{{ ('Deleting this category will remove '.$category->notices->count().' notices') }}" @endif
                                        title="{{ ('Delete') }}">
                                        <i class="las la-trash"></i>
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
                        {{ ('Choose status') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="min-height: 400px">
                    <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                        id="update_status">
                        <option value="1" selected>{{ ('Active') }}</option>
                        <option value="0">{{ ('Inactive') }}</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="change_status()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    {{-- End Change Status Modal --}}

    {{-- Create/Edit Modal --}}
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">{{ ('Create Category') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="category-form" action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="_method" id="form-method" value="POST">
                        <div class="form-group">
                            <label>{{ ('Name') }} <span class="text-danger font-weight-bold" id="name_error">*</span></label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="Enter category name" required>
                        </div>
                        <div class="form-group">
                            <label>{{ ('Slug') }}</label>
                            <input type="text" class="form-control" name="slug" id="slug" placeholder="Enter category slug" required>
                        </div>
                        <div class="form-group">
                            <label>{{ ('Status') }}</label>
                            <select class="form-control" name="status" id="status">
                                <option value="1">{{ ('Active') }}</option>
                                <option value="0">{{ ('Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ ('Close') }}</button>
                        <button type="submit" class="btn btn-primary" id="submit-btn">{{ ('Create') }}</button>
                    </div>
                </form>
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

        $('#name').on('input', function() {
            // Get the input value
            let name = $(this).val().trim();
            
            if (name.length > 0) {
                // Generate slug
                let slug = name
                    .toLowerCase()                   // Convert to lowercase
                    .replace(/\s+/g, '-')            // Replace spaces with -
                    .replace(/[^\w\-]+/g, '')        // Remove all non-word chars except -
                    .replace(/\-\-+/g, '-')          // Replace multiple - with single -
                    .replace(/^-+/, '')              // Trim - from start
                    .replace(/-+$/, '');              // Trim - from end
                
                $('#slug').val(slug);
            } else {
                $('#slug').val('');
            }
        });

        $('#submit-btn').on('click', function(e){
            e.preventDefault();
            let name = $('#name').val();

            if(name.length === 0){
                $('#name_error').html('* Name is required');
                return;
            }else{
                $('#name_error').html('');
            }

            $('#category-form').submit();
        })

        function showModal(type = 'create', data = null) {
            // Reset form
            $('#category-form')[0].reset();
            
            // Set form action and method
            let url = type === 'create' 
                ? '{{ route("notice-categories.store") }}' 
                : '{{ route("notice-categories.update", ":id") }}'.replace(':id', data.id);
            
            $('#category-form').attr('action', url);
            
            // Set the HTTP method (POST for create, PUT for update)
            $('#form-method').val(type === 'create' ? 'POST' : 'PUT');
            
            // Update modal title and button text
            $('#createModalLabel').text(type === 'create' ? '{{ ("Create Category") }}' : '{{ ("Edit Category") }}');
            $('#submit-btn').text(type === 'create' ? '{{ ("Create") }}' : '{{ ("Update") }}');
            
            // If editing, populate the form fields
            if (type === 'edit' && data) {
                $('#name').val(data.name);
                $('#slug').val(data.slug);
                $('#status').val(data.status);
            }
            
            // Show the modal
            $('#createModal').modal('show');
        }

        function change_status() {
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                $('#exampleModal').modal('hide');
                showAlert('error', '{{ ('Please select at least one item') }}');
                return;
            }
            let status = $('#update_status').val();

            $.ajax({
                type: "POST",
                url: "{{ route('notice-categories.bulk-status-update') }}",
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
                        showAlert('error', response.message || '{{ ('Something went wrong') }}');
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
                showAlert('error', '{{ ('Please select at least one item') }}');
                return;
            }
            $.ajax({
                type: "POST",
                url: "{{ route('notice-categories.bulk-delete') }}",
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
                        showAlert('error', response.message || '{{ ('Something went wrong') }}');
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
                showAlert('error', '{{ ('Please select at least one item') }}');
                return;
            }
            $('#exampleModal').modal('show');
        }

        function bulk_delete_modal() {
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                showAlert('error', '{{ ('Please select at least one item') }}');
                return;
            }
            $('#bulk_delete-modal').modal('show');
        }
    </script>
@endsection
