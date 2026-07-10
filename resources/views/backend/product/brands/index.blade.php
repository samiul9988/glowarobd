@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="align-items-center">
			<h1 class="h3">{{ ('All Brands')}}</h1>
	</div>
</div>

<div class="row">
	<div class="col-md-7">
		<div class="card">
		    <div class="card-header row gutters-5">
				<div class="col text-center text-md-left">
					<h5 class="mb-md-0 h6">{{ ('Brands') }}</h5>
				</div>
				<div class="col-md-4">
					<form class="" id="sort_brands" action="" method="GET">
						<div class="input-group input-group-sm">
					  		<input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ ('Type name & Enter') }}">
						</div>
					</form>
				</div>
		    </div>
		    <div class="card-body">
                <table class="table aiz-table mb-0" >
                    <thead>
                        <tr>
                            <th data-breakpoints="lg">#</th>
                            <th>Name</th>
                            <th data-breakpoints="lg">Discount Date</th>
                            <th data-breakpoints="lg">Discount Status</th>
                            <th data-breakpoints="lg">Top</th>
                            <th class="text-right">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brands as $key => $brand)
                            <tr>
                                <td>{{ ($key+1) + ($brands->currentPage() - 1)*$brands->perPage() }}</td>
                                <td>
                                    <div class="row gutters-5 w-200px w-md-300px mw-100">
                                        <div class="col-auto">
                                            <img src="{{ uploaded_asset($brand->logo) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';" alt="Image" class="size-50px img-fit">
                                        </div>
                                        <div class="col ml-2">
                                            <span class="text-muted text-truncate-2">{{ $brand->name }} </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-block">
                                        Start: {{ $brand->start_date!=0?date('d-m-Y H:i:s', $brand->start_date):'0000-00-00 00:00' }}
                                    </span>
                                    <span class="d-block">
                                        End: {{ $brand->end_date!=0?date('d-m-Y H:i:s', $brand->end_date):'0000-00-00 00:00' }}
                                    </span>
                                </td>
                                <td>
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input onchange="update_brand_status(this)" value="{{ $brand->id }}" type="checkbox" <?php if($brand->status == 1) echo "checked";?> >
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input onchange="update_brand_top_status(this)" value="{{ $brand->id }}" type="checkbox" <?php if($brand->top == 1) echo "checked";?> >
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="text-right">
		                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('brands.edit', ['id'=>$brand->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ ('Edit') }}">
		                                <i class="las la-edit"></i>
		                            </a>
		                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('brands.destroy', $brand->id)}}" title="{{ ('Delete') }}">
		                                <i class="las la-trash"></i>
		                            </a>
		                        </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
		        <div class="aiz-pagination">
                	{{ $brands->appends(request()->input())->links() }}
            	</div>
		    </div>
		</div>
	</div>
	<div class="col-md-5">
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0 h6">{{ ('Add New Brand') }}</h5>
			</div>
			<div class="card-body">
				<form action="{{ route('brands.store') }}" method="POST">
					@csrf
					<div class="form-group mb-3">
						<label for="name">{{ ('Name')}}</label>
						<input type="text" placeholder="{{ ('Name')}}" name="name" class="form-control" required>
					</div>

                    @php
                    /*
                      $start_date = date('d-m-Y H:i:s');
                      $end_date = date('d-m-Y H:i:s');
                    @endphp

                    <div class="form-group mb-3">
                        <label class="" for="start_date">{{ ('Discount Date Range')}}</label>
                          <input type="text" class="form-control aiz-date-range" value="{{ $start_date.' to '.$end_date }}" name="date_range" placeholder="Select Date" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="" for="discount">{{ ('Discount')}} </label>
                            <input type="number" lang="en" name="discount" value="{{ $brand->discount }}" min="0" step="1" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="" for="discount_type">{{ ('Discount Type')}} </label><br>
                            <select class="aiz-selectpicker" name="discount_type" class="form-control">
                                <option value="amount" <?php if($brand->discount_type == 'amount') echo "selected";?> >{{ ('Flat') }}</option>
                                <option value="percent" <?php if($brand->discount_type == 'percent') echo "selected";?> >{{ ('Percent') }}</option>
                            </select>
                    </div>
                    @php
                        */
                    @endphp
					<div class="form-group mb-3">
						<label for="name">{{ ('Logo')}} <small>({{ ('120x80') }})</small></label>
						<div class="input-group" data-toggle="aizuploader" data-type="image">
							<div class="input-group-prepend">
									<div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
							</div>
							<div class="form-control file-amount">{{ ('Choose File') }}</div>
							<input type="hidden" name="logo" class="selected-files">
						</div>
						<div class="file-preview box sm">
						</div>
					</div>
					<div class="form-group">
                        <label for="signinSrEmail">{{ ('Page Banner')}} <small>({{ ('1920 × 130 px') }})</small></label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{ ('Choose File') }}</div>
                            <input type="hidden" name="page_banner" class="selected-files">
                        </div>
                        <div class="file-preview box sm">
                        </div>
                    </div>
					<div class="form-group mb-3">
						<label for="meta_title">{{ ('Meta Title')}}</label>
						<input type="text" class="form-control" name="meta_title" placeholder="{{ ('Meta Title')}}">
					</div>
					<div class="form-group mb-3">
						<label for="meta_description">{{ ('Meta Description')}}</label>
						<textarea name="meta_description" rows="5" class="form-control"></textarea>
					</div>
					<div class="form-group mb-3">
						<label for="status">{{ ('Status')}}</label>
						<select name="status" id="status" class="form-control">
							<option value="1">{{ ('Active')}}</option>
							<option value="0">{{ ('Inactive')}}</option>
						</select>
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
    function update_brand_status(el){
        if(el.checked){
            var status = 1;
            var alertmsg = `{{ ('If any product has discount or exists in flash deal, the discount will be replaced by this Brand discount & time limit.') }}`;
        }
        else{
            var status = 0;
            var alertmsg = `{{ ('If any product has discount or exists in flash deal, the discount will be removed.') }}`;
        }
        if(confirm(alertmsg)){
            $.ajax({
                'url': '{{ route('brands.update_status') }}',
                'type': 'POST',
                'headers': {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                'data': {
                    id: el.value,
                    status: status
                },
                'success': function(data) {
                    if(data == 1) {
                        AIZ.plugins.notify('success', 'Brand status updated successfully');
                    } else {
                        el.checked = !el.checked;
                        AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                    }
                },
                'error': function() {
                    el.checked = !el.checked;
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }
    }

    function update_brand_top_status(el) {
        var status = el.checked ? 1 : 0;

        $.ajax({
            'url': '{{ route('brands.update_status') }}',
            'type': 'POST',
            'headers': {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            'data': {
                id: el.value,
                top: status
            },
            'success': function(data) {
                if(data == 1) {
                    AIZ.plugins.notify('success', 'Brand top status updated successfully');
                } else {
                    el.checked = !el.checked;
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            },
            'error': function() {
                el.checked = !el.checked;
                AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
            }
        });
    }

    function sort_brands(el){
        $('#sort_brands').submit();
    }
</script>
@endsection
