@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{ ('All Merchants')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('merchants.create') }}" class="btn btn-sm btn-soft-success">
				<span>{{ ('Add New Merchant')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ ('Merchants')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg" width="10%">#</th>
                    <th>{{ ('Name')}}</th>
                    <th data-breakpoints="lg">{{ ('Email')}}</th>
                    <th data-breakpoints="lg">{{ ('Phone')}}</th>
                    <th data-breakpoints="lg">{{ ('App-ID')}}</th>
                    <th class="text-center">{{ ('Options')}}</th>
                </tr>
            </thead>
            <tbody id="tbody">
                @foreach($merchants as $key => $merchant)
                    @php
                        $info = '*** Merchant Information ***' . "\n";
                        $info .= 'Name: ' . $merchant->name . "\n";
                        $info .= 'Email: ' . $merchant->email . "\n";
                        $info .= 'App-ID: ' . $merchant->app_id . "\n";
                        $info .= 'Secret-Key: ' . $merchant->app_key . "\n";
                    @endphp
                    <tr>
                        <td>{{ ($key+1) + ($merchants->currentPage() - 1)*$merchants->perPage() }}</td>
                        <td>{{$merchant->name ?? 'N/A'}}</td>
                        <td>{{$merchant->email ?? 'N/A'}}</td>
                        <td>{{$merchant->phone ?? 'N/A'}}</td>
                        <td>{{ is_null($merchant->app_id) ? 'N/A' : Str::mask($merchant->app_id, '*', 5, 5) }}</td>
                        <td class="text-center">
                                <button class="btn btn-soft-success btn-icon btn-circle btn-sm copy-info" data-info="{{ $info }}" title="{{ ('Copy Secrets') }}">
                                    <i class="las la-copy"></i>
                                </button>
                                {{-- <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{route('merchants.show', $merchant->id)}}" title="{{ ('View') }}">
                                    <i class="las la-binoculars"></i>
                                </a> --}}
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('merchants.edit', $merchant->id)}}" title="{{ ('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('merchants.destroy', $merchant->id)}}" title="{{ ('Delete') }}">
                                    <i class="las la-trash"></i>
                                </a>
                            </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $merchants->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('#tbody').on('click', '.copy-info', function() {
                const info = $(this).data('info');
                navigator.clipboard.writeText(info).then(() => {
                    AIZ.plugins.notify('success', 'Copied to clipboard');
                }).catch(error => {
                    console.error("Failed to copy to clipboard", error);
                    AIZ.plugins.notify('danger', 'Failed to copy to clipboard');
                });
            });
        });
    </script>
@endsection
