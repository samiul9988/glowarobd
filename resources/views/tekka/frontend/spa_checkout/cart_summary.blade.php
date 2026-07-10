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
        {{ 'Total Club point' }}:
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
@endphp
@foreach ($carts as $key => $cartItem)
    @php
        $product = $cartItem->product;
        $product_stock = collect($cartItem->product->stocks)->where('variant', $cartItem['variation'])->first();
        $cartItem['price'] = getMinimumPriceByVariant(
            $product,
            $product_stock,
            'web',
            $cartItem['quantity'],
            $currentlyAuthenticatedUser,
        );
        $subtotal += $cartItem['price'] * $cartItem['quantity'];
        $tax += $cartItem['tax'] * $cartItem['quantity'];
        $product_shipping_cost = $cartItem['shipping_cost'];

        $shipping += $product_shipping_cost;

        $product_name_with_choice = $product->getTranslation('name');
        if ($cartItem['variation'] != null) {
            $product_name_with_choice = $product->getTranslation('name') . ' - ' . $cartItem['variation'];
        }

        $savedAmount = $product->unit_price * $cartItem['quantity'] - $cartItem['price'] * $cartItem['quantity'];
        $totalSaved = $totalSaved + $savedAmount;

    @endphp
@endforeach

@if (Auth::check() && get_setting('coupon_system') == 1)
    @if ($carts[0]['discount'] > 0)
        <div class="mt-3 coupon-form">
            <form class="" id="remove-coupon-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="owner_id" value="{{ $carts[0]['owner_id'] }}">
                <div class="input-group">
                    <div class="form-control">{{ $carts[0]['coupon_code'] }}</div>
                    <div class="input-group-append">
                        <button type="button" id="coupon-remove" @click="removeCoupon($event.target)"
                            class="btn ">{{ 'Remove Coupon' }}</button>
                    </div>
                </div>
            </form>
            @if (!empty($response_message))
                <div class="text-{{ $response_message['response'] }} fw-700 mt-2 text-center">
                    {{ $response_message['message'] }}</div>
            @elseif(isset($carts[0]['owner_id']))
                <div class="text-success mt-2 fw-700 text-center">Coupon Successfully Applied</div>
            @endif
        </div>
    @else
        <div class="mt-3 coupon-form">
            <form class="" id="apply-coupon-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="owner_id" value="{{ $carts[0]['owner_id'] }}">
                <div class="input-group">
                    <input type="text" class="form-control" name="code" x-model="coupon_code"
                        onkeydown="return event.key != 'Enter';" placeholder="{{ 'Have coupon code? Enter here' }}"
                        required>
                    <div class="input-group-append">
                        <button type="button" id="coupon-apply" @click="applyCoupon($event.target)"
                            class="btn ">{{ 'Apply' }}</button>
                    </div>
                </div>
            </form>
            @if (!empty($response_message))
                <div class="text-{{ $response_message['response'] }} fw-700 text-center mt-2">
                    {{ $response_message['message'] }}</div>
            @endif
        </div>
    @endif
@endif
@if (Auth::check() && get_setting('reward_point_system') == 1 && auth()->user()->point_balance > 0)
    <div class="row mx-0 py-2 mt-2">
        <a class="text-underline fs-14 fw-400 text-dark" href="javascript:void(0)">Apply Rewards points</a>
    </div>
@endif
<table class="table my-3">
    <tfoot>
        <tr class="cart-subtotal fs-16 fw-400">
            <th class="fw-400 px-0 py-3 ">{{ 'Subtotal' }}</th>
            <td class="text-right px-0 py-3 ">
                <span class="fw-600" id="cart-subtotal">{{ single_price($subtotal) }}</span>
            </td>
        </tr>

        <tr class="cart-shipping fs-16 fw-400">
            <th class="fw-400 px-0 py-3 ">{{ 'Tax' }}</th>
            <td class="text-right px-0 py-3 ">
                <span class="" id="cart-tax">{{ single_price($tax) }}</span>
            </td>
        </tr>

        <tr class="cart-shipping fs-16 fw-400">
            <th class="fw-400 px-0 py-3 ">{{ 'Total Shipping' }}</th>
            <td class="text-right px-0 py-3 ">
                @if ($shipping != 0)
                    <span class="" id="cart-shipping">{{ single_price($shipping) }}</span>
                @else
                    <span class="text-success" id="cart-shipping"><strong>Free</strong></span>
                @endif
            </td>
        </tr>

        @if (Session::has('club_point'))
            <tr class="cart-shipping fs-16 fw-400">
                <th class="fw-400 px-0 py-3 ">{{ 'Redeem point' }}</th>
                <td class="text-right px-0 py-3 ">
                    <span class="">{{ single_price(Session::get('club_point')) }}</span>
                </td>
            </tr>
        @endif

        @if ($carts->sum('discount') > 0)
            <tr class="cart-shipping fs-16 fw-400">
                <th class="fw-400 px-0 py-3 ">{{ 'Coupon Discount' }}</th>
                <td class="text-right px-0 py-3 ">
                    <span class="" id="cart-discount">{{ single_price($carts->sum('discount')) }}</span>
                </td>
            </tr>
        @endif

        @if (Session::has('reward_point_discount'))
            <tr class="cart-shipping fs-16 fw-400">
                <th class="fw-400 px-0 py-3 ">{{ 'Reward Point Discount' }}</th>
                <td class="text-right px-0 py-3 ">
                    <span class="">{{ single_price(Session::get('reward_point_discount')) }}</span>
                </td>
            </tr>
        @endif

        @php
            $total = $subtotal + $tax + $shipping;
            if (Session::has('club_point')) {
                $total -= Session::get('club_point');
            }
            if ($carts->sum('discount') > 0) {
                $total -= $carts->sum('discount');
            }
            if (Session::has('reward_point_discount')) {
                $total -= Session::get('reward_point_discount');
            }

            $totalSavings =
                Session::get('reward_point_discount') +
                $carts->sum('discount') +
                Session::get('club_point') +
                $totalSaved;
        @endphp

        <tr class="cart-total fs-18 fw-700  ">
            <th class="px-0 pt-2 pb-1 pb-md-3"><span class="">{{ 'Total' }}</span></th>
            <td class="text-right px-0 pt-2 pb-1 pb-md-3">
                <strong><span id="cart-total">{{ single_price($total) }}</span></strong>
            </td>
        </tr>

        @if ($totalSavings > 0)
            <tr class="total-saving fw-500 text-center fs-16">
                <td colspan="5 my-3">
                    <strong id="cart-total-savings">
                        {{ single_price($totalSavings) }}
                    </strong>
                    {{ 'Total Savings for This Order ' }}
                </td>
            </tr>
        @endif
    </tfoot>
