@if (addon_is_activated('club_point'))
    @php
        $total_point = 0;
    @endphp
    @foreach ($carts as $key => $cartItem)
        @php
            $product = $cartItem->product;
            $total_point += $product->earn_point * $cartItem['quantity'];
        @endphp
    @endforeach

    <div class="rounded px-2 mb-2 bg-soft-primary border-soft-primary border">
        {{ ("Total Club point") }}:
        <span class="fw-700 float-right">{{ $total_point }}</span>
    </div>
@endif

@php
    $subtotal = 0;
    $tax = 0;
    $shipping = 0;
    $product_shipping_cost = 0;
    $shipping_region = @$shipping_info['city'];
    $totalSaved = 0;
    $availableCoupons = collect();
    if(Auth::check()){
        $availableCoupons = \App\Models\CouponCustomerAssignment::with('coupon')
            ->where('customer_id', Auth::id())
            ->whereHas('coupon', function($query) {
                $now = now()->timestamp;
                $query->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
                    // ->where('force_apply', 1);
            })
            ->where('expire_date', '>=', now())
            // ->where('is_used', 0)
            ->get();
    }
@endphp
@foreach ($carts as $key => $cartItem)
    @php
        $product = $cartItem->product;
        $product_stock = collect($cartItem->product->stocks)->where('variant', $cartItem['variation'])->first();
        $cartItem['price'] = getMinimumPriceByVariant($product, $product_stock, 'web', $cartItem['quantity'], $currentlyAuthenticatedUser);
        $subtotal += $cartItem['price'] * $cartItem['quantity'];
        $tax += $cartItem['tax'] * $cartItem['quantity'];
        $product_shipping_cost = $cartItem['shipping_cost'];

        $shipping += $product_shipping_cost;

        $product_name_with_choice = $product->getTranslation('name');
        if ($cartItem['variation'] != null) {
            $product_name_with_choice = $product->getTranslation('name').' - '.$cartItem['variation'];
        }

        $savedAmount = (($product->unit_price * $cartItem['quantity']) - ($cartItem['price'] * $cartItem['quantity']));
        $totalSaved = $totalSaved + $savedAmount;

    @endphp
@endforeach

<link rel="stylesheet" href="{{ static_asset('assets/theme22/frontend/css/coupon-card.css') }}">

<table class="table">
    <tfoot>
        <tr class="cart-subtotal">
            <th>{{ ('Subtotal')}}</th>
            <td class="text-right">
                <span class="fw-600">{{ single_price($subtotal) }}</span>
            </td>
        </tr>

        <tr class="cart-shipping">
            <th>{{ ('Tax')}}</th>
            <td class="text-right">
                <span class="font-italic">{{ single_price($tax) }}</span>
            </td>
        </tr>

        <tr class="cart-shipping">
            <th>{{ ('Total Shipping')}}</th>
            <td class="text-right">
                <span class="font-italic">@if($shipping!=0){{ single_price($shipping) }}@else Free @endif</span>
            </td>
        </tr>

        @if (Session::has('club_point'))
            <tr class="cart-shipping">
                <th>{{ ('Redeem point')}}</th>
                <td class="text-right">
                    <span class="font-italic">{{ single_price(Session::get('club_point')) }}</span>
                </td>
            </tr>
        @endif

        @if ($carts->sum('discount') > 0)
            <tr class="cart-shipping">
                <th>{{ ('Coupon Discount')}}</th>
                <td class="text-right">
                    <span class="font-italic">{{ single_price($carts->sum('discount')) }}</span>
                </td>
            </tr>
        @endif

        @if (Session::has('reward_point_discount'))
            <tr class="cart-shipping">
                <th>{{ ('Reward Point Discount')}}</th>
                <td class="text-right">
                    <span class="font-italic">{{ single_price(Session::get('reward_point_discount')) }}</span>
                </td>
            </tr>
        @endif

        @php
            $total = $subtotal+$tax+$shipping;
            if(Session::has('club_point')) {
                $total -= Session::get('club_point');
            }
            if ($carts->sum('discount') > 0){
                $total -= $carts->sum('discount');
            }
            if(Session::has('reward_point_discount')) {
                $total -= Session::get('reward_point_discount');
            }

            $totalSavings = (Session::get('reward_point_discount') + $carts->sum('discount') + Session::get('club_point') + $totalSaved);
        @endphp

        <tr class="cart-total">
            <th><span class="strong-600">{{ ('Total')}}</span></th>
            <td class="text-right">
                <strong><span>{{ single_price($total) }}</span></strong>
            </td>
        </tr>

        @if($totalSavings > 0)
        <tr class="cart-total bg-soft-success">
            <th><span class="strong-600">{{ ('Your Total Savings for This Order ')}}</span></th>
            <td class="text-right">
                <span class="badge badge-success w-auto"><strong>{{ single_price($totalSavings) }}</strong> </span>
            </td>
        </tr>
        @endif
    </tfoot>
