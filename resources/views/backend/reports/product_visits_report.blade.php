@extends('backend.layouts.app')

@php
    use Illuminate\Support\Arr;
    $filterProducts = Cache::remember('crm_products', now()->addHours(3), function () {
        return App\Models\Product::published()->pluck('name', 'id');
    });
@endphp

@section('content')
@if(blank(request()->date))
<div class="alert alert-info">
    Note: This report is generated for last 7 days by default.
</div>
@endif
@if(get_setting('retain_product_visit_logs_forever') == 1)
    <div class="alert alert-warning">
        <strong>Note:</strong> Retain product visit logs forever is currently enabled. <strong>Please be aware</strong> that retaining logs indefinitely may lead to increased database size over time. If you want to modify this setting, please do so in the <a href="{{ route('website.appearance') }}#product-visit-log-settings" target="_blank">System Settings</a>.
    </div>
@else
    <div class="alert alert-success">
        <strong>Note:</strong> Retain product visit logs forever is currently disabled by default to optimize database size. And it will remove {{ get_setting('retain_product_visit_logs_months', 12) }} months old logs periodically. If you want to customize this setting, please do so in the <a href="{{ route('website.appearance') }}#product-visit-log-settings" target="_blank">System Settings</a>.
    </div>
@endif
<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header">
            <div class="col px-0">
                <h5 class="mb-md-0 h6">Product Visits Report</h5>
            </div>
        </div>
        <div class="card-header row gutters-5 justify-content-start">
            <div class="col-md-2 mb-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control-sm form-control" value="{{ request()->date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="source" name="source" data-live-search="true">
                    <option value="">Filter By Source</option>
                    @foreach ($sources as $source)
                        <option value="{{ $source }}" @if (request()->source == $source) selected @endif>
                            {{ strtoupper(\App\Enums\UtmSources::value($source)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="product" name="product" data-live-search="true">
                    <option value="">Filter By Product</option>
                    @foreach ($filterProducts as $id => $product)
                        <option value="{{ $id }}" @if (request()->product == $id) selected @endif>{{ ucfirst($product) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="cancelled_by" name="cancelled_by">
                    <option value="">Filter By Affiliate User</option>
                </select>
            </div> --}}
            <div class="col-auto mb-2">
                <div class="form-group mb-0 mt-0">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="window.location.href='{{ route('admin.productVisitsReport.index') }}'">Clear</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table aiz-table mb-0" id="theTable">
                <thead>
                    <tr>
                        <th width="30%">Product</th>
                        <th width="50%" class="text-center">Total Visits</th>
                        <th width="20%">Breakdown</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($visits as $visit)
                        @php
                            $lastVisitedAt = \Carbon\Carbon::parse($visit->last_visited_at);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ to_frontend(route('product', $visit->product->slug)) }}" target="_blank">
                                    {{ Str::limit($visit->product->name, 50) }}
                                </a>
                                @if (strlen($visit->product->name) > 50)
                                    @include('components.tooltip', ['title' => $visit->product->name])
                                @endif
                                <span class="d-block text-muted fs-10 font-weight-bold">
                                    {{-- Last Viewed: {{ $lastVisitedAt->diffForHumans() }}
                                    <br> --}}
                                    Last Viewed: {{ $visit->product->last_viewed_at->diffForHumans() }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-inline badge-soft-success fs-11 font-weight-bold">
                                    <i class="las la-eye mr-2"></i>Total: {{ readableNumber($visit->total_visits ?? 0) }}
                                </span>
                            </td>
                            <td>
                                @foreach ($visit->breakdown as $source => $count)
                                    @if(!$loop->last && !$loop->first && $loop->index % 2 == 0) <br> @endif
                                    <span class="badge badge-inline badge-soft-info fs-11 {{ !$loop->last ? 'mb-1' : '' }} font-weight-bold">
                                        <i class="las la-eye mr-2"></i>{{ strtoupper(\App\Enums\UtmSources::value($source)) }}: {{ readableNumber($count ?? 0) }}
                                    </span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $visits->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>
@endsection
