@extends('backend.layouts.app')
@section('meta_title'){{ 'Sub Head Wise Account/Ledger Reports' }}@stop
@section('content')

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">Sub Head Ledger</h5>
        <button id="btnExport" onclick="fnExcelReport();" class="col-auto btn btn-soft-primary" download="your-foo.xls"><i class="lar la-file-excel"></i> Export To Excel</button>
    </div>
    <div class="card-header d-block">
        <form action="{{ route('accounts.reports.sub_head_ledger') }}" method="GET">
            @csrf
            <div class="row gutters-5">
                <div class="col-3">
                    <div class="form-group mb-0">
                        <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group mb-0">
                        <select class="form-control aiz-selectpicker" name="subhead" id="head" data-live-search="true" required>
                            <option value="">{{ ('Select Sub Ledger/Account')}}</option>
                            @foreach ($subheads as $item)
                                <option value="{{ $item->head }}" @if($item->head == $subhead) selected @endif>
                                    {{ $item->head }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-auto">
                    <div class="form-group mb-0">
                        <input type="hidden" name="submit" value="yes" />
                        <button type="submit" class="btn btn-primary">{{ ('Generate Report') }}</button>
                    </div>
                </div>
            </div>
        </form>

    </div>
    <div class="card-body" id="printArea">
        <div class="text-center">
            <h4 class="mb-0 p-0" style="line-height: 1;">{{ get_setting('site_name') }}</h4>
            <small>{{get_setting('contact_address')}}</small>
            <h5 class="mt-1">Account/Ledger Report</h5>
            @if(!empty($subhead))
            <h6 class="mt-1">{{$subhead}}</h6>
            @endif
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
        <table class="table mb-0" id="theTable">
            <thead>
                <tr>
                    <th class="text-left">{{ ('SL') }}</th>
                    <th class="text-left">{{ ('Particulars') }}</th>
                    <th class="text-right">{{ ('Opening Balance') }}</th>
                    <th class="text-right">{{ ('Debit') }}</th>
                    <th class="text-right">{{ ('Credit') }}</th>
                    <th class="text-right">{{ ('Closing Balance') }}</th>
                </tr>
            </thead>

            <tbody>
            @if(!empty($transactions))
                @php
                    $sumofdebit = $transactions->sum('debit');
                    $sumofcredit = $transactions->sum('credit');
                    $sumofopening = $transactions->sum('opening');
                    $sumofclosing = 0;
                @endphp
                @foreach($transactions as $entry)
                @php
                    $closing = $entry->opening + $entry->debit - $entry->credit;
                    $sumofclosing = $sumofclosing + $closing;
                @endphp
                <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>{{$entry->head}}</td>
                    <td class="text-right">{{single_price($entry->opening)}}</td>
                    <td class="text-right">{{single_price($entry->debit)}}</td>
                    <td class="text-right">{{single_price($entry->credit)}}</td>
                    <td class="text-right">{{single_price($closing)}}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="text-right font-weight-bold"></td>
                    <td class="text-right font-weight-bold"></td>
                    <td class="text-right font-weight-bold">Total:</td>
                    <td class="text-right font-weight-bold">{{ single_price($sumofdebit) }}</td>
                    <td class="text-right font-weight-bold">{{ single_price($sumofcredit) }}</td>
                    <td class="text-right font-weight-bold">{{ single_price($sumofclosing) }}</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center">
    <button class="btn btn-sm btn-secondary" onclick="printArea()"><i class="las la-print"></i> Print</button>
</div>

@endsection

@section('script')

<script type="text/javascript">
    function fnExcelReport() {
        var tab_text = "<table border='2px'><tr>";
        var textRange;
        var j = 0;
        tab = document.getElementById('theTable'); // id of table

        for (j = 0; j < tab.rows.length; j++) {
            tab_text = tab_text + tab.rows[j].innerHTML + "</tr>";
            //tab_text=tab_text+"</tr>";
        }

        tab_text = tab_text + "</table>";
        tab_text = tab_text.replace(/<A[^>]*>|<\/A>/g, ""); //remove if u want links in your table
        tab_text = tab_text.replace(/<img[^>]*>/gi, ""); // remove if u want images in your table
        tab_text = tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");

        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet Explorer
        {
            txtArea1.document.open("txt/html", "replace");
            txtArea1.document.write(tab_text);
            txtArea1.document.close();
            txtArea1.focus();
            sa = txtArea1.document.execCommand("SaveAs", true, "Say Thanks to coder71.xls");
            return sa;
        } else {
            // sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text), '_blank', 'stock-report.xls');
            const blob = new Blob([tab_text], {
                type: 'application/vnd.ms-excel'
            })
            const a = document.createElement('a')
            a.href = URL.createObjectURL(blob)
            a.download = 'trial_balance_{{ $date }}.xls'
            a.click()
        }

    }

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
