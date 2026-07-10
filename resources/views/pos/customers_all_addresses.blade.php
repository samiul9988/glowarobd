<div class="alert alert-info">
    {{ count($addresses) . ' addresses found for this phone number.' }}
</div>
<ul class="list-group">
    @foreach ($addresses as $address)
        <li class="list-group-item mb-3 rounded border">
            <div class="d-flex justify-content-between align-items-start">
                <div role="button" data-info="{{ json_encode($address) }}" class="address-info w-100">
                    <h6 class="mb-2 fw-bold">Name: {{ $address['name'] }}</h6>
                    <p class="mb-1 text-muted">
                        <i class="bi bi-geo-alt-fill me-2"></i>
                        {{ $address['address'] }}
                    </p>
                    <p class="mb-0 small text-secondary">
                        <i class="bi bi-pin-map-fill me-2"></i>
                        {{ $address['state'] }}, {{ $address['city'] }}, {{ $address['area'] }}
                    </p>
                </div>
            </div>
        </li>
    @endforeach
</ul>
