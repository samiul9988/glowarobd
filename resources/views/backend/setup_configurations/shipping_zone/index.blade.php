@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{ ('All Shipping Zone')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('shipping_zone.create') }}" class="btn btn-circle btn-info">
				<span>{{ ('Create New Shipping Zone')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ ('Shipping Zones')}}</h5>
        <div class="pull-right clearfix">
            <form class="" id="sort_flash_deals" action="" method="GET">
                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ ('Type name & Enter') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0" >
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>{{ ('Title')}}</th>
                    <th class="text-right">{{ ('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shippingZones as $key => $shipping_zone)
                    <tr>
                        <td>{{ ($key+1) + ($shippingZones->currentPage() - 1)*$shippingZones->perPage() }}</td>
                        <td>{{ $shipping_zone->title }}</td>


						<td class="text-right">
                            <span class="btn btn-soft-primary btn-icon btn-circle btn-sm manage-rates" data-href="{{route('shipping_zone.rates', $shipping_zone->id)}}" title="{{ ('Manage Rate') }}">
                                <i class="las la-cogs"></i>
                            </span>

                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('shipping_zone.edit', ['id'=>$shipping_zone->id] )}}" title="{{ ('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>

                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('shipping_zone.destroy', $shipping_zone->id)}}" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="clearfix">
            <div class="pull-right">
                {{ $shippingZones->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')


    <div id="rates-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ ('Manage Rates')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center" id="rates-modal-body">

                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function(){

        $(document).on("click", ".manage-rates", function(e){
            e.preventDefault();
            var getrateurl = $(this).attr('data-href');
            $.get(getrateurl, function(data){
                $("#rates-modal-body").html(data);
                $('#rates-modal').modal('show', {backdrop: 'static'});
            });
        });

        $(document).on("submit", "#submit_rates", function(e){
            e.preventDefault();
            $.post($(this).attr('action'), $(this).serialize() , function(data){
                if(data == 1){
                    location.reload();
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        });

    });
</script>
@endsection
