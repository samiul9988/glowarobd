@extends(config('app.theme').'frontend.layouts.user_panel')
@section('panel_content')

    <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">{{ ('Coupons') }}</h1>
            </div>
        </div>
    </div>

    <div class="row gutters-10 justify-content-center">
        <div class="col-md-4 mx-auto mb-3" >
            <a href="{{ route('seller.coupon.create')}}">
            <div class="p-3 rounded mb-3 c-pointer text-center bg-white shadow-sm hov-shadow-lg has-transition">
                <span class="size-60px rounded-circle mx-auto bg-secondary d-flex align-items-center justify-content-center mb-3">
                    <i class="las la-plus la-3x text-white"></i>
                </span>
                <div class="fs-18 text-primary">{{ ('Add New Coupon') }}</div>
            </div>
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('All Coupons') }}</h5>
            </div>
        </div>
        <div class="card-body">
            <table class="table aiz-table p-0">
                <thead>
                    <tr>
                        <th data-breakpoints="lg">#</th>
                        <th>{{ ('Code')}}</th>
                        <th data-breakpoints="lg">{{ ('Type')}}</th>
                        <th data-breakpoints="lg">{{ ('Start Date')}}</th>
                        <th data-breakpoints="lg">{{ ('End Date')}}</th>
                        <th width="10%">{{ ('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coupons as $key => $coupon)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$coupon->code}}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $coupon->type)) }}</td>
                            <td>{{ date('d-m-Y', $coupon->start_date) }}</td>
                            <td>{{ date('d-m-Y', $coupon->end_date) }}</td>
                            <td class="text-right">
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('seller.coupon.edit', encrypt($coupon->id) )}}" title="{{ ('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('seller.coupon.destroy', $coupon->id)}}" title="{{ ('Delete') }}">
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
