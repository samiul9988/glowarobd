@foreach ($recentOrders as $index => $recentOrder)
    <div class="card">
        <div class="card-header" id="heading-{{ $index }}">
            <div class="row w-100">
                <div class="d-flex align-items-center flex-grow-1 {{ @$order ? 'col-7' : 'col-9' }}">
                    <div class="mr-3" style="min-width: 200px;">
                        <div>
                            @if(@$order)
                                <button class="btn btn-link collapsed p-0 text-left text-wrap" type="button"
                                    data-toggle="collapse" data-target="#collapse-{{ $index }}"
                                    aria-expanded="false" aria-controls="collapse-{{ $index }}"
                                    style="white-space: nowrap;">
                                    Order #{{ $recentOrder->code }} - {{ single_price($recentOrder->grand_total) }} - <span class="badge badge-inline badge-success">{{ strtoupper($recentOrder->order_source) }}</span>
                                </button>
                            @else
                                <a href="{{ route('all_orders.show', encrypt($recentOrder->id)) }}" target="_blank" class="btn btn-link collapsed p-0 text-left text-wrap" style="white-space: nowrap;">
                                    Order #{{ $recentOrder['code'] }} - {{ single_price($recentOrder->grand_total) }} - <span class="badge badge-inline badge-success">{{ strtoupper($recentOrder->order_source) }}</span>
                                </a>
                            @endif
                        </div>
                        <div class="text-muted small">
                            {{ date('d-m-Y h:i A', $recentOrder->date) }}
                        </div>
                    </div>

                </div>
                <!-- Badges Middle-->
                <div class="d-flex col-3 px-0 flex-wrap align-items-center" style="">
                    {!! order_status_badge($recentOrder) !!}
                    <div class="ml-1">
                        {!! payment_status_badge($recentOrder) !!}
                    </div>
                </div>

                @if(@$order)
                    <div class="d-flex col-2 px-0 justify-content-end mt-2 mt-xl-0" style="min-width: 80px;">
                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                            href="{{ route('all_orders.show', encrypt($recentOrder->id)) }}" target="_blank"
                            title="{{ ('View Invoice') }}">
                            <i class="las la-eye"></i>
                        </a>
                        @if (
                            (Auth::user()->user_type == 'admin' || in_array('processing_orders', json_decode(Auth::user()->staff?->role?->permissions ?? '[]') ?? [])) &&
                                @$order->delivery_status == 'processing' &&
                                $recentOrder->delivery_status == 'processing')
                            <a href="{{ route('invoice.edit', $recentOrder->id) }}" title="{{ ('Edit') }}"
                                class="btn btn-icon btn-soft-success btn-circle btn-sm ml-1" target="_blank">
                                <i class="las la-edit"></i>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if(@$order)
            <div id="collapse-{{ $index }}" class="collapse" aria-labelledby="heading-{{ $index }}"
                data-parent="#accordionExample">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-1"><strong>Order #</strong> <span
                                        class="text-info">{{ $recentOrder['code'] }}</span></li>
                                <li class="mb-1"><strong>Order Date:</strong>
                                    {{ date('d-m-Y h:i A', $recentOrder->date) }}</li>
                                @if ($recentOrder['payment_status'] == 'paid')
                                    <li class="mb-1"><strong>Payment Method:</strong>
                                        {{ strtoupper($recentOrder['payment_type']) }}</li>
                                @endif
                                <li class="mb-1"><strong>Order Status:</strong> {!! order_status_badge($recentOrder) !!}</li>
                                <li class="mb-1"><strong>Payment Status:</strong> {!! payment_status_badge($recentOrder) !!}</li>
                                <li class="mb-1"><strong>Order Source:</strong>
                                    <span class="badge badge-inline badge-success">
                                        {{ strtoupper($recentOrder->order_source) }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                @php
                                    $shipping_address = json_decode($recentOrder->shipping_address, true);
                                @endphp
                                <li class="mb-1"><strong>Name:</strong> {{ data_get($shipping_address, 'name') }}
                                </li>
                                <li class="mb-1"><strong>Phone:</strong> {{ data_get($shipping_address, 'phone') }}
                                </li>
                                @if (data_get($shipping_address, 'email') != '')
                                    <li class="mb-1"><strong>Email:</strong>
                                        {{ data_get($shipping_address, 'email') }}</li>
                                @endif
                                <li class="mb-1"><strong>Address:</strong>
                                    {{ data_get($shipping_address, 'address') }}
                                    City: {{ data_get($shipping_address, 'city') }},
                                    Area: {{ data_get($shipping_address, 'area') }},
                                    @if (data_get($shipping_address, 'postal_code') != '')
                                        Postal Code: {{ data_get($shipping_address, 'postal_code') }}<br>
                                    @endif
                                    {{ data_get($shipping_address, 'country') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    {{-- <hr class="new-section-sm bord-no"> --}}
                    <div class="row mt-3">
                        <div class="col-lg-12 table-responsive">
                            <table class="table table-bordered invoice-summary">
                                <thead>
                                    <tr class="bg-trans-dark">
                                        <th data-breakpoints="lg" class="min-col">#</th>
                                        <th width="10%" class="text-center">{{ ('Photo') }}</th>
                                        <th class="text-uppercase">{{ ('Description') }}</th>
                                        <th data-breakpoints="lg" class="min-col text-center text-uppercase">
                                            {{ ('Qty') }}</th>
                                        <th data-breakpoints="lg" class="min-col text-center text-uppercase">
                                            {{ ('Price') }}</th>
                                        <th data-breakpoints="lg" class="min-col text-center text-uppercase">
                                            {{ ('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentOrder->orderDetails as $key => $orderDetail)
                                        @if ($orderDetail->quantity < 1)
                                            @continue
                                        @endif
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td class="text-center">
                                                @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                                    <a href="{{ to_frontend(route('product', $orderDetail->product->slug)) }}"
                                                        target="_blank"><img height="50"
                                                            src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                                @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                                    <a href="{{ route('auction-product', $orderDetail->product->slug) }}"
                                                        target="_blank"><img height="50"
                                                            src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                                @else
                                                    <strong>{{ ('N/A') }}</strong>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                                    <strong><a
                                                            href="{{ to_frontend(route('product', $orderDetail->product->slug)) }}" target="_blank" class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
                                                    <small>{{ $orderDetail->variation }}</small>
                                                @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                                    <strong><a
                                                            href="{{ route('auction-product', $orderDetail->product->slug) }}"
                                                            target="_blank"
                                                            class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
                                                @else
                                                    <strong>{{ ('Product Unavailable') }}</strong>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ $orderDetail->quantity }}
                                            </td>
                                            <td class="text-center">
                                                {{ single_price($orderDetail->price / $orderDetail->quantity) }}</td>
                                            <td class="text-center">{{ single_price($orderDetail->price) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="clearfix float-right">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('Sub Total') }} :</strong>
                                    </td>
                                    <td class="text-muted ">
                                        {{ single_price($recentOrder->orderDetails->sum('price')) }}
                                    </td>
                                </tr>
                                @if ($recentOrder->coupon_discount > 0 || $recentOrder->reward_point_discount > 0)
                                    <tr>
                                        <td>
                                            <strong class="text-muted">{{ ('Discount') }} @if (@$recentOrder->orderDetails[0]->coupon_code != null)
                                                    ({{ $recentOrder->orderDetails[0]->coupon_code }})
                                                @endif :</strong>
                                        </td>
                                        <td class="text-muted ">
                                            {{ single_price($recentOrder->coupon_discount) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong class="text-muted">{{ ('Reward point discount') }}
                                                @if (@$recentOrder->orderDetails[0]->reward_point_discount != null)
                                                    ({{ $recentOrder->orderDetails[0]->reward_point_discount }})
                                                @endif :</strong>
                                        </td>
                                        <td class="text-muted ">
                                            {{ single_price($recentOrder->reward_point_discount) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong class="text-muted">{{ ('GRAND TOTAL') }} :</strong>
                                        </td>
                                        <td class="h6">
                                            {{ single_price($recentOrder->orderDetails->sum('price') - ($recentOrder->coupon_discount + $recentOrder->reward_point_discount)) }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('Tax') }} :</strong>
                                    </td>
                                    <td class="text-muted ">
                                        {{ single_price($recentOrder->orderDetails->sum('tax')) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('Shipping') }} :</strong>
                                    </td>
                                    <td class="text-muted ">
                                        {{ single_price($recentOrder->orderDetails->sum('shipping_cost')) }}
                                    </td>
                                </tr>
                                @php
                                    $recentOrderPaidAmount = $recentOrder->payments?->sum('amount') ?? 0;
                                    $recentOrderTotal =
                                        $recentOrder->orderDetails->sum('price') +
                                        $recentOrder->orderDetails->sum('tax') +
                                        $recentOrder->orderDetails->sum('shipping_cost') -
                                        ($recentOrder->coupon_discount +
                                            $recentOrder->reward_point_discount +
                                            $recentOrderPaidAmount);
                                @endphp
                                @if ($recentOrder->payment_status != 'unpaid'  && $recentOrderPaidAmount > 0)
                                    <tr>
                                        <td>
                                            <strong class="text-muted">{{ ('Paid Amount') }} :</strong>
                                        </td>
                                        <td>
                                            {{ single_price($recentOrderPaidAmount) }} (-)
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('NET TOTAL') }} :</strong>
                                    </td>
                                    <td class="h6">
                                        {{ single_price($recentOrderTotal) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @if ($recentOrder->isLocked())
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-lock"></i> This order is locked by seller (ID:
                            {{ $recentOrder['locked_by'] }}) at {{ $recentOrder['locked_at'] }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endforeach
