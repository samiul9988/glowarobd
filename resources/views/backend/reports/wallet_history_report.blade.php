@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class=" align-items-center">
        <h1 class="h3">{{ ('Wallet Transaction Report')}}</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <form action="{{ route('wallet-history.index') }}" method="GET">
                <div class="card-header row gutters-5">
                    <div class="col text-center text-md-left">
                        <h5 class="mb-md-0 h6">{{ ('Wallet Transaction') }}</h5>
                    </div>
                    @if(Auth::user()->user_type != 'seller')
                    <div class="col-md-3 ml-auto">
                        <select id="demo-ease" class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="user_id">
                            <option value="">{{ ('Choose User') }}</option>
                            @foreach ($users_with_wallet as $key => $user)
                                <option value="{{ $user->id }}" @if($user->id == $user_id) selected @endif >
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-sm aiz-date-range" id="search" name="date_range"@isset($date_range) value="{{ $date_range }}" @endisset placeholder="{{ ('Daterange') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-md btn-primary" type="submit">
                            {{ ('Filter') }}
                        </button>
                    </div>
                </div>
            </form>
            <div class="card-body">

                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ ('Customer')}}</th>
                            <th data-breakpoints="lg">{{  translate('Date') }}</th>
                            <th>{{ ('Amount')}}</th>
                            <th data-breakpoints="lg">{{ ('Payment Method')}}</th>
                            <th data-breakpoints="lg" class="text-right">{{ ('Approval')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($wallets as $key => $wallet)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                @if ($wallet->user != null)
                                    <td>{{ $wallet->user->name }}</td>
                                @else
                                    <td>{{ ('User Not found') }}</td>
                                @endif
                                <td>{{ date('d-m-Y', strtotime($wallet->created_at)) }}</td>
                                <td>{{ single_price($wallet->amount) }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $wallet ->payment_method)) }}</td>
                                <td class="text-right">
                                    @if ($wallet->offline_payment)
                                        @if ($wallet->approval)
                                            <span class="badge badge-inline badge-success">{{ ('Approved')}}</span>
                                        @else
                                            <span class="badge badge-inline badge-info">{{ ('Pending')}}</span>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination mt-4">
                    {{ $wallets->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
