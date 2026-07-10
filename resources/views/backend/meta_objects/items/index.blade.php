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
            <h1 class="h3">{{ ('Meta Object Items')}}</h1>
        </div>
        @if(@$type != 'Seller')
        <div class="col text-right">
            <a href="@if(filled(@$group)) {{ route('meta-object-items.create', ['group' => $group]) }} @else {{ route('meta-object-items.create') }} @endif" class="btn btn-sm btn-circle btn-info">
                <span>{{ ('Add New Item')}}</span>
            </a>
        </div>
        @endif
    </div>
</div>
<br>

<div class="card">
    <form class="" id="filterForm" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('Meta Object Items') }}</h5>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{ ('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" onclick="bulk_delete_modal()"> {{ ('Delete selection')}}</a>
                </div>
            </div>
            <div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="group" name="group" onchange="apply_filter()" data-live-search="true">
                    <option value="">{{ ('All Groups') }}</option>
                    @foreach (App\Models\MetaObject::all() as $object)
                        <option class="{{ $object->is_active ? '' : 'text-muted' }}" value="{{ $object->name }}" @if ($object->id == $group_id) selected @endif>{{ $object->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" onkeyup="apply_filter()" class="form-control form-control-sm" id="search" name="search" value="{{ @$search }}" placeholder="{{ ('Type & Enter') }}">
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
                        <th data-breakpoints="sm">{{ ('Title')}}</th>
                        <th data-breakpoints="sm">{{ ('Group')}}</th>
                        <th data-breakpoints="lg">{{ ('Status')}}</th>
                        <th data-breakpoints="sm" class="text-right">{{ ('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(@$items ?? [] as $key => $item)
                    <tr>
                        <td>
                            <div class="form-group d-inline-block">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" class="check-one" name="id[]" value="{{$item->id}}">
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="row gutters-5 w-200px w-md-300px mw-100">
                                <div class="col">
                                    <span class="text-muted text-truncate-2">{{ ($item->title) }} </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="row gutters-5 w-200px w-md-300px mw-100">
                                <div class="col">
                                    <span class="text-muted text-truncate-2"><b>{{ ($item->metaObject->name) }}</b></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_status(this)" value="{{ $item->id }}" type="checkbox" @if ($item->is_active) checked @endif >
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm edit-btn" title="{{ ('Edit') }}" href="{{ route('meta-object-items.edit', $item->id) }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="@if(filled(@$group)) {{ route('meta-object-items.destroy', ['id'=>$item->id,'group' => $group]) }} @else {{ route('meta-object-items.destroy', $item->id) }} @endif" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $items->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
    @include('modals.bulk_delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        function apply_filter(){
            $('#filterForm').submit();
        }
        $('#metaObjectItemForm').submit(function(e) {
            e.preventDefault();
        });
        $('#metaObjectItemCreate').click(function() {
            $('#metaObjectItemModalLabel').text('Create Meta Object');
            $('#metaObjectItemForm').trigger('reset');
            $('#metaObjectItemForm').attr('action', `{{ route('meta-objects.store') }}`);
            $('#metaObjectItemModalButton').text('Save');
            $('#metaObjectItemModal').modal('show');
        });
        $('#theTable').on('click','.edit-btn',function() {
            $('#metaObjectItemForm').trigger('reset');
            $('#metaObjectItemModalLabel').text('Edit Meta Object');
            $('#metaObjectItemForm').attr('action', $(this).data('href'));
            var data = $(this).data('value');
            $('#name').val(data.name);
            $('#status').val(data.is_active);
            $('#metaObjectItemModalButton').text('Update');
            $('#metaObjectItemModal').modal('show');
        });
        $('#metaObjectItemModalButton').on('click', function(){
            var href = $('#metaObjectItemForm').attr('action');
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
                            $('#metaObjectItemModal').modal('hide');
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
                url: `{{ route('meta-object-items.update_status', ':id') }}`.replace(':id', el.value),
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
                url: "{{route('meta-object-items.bulk_destroy')}}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id
                },
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
