<div class="modal-header border-0" style="padding-left: 10px">
    <h5 class="modal-title d-none" id="exampleModalLabel">{{ translate('Order id')}}: {{ $order->code }}</h5>
    <h6 class="modal-title font-weight-bold text-center text-secondary" id="exampleModalLabel">{{ translate('Order Details')}}</h6>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
</div>

@php
    $status = $order->orderDetails->first()->delivery_status;
@endphp

<div class="modal-body gry-bg px-2 pt-3" style="overflow-x: clip;">

<div class="d-none d-md-block">
    <div class="py-4">
        <div class="row gutters-5 text-center aiz-steps">

            <div class="col @if($status == 'pending') done @else done @endif">
                <div class="icon">
                    <i class="las la-history" style="font-size: 20px"></i>
                </div>
                <div class="title fs-12">{{ translate('Pending')}}</div>
            </div>


            <div class="col @if($status == 'confirmed') done @elseif($status == 'picked_up' || $status == 'on_the_way' || $status == 'delivered' || $status == 'cancelled') done @endif">
                <div class="icon">
                    <i class="las la-check-square" style="font-size: 20px"></i>
                </div>
              <div class="title fs-12">{{ translate('Confirmed')}}</div>
            </div>


            <div class="col @if($status == 'picked_up') done @elseif($status == 'on_the_way' || $status == 'delivered' || $status == 'cancelled') done @endif">
                <div class="icon">
                    <i class="las la-people-carry" style="font-size: 20px"></i>
                </div>
                <div class="title fs-12">{{ translate('Picked Up')}}</div>
            </div>



            <div class="col @if($status == 'on_the_way') done @elseif($status == 'delivered' || $status == 'cancelled') done @endif">
                <div class="icon">
                    <i class="las la-truck" style="font-size: 20px"></i>
                </div>
                <div class="title fs-12">{{ translate('On The Way')}}</div>
            </div>



            <div class="col @if($status == 'delivered') done @elseif($status == 'cancelled') done @endif">
                <div class="icon">
                    <i class="las la-clipboard-check" style="font-size: 20px"></i>
                </div>
                <div class="title fs-12">{{ translate('Delivered')}}</div>
            </div>
            @if($status == 'cancelled')
            <div class="col @if($status == 'cancelled') done @endif">
                <div class="icon">
                    <i class="las la-ban" style="font-size: 20px"></i>
                </div>
                <div class="title fs-12">{{ translate('Cancelled')}}</div>
            </div>
            @endif
        </div>
    </div> 
</div>

<div class="d-block d-md-none">
    <div class="timeline_container">
        <div class="timeline-block timeline-block-right">
            <div class="timeline_left_content">
                <div class="">
                    <i class="las la-history text-black-50" style="font-size: 20px"></i>
                </div>
            </div>
           <div class="timeline-content">
              <h3>Pending</h3>
           </div>
           <div class="marker @if($status == 'pending') active @else active @endif"><i class="fa fa-check" aria-hidden="true"></i></div>
        </div>

        <div class="timeline-block timeline-block-right">
            <div class="timeline_left_content">
                <div class="">
                    <i class="las la-check-square text-dark" style="font-size: 20px"></i>
                </div>
            </div>
           <div class="timeline-content">
              <h3>Confirmed</h3>
           </div>
           <div class="marker @if($status == 'confirmed') active @elseif($status == 'picked_up' || $status == 'on_the_way' || $status == 'delivered' || $status == 'cancelled') active @endif"><i class="fa fa-check" aria-hidden="true"></i></div>
        </div>
        <div class="timeline-block timeline-block-right">
            <div class="timeline_left_content">
                <div class="">
                    <i class="las la-people-carry text-info" style="font-size: 20px"></i>
                </div>
            </div>
            <div class="timeline-content">
               <h3>Picked Up</h3>
            </div>
            <div class="marker @if($status == 'picked_up') active @elseif($status == 'on_the_way' || $status == 'delivered' || $status == 'cancelled') active @endif"><i class="fa fa-check" aria-hidden="true"></i></div>
         </div>

         <div class="timeline-block timeline-block-right">
            <div class="timeline_left_content">
                <div class="">
                    <i class="far fa-truck text-warning" style="font-size: 20px"></i>
                </div>
            </div>
            <div class="timeline-content">
               <h3>On The Way</h3>
            </div>
            <div class="marker @if($status == 'on_the_way') active @elseif($status == 'delivered' || $status == 'cancelled') active @endif"><i class="fa fa-check" aria-hidden="true"></i></div>
         </div>

         <div class="timeline-block timeline-block-right">
            <div class="timeline_left_content">
                <div class="">
                    <i class="las la-clipboard-check text-success" style="font-size: 20px"></i>
                </div>
            </div>
            <div class="timeline-content">
               <h3>Delivered</h3>
            </div>
            <div class="marker @if($status == 'delivered') active @elseif($status == 'cancelled') active @endif"><i class="fa fa-check" aria-hidden="true"></i></div>
         </div>
         @if($status == 'cancelled')
         <div class="timeline-block timeline-block-right">
            <div class="timeline_left_content">
                <div class="">
                    <i class="las la-ban text-danger" style="font-size: 20px"></i>
                </div>
            </div>
            <div class="timeline-content">
               <h3>Cancelled</h3>
            </div>
            <div class="marker @if($status == 'cancelled') active @endif"><i class="fa fa-check" aria-hidden="true"></i></div>
         </div>
        @endif
    </div>
