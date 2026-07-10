<div class="address-card {{ @$selected ? 'selected' : '' }} {{ @$hidden ? 'address-hidden' : '' }}" onclick="this.querySelector('input').checked = true; document.querySelectorAll('.address-card').forEach(c => c.classList.remove('selected')); this.classList.add('selected');">
    <input type="radio" name="address_id" value="{{ $address->id }}">
    <div class="address-radio-indicator"></div>
    <span class="address-type-badge {{ strtolower($address->address_type) }}">
        <i class="las la-{{ strtolower($address->address_type) == 'home' ? 'home' : (strtolower($address->address_type) == 'office' ? 'building' : 'map-pin') }}"></i>
        {{ $address->address_type }}
    </span>
    <div class="address-name">
        {{ $address->name }}
        <span class="fs-12 text-muted">
            <i class="las la-phone"></i>
            {{ $address->phone }}
        </span>
    </div>
    <div class="address-text">{{ $address->address }}</div>
    <div class="address-location">
        <span class="location-tag">
            <i class="las la-map"></i>
            {{ $address->state->name }}
        </span>
        <span class="location-tag">
            <i class="las la-city"></i>
            {{ $address->city->name }}
        </span>
        <span class="location-tag">
            <i class="las la-map-marker"></i>
            {{ $address->area->name }}
        </span>
    </div>
    <button type="button" class="address-edit-btn" data-address="{{ json_encode($address) }}">
        <i class="las la-pen"></i>
    </button>
</div>
