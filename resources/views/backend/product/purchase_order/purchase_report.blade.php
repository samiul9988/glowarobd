@extends('backend.layouts.app')

@section('content')
@php
    $suppliers = Cache::remember('all_suppliers', now()->addHours(3), function () {
        return App\Models\Supplier::query()->pluck('name', 'id')->toArray();
    });
    $products = Cache::remember('filter_products', now()->addDay(), function () {
        return DB::table('products')->where('published', 1)
            ->pluck('name', 'id')
            ->toArray();
    });
@endphp
<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('All Purchase Order') }}</h5>
            </div>
            <div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="user_id" name="user_id" data-live-search="true" required>
                    <option value="">{{ ('Select a supplier') }}</option>
                    @foreach ($suppliers as $sid => $name)
                        <option value="{{ $sid }}"  @if ($sid == request('user_id')) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="product_id" name="product_id" data-live-search="true">
                    <option value="">{{ ('Select a product') }}</option>
                    @foreach ($products as $pid => $name)
                        <option value="{{ $pid }}"  @if ($pid == request('product_id')) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ request('date') }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="{{ ('Purchase Order Number') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>

        <div class="card-body">
            @if(!empty($supplier_id))
                <div class="d-flex justify-content-between">
                    <div class="alert alert-info">
                        Purchase Report:
                        @if (!empty(request('date')))
                            From {{ request('date') }}
                        @endif
                        For {{ $suppliers[$supplier_id] }}
                    </div>
                    <div>
                        <a href="javascript:void(0);" id="btnExport" onclick="fnExcelReport();" class="btn btn-sm btn-soft-primary" download="purchase-report.xls"><i class="lar la-file-excel"></i> Export To Excel</a>
                    </div>
                </div>
            @endif
            <table class="table aiz-table mb-0" id="theTable">
                <thead>
                    <tr>
                        <th data-breakpoints="md">{{ ('Date') }}</th>
                        <th>{{ ('Purchase Order Number') }}</th>
                        <th data-breakpoints="md">{{ ('Supplier') }}</th>
                        <th data-breakpoints="md">{{ ('Seller') }}</th>
                        <th data-breakpoints="md">{{ ('Num. of Products') }}</th>
                        <th data-breakpoints="md">{{ ('Amount') }}</th>
                        <th data-breakpoints="md">{{ ('Paid Amount') }}</th>
                        <th data-breakpoints="md">{{ ('Due Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchaseorders as $key => $purchaseorder)
                    <tr>
                        <td>{{ date('d-m-Y', $purchaseorder->purchase_date )}}</td>
                        <td><a href="{{route('purchaseorder.show', encrypt($purchaseorder->id))}}" title="{{ ('See Invoice') }}">{{ $purchaseorder->po_number }}</a></td>
                        <td>
                            {{ $purchaseorder->supplier?->name ?? 'N/A' }}<br>
                            {{ $purchaseorder->supplier->contact_number ?? "" }}
                        </td>
                        <td>{{ $purchaseorder->sellername?->name ?? 'N/A' }}</td>
                        <td>{{ count($purchaseorder->purchaseOrderDetails) }}</td>
                        <td>{{ $purchaseorder->grand_total }}</td>
                        <td>{{ $purchaseorder->total_payment }}</td>
                        <td>{{ $purchaseorder->grand_total - $purchaseorder->total_payment }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td class="font-weight-bold"></td>
                        <td class="font-weight-bold"></td>
                        <td class="font-weight-bold text-right">Total: </td>
                        <td class="font-weight-bold"></td>
                        <td class="font-weight-bold"></td>
                        <td class="font-weight-bold"></td>
                        <td class="font-weight-bold"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
</div>
@endsection

@section('script')
    <script type="text/javascript">
        var final = 0
        var tbody = document.querySelector("tbody");
        var howManyCols = tbody.rows[0].cells.length;
        var totalRow = tbody.rows[tbody.rows.length - 1];

        for (var j = 3; j < howManyCols; j++) {
            final = computeTableColumnTotal(j).toFixed(2);
            totalRow.cells[j].innerText = final;
        }

        function computeTableColumnTotal(colNumber) {
            var result = 0;
            try {
                var tableBody = document.querySelector("tbody");
                var howManyRows = tableBody.rows.length;

                for (var i = 0; i < (howManyRows - 1); i++) {
                    var thisNumber = parseFloat(tableBody.rows[i].cells[colNumber].childNodes.item(0).data);

                    if (!isNaN(thisNumber)){
                        result += thisNumber;
                    }else{
                        thisNumber = tableBody.rows[i].cells[colNumber].childNodes.item(0).data
                        thisNumber = thisNumber.replace(/[^0-9.]/g, '');
                        thisNumber = parseFloat(thisNumber);
                        result += thisNumber;
                    }
                }
            } finally {
                return result;
            }
        }
        function fnExcelReport() {
            var tab_text = "<table border='2px'><tr>";
            var textRange;
            var j = 0;
            tab = document.getElementById('theTable'); // id of table

            for (j = 0; j < tab.rows.length; j++) {
                tab_text = tab_text + tab.rows[j].innerHTML + "</tr>";
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
                });
                const a = document.createElement('a');
                const date = "{{ request('date') }}";
                a.href = URL.createObjectURL(blob)
                a.download = 'purchase-report-' + date + '.xls'
                a.click()
            }
        }
    </script>
@endsection
