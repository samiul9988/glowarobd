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
            <h1 class="h3">{{ ('Meta Objects')}}</h1>
        </div>
        @if(@$type != 'Seller')
        <div class="col text-right">
            <button class="btn btn-sm btn-circle btn-info" id="metaObjectFieldCreate">{{ ('Create')}}</button>
        </div>
        @endif
    </div>
</div>
<br>

<div class="card">
    <form class="" id="sort_products" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('Meta Objects') }}</h5>
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
                        <th data-breakpoints="lg">{{ ('Status')}}</th>
                        <th data-breakpoints="sm" class="text-right">{{ ('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(@$metaObjects ?? [] as $key => $metaObject)
                    <tr>
                        <td>
                            <div class="form-group d-inline-block">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" class="check-one" name="id[]" value="{{$metaObject->id}}">
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="row gutters-5 w-200px w-md-300px mw-100">
                                <div class="col">
                                    <a href="{{ route('meta-object-items.index', ['group'=>$metaObject->name]) }}"><b>{{ ($metaObject->name) }} </b></a>
                                </div>
                            </div>
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_status(this)" value="{{ $metaObject->id }}" type="checkbox" @if ($metaObject->is_active) checked @endif >
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm edit-btn" href="javascript:;" title="{{ ('Edit') }}" data-href="{{ route('meta-objects.update', $metaObject->id) }}" data-value="{{ json_encode($metaObject) }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('meta-objects.destroy', $metaObject->id)}}" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $metaObjects->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
    @include('modals.bulk_delete_modal')
    <div class="modal fade" id="metaObjectFieldModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="metaObjectFieldModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="metaObjectFieldModalLabel">Create Meta Object</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="metaObjectFieldForm" method="post">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="name">
                                {{ ('Name') }}
                            </label>
                            <input id="name" name="name" class="form-control" placeholder="Enter name" />
                        </div>
                        <div class="form-group mb-3">
                            <label for="status">
                                {{ ('Status') }}
                            </label>
                            <select id="status" name="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
                    <button type="button" class="btn btn-primary" id="metaObjectFieldModalButton">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $('#metaObjectFieldForm').submit(function(e) {
            e.preventDefault();
        });
        $('#metaObjectFieldCreate').click(function() {
            $('#metaObjectFieldModalLabel').text('Create Meta Object');
            $('#metaObjectFieldForm').trigger('reset');
            $('#metaObjectFieldForm').attr('action', `{{ route('meta-objects.store') }}`);
            $('#metaObjectFieldModalButton').text('Save');
            $('#metaObjectFieldModal').modal('show');
        });
        $('#theTable').on('click','.edit-btn',function() {
            $('#metaObjectFieldForm').trigger('reset');
            $('#metaObjectFieldModalLabel').text('Edit Meta Object');
            $('#metaObjectFieldForm').attr('action', $(this).data('href'));
            var data = $(this).data('value');
            $('#name').val(data.name);
            $('#status').val(data.is_active);
            $('#metaObjectFieldModalButton').text('Update');
            $('#metaObjectFieldModal').modal('show');
        });
        $('#metaObjectFieldModalButton').on('click', function(){
            var href = $('#metaObjectFieldForm').attr('action');
            var name = $('#name').val();
            var status = $('#status').val();
            if(name == '' || status == '') {
                AIZ.plugins.notify('danger', '{{ ('All fields are required') }}');
            } else {
                $.ajax({
                    type: 'POST',
                    url: href,
                    data: {
                        _token: '{{ csrf_token() }}',
                        name: name,
                        is_active: status
                    },
                    success: function(response) {
                        if(response.success) {
                            AIZ.plugins.notify('success', `{{ ('Meta object created successfully') }}`);
                            $('#metaObjectFieldModal').modal('hide');
                            location.reload();
                        } else {
                            AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                        }
                    },
                    error: function(response) {
                        AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
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
                url: `{{ route('meta-objects.update_status', ':id') }}`.replace(':id', el.value),
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
                url: "{{route('meta-objects.bulk_destroy')}}",
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
