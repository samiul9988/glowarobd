@extends('backend.layouts.app')

@section('content')
<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header">
            <div class="col">
                    <h5 class="mb-md-0 h6">{{ ('Scanned Report') }}</h5>
            </div>
        </div>
        <div class="card-header d-flex">
            <div class="col-10">
                <div class="row gutters-5">
                    <div class="col-2">
                        <div class="form-group mb-0">
                            <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-2">
                        <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="entry_status" name="entry_status" >
                            <option value="" @if(empty($entry_status)) selected @endif>{{ ('Entry Status') }}</option>
                            <option value="0" @if($entry_status == '0') selected @endif>{{ ('Not Created') }}</option>
                            <option value="1" @if($entry_status == '1') selected @endif>{{ ('Success') }}</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="shipping_method" name="shipping_method" >
                            <option value="">Shipping Method</option>
                            @foreach($methods as $method)
                            <option value="{{$method->id}}" @if ($shipping_method == $method->id) selected @endif>{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="form-group mb-0 mt-0">
                            <input type="hidden" name="submit" value="yes"/>
                            <button type="submit" class="btn btn-primary btn-sm">{{ ('Filter') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <button id="btnExport" onclick="fnExcelReport();" class="col-auto btn btn-soft-primary" download="your-foo.xls"><i class="lar la-file-excel"></i> Export To Excel</button>
        </div>
        <div class="card-body">
            <table class="table aiz-table mb-0" id="theTable">
                <thead>
                    <div class="alert alert-info">
                        Note: Amount calculate without delivery charge.
                    </div>
                    <tr>
                        <th>{{ ('#') }}</th>
                        <th>{{ ('Order Code') }}</th>
                        <th>{{ ('Customer') }}</th>
                        <th>{{ ('Amount') }}</th>
                        <th>{{ ('Delivery Cost') }}</th>
                        <th>{{ ('Entry Status') }}</th>
                        <th class="col-3">{{ ('Info') }}</th>
                        <th>{{ ('Shipping Method')}}</th>
                        <th>{{ ('Order Source')}}</th>
                    </tr>
                </thead>

                <tbody>
                    @if(isset($logs))
                    @foreach($logs as $key => $log)
                    @php
                        $shippingCostSum = 0;

                        if ($log->order && $log->order->orderDetails) {
                            $shippingCostSum = $log->order->orderDetails->sum('shipping_cost');
                        }
                    @endphp
                    <tr>
                        <td>{{ 1 + $key }}</td>
                        <td><a href="{{route('all_orders.show', encrypt(@$log->order->id))}}" target="_blank">{{ @$log->order->code }}</a></td>
                        <td>
                            {{ @json_decode($log->order->shipping_address)->name }}<br>
                            {{ @json_decode($log->order->shipping_address)->phone }}
                        </td>
                        <td>{{ @$log->order->grand_total - @$log->order->shipping_cost }}</td>
                        <td>{{ $shippingCostSum ?? 0 }}</td>
                        <td>
                            @php
                                $status = $log->createdEntry == 1 ? '<span class="w-auto badge badge-success">Created</span>' : '<span class="w-auto badge badge-danger">Not Created</span>';
                            @endphp
                            {!! $status !!}
                        </td>
                        <td class="break">

                            @if($log->createdEntry == 1)
                                <span>Shipping Entry Was successfully Created.</span>
                            @else
                                <p class="m-0">Unable to create entry.</p>
                                @php
                                    $response = @json_decode($log->error_response, true);
                                @endphp
                                <strong>{{ @$response[0]->reason }}</strong>
                            @endif
                        </td>
                        <td>
                            {{ (ucfirst(str_replace('_', ' ', @$log->shipping_method->name))) }}
                        </td>
                        <td>
                            @if(@$log->order->order_source == 'IOS')
                                iPhone
                            @else
                                {{@$log->order->order_source}}
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @endif
                    <tr>
                        <td class="font-weight-bold"></td>
                        <td class="font-weight-bold"></td>
                        <td class="font-weight-bold">Total</td>
                        <td id="totalAmount" class="font-weight-bold"></td>
                        <td id="totalShipCost" class="font-weight-bold"></td>
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

<script>
    calculateTotals(); // Initial call

    function calculateTotals() {
        var table = document.getElementById("theTable");
        let totalAmount = 0;
        for (let i = 1; i < table.rows.length; i++) {
            let cellValue = parseFloat(table.rows[i].cells[3].textContent);
            totalAmount += isNaN(cellValue) ? 0 : cellValue;
        }
        document.getElementById("totalAmount").innerHTML = totalAmount.toFixed(2);

        let totalShipCost = 0;
        for (let i = 1; i < table.rows.length; i++) {
            let cellValue = parseFloat(table.rows[i].cells[4].textContent);
            totalShipCost += isNaN(cellValue) ? 0 : cellValue;
        }
        document.getElementById("totalShipCost").innerHTML = totalShipCost.toFixed(2);
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
            a.download = 'scan-report-{{ $date }}.xls'
            a.click()
        }

    }
</script>

@endsection
