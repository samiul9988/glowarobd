@extends(config('app.theme').'frontend.layouts.app')

@section('meta')
<x-seo />
@endsection

@section('content')
    @php
        $first_order = $combined_order->orders->first()
    @endphp
    <section class=" pt-md-4 bg-gray-100">
        <div class="container px-0">
            <div class="row mx-0">
            @foreach ($combined_order->orders as $order)
                <div class="col-xl-8  mx-auto bg-white px-2 pt-2">
                     <h3 class="fs-14 d-flex align-items-center px-2 px-md-4 fw-400">Order Code: <span class="h5  m-0 ml-2 mr-2 fs-14"> {{ $order->code }}</span> <span class="fs-12 py-1  px-3 " style="background-color:#1f2029cf;color:#fff;border-radius:100px">{{ (ucfirst(str_replace('_', ' ', $first_order->delivery_status))) }}</span></h3>
                        <p class="d-flex align-items-center opacity-70 px-2 px-md-4">Date: <span class="ml-1 fs-14 fw-400">{{ date('d-m-Y H:i A', $first_order->date) }}</span></p>
                </div>
            @endforeach
            </div>
        </div>
    </section>
    <section class="bg-gray-100 order-confirm-step">
        <div class="container">
            <div class="row">
                <div class="col-xl-8 mx-auto bg-white pt-5 pb-4">
                    <div class="aiz-steps arrow-divider order-confirm-step-wrapper px-md-3 position-relative">
                        <div class=" done">
                            <div class="text-center ">
                                <span class="step-counter-order">
                                     <i class="fas fa-check"></i>
                                </span>
                                <h3 class="fs-12 fw-400  d-lg-block">{{ ('My Cart')}}</h3>
                            </div>
                        </div>
                        <div class=" done">
                            <div class="text-center ">
                                <!-- <i class="la-3x mb-2 las la-map"></i> -->
                                <span class="step-counter-order">
                                    <i class="fas fa-check"></i>
                                </span>
                                <h3 class="fs-12 fw-400  d-lg-block">{{ ('Shipping info')}}</h3>
                            </div>
                        </div>
                        <div class=" done">
                            <div class="text-center ">
                                <!-- <i class="la-3x mb-2 las la-truck"></i> -->
                                <span class="step-counter-order">
                                    <i class="fas fa-check"></i>
                                </span>
                                <h3 class="fs-12 fw-400  d-lg-block">{{ ('Delivery info')}}</h3>
                            </div>
                        </div>
                        <div class=" done">
                            <div class="text-center ">
                                <!-- <i class="la-3x mb-2 las la-credit-card"></i> -->
                                <span class="step-counter-order">
                                    <i class="fas fa-check"></i>
                                </span>
                                <h3 class="fs-12 fw-400  d-lg-block">{{ ('Payment')}}</h3>
                            </div>
                        </div>
                        <div class="done active">
                            <div class="text-center text-primary">
                                <!-- <i class="la-3x mb-2 las la-check-circle"></i> -->
                                <span class="step-counter-order">5</span>
                                <h3 class="fs-12 fw-400  d-lg-block">{{ ('Confirmation')}}</h3>
                            </div>
                        </div>
                        <span class="incompleted-step" style="  background-color: #ddd;width: 90%;"></span>
                        <span class="completed-step position-absolute" style=" background-color: #11B670; width: 68%;"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class=" bg-gray-100 order-confirm-page">
        <div class="container text-left">
            <div class="row">
                <div class="col-xl-8 mx-auto bg-white">
                    <!-- @php
                        $first_order = $combined_order->orders->first()
                    @endphp -->
                    <div class="text-center pb-2  bg-white pt-2 pb-4">
                        <i class="la la-check-circle la-3x text-success mb-1 mb-md-2"></i>
                        <h1 class="h5 mb-2 fw-600">{{ ('Thank You for Your Order!')}}</h1>
                        <p class="opacity-70 font-italic">{{  translate('A copy or your order summary has been sent to') }} {{ json_decode($first_order->shipping_address)->email }}</p>
                    </div>
                    <div class=" bg-white py-md-4 rounded  pt-0">
                        <h5 class="fw-600 mb-1 mb-md-2 fs-17 pb-1">{{ ('Order Summary')}}</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table">
                                    <!-- <tr>
                                        <td class="w-50 fw-600">{{ ('Order date')}}:</td>
                                        <td>{{ date('d-m-Y H:i A', $first_order->date) }}</td>
                                    </tr> -->
                                    <tr>
                                        <td class="w-50 fw-600 border-top-0">{{ ('Name')}}:</td>
                                        <td >{{ json_decode($first_order->shipping_address)->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ ('Email')}}:</td>
                                        <td>{{ json_decode($first_order->shipping_address)->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ ('Shipping address')}}:</td>
                                        <td>{{ json_decode($first_order->shipping_address)->address }}, {{ json_decode($first_order->shipping_address)->city }}, {{ json_decode($first_order->shipping_address)->country }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table">
                                    <!-- <tr>
                                        <td class="w-50 fw-600">{{ ('Order status')}}:</td>
                                        <td>{{ (ucfirst(str_replace('_', ' ', $first_order->delivery_status))) }}</td>
                                    </tr> -->
                                    <tr>
                                        <td class="w-50 fw-600">{{ ('Total order amount')}}:</td>
                                        <td>{{ single_price($combined_order->grand_total) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ ('Shipping')}}:</td>
                                        <td>{{ ('Flat shipping rate')}}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ ('Payment method')}}:</td>
                                        <td>{{ (ucfirst(str_replace('_', ' ', $first_order->payment_type))) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    @foreach ($combined_order->orders as $order)
                        <div class="card shadow-sm border-0 rounded">
                            <div class="card-body px-0 px-md-2">
                                <!-- <div class="text-center py-4 mb-4">
                                    <h2 class="h5">{{ ('Order Code:')}} <span class="fw-700 text-primary">{{ $order->code }}</span></h2>
                                </div> -->
                                <div>
                                    <h5 class="fw-600 mb-3 fs-17 pb-1">{{ ('Order Details')}}</h5>
                                    <div>
                                        <table class="table table-responsive-md">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th width="30%">{{ ('Product')}}</th>
                                                    <th>{{ ('Variation')}}</th>
                                                    <th>{{ ('Quantity')}}</th>
                                                    <th>{{ ('Delivery Type')}}</th>
                                                    <th class="text-right">{{ ('Price')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($order->orderDetails as $key => $orderDetail)
                                                    <tr>
                                                        <td>{{ $key+1 }}</td>
                                                        <td>
                                                            @if ($orderDetail->product != null)
                                                                <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank" class="text-reset">
                                                                    {{ $orderDetail->product->getTranslation('name') }}
                                                                    @php
                                                                        if($orderDetail->combo_id != null) {
                                                                            $combo = \App\ComboProduct::findOrFail($orderDetail->combo_id);

                                                                            echo '('.$combo->combo_title.')';
                                                                        }
                                                                    @endphp
                                                                </a>
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
                                                                    {{ $orderDetail->pickup_point->getTranslation('name') }} ({{ ('Pickip Point') }})
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td class="text-right">{{ single_price($orderDetail->price) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-5 col-md-6 ml-auto mr-0">
                                            <table class="table ">
                                                <tbody>
                                                    <tr>
                                                        <th class="border-top-0">{{ ('Subtotal')}}</th>
                                                        <td class="text-right">
                                                            <span class="fw-600">{{ single_price($order->orderDetails->sum('price')) }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>{{ ('Shipping')}}</th>
                                                        <td class="text-right">
                                                            <span class="font-italic">{{ single_price($order->orderDetails->sum('shipping_cost')) }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>{{ ('Tax')}}</th>
                                                        <td class="text-right">
                                                            <span class="font-italic">{{ single_price($order->orderDetails->sum('tax')) }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>{{ ('Coupon Discount')}}</th>
                                                        <td class="text-right">
                                                            <span class="font-italic">{{ single_price($order->coupon_discount) }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th><span class="fw-600">{{ ('Total')}}</span></th>
                                                        <td class="text-right">
                                                            <strong><span>{{ single_price(get_order_grand_total($order)) }}</span></strong>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- @if(env('GOOGLE_TAG_MANAGE') == 'ON') --}}
                        @if (get_setting('google_tagmanager'))
                            <script type="text/javascript">
                                dataLayer.push({ ecommerce: null });
                                dataLayer.push({
                                    event    : "purchase",
                                    ecommerce: {
                                        transaction_id: "{{ $order->code }}",
                                        affiliation   : "env('APP_NAME')",
                                        value         : "{{ single_price($order->grand_total) }}",
                                        tax           : "{{ single_price($order->orderDetails->sum('tax')) }}",
                                        shipping      : "{{ single_price($order->orderDetails->sum('shipping_cost')) }}",
                                        currency      : "BDT",
                                        coupon        : "{{ single_price($order->coupon_discount) }}",
                                        items         : [@foreach ($order->orderDetails as $orderDetail){
                                            item_name    : "{{$orderDetail->product->name}}",
                                            item_id      : "{{$orderDetail->product->id}}",
                                            price        : "{{ single_price($orderDetail->price) }}",
                                            item_brand   : "{{$orderDetail->product->brand->name ?? ''}}",
                                            item_category: "{{$orderDetail->product->category->name ?? ''}}",
                                            item_variant : "",
                                            quantity     : "{{$orderDetail->quantity}}"
                                        },@endforeach]
                                    }
                                });

                            </script>
                        @endif





                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
