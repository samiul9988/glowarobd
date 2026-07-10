@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="align-items-center">
			<h1 class="h3">{{ ('All SMS User')}}</h1>
	</div>
</div>

<div class="row">
	<div class="col-md-7">
		<div class="card">
		    <div class="card-header row gutters-5">
				<div class="col text-center text-md-left">
					<h5 class="mb-md-0 h6">{{ ('All SMS User') }}</h5>
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
                            <th>{{ ('Mobile Number')}}</th>
                            <th class="text-right">{{ ('Options')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $key => $user)
                            <tr>
                                <td>{{ ($key+1) + ($users->currentPage() - 1)*$users->perPage() }}</td>
                                <td>{{ $user->mobile_number }}</td>

                                <td class="text-right">
		                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('sms_user.edit', ['id'=>$user->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ ('Edit') }}">
		                                <i class="las la-edit"></i>
		                            </a>
		                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('sms_user.destroy', $user->id)}}" title="{{ ('Delete') }}">
		                                <i class="las la-trash"></i>
		                            </a>
		                        </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
		        <div class="aiz-pagination">
                	{{ $users->appends(request()->input())->links() }}
            	</div>
		    </div>
		</div>
	</div>
	<div class="col-md-5">
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0 h6">{{ ('Add New SMS User') }}</h5>
			</div>
			<div class="card-body">
				<form action="{{ route('sms_user.store') }}" method="POST">
					@csrf
					<div class="form-group mb-3">
						<label for="name">{{ ('Mobile Number')}}</label>
						{{-- <input type="text" placeholder="{{ ('Mobile Number')}}" name="mobile_number" class="form-control" required> --}}
                        <textarea placeholder="{{ ('Mobile Number')}}" name="mobile_number" class="form-control" required></textarea>
                        <small class="form-text text-danger">**N.B : For multiple entry press enter after typing mobile number .**</small>
					</div>


					{{-- <div class="form-group mb-3">
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
					</div> --}}

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
            $.post('{{ route('brands.update_status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    location.reload();
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }
        }
    function sort_brands(el){
        $('#sort_brands').submit();
    }
</script>
@endsection
