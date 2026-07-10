@extends('backend.layouts.app')

@section('content')
<style>
    .badge-count{
        margin-left: 5px;
        width: auto;
    }
</style>
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ ('Products Custom Fields')}}</h1>
        </div>
        @if(@$type != 'Seller')
        <div class="col text-right">
            <button class="btn btn-sm btn-circle btn-info" id="productCustomFieldCreate">{{ ('Create')}}</button>
        </div>
        @endif
    </div>
</div>
<br>

<div class="card">
    <form class="" id="sort_products" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('Products Custom Fields') }}</h5>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{ ('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" onclick="bulk_delete_modal()"> {{ ('Delete selection')}}</a>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ @$search }}" placeholder="{{ ('Type & Enter') }}">
                </div>
            </div>
        </div>

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
                        <th data-breakpoints="sm">{{ ('Name')}}</th>
                        <th data-breakpoints="md">{{ ('Type')}}</th>
                        <th data-breakpoints="lg">{{ ('Status')}}</th>
                        <th data-breakpoints="sm" class="text-right">{{ ('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(@$customFields ?? [] as $key => $customField)
                    @php
                        $customField->bannerPreview = uploaded_asset($customField->banner);
                    @endphp
                    <tr>
                        <td>
                            <div class="form-group d-inline-block">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" class="check-one" name="id[]" value="{{$customField->id}}">
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="row gutters-5 w-200px w-md-300px mw-100">
                                <div class="col">
                                    <span class="text-muted text-truncate-2">{{ ($customField->name) }} </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $type = str_replace('_', ' ', $customField->type);
                            @endphp
                            <span>{{ ucwords($type) }}</span>
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_status(this)" value="{{ $customField->id }}" type="checkbox" @if ($customField->is_active) checked @endif >
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm edit-btn" href="javascript:;" title="{{ ('Edit') }}" data-href="{{ route('products.custom_fields.update', $customField->id) }}" data-value="{{ json_encode($customField) }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('products.custom_fields.destroy', $customField->id)}}" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $customFields->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
    @include('modals.bulk_delete_modal')
    <div class="modal fade" id="productCustomFieldModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="productCustomFieldModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productCustomFieldModalLabel">Create Custom Field</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="productCustomFieldForm" method="post">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="field_name">
                                {{ ('Field Name') }}
                            </label>
                            <input id="field_name" name="field_name" class="form-control" placeholder="Enter field name" />
                        </div>
                        <div id="slugable" class="form-group mb-3 d-none">
                            <label for="field_slug">
                                {{ ('Field Slug') }}
                            </label>
                            <input id="field_slug" name="field_slug" class="form-control" placeholder="enter_field_slug" />
                        </div>
                        <div class="form-group mb-3">
                            <label for="field_type">
                                {{ ('Field Type') }}
                            </label>
                            <select id="field_type" name="field_type" class="form-control">
                                <option value="">Select field type</option>
                                @foreach (\App\Models\ProductCustomField::getFields() as $key => $field)
                                    <option value="{{ $key }}">{{ $field }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="field_status">
                                {{ ('Status') }}
                            </label>
                            <select id="field_status" name="field_status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group row">
                            <label class="col-12 col-form-label"
                                for="field_banner">Banner
                                <small>(300x300)</small>
                            </label>
                            <div class="col-12">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="field_banner" id="field_banner" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                                <small class="text-muted">This image is visible in product details with faq. Use 300x300 sizes image.</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
                    <button type="button" class="btn btn-primary" id="productCustomFieldModalButton">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $('#productCustomFieldForm').submit(function(e) {
            e.preventDefault();
        });
        $('#productCustomFieldCreate').click(function() {
            $('#slugable').addClass('d-none');
            $('#productCustomFieldModalLabel').text('Create Custom Field');
            $('#productCustomFieldForm').trigger('reset');
            $('#field_banner').val('');
            AIZ.uploader.previewGenerate();
            $('#productCustomFieldForm').attr('action', `{{ route('products.custom_fields.store') }}`);
            $('#productCustomFieldModalButton').text('Save');
            $('#productCustomFieldModal').modal('show');
        });
        $('#theTable').on('click','.edit-btn',function() {
            $('#slugable').removeClass('d-none');
            $('#productCustomFieldForm').trigger('reset');
            $('#productCustomFieldModalLabel').text('Edit Custom Field');
            $('#productCustomFieldForm').attr('action', $(this).data('href'));
            var data = $(this).data('value');
            $('#field_name').val(data.name);
            $('#field_slug').val(data.slug);
            $('#field_type').val(data.type);
            $('#field_status').val(data.is_active);
            $('#field_banner').val(data.banner);
            AIZ.uploader.previewGenerate();
            $('#productCustomFieldModalButton').text('Update');
            $('#productCustomFieldModal').modal('show');
        });
        $('#productCustomFieldModalButton').on('click', function(){
            var href = $('#productCustomFieldForm').attr('action');
            var field_name = $('#field_name').val();
            var field_type = $('#field_type').val();
            var field_status = $('#field_status').val();
            var field_banner = $('#field_banner').val();
            if(field_name == '' || field_type == '' || field_status == '') {
                AIZ.plugins.notify('danger', '{{ ('All fields are required') }}');
            } else {
                $.ajax({
                    type: 'POST',
                    url: href,
                    data: {
                        _token: '{{ csrf_token() }}',
                        name: field_name,
                        type: field_type,
                        is_active: field_status,
                        banner: field_banner
                    },
                    success: function(response) {
                        if(response.success) {
                            AIZ.plugins.notify('success', `{{ ('Custom field created successfully') }}`);
                            $('#productCustomFieldModal').modal('hide');
                            location.reload();
                        } else {
                            AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                        }
                    },
                    error: function(xhr, error, status) {
                        if(xhr.status == 422){
                            AIZ.plugins.notify('danger', xhr.responseJSON?.errors?.name[0] || 'The given data is invalid');
                        }else{
                            AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                        }
                    }
                });
            }
        });

        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });

        function update_status(el){
            var is_active = el.checked ? 1 : 0;
            $.ajax({
                url: `{{ route('products.custom_fields.update_status', ':id') }}`.replace(':id', el.value),
                type: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}',
                    is_active: is_active
                },
                success: function (response) {
                    if(response.success){
                        AIZ.plugins.notify('success', '{{ ('Status has been updated successfully') }}');
                        location.reload();
                    }
                    else{
                        AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                        el.checked = !el.checked;
                    }
                },
                error: function (response) {
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                    el.checked = !el.checked;
                }
            });
        }

        function bulk_delete() {
            // select all with the name of id[]
            var data = $('input[name="id[]"]:checked');

            // Extract the values
            var id = [];
            data.each(function() {
                id.push($(this).val());
            });
            $.ajax({
                url: "{{route('products.custom_fields.bulk_destroy')}}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id
                },
                success: function (response) {
                    if(response.success) {
                        AIZ.plugins.notify('success', response.message);
                        location.reload();
                    }
                },
                error: function (xhr) {
                    AIZ.plugins.notify('danger', xhr.responseJSON.message || 'Something went wrong');
                    $('#bulk_delete-modal').modal('hide');
                }
            });
        }
        function bulk_delete_modal(){
            $('#bulk_delete-modal').modal('show');
        }
    </script>
@endsection
