@extends('backend.layouts.app')

@section('content')
<div class="row gutters-10">
    <div class="col-6 col-md-3">
        <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                <div class="h3 fw-700">
                    {{ count($suppliers) }}
                </div>
                <div class="opacity-50">{{ ('Supplier(s)') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="bg-grad-1 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                <div class="h3 fw-700">
                    {{ single_price(abs($suppliers->where('balance', '<', 0)->sum('balance'))) }}
                </div>
                <div class="opacity-50">{{ ('Payable') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="bg-grad-3 text-white rounded-lg mb-4 overflow-hidden">
            <div class="p-3">
                <div class="h3 fw-700">
                    {{ single_price(abs($suppliers->where('balance', '>', 0)->sum('balance'))) }}
                </div>
                <div class="opacity-50">{{ ('Advance') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_customers" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-0 h6">{{ ('Supplier')}}</h5>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{ ('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" onclick="bulk_delete()">{{ ('Delete selection')}}</a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name or phone">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>{{ ('Name')}}</th>
                        <th data-breakpoints="sm">{{ ('Phone')}}</th>
                        <th data-breakpoints="lg">{{ ('Address')}}</th>
                        <th>{{ ('Payable')}}</th>
                        <th data-breakpoints="xs">{{ ('Advance')}}</th>
                        <th class="text-center">{{ ('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $key => $item)
                            <tr>
                                <td>
                                    <div class="form-group">
                                        <div class="aiz-checkbox-inline">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" class="check-one" name="id[]" value="">
                                                <span class="aiz-square-check"></span>
                                            </label>
                                        </div>
                                    </div>
                                </td>
                                <td>{{$item->name}}</td>
                                <td>{{$item->contact_number}}</td>
                                <td>{{$item->address}}</td>
                                <td>
                                    @php
                                        $advancePayments = $item->payments
                                            ->filter(fn($payment) => Str::contains($payment->remarks, 'General Payment', true))
                                            ->sum('amount');
                                        $payable = $item->total_purchase - $item->total_paid - $advancePayments;
                                    @endphp
                                    {{ $payable }}
                                    {{-- <span class="d-block text-muted">
                                        @if($item->balance < 0) {{abs($item->balance)}} @else <strong>-</strong> @endif
                                    </span> --}}
                                </td>
                                <td>@if($item->balance > 0) {{abs($item->balance)}} @else <strong>-</strong> @endif</td>
                                <td class="text-center">
                                    {{-- @if($item->balance < 0) --}}
                                    @if($payable > 0)
                                    <a class="btn btn-soft-success btn-icon btn-circle btn-sm mb-1 mb-md-0" href="{{route('accounts.payments.create', ['sid' => $item->id])}}" data-toggle="tooltip" title="Pay Supplier">
                                        <i class="las la-money-bill"></i>
                                    </a>
                                    @endif
                                    <a class="btn btn-soft-info btn-icon btn-circle btn-sm mb-1 mb-md-0" href="{{route('suppliers.details', $item->id)}}" title="{{ ('View') }}">
                                        <i class="las la-eye"></i>
                                    </a>
                                    <a href="{{route('supplier.edit', $item->id)}}" class="btn btn-soft-primary btn-icon btn-circle btn-sm mb-1 mb-md-0" title="{{ ('Log in as this Customer') }}">
                                        <i class="las la-edit"></i>
                                    </a>

                                    <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('supplier.delete', $item->id)}}" title="{{ ('Delete') }}">
                                        <i class="las la-trash"></i>
                                    </a>
                                </td>
                            </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
</div>
@endsection


@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">

        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });

        function sort_customers(el){
            $('#sort_customers').submit();
        }
        function confirm_ban(url)
        {
            $('#confirm-ban').modal('show', {backdrop: 'static'});
            document.getElementById('confirmation').setAttribute('href' , url);
        }

        function confirm_unban(url)
        {
            $('#confirm-unban').modal('show', {backdrop: 'static'});
            document.getElementById('confirmationunban').setAttribute('href' , url);
        }

        function bulk_delete() {
            var data = new FormData($('#sort_customers')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-customer-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        location.reload();
                    }
                }
            });
        }
    </script>
@endsection
