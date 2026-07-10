<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    {{-- <meta charset="utf-8"> --}}
    <style>
        @font-face {
            font-family: 'label';
            src: url("{{ static_asset('assets/fonts/label.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'bangla';
            src: url("{{ static_asset('assets/fonts/bn.ttf') }}") format('truetype');
        }
        @page {
            /* size: 80mm 140mm; */
            width: 82mm;
            margin: 5mm;
        }

        body {
            font-size: 8pt;
            margin: 0;
        }

        .label {
            font-family: 'label';
            font-size: 12pt;
            line-height: 1;
        }
        .bn {
            font-family: 'bangla';
            font-size: 10pt;
            line-height: 1;
        }
        .container {
            border: 1px dotted #000;
            border-radius: 5px;
            width: 100%;
            padding: 10px;
        }

        .center {
            text-align: center;
        }

        .barcode {
            margin: 0;
            margin-bottom: 5px;
        }

        .info {
            /* margin-top: 10px; */
            font-size: 8pt;
        }
        p{
            line-height: 1;
            margin: 0;
        }
        .title {
            font-size: 10pt;
            /* margin: 0; */
            margin-bottom: 5px !important;
        }
        .subtitle {
            font-size: 8pt;
            margin: 0;
            /* margin-bottom: 7px !important; */
        }
        .heading {
            font-size: 11pt;
        }
        .label-bold{
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .cell {
            border-right: 1px dotted #000;
            border-radius: 5px;
            padding-left: 5px;
            padding-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container center">
        <h3 class="title label-bold">{{ get_setting('site_name') }}</h3>
        <div class="subtitle">
            <p class="label">{{ get_setting('site_motto') }}</p>
        </div>
        <p class="label">{{ get_setting('contact_address',null,App::getLocale()) }}<br>
        Call : {{ get_setting('contact_phone') }}</p>
        <hr style="margin-bottom: 0;">
        {{-- <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="label">BIN. NO. : {{ env('BIN_NO') }}</span>
            <span class="label">Mushak 6.3</span>
        </div> --}}
        <div>
            <table style="width: 100%;">
                <tr>
                    <td class="label" style="text-align: left;">BIN. NO. : {{ env('BIN_NO') }}</td>
                    <td class="label" style="text-align: right;">Mushak 6.3</td>
                </tr>
            </table>
        </div>
        <hr style="margin-top: 0;">

        <div class="info" style="text-align: left !important;">
            <p class="label">
                INVOICE: #{{ $order->code }}<br>
                DATE &nbsp;&nbsp;: {{ date('d/m/Y', strtotime($order->created_at)) }}<br>
                STATUS : {{ strtoupper($order->payment_status) }}
            </p>
        </div>

        <hr>
        @php
            $shipping_address = json_decode($order->shipping_address);
        @endphp
        <div class="info" style="text-align: left !important;">
            <span class="label-bold">CUSTOMER INFO:</span><br>
            <p class="label" style="margin-top: 5px;">
                NAME : {{ ucfirst($shipping_address->name ?: 'N/A') }}<br>
                PHONE: {{ $shipping_address->phone }}<br>
                @php
                    $address = $shipping_address->address;
                    if(strlen($address.', City: '.$shipping_address->city) <= 130){
                        $address .= ', City: '.$shipping_address->city;
                    }
                    if(strlen($address.', Area: '.$shipping_address->area) <= 130){
                        $address .= ', Area: '.$shipping_address->area;
                    }
                @endphp
                {{ Str::limit($address, 130, '') }}
            </p>
        </div>
        <hr>
        {{-- Dynamic products Will be display here with quantity and price --}}
        <div class="info" style="text-align: left !important;">
            <span class="label-bold">PRODUCTS:</span>
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt; margin-top:5px;">
                @foreach($order->orderDetails as $detail)
                    @if ($detail->quantity < 1)
                        @continue
                    @endif
                    <tr style="border: 1px dotted #000;">
                        <td class="label cell">{{ $detail->product->name }}</td>
                        <td class="label cell">{{ $detail->quantity }}</td>
                        <td class="label cell">{{ number_format($detail->price, 2) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        {{-- <hr> --}}
        <div class="info" style="text-align: left !important; margin-top: 5px;">
            <p class="label">
                SUBTOTAL: {{ number_format($order->orderDetails->sum('price'), 2) }}<br>
                @if(filled($order->coupon_discount) && $order->coupon_discount > 0)
                DISCOUNT: {{ number_format($order->coupon_discount, 2) }} (-)<br>
                @endif
                TOTAL &nbsp;&nbsp;: {{ number_format(get_order_grand_total($order), 2) }}<br>
                ** Price inclusives of standard VAT
            </p>
        </div>
        <hr>
        <p class="label">Handle with care | Keep dry</p>
        <p class="label">Thank you for shopping with us!</p>

        {{-- @if($order->packaged_by)
            <p class="label">Packaged By {{ ucwords($order->packagedBy->name) }}</p>
        @elseif(!$bulk)
            <p class="label">Packaged By {{ ucwords(auth()->user()->name) }}</p>
        @endif --}}
    </div>
</body>
</html>