</table>

@if (addon_is_activated('club_point'))
    @if (Session::has('club_point'))
        <div class="mt-3">
            <form class="" action="{{ route('checkout.remove_club_point') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="input-group">
                    <div class="form-control">{{ Session::get('club_point')}}</div>
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">{{ ('Remove Redeem Point')}}</button>
                    </div>
                </div>
            </form>
        </div>
    @else
        {{--@if(Auth::user()->point_balance > 0)
            <div class="mt-3">
                <p>
                    {{ ('Your club point is')}}:
                    @if(isset(Auth::user()->point_balance))
                        {{ Auth::user()->point_balance }}
                    @endif
                </p>
                <form class="" action="{{ route('checkout.apply_club_point') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <input type="text" class="form-control" name="point" placeholder="{{ ('Enter club point here')}}" required>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">{{ ('Redeem')}}</button>
                        </div>
                    </div>
                </form>
            </div>
        @endif--}}
    @endif
@endif

@php
    // Filter by min_buy <= $total and usage_limit
    $availableCoupons = $availableCoupons->filter(function ($assignment) use ($total) {
        $limitCheck = true;
        if('single' === strtolower($assignment->coupon->usage_limit)) {
            $limitCheck = (0 === $assignment->is_used);
        }
        $details = json_decode($assignment->coupon->details, true) ?? null;

        return $details
            && isset($details['min_buy'])
            && (int) $details['min_buy'] <= $total
            && $limitCheck;
    });
@endphp

@if($availableCoupons->isNotEmpty() && !@$couponApplied)
<div class="container my-4">
    <h5 class="mb-3 font-weight-bold">🎟 Available Coupons</h5>
    <div class="d-block d-md-flex flex-wrap" style="gap: 1rem;">
        @foreach($availableCoupons as $availableCoupon)
            <div class="coupon-card">
                <div class="coupon-content">
                    <div class="left-section">
                        <span class="discount-text">
                            @if($availableCoupon->coupon->discount_type == 'amount')
                                {{ single_price($availableCoupon->coupon->discount) }}
                            @elseif($availableCoupon->coupon->discount_type == 'percent')
                                {{ $availableCoupon->coupon->discount }}%
                            @endif
                        </span>
                        <span class="off-text">OFF</span>
                    </div>

                    <div class="vertical-divider"></div>

                    <div class="right-section">
                        <div class="right-text">
                            <div class="days-left">
                                {{ \Carbon\Carbon::parse($availableCoupon->expire_date)->diffInDays() }} Days Left
                            </div>
                            <div class="valid-until">Valid Until {{ \Carbon\Carbon::parse($availableCoupon->expire_date)->format('d M Y') }}</div>
                        </div>
                        <div data-code="{{ $availableCoupon->coupon->code }}" class="coupon-arrow"
                            role="button"
                            @click="applyCoupon($event.target, '{{ $availableCoupon->coupon->code }}')"
                            >Apply</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