</table>

@if (addon_is_activated('club_point'))
    @if (Session::has('club_point'))
        <div class="mt-3">
            <form class="" action="{{ route('checkout.remove_club_point') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="input-group">
                    <div class="form-control">{{ Session::get('club_point') }}</div>
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">{{ 'Remove Redeem Point' }}</button>
                    </div>
                </div>
            </form>
        </div>
    @else
        {{-- @if (Auth::user()->point_balance > 0)
            <div class="mt-3">
                <p>
                    {{ ('Your club point is')}}:
                    @if (isset(Auth::user()->point_balance))
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
        @endif --}}
    @endif
@endif


@if (Auth::check() && get_setting('reward_point_system') == 1 && auth()->user()->point_balance > 0)
    <hr />
    @if (Session::has('reward_point_discount'))
        <div class="mt-3">
            <form class="" id="remove-reward-point-form" enctype="multipart/form-data">
                @csrf
                <div class="input-group">
                    <div class="form-control">{{ Session::get('applied_reward_point') }}</div>
                    <div class="input-group-append">
                        <button type="button" id="remove-reward-point" @click="removeRewardPoint($event.target)"
                            class="btn btn-primary">{{ 'Remove Redeem Point' }}</button>
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
                <p>You have <span class="font-weight-bold text-danger">{{ auth()->user()->point_balance }} Reward
                        Points</span> available</p>
                <div class="input-group">
                    <input type="text" max="{{ auth()->user()->point_balance }}" id="reawrd_point"
                        class="form-control" name="point" onkeydown="return event.key != 'Enter';"
                        placeholder="{{ 'Enter amount of points to spend' }}" required>
                    <div class="input-group-append">
                        <button type="button" id="apply-reward-point" @click="redeemRewardPoint($event.target)"
                            class="btn btn-primary">{{ 'Redeem' }}</button>
                    </div>
                </div>
                <div class="my-2 form-check">
                    <input class="form-check-input" type="radio" name="availablePoint"
                        @change="applyMaxRewardPoints($event.target)" id="availablePoint"
                        value="{{ auth()->user()->point_balance }}">
                    <label class="form-check-label mt-1" for="availablePoint">
                        Use maximum <span class="font-weight-bold text-danger">{{ auth()->user()->point_balance }}
                            Reward Points</span>
                    </label>
                </div>
            </form>
            @if (!empty($response_message) && $response_message['type'] == 'reward_point')
                <div class="text-{{ $response_message['response'] }} mt-2">{{ $response_message['message'] }}</div>
            @endif
        </div>
    @endif
@endif
<!-- place order -->
<div class="d-none d-lg-block ">
    <div class="form-group form-check d-flex" style="align-items: flex-start; gap: 4px;">
        <input type="checkbox" class="form-check-input aiz-checkbox" name="agree" id="agree_checkbox_" required>
        <label class="form-check-label fw-400 fs-14" for="agree_checkbox_">
            <span>
                I agree to the
                <a class="fw-600 text-dark" href="{{ route('privacypolicy') }}">
                    Privacy Policy
                </a>
                and
                <a class="fw-600 text-dark" href="{{ route('terms') }}">
                    Terms &amp; Conditions
                </a>
            </span>
        </label>
    </div>

    <div class="row align-items-center pt-3">
        <div class="col-12   text-center">
            <button type="button" @click="submitOrder($event.target)"
                class="confirm-order btn-dark fw-500 text-center fs-16">{{ 'Confirm Order' }}</button>
        </div>
    </div>
</div>
