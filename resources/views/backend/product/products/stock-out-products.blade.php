@extends('backend.layouts.app')
@php
    $categories = Cache::remember('filter_categories', now()->addDay(), function () {
        return \App\Models\Category::pluck('name', 'id')->toArray();
    });
    $brands = Cache::remember('filter_brands', now()->addDay(), function () {
        return \App\Models\Brand::pluck('name', 'id')->toArray();
    });
@endphp
@section('content')
    <style>
        .badge-count {
            margin-left: 5px;
            width: auto;
        }
    </style>

    <div class="card">
        <form class="" id="sort_orders" action="" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-md-0 h6">
                        All Stock Out Products <span class="badge badge-soft-dark badge-count">{{ count($products) }}</span>
                    </h5>
                </div>

                <div class="col-md-2 ml-auto mb-2 mb-md-0">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="brand_id" name="brand_id" data-live-search="true">
                        <option value="">{{ 'All Brands' }}</option>
                        @foreach ($brands as $id => $name)
                            <option value="{{ $id }}" @if ($id == request('brand_id')) selected @endif>
                                {{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 ml-auto mb-2 mb-md-0">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="category_id"
                        name="category_id" data-live-search="true">
                        <option value="">{{ 'All Categories' }}</option>
                        @foreach ($categories as $id => $name)
                            <option value="{{ $id }}" @if ($id == request('category_id')) selected @endif>
                                {{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 mb-2 mb-md-0">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control form-control-sm" id="search" name="search"
                            value="{{ request('search') }}" placeholder="Search...">
                    </div>
                </div>

                <div class="col-auto mb-2 mb-md-0">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="window.location.href='{{ route('all_products.stock_out') }}'">Reset</button>
                    <button type="button" class="btn btn-success btn-sm" onclick="exportProducts()">
                        <i class="las la-file-excel"></i> Export
                    </button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <table class="table aiz-table mb-0" id="theTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th data-breakpoints="sm">Sales Info</th>
                        <th data-breakpoints="sm">Pricing Info</th>
                        <th data-breakpoints="sm" class="text-right">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $key => $product)
                        <tr>
                            <td>
                                <div class="row gutters-5 w-200px w-md-300px mw-100">
                                    <div class="col-auto">
                                        <img src="{{ uploaded_asset($product->thumbnail_img) }}" alt="Image"
                                            class="size-50px img-fit"
                                            onerror="this.error=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                    </div>
                                    <div class="col">
                                        <span class="text-muted text-truncate-2">{{ $product->name }} </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-inline fs-11 badge-soft-info font-weight-bold mb-1">
                                    <i class="las la-chart-bar mr-2"></i>Num Of Sale: {{ $product->num_of_sale }}
                                    {{ 'times' }}
                                </span> <br>
                                <span class="badge badge-inline fs-11 badge-soft-success font-weight-bold mb-1">
                                    <i class="las la-coins mr-2"></i>Last 30 Days Sale:
                                    {{ $product->last_30_days_sell ?? 0 }} (Qty)
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-inline fs-11 badge-soft-success font-weight-bold mb-1">
                                    <i class="las la-money-bill-wave mr-2"></i>Last Purchase Price:
                                    {{ single_price(optional($product->lastPurchaseOrderItem)->price ?? 0) }}
                                </span> <br>
                                <span class="badge badge-inline fs-11 badge-soft-primary font-weight-bold mb-1">
                                    <i class="las la-boxes mr-2"></i>Last Purchase Qty:
                                    {{ optional($product->lastPurchaseOrderItem)->qty ?? 0 }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                    href="{{ to_frontend(route('product', $product->slug)) }}" target="_blank"
                                    title="{{ 'View' }}">
                                    <i class="las la-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection


@section('script')
    <script>
        function exportProducts() {
            const brandId = $('#brand_id').val();
            const categoryId = $('#category_id').val();
            const searchQuery = $('#search').val();

            let url = '{{ route('all_products.stock_out.export') }}?';

            if (brandId) {
                url += `brand_id=${brandId}&`;
            }
            if (categoryId) {
                url += `category_id=${categoryId}&`;
            }
            if (searchQuery) {
                url += `search=${encodeURIComponent(searchQuery)}&`;
            }

            window.location.href = url;
        }
    </script>
@endsection
