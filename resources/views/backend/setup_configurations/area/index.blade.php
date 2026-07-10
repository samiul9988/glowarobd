@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
    	<div class="row align-items-center">
    		<div class="col-md-12">
    			<h1 class="h3">{{ ('All Areas')}}</h1>
    		</div>
    	</div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <form class="" id="sort_area" action="" method="GET">
                    <div class="card-header row gutters-5">
                        <div class="col text-center text-md-left">
                            <h5 class="mb-md-0 h6">{{ ('Area') }}</h5>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="{{ ('Type area name & Enter') }}">
                        </div>
                        <div class="col-md-4">
                            <select class="form-control aiz-selectpicker" data-live-search="true" id="sort_city" name="city">
                                <option value="">{{ ('Select City') }}</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}" @if (request('city') == $city->id) selected @endif>
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
                <div class="card-body">
                    <table class="table aiz-table mb-0">
                        <thead>
                            <tr>
                                <th data-breakpoints="lg">#</th>
                                <th>{{ ('Name')}}</th>
                                <th>{{ ('City')}}</th>
                                <th>{{ ('Area Wise Shipping Cost')}}</th>
                                <th>{{ ('Show/Hide')}}</th>
                                <th data-breakpoints="lg" class="text-right">{{ ('Options')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($areas as $key => $area)
                                <tr>
                                    <td>{{ ($key+1) + ($areas->currentPage() - 1)*$areas->perPage() }}</td>
                                    <td>{{ $area->name }}</td>
                                    <td>{{ $area->city->name }}</td>
                                    <td>{{ single_price($area->cost) }}</td>
                                    <td>
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                          <input onchange="update_status(this)" value="{{ $area->id }}" type="checkbox" <?php if($area->status == 1) echo "checked";?> >
                                          <span class="slider round"></span>
                                        </label>
                                      </td>
                                    <td class="text-right">
                                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('areas.edit', ['id'=>$area->id, 'lang'=>env('DEFAULT_LANGUAGE')]) }}" title="{{ ('Edit') }}">
                                            <i class="las la-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('areas.destroy', $area->id)}}" title="{{ ('Delete') }}">
                                            <i class="las la-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="aiz-pagination">
                        {{ $areas->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
    		<div class="card">
    			<div class="card-header">
    				<h5 class="mb-0 h6">{{ ('Add New area') }}</h5>
    			</div>
    			<div class="card-body">
    				<form action="{{ route('areas.store') }}" method="POST">
    					@csrf
    					<div class="form-group mb-3">
    						<label for="name">{{ ('Name')}}</label>
    						<input type="text" placeholder="{{ ('Name')}}" name="name" class="form-control" required>
    					</div>

                        <div class="form-group">
                            <label for="country">{{ ('City')}}</label>
                            <select class="select2 form-control aiz-selectpicker" name="city_id" data-toggle="select2" data-placeholder="Choose ..." data-live-search="true">
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
    						<label for="name">{{ ('Cost')}}</label>
    						<input type="number" min="0" step="0.01" placeholder="{{ ('Cost')}}" name="cost" class="form-control" required>
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
        function sort_area(el){
            $('#sort_area').submit();
        }

        function update_status(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('areas.status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ ('Area status updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }

    </script>
@endsection
