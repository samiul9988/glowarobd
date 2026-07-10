@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
    	<div class="row align-items-center">
    		<div class="col-md-12">
    			<h1 class="h3">{{ ('Shipping Method')}}</h1>
    		</div>
    	</div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <?php /*
                <form class="" id="sort_area" action="" method="GET">
                    <div class="card-header row gutters-5">
                        <div class="col text-center text-md-left">
                            <h5 class="mb-md-0 h6">{{ ('Shipping Method') }}</h5>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="sort_area" name="sort_area" @isset($sort_area) value="{{ $sort_area }}" @endisset placeholder="{{ ('Type area name & Enter') }}">
                        </div>
                        <div class="col-md-4">
                            <select class="form-control aiz-selectpicker" data-live-search="true" id="sort_city" name="sort_city">
                                <option value="">{{ ('Select City') }}</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}" @if ($sort_city == $city->id) selected @endif {{$sort_city}}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-primary" type="submit">{{ ('Filter') }}</button>
                        </div>
                    </div>
                </form>
                */ ?>

                <div class="card-body">
                    <table class="table aiz-table mb-0">
                        <thead>
                            <tr>
                                <th data-breakpoints="lg">#</th>
                                <th>{{ ('Logo')}}</th>
                                <th>{{ ('Name')}}</th>
                                <th>{{ ('Show/Hide')}}</th>
                                <th data-breakpoints="lg" class="text-right">{{ ('Options')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shippingMethods as $key => $shippingMethod)
                                <tr>
                                    <td>{{ ($key+1) + ($shippingMethods->currentPage() - 1)*$shippingMethods->perPage() }}</td>
                                    <td><img src="@if(!empty($shippingMethod->logo) && is_object($shippingMethod->logo)) {{ my_asset($shippingMethod->logo->file_name) }} @else {{ uploaded_asset($shippingMethod->logo) }} @endif" alt="Image" class="size-50px img-fit"></td>
                                    <td>{{ $shippingMethod->name }}</td>
                                    <td>
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                          <input onchange="update_status(this)" value="{{ $shippingMethod->id }}" type="checkbox" <?php if($shippingMethod->status == 1) echo "checked";?> >
                                          <span class="slider round"></span>
                                        </label>
                                      </td>
                                    <td class="text-right">
                                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('shipping_method.edit', ['id'=>$shippingMethod->id]) }}" title="{{ ('Edit') }}">
                                            <i class="las la-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('shipping_method.destroy', $shippingMethod->id)}}" title="{{ ('Delete') }}">
                                            <i class="las la-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="aiz-pagination">
                        {{ $shippingMethods->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
    		<div class="card">
    			<div class="card-header">
    				<h5 class="mb-0 h6">{{ ('Add New Shipping Method') }}</h5>
    			</div>
    			<div class="card-body">
    				<form action="{{ route('shipping_method.store') }}" method="POST">
    					@csrf
    					<div class="form-group mb-3">
    						<label for="name">{{ ('Name')}}</label>
    						<input type="text" placeholder="{{ ('Name')}}" name="name" class="form-control" required>
    					</div>

                        <div class="form-group mb-3">
                            <label class="col-12 col-form-label px-0" for="signinSrEmail">{{ ('Logo')}} <small>(300x300)</small></label>
                            <div class="col-12 px-0">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="logo" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                                <small class="text-muted">{{ ('This image is visible in all product box. Use 300x300 sizes image. Keep some blank space around main object of your image as we had to crop some edge in different devices to make it responsive.')}}</small>
                            </div>
                        </div>

    					<div class="form-group mb-3 text-right">
    						<button type="submit" class="btn btn-primary">{{ ('Save')}}</button>
    					</div>
    				</form>
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


        function update_status(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('shipping_method.status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ ('Shipping method status updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }

    </script>
@endsection
