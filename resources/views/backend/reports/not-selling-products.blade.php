@extends('backend.layouts.app')
@php
    use Illuminate\Support\Number;
    $brands = Cache::remember('filter_brands', now()->addDay(), function () {
        return \App\Models\Brand::pluck('name', 'id')->toArray();
    });
@endphp
@section('content')
    <div class="card">
        <form class="" action="" id="sort_orders" method="GET">
            <div class="card-header">
                <div class="col px-0">
                    <h5 class="mb-md-0 h6">{{ 'Not Selling Products' }}</h5>
                </div>
            </div>
            <div class="card-header">
                <form action="{{ route(Route::currentRouteName()) }}" method="GET">
                    <div class="form-row align-items-center w-100">
                        <div class="form-group col-md-1 mb-2">
                            @php
                                $perPages = [10, 25, 50, 100, 'all'];
                            @endphp
                            <select id="per_page" name="per_page" class="form-control form-control-sm aiz-selectpicker"
                                data-live-search="true">
                                <option value="">Per Page</option>
                                @foreach ($perPages as $page)
                                    <option value="{{ $page }}" @if ($page == (request()->per_page ?? 25)) selected @endif>
                                        {{ $page }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2 mb-2">
                            <select id="sort_by" name="sort_by" class="form-control form-control-sm aiz-selectpicker">
                                <option value="">Sort By</option>
                                <option value="asc" @if (request()->sort_by == 'asc') selected @endif>Current Stock (Asc)
                                </option>
                                <option value="desc" @if (request()->sort_by == 'desc') selected @endif>Current Stock
                                    (Desc)</option>
                            </select>
                        </div>
                        {{-- Date Picker --}}
                        <div class="form-group col-md-2 mb-2">
                            <input type="date" id="date" name="date" class="form-control form-control-sm"
                                value="{{ request()->date ?? now()->format('Y-m-d') }}" max="{{ date('Y-m-d') }}"
                                min="2000-01-01" placeholder="{{ 'Filter by date' }}">
                        </div>

                        {{-- Brand Dropdown --}}
                        <div class="form-group col-md-2 mb-2">
                            <select id="brand_id" name="brand_id" class="form-control form-control-sm aiz-selectpicker"
                                data-live-search="true">
                                <option value="">{{ 'Select Brand' }}</option>
                                @foreach ($brands as $id => $name)
                                    <option value="{{ $id }}" @if ($id == request()->brand_id) selected @endif>
                                        {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Search Field --}}
                        <div class="form-group col-md-3 mb-2">
                            <input type="text" id="search" name="search" class="form-control form-control-sm"
                                placeholder="{{ 'Type product name...' }}" value="{{ request()->search ?? '' }}">
                        </div>

                        {{-- Submit Button --}}
                        <div class="form-group col-auto mb-2 text-right">
                            <input type="hidden" name="submit" value="yes">
                            <button type="submit" class="btn btn-sm btn-primary">
                                {{ 'Filter' }}
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary"
                                onclick="window.location.href='{{ route('admin.notSellingProducts') }}'">
                                Clear
                            </button>
                        </div>
                    </div>
                </form>
            </div>


            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="alert alert-sm alert-primary font-weight-bold mb-0">
                            Total Unsold Products = {{ @$unsoldProductsCount }};
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-success" onclick="exportProducts()">
                            <i class="las la-file-excel"></i> Export
                        </button>
                    </div>
                </div>
                <table class="table aiz-table mb-0" id="theTable">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="35%">Product Name</th>
                            <th class="text-center">Brand Name</th>
                            <th class="text-center">Last Purchase Date</th>
                            <th class="text-center">Last Purchase Price</th>
                            <th class="text-center">Current Stock <span class="text-danger" data-toggle="tooltip"
                                    data-title="{{ ucfirst(Number::spell($summary['total_stocks'] ?? 0)) }}">({{ $summary['total_stocks'] ?? 0 }})</span>
                            </th>
                            <th class="text-center">Stock Amount <span class="text-danger cursor-pointer"
                                    data-toggle="tooltip"
                                    data-title="{{ ucfirst(Number::spell(round($summary['total_stock_amount'] ?? 0))) }}">({{ single_price($summary['total_stock_amount'] ?? 0) }})</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($unsoldProducts as $key => $product)
                            <tr>
                                <td>
                                    @if (request()->per_page === 'all')
                                        {{ $key + 1 }}
                                    @else
                                        {{ ($unsoldProducts->currentPage() - 1) * $unsoldProducts->perPage() + $key + 1 }}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ to_frontend(route('product', $product->slug)) }}" target="_blank">
                                        {{ $product->name }}
                                    </a>
                                    <span class="d-block text-muted fs-10 font-weight-bold">
                                        Created At {{ $product->created_at->format('d-m-Y') }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $product->brand?->name ?? 'N/A' }}</td>
                                <td class="text-center">
                                    {{ $product->lastPurchaseOrderItem?->updated_at->format('d-m-Y') }}
                                </td>
                                <td class="text-center">
                                    {{ single_price($product->lastPurchaseOrderItem?->price ?? 0) }}
                                </td>
                                <td class="text-center">
                                    {{ $product->latest_stock_qty ?? 0 }}
                                </td>
                                <td class="text-center">
                                    @php
                                        $qty = $product->latest_stock_qty ?? 0;
                                        $unit_price = $product->lastPurchaseOrderItem?->price ?? 0;
                                    @endphp
                                    {{ single_price($qty * $unit_price) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if (request()->per_page !== 'all')
                    <div class="aiz-pagination">
                        {{ $unsoldProducts->appends(request()->input())->links() }}
                    </div>
                @endif
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        function exportProducts() {
            const brandId = $('#brand_id').val();
            const date = $('#date').val();
            const searchQuery = $('#search').val();

            let url = '{{ route('admin.notSellingProducts.export') }}?';

            if (brandId) {
                url += `brand_id=${brandId}&`;
            }
            if (date) {
                url += `date=${date}&`;
            }
            if (searchQuery) {
                url += `search=${encodeURIComponent(searchQuery)}&`;
            }

            window.location.href = url;
        }
    </script>
@endsection