</div>
    <div class="card">
        <div class="card-body p-2">
            <div class="row">
                <div class="col-6">
                    <p><span class="font-weight-bold">{{ translate('Order Code')}}</span><br><span class="text-danger font-weight-bold">{{ $order->code }}</span></p>
                    <p><span class="font-weight-bold">{{ translate('Order date')}}:</span><br><span>{{ date('d-m-Y H:i A', $order->date) }}</span></p>
                    <p>
                        <span class="font-weight-bold">{{ translate('Payment Status:')}}</span><br>
                        <span>
                            @if ($order->payment_status == 'paid')
                                <span class="">{{translate('Paid')}} <i class="fas fa-check-circle text-success"></i></span>
                            @elseif($order->payment_status == 'refunded')
                                <span class="">{{translate('Refunded')}} <i class="fas fa-times-circle text-warning"></i></span>
                            @else
                                <span class="">{{translate('Unpaid')}} <i class="fas fa-times-circle text-danger"></i></span>
                            @endif
                            @if($order->payment_status_viewed == 0)
                                <span class="ml-2" style="color:green"><strong>*</strong></span>
                            @endif
                        </span>
                    </p>
                    <p>
                        <span class="font-weight-bold">{{ translate('Shipping address:')}}</span><br>
                        <span>
                            {{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, {{ json_decode($order->shipping_address)->postal_code }}, {{ json_decode($order->shipping_address)->country }}
                        </span>
                    </p>
                </div>
                <div class="col-6 text-right">
                    <p><span class="font-weight-bold">{{ translate('Shipping method:')}}</span><br><span>{{ @$order->orderDetails[0]->shippingMethod->name }}<br/>
                        {{ translate('Flat shipping rate')}}</span></p>
                    <p><span class="font-weight-bold">{{ translate('Payment method:')}}</span><br><span>{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</span></p>
                    <p><span class="font-weight-bold">{{ translate('Order date:')}}</span><br><span>{{ date('d-m-Y H:i A', $order->date) }}</span></p>
                    <h6 class="text-danger font-weight-bold">{{ single_price($order->grand_total) }}</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="order_details_bottom">
        @php
            if($order->payment_status != 'paid' && $order->delivery_status=='pending'):
            @endphp
                <button type="button" class="btn btn-danger btn-block rounded-4" onclick="cancel_order({{ $order->id }})"><b class="fs-15">{{ translate('Cancel Order') }}</b></button>
            @php
            endif;
        @endphp
        
        {{-- Refund Request --}}
        @if($order->payment_status == 'paid' && $order->delivery_status=='pending' && $order->payment_type == 'bkash')
            <button type="button" class="btn btn-warning btn-block rounded-4" data-toggle="collapse" data-target="#refundCollapse" aria-expanded="false" aria-controls="refundCollapse"><b class="fs-15">{{ translate('Request Refund') }}</b></button>
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
        <h6 class="text-center text-info my-3 font-weight-bold">Ordered Product</h6>
        @foreach ($order->orderDetails as $key => $orderDetail)
        <div class="card p-3 mb-2">
            <p class="mb-0">
                @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                    <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank">{{ $orderDetail->product->getTranslation('name') }}</a>
                @elseif($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                    <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank">{{ $orderDetail->product->getTranslation('name') }}</a>
                @else
                    <strong>{{  translate('Product Unavailable') }}</strong>
                @endif

            </p>
            <p class="font-weight-bold mb-0">{{ $orderDetail->quantity }} x Item <span class="float-right text-danger">{{ single_price($orderDetail->price) }}</span></p>
        </div>
        @endforeach
    </div>

    <div class="col-12 mt-3">
        <div class="row">
            <div class="col-8 text-right">
                <p class="fw-600">{{ translate('Subtotal')}}</p>
                <p class="fw-600">{{ translate('Tax')}}</p>
                <p class="fw-600">{{ translate('Shipping Cost')}}</p>
                <p class="fw-600">{{ translate('Discount')}}</p>
                <p class="fw-600">{{ translate('Reward Point Discount')}}</p>
            </div>
            <div class="col-4 text-right">
                <p>{{ single_price($order->orderDetails->sum('price')) }}</p>
                <p>{{ single_price($order->orderDetails->sum('tax')) }}</p>
                <p>{{ single_price($order->orderDetails->sum('shipping_cost')) }}</p>
                <p>{{ single_price($order->coupon_discount) }}</p>
                <p>{{ single_price($order->reward_point_discount) }}</p>
            </div>
        </div>
    </div>

    <hr>
    <div class="col-12">
        <div class="row">
            <div class="col-8 text-right">
                <p class="fw-600">{{ translate('Grand Total')}}</p>
            </div>
            <div class="col-4 text-right">
                <p class="text-danger font-weight-bold">{{ single_price($order->grand_total) }}</p>
            </div>
        </div>
    </div>

    <div class="card mt-4 d-none">
        <div class="card-header">
          <b class="fs-15">{{ translate('Order Summary') }}</b>
            @php
            if($order->payment_status != 'paid' && $order->delivery_status=='pending'):
            @endphp
          <button type="button" class="btn btn-success" onclick="cancel_order({{ $order->id }})"><b class="fs-15">{{ translate('Cancel Order') }}</b></button>
          @php
            endif;
            @endphp
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Order Code')}}:</td>
                            <td>{{ $order->code }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Customer')}}:</td>
                            <td>{{ json_decode($order->shipping_address)->name }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Email')}}:</td>
                            @if ($order->user_id != null)
                                <td>{{ $order->user->email }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Shipping address')}}:</td>
                            <td>{{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, {{ json_decode($order->shipping_address)->postal_code }}, {{ json_decode($order->shipping_address)->country }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-lg-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Order date')}}:</td>
                            <td>{{ date('d-m-Y H:i A', $order->date) }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Order status')}}:</td>
                            <td>{{ translate(ucfirst(str_replace('_', ' ', $status))) }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Total order amount')}}:</td>
                            <td>{{ single_price($order->orderDetails->sum('price') + $order->orderDetails->sum('tax')) }}</td>
                        </tr>
                        @if($order->orderDetails[0]->shipping_type=='home_delivery')
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Shipping method')}}:</td>
                            <td>
                                {{ @$order->orderDetails[0]->shippingMethod->name }}<br/>
                                {{ translate('Flat shipping rate')}}
                            </td>
                        </tr>
                        @endif
                        @if($order->orderDetails[0]->shipping_type=='pickup_point')
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Shipping method')}}:</td>
                            <td>
                                Pickup Point<br>
                                {{ @$order->orderDetails[0]->pickup_point->name }}<br>
                                {{ @$order->orderDetails[0]->pickup_point->address }}<br>
                                {{ @$order->orderDetails[0]->pickup_point->phone }}<br>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Payment method')}}:</td>
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
                  <b class="fs-15">{{ translate('Order Details') }}</b>
                </div>
                <div class="card-body pb-0">
                    <table class="table table-borderless table-responsive">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th width="30%">{{ translate('Product')}}</th>
                                <th>{{ translate('Variation')}}</th>
                                <th>{{ translate('Quantity')}}</th>
                                <th>{{ translate('Delivery Type')}}</th>
                                <th>{{ translate('Price')}}</th>
                                @if (addon_is_activated('refund_request'))
                                    <th>{{ translate('Refund')}}</th>
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
                  <b class="fs-15">{{ translate('Order Ammount') }}</b>
                </div>
                <div class="card-body pb-0">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Subtotal')}}</td>
                                <td class="text-right">
                                    <span class="strong-600">{{ single_price($order->orderDetails->sum('price')) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Shipping')}}</td>
                                <td class="text-right">
                                    <span class="text-italic">{{ single_price($order->orderDetails->sum('shipping_cost')) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Tax')}}</td>
                                <td class="text-right">
                                    <span class="text-italic">{{ single_price($order->orderDetails->sum('tax')) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Coupon')}}</td>
                                <td class="text-right">
                                    <span class="text-italic">{{ single_price($order->coupon_discount) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Total')}}</td>
                                <td class="text-right">
                                    <strong><span>{{ single_price($order->grand_total) }}</span></strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($order->manual_payment && $order->manual_payment_data == null)
                <button onclick="show_make_payment_modal({{ $order->id }})" class="btn btn-block btn-primary">{{ translate('Make Payment')}}</button>
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
