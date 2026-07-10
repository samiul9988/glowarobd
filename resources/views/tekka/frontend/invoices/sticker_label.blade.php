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
        @page {
            size: 80mm 140mm;
            margin: 5mm;
        }

        body {
            font-size: 8pt;
            margin: 0;
        }

        .label {
            font-family: 'label';
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
            font-size: 7pt;
        }
        p{
            line-height: 1</span>;
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
            font-size: 10pt;
        }
        .label-bold{
            font-weight: bold;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    @foreach ($orders as $key => $order)
        <div class="container center">
            <h3 class="title label-bold">{{ get_setting('site_name') }}</h3>
            <div class="subtitle">
                <p class="label">{{ get_setting('site_motto') }}</p>
            </div>
            <p class="label">{{ get_setting('contact_address',null,App::getLocale()) }}<br>
            Call : {{ get_setting('contact_phone') }}</p>
            <hr>
            <div class="barcode">
                @php
                    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                    $barcode = $generator->getBarcode($order->code, $generator::TYPE_CODE_128, 1);
                @endphp
                <img src="{{ 'data:image/png;base64,' . base64_encode($barcode) }}" alt="#{{ $order->code }}" height="40">
            </div>

            <div class="info">
                <p class="label">INVOICE #: {{ $order->code }}<br>
                DATE: {{ date('d/m/Y', strtotime($order->created_at)) }}<br>
                INVOICE STATUS: {{ strtoupper($order->payment_status) }}</p><br>
            </div>

            @php
                $shipping_address = json_decode($order->shipping_address);
            @endphp
            <div class="info">
                <span class="label-bold">CUSTOMER INFO:</span><br>
                <p class="label">NAME: {{ ucfirst($shipping_address->name ?: 'N/A') }}<br>
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
            <p class="label">Handle with care | Keep dry</p>

            @if($order->packaged_by)
                <p class="label">Packaged By {{ ucwords($order->packagedBy->name) }}</p>
            @elseif(!$bulk)
                <p class="label">Packaged By {{ ucwords(auth()->user()->name) }}</p>
            @endif
        </div>
    @endforeach
    
</body>
</html>
