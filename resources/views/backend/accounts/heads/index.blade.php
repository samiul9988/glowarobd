@extends('backend.layouts.app')
@section('meta_title'){{ 'Head/Account List' }}@stop
@section('content')
<div class="card">
    <div class="col-12 mb-2">
        @if(session()->has('success'))
        <div class="alert alert-success" role="alert">
            {{ session()->get('success') }}
        </div>
        @endif
        @if(session()->has('error'))
        <div class="alert alert-danger" role="alert">
            {{ session()->get('error') }}
        </div>
        @endif
    </div>
    <form class="" action="{{ route('heads.index') }}" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('All Heads') }}</h5>
            </div>

            {{--<div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>--}}
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" value="{{ @$search }}" placeholder="{{ ('Search ...') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Search') }}</button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <a href="{{ route('heads.create') }}" class="btn btn-primary btn-sm float-right">Add New Head</a>
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>{{ ('#') }}</th>
                        <th data-breakpoints="md">{{ ('Head') }}</th>
                        <th data-breakpoints="md">{{ ('Parent Head') }}</th>
                        <th data-breakpoints="md">{{ ('Sub Head') }}</th>
                        <th class="text-right" width="15%">{{ ('options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($heads as $head)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $head->head }}</td>
                        <td>{{ $head->parent_head }}</td>
                        <td>{{ $head->sub_head }}</td>
                        <td class="text-right">
                        @if($head->type == 'normal')
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('heads.edit',$head->id)}}" title="{{ ('Edit') }}">
                                <i class="las la-pen"></i>
                            </a>

                            {{--<a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('accounts.heads.destroy', $head->id)}}" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>--}}
                        @else
                            {{" "}}
                        @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $heads->appends(request()->input())->links() }}
            </div>

        </div>
    </form>
</div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
