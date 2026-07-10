@extends('backend.layouts.app')
@php
    $categories = Cache::remember('filter_categories', now()->addDay(), function () {
        return \App\Models\Category::pluck('name', 'id')->toArray();
    });
    $brands = Cache::remember('filter_brands', now()->addDay(), function () {
        return \App\Models\Brand::pluck('name', 'id')->toArray();
    });
    $products = Cache::remember('filter_products', now()->addDay(), function () {
        return DB::table('products')->where('published', 1)
            ->pluck('name', 'id')
            ->toArray();
    });
@endphp
@section('content')
<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header">
            <div class="col px-0">
                <h5 class="mb-md-0 h6">{{ ('Top Selling Products') }}</h5>
            </div>
        </div>
        <div class="card-header row gutters-5 align-items-center">
            <div class="col-12 col-md-2 mb-2 mb-md-0">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control form-control-sm" value="{{ request('date') }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-12 col-md-2 mb-2 mb-md-0">
                <div class="form-group mb-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="status" id="status" data-live-search="true">
                        <option value="">{{ ('Delivery Status')}}</option>
                        <option value="pending" @if(request('status')=='pending' ) selected @endif>Pending</option>
                        <option value="confirmed" @if(request('status')=='confirmed' ) selected @endif>Confirmed</option>
                        <option value="picked_up" @if(request('status')=='picked_up' ) selected @endif>Picked Up</option>
                        <option value="on_the_way" @if(request('status')=='on_the_way' ) selected @endif>On the Way</option>
                        <option value="delivered" @if(request('status')=='delivered' ) selected @endif>Delivered</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-2 mb-2 mb-md-0">
                <div class="form-group mb-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="product_id" id="product_id" data-live-search="true">
                        <option value="">{{ ('Select Product')}}</option>
                        @foreach ($products as $productId => $productName)
                            <option value="{{ $productId }}" @if($productId == request('product_id')) selected @endif>
                                {{ $productName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-2 mb-2 mb-md-0">
                <div class="form-group mb-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="brand_id" id="brand_id" data-live-search="true">
                        <option value="">{{ ('Select Brand')}}</option>
                        @foreach ($brands as $id => $brand)
                            <option value="{{ $id }}" @if($id == request('brand_id')) selected @endif>
                                {{ $brand }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-2 mb-2 mb-md-0">
                <div class="form-group mb-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="category_id" id="category_id" data-live-search="true">
                        <option value="">{{ ('Select Category')}}</option>
                        @foreach ($categories as $id => $category)
                            <option value="{{ $id }}" @if($id == request('category_id')) selected @endif>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-2 mb-2 mb-md-0">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="{{ ('Type product name...') }}">
                </div>
            </div>
            <div class="col-auto ml-auto mt-2">
                <div class="form-group mb-0">
                    <input type="hidden" name="submit" value="yes" />
                    <input type="hidden" name="export" value="no" />
                    <button type="submit" class="btn btn-sm btn-primary">{{ ('Filter') }}</button>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'yes']) }}" class="btn btn-sm btn-soft-primary"><i class="lar la-file-excel"></i> Export</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex w-100 flex-md-row flex-column">
                <div class="alert alert-info py-2 flex-fill mr-2 mb-2 mb-md-0 w-100 w-md-auto">
                    Note: Amount calculate without delivery charge.
                </div>

                <div class="alert alert-primary py-2 flex-fill mb-0 font-weight-bold">
                    Total Quantity = {{ $totalQty }};
                    Total Sell Amount = {{ single_price(round($totalAmount, 2)) }};
                    Total Purchase Amount = {{ single_price(round($purchaseTotal, 2)) }};
                    Gross Profit = {{ single_price(round($totalAmount - $purchaseTotal, 2)) }};
                </div>
            </div>
            <table class="table aiz-table mb-0" id="theTable">
                <thead>
                    <tr>
                        <th data-breakpoints="sm">{{ ('#') }}</th>
                        <th>{{ ('Product') }}</th>
                        <th width="10%" data-breakpoints="sm">{{ ('Brand') }}</th>
                        <th width="10%" data-breakpoints="sm">{{ ('Category') }}</th>
                        <th width="10%" data-breakpoints="sm"class="text-center">{{ ('Total Sell Qty (pcs)') }}</th>
                        <th width="10%" class="text-center">{{ ('Total Sell Amount') }}</th>
                        <th width="10%" data-breakpoints="sm" class="text-center">{{ ('Last Sell') }}</th>
                        <th width="10%" data-breakpoints="sm" class="text-center">{{ ('Last Purchase Price') }}</th>
                        <th width="10%" class="text-center">{{ ('Current Stock') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $ta = 0;
                        $pt = 0;
                    @endphp
                    @foreach($sales as $key => $item)
                    @php
                        $ta += $item->total_amount;
                        $pt += ($item->product->lastPurchaseOrderItem->price ?? 0) * $item->total_quantity;
                    @endphp
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>
                            {{@$item->product_name}}
                            @if($item->variation != '' & $item->variation != NULL)
                            ({{@$item->variation}})
                            @endif
                        </td>
                        <td>
                            {{@$item->brand}}
                        </td>
                        <td>
                            {{@$item->category}}
                        </td>
                        <td class="text-center">
                            {{@$item->total_quantity}}
                        </td>
                        <td class="text-center">
                            {{ single_price(round($item->total_amount, 2)) }}
                        </td>
                        <td class="text-center">{{ \Carbon\Carbon::parse(@$item->max_time)->format('Y-m-d') }}</td>
                        {{-- <td>{{ @$item->purchase_price ?? 0 }}</td> --}}
                        <td class="text-center">{{ single_price(@$item->product->lastPurchaseOrderItem->price ?? 0) }}</td>
                        <td class="text-center">
                            @php
                                // Calculate current stock based on variant status
                                $currentStock = 0;
                                if ($item->variant_product) {
                                    // Sum all variant stocks
                                    foreach ($item->product->stocks as $stock) {
                                        $currentStock += $stock->qty;
                                    }
                                } else {
                                    // Single product stock
                                    $currentStock = optional($item->product->stocks->first())->qty ?? 0;
                                }

                                // Display stock
                                echo $currentStock;

                                // Show low stock warning if needed
                                if ($currentStock <= $item->product->low_stock_quantity) {
                                    echo ' <span class="badge badge-inline badge-danger">Low</span>';
                                }
                            @endphp
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{--<div class="aiz-pagination">
                @if($sales != NULL)
                {{ $sales->appends(request()->input())->links() }}
                @endif
            </div>--}}

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
            });
            const a = document.createElement('a');
            const date = "{{ request('date') ?? now()->format('Y-m-d') }}";
            a.href = URL.createObjectURL(blob)
            a.download = 'top-selling-products-report-' + date + '.xls'
            a.click()
        }

    }
</script>

@endsection
