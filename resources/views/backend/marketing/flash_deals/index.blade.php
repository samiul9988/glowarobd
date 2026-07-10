@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">Flash Deals</h5>
        <div class="pull-right clearfix d-flex align-items-center">
            <form class="d-flex align-items-center" id="sort_flash_deals" action="" method="GET">
                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control form-control-sm" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="Type name & Enter">
                    </div>
                </div>
                <button class="btn btn-sm btn-primary ml-3" type="submit">
                    <i class="las la-search aiz-side-nav-icon"></i> Search
                </button>
            </form>
            <div class="ml-4">
                <button onclick="window.location='{{ route('flash_deals.create') }}'" class="btn btn-sm btn-soft-success">
                    <i class="las la-plus"></i> Create New
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0" >
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>Title</th>
                    <th>Date Range</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" data-breakpoints="lg">
                        Featured <span class="text-primary">(Web)</span>
                    </th>
                    <th class="text-center" data-breakpoints="lg">
                        Featured <span class="text-info">(App)</span>
                    </th>
                    <th class="text-center">Options</th>
                </tr>
            </thead>
            <tbody>
                @foreach($flash_deals as $key => $flash_deal)
                    <tr>
                        <td>{{ ($key+1) + ($flash_deals->currentPage() - 1)*$flash_deals->perPage() }}</td>
                        <td>
                            <span class="d-block font-weight-bold">
                                {{ $flash_deal->title }}
                            </span>
                            <span class="d-block font-weight-bold text-muted fs-10">
                                Sync with {{ $flash_deal->flash_deal_products_count ?? 0 }} products
                            </span>
                        </td>
                        <td class="font-weight-bold">
                            <span class="d-block">
                                Start: {{ date('d-m-Y H:i:s', $flash_deal->start_date) }}
                            </span>
                            <span class="d-block {{ \Carbon\Carbon::parse($flash_deal->end_date)->isPast() ? 'text-danger' : '' }}">
                                End: {{ date('d-m-Y H:i:s', $flash_deal->end_date) }}
                            </span>
                        </td>
                        <td class="text-center">
							<label class="aiz-switch aiz-switch-success mb-0">
								<input onchange="update_flash_deal_status(this)" value="{{ $flash_deal->id }}" type="checkbox" {{ $flash_deal->status == 1 ? 'checked' : '' }} >
								<span class="slider round"></span>
							</label>
						</td>
						<td class="text-center">
							<label class="aiz-switch aiz-switch-success mb-0">
								<input onchange="update_flash_deal_feature(this)" data-type="web" value="{{ $flash_deal->id }}" type="checkbox" {{ $flash_deal->featured == 1 ? 'checked' : '' }} >
								<span class="slider round"></span>
							</label>
						</td>
						<td class="text-center">
							<label class="aiz-switch aiz-switch-success mb-0">
								<input onchange="update_flash_deal_feature(this)" data-type="app" value="{{ $flash_deal->id }}" type="checkbox" {{ $flash_deal->app_featured == 1 ? 'checked' : '' }} >
								<span class="slider round"></span>
							</label>
						</td>
						<td class="text-center">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ to_frontend(url('flash-deal/'.$flash_deal->slug), 'flash-deals') }}" title="View" target="_blank">
                                <i class="las la-eye"></i>
                            </a>
                            <a class="btn btn-soft-success btn-icon btn-circle btn-sm" href="{{route('flash_deals.edit', ['id'=>$flash_deal->id] )}}" title="Edit">
                                <i class="las la-edit"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('flash_deals.destroy', $flash_deal->id)}}" title="Delete">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="clearfix">
            <div class="pull-right">
                {{ $flash_deals->appends(request()->input())->links() }}
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
        function update_flash_deal_status(el){

        if(el.checked){
            var status = 1;
            var alertmsg = `{{ ('If any product has discount or exists in flash deal, the discount will be replaced by this discount & time limit.') }}`;
        }else{
            var status = 0;
            var alertmsg = `{{ ('If any product has discount or exists in flash deal, the discount will be removed.') }}`;
        }
        if(confirm(alertmsg)){
            $.post('{{ route('flash_deals.update_status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    location.reload();
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }
        }
        function update_flash_deal_feature(el){
            if(el.checked){
                var featured = 1;
            }
            else{
                var featured = 0;
            }
            let featureType = el.getAttribute('data-type');

            $.post('{{ route('flash_deals.update_featured') }}', {
                _token:'{{ csrf_token() }}',
                id:el.value,
                featured:featured,
                type: featureType
            }, function(data){
                if(data == 1){
                    location.reload();
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }
    </script>
@endsection
