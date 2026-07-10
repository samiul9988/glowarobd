<div class="card border-0 shadow-sm rounded">
    <div class="card-header">
        <h3 class="fs-16 fw-600 mb-0">{{translate('Summary')}}</h3>
        <div class="text-right">
            <span class="badge badge-inline badge-primary">
                {{ count($carts) }}
                {{translate('Items')}}
            </span>
        </div>
    </div>

    <div class="card-body">
        @if (addon_is_activated('club_point'))
            @php
                $total_point = 0;
            @endphp
            @foreach ($carts as $key => $cartItem)
                @php
                    $product = \App\Models\Product::find($cartItem['product_id']);
                    $total_point += $product->earn_point * $cartItem['quantity'];
                @endphp
            @endforeach

            <div class="rounded px-2 mb-2 bg-soft-primary border-soft-primary border">
                {{ translate("Total Club point") }}:
                <span class="fw-700 float-right">{{ $total_point }}</span>
            </div>
        @endif
        <table class="table">
            <thead>
                <tr>
                    <th class="product-name">{{translate('Product')}}</th>
                    <th class="product-total text-right">{{translate('Total')}}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotal = 0;
                    $tax = 0;
                    $shipping = 0;
                    $product_shipping_cost = 0;
                    $shipping_region = $shipping_info['city'];
                @endphp
                @foreach ($carts as $key => $cartItem)
                    @php
                        $product = \App\Models\Product::find($cartItem['product_id']);
                        $product_stock = $product->stocks->where('variant', $cartItem['variation'])->first();
                        $cartItem['price'] = getMinimumPriceByVariant($product, $product_stock, 'web', $cartItem['quantity'], $currentlyAuthenticatedUser);
                        $subtotal += $cartItem['price'] * $cartItem['quantity'];
                        $tax += $cartItem['tax'] * $cartItem['quantity'];
                        $product_shipping_cost = $cartItem['shipping_cost'];

                        $shipping += $product_shipping_cost;

                        $product_name_with_choice = $product->getTranslation('name');
                        if ($cartItem['variation'] != null) {
                            $product_name_with_choice = $product->getTranslation('name').' - '.$cartItem['variation'];
                        }

                    @endphp
                    <tr class="cart_item">
                        <td class="product-name">
                            {{ $product_name_with_choice }}
                            <strong class="product-quantity">
                                × {{ $cartItem['quantity'] }}
                            </strong>
                        </td>
                        <td class="product-total text-right">
                            <span class="pl-4 pr-0">{{ single_price($cartItem['price']*$cartItem['quantity']) }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="table">

            <tfoot>
                <tr class="cart-subtotal">
                    <th>{{translate('Subtotal')}}</th>
                    <td class="text-right">
                        <span class="fw-600">{{ single_price($subtotal) }}</span>
                    </td>
                </tr>

                <tr class="cart-shipping">
                    <th>{{translate('Tax')}}</th>
                    <td class="text-right">
                        <span class="font-italic">{{ single_price($tax) }}</span>
                    </td>
                </tr>

                <tr class="cart-shipping">
                    <th>{{translate('Total Shipping')}}</th>
                    <td class="text-right">
                        <span class="font-italic">@if($shipping!=0){{ single_price($shipping) }}@else Free @endif</span>
                    </td>
                </tr>

                @if (Session::has('club_point'))
                    <tr class="cart-shipping">
                        <th>{{translate('Redeem point')}}</th>
                        <td class="text-right">
                            <span class="font-italic">{{ single_price(Session::get('club_point')) }}</span>
                        </td>
                    </tr>
                @endif

                @if ($carts->sum('discount') > 0)
                    <tr class="cart-shipping">
                        <th>{{translate('Coupon Discount')}}</th>
                        <td class="text-right">
                            <span class="font-italic">{{ single_price($carts->sum('discount')) }}</span>
                        </td>
                    </tr>
                @endif

                @if (Session::has('reward_point_discount'))
                    <tr class="cart-shipping">
                        <th>{{translate('Reward Point Discount')}}</th>
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
                @endphp

                <tr class="cart-total">
                    <th><span class="strong-600">{{translate('Total')}}</span></th>
                    <td class="text-right">
                        <strong><span>{{ single_price($total) }}</span></strong>
                    </td>
                </tr>
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
                                <button type="submit" class="btn btn-primary">{{translate('Remove Redeem Point')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                {{--@if(Auth::user()->point_balance > 0)
                    <div class="mt-3">
                        <p>
                            {{translate('Your club point is')}}:
                            @if(isset(Auth::user()->point_balance))
                                {{ Auth::user()->point_balance }}
                            @endif
                        </p>
                        <form class="" action="{{ route('checkout.apply_club_point') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" name="point" placeholder="{{translate('Enter club point here')}}" required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">{{translate('Redeem')}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif--}}
            @endif
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
                                <button type="button" id="coupon-remove" class="btn btn-primary">{{translate('Change Coupon')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <div class="mt-3">
                    <form class="" id="apply-coupon-form" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="owner_id" value="{{ $carts[0]['owner_id'] }}">
                        <div class="input-group">
                            <input type="text" class="form-control" name="code" onkeydown="return event.key != 'Enter';" placeholder="{{translate('Have coupon code? Enter here')}}" required>
                            <div class="input-group-append">
                                <button type="button" id="coupon-apply" class="btn btn-primary">{{translate('Apply')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        @endif

        @if (Auth::check() && get_setting('reward_point_system') == 1)
            @if (Session::has('reward_point_discount'))
            <div class="mt-3">
                <form class="" id="remove-reward-point-form" enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <div class="form-control">{{ Session::get('applied_reward_point')}}</div>
                        <div class="input-group-append">
                            <button type="button" id="remove-reward-point" class="btn btn-primary">{{translate('Remove Redeem Point')}}</button>
                        </div>
                    </div>
                </form>
            </div>
            @else
            <div class="mt-3">
                <form class="" id="apply-reward-point-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="redeem_reward_type" value="checkout">
                    <h6>Use Reward Points</h6>
                    <p>You have <span class="font-weight-bold text-danger">{{ auth()->user()->point_balance }} Reward Points</span> available</p>
                    <div class="input-group">
                        <input type="text" max="{{auth()->user()->point_balance}}" id="reawrd_point" class="form-control" name="point" onkeydown="return event.key != 'Enter';" placeholder="{{translate('Enter amount of points to spend')}}" required>
                        <div class="input-group-append">
                            <button type="button" id="apply-reward-point" class="btn btn-primary">{{translate('Redeem')}}</button>
                        </div>
                    </div>
                    <div class="my-2 form-check">
                        <input class="form-check-input" type="radio" name="availablePoint" id="availablePoint" value="{{ auth()->user()->point_balance }}">
                        <label class="form-check-label mt-1" for="availablePoint">
                            Use maximum <span class="font-weight-bold text-danger">{{ auth()->user()->point_balance }} Reward Points</span>
                        </label>
                    </div>
                </form>
            </div>
            @endif
        @endif
    </div>
</div>
