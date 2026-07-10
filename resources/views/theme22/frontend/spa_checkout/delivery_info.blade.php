<form id="delivery_info_form" class="form-default" action="{{ route('checkout.store_delivery_info') }}" role="form" method="POST">
    @csrf

    @php
        $admin_products = array();
        $seller_products = array();
        $address_ids = array();
        $shipping_method_ids = array();
        foreach ($carts as $key => $cartItem){
            $address_ids[]=$cartItem['address_id'];
            $shipping_method_ids[]=$cartItem['shipping_method'];
            $product = $cartItem->product;

            if($product->added_by == 'admin'){
                array_push($admin_products, $cartItem);
            }else{
                $product_ids = array();
                if(isset($seller_products[$product->user_id])){
                    $product_ids = $seller_products[$product->user_id];
                }
                array_push($product_ids, $cartItem);
                $seller_products[$product->user_id] = $product_ids;
            }
        }

        if($address_ids[0] == 0){
            $address_ids[0] = @$user_addresses->first()->id;
        }

        $addressInfo = $user_addresses->where('id', $address_ids[0])->first();
        $matchZone = @\App\Models\ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id])->first();

        $shippingMethods = NULL;
        if(@$matchZone->rates!==NULL){
            $shippingMethods = json_decode($matchZone->rates);
        }else{
            $matchZone = \App\Models\ShippingZone::where('rest_of_the_world',1)->first();
            if(@$matchZone->rates!==NULL)
            $shippingMethods = json_decode($matchZone->rates);
        }

        if(empty($shipping_method_ids)){
            $shipping_method_ids[0] = $shippingMethods[0]['id'];
        }

        $adminInfo = \App\Models\User::where('user_type', 'admin')->first();

        $shippingMethodfilePath = storage_path('app/public/shipping/methods.json');
        if (file_exists($shippingMethodfilePath)) {
            $jsonData = file_get_contents($shippingMethodfilePath);
            $shipping_methods = collect(json_decode($jsonData, true));
        } else {
            $shipping_methods = collect(\App\Models\ShippingMethod::all());
        }
    @endphp

    <input type="hidden" name="address_id" x-model="address_id" value="{{ @$addressInfo->id }}">
    @if (!empty($admin_products))
    <div class="card mb-3 shadow-sm border-0 rounded">
        <div class="card-header p-3">
            <h5 class="fs-16 fw-600 mb-0">Delivery Information</h5>
        </div>
        <div class="card-body">
            @foreach ($admin_products as $key => $cartItem)
                @php
                $product = $cartItem->product;
                $product_name_with_choice = collect($product->product_translations)->first()->name;
                if ($cartItem['variation'] != null) {
                    $product_name_with_choice = collect($product->product_translations)->first()->name.' - '.$cartItem['variation'];
                }
                @endphp
            @endforeach
            {{--<ul class="list-group list-group-flush">

                <li class="list-group-item">
                    <div class="d-flex">
                        <span class="mr-2">
                            <img src="{{ uploaded_asset($product->thumbnail_img) }}" class="img-fit size-60px rounded" alt="{{ collect($product->product_translations)->first()->name }}">
                        </span>
                        <span class="fs-14 opacity-60">{{ $product_name_with_choice }}</span>
                    </div>
                </li>
            </ul>--}}

            <div class="row pt-3">
                <div class="col-md-6">
                    <h6 class="fs-15 fw-600">{{ ('Choose Delivery Type') }}</h6>
                </div>
                <div class="col-md-6">
                    <div class="row gutters-5">
                        <div class="col-6">
                            <label class="aiz-megabox d-block bg-white mb-0">
                                <input type="radio" name="shipping_type_{{ $adminInfo->id }}" value="home_delivery" @change="show_pickup_point($event.target)" data-target=".pickup_point_id_admin" data-target2=".home_delivery_id_admin" checked>
                                <span class="d-flex p-3 aiz-megabox-elem">
                                    <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                    <span class="flex-grow-1 pl-3 fw-600">{{ ('Home Delivery') }}</span>
                                </span>
                            </label>
                        </div>
                        @if (get_setting('pickup_point') == 1)
                        <div class="col-6">
                            <label class="aiz-megabox d-block bg-white mb-0">
                                <input type="radio" name="shipping_type_{{ $adminInfo->id }}" value="pickup_point" @change="show_pickup_point($event.target)" data-target=".pickup_point_id_admin" data-target2=".home_delivery_id_admin">
                                <span class="d-flex p-3 aiz-megabox-elem">
                                    <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                    <span class="flex-grow-1 pl-3 fw-600">{{ ('Local Pickup') }}</span>
                                </span>
                            </label>
                        </div>
                        @endif
                    </div>

                    <div class="mt-4 home_delivery_id_admin">
                        @if($shippingMethods!=NULL)
                        @php
                            $shipping_price = 0;
                        @endphp
                        @foreach($shippingMethods as $k=>$v)
                        @php
                            $shipping_price = $shipping_charge ?? get_shipping_price($v->price);
                            $shipping_method = (object) $shipping_methods->where('id', $v->id)->first();
                        @endphp

                        <label class="aiz-megabox d-block bg-white mb-0">
                            <input type="radio" name="shipping_method_{{ $adminInfo->id }}" value="{{ $v->id }}"
                                @change="saveDeliveryInfo($event.target)"
                                @if($carts->first()->shipping_method == $v->id)
                                checked
                                @elseif($loop->first)
                                checked
                                @endif
                                required
                            >
                            <span class="d-flex p-3 aiz-megabox-elem">
                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                <span class="flex-grow-1 pl-3 fw-600">
                                    <img src="{{ uploaded_asset($shipping_method->logo)}}" alt="{{ $shipping_method->name }}" class="size-50px img-fit"> {{ $shipping_method->name }} @if($shipping_price>0){{ single_price($shipping_price) }}@else Free @endif
                                </span>
                            </span>
                        </label>
                        @endforeach
                        @else
                        <input type="hidden" name="shipping_method_{{ $adminInfo->id }}" value="" />
                        @endif
                    </div>

                    <div class="mt-4 pickup_point_id_admin d-none">
                        <select class="form-control aiz-selectpicker" name="pickup_point_id_{{ $adminInfo->id }}" @change="saveDeliveryInfo($event.target)" data-live-search="true">
                            <option value="">{{ ('Select your nearest pickup point')}}</option>
                            @foreach (\App\Models\PickupPoint::where('pick_up_status',1)->get() as $key => $pick_up_point)
                            <option value="{{ $pick_up_point->id }}" data-content="<span class='d-block'><span class='d-block fs-16 fw-600 mb-2'>{{ $pick_up_point->getTranslation('name') }}</span><span class='d-block opacity-50 fs-12'><i class='las la-map-marker'></i> {{ $pick_up_point->getTranslation('address') }}</span><span class='d-block opacity-50 fs-12'><i class='las la-phone'></i>{{$pick_up_point->phone}}</span></span>">
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
    <div class="card mb-3 shadow-sm border-0 rounded">
        <div class="card-header p-3">
            <h5 class="fs-16 fw-600 mb-0">{{ \App\Models\Shop::where('user_id', $key)->first()->name }} {{ ('Products') }}</h5>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                @foreach ($seller_product as $cartItem)
                @php
                    $product =$cartItem->product;
                    $product_name_with_choice = collect($product->product_translations)->first()->name;
                    if ($cartItem['variation'] != null) {
                        $product_name_with_choice = collect($product->product_translations)->first()->name.' - '.$cartItem['variation'];
                    }
                @endphp
                <li class="list-group-item">
                    <div class="d-flex">
                        <span class="mr-2">
                            <img src="{{ uploaded_asset($product->thumbnail_img) }}" class="img-fit size-60px rounded" alt="{{ collect($product->product_translations)->first()->name }}">
                        </span>
                        <span class="fs-14 opacity-60">{{ $product_name_with_choice }}</span>
                    </div>
                </li>
                @endforeach
            </ul>

            <div class="row border-top pt-3">
                <div class="col-md-6">
                    <h6 class="fs-15 fw-600">{{ ('Choose Delivery Type') }}</h6>
                </div>
                <div class="col-md-6">
                    <div class="row gutters-5">
                        <div class="col-6">
                            <label class="aiz-megabox d-block bg-white mb-0">
                                <input type="radio" name="shipping_type_{{ $key }}" value="home_delivery" @change="show_pickup_point($event.target)" data-target=".pickup_point_id_{{ $key }}" data-target2=".home_delivery_id_admin_{{ $key }}" checked>
                                <span class="d-flex p-3 aiz-megabox-elem">
                                    <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                    <span class="flex-grow-1 pl-3 fw-600">{{ ('Home Delivery') }}</span>
                                </span>
                            </label>
                        </div>
                        @if (get_setting('pickup_point') == 1)
                        @if (is_array(json_decode(\App\Models\Shop::where('user_id', $key)->first()->pick_up_point_id)))
                        <div class="col-6">
                            <label class="aiz-megabox d-block bg-white mb-0">
                                <input type="radio" name="shipping_type_{{ $key }}" value="pickup_point" @change="show_pickup_point($event.target)" data-target=".pickup_point_id_{{ $key }}" data-target2=".home_delivery_id_admin_{{ $key }}">
                                <span class="d-flex p-3 aiz-megabox-elem">
                                    <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                    <span class="flex-grow-1 pl-3 fw-600">{{ ('Local Pickup') }}</span>
                                </span>
                            </label>
                        </div>
                        @endif
                        @endif
                    </div>

                    <div class="mt-4 home_delivery_id_admin_{{ $key }}">
                        @if($shippingMethods!=NULL)
                        @php
                            $shipping_price = 0;
                        @endphp
                        @foreach($shippingMethods as $k=>$v)
                        @php
                            $shipping_price = $shipping_charge ?? get_shipping_price($v->price);
                            $shipping_method = \App\Models\ShippingMethod::findOrFail($v->id);
                        @endphp

                        <label class="aiz-megabox d-block bg-white mb-0">
                            <input type="radio" name="shipping_method_{{ $key }}" value="{{ $v->id }}" required>
                            <span class="d-flex p-3 aiz-megabox-elem">
                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                <span class="flex-grow-1 pl-3 fw-600">
                                    <img src="{{ uploaded_asset($shipping_method->logo)}}" alt="{{ $shipping_method->name }}" class="size-50px img-fit"> {{ $shipping_method->name }} @if($shipping_price>0){{ single_price($shipping_price) }}@else Free @endif
                                </span>
                            </span>
                        </label>
                        @endforeach
                        @else
                        <input type="hidden" name="shipping_method_{{ $key }}" value="" />
                        @endif
                    </div>

                    @if (get_setting('pickup_point') == 1)
                    @if (is_array(json_decode(\App\Models\Shop::where('user_id', $key)->first()->pick_up_point_id)))
                    <div class="mt-4 pickup_point_id_{{ $key }} d-none">
                        <select class="form-control aiz-selectpicker" name="pickup_point_id_{{ $key }}" data-live-search="true">
                            <option value="">{{ ('Select your nearest pickup point')}}</option>
                            @foreach (\App\Models\PickupPoint::where('pick_up_status',1)->get() as $key => $pick_up_point)
                            <option value="{{ $pick_up_point->id }}" data-content="<span class='d-block'><span class='d-block fs-16 fw-600 mb-2'>{{ $pick_up_point->getTranslation('name') }}</span><span class='d-block opacity-50 fs-12'><i class='las la-map-marker'></i> {{ $pick_up_point->getTranslation('address') }}</span><span class='d-block opacity-50 fs-12'><i class='las la-phone'></i>{{ $pick_up_point->phone }}</span></span>">
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
