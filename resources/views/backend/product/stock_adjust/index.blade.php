@extends('backend.layouts.app')

@section('content')

<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('All Stock Adjustments') }}</h5>
            </div>

            {{--<div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{ ('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" onclick="bulk_delete()"> {{ ('Delete selection')}}</a>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#exampleModal">
                        <i class="las la-sync-alt"></i>
                        {{ ('Change Order Status')}}
                    </a>
                </div>
            </div>--}}


            {{--<div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="user_id" name="user_id" onchange="sort_products()">
                    <option value="">{{ ('All Sellers') }}</option>
                        @foreach (App\Models\User::where('user_type', '=', 'admin')->orWhere('user_type', '=', 'seller')->get() as $key => $seller)
                        <option value="{{ $seller->id }}"  @if ($seller->id == $seller_id) selected @endif>{{ $seller->name }}</option>
                        @endforeach
                </select>
            </div>--}}

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ ('Type Order code & keyword') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>

        <div class="card-body">

            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th data-breakpoints="md">{{ ('Date') }}</th>
                        <th>{{ ('Stock Adjust Number') }}</th>
                        <th class="text-center">Type/Reason</th>
                        <th data-breakpoints="md">{{ ('Seller') }}</th>
                        <th data-breakpoints="md">{{ ('Num. of Products') }}</th>
                        {{-- <th data-breakpoints="md">{{ ('Delivery Status') }}</th> --}}
                        {{-- <th data-breakpoints="md">{{ ('Payment Method')}}</th>
                        <th data-breakpoints="md">{{ ('Payment Status') }}</th> --}}

                        <th class="text-right" width="15%">{{ ('options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stockadjustments as $key => $stockadjustment)
                    <tr>
                        <td>{{ date('d-m-Y', $stockadjustment->sa_date )}}</td>
                        <td>{{ $stockadjustment->sa_number }}</td>
                        <td class="text-capitalize text-center">
                            {{ $stockadjustment->sa_type }}
                            @if (Str::contains(strtolower($stockadjustment->note), 'returned to supplier'))
                                <span class="d-block font-weight-bold fs-10">** Returned to Supplier **</span>
                            @endif
                        </td>
                        <td>{{@$stockadjustment->sellername->name}}</td>
                        <td class="text-center">{{ count($stockadjustment->stockAdjustDetails) }}</td>

                        <td class="text-right">
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{route('stock-adjust.show', encrypt($stockadjustment->id))}}" title="{{ ('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                            {{-- <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('stock-adjust.edit', ['id'=>$stockadjustment->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ ('Edit') }}">
                                <i class="las la-edit"></i>
                            </a> --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $stockadjustments->appends(request()->input())->links() }}
            </div>

        </div>
    </form>
</div>

@endsection
