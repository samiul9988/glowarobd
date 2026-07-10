@php
    $status = $order->orderDetails->first()->delivery_status;
@endphp
<div class="modal-header border-0 align-items-baseline ml-sm-3 " >
    <h5 class="modal-title d-none" id="exampleModalLabel">{{ ('Order id')}}: {{ $order->code }} </h5>
    <div>
        <h6 class="modal-title " id="exampleModalLabel">
            {{ ('Order Details')}}
            <span class="text-capitalize order-status d-inline d-sm-none">{{ $status }}</span>
            #{{ $order->code }}
            <span class="text-capitalize order-status d-none d-sm-inline">{{ $status }}</span>
        </h6>
        <p>
            <span class="">
                {{ ('Date')}}: {{ date('d-m-Y H:i A', $order->date) }}
            </span>
        </p>
    </div>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body gry-bg px-6 pb-4 pt-0" style="overflow-x: clip;">

<div class="d-none d-md-block order-details">
    <div class="pb-4">
        <div class="row gutters-5 text-center aiz-steps ">

            <!-- <div class="col @if($status == 'pending') done @else done @endif">
                <div class="icon">
                    <i class="las la-history" style="font-size: 20px"></i>
                </div>
                <div class="title fs-12">{{ ('Pending')}}</div>
            </div> -->


            <div class="col done @if($status == 'confirmed') done @elseif($status == 'picked_up' || $status == 'on_the_way' || $status == 'delivered' || $status == 'cancelled') done @endif">
                <div class="icon">
                @if($status == 'confirmed')
                    <i class="fas fa-check"></i>
                @elseif($status == 'picked_up' || $status == 'on_the_way' || $status == 'delivered' || $status == 'cancelled')
                    <i class="fas fa-check"></i>
                @else
                    1
                @endif
                    <!-- <i class="las la-check-square" style="font-size: 20px"></i> -->
                </div>
              <div class="title fs-12">{{ ('Confirmed')}}</div>
            </div>


            <div class="col @if($status == 'picked_up') done @elseif($status == 'on_the_way' || $status == 'delivered' || $status == 'cancelled') done @endif">
                <div class="icon">
                @if($status == 'picked_up')
                    <i class="fas fa-check"></i>
                @elseif($status == 'on_the_way' || $status == 'delivered' || $status == 'cancelled')
                    <i class="fas fa-check"></i>
                @else
                    2
                @endif

                </div>
                <div class="title fs-12">{{ ('Picked Up')}}</div>
            </div>



            <div class="col @if($status == 'on_the_way') done @elseif($status == 'delivered' || $status == 'cancelled') done @endif">
                <div class="icon">
                    @if($status == 'on_the_way')
                        <i class="fas fa-check"></i>
                    @elseif($status == 'delivered' || $status == 'cancelled')
                        <i class="fas fa-check"></i>
                    @else
                    3
                    @endif
                    <!-- <i class="las la-truck" style="font-size: 20px"></i> -->
                </div>
                <div class="title fs-12">{{ ('On The Way')}}</div>
            </div>



            <div class="col @if($status == 'delivered') done @elseif($status == 'cancelled') done @endif">
                <div class="icon">
                @if($status == 'delivered')
                 <i class="fas fa-check"></i>
                @elseif($status == 'cancelled')
                    <i class="fas fa-check"></i>
                @else
                    4
                @endif
                    <!-- <i class="las la-clipboard-check" style="font-size: 20px"></i> -->
                </div>
                <div class="title fs-12">{{ ('Delivered')}}</div>
            </div>
            @if($status == 'cancelled')
            <div class="col @if($status == 'cancelled') done @endif">
                <div class="icon">
                    <i class="las la-ban" style="font-size: 20px"></i>
                </div>
                <div class="title fs-12">{{ ('Cancelled')}}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="d-block d-md-none">
    <div class="timeline_container">
        <!-- <div class="timeline-block timeline-block-right">
            <div class="timeline_left_content">
                <div class="marker @if($status == 'pending') active @else active @endif"><i class="fa fa-check" aria-hidden="true"></i></div>
            </div>
           <div class="timeline-content">
              <h3>Pending</h3>
           </div>

        </div> -->

        <div class="timeline-block timeline-block-right">
            <div class="timeline_left_content">
                <div class="">
                    <div class="marker @if($status == 'pending') active @elseif($status == 'picked_up' || $status == 'on_the_way' || $status == 'delivered' || $status == 'cancelled') active @endif">

                        @if($status == 'pending')
                        <i class="fas fa-check"></i>
                        @elseif($status == 'picked_up' || $status == 'on_the_way' || $status == 'delivered' || $status == 'cancelled')
                        <i class="fas fa-check"></i>
                        @else
                        1
                        @endif
                    </div>
                </div>
            </div>
           <div class="timeline-content">
              <h3>Confirmed</h3>
           </div>

        </div>
        <div class="timeline-block timeline-block-right">
            <div class="timeline_left_content">
                <div class="">
                 <div class="marker @if($status == 'picked_up') active @elseif($status == 'on_the_way' || $status == 'delivered' || $status == 'cancelled') active @endif">
                 @if($status == 'picked_up')
                 <i class="fas fa-check"></i>
                 @elseif($status == 'on_the_way' || $status == 'delivered' || $status == 'cancelled')
                 <i class="fas fa-check"></i>
                 @else
                    2
                 @endif
                </div>
                </div>
            </div>
            <div class="timeline-content">
               <h3>Picked Up</h3>
            </div>

         </div>

         <div class="timeline-block timeline-block-right">
            <div class="timeline_left_content">
                <div class="">
                    <div class="marker @if($status == 'on_the_way') active @elseif($status == 'delivered' || $status == 'cancelled') active @endif">
                        @if($status == 'on_the_way')
                        <i class="fas fa-check"></i>
                        @elseif($status == 'delivered' || $status == 'cancelled')
                        <i class="fas fa-check"></i>
                        @else
                        3
                        @endif
                    </div>
                </div>
            </div>
            <div class="timeline-content">
               <h3>On The Way</h3>
            </div>

         </div>

         <div class="timeline-block timeline-block-right">
            <div class="timeline_left_content">
                <div class="">
                    <div class="marker @if($status == 'delivered') active @elseif($status == 'cancelled') active @endif">
                        @if($status == 'delivered')
                        <i class="fas fa-check"></i>
                        @elseif($status == 'cancelled')
                        <i class="fas fa-check"></i>
                        @else
                        4
                        @endif
                    </div>
                </div>
            </div>
            <div class="timeline-content">
               <h3>Delivered</h3>
            </div>

         </div>
         @if($status == 'cancelled')
         <div class="timeline-block timeline-block-right">
            <div class="timeline_left_content">
                <div class="">
                    <div class="marker @if($status == 'cancelled') active @endif"><i class="fa fa-check" aria-hidden="true"></i></div>
                </div>
            </div>
            <div class="timeline-content">
               <h3>Cancelled</h3>
            </div>

         </div>
        @endif
    </div>