@if (Auth::check() && get_setting('coupon_system') == 1)
    @if ($carts[0]['discount'] > 0)
        <div class="mt-3">
            <form class="" id="remove-coupon-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="owner_id" value="{{ $carts[0]['owner_id'] }}">
                <div class="input-group">
                    <div class="form-control">{{ $carts[0]['coupon_code'] }}</div>
                    <div class="input-group-append">
                        <button type="button" id="coupon-remove" @click="removeCoupon($event.target)" class="btn btn-primary">{{ ('Remove Coupon')}}</button>
                    </div>
                </div>
            </form>
            @if(!empty($response_message))
                <div class="text-{{$response_message['response']}} fw-700 mt-2 text-center">{{ $response_message['message'] }}</div>
            @elseif(isset($carts[0]['owner_id']))
                <div class="text-success mt-2 fw-700 text-center">Coupon Successfully Applied</div>
            @endif
        </div>
    @else
        <div class="mt-3">
            <form class="" id="apply-coupon-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="owner_id" value="{{ $carts[0]['owner_id'] }}">
                <div class="input-group">
                    <input type="text" class="form-control" name="code" x-model="coupon_code" onkeydown="return event.key != 'Enter';" placeholder="{{ ('Have coupon code? Enter here')}}" required>
                    <div class="input-group-append">
                        <button type="button" id="coupon-apply" @click="applyCoupon($event.target)" class="btn btn-primary">{{ ('Apply')}}</button>
                    </div>
                </div>
            </form>
            @if(!empty($response_message))
            <div class="text-{{$response_message['response']}} fw-700 text-center mt-2">{{ $response_message['message'] }}</div>
            @endif
        </div>
    @endif
@endif

@if (Auth::check() && get_setting('reward_point_system') == 1)
    <hr />
    @if (Session::has('reward_point_discount'))
    <div class="mt-3">
        <form class="" id="remove-reward-point-form" enctype="multipart/form-data">
            @csrf
            <div class="input-group">
                <div class="form-control">{{ Session::get('applied_reward_point') }}</div>
                <div class="input-group-append">
                    <button type="button" id="remove-reward-point" @click="removeRewardPoint($event.target)" class="btn btn-primary">{{ ('Remove Redeem Point')}}</button>
                </div>
            </div>
        </form>
        <div class="text-success mt-2">Point has been redeemed!</div>
    </div>
    @else
    <div class="mt-3">
        <form class="" id="apply-reward-point-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="redeem_reward_type" value="checkout">
            <h6>Use Reward Points</h6>
            <p>You have <span class="font-weight-bold text-danger">{{ auth()->user()->point_balance }} Reward Points</span> available</p>
            <div class="input-group">
                <input type="text" max="{{auth()->user()->point_balance}}" id="reawrd_point" class="form-control" name="point" onkeydown="return event.key != 'Enter';" placeholder="{{ ('Enter amount of points to spend')}}" required>
                <div class="input-group-append">
                    <button type="button" id="apply-reward-point" @click="redeemRewardPoint($event.target)" class="btn btn-primary">{{ ('Redeem')}}</button>
                </div>
            </div>
            <div class="my-2 form-check">
                <input class="form-check-input" type="radio" name="availablePoint" @change="applyMaxRewardPoints($event.target)" id="availablePoint" value="{{ auth()->user()->point_balance }}">
                <label class="form-check-label mt-1" for="availablePoint">
                    Use maximum <span class="font-weight-bold text-danger">{{ auth()->user()->point_balance }} Reward Points</span>
                </label>
            </div>
        </form>
        @if(!empty($response_message) && $response_message['type'] == 'reward_point')
            <div class="text-{{$response_message['response']}} mt-2">{{ $response_message['message'] }}</div>
        @endif
    </div>
    @endif
@endif
