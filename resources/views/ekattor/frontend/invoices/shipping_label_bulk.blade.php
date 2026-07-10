<div>
    @php
        $logo = get_setting('header_logo');
    @endphp

    <div style="padding: 0.5rem;">
        <table class="padding text-left small border-bottom">
            <tbody class="strong">
                <tr>
                    <td style="text-align:center;"> @php
                        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                        echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($order->code, $generator::TYPE_CODE_128, 1)) . '">';
                        @endphp
                        <br>
                        <span class="strong">{{ $order->code }}</span>
                    </td>
                </tr>

                <tr>

                    <td style="text-align: center;">
                        <table class="padding text-left small border-bottom" style="width: 60%">
                            <tr>
                                <td>
                                    @if($logo != null)
                                    <img src="{{ uploaded_asset($logo) }}" height="30" style="display:inline-block;">
                                @else
                                    <img src="{{ static_asset('assets/img/logo.png') }}" height="30" style="display:inline-block;">
                                @endif
                                <br>
                                <strong>{{ get_setting('site_name') }}</strong>
                                <br>
                                <strong>{{  translate('Phone') }}:</strong> {{ get_setting('contact_phone') }}
                                <br>
                                <strong>{{  translate('Email') }}:</strong> {{ get_setting('contact_email') }}
                                <br>
                                {{ get_setting('contact_address',null,App::getLocale()) }}
                                </td>
                                <td>
                                    @php
                                    $shipping_address = json_decode($order->shipping_address);
                                @endphp
                                    <td>
                                        <strong>{{ translate('Ship to') }}:</strong>
                                    <br>
                                    {{ $shipping_address->name }}
                                    <br>
                                    <strong>{{ translate('Phone') }}:</strong> {{ $shipping_address->phone }}
                                    <br>
                                    <strong>{{ translate('Email') }}:</strong> {{ $shipping_address->email }}
                                    <br>
                                    {{ $shipping_address->address }},
                                    <br>
                                    City: {{ $shipping_address->city }},
                        Area: {{ @$shipping_address->area }},
                        @if($shipping_address->postal_code!='')
                        Postal Code: {{ $shipping_address->postal_code }},
                        @endif
                        Country: {{ $shipping_address->country }}
                                </td>
                            </tr>
                        </table>

                        </td>
                </tr>

                <tr>

                    <td style="text-align: center;">
                        <table class="padding text-left small border-bottom" style="width:50%;">
                            <tr>
                                <td>{{ translate('Delivery Type') }}</td>
                                <td>@if ($order->orderDetails[0]->shipping_type != null && $order->orderDetails[0]->shipping_type == 'home_delivery')
                                    {{ translate('Home Delivery') }}
                                @elseif ($order->orderDetails[0]->shipping_type == 'pickup_point')
                                    @if ($order->orderDetails[0]->pickup_point != null)
                                        {{ $order->orderDetails[0]->pickup_point->getTranslation('name') }} ({{ translate('Pickip Point') }})
                                    @endif
                                @endif</td>
                            </tr>
                            @if($order->orderDetails[0]->shipping_type == 'home_delivery')
                            <tr>
                                <td>{{ translate('Shipping Method') }}</td>
                                <td>{{ @$order->orderDetails[0]->shippingMethod->name }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td>{{ translate('Payment Type') }}</td>
                                <td>{{ str_replace('_',' ',strtoupper($order->payment_type))  }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Item Number') }}:</strong></td>
                                <td>{{ count($order->orderDetails) }}</td>
                            </tr>
                        </table>
                        </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- <htmlpagefooter name="myFooter2">
    <table width="100%" style="padding: 0px 70px;">
        <tr>
            <td width="33%">Print Date: {DATE j-m-Y}</td>
            <td width="33%" align="center"></td>
            <td width="33%" style="text-align: center; border-top:solid 1px #000000">Authorized Signature</td>
        </tr>
    </table>
</htmlpagefooter> --}}
{{-- <sethtmlpagefooter name="myFooter2" value="on" /> --}}
<?php if($counter < count($ids)):?>
<p style="page-break-after: auto;">&nbsp;</p>
<?php endif;?>

