<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice Label</title>
    <style>
        @font-face {
            font-family: 'label';
            src: url("{{ static_asset('assets/fonts/label.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'bangla';
            src: url("{{ static_asset('assets/fonts/bangla.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        @page {
            size: 80mm auto;
            margin: 5mm;
        }
        body {
            font-family: 'label', Arial, Helvetica, sans-serif;
        }
        .bangla {
            font-family: 'bangla', 'label', Arial, Helvetica, sans-serif;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="m-0 font-label text-[8pt] leading-tight">

@foreach ($orders as $order)
    <div class="w-full border border-dotted border-[#000000] rounded p-2 text-center">

        <!-- Header -->
        <h3 class="text-[10pt] font-bold my-2" style="font-family: label">{{ get_setting('site_name') }}</h3>
        <p class="text-[7pt]">{{ get_setting('site_motto') }}</p>
        <p class="text-[7pt]">
            {{ get_setting('contact_address', null, App::getLocale()) }}<br>
            Call: {{ get_setting('contact_phone') }}
        </p>

        <hr class="border-t border-gray-300 my-2">

        <!-- Barcode -->
        <div class="my-1">
            @php
                $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                $barcode = $generator->getBarcode($order->code, $generator::TYPE_CODE_128, 1);
            @endphp
            <img src="{{ 'data:image/png;base64,' . base64_encode($barcode) }}" 
                 alt="#{{ $order->code }}" class="mx-auto h-10">
        </div>

        <!-- Invoice Info -->
        <div class="text-[7pt]">
            <p>INVOICE #: {{ $order->code }}</p>
            <p>DATE: {{ date('d/m/Y', strtotime($order->created_at)) }}</p>
            <p>INVOICE STATUS: {{ strtoupper($order->payment_status) }}</p>
        </div>

        <!-- Customer Info -->
        @php
            $shipping_address = json_decode($order->shipping_address);
            $address = $shipping_address->address;
            if(strlen($address.', City: '.$shipping_address->city) <= 130){
                $address .= ', City: '.$shipping_address->city;
            }
            if(strlen($address.', Area: '.$shipping_address->area) <= 130){
                $address .= ', Area: '.$shipping_address->area;
            }
        @endphp
        <div class="text-[7pt] text-center mt-4">
            <p class="font-bold">CUSTOMER INFO:</p>
            <p class="bangla">
                NAME: {{ ucfirst($shipping_address->name ?: 'N/A') }}<br>
                PHONE: {{ $shipping_address->phone }}<br>
                {{ Str::limit($address, 130, '') }}<br>
                City: {{ $shipping_address->city ?? 'N/A' }}
            </p>
        </div>

        <hr class="border-t border-gray-300 my-2">

        <!-- Footer -->
        <p class="text-[7pt]" style="font-family: label !important;">Handle with care | Keep dry</p>

        @if($order->packaged_by)
            <p class="text-[7pt]">Packaged By {{ ucwords($order->packagedBy->name) }}</p>
        @elseif(!$bulk)
            <p class="text-[7pt]">Packaged By {{ ucwords(auth()->user()->name) }}</p>
        @endif
    </div>
    @if (!$loop->last)
        @pageBreak
    @endif
@endforeach

</body>
</html>
