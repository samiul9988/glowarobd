@extends('backend.layouts.app')

@section('content')

<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">All Returned Products</h5>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ request('date') }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group mb-0">
                    <select name="supplier_id" id="supplier_id" class="form-control aiz-selectpicker" data-live-search="true">
                        <option value="">Filter by supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @if (request('supplier_id') == $supplier->id) selected @endif>
                                {{ $supplier->name . ' ' . $supplier->phone }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by RS number">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>

        <div class="card-body">

            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th data-breakpoints="md">Date</th>
                        <th>RS Number</th>
                        <th>Supplier</th>
                        <th>Product Count</th>
                        <th>Total Amount</th>
                        <th data-breakpoints="md">Creator</th>
                        <th class="text-right" width="15%">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($returnSuppliers as $key => $returnSupplier)
                    <tr>
                        <td>{{ $returnSupplier->date }}</td>
                        <td>{{ $returnSupplier->rs_number }}</td>
                        <td class="text-capitalize">
                            {{ $returnSupplier->supplier?->name ?? 'Unknown/Deleted' }}
                        </td>
                        <td>{{ $returnSupplier->items_count }}</td>
                        <td>{{ single_price($returnSupplier->total_amount) }}</td>
                        <td>{{ $returnSupplier->user?->name ?? 'Unknown/Deleted' }}</td>

                        <td class="text-right">
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ route('stock-adjust.return_supplier.show', $returnSupplier->id) }}" title="View">
                                <i class="las la-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $returnSuppliers->appends(request()->input())->links() }}
            </div>

        </div>
    </form>
</div>

@endsection

@section('script')
    <script type="text/javascript">

    </script>
@endsection
