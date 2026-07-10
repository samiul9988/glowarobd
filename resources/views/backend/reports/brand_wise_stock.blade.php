@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="col">
            <h5 class="mb-md-0 h6">{{ ('Brand Wise Stock Report') }}</h5>
        </div>
    </div>
    <div class="card-header d-flex">
        <form class="col-10" action="" id="sort_orders" method="GET">
            <div class="row gutters-5">
                <div class="col-3">
                    <div class="form-group mb-0">
                        <select class="form-control aiz-selectpicker" name="brand" id="brand" data-live-search="true">
                            <option value="">{{ ('Select Brand')}}</option>
                            @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @if($brand->id == $brand_id) selected @endif>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-auto">
                    <div class="form-group mb-0">
                        <input type="hidden" name="submit" value="yes"/>
                        <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                    </div>
                </div>
            </div>
        </form>


        @if($brandStocks->isNotEmpty())
            <div>
                <a href="javascript:void(0);" id="btnExport" onclick="fnExcelReport();" class="btn btn-sm btn-soft-primary" download="sales-report.xls"><i class="lar la-file-excel"></i> Export To Excel</a>
            </div>
        @endif
    </div>

    <div class="card-body">
        <div class="d-flex justify-content-center">
            <div class="alert alert-sm alert-primary font-weight-bold">
                Grand Total Stock Value = {{ single_price($grandTotalStockValue) }}
            </div>
        </div>
        <table class="table mb-0" id="theTable">
            <thead>
                <tr>
                    <th>{{ ('#') }}</th>
                    <th width="25%">{{ ('Brand') }}</th>
                    <th class="text-center">{{ ('No. of Products') }}</th>
                    <th class="text-center">
                        <span class="d-block">
                            {{ ('Total Stock') }}
                        </span>
                        <span class="d-block text-info fs-9">{{ ucwords('(Sum of each products last available stock)') }}</span>
                    </th>
                    <th class="text-center">
                        <span class="d-block">
                            {{ ('Total Stock Value') }} <span class="text-success fs-10">(Approx)</span>
                        </span>
                        <span class="d-block text-info fs-9">{{ ucwords('(Qty * Last purchase price)') }}</span>
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($brandStocks as $key => $stock)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $stock->brand_name }}</td>
                        <td class="text-center">
                            {{ $stock->number_of_products }}
                        </td>
                        <td class="text-center">
                            {{ $stock->total_available_stock }}
                        </td>
                        <td class="text-center">
                            {{ single_price($stock->total_stock_value) }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td class="text-right font-weight-bold"></td>
                    <td class="text-right font-weight-bold"></td>
                    <td class="text-right font-weight-bold"></td>
                    <td class="text-right font-weight-bold"></td>
                    <td class="text-center font-weight-bold">Total: {{ single_price($grandTotalStockValue) }}</td>
                </tr>
            </tbody>
        </table>
        <iframe id="txtArea1" style="display:none"></iframe>
    </div>
</div>
@endsection

@section('script')
<script>
    var date = `{{ $date ?? date('Y-m-d') }}`;

    function fnExcelReport() {
        var tab = document.getElementById('theTable'); // reference to the HTML table
        var tab_text = "<table border='2px'>";

        for (var i = 0; i < tab.rows.length; i++) {
            var row = tab.rows[i];
            var cells = row.cells;
            tab_text += "<tr>";
            for (var j = 1; j < cells.length; j++) {
                tab_text += "<td>" + cells[j].innerHTML + "</td>";
            }
            tab_text += "</tr>";
        }

        tab_text += "</table>";

        // Clean unwanted tags
        tab_text = tab_text.replace(/<A[^>]*>|<\/A>/g, ""); // remove anchor tags
        tab_text = tab_text.replace(/<img[^>]*>/gi, ""); // remove images
        tab_text = tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // remove input elements

        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");

        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
            txtArea1.document.open("txt/html", "replace");
            txtArea1.document.write(tab_text);
            txtArea1.document.close();
            txtArea1.focus();
            sa = txtArea1.document.execCommand("SaveAs", true, `stock-report-by-brand-${date}.xls`);
            return sa;
        } else {
            const blob = new Blob([tab_text], {
                type: 'application/vnd.ms-excel'
            });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = `stock-report-by-brand-${date}.xls`;
            a.click();
        }
    }

</script>
@endsection
