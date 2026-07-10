@php
    $permissions = json_decode(Auth::user()->staff?->role?->permissions ?? '[]', true) ?? [];

    // dd($permissions);
@endphp
@if (Auth::user()->user_type == 'admin' || any_in_array(['3', 'pending_orders'], $permissions))
    <a href="{{ route('all_orders.status', 'preorder') }}"
        class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center @if ($currentStatus == 'preorder') active @endif">Preorder <span
            class="w-auto badge badge-primary ml-1" id="preorder-count">{{ $deliveryStatusCount['preorder'] }}</span></a>
    <a href="{{ route('all_orders.status', 'merchant') }}"
        class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center @if ($currentStatus == 'merchant') active @endif">Merchant <span
            class="w-auto badge badge-primary ml-1" id="merchant-count">{{ $deliveryStatusCount['merchant'] }}</span></a>
    <a href="{{ route('all_orders.status', 'pending') }}"
        class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center @if ($currentStatus == 'pending') active @endif">Pending <span
            class="w-auto badge badge-primary ml-1" id="pending-count">{{ $deliveryStatusCount['pending'] }}</span></a>
@endif
@if (Auth::user()->user_type == 'admin' || any_in_array(['3', 'processing_orders'], $permissions))
    <a href="{{ route('all_orders.status', 'processing') }}"
        class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center @if ($currentStatus == 'processing') active @endif">Processing <span
            class="w-auto badge badge-primary ml-1" id="processing-count">{{ $deliveryStatusCount['processing'] }}</span></a>
@endif
@if (Auth::user()->user_type == 'admin' || any_in_array(['3', 'hold_orders'], $permissions))
    <a href="{{ route('all_orders.status', 'hold') }}"
        class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center @if ($currentStatus == 'hold') active @endif">Hold <span
            class="w-auto badge badge-primary ml-1" id="hold-count">{{ $deliveryStatusCount['hold'] }}</span></a>
@endif
@if (Auth::user()->user_type == 'admin' || any_in_array(['3', 'confirmed_orders'], $permissions))
    <a href="{{ route('all_orders.status', 'confirmed') }}"
        class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center @if ($currentStatus == 'confirmed') active @endif">Confirmed <span
            class="w-auto badge badge-primary ml-1" id="confirmed-count">{{ $deliveryStatusCount['confirmed'] }}</span></a>
@endif
@if (Auth::user()->user_type == 'admin' || in_array('3', $permissions) || in_array('packaging_orders', $permissions))
    <a href="{{ route('all_orders.status', 'packaging') }}"
        class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center @if ($currentStatus == 'packaging') active @endif">Packaging <span
            class="w-auto badge badge-primary ml-1" id="packaging-count">{{ $deliveryStatusCount['packaging'] }}</span></a>
@endif
@if (Auth::user()->user_type == 'admin' || any_in_array(['3', 'picked_up_orders'], $permissions))
    <a href="{{ route('all_orders.status', 'picked_up') }}"
        class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center @if ($currentStatus == 'picked_up') active @endif">Picked Up <span
            class="w-auto badge badge-primary ml-1" id="picked_up-count">{{ $deliveryStatusCount['picked_up'] }}</span></a>
@endif
@if (Auth::user()->user_type == 'admin' || any_in_array(['3', 'on_the_way_orders'], $permissions))
    <a href="{{ route('all_orders.status', 'on_the_way') }}"
        class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center @if ($currentStatus == 'on_the_way') active @endif">On The Way <span
            class="w-auto badge badge-primary ml-1" id="on_the_way-count">{{ $deliveryStatusCount['on_the_way'] }}</span></a>
@endif
@if (Auth::user()->user_type == 'admin' || any_in_array(['3', 'delivered_orders'], $permissions))
    <a href="{{ route('all_orders.status', 'delivered') }}" class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center @if ($currentStatus == 'delivered') active @endif">
        Delivered
        <span class="w-auto badge badge-primary ml-1" id="delivered-count">{{ $deliveryStatusCount['delivered'] }}</span>
        {{-- <span class="w-auto ml-1" data-toggle="tooltip" data-placement="top" title="Last Cached at {{ \Carbon\Carbon::parse(Cache::get('order_delivered_count_cache_time', now()))->diffForHumans() }}">
            <i class="las la-info-circle text-light fs-16"></i>
        </span> --}}
    </a>
@endif
@if (Auth::user()->user_type == 'admin' || any_in_array(['3', 'returned_orders'], $permissions))
    <a href="{{ route('all_orders.status', 'returned') }}" class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center @if ($currentStatus == 'returned') active @endif">
        Returned
        <span class="w-auto badge badge-primary ml-1" id="returned-count">{{ $deliveryStatusCount['returned'] }}</span>
        {{-- <span class="w-auto ml-1" data-toggle="tooltip" data-placement="top" title="Last Cached at {{ \Carbon\Carbon::parse(Cache::get('order_returned_count_cache_time', now()))->diffForHumans() }}">
            <i class="las la-info-circle text-light fs-16"></i>
        </span> --}}
    </a>
@endif
@if (Auth::user()->user_type == 'admin' || any_in_array(['3', 'cancelled_orders'], $permissions))
    <a href="{{ route('all_orders.status', 'cancelled') }}" class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center @if ($currentStatus == 'cancelled') active @endif">
        Cancelled
        <span class="w-auto badge badge-primary ml-1" id="cancelled-count">{{ $deliveryStatusCount['cancelled'] }}</span>
        {{-- <span class="w-auto ml-1" data-toggle="tooltip" data-placement="top" title="Last Cached at {{ \Carbon\Carbon::parse(Cache::get('order_cancelled_count_cache_time', now()))->diffForHumans() }}">
            <i class="las la-info-circle text-light fs-16"></i>
        </span> --}}
    </a>
@endif
