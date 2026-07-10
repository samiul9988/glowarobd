@extends('backend.layouts.app')
@section('meta_title'){{ 'Voucher List' }}@stop
@section('content')
<style>
    #print_head{
        display: none;
    }
    @media print {
        #print_head, #printoptions_head, #printoptions_td{
            display: block;
        }
        #printoptions_head, #printoptions_td{
            display: none !important;
        }
    }
</style>
<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('All Voucher Entries') }}</h5>
            </div>

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ ('Purchase Order Number') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card-body" id="printArea">
        <div class="text-center" id="print_head">
            <br />
            @php
                if(!empty($date)){
                    $startDate = date('Y-m-d', strtotime(explode(" to ", $date)[0]));
                    $endDate = date('Y-m-d', strtotime(explode(" to ", $date)[1]));
                }
            @endphp
            <h4 class="mb-0 p-0" style="line-height: 1;">{{ get_setting('site_name') }}</h4>
            <small>{{get_setting('contact_address')}}</small>
            <h5 class="mt-1">Voucher Entries Report</h5>
            <div>
                @if(!empty($startDate))
                    From <strong>{{date('d-m-Y', strtotime($startDate))}} </strong>
                @endif
                @if(!empty($endDate))
                    to <strong> {{date('d-m-Y', strtotime($endDate))}}</strong>
                @endif
            </div>
            <br />
        </div>
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="md">{{ ('Date') }}</th>
                    <th>{{ ('Voucher Number') }}</th>
                    <th data-breakpoints="md">{{ ('Voucher Type') }}</th>
                    <th data-breakpoints="md">{{ ('Total Debit') }}</th>
                    <th data-breakpoints="md">{{ ('Total Credit') }}</th>
                    <th class="text-right" width="15%" id="printoptions_head">{{ ('options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entries as $key => $entry)
                <tr>
                    <td>{{ date('d-m-Y', strtotime($entry->date))}}</td>
                    <td><a href="{{route('accounts.vouchers.show', $entry->vno)}}" title="{{ ('See Voucher') }}">{{ $entry->vno }}</a></td>
                    <td class="text-capitalize">{{$entry->voucher_type}}</td>
                    <td>{{ single_price($entry->debit) }}</td>
                    <td>{{ single_price($entry->credit) }}</td>
                    <td class="text-right" id="printoptions_td">
                        <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{route('accounts.vouchers.show', $entry->vno)}}" title="{{ ('View') }}">
                            <i class="las la-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="aiz-pagination">
            {{ $entries->appends(request()->input())->links() }}
        </div>

    </div>
</div>
<div class="d-flex justify-content-between align-items-center">
    <button class="btn btn-sm btn-secondary" onclick="printArea()"><i class="las la-print"></i> Print</button>
</div>

<script>
    function printArea() {
        var printContents = document.getElementById("printArea").innerHTML;
        var originalContents = document.body.innerHTML;

        // Replace the current document content with the content of the printable div
        document.body.innerHTML = printContents;

        // Print the content
        window.print();

        // Restore the original document content
        document.body.innerHTML = originalContents;
    }
</script>

@endsection
