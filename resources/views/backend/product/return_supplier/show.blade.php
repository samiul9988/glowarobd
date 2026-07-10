@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h1 class="h2 fs-16 mb-0">Return Supplier Details</h1>
    </div>
    <div class="card-body">
        <div class="row gutters-5 mt-2">
            <div class="col-md-3 text-center text-md-left">
                <h5>Supplier</h5>
                <address>
                    <strong class="text-main">{{ $returnSupplier->supplier?->name ?? 'Unknown/Deleted' }}</strong><br>
                    <a href="tel:">{{ $returnSupplier->supplier?->contact_number  ?? 'N/A' }}</a>
                    <p>{{ $returnSupplier->supplier?->address ?? 'N/A' }}</p>
                </address>
            </div>

            <div class="col ml-auto">
                <table class="float-right">
                    <tbody>
                        <tr>
                            <td class="text-main text-bold px-2">RS Number #</td>
                            <td class="text-right text-info text-bold mx-2">{{ $returnSupplier->rs_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold px-2">Created By:</td>
                            <td class="text-right text-bold mx-2">{{ $returnSupplier->user?->name ?: 'Unknown' }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold px-2">Date:</td>
                            <td class="text-right text-bold mx-2">{{ $returnSupplier->date }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <hr class="new-section-sm bord-no">
        <div class="row">
            <div class="col-lg-12 table-responsive">
                <h4 class="h6">Returned Products</h4>
                <table class="table table-bordered invoice-summary">
                    <thead>
                        <tr class="bg-trans-dark">
                            <th data-breakpoints="lg" class="min-col">#</th>
                            <th class="text-uppercase">Product</th>
                            <th data-breakpoints="lg" class="text-center">Variant</th>
                            <th data-breakpoints="lg" class="text-center">SKU</th>
                            <th data-breakpoints="lg" class="text-right">Purchase Price</th>
                            <th data-breakpoints="lg" class="text-center">Qty</th>
                            <th data-breakpoints="lg" class="text-center">Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($returnSupplier->items as $key => $return_item)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $return_item->product?->name ?? '--' }}</td>
                                <td class="text-center">
                                    {{ $return_item->product_stock?->variant ?: 'N/A' }}
                                </td>
                                <td class="text-center">
                                    {{ $return_item->product_stock?->sku ?: 'N/A' }}
                                </td>
                                <td class="text-right">{{ single_price($return_item->purchase_price) }}</td>
                                <td class="text-center">{{ $return_item->qty }}</td>
                                <td class="text-center">
                                    {{ single_price($return_item->purchase_price * $return_item->qty) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5">Grand Total :</th>
                            <th class="text-center">{{ $returnSupplier->items->sum('qty') }}</th>
                            <th class="text-center">
                                {{ single_price($returnSupplier->items->sum(fn ($item) => $item->purchase_price * $item->qty)) }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
                <p><strong>Note:</strong> {{$returnSupplier->note}}</p>
            </div>
        </div>
    </div>
</div>
@endsection
