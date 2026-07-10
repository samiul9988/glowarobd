@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="col">
            <h5 class="mb-md-0 h6">{{ ('Product Stock Report') }}</h5>
        </div>
    </div>
    <div class="card-header d-flex">
        <form class="col-10" action="" id="sort_orders" method="GET">
            <div class="row gutters-5">
                <div class="col-2">
                    <div class="form-group mb-0">
                        <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group mb-0">
                        <select class="form-control aiz-selectpicker" name="product_id" id="product_id" data-live-search="true" required>
                            <option value="">{{ ('Select Product')}}</option>
                            @foreach ($allproducts as $product)
                            <option value="{{ $product->id }}" @if($product->id == $product_id) selected @endif>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 varient_data" style="display: none;">
                    <div class="form-group">
                        <select class="form-control aiz-selectpicker" name="varient_id" id="varient_id" data-live-search="true">
                            <option value="">{{ ('Select Variant')}}</option>
                        </select>
                    </div>
                </div>

                <div class="col-auto">
                    <div class="form-group mb-0">
                        <input type="hidden" name="submit" value="yes" />
                        <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                    </div>
                </div>
            </div>
        </form>
        <button id="btnExport" onclick="fnExcelReport();" class="col-auto btn btn-soft-primary" download="your-foo.xls"><i class="lar la-file-excel"></i> Export To Excel</button>
    </div>
    <div class="card-body">
        <table class="table mb-0" id="theTable">
            <thead>
                <tr>
                    <th class="text-left">{{ ('Date') }}</th>
                    <th class="text-right">{{ ('Opening Stock') }}</th>
                    <th class="text-right">{{ ('Current Purchase') }}</th>
                    <th class="text-right">{{ ('Current Stock Adjustment') }}</th>
                    <th class="text-right">{{ ('Current Sell') }}</th>
                    <th class="text-right">{{ ('Closing Stock') }}</th>
                    <th class="text-right">{{ ('Purchase') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($report as $entry)
                <tr>
                    <td>{{@$entry['date']}}</td>
                    <td class="text-right" data-val="{{$entry['opening']}}">
                        {{$entry['opening']}}
                    </td>
                    <td class="text-right" data-val="{{$entry['purchases']}}">
                        {{$entry['purchases']}}
                    </td>
                    <td class="text-right" data-val="{{ ($entry['plus_adjustments'] - $entry['minus_adjustments']) }}">
                        @php
                        $adjustments = ($entry['plus_adjustments'] - $entry['minus_adjustments']);
                        // dump('Adjustments: '.$adjustments);
                        @endphp
                        {{ $adjustments }}
                    </td>
                    <td class="text-right" data-val="{{$entry['sales']}}">
                        {{$entry['sales']}}
                    </td>
                    <td class="text-right" data-val="{{ $entry['closing'] }}">
                        {{ $entry['closing'] }}
                    </td>
                    <td class="text-right" data-val="0">
                        @if(isset($entry['po_number']))
                        <a href="{{route('purchaseorder.show', encrypt(@$entry['po_id']))}}" title="{{ ('View Purchase Order') }}">
                            {{ @$entry['po_number'] }}
                        </a>
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @endforeach
                <tr>
                    <td class="text-right font-weight-bold"></td>
                    <td class="text-right font-weight-bold">Total</td>
                    <td class="text-right font-weight-bold"></td>
                    <td class="text-right font-weight-bold"></td>
                    <td class="text-right font-weight-bold"></td>
                    <td class="text-right font-weight-bold"></td>
                    <td class="text-right font-weight-bold"></td>
                </tr>
            </tbody>
        </table>
        <iframe id="txtArea1" style="display:none"></iframe>
    </div>
</div>

@endsection

@section('script')

<script>
    var final = 0
    var tbody = document.querySelector("tbody");
    var howManyCols = tbody.rows[0].cells.length;
    var totalRow = tbody.rows[tbody.rows.length - 1];

    for (var j = 2; j < howManyCols - 2; j++) {
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

                if (!isNaN(thisNumber)) {
                    result += thisNumber;
                } else {
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
            a.download = 'stock-report-{{ $date }}.xls'
            a.click()
        }

    }

    $("[name=product_id]").on("change", function() {
        getproductvarient($(this).val());
    });

    function getproductvarient(productid) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "POST",
            url: '{{ route("purchaseorder.getproductvarient") }}',
            data: {
                productid: productid
            },
            success: function(data) {
                if (data.status == true) {
                    if (data.varient_status == 1) {
                        $('.varient_data').show();
                        $('#varient_id').html(data.varientdata);
                        AIZ.plugins.bootstrapSelect('refresh');
                    } else {
                        $('.varient_data').hide();
                        $('#varient_id').html(data.varientdata);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            }
        });
    }

    // $(document).ready(function(){
    //     const today = new Date();
    //     const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    //     const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    //     $('.aiz-date-range').daterangepicker({
    //         startDate: firstDayOfMonth,
    //         endDate: lastDayOfMonth,
    //         locale: {
    //             separator: " to ",
    //             format: 'DD-MM-YY'
    //         }
    //     });
    // });
</script>

@endsection
