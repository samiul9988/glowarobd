@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{ ('All Shipping Discounts')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('ship_discounts.create') }}" class="btn btn-circle btn-info">
				<span>{{ ('Add New Discount')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
  <div class="card-header">
      <h5 class="mb-0 h6">{{ ('Discount Information')}}</h5>
  </div>
  <div class="card-body">
      <table class="table aiz-table p-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th data-breakpoints="lg">{{ ('Type')}}</th>
                    <th data-breakpoints="lg">{{ ('Zone')}}</th>
                    <th data-breakpoints="lg">{{ ('Shipping Charge')}}</th>
                    <th data-breakpoints="lg">{{ ('Min Amount')}}</th>
                    <th data-breakpoints="lg">{{ ('Start Date')}}</th>
                    <th data-breakpoints="lg">{{ ('End Date')}}</th>
                    <th data-breakpoints="lg">{{ ('Status')}}</th>
                    <th width="10%">{{ ('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($discounts as $key => $discount)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td>@if ($discount->type == 'brand')
                                {{ ('On Selected Brands') }}
                            @elseif ($discount->type == 'category')
                                {{ ('On Selected Categories') }}
                            @elseif ($discount->type == 'product')
                                {{ ('On Selected Products') }}
                            @else
                                {{ ('On All Products') }}
                        @endif</td>
                        <td>@if ($discount->zone_id == 0) {{ ('All Zones') }} @else {{ $discount->zone->title }} @endif</td>
                        <td>{{$discount->s_charge}}</td>
                        <td>{{$discount->threshold_amount}}</td>
                        <td>{{ date('d-m-Y', $discount->start_date) }}</td>
                        <td>{{ date('d-m-Y', $discount->end_date) }}</td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_status(this)" value="{{ $discount->id }}" type="checkbox" <?php if ($discount->status == 1) echo "checked"; ?> >
                                <span class="slider round"></span>
                            </label>
                        </td>
						<td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('ship_discounts.edit', $discount->id )}}" title="{{ ('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('ship_discount.destroy', $discount->id)}}" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="clearfix">
            <div class="pull-right">
                {{ $discounts->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function(){
            @if (count($errors) > 0)
                @foreach ($errors->all() as $error)
                    AIZ.plugins.notify('danger', "{{ $error }}");
                @endforeach
            @endif
        });
        function update_status(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post("{{ route('ship_discounts.status') }}", {_token:'{{ csrf_token() }}', id: el.value, status: status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', "{{ ('Status updated successfully') }}");
                    location.reload();
                }else{
                    AIZ.plugins.notify('danger', "{{ ('Something went wrong') }}");
                }
            });
        }
    </script>
@endsection
