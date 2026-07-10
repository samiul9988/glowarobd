@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ ('Customer Group')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('customer.group.create') }}" class="btn  btn-info">
                <span>{{ ('Add New Group')}}</span>
            </a>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header">

    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="">{{ ('#')}}</th>
                    <th>{{ ('Image')}}</th>
                    <th data-breakpoints="">{{ ('Group Name')}}</th>
                    <th data-breakpoints="sm">{{ ('Min Order Qty')}}</th>
                    <th data-breakpoints="sm">{{ ('Min Order Amount')}}</th>
                    <th data-breakpoints="">{{ ('Discount Status') }}</th>
                    <th data-breakpoints="">{{ ('Delivery Discount Status') }}</th>
                    <th data-breakpoints="">{{ ('Ordering') }}</th>
                    <th data-breakpoints="" class="">{{ ('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $key => $group)
                    <tr>
                        <td>{{$groups->firstItem() + $key}}</td>
                        <td><img width="50" src="{{ !empty($group->image->file_name) ? my_asset($group->image->file_name) : uploaded_asset($group->group_image)}}" alt=""></td>
                        <td>{{$group->group_name}}</td>
                        <td>{{$group->min_order_qty}}</td>
                        <td>{{single_price($group->min_order_amount)}}</td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_brand_status(this)" value="{{ $group->id }}" type="checkbox" <?php if($group->discount_status == 1) echo "checked";?> >
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_delivery_discount_status(this)" value="{{ $group->id }}" type="checkbox" <?php if($group->delivery_discount == 1) echo "checked";?> >
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>{{$group->ordering}}</td>
                        <td>
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('customer.group.edit', encrypt($group->id) )}}" title="{{ ('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('customer.group.delete', encrypt($group->id) )}}" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination mt-3">
                {{ $groups->appends(request()->input())->links() }}
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
function update_brand_status(el){
    var alertmsg = `{{ ('Are you sure you want to change this discount status?') }}`;
    if(el.checked){
        var status = 1;
    }
    else{
        var status = 0;
    }
    if(confirm(alertmsg)){
            $.post('{{ route('customer.group.update_status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    location.reload();
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }else{
            location.reload();
        }
        }
function update_delivery_discount_status(el){
    var alertmsg = `{{ ('Are you sure you want to change this delivery discount status?') }}`;
    if(el.checked){
        var status = 1;
    }
    else{
        var status = 0;
    }
    if(confirm(alertmsg)){
            $.post('{{ route('customer.group.update_delivery_discount_status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    location.reload();
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }else{
            location.reload();
        }
        }
    function sort_brands(el){
        $('#sort_brands').submit();
    }
</script>
@endsection
