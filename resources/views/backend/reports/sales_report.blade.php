@extends('backend.layouts.app')
@php
    $brands = Cache::remember('filter_brands', now()->addDay(), function () {
        return \App\Models\Brand::pluck('name', 'id')->toArray();
    });
    $sellers = Cache::remember('filter_sellers', now()->addDay(), function () {
        return \App\Models\User::where('user_type', 'seller')->pluck('name', 'id')->toArray();
    });
    $products = Cache::remember('filter_products', now()->addDay(), function () {
        return \App\Models\Product::where('published',1)->pluck('name', 'id')->toArray();
    });
@endphp
@section('content')
<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('Sales report') }}</h5>
            </div>
        </div>
        <div class="card-header row gutters-5 justify-content-start">
            <div class="col-lg-2 mb-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control-sm form-control" value="{{ request('date') }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="delivery_status" name="delivery_status">
                    <option value="">{{ ('Delivery Status') }}</option>
                    <option value="pending" @if (request('delivery_status')=='pending' ) selected @endif>{{ ('Pending') }}</option>
                    <option value="confirmed" @if (request('delivery_status')=='confirmed' ) selected @endif>{{ ('Confirmed') }}</option>
                    <option value="picked_up" @if (request('delivery_status')=='picked_up' ) selected @endif>{{ ('Picked Up') }}</option>
                    <option value="on_the_way" @if (request('delivery_status')=='on_the_way' ) selected @endif>{{ ('On The Way') }}</option>
                    <option value="delivered" @if (request('delivery_status')=='delivered' ) selected @endif>{{ ('Delivered') }}</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="payment_method" name="payment_method">
                    <option value="">Payment Method</option>
                    <option value="cash_on_delivery" @if (request('payment_method')=='cash_on_delivery' ) selected @endif>Cash On Delivery</option>
                    <option value="bkash" @if (request('payment_method')=='bkash' ) selected @endif>Bkash</option>
                    <option value="cash" @if (request('payment_method')=='cash' ) selected @endif>Cash</option>
                    <option value="sslcommerz" @if (request('payment_method')=='sslcommerz' ) selected @endif>Sslcommerz</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="payment_status" name="payment_status">
                    <option value="">{{ ('Payment Status') }}</option>
                    <option value="paid" @if (request('payment_status')=='paid' ) selected @endif>{{ ('Paid') }}</option>
                    <option value="unpaid" @if (request('payment_status')=='unpaid' ) selected @endif>{{ ('Unpaid') }}</option>
                </select>
            </div>

            <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="order_source" name="order_source">
                    <option value="">{{ ('Order Source') }}</option>
                    <option value="android" @if (strtolower(request('order_source'))=='android') selected @endif>{{ ('Android') }}</option>
                    <option value="IOS" @if (strtolower(request('order_source'))=='ios') selected @endif>{{ ('iOS') }}</option>
                    <option value="website" @if (strtolower(request('order_source'))=='website') selected @endif>{{ ('Website') }}</option>
                    <option value="POS" @if (strtolower(request('order_source'))=='pos') selected @endif>{{ ('POS') }}</option>
                    <option value="merchant" @if (strtolower(request('order_source'))=='merchant') selected @endif>{{ ('Merchant') }}</option>
                    <option value="showroom" @if (strtolower(request('order_source'))=='showroom') selected @endif>{{ ('Showroom') }}</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="utm_source" name="utm_source">
                    <option value="">Utm Source</option>
                    <option value="all" @if ('all' == request('utm_source')) selected @endif>All</option>
                    @foreach ($utmSources as $utmSource)
                        <option value="{{ $utmSource }}" @if ($utmSource == request('utm_source')) selected @endif>
                            {{ strtoupper(\App\Enums\UtmSources::value($utmSource)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="seller" name="seller">
                    <option value="">{{ ('All Sellers') }}</option>
                    @foreach ($sellers as $id => $name)
                        <option value="{{ $id }}" @if ($id == request('seller')) selected @endif>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="seller" name="product" data-live-search="true">
                    <option value="">{{ ('All Products') }}</option>
                    @foreach ($products as $id => $name)
                        <option value="{{ $id }}" @if ($id == request('product')) selected @endif>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="brand" name="brand" data-live-search="true">
                    <option value="">All Brands</option>
                    @foreach ($brands as $id => $name)
                        <option value="{{ $id }}" @if ($id == request('brand')) selected @endif>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="{{ ('Type Order code & customer') }}">
                </div>
            </div>
            <div class="col-auto mb-2">
                <div class="form-group mb-0 mt-0">
                    <input type="hidden" name="submit" value="yes" />
                    <button type="submit" class="btn btn-sm btn-primary">{{ ('Filter') }}</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="window.location.href='{{ route('sales.report') }}'">{{ ('Clear') }}</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div class="alert alert-info py-2">
                    Note: Amount calculate without delivery charge.
                </div>
                <div>
                    @if($count > 0)
                        <button type="button" class="btn btn-success btn-sm" onclick="location.href='{{ route('sales_report.export', request()->query()) }}'"><i class="lar la-file-excel"></i> Export To Excel</button>
                    @else
                        <button type="button" class="btn btn-success btn-sm" disabled><i class="lar la-file-excel"></i> Export To Excel</button>
                    @endif
                </div>
            </div>
            <table class="table aiz-table mb-0" id="theTable">

                <thead>
                    @if($count > 0)
                    <tr>
                        <td colspan="2"></td>

                        @php
                        // $grand = $orders->sum(function ($item) {
                        //     return $item->grand_total - $item->orderDetails->sum('shipping_cost');
                        // });

                        // $grand_delivery = $orders->sum('grand_total');
                        @endphp
                        {{-- <td colspan="7" class="text-right font-weight-bold">Total count: {{@$count}} &nbsp; Sales amount: {{single_price(@$grand)}} + Delivery charge {{single_price(@$grand_delivery - @$grand)}} = Total amount {{single_price(@$grand_delivery)}} &nbsp; @if(!empty($product_id)) Total Product Qty: {{ $orders->sum('product_qty') }} @endif</td> --}}
                        <td colspan="7" class="text-right font-weight-bold">Total count: {{@$count}} &nbsp; Sales amount: {{single_price(@$grand)}} + Delivery charge {{single_price(@$delivery_charge)}} = Total amount {{single_price(@$grand_delivery)}} {{--&nbsp; @if(!empty($product_id)) Total Product Qty: {{ $orders->sum('product_qty') }} @endif--}}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>{{ ('#') }}</th>
                        <th>{{ ('Date') }}</th>
                        <th>{{ ('Order Code') }}</th>
                        <th>{{ ('Customer') }}</th>
                        <th>{{ ('Amount') }}</th>
                        <th>{{ ('Delivery Status') }}</th>
                        <th>{{ ('Payment Method')}}</th>
                        <th>{{ ('Payment Status') }}</th>
                        <th>{{ ('Order Source')}}</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($orders as $key => $order)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ date("d-m-Y", @$order->date)}}</td>
                        <td>
                            <a href="{{route('all_orders.show', encrypt($order->id))}}" target="_blank">
                                {{ $order->code }}
                                @if(!empty($product_id))
                                    <span class="ml-1 w-auto badge badge-primary" title="Qty of this product for this order is {{ $order->product_qty }}">{{ $order->orderDetails->where('product_id', $product_id)->sum('quantity') }}</span>
                                @endif
                            </a>
                        </td>
                        <td>
                            {{ @json_decode($order->shipping_address)->name }}<br>
                            {{ @json_decode($order->shipping_address)->phone }}
                        </td>
                        <td>{{ single_price($order->grand_total - $order->orderDetails->sum('shipping_cost')) }}</td>
                        <td>
                            @php
                            $status = strtoupper(str_replace('_', ' ', $order->delivery_status));
                            @endphp
                            <span class="badge badge-inline badge-soft-info">{!! $status !!}</span>
                        </td>
                        <td>
                            {{ (ucwords(str_replace('_', ' ', $order->payment_type))) }}
                        </td>
                        <td>
                            @php
                                $paymentClass = match(strtolower($order->payment_status)) {
                                    'paid' => 'success',
                                    'unpaid' => 'danger',
                                    default => 'warning',
                                };
                            @endphp
                            <span class="badge badge-inline badge-soft-{{ $paymentClass }}">
                                {{ strtoupper($order->payment_status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-inline badge-soft-success">{{strtoupper($order->order_source)}}</span>
                            @if($order->orderTrack && !is_null($order->orderTrack->utm_source))
                                <br>
                                <span class="badge badge-inline badge-soft-info">{{ strtoupper(\App\Enums\UtmSources::value($order->orderTrack->utm_source)) }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                @if($orders != NULL)
                    {{ $orders->appends(request()->input())->links() }}
                @endif
            </div>
        </div>
    </form>
</div>

@endsection

@section('script')

<script>
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
            a.download = 'sales-report-{{ request('date') }}.xls'
            a.click()
        }

    }
</script>

@endsection
