<!-- Coupon -->
<div class="coupon-section">
    <div class="coupon-input-group">
        <input type="text" placeholder="Enter coupon code" id="coupon_code" value="{{ $coupon ?? '' }}" {{ isset($coupon) ? 'readonly disabled' : '' }}>
        <button type="button" class="btn-apply-coupon" data-action="{{ isset($coupon) ? 'remove' : 'apply' }}">
            @if (isset($coupon))
                Remove
            @else
                Apply
            @endif
        </button>
    </div>
</div>

<div class="summary-calculations">
    <div class="calc-row">
        <span class="label">Subtotal</span>
        <span class="value">{{ single_price($subtotal ?? 0) }}</span>
    </div>
    @if ($giftOfferTotal > 0)
        <div class="calc-row">
            <span class="label">Gift Offer</span>
            <span class="value">{{ single_price($giftOfferTotal ?? 0) }}</span>
        </div>
    @endif
    <div class="calc-row">
        <span class="label">Shipping</span>
        <span class="value">{{ single_price($cartShippingCharge ?? 0) }}</span>
    </div>
    @if (isset($discount) && $discount > 0)
        <div class="calc-row discount">
            <span class="label">
                Discount @if (isset($coupon))
                    <small class="text-success">({{ $coupon }})</small>
                @endif
            </span>
            <span class="value">-{{ single_price($discount) }}</span>
        </div>
    @endif
    <div class="calc-row">
        <span class="label">Tax</span>
        <span class="value">{{ single_price($tax ?? 0) }}</span>
    </div>
    <div class="calc-row total">
        <span class="label">Total</span>
        <span class="value">{{ single_price($total ?? 0) }}</span>
    </div>
</div>

@if(isset($totalSavings) && $totalSavings > 0)
<div class="new-address-form">
    <div class="fw-500 text-center fs-16">
        <strong>
            {{ single_price($totalSavings, 2) }}
        </strong>
        Total Savings For This Order
    </div>
</div>
@endif
