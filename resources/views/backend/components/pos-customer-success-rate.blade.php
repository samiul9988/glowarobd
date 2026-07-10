@php
    $successRate = Session::get('pos.success_rate', [
        'total' => 0,
        'delivered' => 0,
        'returned' => 0,
        'ratio' => 0,
    ]);
    if ($successRate['ratio'] == 100) {
        $class = 'success';
    } elseif ($successRate['ratio'] == 0) {
        $class = 'danger';
    } else {
        $class =
            $successRate['ratio'] <= 25
                ? 'success'
                : ($successRate['ratio'] < 100
                    ? 'warning'
                    : 'danger');
    }
@endphp
<div class="card p-3 mb-3">
    <h6 class="mb-2 fs-11">Customer Success Rate @if ($successRate['message'] ?? null)
            | <span class="text-{{ $class }}">({{ $successRate['message'] }})</span>
        @endif
    </h6>
    <div class="row align-items-center px-0 mx-0 mb-2">
        <div class="progress mb-0 col-11 px-0" style="height: 20px;">
            <div class="progress-bar bg-{{ $class }}" role="progressbar"
                style="width: {{ $successRate['ratio'] }}%;" aria-valuenow="{{ $successRate['ratio'] }}"
                aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
        <div class="col px-0 text-right font-weight-bold text-{{ $class }}">
            {{ $successRate['ratio'] }}%
        </div>
    </div>

    <div class="row text-center mt-2">
        <div class="col-md-4">
            <div class="p-2 rounded" style="background-color: #fff3cd;">
                <div class="font-weight-bold">{{ $successRate['total'] }}</div>
                <small>Processed</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-2 rounded" style="background-color: #d4edda;">
                <div class="font-weight-bold">{{ $successRate['delivered'] }}</div>
                <small>Delivered</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-2 rounded" style="background-color: #e2e3e5;">
                <div class="font-weight-bold">{{ $successRate['returned'] }}</div>
                <small>Returned</small>
            </div>
        </div>
    </div>
</div>
