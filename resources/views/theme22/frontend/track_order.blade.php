@extends(config('app.theme').'frontend.layouts.app')

@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ ('Track Order') }}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ ('Home') }}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('orders.track') }}">"{{ ('Track Order') }}"</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="mb-5">
    <div class="container text-left">
        <div class="row">
            <div class="col-xxl-5 col-xl-6 col-lg-8 mx-auto">
                <form class="" action="{{ route('orders.track') }}" method="GET" enctype="multipart/form-data">
                    <div class="bg-white rounded shadow-sm">
                        <div class="fs-15 fw-600 p-3 border-bottom text-center">
                            {{ ('Check Your Order Status')}}
                        </div>
                        <div class="form-box-content p-3">
                            <div class="form-group">
                                <input type="text" class="form-control mb-3" placeholder="{{ ('Order Code')}}" name="order_code" value="{{ request('order_code') }}" required>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">{{ ('Track Order')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @isset($order)
            <div class="bg-white rounded shadow-sm mt-5">
                <div class="fs-15 fw-600 p-3 border-bottom">
                    {{ ('Order Summary')}}
                </div>
                <div class="p-3">
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
                                @if(filled($order->user?->email ?? null))
                                    <tr>
                                        <td class="w-50 fw-600">{{ ('Email')}}:</td>
                                        <td>{{ $order->user?->email ?? '' }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="w-50 fw-600">{{ ('Shipping address')}}:</td>
                                    <td>{{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, {{ json_decode($order->shipping_address)->country }}</td>
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
                                    <td class="w-50 fw-600">{{ ('Total order amount')}}:</td>
                                    <td>{{ single_price($order->orderDetails->sum('price') + $order->orderDetails->sum('tax')) }}</td>
                                </tr>
                                <tr>
                                    <td class="w-50 fw-600">{{ ('Shipping method')}}:</td>
                                    <td>{{ ('Flat shipping rate')}}</td>
                                </tr>
                                <tr>
                                    <td class="w-50 fw-600">{{ ('Payment method')}}:</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</td>
                                </tr>
                                <tr>
                                    <td class="w-50 fw-600">{{ ('Delivery Status')}}:</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $order->delivery_status)) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded shadow-sm mt-4">
                <div class="p-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ ('Product Name')}}</th>
                                <th>{{ ('Quantity')}}</th>
                                <th>{{ ('Shipped By')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->orderDetails as $key => $orderDetail)
                                @if ($orderDetail->quantity < 1)
                                    @continue
                                @endif
                                @php
                                    $status = $order->delivery_status;
                                @endphp
                                @if($orderDetail->product != null)
                                <tr>
                                    <td>{{ $orderDetail->product->getTranslation('name') }} ({{ $orderDetail->variation }})</td>
                                    <td>{{ $orderDetail->quantity }}</td>
                                    <td>{{ $orderDetail->product->user->name }}</td>
                                </tr>
                                @else
                                <tr>
                                    <td colspan="3" class="text-center">{{ ('Product not found') }}</td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endisset
    </div>
</section>

@endsection
