@php
    $coupons = Cache::remember('all_coupons', now()->addHour(), function () {
        return \App\Models\Coupon::valid()
            ->where('force_apply', 1)
            ->orderBy('end_date', 'asc')
            ->get();
    });
@endphp
<div class="row gutters-5">
    <div class="col-12">
        <div class="row gutters-5">
            <div class="col-lg-6">
                <div class="row gutters-5 mb-3">
                    <div class="col-md-6">
                        <label for="change-order-source">Change Order Source</label>
                        <select class="form-control" id="change-order-source" name="order_source">
                            <option value="POS" selected>POS</option>
                            <option value="SHOWROOM">SHOWROOM</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="select-coupon">
                            Apply Coupon
                            @include('components.tooltip', [
                                'title' => 'It will remove any existing discount and apply the selected coupon.',
                            ])
                        </label>
                        <select class="form-control" id="select-coupon" name="coupon">
                            <option value="" selected>Select A Coupon</option>
                            @foreach ($coupons as $coupon)
                                <option value="{{ $coupon->code }}" @selected(Session::get('pos.coupon_code') == $coupon->code)>{{ $coupon->code }}
                                    ({{ $coupon->discount_type == 'percent' ? $coupon->discount . '%' : single_price($coupon->discount) }}
                                    off)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row gutters-5 pl-xl-4 mb-3">
                    <div class="col-10">
                        <label for="change-delivery-status">Change Delivery Status</label>
                        <select class="form-control" id="change-delivery-status" name="delivery_status">
                            <option value="pending" selected>Pending</option>
                            <option value="processing">Processing</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="hold">Hold</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button type="button" class="btn btn-icon btn-soft-info ml-1" id="create-call-log-btn"
                            data-toggle="tooltip" title="Add Call Log">
                            <i class="las la-phone-volume"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        @php
            $subtotal = 0;
            $tax = 0;
            $successRate = Session::get('pos.success_rate', []);
            $ratio = $successRate['ratio'] ?? 0;
            if ($ratio == 100) {
                $success_rate_class = 'success';
            } elseif ($ratio == 0) {
                $success_rate_class = 'danger';
            } else {
                $success_rate_class = $ratio <= 25 ? 'success' : ($ratio < 100 ? 'warning' : 'danger');
            }

            $carts = Session::get('pos.cart', collect());
        @endphp
        @if ($carts->isNotEmpty())
            <ul class="list-group list-group-flush">
                @forelse ($carts->sortByDesc('type') as $key => $cartItem)
                    @php
                        $subtotal += $cartItem['price'] * $cartItem['quantity'];
                        $tax += ($cartItem['tax'] ?? 0) * $cartItem['quantity'];
                        $stock = \App\Models\ProductStock::find($cartItem['stock_id']);
                    @endphp
                    <li class="list-group-item px-0 {{ $cartItem['type'] === 'gift' ? 'text-success' : '' }}">
                        <div class="row gutters-10 align-items-center">
                            <div class="col">
                                <div class="d-flex">
                                    @if ($stock->image == null)
                                        <img src="{{ uploaded_asset($stock->product->thumbnail_img) }}"
                                            data-src="{{ uploaded_asset($stock->product->thumbnail_img) }}" alt="{{ $stock->product->name }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                            class="img-fit size-60px">
                                    @else
                                        <img src="{{ uploaded_asset($stock->image) }}" class="img-fit size-60px">
                                    @endif
                                    <span class="flex-grow-1 ml-3 mr-0">
                                        <div class="text-truncate-2">{{ $stock->product->name }}</div>
                                        <span class="span badge badge-inline fs-12 badge-soft-secondary">{{ $cartItem['variant'] }}</span>
                                        @if ($cartItem['type'] === 'gift')
                                            <span role="button" class="{{ $cartItem['quantity'] > 1 ? 'text-dark' : 'text-muted disabled' }} fs-16 {{ filled($cartItem['variant']) ? 'ml-2' : '' }} gift-item-qty" data-action="dec" data-key="{{ $key }}">
                                                <i class="las la-minus"></i>
                                            </span>
                                            <span role="button" class="text-dark fs-16 {{ filled($cartItem['variant']) ? 'ml-2' : '' }} gift-item-qty" data-action="inc" data-key="{{ $key }}">
                                                <i class="las la-plus"></i>
                                            </span>
                                            <span role="button" class="text-danger fs-16 {{ filled($cartItem['variant']) ? 'ml-2' : '' }} remove-gift-item" data-key="{{ $key }}">
                                                <i class="las la-trash"></i>
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="col-xl-3">
                                <div class="fs-14 fw-600 text-right">{{ single_price($cartItem['price']) }}</div>
                                <div class="fs-14 text-right">Qty: {{ $cartItem['quantity'] }}</div>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item">
                        <div class="text-center">
                            <i class="las la-frown la-3x opacity-50"></i>
                            <p>{{ 'No Product Added' }}</p>
                        </div>
                    </li>
                @endforelse
            </ul>
        @else
            <div class="text-center">
                <i class="las la-frown la-3x opacity-50"></i>
                <p>{{ 'No Product Added' }}</p>
            </div>
        @endif

        @if ($carts->isNotEmpty())
            <div id="giftOffersContainer" class="mt-4">
                {!! $giftOffersView !!}
            </div>
        @endif
    </div>
    <div class="col-md-6">
        <div class="pl-xl-4">
            <div class="card mb-4">
                <div class="card-header d-block">
                    <span class="fs-16">{{ 'Customer Info' }}</span>
                    @if (Session::has('pos.success_rate'))
                        <span class="d-block fs-10">
                            Success Rate - <span class="text-{{ $success_rate_class }}">{{ $ratio }}%</span>
                            @if (filled(@$lastOrderDate))
                                Last Order - <span
                                    class="font-weight-bold text-danger">{{ $lastOrderDate['default'] ?? '' }}</span>
                                <span
                                    class="font-weight-bold text-dark">({{ $lastOrderDate['formatted'] ?? '' }})</span>
                            @endif
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    @if (Session::has('pos.shipping_info') && Session::get('pos.shipping_info')['name'] != null)
                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{ 'Name' }}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['name'] }}</span>
                        </div>
                        {{-- <div class="d-flex justify-content-between  mb-2">
							<span class="">{{ ('Email')}}:</span>
							<span class="fw-600">{{ Session::get('pos.shipping_info')['email'] }}</span>
						</div> --}}
                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{ 'Phone' }}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['phone'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{ 'Address' }}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['address'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{ 'Country' }}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['country'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{ 'State' }}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['state'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{ 'City' }}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['city'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{ 'Area' }}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['area'] }}</span>
                        </div>
                        {{-- <div class="d-flex justify-content-between  mb-2">
							<span class="">{{ ('Postal Code')}}:</span>
							<span class="fw-600">{{ Session::get('pos.shipping_info')['postal_code'] }}</span>
						</div> --}}
                    @else
                        <div class="text-center p-4">
                            {{ 'No customer information selected.' }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                <span>{{ 'Total' }}</span>
                <span>{{ single_price($subtotal) }}</span>
            </div>
            <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                <span>{{ 'Tax' }}</span>
                <span>{{ single_price($tax) }}</span>
            </div>
            <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                <span>{{ 'Shipping' }}</span>
                <span>{{ single_price(Session::get('pos.shipping', 0)) }}</span>
            </div>

            @if (Session::get('pos.coupon_discount', 0) > 0)
                <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                    <span>Coupon Discount ({{ Session::get('pos.coupon_code') }})</span>
                    <span>{{ single_price(Session::get('pos.coupon_discount', 0)) }}</span>
                </div>
            @else
                <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                    <span>{{ 'Discount' }}</span>
                    <span>{{ single_price(Session::get('pos.discount', 0)) }}</span>
                </div>
            @endif
            @if(Session::get('pos.total_paid', 0))
                <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                    <span>{{ ('Paid Amount')}}</span>
                    <span>
                        <a type="button" title="Remove Paid Amount" class="text-danger" onclick="removePaidAmount()">
                            <i class="las la-times-circle"></i>
                        </a>
                        {{ single_price(Session::get('pos.total_paid', 0)) }} (-)
                    </span>
                </div>
            @endif
            @php
                $total = $subtotal + $tax + Session::get('pos.shipping', 0) - Session::get('pos.discount', 0) - Session::get('pos.total_paid', 0) - Session::get('pos.coupon_discount', 0);
            @endphp
            <div class="d-flex justify-content-between fw-600 fs-18 border-top pt-2">
                <span>{{ 'Total' }}</span>
                <span class="final-total-amount" data-total="{{ max($total, 0) }}">{{ single_price(max($total, 0)) }}</span>
            </div>

            @php
                $showCopySummary = (Session::has('pos.coupon_code') && Session::get('pos.coupon_discount', 0) > 0) || $carts->where('type', 'gift')->count() > 0 || Session::get('pos.total_paid', 0) > 0;
            @endphp

            <div class="row gutters-5 mt-4">
                @if ($showCopySummary)
                    <div class="col-md-6">
                            <button type="button" class="btn btn-sm btn-outline-dark btn-styled copy_final_summary w-100">
                                Copy Final Summary
                            </button>
                    </div>
                @endif
                <div class="col-md-{{ $showCopySummary ? '6' : '12' }}">
                    <button type="button" onclick="paynow()" class="btn btn-sm btn-outline-dark btn-styled w-100">
                        Pay Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
