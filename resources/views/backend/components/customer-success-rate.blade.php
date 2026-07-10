@php
    $isset = @$successRatio ?? false;
    if($isset && @$order){
        $shippingInfo = json_decode($order->shipping_address, true) ?? [];
        $phone = data_get($shippingInfo, 'phone', '');
        $successRatio = get_customer_success_rate($order->user_id, $phone);
    }
    if ($successRatio['success_rate'] == 100) {
        $class = 'success';
    } elseif ($successRatio['success_rate'] == 0) {
        $class = 'danger';
    } else {
        $class =
            $successRatio['success_rate'] <= 25
                ? 'success'
                : ($successRatio['success_rate'] < 100
                    ? 'warning'
                    : 'danger');
    }
@endphp
<div class="card p-3 mb-3">
    <h6 class="mb-2 fs-11 d-flex justify-content-between align-items-center">
        <span class="font-weight-bold fs-20">
            {{ data_get($successRatio, 'label') }}
            @if (data_get($successRatio, 'message'))
                | <span class="text-{{ $class }}">({{ data_get($successRatio, 'message') }})</span>
            @endif
        </span>
        @if (data_get($successRatio, 'summary', []))
            <span class="success-rate-summary rounded rounded-circle border border-info" title="View Summary" data-toggle="tooltip" class="badge badge-info" role="button" data-summary="{{ json_encode(data_get($successRatio, 'summary')) }}" style="animation: heartbeat 1.5s infinite;">
                <i class="las la-info text-info fs-20"></i>
            </span>
        @endif
    </h6>
    <div class="row align-items-center px-0 mx-0 mb-2">
        <div class="progress mb-0 col-11 px-0" style="height: 20px;">
            <div class="progress-bar bg-{{ $class }}" role="progressbar"
                style="width: {{ $successRatio['success_rate'] }}%;" aria-valuenow="{{ $successRatio['success_rate'] }}"
                aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
        <div class="col px-0 text-right font-weight-bold text-{{ $class }}">
            {{ $successRatio['success_rate'] }}%
        </div>
    </div>

    <div class="row text-center mt-2">
        <div class="col-md-4">
            <div class="p-2 rounded" style="background-color: #fff3cd;">
                <div class="font-weight-bold">{{ $successRatio['total_orders'] }}</div>
                <small>Processed</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-2 rounded" style="background-color: #d4edda;">
                <div class="font-weight-bold">{{ $successRatio['delivered_orders'] }}</div>
                <small>Delivered</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-2 rounded" style="background-color: #e2e3e5;">
                <div class="font-weight-bold">{{ $successRatio['returned_orders'] }}</div>
                <small>Returned</small>
            </div>
        </div>
    </div>
</div>
