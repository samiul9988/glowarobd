<form id="delivery_info_form" class="form-default" action="{{ route('checkout.store_delivery_info') }}" role="form"
    method="POST">
    @csrf

    @php
        $admin_products = [];
        $seller_products = [];
        $address_ids = [];
        $shipping_method_ids = [];
        foreach ($carts as $key => $cartItem) {
            $address_ids[] = $cartItem['address_id'] ?? 0;
            $shipping_method_ids[] = $cartItem['shipping_method'];
            $product = $cartItem->product;

            if ($product->added_by == 'admin') {
                array_push($admin_products, $cartItem);
            } else {
                $product_ids = [];
                if (isset($seller_products[$product->user_id])) {
                    $product_ids = $seller_products[$product->user_id];
                }
                array_push($product_ids, $cartItem);
                $seller_products[$product->user_id] = $product_ids;
            }
        }

        if ($address_ids[0] == 0) {
            $addressInfo = $user_addresses->first() ?? null;
        } else {
            $addressInfo = $user_addresses->where('id', $address_ids[0])->first() ?? null;
        }

        if (is_null($addressInfo)) {
            $matchZone = \App\Models\ShippingZone::where('rest_of_the_world', 1)->first();
        } else {
            $matchZone =
                \App\Models\ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id])->first() ??
                \App\Models\ShippingZone::where('rest_of_the_world', 1)->first();
        }

        $shippingRates = is_null($matchZone->rates) ? null : json_decode($matchZone->rates, true);

        if (empty($shipping_method_ids)) {
            $shipping_method_ids[0] = $shippingRates[0]['id'];
        }

        $adminInfo = \App\Models\User::where('user_type', 'admin')->first();

        $allShippingMethods = Cache::remember('frontend_shipping_methods', now()->addDay(), function () {
            return \App\Models\ShippingMethod::all();
        });
    @endphp

    <input type="hidden" name="address_id" x-model="address_id" value="{{ @$addressInfo->id }}">
    @if (!empty($admin_products))
        <div class="card mb-3 border-0 rounded">
            <div class="card-body p-0">
                @foreach ($admin_products as $key => $cartItem)
                    @php
                        $product = $cartItem->product;
                        $product_name_with_choice = $product->name;
                        if ($cartItem['variation'] != null) {
                            $product_name_with_choice = $product->name . ' - ' . $cartItem['variation'];
                        }
                    @endphp
                @endforeach
                <div class="row pt-3">
                    <div class="col-12 ">
                        <h6 class="fs-18 fw-500 mb-0 py-3">{{ 'Choose Delivery Type' }}:</h6>
                    </div>
                    <div class="col-12 p-0">
                        <div class="row gutters-5">
                            @if (get_setting('pickup_point') == 1)
                                <div class="col-6">
                                    <label class="aiz-megabox d-block bg-white mb-0">
                                        <input type="radio" name="shipping_type_{{ $adminInfo->id }}"
                                            value="pickup_point" @change="show_pickup_point($event.target)"
                                            data-target=".pickup_point_id_admin" data-target2=".home_delivery_id_admin">
                                        <span class="d-flex p-3 aiz-megabox-elem">
                                            <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                            <span class="flex-grow-1 pl-3 fw-600">{{ 'Local Pickup' }}</span>
                                        </span>
                                    </label>
                                </div>
                            @endif
                        </div>

                        <div class=" home_delivery_id_admin mb-1">
                            @if (!is_null($shippingRates))
                                @foreach ($shippingRates as $k => $v)
                                    @php
                                        $shipping_method = $allShippingMethods->where('id', $v['id'])->first();
                                    @endphp

                                    <label class="aiz-megabox d-block bg-white mb-0">
                                        <input type="radio" name="shipping_method_{{ $adminInfo->id }}"
                                            value="{{ $v['id'] }}" @change="saveDeliveryInfo($event.target)"
                                            @if ($carts->first()?->shipping_method == $v['id'] || $loop->first) checked @endif required
                                        >
                                        <span class="d-flex px-1 py-2 aiz-megabox-elem">
                                            <span class="aiz-rounded-check flex-shrink-0 mt-1 d-none"></span>
                                            <span
                                                class="flex-grow-1 px-1 py-2 fw-600 delivary-method align-items-center">
                                                <img src="{{ uploaded_asset($shipping_method->logo) }}"
                                                    alt="{{ $shipping_method->name ?? '' }}" class="size-50px img-fit">
                                                <div class="fs-12 fw-500">
                                                    {{ $shipping_method->name ?? '' }} <br />
                                                    @if ($shipping_charge > 0)
                                                        {{ single_price($shipping_charge) }}
                                                    @else
                                                        <span style="color:#FA7E16">Free</span>
                                                    @endif
                                                </div>
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            @else
                                <input type="hidden" name="shipping_method_{{ $adminInfo->id }}" value="" />
                            @endif
                        </div>

                        <div class="mt-4 pickup_point_id_admin d-none">
                            <select class="form-control aiz-selectpicker" name="pickup_point_id_{{ $adminInfo->id }}"
                                @change="saveDeliveryInfo($event.target)" data-live-search="true">
                                <option value="">{{ 'Select your nearest pickup point' }}</option>
                                @foreach (\App\Models\PickupPoint::where('pick_up_status', 1)->get() as $key => $pick_up_point)
                                    <option value="{{ $pick_up_point->id }}"
                                        data-content="<span class='d-block'><span class='d-block fs-16 fw-600 mb-2'>{{ $pick_up_point->getTranslation('name') }}</span><span class='d-block opacity-50 fs-12'><i class='las la-map-marker'></i> {{ $pick_up_point->getTranslation('address') }}</span><span class='d-block opacity-50 fs-12'><i class='las la-phone'></i>{{ $pick_up_point->phone }}</span></span>">
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    @endif

    @if (!empty($seller_products))
        @foreach ($seller_products as $key => $seller_product)
            <div class="card mb-3  border-0 rounded">
                <div class="card-header py-3">
                    <h5 class="fs-16 fw-600 mb-0">{{ \App\Models\Shop::where('user_id', $key)->first()->name }}
                        {{ 'Products' }}</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach ($seller_product as $cartItem)
                            @php
                                $product = $cartItem->product;
                                $product_name_with_choice = $product->name;
                                if ($cartItem['variation'] != null) {
                                    $product_name_with_choice = $product->name . ' - ' . $cartItem['variation'];
                                }
                            @endphp
                            <li class="list-group-item">
                                <div class="d-flex">
                                    <span class="mr-2">
                                        <img src="{{ uploaded_asset($product->thumbnail_img) }}"
                                            class="img-fit size-60px rounded" alt="{{ $product->name }}">
                                    </span>
                                    <span class="fs-14 opacity-60">{{ $product_name_with_choice }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="row border-top pt-3">
                        <div class="col-md-6">
                            <h6 class="fs-15 fw-600">{{ 'Choose Delivery Type' }}</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="row gutters-5">
                                <div class="col-6 ">
                                    <label class="aiz-megabox d-block bg-white mb-0">
                                        <input type="radio" name="shipping_type_{{ $key }}"
                                            value="home_delivery" @change="show_pickup_point($event.target)"
                                            data-target=".pickup_point_id_{{ $key }}"
                                            data-target2=".home_delivery_id_admin_{{ $key }}" checked>
                                        <span class="d-flex p-3 aiz-megabox-elem">
                                            <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                            <span class="flex-grow-1 pl-3 fw-600">{{ 'Home Delivery' }}</span>
                                        </span>
                                    </label>
                                </div>
                                @if (get_setting('pickup_point') == 1)
                                    @if (is_array(json_decode(\App\Models\Shop::where('user_id', $key)->first()->pick_up_point_id)))
                                        <div class="col-6">
                                            <label class="aiz-megabox d-block bg-white mb-0">
                                                <input type="radio" name="shipping_type_{{ $key }}"
                                                    value="pickup_point" @change="show_pickup_point($event.target)"
                                                    data-target=".pickup_point_id_{{ $key }}"
                                                    data-target2=".home_delivery_id_admin_{{ $key }}">
                                                <span class="d-flex p-3 aiz-megabox-elem">
                                                    <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                    <span class="flex-grow-1 pl-3 fw-600">{{ 'Local Pickup' }}</span>
                                                </span>
                                            </label>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <div class="mt-4 home_delivery_id_admin_{{ $key }}">
                                @if (!is_null($shippingRates))
                                    @foreach ($shippingRates as $k => $v)
                                        @php
                                            $shipping_method = $allShippingMethods->where('id', $v['id'])->first();
                                        @endphp

                                        <label class="aiz-megabox d-block bg-white mb-0">
                                            <input type="radio" name="shipping_method_{{ $key }}"
                                                value="{{ $v->id }}" required>
                                            <span class="d-flex p-3 aiz-megabox-elem">
                                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                <span class="flex-grow-1 pl-3 fw-600">
                                                    <img src="{{ uploaded_asset($shipping_method->logo) }}"
                                                        alt="{{ $shipping_method->name ?? '' }}" class="size-50px img-fit">
                                                    {{ $shipping_method->name ?? '' }} <br />
                                                    @if ($shipping_charge > 0)
                                                        {{ single_price($shipping_charge) }}
                                                    @else
                                                        Free
                                                    @endif
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                @else
                                    <input type="hidden" name="shipping_method_{{ $key }}"
                                        value="" />
                                @endif
                            </div>

                            @if (get_setting('pickup_point') == 1)
                                @if (is_array(json_decode(\App\Models\Shop::where('user_id', $key)->first()->pick_up_point_id)))
                                    <div class="mt-4 pickup_point_id_{{ $key }} d-none">
                                        <select class="form-control aiz-selectpicker"
                                            name="pickup_point_id_{{ $key }}" data-live-search="true">
                                            <option value="">{{ 'Select your nearest pickup point' }}</option>
                                            @foreach (\App\Models\PickupPoint::where('pick_up_status', 1)->get() as $key => $pick_up_point)
                                                <option value="{{ $pick_up_point->id }}"
                                                    data-content="<span class='d-block'><span class='d-block fs-16 fw-600 mb-2'>{{ $pick_up_point->getTranslation('name') }}</span><span class='d-block opacity-50 fs-12'><i class='las la-map-marker'></i> {{ $pick_up_point->getTranslation('address') }}</span><span class='d-block opacity-50 fs-12'><i class='las la-phone'></i>{{ $pick_up_point->phone }}</span></span>">
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    @endif
</form>
