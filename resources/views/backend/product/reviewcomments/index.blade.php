@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="align-items-center">
		<h1 class="h3">{{ ('All Review Comments')}}</h1>
	</div>
</div>

<div class="row">
	<div class="col-md-7">
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0 h6">{{ ('All Review Comments')}}</h5>
			</div>
			<div class="card-body">
				<table class="table aiz-table mb-0">
					<thead>
						<tr>
							<th>#</th>
							<th>{{ ('Name')}}</th>
							<th class="text-right">{{ ('Options')}}</th>
						</tr>
					</thead>
					<tbody>
						@foreach($reviewcomments as $key => $reviewcomment)
							<tr>
								<td>{{$key+1}}</td>
								<td>{{$reviewcomment->title}}</td>
								<td class="text-right">
									<a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('reviewcomments.edit', ['id'=>$reviewcomment->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ ('Edit') }}">
										<i class="las la-edit"></i>
									</a>
									<a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('reviewcomments.destroy', $reviewcomment->id)}}" title="{{ ('Delete') }}">
										<i class="las la-trash"></i>
									</a>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="col-md-5">
		<div class="card">
			<div class="card-header">
					<h5 class="mb-0 h6">{{ ('Add New Review Comments') }}</h5>
			</div>
			<div class="card-body">
				<form action="{{ route('reviewcomments.store') }}" method="POST">
					@csrf
					<div class="form-group mb-3">
						<label for="name">{{ ('Title')}}</label>
						<input type="text" placeholder="{{ ('Title')}}" id="title" name="title" class="form-control" required>
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
