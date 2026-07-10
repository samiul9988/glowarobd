@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{ ('All Notification')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('user-notification.create') }}" class="btn btn-circle btn-info">
				<span>{{ ('Send New Notification')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ ('Notification')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>{{ ('Image')}}</th>
                    <th>{{ ('Date & Time')}}</th>
                    <th>{{ ('Type')}}</th>
                    <th>{{ ('Title')}}</th>
                    <th data-breakpoints="lg">{{ ('Message')}}</th>
                    {{-- <th width="10%">{{ ('Options')}}</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach($notifications as $key => $notification)
                        <tr>
                            <td>{{ ($key+1) + ($notifications->currentPage() - 1)*$notifications->perPage() }}</td>
                            <td>
                                @if($notification->image != null)
                                    <img src="{{ uploaded_asset($notification->image) }}" alt="{{ ('Image')}}" class="h-50px">
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{date('F d, Y h:i A',strtotime($notification->created_at))}}</td>
                            <td>{{ucfirst($notification->type)}}</td>
                            <td>{{$notification->title}}</td>
                            <td>{{$notification->message}}</td>

                            {{-- <td class="text-right">
		                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('staffs.edit', encrypt($staff->id))}}" title="{{ ('Edit') }}">
		                                <i class="las la-edit"></i>
		                            </a>
		                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('staffs.destroy', $staff->id)}}" title="{{ ('Delete') }}">
		                                <i class="las la-trash"></i>
		                            </a>
		                        </td> --}}
                        </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $notifications->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
