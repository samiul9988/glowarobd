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
                <div class="col-2">
                    <div class="form-group mb-0">
                        <select class="form-control aiz-selectpicker" name="brand" id="brand" data-live-search="true">
                            <option value="">{{ ('Select Brand')}}</option>
                            @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @if($brand->id == $brand_id) selected @endif>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{--<div class="col-4">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control" id="search" name="search" @isset($search) value="{{ $search }}" @endisset placeholder="{{ ('Type product name...') }}">
                    </div>
                </div>--}}

                <div class="col-auto">
                    <div class="form-group mb-0">
                        <input type="hidden" name="submit" value="yes"/>
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
                    <th>{{ ('#') }}</th>
                    <th width="25%">{{ ('Product Name') }}</th>

                    {{--<th>{{ ('Opening Purchase') }}</th>
                    <th>{{ ('Opening Sell') }}</th>--}}

                    <th class="text-right">{{ ('Opening Stock') }}</th>
                    <th class="text-right">{{ ('Current Purchase') }}</th>
                    <th class="text-right">{{ ('Current Stock Adjustment') }}</th>
                    <th class="text-right">{{ ('Current Sell') }}</th>

                    {{--<th>{{ ('Sub 2') }}</th>--}}

                    <th class="text-right">{{ ('Closing Stock') }}</th>
                    <th class="text-right">{{ ('Avg. Cur. Stock Amount') }}</th>
                    <th class="text-right">{{ ('Last P.Price') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($products as $key => $item)

                <tr>
                    <td>{{$key + 1 }}</td>
                    <td>{{@$item->name}} @if(@$item->variant != NULL)({{@$item->variant}})@endif</td>
                    {{--<td>
                        @if($item->opening_purchase == NULL)
                            {{0}}
                        @else
                            {{$item->opening_purchase}}
                        @endif
                    </td>
                    <td>
                        @if($item->opening_sell == NULL)
                            {{0}}
                        @else
                            {{$item->opening_sell}}
                        @endif
                    </td>--}}

                    @php
                        $openingStock = ($item->opening_plus_adjustment + $item->opening_purchase) - ($item->opening_sell + $item->opening_minus_adjustment);
                    @endphp

                    <td class="text-right" data-val="{{$openingStock}}">
                        {{$openingStock}}
                    </td>

                    <td class="text-right" data-val="@if($item->current_purchase == NULL){{0}}@else $item->current_purchase @endif">
                        @if($item->current_purchase == NULL)
                            {{0}}
                        @else
                            {{$item->current_purchase}}
                        @endif
                    </td>
                    <td class="text-right" data-val="{{ ($item->current_plus_adjustment - $item->current_minus_adjustment) }}">
                        {{ ($item->current_plus_adjustment - $item->current_minus_adjustment) }}
                    </td>
                    <td class="text-right" data-val="@if($item->current_sell == NULL){{0}}@else $item->current_sell @endif">
                        @if($item->current_sell == NULL)
                            {{0}}
                        @else
                            {{$item->current_sell}}
                        @endif
                    </td>
                    {{--<td>
                        {{$item->current_purchase - $item->current_sell}}
                    </td>--}}
                    @php
                        $currentStock = ($item->current_purchase + $item->current_plus_adjustment) - ($item->current_sell + $item->current_minus_adjustment);
                        $closingStock = ($openingStock + $currentStock);
                    @endphp
                    <td class="text-right" data-val="{{ $closingStock }}">
                        {{ $closingStock }}
                    </td>
                    @php
                        $closing_stock = ($item->opening_purchase - $item->opening_sell) + ($item->current_purchase - $item->current_sell);

                    @endphp
                    <td class="text-right" data-val="{{ $closingStock * $item->avg_price }}">
                        {{single_price(@$closingStock * @$item->avg_price)}}
                    </td>
                    <td class="text-right" data-val="{{ @$item->last_price }}">
                        {{single_price(@$item->last_price)}}
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

    for (var j = 2; j < howManyCols - 1; j++) {
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

    function fnExcelReport()
    {
        var tab_text="<table border='2px'><tr>";
        var textRange; var j=0;
        tab = document.getElementById('theTable'); // id of table

        for(j = 0 ; j < tab.rows.length ; j++)
        {
            tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
            //tab_text=tab_text+"</tr>";
        }

        tab_text=tab_text+"</table>";
        tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
        tab_text= tab_text.replace(/<img[^>]*>/gi,""); // remove if u want images in your table
        tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");

        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer
        {
            txtArea1.document.open("txt/html","replace");
            txtArea1.document.write(tab_text);
            txtArea1.document.close();
            txtArea1.focus();
            sa=txtArea1.document.execCommand("SaveAs",true,"Say Thanks to coder71.xls");
            return sa;
        }else{
            // sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text), '_blank', 'stock-report.xls');
            const blob = new Blob([tab_text], { type: 'application/vnd.ms-excel' })
            const a = document.createElement('a')
            a.href = URL.createObjectURL(blob)
            a.download = 'stock-report-{{ $date }}.xls'
            a.click()
        }

    }
</script>

@endsection
