@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ 'All Gift Offers' }}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.gift_offers.create') }}" class="btn btn-circle btn-info">
                <span>{{ 'Create New Gift Offer' }}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ 'Gift Offers' }}</h5>
        <div class="pull-right clearfix">
            <form class="" id="sort_gift_offers" action="" method="GET">
                <div class="box-inline pad-rgt pull-left">
                    <div class="d-flex gap-2">
                        <div style="min-width: 150px;">
                            <select class="form-control aiz-selectpicker" name="offer_type" onchange="this.form.submit()">
                                <option value="">{{ 'All Types' }}</option>
                                <option value="product" {{ $offer_type == 'product' ? 'selected' : '' }}>{{ 'Product Wise' }}</option>
                                {{-- <option value="brand" {{ $offer_type == 'brand' ? 'selected' : '' }}>{{ 'Brand Wise' }}</option>
                                <option value="category" {{ $offer_type == 'category' ? 'selected' : '' }}>{{ 'Category Wise' }}</option> --}}
                                <option value="cart" {{ $offer_type == 'cart' ? 'selected' : '' }}>{{ 'Cart Amount' }}</option>
                            </select>
                        </div>
                        <div style="min-width: 200px;">
                            <input type="text" class="form-control" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ 'Type name & Enter' }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>Title</th>
                    <th data-breakpoints="lg">Type</th>
                    <th data-breakpoints="lg">Condition</th>
                    <th data-breakpoints="lg">Gift Products</th>
                    <th data-breakpoints="lg">Max Selection</th>
                    <th data-breakpoints="lg">Date Range</th>
                    <th data-breakpoints="lg">Status</th>
                    <th class="text-center">Options</th>
                </tr>
            </thead>
            <tbody>
                @foreach($giftOffers as $key => $giftOffer)
                    <tr>
                        <td>{{ ($key + 1) + ($giftOffers->currentPage() - 1) * $giftOffers->perPage() }}</td>
                        <td>
                            <span class="d-block font-weight-bold">
                                {{ $giftOffer->title }}
                            </span>
                            @if($giftOffer->description)
                                <span class="d-block text-muted fs-10">
                                    {{ Str::limit($giftOffer->description, 50) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeColor = match($giftOffer->offer_type) {
                                    'product' => 'info',
                                    'brand' => 'primary',
                                    'category' => 'success',
                                    'cart' => 'warning',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge badge-inline badge-{{ $badgeColor }}">{{ ucfirst($giftOffer->offer_type) }} Wise</span>
                        </td>
                        <td>
                            @if($giftOffer->offer_type === 'cart')
                                <span class="text-success font-weight-bold">
                                    Min: {{ single_price($giftOffer->min_cart_amount) }}
                                </span>
                                @if($giftOffer->max_cart_amount)
                                    <br>
                                    <span class="text-danger">
                                        Max: {{ single_price($giftOffer->max_cart_amount) }}
                                    </span>
                                @endif
                            @else
                                <span class="text-muted">
                                    {{ $giftOffer->conditions_count ?? 0 }} {{ Str::plural('condition', $giftOffer->conditions_count ?? 0) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-inline badge-soft-primary">
                                {{ $giftOffer->items_count ?? 0 }} products
                            </span>
                        </td>
                        <td>
                            <span class="font-weight-bold d-block">
                                Items: {{ $giftOffer->max_item_per_order }}
                            </span>
                            <span class="font-weight-bold d-block">
                                Qty: {{ $giftOffer->max_qty_per_order }}
                            </span>
                        </td>
                        <td class="font-weight-bold">
                            <span class="d-block">
                                Start: {{ $giftOffer->start_date ? date('d-m-Y h:i a', $giftOffer->start_date) : '-' }}
                            </span>
                            <span class="d-block {{ $giftOffer->end_date && \Carbon\Carbon::createFromTimestamp($giftOffer->end_date)->isPast() ? 'text-danger' : '' }}">
                                End: {{ $giftOffer->end_date ? date('d-m-Y h:i a', $giftOffer->end_date) : '-' }}
                            </span>
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_gift_offer_status(this)" value="{{ $giftOffer->id }}" type="checkbox" {{ $giftOffer->status == 1 ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('admin.gift_offers.edit', $giftOffer->id) }}" title="{{ 'Edit' }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{ route('admin.gift_offers.destroy', $giftOffer->id) }}" title="{{ 'Delete' }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="clearfix">
            <div class="pull-right">
                {{ $giftOffers->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
<script type="text/javascript">
    function update_gift_offer_status(el) {
        var status = el.checked ? 1 : 0;
        var alertmsg = el.checked
            ? '{{ "Are you sure you want to activate this gift offer?" }}'
            : '{{ "Are you sure you want to deactivate this gift offer?" }}';

        if (confirm(alertmsg)) {
            $.post('{{ route('admin.gift_offers.update_status') }}', {
                _token: '{{ csrf_token() }}',
                id: el.value,
                status: status
            }, function(data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', '{{ "Status updated successfully" }}');
                } else {
                    AIZ.plugins.notify('danger', '{{ "Cannot activate gift offer without gift products" }}');
                    el.checked = !el.checked;
                }
            });
        } else {
            el.checked = !el.checked;
        }
    }
</script>
@endsection
