@extends('backend.layouts.app')

@section('content')
@if(blank(@$date))
<div class="alert alert-info">
    Note: This report shows products that are about to expire in {{ get_setting('expire_products_alert_duration', 7) }} days.
</div>
@endif

<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header">
            <div class="col px-0">
                <h5 class="mb-md-0 h6 mx-0">{{ ('Recent Expire Products') }}</h5>
            </div>
            @if($items->isNotEmpty())
                <a href="javascript:void(0);" id="btnExport" onclick="location.href='{{ route('admin.expireProductsReport.export', request()->query()) }}'" class="btn btn-sm btn-soft-primary"><i class="lar la-file-excel"></i> Export To Excel</a>
            @endif
        </div>
        <div class="card-header row gutters-5 align-items-center">
            <div class="col-12 col-md-4">
                <div class="form-group mb-0">
                    <select class="form-control aiz-selectpicker" data-live-search="true" name="product" id="product">
                        <option value="">Loading ...</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group mb-0">
                    @php
                        $today = \Carbon\Carbon::today()->format('Y-m-d');
                    @endphp

                    <input type="date" class="form-control" value="{{ @$date }}" name="date" placeholder="{{ ('Filter by date') }}" min="{{ $today }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" @isset($search) value="{{ $search }}" @endisset placeholder="{{ ('Type product name...') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table aiz-table mb-0" id="theTable">
                <thead>
                    <tr>
                        <th>{{ ('#') }}</th>
                        <th>{{ ('Product Name') }}</th>
                        <th class="text-center">{{ ('Left Qty (pcs)') }}</th>
                        <th class="text-center">{{ ('Expire Date') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($items as $key => $item)
                        <tr class="{{ $item->expire_date < now() ? 'bg-soft-danger' : '' }}">
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <a href="{{ to_frontend(route('product', $item->product->slug)) }}" target="_blank">{{ $item->product->getTranslation('name') }}</a>
                            </td>
                            <td class="text-center">{{ $item->left_qty }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($item->expire_date)->format('d-m-Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
</div>

@endsection

@section('script')

<script>
    let selectedProduct = '{{ @$product }}';

    // Initial loads
    getProducts();
    // Generic debounce function
    function debounce(func, delay) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), delay);
        };
    }

    // Fetch products
    async function getProducts(search = '') {
        try {
            const params = new URLSearchParams({
                search,
                selected: selectedProduct,
            });
            const response = await fetch(`{{ route('reviews.fetch_products') }}?${params}`);
            if (!response.ok) throw new Error('Server Error');

            const data = await response.json();
            $('#product').empty().append('<option value="">Select a product</option>');
            $.each(data, (id, name) => {
                $('#product').append(
                    `<option value="${id}" ${selectedProduct == id ? 'selected' : ''}>${name}</option>`);
            });
            $('#product').selectpicker('refresh');
        } catch (error) {
            console.error('Error fetching products:', error);
        }
    }

    $(document).on('shown.bs.select', function(e) {
        const $select = $(e.target);
        const selectId = $select.attr('id');

        setTimeout(() => {
            const $searchInput = $select.closest('.bootstrap-select').find('.bs-searchbox input');

            if (selectId === 'product') {
                $searchInput.off('input').on('input', debounce(function() {
                    getProducts(this.value);
                }, 300));
            } else if (selectId === 'playlist') {
                $searchInput.off('input').on('input', debounce(function() {
                    getplaylists(this.value);
                }, 300));
            }
        }, 10); // slight delay to ensure DOM is ready
    });
</script>

@endsection
