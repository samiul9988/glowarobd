@extends('backend.layouts.app')
@section('content')
    <div class="card">
        <form class="" id="sort_products" action="" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-md-0 h6">All Highlighted Items</h5>
                </div>

                <div class="col-md-2 ml-auto">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="type"
                        name="type" onchange="sort_products()" data-live-search="true">
                        <option value="">{{ 'All Types' }}</option>
                        <option value="product" @if (request()->type == 'product') selected @endif>Product</option>
                        <option value="brand" @if (request()->type == 'brand') selected @endif>Brand</option>
                        <option value="category" @if (request()->type == 'category') selected @endif>Category</option>
                        <option value="custom" @if (request()->type == 'custom') selected @endif>Custom</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control form-control-sm" id="search" name="search"
                            value="{{ request()->search }}" placeholder="Type & Enter">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="reset_filters()">Reset</button>
                    <div class="dropdown mb-2 mb-md-0 d-inline-block">
                        <button type="button" data-toggle="dropdown" class="btn btn-light btn-sm p-0 py-1">
                            <i class="las la-ellipsis-v font-weight-bold fs-24"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item btn-item" href="{{ route('highlightedProduct.create') }}">
                                <i class="las la-plus text-success"></i> Create New
                            </a>
                            <a class="dropdown-item btn-item" href="javascript:;" onclick="bulk_delete_modal()">
                                <i class="las la-trash text-danger"></i> Bulk Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body">
            <table class="table aiz-table mb-0" id="theTable">
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
                        <th>Title</th>
                        <th class="text-center">Linkable Type</th>
                        <th>Link</th>
                        <th>Status</th>
                        <th>Display Position @include('components.tooltip', ['title' => 'Items will be displayed in ascending order'])</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($highlightedItems as $key => $highlightedItem)
                        @php
                            $formattedLink = '<span class="text-danger">Link not found</span>';
                            if (filled($highlightedItem->custom_link)) {
                                $formattedLink = '<a href="' . $highlightedItem->custom_link . '" target="_blank">' . Str::limit($highlightedItem->custom_link, 30) . '</a>';
                            } else if ($highlightedItem->linkable) {
                                $formattedLink = match(strtolower(class_basename($highlightedItem->linkable_type))) {
                                    'product' => '<a href="' . to_frontend(route('product', $highlightedItem->linkable->slug)) . '" target="_blank">' . $highlightedItem->linkable->name . '</a>',
                                    'category' => '<a href="' . to_frontend(route('products.category', $highlightedItem->linkable->slug), 'category') . '" target="_blank">' . $highlightedItem->linkable->name . '</a>',
                                    'brand' => '<a href="' . to_frontend(route('products.brand', $highlightedItem->linkable->slug), 'brand') . '" target="_blank">' . $highlightedItem->linkable->name . '</a>',
                                    default => '<span class="text-danger">Link not found</span>',
                                };
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="form-group d-inline-block">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{ $highlightedItem->id }}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <span class="d-block font-weight-bold">{{ Str::limit($highlightedItem->title, 30) }}</span>
                            </td>
                            <td class="text-center">
                                {{ $highlightedItem->linkable_type ? class_basename($highlightedItem->linkable_type) : 'Custom' }}
                            </td>
                            <td>
                                {!! $formattedLink !!}
                            </td>
                            <td>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" id="item-status-{{ $highlightedItem->id }}" {{ $highlightedItem->status ? 'checked' : '' }} value="{{ $highlightedItem->id }}" onchange="update_status(this)">
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td>
                                <div class="row gutters-5">
                                    <div class="col-10">
                                        <input type="text" class="form-control form-control-sm new-position"
                                            id="item-position-{{ $highlightedItem->id }}" data-min="0" data-position="{{ $highlightedItem->position }}" value="{{ $highlightedItem->position }}" style="border-radius: 5px;">
                                    </div>
                                    <div class="col w-80px">
                                        <div class="d-flex align-items-center h-100">
                                            <button class="btn btn-outline-none p-0" type="button"
                                                id="status-{{ $highlightedItem->id }}" style="display: none;" disabled>
                                                <i class="la la-spinner la-spin fs-18"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <a class="btn btn-soft-success btn-icon btn-circle btn-sm" href="{{ route('highlightedProduct.edit', $highlightedItem->id) }}" title="{{ ('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{ route('highlightedProduct.destroy', $highlightedItem->id) }}" title="{{ ('Delete') }}">
                                    <i class="las la-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $highlightedItems->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection

@section('modal')
    @include('modals.delete_modal')
    @include('modals.bulk_delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        async function update_status(el){
            const response = await touchModel({ id: el.value, status: el.checked ? 1 : 0 });
            if(response){
                AIZ.plugins.notify('success', 'Status has been updated successfully');
            }
            else{
                AIZ.plugins.notify('danger', 'Something went wrong');
                el.checked = !el.checked;
            }
        }

        async function touchModel(data) {
            data['_token'] = '{{ csrf_token() }}';
            return await $.ajax({
                url: '{{ route('highlightedProduct.touch', 'id') }}'.replace('id', data.id),
                type: 'PUT',
                data: data,
                success: function(response){
                    if(response.success){
                        return true;
                    }
                    else{
                        return false;
                    }
                }, error: function(xhr, status, error) {
                    console.error('Error:', error);
                    return false;
                }
            });
        }

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

        $('#search').on('keydown', function(e) {
            if (e.keyCode == 13) {
                e.preventDefault(); // Ensure it is only this code that runs
                sort_products();
            }
        });

        function sort_products(el) {
            $('#sort_products').submit();
        }

        function reset_filters() {
            window.location.href = '{{ route('highlightedProduct.index') }}';
        }

        function bulk_delete_modal(){
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                showAlert('error', 'Please select at least one item');
                return;
            }
            $('#bulk_delete-modal').modal('show');
        }

        function bulk_delete() {
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                $('#bulk_delete-modal').modal('hide');
                showAlert('error', 'Please select at least one item');
                return;
            }
            $.ajax({
                type: "POST",
                url: "{{ route('highlightedProduct.bulk-destroy') }}",
                data: {
                    ids: ids,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        $('#bulk_delete-modal').modal('hide');
                        showAlert('success', response.message, window.location.href);
                    } else {
                        $('#bulk_delete-modal').modal('hide');
                        showAlert('error', response.message || 'Something went wrong');
                    }
                },
                error: function (xhr) {
                    $('#bulk_delete-modal').modal('hide');
                    showAlert('error', xhr.responseJSON.message);
                }
            });
        }

        $().ready(function() {
            $('#theTable').on('focusout', '.new-position', async function() {
                let id = $(this).closest('tr').find('.check-one').val();
                let new_position = parseFloat($(this).val()) || 0;
                let old_position = parseFloat($(this).data('position')) || 0;
                if (old_position === new_position || isNaN(new_position) || new_position == '') {
                    return;
                }
                $('#item-position-' + id).prop('disabled', true);
                $('#status-' + id).fadeIn();

                const response = await touchModel({ id: id, position: new_position });

                if (response) {
                    $(this).data('position', new_position);
                    AIZ.plugins.notify('success', 'Position has been updated successfully');
                } else {
                    AIZ.plugins.notify('danger', 'Something went wrong');
                    $(this).val(old_position);
                }
                $('#item-position-' + id).prop('disabled', false);
                $('#status-' + id).fadeOut();
            });
        });
    </script>
@endsection
