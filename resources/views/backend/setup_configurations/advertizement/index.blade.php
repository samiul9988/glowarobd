@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="align-items-center">
		<div class="text-md-right">
			<a href="{{route('ads.create')}}" class="btn btn-circle btn-info">
				<span>Add New Advertisement</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ ('Advertisement')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>{{ ('Image')}}</th>
                    <th>{{ ('Ads Type')}}</th>
                    <th>{{ ('Link Type')}}</th>
                    <th>{{ ('Position')}}</th>
                    <th data-breakpoints="lg">{{ ('Status')}}</th>
                    <th data-breakpoints="lg">{{ ('Start Date')}}</th>
                    <th data-breakpoints="lg">{{ ('End Date')}}</th>
                    <th class="text-right" width="15%">{{ ('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($advertizements as $advertizement)
                    <tr>
                        <td>{{$loop->index + 1}}</td>
                        <td>
                            @if($advertizement->image != null)
                                <img src="{{ uploaded_asset($advertizement->image) }}" alt="{{ ('Image')}}" class="h-50px">
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ucfirst($advertizement->ads_type)}}</td>
                        <td>{{ucfirst($advertizement->link_type)}}</td>
                        <td>{{ucwords(str_replace('_', ' ', $advertizement->position))}}</td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                            <input onchange="update_status(this)" value="{{$advertizement->id}}" type="checkbox" {{$advertizement->status == 1 ? 'checked' : ''}}>
                            <span class="slider round"></span></label>
                        </td>
                        <td>{{date("d-m-Y",strtotime($advertizement->start_date))}}</td>
                        <td>{{date("d-m-Y",strtotime($advertizement->end_date))}}</td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('ads.edit', $advertizement->id)}}" title="{{ ('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                             <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('ads.destroy', $advertizement->id)}}" title="{{ ('Delete') }}">
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
        function update_status(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('ads.update_status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    // location.reload();
                    AIZ.plugins.notify('success', '{{ ('Advertizement status updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }
    </script>
@endsection
