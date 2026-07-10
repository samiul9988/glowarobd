@php
    $role = Auth::user()->staff?->role?->getTranslation('name') ?? '';
@endphp
@if ($order->isLocked())
    <span role="button" class="btn btn-soft-secondary btn-icon btn-circle btn-sm"
        title="{{ ('Locked by ' . $order->lockedBy->name) }}">
        <i class="las la-lock"></i>
    </span>
@endif
@if ((!$order->isLocked() || $order->locked_by == Auth::user()->id) && $currentStatus != 'packaging')
    <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('all_orders.show', encrypt($order->id)) }}"
        title="{{ ('View') }}">
        <i class="las la-eye"></i>
    </a>
@endif
@if ($order->pendingReturnRequest)
    <a href="{{ route('return-orders.show', encrypt($order->pendingReturnRequest->id)) }}" class="btn btn-soft-danger btn-icon btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="This order has a pending return request. You cannot take any action on this order until the return request is resolved">
        <i class="las la-info-circle"></i>
    </a>
@else
    @if ((!$order->isLocked() || $order->locked_by == Auth::user()->id) && (Auth::user()->user_type == 'admin' || in_array('processing_orders', json_decode(Auth::user()->staff?->role?->permissions ?? '[]') ?? [])) && $currentStatus == 'processing' && !hasGiftItem($order))
        <a class="btn btn-soft-success btn-icon btn-circle btn-sm" href="{{ route('invoice.edit', $order->id) }}"
            title="{{ ('Edit') }}">
            <i class="las la-edit"></i>
        </a>
    @endif
    @if ($currentStatus == 'packaging' && (!$order->isLocked() || $order->locked_by == Auth::user()->id) && (Auth::user()->user_type == 'admin' || in_array('packaging_orders', json_decode(Auth::user()->staff?->role?->permissions ?? '[]') ?? [])))
        <a class="btn btn-soft-success btn-icon btn-circle btn-sm" href="{{ route('all_orders.package', $order->id) }}"
            title="{{ ('Package') }}">
            <i class="las la-box"></i>
        </a>
    @endif
@endif

<a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ route('invoice.download', $order->id) }}"
    title="{{ ('Download Invoice') }}" target="_blank">
    <i class="las la-download"></i>
</a>