</div>
    <div class="card border-0 shadow-none">
        <div class="card-body py-3 px-0 px-sm-3">
            <div class="">
            <table class="w-100">
                    <tr class="row">
                        <td class="col-6">
                            <p>
                                <span class=" shipping-title d-block">{{ ('Shipping address')}}:</span>
                                <span class="shipping-address" style="color:#000">
                                    {{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, {{ @json_decode($order->shipping_address)->postal_code ?? '' }}, {{ json_decode($order->shipping_address)->country }}
                                </span>
                            </p>
                        </td>
                        <td class="col-6">
                            <p>
                                <span class=" shipping-title d-block">{{ ('Shipping method')}}:</span>
                                <span class="shipping-address" style="color:#000">{{ @$order->orderDetails[0]->shippingMethod->name }}<br/>
                                {{ ('Flat shipping rate')}}</span>
                            </p>
                        </td>
                    </tr>
                    <tr class="row">
                        <td class="col-6">
                             <p>
                                <span class=" shipping-title d-block">{{ ('Payment method')}}:</span>
                                <span class="shipping-address" style="color:#000">{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</span>
                            </p>
                        </td>
                        <td class="col-6">
                        <p>
                            <span class="shipping-title d-block">{{ ('Payment Status')}}:</span>
                            <span class="shipping-address" style="color:#000">
                                @if ($order->payment_status == 'paid')
                                    <span class="text-success">{{ ('Paid')}}</span>
                                @elseif($order->payment_status == 'refunded')
                                    <span class="text-warning">{{ ('Refunded')}} </span>
                                @elseif($order->payment_status == 'unpaid')
                                    <span class="text-danger">{{ ('Unpaid')}} </span>
                                @else
                                    <span class="text-info">{{ (ucfirst($order->payment_status))}} </span>
                                @endif
                                @if($order->payment_status_viewed == 0)
                                    <span class="ml-2" style="color:green"><strong>*</strong></span>
                                @endif
                            </span>
                        </p>
                        </td>
                    </tr>
            </table>
                <!-- <div class="col-12"> -->
                    <!-- <p><span class="font-weight-bold">{{ ('Order Code')}}</span><br><span class="text-danger font-weight-bold">{{ $order->code }}</span></p> -->
                    <!-- <p><span class="font-weight-bold">{{ ('Order date')}}:</span><br><span>{{ date('d-m-Y H:i A', $order->date) }}</span></p> -->
                    <!-- <p>
                        <span class="font-weight-bold">{{ ('Payment Status:')}}</span><br>
                        <span>
                            @if ($order->payment_status == 'paid')
                                <span class="">{{ ('Paid')}} <i class="fas fa-check-circle text-success"></i></span>
                            @elseif($order->payment_status == 'refunded')
                                <span class="">{{ ('Refunded')}} <i class="fas fa-times-circle text-warning"></i></span>
                            @else
                                <span class="">{{ ('Unpaid')}} <i class="fas fa-times-circle text-danger"></i></span>
                            @endif
                            @if($order->payment_status_viewed == 0)
                                <span class="ml-2" style="color:green"><strong>*</strong></span>
                            @endif
                        </span>
                    </p> -->



                <!-- </div> -->
                <!-- <div class="col-6 ">

                    <p><span class="font-weight-bold">{{ ('Payment method:')}}</span><br><span>{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</span></p>
                    <p><span class="font-weight-bold">{{ ('Order date:')}}</span><br><span>{{ date('d-m-Y H:i A', $order->date) }}</span></p>
                    <h6 class="text-danger font-weight-bold">{{ single_price($order->grand_total) }}</h6>

                </div> -->
            </div>
        </div>
    </div>

    <div class="order_details_bottom">
        <!-- @php
            if($order->payment_status != 'paid' && $order->delivery_status=='pending'):
            @endphp
                <button type="button" class="btn btn-danger btn-block rounded-4" onclick="cancel_order({{ $order->id }})"><b class="fs-15">{{ ('Cancel Order') }}</b></button>
            @php
            endif;
        @endphp -->

        {{-- Refund Request --}}
        <!-- @if($order->payment_status == 'paid' && $order->delivery_status=='pending' && $order->payment_type == 'bkash')
            <button type="button" class="btn btn-warning btn-block rounded-4" data-toggle="collapse" data-target="#refundCollapse" aria-expanded="false" aria-controls="refundCollapse"><b class="fs-15">{{ ('Request Refund') }}</b></button>
        @endif
        <div class="collapse mt-2" id="refundCollapse">
            <div class="card bg-danger card-body">
                <select class="form-control mb-3" name="refund_reason" id="refund_reason" aria-label="Reason for refund">
                    <option value="" selected>Select a reason</option>
                    <option value="Change of Mind">Change of Mind</option>
                    <option value="Ordered by mistake">Ordered by mistake</option>
                    <option value="Made payment by mistake">Made payment by mistake</option>
                    <option value="I have been informed stock out">I have been informed stock out</option>
                    <option value="Wanted to order a different product">Wanted to order a different product</option>
                    <option value="Others">Others</option>
                </select>
                <button type="submit" onclick="refund_request({{ $order->id }})" class="btn btn-primary">Make Request</button>
            </div>
        </div>
        <h6 class="text-center text-info my-3 font-weight-bold">Ordered Product</h6> -->
        <div class="card mb-2 shadow-none border-0">
            <table>
                <tr class=" table-header">
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right" >Unit Price</th>
                    <th class="text-right">Amount</th>
                </tr>
                @foreach ($order->orderDetails as $key => $orderDetail)
                <tr>
                    <td class="product-name">
                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                        <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank">{{ $orderDetail->product->getTranslation('name') }}</a>
                        @elseif($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                            <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank">{{ $orderDetail->product->getTranslation('name') }}</a>
                        @else
                            <strong>{{  translate('Product Unavailable') }}</strong>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ $orderDetail->quantity }}
                    </td>
                    <td class="text-right">
                        {{ single_price($orderDetail->price/$orderDetail->quantity) }}
                    </td>
                    <td class="text-right">
                        {{ single_price($orderDetail->price) }}
                    </td>
                </tr>
                @endforeach
            </table>
            <!-- <p class="mb-0">
                @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                    <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank">{{ $orderDetail->product->getTranslation('name') }}</a>
                @elseif($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                    <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank">{{ $orderDetail->product->getTranslation('name') }}</a>
                @else
                    <strong>{{  translate('Product Unavailable') }}</strong>
                @endif

            </p>
            <p class="font-weight-bold mb-0">{{ $orderDetail->quantity }} x Item <span class="float-right text-danger">
                {{ single_price($orderDetail->price) }}</span></p> -->
        </div>
    </div>

    <div class="col-12 mt-4">
        <div class="row pt-3">
            <div class="col-6 col-sm-10 text-right pr-0 pr-sm-2">
                <p class="">{{ ('Subtotal')}}</p>
                <p class="">{{ ('Tax')}}</p>
                <p class="">{{ ('Shipping Cost')}}</p>
                <p class="">{{ ('Discount')}}</p>
                @if($order->payments->sum('amount') > 0)
                    <p class="">{{ ('Paid Amount')}}</p>
                @endif
            </div>
            <div class="col-6 col-sm-2 text-right">
                <p>{{ single_price($order->orderDetails->sum('price')) }}</p>
                <p>{{ single_price($order->orderDetails->sum('tax')) }}</p>
                <p>{{ single_price($order->orderDetails->sum('shipping_cost')) }}</p>
                <p>{{ single_price($order->coupon_discount) }}</p>
                @if($order->payments->sum('amount') > 0)
                    <p>{{ single_price($order->payments->sum('amount')) }} (-)</p>
                @endif
            </div>
        </div>
    </div>

    <hr>
    <div class="col-12">
        <div class="row align-items-center">
            <div class="col-4 col-sm-2 pl-0 pl-sm-auto">
            @php
                if($order->payment_status != 'paid' && $order->delivery_status=='pending'):
                    @endphp

                       <p type="button" class="btn  p-0 text-danger btn-block rounded-2 fs-14 fw-400 text-nowrap" onclick="cancel_order({{ $order->id }})">{{ ('Cancel Order') }}</p>

                    @php
                endif;
            @endphp
            </div>
            <div class="col-3 col-sm-8 text-right">
                <p class="">{{ ('Total')}}</p>
            </div>
            <div class="col-5 col-sm-2 text-right">
                {{-- <p class="font-weight-bold">{{ single_price($order->grand_total) }}</p> --}}
                <p class="text-danger font-weight-bold">{{ single_price(max(0, get_order_grand_total($order) - $order->payments->sum('amount'))) }}</p>
            </div>
        </div>
    </div>

    <div class="card mt-4 d-none pr-0 pr-sm-auto">
        <div class="card-header">
          <b class="fs-15">{{ ('Order Summary') }}</b>
            @php
            if($order->payment_status != 'paid' && $order->delivery_status=='pending'):
            @endphp
          <button type="button" class="btn btn-success" onclick="cancel_order({{ $order->id }})"><b class="fs-15">{{ ('Cancel Order') }}</b></button>
          @php
            endif;
            @endphp
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="w-50 fw-600">{{ ('Order Code')}}:</td>
                            <td>{{ $order->code }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ ('Customer')}}:</td>
                            <td>{{ json_decode($order->shipping_address)->name }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ ('Email')}}:</td>
                            @if ($order->user_id != null)
                                <td>{{ $order->user->email }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ ('Shipping address')}}:</td>
                            <td>{{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, {{ @json_decode($order->shipping_address)->postal_code ?? '' }}, {{ json_decode($order->shipping_address)->country }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-lg-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="w-50 fw-600">{{ ('Order date')}}:</td>
                            <td>{{ date('d-m-Y H:i A', $order->date) }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ ('Order status')}}:</td>
                            <td>{{ (ucfirst(str_replace('_', ' ', $status))) }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ ('Total order amount')}}:</td>
                            <td>{{ single_price($order->orderDetails->sum('price') + $order->orderDetails->sum('tax')) }}</td>
                        </tr>
                        @if($order->orderDetails[0]->shipping_type=='home_delivery')
                        <tr>
                            <td class="w-50 fw-600">{{ ('Shipping method')}}:</td>
                            <td>
                                {{ @$order->orderDetails[0]->shippingMethod->name }}<br/>
                                {{ ('Flat shipping rate')}}
                            </td>
                        </tr>
                        @endif
                        @if($order->orderDetails[0]->shipping_type=='pickup_point')
                        <tr>
                            <td class="w-50 fw-600">{{ ('Shipping method')}}:</td>
                            <td>
                                Pickup Point<br>
                                {{ @$order->orderDetails[0]->pickup_point->name }}<br>
                                {{ @$order->orderDetails[0]->pickup_point->address }}<br>
                                {{ @$order->orderDetails[0]->pickup_point->phone }}<br>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td class="w-50 fw-600">{{ ('Payment method')}}:</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row d-none">
        <div class="col-lg-9">
            <div class="card mt-4">
                <div class="card-header">
                  <b class="fs-15">{{ ('Order Details') }}</b>
                </div>
                <div class="card-body pb-0">
                    <table class="table table-borderless table-responsive">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th width="30%">{{ ('Product')}}</th>
                                <th>{{ ('Variation')}}</th>
                                <th>{{ ('Quantity')}}</th>
                                <th>{{ ('Delivery Type')}}</th>
                                <th>{{ ('Price')}}</th>
                                @if (addon_is_activated('refund_request'))
                                    <th>{{ ('Refund')}}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->orderDetails as $key => $orderDetail)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>
                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                            <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank">{{ $orderDetail->product->getTranslation('name') }}</a>
                                        @elseif($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                            <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank">{{ $orderDetail->product->getTranslation('name') }}</a>
                                        @else
                                            <strong>{{  translate('Product Unavailable') }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $orderDetail->variation }}
                                    </td>
                                    <td>
                                        {{ $orderDetail->quantity }}

                                    </td>
                                    <td>
                                        @if ($orderDetail->shipping_type != null && $orderDetail->shipping_type == 'home_delivery')
                                            {{  translate('Home Delivery') }}
                                        @elseif ($orderDetail->shipping_type == 'pickup_point')
                                            @if ($orderDetail->pickup_point != null)
                                                {{ $orderDetail->pickup_point->name }} ({{  translate('Pickip Point') }})
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ single_price($orderDetail->price) }}</td>
                                    @if (addon_is_activated('refund_request'))
                                        @php
                                            $no_of_max_day = get_setting('refund_request_time');
                                            $last_refund_date = $orderDetail->created_at->addDays($no_of_max_day);
                                            $today_date = Carbon\Carbon::now();
                                        @endphp
                                        <td>
                                            @if ($orderDetail->product != null &&
                                            $orderDetail->product->refundable != 0 &&
                                            $orderDetail->refund_request == null &&
                                            $today_date <= $last_refund_date &&
                                            $orderDetail->payment_status == 'paid' &&
                                            $orderDetail->delivery_status == 'delivered')
                                                <a href="{{route('refund_request_send_page', $orderDetail->id)}}" class="btn btn-primary btn-sm">{{  translate('Send') }}</a>
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 0)
                                                <b class="text-info">{{  translate('Pending') }}</b>
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 2)
                                                <b class="text-success">{{  translate('Rejected') }}</b>
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 1)
                                                <b class="text-success">{{  translate('Approved') }}</b>
                                            @elseif ($orderDetail->product->refundable != 0)
                                                <b>{{  translate('N/A') }}</b>
                                            @else
                                                <b>{{  translate('Non-refundable') }}</b>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card mt-4">
                <div class="card-header">
                  <b class="fs-15">{{ ('Order Ammount') }}</b>
                </div>
                <div class="card-body pb-0">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td class="w-50 fw-600">{{ ('Subtotal')}}</td>
                                <td class="text-right">
                                    <span class="strong-600">{{ single_price($order->orderDetails->sum('price')) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ ('Shipping')}}</td>
                                <td class="text-right">
                                    <span class="text-italic">{{ single_price($order->orderDetails->sum('shipping_cost')) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ ('Tax')}}</td>
                                <td class="text-right">
                                    <span class="text-italic">{{ single_price($order->orderDetails->sum('tax')) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ ('Coupon')}}</td>
                                <td class="text-right">
                                    <span class="text-italic">{{ single_price($order->coupon_discount) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ ('Total')}}</td>
                                <td class="text-right">
                                    <strong><span>{{ single_price(get_order_grand_total($order)) }}</span></strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($order->manual_payment && $order->manual_payment_data == null)
                <button onclick="show_make_payment_modal({{ $order->id }})" class="btn btn-block btn-primary">{{ ('Make Payment')}}</button>
            @endif
        </div>
    </div>
</div>

<script type="text/javascript">
    function show_make_payment_modal(order_id){
        $.post('{{ route('checkout.make_payment') }}', {_token:'{{ csrf_token() }}', order_id : order_id}, function(data){
            $('#payment_modal_body').html(data);
            $('#payment_modal').modal('show');
            $('input[name=order_id]').val(order_id);
        });
    }
</script>
