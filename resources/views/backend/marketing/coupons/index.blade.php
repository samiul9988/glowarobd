@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{ ('All Coupons')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('coupon.create') }}" class="btn btn-circle btn-info">
				<span>{{ ('Add New Coupon')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
  <div class="card-header">
      <h5 class="mb-0 h6">{{ ('Coupon Information')}}</h5>
  </div>
  <div class="card-body">
      <table class="table aiz-table p-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>Code</th>
                    <th>Discount</th>
                    <th class="text-center">Total Usage</th>
                    <th data-breakpoints="lg">Type</th>
                    <th data-breakpoints="lg">Start Date</th>
                    <th data-breakpoints="lg">End Date</th>
                    <th class="text-center">Status</th>
                    <th width="10%" class="text-center">Options</th>
                </tr>
            </thead>
            <tbody>
                @foreach($coupons as $key => $coupon)
                    @php
                        $expired = \Carbon\Carbon::createFromTimestamp($coupon->end_date)->isPast();
                    @endphp
                    <tr @if($expired) title="{{ ('Expired') }}" @endif>
                        <td>{{$key+1}}</td>
                        <td>{{$coupon->code}}</td>
                        <td>
                            @if ($coupon->discount_type == 'percent')
                                {{ $coupon->discount }}% OFF
                            @else
                                {{ single_price($coupon->discount) }} OFF
                            @endif
                        </td>
                        <td class="text-center">{{ $coupon->usage_count }}</td>
                        <td>
                            {{ ucwords(str_replace('_', ' ', $coupon->type)) }}
                        </td>
                        <td>{{ date('d-m-Y', $coupon->start_date) }}</td>
                        <td class="{{ $expired ? 'text-danger font-weight-bold' : '' }}">{{ date('d-m-Y', $coupon->end_date) }}</td>
                        <td class="text-center">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" id="coupon-status-{{ $coupon->id }}" {{ $coupon->status ? 'checked' : '' }} value="{{ $coupon->id }}" class="status-change">
                                <span class="slider round"></span>
                            </label>
                        </td>
						<td class="text-center">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('coupon.edit', encrypt($coupon->id) )}}" title="{{ ('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('coupon.destroy', $coupon->id)}}" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function(){
            $(document).on("change", ".status-change", async function() {
                let id = this.value;
                let status = this.checked ? 1 : 0;

                await touchModel('status', {id: id, status: status});
            });

            async function touchModel(type, data) {
                // Disable the checkbox to prevent multiple clicks
                $(`#coupon-${type}-${data.id}`).prop('disabled', true);
                data['_token'] = '{{ csrf_token() }}';
                await $.ajax({
                    url: `{{ route('coupons.touch') }}`,
                    type: 'PUT',
                    data: data,
                    success: function(response){
                        $(`#coupon-${type}-${data.id}`).prop('disabled', false);
                        if(response.success){
                            AIZ.plugins.notify('success', response.message || 'Request successful');
                        }
                        else{
                            $(`#coupon-${type}-${data.id}`).prop('checked', !$(`#coupon-${type}-${data.id}`).prop('checked'));
                            AIZ.plugins.notify('danger', response.message || 'Something went wrong');
                        }
                    }, error: function(xhr, status, error) {
                        $(`#coupon-${type}-${data.id}`).prop('disabled', false);
                        console.error('Error:', error);
                        $(`#coupon-${type}-${data.id}`).prop('checked', !$(`#coupon-${type}-${data.id}`).prop('checked'));
                        AIZ.plugins.notify('danger', xhr.responseJSON.message || 'Something went wrong');
                    }
                });
            }
        });
    </script>
@endsection
