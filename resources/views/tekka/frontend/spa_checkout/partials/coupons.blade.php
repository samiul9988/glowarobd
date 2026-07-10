<div class="coupon-list {{ $coupons->count() > 0 ? 'mb-4' : '' }} swiper">
    <div class="swiper-wrapper">
        @foreach ($coupons as $coupon)
            @continue(!$coupon->coupon) {{-- Skip if coupon relation is missing --}}
            @php
                $details = $coupon->coupon->details ? json_decode($coupon->coupon->details, true) : [];
            @endphp
            <div class="swiper-slide">
                <div class="discount-card ">
                    <div class="discount-card__sidebar">
                        <span class="discount-card__label">{{ $coupon->coupon->code }}</span>
                    </div>

                    <div class="discount-card__body">
                        <div class="discount-card__inner">
                            <p class="discount-card__amount">
                                @if ($coupon->coupon->discount_type == 'amount')
                                    Tk {{ $coupon->coupon->discount }} OFF
                                @else
                                    {{ $coupon->coupon->discount }}% OFF
                                @endif
                            </p>
                            <p class="discount-card__validity">
                                @if (isset($details['min_buy']) && $details['min_buy'] > 0)
                                    Min. Spend: {{ single_price($details['min_buy']) }}
                                @else
                                    * No Condition
                                @endif
                            </p>
                            <p class="discount-card__validity">
                                Valid {{ \Carbon\Carbon::parse($coupon->expire_date)->format('d M Y') }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <button
                                    class="discount-card__btn {{ $appliedCouponCode === $coupon->coupon->code ? 'applied disabled' : 'apply-coupon' }}"
                                    data-coupon="{{ $coupon->coupon->code }}">
                                    {{ $appliedCouponCode === $coupon->coupon->code ? 'Applied' : 'Apply Code' }}
                                </button>
                                @if(strlen(trim($coupon->coupon->description ?? '')) > 0)
                                    <span class="fs-18 mt-2">
                                        @include('components.tooltip', [
                                            'title' => $coupon->coupon->description,
                                            'position' => 'top',
                                            'animate' => true,
                                        ])
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="discount-card__notch"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@if($coupons->count() > 0)
    <script>
        var swiper = new Swiper(".coupon-list", {
            slidesPerView: 2.5,
            spaceBetween: 10,
            breakpoints: {
                0: {
                    slidesPerView: 1.5,
                    spaceBetween: 15,
                },
                500: {
                    slidesPerView: 2,
                    spaceBetween: 15,
                },
                768: {
                    slidesPerView: 2.5,
                    spaceBetween: 20,
                },
                1000: {
                    slidesPerView: 1.5,
                    spaceBetween: 10,
                },
                1400: {
                    slidesPerView: 2.5,
                    spaceBetween: 20,
                }
            },
        });
    </script>
@endif
