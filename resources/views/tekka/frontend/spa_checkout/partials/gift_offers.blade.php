<div class="gift-offers-wrapper">
    <div class="gift-offers-header" id="giftOffersHeader">
        <div class="gift-offers-title">
            <i class="fas fa-gift" style="animation: 1.5s ease 0s infinite normal none running heartbeat;"></i>
            <span>Gift Offers</span>
            <span class="gift-offers-count">({{ $offers->count() + $invalidOffers->count() }})</span>
        </div>
        <i class="fas fa-chevron-up"></i>
    </div>
    @php
        $giftCarts = $carts->where('cart_type', '!=', 'regular');
        $giftCartProducts = $giftCarts->pluck('product_id')->unique()->toArray();
        $giftCartOffers = $giftCarts->pluck('gift_offer_id')->unique()->toArray();
        $giftCartOfferItems = $giftCarts->pluck('gift_offer_item_id')->unique()->toArray();
    @endphp
    <div class="gift-offers-content" id="giftOffersContent">
        @foreach ($offers as $offer)
            @php
                $offerCardClass = '';
                $disableItems = false;
                if (!empty($giftCartOffers)) {
                    $offerCardClass = in_array(data_get($offer, 'id'), $giftCartOffers) ? 'offer-card--selected' : 'offer-card--disabled';
                    $disableItems = !in_array(data_get($offer, 'id'), $giftCartOffers) ? true : false;
                }
            @endphp
            <div class="offers-section {{ $loop->index >= 2 ? 'offer-hidden' : '' }}">
                <div class="offer-card {{ $offerCardClass }}" data-offer-id="{{ data_get($offer, 'id') }}">
                    <div class="offer-card-header">
                        <div class="offer-info">
                            <h6 class="offer-title">
                                <i class="las la-gift"></i> {{ data_get($offer, 'title') }}
                            </h6>
                            @if (strlen(data_get($offer, 'description', '')) > 0)
                                <p class="offer-desc">
                                    {{ data_get($offer, 'description', '') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="gift-items-section">
                        <div class="gift-items-label">Available Gifts:</div>
                        <div class="gift-items-list">
                            @foreach (data_get($offer, 'items', []) as $item)
                                <div class="gift-item-row" @if(!$disableItems) data-item-id="{{ data_get($item, 'id') }}" @endif>
                                    <div class="gift-item-img-wrap">
                                        <img src="{{ data_get($item, 'product_thumbnail_url') }}" data-src="{{ data_get($item, 'product_thumbnail_url') }}" class="gift-item-img"
                                            alt="{{ data_get($item, 'product_name') }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                    </div>
                                    <div class="gift-item-info">
                                        <div class="gift-item-name" title="{{ data_get($item, 'product_name') }}">
                                            {{ data_get($item, 'product_name') }}
                                        </div>
                                        <div class="gift-item-pricing">
                                            <span class="gift-item-offer-price">
                                                {{ data_get($item, 'offer_price', 0) == 0 ? 'FREE' : single_price(data_get($item, 'offer_price', 0)) }}
                                            </span>
                                            <del class="gift-item-regular-price">
                                                {{ single_price(data_get($item, 'original_price', 0)) }}
                                            </del>
                                        </div>
                                        <div class="gift-item-qty">Remaining: <span>{{ data_get($item, 'available_qty', 0) }}</span></div>
                                    </div>
                                    @if(data_get($offer, 'max_item_per_order', 0) < count($giftCartProducts) || in_array(data_get($item, 'product_id'), $giftCartProducts) || count($giftCartProducts) == 0)
                                        <button type="button" class="gift-item-btn {{ in_array(data_get($item, 'product_id'), $giftCartProducts) ? 'gift-item-btn--selected' : '' }} {{ $disableItems ? 'gift-item-btn--disabled' : 'gift-item-btn-ac' }}"
                                            {{ in_array(data_get($item, 'product_id'), $giftCartProducts) ? 'disabled' : '' }}>
                                            @if (in_array(data_get($item, 'product_id'), $giftCartProducts))
                                                <span>✓ Selected</span>
                                            @else
                                                <span>Select</span>
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @foreach ($invalidOffers as $offer)
            @php
                $hidden = false;
                if ($loop->index >= 2 || $offers->count() >= 2) {
                    $hidden = true;
                }
            @endphp
            <div class="offers-section {{ $hidden ? 'offer-hidden' : '' }}">
                <div class="offer-card offer-card--disabled">
                    <div class="offer-card-header">
                        <div class="offer-info">
                            <h6 class="offer-title">
                                <i class="las la-gift"></i> {{ data_get($offer, 'title') }}
                            </h6>
                            @if (strlen(data_get($offer, 'description', '')) > 0)
                                <p class="offer-desc">
                                    {{ data_get($offer, 'description', '') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="gift-items-section">
                        <div class="gift-items-label">Available Gifts:</div>
                        <div class="gift-items-list">
                            @foreach (data_get($offer, 'items', []) as $item)
                                <div class="gift-item-row">
                                    <div class="gift-item-img-wrap">
                                        <img src="{{ data_get($item, 'product_thumbnail_url') }}" data-src="{{ data_get($item, 'product_thumbnail_url') }}" class="gift-item-img"
                                            alt="{{ data_get($item, 'product_name') }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                    </div>
                                    <div class="gift-item-info">
                                        <div class="gift-item-name" title="{{ data_get($item, 'product_name') }}">
                                            {{ data_get($item, 'product_name') }}
                                        </div>
                                        <div class="gift-item-pricing">
                                            <span class="gift-item-offer-price">
                                                {{ data_get($item, 'offer_price', 0) == 0 ? 'FREE' : single_price(data_get($item, 'offer_price', 0)) }}
                                            </span>
                                            <del class="gift-item-regular-price">
                                                {{ single_price(data_get($item, 'original_price', 0)) }}
                                            </del>
                                        </div>
                                        <div class="gift-item-qty">Remaining: <span>{{ data_get($item, 'available_qty', 0) }}</span></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if ($offers->count() + $invalidOffers->count() > 2)
            <div class="px-2 mb-3">
                <button class="btn btn-dark mt-3 show-all-offers-btn w-100" style="border-radius: 10px !important;" onclick="document.querySelectorAll('.offer-hidden').forEach(c =&gt; c.classList.remove('offer-hidden')); this.style.display = 'none';">
                    Show All Offers ({{ $offers->count() + $invalidOffers->count() - 2 }})
                </button>
            </div>
        @endif
    </div>
</div>
