@php
    $firstPayment = true;
@endphp

<div class="payment-grid">
    @if (get_setting('cash_payment') == 1)
        <div class="payment-option {{ $firstPayment ? 'selected' : '' }}"
            onclick="this.querySelector('input').checked = true; document.querySelectorAll('.payment-option').forEach(c => c.classList.remove('selected')); this.classList.add('selected');">
            <input value="cash_on_delivery" class="online_payment" type="radio" name="payment_option"
                {{ $firstPayment ? 'checked' : '' }}>
            <div class="payment-icon">
                <img src="{{ static_asset('assets/img/cards/cod.png') }}" alt="Cash on Delivery">
            </div>
            <div class="payment-name">Cash on Delivery</div>
            <div class="payment-check">
                <i class="las la-check"></i>
            </div>
        </div>
        @php $firstPayment = false; @endphp
    @endif

    @if (get_setting('sslcommerz_payment') == 1)
        <div class="payment-option {{ $firstPayment ? 'selected' : '' }}"
            onclick="this.querySelector('input').checked = true; document.querySelectorAll('.payment-option').forEach(c => c.classList.remove('selected')); this.classList.add('selected');">
            <input value="sslcommerz" class="online_payment" type="radio" name="payment_option"
                {{ $firstPayment ? 'checked' : '' }}>
            <div class="payment-icon">
                <img src="{{ static_asset('assets/img/cards/sslcommerz.png') }}" alt="SSLCommerz">
            </div>
            <div class="payment-name">SSLCommerz</div>
            <div class="payment-check">
                <i class="las la-check"></i>
            </div>
        </div>
        @php $firstPayment = false; @endphp
    @endif

    @if (get_setting('bkash') == 1)
        <div class="payment-option {{ $firstPayment ? 'selected' : '' }}"
            onclick="this.querySelector('input').checked = true; document.querySelectorAll('.payment-option').forEach(c => c.classList.remove('selected')); this.classList.add('selected');">
            <input value="bkash" class="online_payment" type="radio" name="payment_option"
                {{ $firstPayment ? 'checked' : '' }}>
            <div class="payment-icon">
                <img src="{{ static_asset('assets/img/cards/bkash-trans.png') }}" alt="bKash">
            </div>
            <div class="payment-name">bKash</div>
            <div class="payment-check">
                <i class="las la-check"></i>
            </div>
        </div>
        @php $firstPayment = false; @endphp
    @endif

    @if (get_setting('nagad') == 1)
        <div class="payment-option {{ $firstPayment ? 'selected' : '' }}"
            onclick="this.querySelector('input').checked = true; document.querySelectorAll('.payment-option').forEach(c => c.classList.remove('selected')); this.classList.add('selected');">
            <input value="nagad" class="online_payment" type="radio" name="payment_option"
                {{ $firstPayment ? 'checked' : '' }}>
            <div class="payment-icon">
                <img src="{{ static_asset('assets/img/cards/nagad.png') }}" alt="Nagad">
            </div>
            <div class="payment-name">Nagad</div>
            <div class="payment-check">
                <i class="las la-check"></i>
            </div>
        </div>
        @php $firstPayment = false; @endphp
    @endif

    @if (get_setting('aamarpay') == 1)
        <div class="payment-option {{ $firstPayment ? 'selected' : '' }}"
            onclick="this.querySelector('input').checked = true; document.querySelectorAll('.payment-option').forEach(c => c.classList.remove('selected')); this.classList.add('selected');">
            <input value="aamarpay" class="online_payment" type="radio" name="payment_option"
                {{ $firstPayment ? 'checked' : '' }}>
            <div class="payment-icon">
                <img src="{{ static_asset('assets/img/cards/aamarpay.png') }}" alt="Aamarpay">
            </div>
            <div class="payment-name">Aamarpay</div>
            <div class="payment-check">
                <i class="las la-check"></i>
            </div>
        </div>
        @php $firstPayment = false; @endphp
    @endif
</div>

@if (Auth::check() && get_setting('wallet_system') == 1)
    <div class="wallet-section">
        <div class="wallet-divider">
            <span>Or Pay With Wallet</span>
        </div>
        <div class="wallet-card">
            <div class="wallet-balance-label">Your Wallet Balance</div>
            <div class="wallet-balance-amount">{{ single_price(Auth::user()->balance) }}</div>
            @if (Auth::user()->balance < $total)
                <button type="button" class="btn-wallet-pay disabled" disabled>
                    <i class="las la-wallet"></i>
                    Insufficient Balance
                </button>
            @else
                <button type="button" class="btn-wallet-pay active" @click="use_wallet($event.target)">
                    <i class="las la-wallet"></i>
                    Pay with Wallet
                </button>
            @endif
        </div>
    </div>
@endif
