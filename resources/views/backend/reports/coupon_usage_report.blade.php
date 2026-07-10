@extends('backend.layouts.app')

@section('content')
    <div class="card">
        <form class="" action="" id="sort_orders" method="GET">
            <div class="card-header">
                <div class="col px-0">
                    <h5 class="mb-md-0 h6">Coupon Usage Report</h5>
                </div>
            </div>
            <div class="card-header row gutters-5 justify-content-start">
                <div class="col-md-2 mb-2">
                    <div class="form-group mb-0">
                        <input type="text" class="aiz-date-range form-control-sm form-control"
                            value="{{ request('date') }}" name="date" placeholder="Filter By Date"
                            data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="coupon" data-live-search="true">
                        <option value="">Filter By Coupon</option>
                        @foreach ($coupons as $id => $code)
                            <option value="{{ $id }}" @if (request('coupon') == $id) selected @endif>
                                {{ $code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto mb-2">
                    <div class="form-group mb-0 mt-0">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        <button type="button" class="btn btn-sm btn-secondary"
                            onclick="window.location.href='{{ route('admin.couponUsageReport.index') }}'">Clear</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="text-muted font-weight-bold mb-2">
                    Showing {{ $couponUsages->count() }} of {{ $couponUsages->total() }} entries
                </div>
                <table class="table aiz-table mb-0" id="theTable">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="15%" class="text-center">Coupon</th>
                            <th width="20%" >User</th>
                            <th class="text-center">Order</th>
                            <th width="20%" >Referrer</th>
                            <th width="20%" class="text-center">Used At</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($couponUsages as $usage)
                            <tr>
                                <td class="text-center">{{ $loop->iteration + (($couponUsages->currentPage() - 1) * $couponUsages->perPage()) }}</td>
                                <td class="text-center">{{ $usage->coupon->code }}</td>
                                <td>
                                    @if($usage->user)
                                        <span class="{{ Str::contains($usage->user->name, 'deleted', true) ? 'text-danger' : '' }}">
                                            {{ $usage->user->name }}
                                        </span>
                                    @else
                                        Guest User
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($usage->order)
                                        <a href="{{ route('all_orders.show', encrypt($usage->order->id)) }}" target="_blank">
                                            {{ $usage->order->code }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    {{ $usage->referrer->name }}
                                </td>
                                <td class="text-center">{{ $usage->created_at->format('d-m-Y h:i a') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $couponUsages->appends(request()->input())->links() }}
                </div>
            </div>
        </form>
    </div>
@endsection
