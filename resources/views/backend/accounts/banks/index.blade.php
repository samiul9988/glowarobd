@extends('backend.layouts.app')
@section('meta_title'){{ 'Bank List' }}@stop
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
    <form class="" action="{{route('accounts.banks.search')}}" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('All Banks') }}</h5>
            </div>

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ ('Type bank name, account number or account name') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Search') }}</button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <a href="{{ route('banks.create') }}" class="btn btn-primary btn-sm float-right">Add New Bank</a>
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>{{ ('#') }}</th>
                        <th data-breakpoints="md">{{ ('Bank Name') }}</th>
                        <th data-breakpoints="md">{{ ('Account No.') }}</th>
                        <th data-breakpoints="md">{{ ('Account Name') }}</th>
                        <th data-breakpoints="md">{{ ('Bank Type') }}</th>
                        <th data-breakpoints="md">{{ ('Other Info') }}</th>
                        <th class="text-right" width="15%">{{ ('options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($banks as $bank)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $bank->bank_name }}</td>
                        <td>{{ $bank->acc_no }}</td>
                        <td>{{ $bank->acc_name }}</td>
                        <td>{{ $bank->type }}</td>
                        <td>@if(!empty($bank->address)) <span class="text-primary">Address:</span> {{ $bank->address.' /' }} @endif @if(!empty($bank->contact_no)) <span class="text-primary">Mobile:</span> {{ $bank->contact_no }} @endif
                        </td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('banks.edit',$bank->id)}}" title="{{ ('Edit') }}">
                                <i class="las la-pen"></i>
                            </a>

                            {{--<a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('accounts.banks.destroy', $bank->id)}}" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>--}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $banks->appends(request()->input())->links() }}
            </div>

        </div>
    </form>
</div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
