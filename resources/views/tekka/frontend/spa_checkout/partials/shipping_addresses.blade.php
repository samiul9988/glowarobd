<div class="section-header">
    <h2 class="section-title">
        <span class="section-icon d-none d-md-flex">
            <i class="las la-map-marker-alt"></i>
        </span>
        Shipping Address
    </h2>
    @if (Auth::check() && $addresses->count() > 0)
        <button class="btn-add-address">
            <i class="las la-plus d-none d-md-block"></i>
            Add New
        </button>
    @endif
</div>
<div class="section-body">
    <div class="address-grid">
        @if ($selectedAddress)
            @include(config('app.theme') . 'frontend.spa_checkout.partials.address_card', [
                'address' => $selectedAddress,
                'selected' => true,
            ])
        @endif

        @php
            $othersAddresses = $selectedAddress ? $addresses->where('id', '!=', $selectedAddress->id) : $addresses;
            $visibleAddressesCount = $selectedAddress ? 1 : 2; // Show 1 address if one is selected, otherwise show 2 addresses
        @endphp
        @if (!$selectedAddress && $othersAddresses->count() == 0)
            @include(config('app.theme') . 'frontend.spa_checkout.partials.address_form')
        @else
            @foreach ($othersAddresses as $address)
                @if ($loop->index == $visibleAddressesCount && $othersAddresses->count() > 0)
                    <button class="btn btn-dark mt-3 show-all-addresses-btn" style="border-radius: 10px !important;" onclick="document.querySelectorAll('.address-hidden').forEach(c => c.classList.remove('address-hidden')); this.style.display = 'none';">
                        Show All Addresses ({{ $othersAddresses->count() - $visibleAddressesCount }})
                    </button>
                @endif
                @include(config('app.theme') . 'frontend.spa_checkout.partials.address_card', [
                    'address' => $address,
                    'selected' => !$selectedAddress && $loop->first, // Select the first address if no address is selected
                    'hidden' => $loop->index >= $visibleAddressesCount, // Hide addresses after the visible count
                ])
            @endforeach
        @endif

        {{-- @each(config('app.theme') . 'frontend.spa_checkout.partials.address_card', $othersAddresses, 'address', config('app.theme') . 'frontend.spa_checkout.partials.address_form') --}}
    </div>
</div>
