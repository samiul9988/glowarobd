@forelse ($orders as $order)
@php
    $products = [];
    $order->orderDetails->each(function($item) use (&$products) {
        $products[] = [
            'id' => $item->product?->id,
            'name' => $item->product?->name,
        ];
    });
@endphp
    <div class="card">
        <div class="card-header px-0" id="heading-{{ $order->id }}">
            <div class="row w-100 justify-content-between">
                <div class="col-10">
                    <div class="d-flex align-items-center flex-grow-1 col-9">
                        <div class="mr-3" style="min-width: 200px;">
                            <div>
                                <button class="btn btn-link collapsed p-0 text-left text-wrap" type="button"
                                    data-toggle="collapse" data-target="#collapse-{{ $order->id }}"
                                    aria-expanded="false" aria-controls="collapse-{{ $order->id }}"
                                    style="white-space: nowrap;">
                                    Order #{{ $order->code }} - {{ single_price($order->grand_total) }}
                                </button>
                                {!! order_status_badge($order) !!} @if($order->feedback) - <span class="badge badge-inline badge-warning font-weight-bold">Rating: {{ $order->feedback->rating }}</span> @endif
                            </div>
                            <div class="text-muted small">
                                {{ date('d-m-Y h:i A', $order->date) }}
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-auto d-flex justify-content-end pr-0">
                    <div class="d-flex px-0 justify-content-end mt-2 mt-xl-0" style="min-width: 80px;">
                        @if($order->feedback)
                            <button class="btn btn-soft-success btn-icon btn-circle btn-sm mr-2"
                                title="{{ ('Feedback Given') }}">
                                <i class="las la-check"></i>
                            </button>
                        @else
                            <button id="feedback-btn-{{ $order->id }}" class="btn btn-soft-info btn-icon btn-circle btn-sm mr-2 feedback-btn"
                                data-order="{{ $order->id }}"
                                data-products="{{ json_encode($products) }}"
                                title="{{ ('Take Feedback') }}">
                                <i class="las la-plus"></i>
                            </button>
                        @endif
                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                            href="{{ route('all_orders.show', encrypt($order->id)) }}" target="_blank"
                            title="{{ ('View Invoice') }}">
                            <i class="las la-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id="collapse-{{ $order->id }}" class="collapse" aria-labelledby="heading-{{ $order->id }}"
            data-parent="#accordionExample">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-1"><strong>Order #</strong> <span
                                    class="text-info">{{ $order['code'] }}</span></li>
                            <li class="mb-1"><strong>Order Date:</strong>
                                {{ date('d-m-Y h:i A', $order->date) }}</li>
                            @if ($order['payment_status'] == 'paid')
                                <li class="mb-1"><strong>Payment Method:</strong>
                                    {{ strtoupper($order['payment_type']) }}</li>
                            @endif
                            <li class="mb-1"><strong>Order Status:</strong> {!! order_status_badge($order) !!}</li>
                            <li class="mb-1"><strong>Payment Status:</strong> {!! payment_status_badge($order) !!}</li>
                            <li class="mb-1"><strong>Order Source:</strong>
                                <span class="badge badge-inline badge-success">
                                    {{ strtoupper($order->order_source) }}
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <ul class="list-unstyled">
                            @php
                                $shipping_address = json_decode($order->shipping_address, true);
                            @endphp
                            <li class="mb-1"><strong>Name:</strong> {{ data_get($shipping_address, 'name') }}
                            </li>
                            <li class="mb-1"><strong>Phone:</strong> {{ data_get($shipping_address, 'phone') }}
                            </li>
                            @if (data_get($shipping_address, 'email') != '')
                                <li class="mb-1"><strong>Email:</strong>
                                    {{ data_get($shipping_address, 'email') }}</li>
                            @endif
                            <li class="mb-1">
                                <strong>Address:</strong> {{ data_get($shipping_address, 'address') }}
                            </li>
                            <li class="mb-1">
                                <strong>State:</strong> {{ data_get($shipping_address, 'state', 'N/A') }}
                            </li>
                            <li class="mb-1">
                                <strong>City:</strong> {{ data_get($shipping_address, 'city', 'N/A') }}
                            </li>
                            <li class="mb-1">
                                <strong>Area:</strong> {{ data_get($shipping_address, 'area', 'N/A') }}
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
                                @foreach ($order->orderDetails as $key => $orderDetail)
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
                                                        href="{{ to_frontend(route('product', $orderDetail->product->slug)) }}"
                                                        target="_blank"
                                                        class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
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
                <div class="d-flex justify-content-between">
                    <div>
                        @if ($order->feedback)
                            <div class="alert alert-info d-inline-block py-2 font-weight-bold mb-1">
                                Rating: {{ $order->feedback->rating }}
                            </div>
                            @if(intval(data_get($order->feedback->feedback, 'rider_behavior', 0)) > 0)
                                <span class="text-muted font-weight-bold d-block fs-15">
                                    {{ ('Rider Behavior') }}: {{ data_get($order->feedback->feedback, 'rider_behavior') }}
                                    @if(data_get($order->feedback->feedback, 'rider_behavior_note'))
                                        @include('components.tooltip', [
                                            'title' => data_get($order->feedback->feedback, 'rider_behavior_note'),
                                            'class' => 'text-dark ml-2 font-weight-bold fs-18',
                                            'position' => 'top',
                                        ])
                                    @endif
                                </span>
                            @endif
                            @if(intval(data_get($order->feedback->feedback, 'packaging', 0)) > 0)
                                <span class="text-muted font-weight-bold d-block fs-15">
                                    Packaging: {{ data_get($order->feedback->feedback, 'packaging') }}
                                    @if(data_get($order->feedback->feedback, 'packaging_note'))
                                        @include('components.tooltip', [
                                            'title' => data_get($order->feedback->feedback, 'packaging_note'),
                                            'class' => 'text-dark ml-2 font-weight-bold fs-18',
                                            'position' => 'top',
                                        ])
                                    @endif
                                </span>
                            @endif
                            @if(intval(data_get($order->feedback->feedback, 'cs_behavior', 0)) > 0)
                                <span class="text-muted font-weight-bold d-block fs-15">
                                    {{ ('CS Behavior') }}: {{ data_get($order->feedback->feedback, 'cs_behavior') }}
                                    @if(data_get($order->feedback->feedback, 'cs_behavior_note'))
                                        @include('components.tooltip', [
                                            'title' => data_get($order->feedback->feedback, 'cs_behavior_note'),
                                            'class' => 'text-dark ml-2 font-weight-bold fs-18',
                                            'position' => 'top',
                                        ])
                                    @endif
                                </span>
                            @endif
                            @if(intval(data_get($order->feedback->feedback, 'delivery_time', 0)) > 0)
                                <span class="text-muted font-weight-bold d-block fs-15">
                                    {{ ('Delivery Time') }}: {{ data_get($order->feedback->feedback, 'delivery_time') }}
                                    @if(data_get($order->feedback->feedback, 'delivery_time_note'))
                                        @include('components.tooltip', [
                                            'title' => data_get($order->feedback->feedback, 'delivery_time_note'),
                                            'class' => 'text-dark ml-2 font-weight-bold fs-18',
                                            'position' => 'top',
                                        ])
                                    @endif
                                </span>
                            @endif
                            @if(intval(data_get($order->feedback->feedback, 'products_rating', 0)) > 0)
                                <span class="text-muted font-weight-bold d-block fs-15">
                                    {{ ('Product Quality') }}: {{ data_get($order->feedback->feedback, 'products_rating') }}
                                    @if(data_get($order->feedback->feedback, 'product_quality_note'))
                                        @include('components.tooltip', [
                                            'title' => data_get($order->feedback->feedback, 'product_quality_note'),
                                            'class' => 'text-dark ml-2 font-weight-bold fs-18',
                                            'position' => 'top',
                                        ])
                                    @endif
                                </span>
                            @endif

                            @if($order->feedback->note)
                                <div class="alert alert-warning py-2 font-weight-bold mt-2">
                                    Note: {{ $order->feedback->note }}
                                </div>
                            @endif
                        @endif
                    </div>
                    <div>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('Sub Total') }} :</strong>
                                    </td>
                                    <td class="text-muted ">
                                        {{ single_price($order->orderDetails->sum('price')) }}
                                    </td>
                                </tr>
                                @if ($order->coupon_discount > 0 || $order->reward_point_discount > 0)
                                    <tr>
                                        <td>
                                            <strong class="text-muted">{{ ('Discount') }} @if (@$order->orderDetails[0]->coupon_code != null)
                                                    ({{ $order->orderDetails[0]->coupon_code }})
                                                @endif :</strong>
                                        </td>
                                        <td class="text-muted ">
                                            {{ single_price($order->coupon_discount) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong class="text-muted">{{ ('Reward point discount') }}
                                                @if (@$order->orderDetails[0]->reward_point_discount != null)
                                                    ({{ $order->orderDetails[0]->reward_point_discount }})
                                                @endif :</strong>
                                        </td>
                                        <td class="text-muted ">
                                            {{ single_price($order->reward_point_discount) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong class="text-muted">{{ ('GRAND TOTAL') }} :</strong>
                                        </td>
                                        <td class="h6">
                                            {{ single_price($order->orderDetails->sum('price') - ($order->coupon_discount + $order->reward_point_discount)) }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('Tax') }} :</strong>
                                    </td>
                                    <td class="text-muted ">
                                        {{ single_price($order->orderDetails->sum('tax')) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('Shipping') }} :</strong>
                                    </td>
                                    <td class="text-muted ">
                                        {{ single_price($order->orderDetails->sum('shipping_cost')) }}
                                    </td>
                                </tr>
                                @php
                                    $orderPaidAmount = $order->payments?->sum('amount') ?? 0;
                                    $orderTotal =
                                        $order->orderDetails->sum('price') +
                                        $order->orderDetails->sum('tax') +
                                        $order->orderDetails->sum('shipping_cost') -
                                        ($order->coupon_discount +
                                            $order->reward_point_discount +
                                            $orderPaidAmount);
                                @endphp
                                @if ($order->payment_status != 'unpaid' && $orderPaidAmount > 0)
                                    <tr>
                                        <td>
                                            <strong class="text-muted">{{ ('Paid Amount') }} :</strong>
                                        </td>
                                        <td>
                                            {{ single_price($orderPaidAmount) }} (-)
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('NET TOTAL') }} :</strong>
                                    </td>
                                    <td class="h6">
                                        {{ single_price($orderTotal) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($order->isLocked())
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-lock"></i> This order is locked by seller (ID:
                        {{ $order['locked_by'] }}) at {{ $order['locked_at'] }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="text-center">
        <img class="mw-100 h-150px" src="{{ static_asset('assets/img/nothing.svg') }}" alt="Image">
        <h5 class="mb-0 h5 mt-3">{{ ("There isn't any order yet")}}</h5>
    </div>
@endforelse

@if (@$nextPageUrl)
    <div class="text-center mt-3 next-page-url">
        <button data-href="{{ $nextPageUrl }}" class="btn btn-sm btn-soft-primary load-more-order">{{ ('Load More') }}</button>
    </div>
@endif
