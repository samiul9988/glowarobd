@php
$languagefilePath = storage_path('app/public/languages/language.json');
if (file_exists($languagefilePath)) {
    $jsonData = file_get_contents($languagefilePath);
    $languages = collect(json_decode($jsonData, true));
} else {
    $languages = collect(\App\Models\Language::all()->toArray());
}

$currencyfilePath = storage_path('app/public/currencies/currency.json');
if (file_exists($currencyfilePath)) {
    $jsonData = file_get_contents($currencyfilePath);
    $currencies = collect(json_decode($jsonData, true));
} else {
    $currencies = collect(\App\Models\Currency::all()->toArray());
}
@endphp
<!DOCTYPE html>
@if($languages->where('code', Session::get('locale', Config::get('app.locale')))->first()['rtl'] == 1)
<html dir="rtl" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@else
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@endif
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ getBaseURL() }}">
    <meta name="file-base-url" content="{{ getFileBaseURL() }}">
    <meta name="author" content="{{ get_setting('meta_author', env('APP_NAME')) }}">
    <link rel="canonical" href="{{ env('APP_URL') }}" />

    <title>@yield('meta_title', get_setting('website_name').' | '.get_setting('site_motto'))</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="@yield('meta_title', get_setting('website_name').' | '.get_setting('site_motto'))">
    @if(app()->environment('staging'))
        <meta name="robots" content="noindex,nofollow">
    @else
        <meta name="robots" content="index,follow">
    @endif

    <meta name="description" content="@yield('meta_description', get_setting('meta_description') )" />
    <meta name="keywords" content="@yield('meta_keywords', get_setting('meta_keywords') )">

    <link rel="alternate" href="{{ env('APP_URL') }}" hreflang="en" />


    @yield('meta')

    @if(!isset($detailedProduct) && !isset($customer_product) && !isset($shop) && !isset($page) && !isset($blog))
        <!-- Schema.org markup for Google+ -->
        <meta itemprop="name" content="{{ get_setting('meta_title') }}">
        <meta itemprop="description" content="{{ get_setting('meta_description') }}">
        <meta itemprop="image" content="{{ uploaded_asset(get_setting('meta_image')) }}">

        <!-- Twitter Card data -->
        <meta name="twitter:card" content="product">
        <meta name="twitter:site" content="@publisher_handle">
        <meta name="twitter:title" content="{{ get_setting('twitter_title', get_setting('meta_title')) }}">
        <meta name="twitter:description" content="{{ get_setting('twitter_description', get_setting('meta_description')) }}">
        <meta name="twitter:creator" content="@author_handle">
        <meta name="twitter:image" content="{{ uploaded_asset(get_setting('twitter_image', get_setting('meta_image'))) }}">

        <!-- Open Graph data -->
        <meta property="og:title" content="{{ get_setting('og_title', get_setting('meta_title')) }}" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ route('home') }}" />
        <meta property="og:image" content="{{ uploaded_asset(get_setting('og_image', get_setting('meta_image'))) }}" />
        <meta property="og:description" content="{{ get_setting('og_description', get_setting('meta_description')) }}" />
        <meta property="og:site_name" content="{{ env('APP_NAME') }}" />
        <meta property="fb:app_id" content="{{ env('FACEBOOK_PIXEL_ID') }}">
    @endif

    <!-- Favicon -->
    <link rel="icon" href="{{ uploaded_asset(get_setting('site_icon')) }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i&display=swap" rel="stylesheet">

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ static_asset('assets/css/vendors.css') }}">
    @if($languages->where('code', Session::get('locale', Config::get('app.locale')))->first()['rtl'] == 1)
    <link rel="stylesheet" href="{{ static_asset('assets/css/bootstrap-rtl.min.css') }}">
    @endif
    <link rel="stylesheet" href="{{ static_asset('assets/css/aiz-core.css') }}">

    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>

    <link rel="stylesheet" href="{{ static_asset("assets/".str_replace('.','',config('app.theme'))."/frontend/css/custom-style.css") }}">


    @stack('styles22')
    <script src="{{ static_asset('assets/js/sweetalert2@11.js') }}"></script>
    <style>

        .swal-title {
            font-size: 17px !important;
            font-weight: 500 !important;;
        }
        .swal-confirm-btn {
            background-color: #0e2a31 !important;
            border: none !important;
            box-shadow: none !important;
            border-radius: 4px !important;
            padding: 8px 18px !important;
            font-weight: bold !important;
            font-size: 15px !important;
        }
    </style>
    <script>
        var AIZ = AIZ || {};
        AIZ.local = {
            nothing_selected: '{{ ('Nothing selected') }}',
            nothing_found: '{{ ('Nothing found') }}',
            choose_file: '{{ ('Choose file') }}',
            file_selected: '{{ ('File selected') }}',
            files_selected: '{{ ('Files selected') }}',
            add_more_files: '{{ ('Add more files') }}',
            adding_more_files: '{{ ('Adding more files') }}',
            drop_files_here_paste_or: '{{ ('Drop files here, paste or') }}',
            browse: '{{ ('Browse') }}',
            upload_complete: '{{ ('Upload complete') }}',
            upload_paused: '{{ ('Upload paused') }}',
            resume_upload: '{{ ('Resume upload') }}',
            pause_upload: '{{ ('Pause upload') }}',
            retry_upload: '{{ ('Retry upload') }}',
            cancel_upload: '{{ ('Cancel upload') }}',
            uploading: '{{ ('Uploading') }}',
            processing: '{{ ('Processing') }}',
            complete: '{{ ('Complete') }}',
            file: '{{ ('File') }}',
            files: '{{ ('Files') }}',
        }
    </script>

    <style>
        body{
            font-family: 'Open Sans', sans-serif;
            font-weight: 400;
        }
        :root{
            --primary: {{ get_setting('base_color', '#010147') }};
            --hov-primary: {{ get_setting('base_hov_color', '#00adee') }};
            --soft-primary: {{ hex2rgba(get_setting('base_color','#e62d04'),.15) }};
            --secondary: #00adee;
        }

        #map{
            width: 100%;
            height: 250px;
        }
        #edit_map{
            width: 100%;
            height: 250px;
        }

        .pac-container { z-index: 100000; }

        /* Style Christmas TopBar*/
        .snowfall-background{
            background-image: -webkit-linear-gradient(left , rgb(243, 146, 0) , rgb(230, 0, 126) 100%);
            background-image: linear-gradient(to right , rgb(243, 146, 0) , rgb(230, 0, 126) 100%);
            min-height: 50px;
        }
        .lightrope {
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            position: absolute;
            z-index: 1;
            margin: -15px 0 0 0;
            padding: 0;
            pointer-events: none;
            width: 100%;
        }

        .lightrope li {
            position: relative;
            animation-fill-mode: both;
            animation-iteration-count: infinite;
            list-style: none;
            margin: 0;
            padding: 0;
            display: block;
            width: 12px;
            height: 28px;
            border-radius: 50%;
            margin: 20px;
            display: inline-block;
            background: rgba(0, 247, 165, 1);
            box-shadow: 0px 4.6666666667px 24px 3px rgba(0, 247, 165, 1);
            animation-name: flash-1;
            animation-duration: 2s;
        }

        .lightrope li:nth-child(2n+1) {
            background: rgba(0, 255, 255, 1);
            box-shadow: 0px 4.6666666667px 24px 3px rgba(0, 255, 255, 0.5);
            animation-name: flash-2;
            animation-duration: 0.4s;
        }

        .lightrope li:nth-child(4n+2) {
            background: rgba(247, 0, 148, 1);
            box-shadow: 0px 4.6666666667px 24px 3px rgba(247, 0, 148, 1);
            animation-name: flash-3;
            animation-duration: 1.1s;
        }

        .lightrope li:nth-child(odd) {
            animation-duration: 1.8s;
        }

        .lightrope li:nth-child(3n+1) {
            animation-duration: 1.4s;
        }

        .lightrope li:before {
            content: "";
            position: absolute;
            background: #222;
            width: 10px;
            height: 9.3333333333px;
            border-radius: 3px;
            top: -4.6666666667px;
            left: 1px;
        }

        .lightrope li:after {
            content: "";
            top: -14px;
            left: 9px;
            position: absolute;
            width: 52px;
            height: 18.6666666667px;
            border-bottom: solid #222 2px;
            border-radius: 50%;
        }

        .lightrope li:last-child:after {
            content: none;
        }

        .lightrope li:first-child {
            margin-left: -40px;
        }

        @keyframes flash-1 {

            0%,
            100% {
                background: rgba(0, 247, 165, 1);
                box-shadow: 0px 4.6666666667px 24px 3px rgba(0, 247, 165, 1);
            }

            50% {
                background: rgba(0, 247, 165, 0.4);
                box-shadow: 0px 4.6666666667px 24px 3px rgba(0, 247, 165, 0.2);
            }
        }

        @keyframes flash-2 {

            0%,
            100% {
                background: rgba(0, 255, 255, 1);
                box-shadow: 0px 4.6666666667px 24px 3px rgba(0, 255, 255, 1);
            }

            50% {
                background: rgba(0, 255, 255, 0.4);
                box-shadow: 0px 4.6666666667px 24px 3px rgba(0, 255, 255, 0.2);
            }
        }

        @keyframes flash-3 {

            0%,
            100% {
                background: rgba(247, 0, 148, 1);
                box-shadow: 0px 4.6666666667px 24px 3px rgba(247, 0, 148, 1);
            }

            50% {
                background: rgba(247, 0, 148, 0.4);
                box-shadow: 0px 4.6666666667px 24px 3px rgba(247, 0, 148, 0.2);
            }
        }

        /*Snow + banner z-index*/
        .snowflake {
            --size: 5px;
            width: var(--size);
            height: var(--size);
            position: fixed;
            top: -5vh;
            background-size: contain;
            background-repeat: no-repeat;
            backface-visibility: hidden;
            background-image: url(https://staging.glowarobd.com/public/snoflake.png);
            z-index: 9999;
        }

        @keyframes snowfall {
            0% {
                transform: translate3d(var(--left-ini), 0, 0);
            }

            100% {
                transform: translate3d(var(--left-end), 110vh, 0);
            }
        }

        /* added small blur every 6 snowflakes*/
        .snowflake:nth-child(6n) {
            filter: blur(1px);
        }

        /*Snow*/
        .snowflake:nth-child(1) {
            --size: 20px;
            --left-ini: -8vw;
            --left-end: 2vw;
            left: 80vw;
            animation: snowfall 10s linear infinite;
            animation-delay: -4s;
        }

        .snowflake:nth-child(2) {
            --size: 24px;
            --left-ini: 2vw;
            --left-end: 4vw;
            left: 47vw;
            animation: snowfall 14s linear infinite;
            animation-delay: -9s;
        }

        .snowflake:nth-child(3) {
            --size: 20px;
            --left-ini: 8vw;
            --left-end: 5vw;
            left: 63vw;
            animation: snowfall 11s linear infinite;
            animation-delay: -10s;
        }

        .snowflake:nth-child(4) {
            --size: 20px;
            --left-ini: -6vw;
            --left-end: 10vw;
            left: 48vw;
            animation: snowfall 12s linear infinite;
            animation-delay: -9s;
        }

        .snowflake:nth-child(5) {
            --size: 10px;
            --left-ini: 3vw;
            --left-end: -8vw;
            left: 97vw;
            animation: snowfall 12s linear infinite;
            animation-delay: -1s;
        }

        .snowflake:nth-child(6) {
            --size: 20px;
            --left-ini: -9vw;
            --left-end: -6vw;
            left: 91vw;
            animation: snowfall 8s linear infinite;
            animation-delay: -6s;
        }

        .snowflake:nth-child(7) {
            --size: 24px;
            --left-ini: 6vw;
            --left-end: -6vw;
            left: 19vw;
            animation: snowfall 15s linear infinite;
            animation-delay: -7s;
        }

        .snowflake:nth-child(8) {
            --size: 10px;
            --left-ini: 10vw;
            --left-end: -1vw;
            left: 17vw;
            animation: snowfall 8s linear infinite;
            animation-delay: -8s;
        }

        .snowflake:nth-child(9) {
            --size: 20px;
            --left-ini: 7vw;
            --left-end: -4vw;
            left: 26vw;
            animation: snowfall 11s linear infinite;
            animation-delay: -6s;
        }

        .snowflake:nth-child(10) {
            --size: 20px;
            --left-ini: 2vw;
            --left-end: 9vw;
            left: 100vw;
            animation: snowfall 7s linear infinite;
            animation-delay: -2s;
        }

        .snowflake:nth-child(11) {
            --size: 20px;
            --left-ini: 2vw;
            --left-end: 5vw;
            left: 73vw;
            animation: snowfall 13s linear infinite;
            animation-delay: -4s;
        }

        .snowflake:nth-child(12) {
            --size: 20px;
            --left-ini: 9vw;
            --left-end: 10vw;
            left: 1vw;
            animation: snowfall 12s linear infinite;
            animation-delay: -9s;
        }

        .snowflake:nth-child(13) {
            --size: 10px;
            --left-ini: 8vw;
            --left-end: 4vw;
            left: 69vw;
            animation: snowfall 14s linear infinite;
            animation-delay: -9s;
        }

        .snowflake:nth-child(14) {
            --size: 20px;
            --left-ini: 10vw;
            --left-end: -7vw;
            left: 67vw;
            animation: snowfall 14s linear infinite;
            animation-delay: -1s;
        }

        .snowflake:nth-child(15) {
            --size: 10px;
            --left-ini: 9vw;
            --left-end: 1vw;
            left: 86vw;
            animation: snowfall 8s linear infinite;
            animation-delay: -9s;
        }

        .snowflake:nth-child(16) {
            --size: 10px;
            --left-ini: -5vw;
            --left-end: 4vw;
            left: 91vw;
            animation: snowfall 15s linear infinite;
            animation-delay: -10s;
        }

        .snowflake:nth-child(17) {
            --size: 5px;
            --left-ini: 3vw;
            --left-end: 5vw;
            left: 60vw;
            animation: snowfall 6s linear infinite;
            animation-delay: -10s;
        }

        .snowflake:nth-child(18) {
            --size: 10px;
            --left-ini: -7vw;
            --left-end: 6vw;
            left: 1vw;
            animation: snowfall 14s linear infinite;
            animation-delay: -6s;
        }

        .snowflake:nth-child(19) {
            --size: 10px;
            --left-ini: -3vw;
            --left-end: 10vw;
            left: 89vw;
            animation: snowfall 8s linear infinite;
            animation-delay: -10s;
        }

        .snowflake:nth-child(20) {
            --size: 20px;
            --left-ini: 1vw;
            --left-end: -9vw;
            left: 56vw;
            animation: snowfall 8s linear infinite;
            animation-delay: -4s;
        }

        .snowflake:nth-child(21) {
            --size: 10px;
            --left-ini: -9vw;
            --left-end: -7vw;
            left: 73vw;
            animation: snowfall 14s linear infinite;
            animation-delay: -10s;
        }

        .snowflake:nth-child(22) {
            --size: 10px;
            --left-ini: 1vw;
            --left-end: -3vw;
            left: 50vw;
            animation: snowfall 11s linear infinite;
            animation-delay: -1s;
        }

        .snowflake:nth-child(23) {
            --size: 10px;
            --left-ini: 9vw;
            --left-end: 4vw;
            left: 82vw;
            animation: snowfall 8s linear infinite;
            animation-delay: -6s;
        }

        .snowflake:nth-child(24) {
            --size: 10px;
            --left-ini: 2vw;
            --left-end: 4vw;
            left: 51vw;
            animation: snowfall 10s linear infinite;
            animation-delay: -6s;
        }

        .snowflake:nth-child(25) {
            --size: 5px;
            --left-ini: -3vw;
            --left-end: 8vw;
            left: 48vw;
            animation: snowfall 9s linear infinite;
            animation-delay: -6s;
        }

        .snowflake:nth-child(26) {
            --size: 10px;
            --left-ini: -3vw;
            --left-end: 7vw;
            left: 56vw;
            animation: snowfall 12s linear infinite;
            animation-delay: -1s;
        }

        .snowflake:nth-child(27) {
            --size: 10px;
            --left-ini: 2vw;
            --left-end: -9vw;
            left: 14vw;
            animation: snowfall 14s linear infinite;
            animation-delay: -4s;
        }

        .snowflake:nth-child(28) {
            --size: 5px;
            --left-ini: -6vw;
            --left-end: 2vw;
            left: 36vw;
            animation: snowfall 12s linear infinite;
            animation-delay: -2s;
        }

        .snowflake:nth-child(29) {
            --size: 10px;
            --left-ini: 4vw;
            --left-end: -3vw;
            left: 40vw;
            animation: snowfall 15s linear infinite;
            animation-delay: -3s;
        }

        .snowflake:nth-child(30) {
            --size: 20px;
            --left-ini: 7vw;
            --left-end: -8vw;
            left: 21vw;
            animation: snowfall 12s linear infinite;
            animation-delay: -9s;
        }

        .snowflake:nth-child(31) {
            --size: 5px;
            --left-ini: -8vw;
            --left-end: 4vw;
            left: 71vw;
            animation: snowfall 12s linear infinite;
            animation-delay: -9s;
        }

        .snowflake:nth-child(32) {
            --size: 20px;
            --left-ini: 1vw;
            --left-end: -8vw;
            left: 6vw;
            animation: snowfall 14s linear infinite;
            animation-delay: -4s;
        }

        .snowflake:nth-child(33) {
            --size: 6px;
            --left-ini: -5vw;
            --left-end: -1vw;
            left: 11vw;
            animation: snowfall 8s linear infinite;
            animation-delay: -4s;
        }

        .snowflake:nth-child(34) {
            --size: 10px;
            --left-ini: -8vw;
            --left-end: -7vw;
            left: 70vw;
            animation: snowfall 9s linear infinite;
            animation-delay: -4s;
        }

        .snowflake:nth-child(35) {
            --size: 10px;
            --left-ini: -7vw;
            --left-end: 3vw;
            left: 29vw;
            animation: snowfall 7s linear infinite;
            animation-delay: -4s;
        }

        .snowflake:nth-child(36) {
            --size: 2px;
            --left-ini: -9vw;
            --left-end: -1vw;
            left: 23vw;
            animation: snowfall 10s linear infinite;
            animation-delay: -6s;
        }

        .snowflake:nth-child(37) {
            --size: 4px;
            --left-ini: 9vw;
            --left-end: -7vw;
            left: 93vw;
            animation: snowfall 8s linear infinite;
            animation-delay: -9s;
        }

        .snowflake:nth-child(38) {
            --size: 2px;
            --left-ini: -9vw;
            --left-end: 7vw;
            left: 72vw;
            animation: snowfall 10s linear infinite;
            animation-delay: -9s;
        }

        .snowflake:nth-child(39) {
            --size: 2px;
            --left-ini: 4vw;
            --left-end: -3vw;
            left: 70vw;
            animation: snowfall 6s linear infinite;
            animation-delay: -5s;
        }

        .snowflake:nth-child(40) {
            --size: 2px;
            --left-ini: -6vw;
            --left-end: -9vw;
            left: 92vw;
            animation: snowfall 9s linear infinite;
            animation-delay: -5s;
        }

        .snowflake:nth-child(41) {
            --size: 4px;
            --left-ini: 8vw;
            --left-end: 3vw;
            left: 45vw;
            animation: snowfall 9s linear infinite;
            animation-delay: -4s;
        }

        .snowflake:nth-child(42) {
            --size: 6px;
            --left-ini: 8vw;
            --left-end: 1vw;
            left: 72vw;
            animation: snowfall 7s linear infinite;
            animation-delay: -4s;
        }

        .snowflake:nth-child(43) {
            --size: 2px;
            --left-ini: -9vw;
            --left-end: 2vw;
            left: 82vw;
            animation: snowfall 15s linear infinite;
            animation-delay: -3s;
        }

        .snowflake:nth-child(44) {
            --size: 2px;
            --left-ini: -1vw;
            --left-end: 6vw;
            left: 60vw;
            animation: snowfall 15s linear infinite;
            animation-delay: -5s;
        }

        .snowflake:nth-child(45) {
            --size: 4px;
            --left-ini: -5vw;
            --left-end: 4vw;
            left: 71vw;
            animation: snowfall 9s linear infinite;
            animation-delay: -8s;
        }

        .snowflake:nth-child(46) {
            --size: 4px;
            --left-ini: -7vw;
            --left-end: 9vw;
            left: 43vw;
            animation: snowfall 8s linear infinite;
            animation-delay: -8s;
        }

        .snowflake:nth-child(47) {
            --size: 4px;
            --left-ini: 6vw;
            --left-end: 10vw;
            left: 77vw;
            animation: snowfall 13s linear infinite;
            animation-delay: -5s;
        }

        .snowflake:nth-child(48) {
            --size: 10px;
            --left-ini: 10vw;
            --left-end: -9vw;
            left: 33vw;
            animation: snowfall 11s linear infinite;
            animation-delay: -4s;
        }

        .snowflake:nth-child(49) {
            --size: 5px;
            --left-ini: 10vw;
            --left-end: -8vw;
            left: 90vw;
            animation: snowfall 13s linear infinite;
            animation-delay: -2s;
        }

        .snowflake:nth-child(50) {
            --size: 20px;
            --left-ini: 2vw;
            --left-end: 5vw;
            left: 4vw;
            animation: snowfall 8s linear infinite;
            animation-delay: -6s;
        }

        #ajax-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(116, 115, 115, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loader {
            width: 50px;
            padding: 8px;
            aspect-ratio: 1;
            border-radius: 50%;
            background: #25b09b;
            --_m:
                conic-gradient(#0000 10%,#000),
                linear-gradient(#000 0 0) content-box;
            -webkit-mask: var(--_m);
                    mask: var(--_m);
            -webkit-mask-composite: source-out;
                    mask-composite: subtract;
            animation: l3 1s infinite linear;
        }
        @keyframes l3 {to{transform: rotate(1turn)}}
    </style>
    @if (get_setting('google_tagmanager') == 1)
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','{{ env("TAG_MANAGER_ID") }}');</script>
        <!-- End Google Tag Manager -->
    @endif
    <meta name="facebook-domain-verification" content="7p2e1ocyii0ex6q9ulpf3qm57txrgi" />

    @if (get_setting('google_analytics') == 1)
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('TRACKING_ID') }}"></script>

        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ env("TRACKING_ID") }}');
        </script>
    @endif
    <meta name="yandex-verification" content="4713fa70af8499f0" />

    @if (get_setting('facebook_pixel') == 1)
        <!-- Facebook Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ env("FACEBOOK_PIXEL_ID") }}');
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ env('FACEBOOK_PIXEL_ID') }}&ev=PageView&noscript=1"/>
        </noscript>
        <!-- End Facebook Pixel Code -->
    @endif

    @php
        echo get_setting('header_script');
    @endphp
    @if(get_setting('onesignal') == 1)
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    OneSignalDeferred.push(function(OneSignal) {
        OneSignal.init({
        appId: "{{env('ONE_SIGNAL_APP_ID')}}",
        });
    });
    </script>
    @endif

</head>
<body>

    @if (get_setting('google_tagmanager') == 1)
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ env('TAG_MANAGER_ID') }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    @endif

    <div id="ajax-loader" class="">
        <div class="loader"></div>
    </div>
    <!-- aiz-main-wrapper -->
    <div class="aiz-main-wrapper d-flex flex-column">

        @if(get_setting('enable_snow_effect') == 'on')
            @include(config('app.theme').'frontend.partials.snowFall')
        @endif

        <!-- Header -->
        @include(config('app.theme').'frontend.inc.nav')

        @yield('content')

        @include(config('app.theme').'frontend.inc.footer')

    </div>

    @if (get_setting('show_cookies_agreement') == 'on')
        <div class="aiz-cookie-alert shadow-xl">
            <div class="p-3 bg-dark rounded">
                <div class="text-white mb-3">
                    @php
                        echo get_setting('cookies_agreement_text');
                    @endphp
                </div>
                <button class="btn btn-primary aiz-cookie-accept">
                    {{ ('Ok. I Understood') }}
                </button>
            </div>
        </div>
    @endif

    @if (get_setting('show_website_popup') == 'on')
        <div class="modal website-popup removable-session d-none" data-key="website-popup" data-value="removed">
            <div class="absolute-full bg-black opacity-60"></div>
            <div class="modal-dialog modal-dialog-centered modal-dialog-zoom modal-md">
                <div class="modal-content position-relative border-0 rounded-0">
                    <div class="aiz-editor-data">
                        {!! get_setting('website_popup_content') !!}
                    </div>
                    @if (get_setting('show_subscribe_form') == 'on')
                        <div class="pb-5 pt-4 px-5">
                            <form class="" method="POST" action="{{ route('subscribers.store') }}">
                                @csrf
                                <div class="form-group mb-0">
                                    <input type="email" class="form-control" placeholder="{{ ('Your Email Address') }}" name="email" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block mt-3">
                                    {{ ('Subscribe Now') }}
                                </button>
                            </form>
                        </div>
                    @endif
                    <button class="absolute-top-right bg-white shadow-lg btn btn-circle btn-icon mr-n3 mt-n3 set-session" data-key="website-popup" data-value="removed" data-toggle="remove-parent" data-parent=".website-popup">
                        <i class="la la-close fs-20"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @include(config('app.theme').'frontend.partials.modal')

    <div class="modal fade" id="addToCart">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="c-preloader text-center p-3">
                    <i class="las la-spinner la-spin la-3x"></i>
                </div>
                <button type="button" class="close absolute-top-right btn-icon close z-1" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="la-2x">&times;</span>
                </button>
                <div id="addToCart-modal-body">

                </div>
            </div>
        </div>
    </div>

    @yield('modal')

    <!-- SCRIPTS -->
    <script src="{{ static_asset('assets/js/vendors.js') }}"></script>
    <script src="{{ static_asset('assets/js/aiz-core.js') }}"></script>

    <script async src="{{ static_asset("assets/".str_replace('.','',config('app.theme'))."/frontend/js/ajaxinate.js") }}"></script>

    @if (get_setting('facebook_chat') == 1)
        <script type="text/javascript">
            window.fbAsyncInit = function() {
                FB.init({
                  xfbml            : true,
                  version          : 'v3.3'
                });
              };

              (function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id;
              js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
              fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        </script>
        <div id="fb-root"></div>
        <!-- Your customer chat code -->
        <div class="fb-customerchat"
          attribution=setup_tool
          page_id="{{ env('FACEBOOK_PAGE_ID') }}">
        </div>
    @endif

    <script>
        @foreach (session('flash_notification', collect())->toArray() as $message)
            AIZ.plugins.notify('{{ $message['level'] }}', '{{ $message['message'] }}');
        @endforeach
    </script>

    @include(config('app.theme').'frontend.schema.local_business')
    @include(config('app.theme').'frontend.schema.organization')
    @include(config('app.theme').'frontend.schema.website')

    <script>
        function showAlert(type, message, url = null) {
            const swalOptions = {
                title: message,
                icon: type,
                showConfirmButton: true,
                confirmButtonColor: '#0e2a31',
                confirmButtonText: '{{ ('Ok') }}',
                timer: 3000,
                timerProgressBar: false,
                customClass: {
                    title: 'swal-title',
                    confirmButton: 'swal-confirm-btn',
                }
            };

            if (url) {
                Swal.fire(swalOptions).then((result) => {
                    if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                        window.location.href = url;
                    }
                });
            } else {
                Swal.fire(swalOptions);
            }
        }
        function showLoader(){
            return;
            $('#ajax-loader').css('display', 'flex');
        }
        function hideLoader(){
            return;
            $('#ajax-loader').css('display', 'none');
        }
        $(document).on('click', '.copy-product-info', function(e) {
            e.preventDefault();
            var data = $(this).data('info');
            var $temp = $("<textarea>");
            $("body").append($temp);
            $temp.val(data).select();
            try {
                document.execCommand("copy");
                AIZ.plugins.notify('success', '{{ ('Copied to clipboard') }}');
            } catch (err) {
                AIZ.plugins.notify('danger', '{{ ('Oops, unable to copy') }}');
            }
            $temp.remove();
        });
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            /* $('.category-nav-element').each(function(i, el) {
                $(el).on('mouseover', function(){
                    if(!$(el).find('.sub-cat-menu').hasClass('loaded')){
                        $.post('{{ route('category.elements') }}', {_token: AIZ.data.csrf, id:$(el).data('id')}, function(data){
                            $(el).find('.sub-cat-menu').addClass('loaded').html(data);
                        });
                    }
                });
            }); */
            if ($('#lang-change').length > 0) {
                $('#lang-change .dropdown-menu a').each(function() {
                    $(this).on('click', function(e){
                        e.preventDefault();
                        var $this = $(this);
                        var locale = $this.data('flag');
                        $.post('{{ route('language.change') }}',{_token: AIZ.data.csrf, locale:locale}, function(data){
                            location.reload();
                        });

                    });
                });
            }

            if ($('#currency-change').length > 0) {
                $('#currency-change .dropdown-menu a').each(function() {
                    $(this).on('click', function(e){
                        e.preventDefault();
                        var $this = $(this);
                        var currency_code = $this.data('currency');
                        $.post('{{ route('currency.change') }}',{_token: AIZ.data.csrf, currency_code:currency_code}, function(data){
                            location.reload();
                        });

                    });
                });
            }
        });
        let searchTimer;
        $('#search').on('keyup', function(){
            clearTimeout(searchTimer);

            // Set a new timer to trigger the search after a delay
            searchTimer = setTimeout(function() {
                search();
            }, 1000);
            // search();
        });

        $('#search').on('focus', function(){
            search();
        });

        function search(){
            var searchKey = $('#search').val();
            if(searchKey.length > 2){
                $('body').addClass("typed-search-box-shown");

                $('.typed-search-box').removeClass('d-none');
                $('.search-preloader').removeClass('d-none');
                $.post('{{ route('search.ajax') }}', { _token: AIZ.data.csrf, search:searchKey}, function(data){
                    if(data == '0'){
                        // $('.typed-search-box').addClass('d-none');
                        $('#search-content').html(null);
                        $('.typed-search-box .search-nothing').removeClass('d-none').html('Sorry, nothing found for <strong>"'+searchKey+'"</strong>');
                        $('.search-preloader').addClass('d-none');

                    }
                    else{
                        $('.typed-search-box .search-nothing').addClass('d-none').html(null);
                        $('#search-content').html(data);
                        $('.search-preloader').addClass('d-none');
                    }
                });
            } else {
                $('.typed-search-box').addClass('d-none');
                $('body').removeClass("typed-search-box-shown");
            }
        }

        function updateNavCart(view,count){
            $('.cart-count').html(count);
            $('#cart_items').html(view);
        }

        function removeFromCart(key){
            $.post('{{ route('cart.removeFromCart') }}', {
                _token  : AIZ.data.csrf,
                id      :  key
            }, function(data){
                var spa_checkout = '{{ get_setting("spa_checkout") }}';
                if(spa_checkout == 1){
                    window.location.reload();
                }
                updateNavCart(data.nav_cart_view,data.cart_count);
                $('#cart-summary').html(data.cart_view);
                AIZ.plugins.notify('success', "{{ ('Item has been removed from cart') }}");
                $('#cart_items_sidenav').html(parseInt($('#cart_items_sidenav').html())-1);
            });
        }

        function addToCompare(id){
            $.post('{{ route('compare.addToCompare') }}', {_token: AIZ.data.csrf, id:id}, function(data){
                $('#compare').html(data);
                AIZ.plugins.notify('success', "{{ ('Item has been added to compare list') }}");
                $('#compare_items_sidenav').html(parseInt($('#compare_items_sidenav').html())+1);
            });
        }

        function addToWishList(id){
            $.post('{{ route('wishlists.store') }}', {_token: AIZ.data.csrf, id:id}, function(data){
                if(data != 0){
                    $('#wishlist').html(data);
                    AIZ.plugins.notify('success', "{{ ('Item has been added to wishlist') }}");
                }
                else{
                    AIZ.plugins.notify('warning', "{{ ('Please login first') }}");
                }
            });
        }

        function showAddToCartModal(id){
            if(!$('#modal-size').hasClass('modal-lg')){
                $('#modal-size').addClass('modal-lg');
            }
            $('#addToCart-modal-body').html(null);
            $('#addToCart').modal();
            $('.c-preloader').show();
            $.post('{{ route('cart.showCartModal') }}', {_token: AIZ.data.csrf, id:id}, function(data){
                $('.c-preloader').hide();
                $('#addToCart-modal-body').html(data);
                AIZ.plugins.slickCarousel();
                AIZ.plugins.zoom();
                AIZ.extra.plusMinus();
                getVariantPrice();
            });
        }

        $('#option-choice-form input').on('change', function(){
            getVariantPrice();
        });

        function getVariantPrice(){
            if($('#option-choice-form input[name=quantity]').val() > 0 && checkAddToCartValidity()){
                $.ajax({
                    type:"POST",
                    url: '{{ route('products.variant_price') }}',
                    data: $('#option-choice-form').serializeArray(),
                    success: function(data){

                        $('.product-gallery-thumb .carousel-box').each(function (i) {
                            if($(this).data('variation') && data.variation == $(this).data('variation')){
                                $('.product-gallery-thumb').slick('slickGoTo', i);
                            }
                        });

                        $('#option-choice-form #chosen_price_div').removeClass('d-none');
                        $('#option-choice-form #chosen_price_div #chosen_price').html(data.min_price);
                        $('#available-quantity').html(data.quantity);
                        $('.input-number').prop('max', data.max_limit);
                        if(parseInt(data.in_stock) == 0 && data.digital  == 0){
                            $('.buy-now').addClass('d-none');
                            $('.add-to-cart').addClass('d-none');
                            if(data.is_preorder == true){
                                    if(parseInt(data.preorder_max) > 0){
                                        $('.input-number').prop('max', data.preorder_max);
                                        $('#available-quantity').html(data.preorder_max);
                                    }
                                    $('.pre-order').removeClass('d-none');
                                    $('.pre-order-text').removeClass('d-none');
                            }else{
                                    $('.out-of-stock').removeClass('d-none');
                            }
                        }else{
                            $('.buy-now').removeClass('d-none');
                            $('.add-to-cart').removeClass('d-none');
                        }

                        $('#min-price').html(data.min_unit_price);
                    }
                });
            }
        }

        function checkAddToCartValidity(){
            var names = {};
            $('#option-choice-form input:radio').each(function() { // find unique names
                  names[$(this).attr('name')] = true;
            });
            var count = 0;
            $.each(names, function() { // then count them
                  count++;
            });

            if($('#option-choice-form input:radio:checked').length == count){
                return true;
            }

            return false;
        }

        function addToCart(){
            if(checkAddToCartValidity()) {
                $('#addToCart').modal();
                $('.c-preloader').show();
                $.ajax({
                    type:"POST",
                    url: '{{ route('cart.addToCart') }}',
                    data: $('#option-choice-form').serializeArray(),
                    success: function(data){
                       $('#addToCart-modal-body').html(null);
                       $('.c-preloader').hide();
                       $('#modal-size').removeClass('modal-lg');
                       $('#addToCart-modal-body').html(data.modal_view);
                       AIZ.extra.plusMinus();
                       updateNavCart(data.nav_cart_view,data.cart_count);
                       @if (get_setting('google_tagmanager'))
                            dataLayer.push({ ecommerce: null });
                            dataLayer.push({
                                event    : "add_to_cart",
                                ecommerce: {
                                    items: data.carts
                                }
                            });
                        @endif
                    }
                });
            }
            else{
                AIZ.plugins.notify('warning', "{{ ('Please choose all the options') }}");
            }
        }

        function buyNow(){
            if(checkAddToCartValidity()) {
                // $('#addToCart-modal-body').html(null);
                $('#addToCart').modal();
                $('.c-preloader').show();
                $.ajax({
                   type:"POST",
                   url: '{{ route('cart.addToCart') }}',
                   data: $('#option-choice-form').serializeArray(),
                   success: function(data){
                       if(data.status == 1){

                            $('#addToCart-modal-body').html(data.modal_view);
                            updateNavCart(data.nav_cart_view,data.cart_count);

                            window.location.replace("{{ route('checkout.shipping_info') }}");
                       }
                       else{
                            $('#addToCart-modal-body').html(null);
                            $('.c-preloader').hide();
                            $('#modal-size').removeClass('modal-lg');
                            $('#addToCart-modal-body').html(data.modal_view);
                       }
                   }
               });
            }
            else{
                AIZ.plugins.notify('warning', "{{ ('Please choose all the options') }}");
            }
        }

        function show_purchase_history_details(order_id)
        {
            $('#order-details-modal-body').html(null);

            if(!$('#modal-size').hasClass('modal-lg')){
                $('#modal-size').addClass('modal-lg');
            }

            $.post('{{ route('purchase_history.details') }}', { _token : AIZ.data.csrf, order_id : order_id}, function(data){
                $('#order-details-modal-body').html(data);
                $('#order_details').modal();
                $('.c-preloader').hide();
            });
        }

        async function takeCancellationReason() {
            const reasons = @json(\App\Enums\Reasons::cancelReason());
            let htmlOptions = Object.entries(reasons)
                .map(([key, label]) => `<option value="${key}">${label}</option>`)
                .join('');
            const { value: reason } = await Swal.fire({
                title: '<span style="font-size: 22px; display:block; text-align:left;">Why are you cancelling this order?</span>',
                html: `
                <select id="swal-reason" class="form-control">
                    <option value="" selected disabled>Select a reason</option>
                    ${htmlOptions}
                </select>
                <textarea rows="4" id="swal-reason-other" class="form-control" placeholder="Write your reason here" style="display:none; margin-top:12px; border-radius: 8px !important;"></textarea>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Submit',
                allowOutsideClick: false,
                preConfirm: () => {
                    const sel = document.getElementById('swal-reason').value;
                    const other = document.getElementById('swal-reason-other').value.trim();

                    if (!sel) {
                        Swal.showValidationMessage('Please select a reason');
                        return false;
                    }

                    if (sel === 'other') {
                        if (!other) {
                            Swal.showValidationMessage('Please type your reason');
                            return false;
                        }
                        return other;
                    }

                    return sel;
                },
                didOpen: () => {
                    const sel = document.getElementById('swal-reason');
                    const other = document.getElementById('swal-reason-other');

                    sel.addEventListener('change', function () {
                        if (this.value === 'other') {
                            other.style.display = 'block';
                            other.focus();
                        } else {
                            other.style.display = 'none';
                        }
                    });
                }
            });

            return reason;
        }

        async function cancel_order(order_id)
        {
            $('#order_details').modal('hide');
            let reason = await takeCancellationReason();
            if (reason) {
                $.ajax({
                    type: "POST",
                    url: '{{ route('purchase_history.cancel') }}',
                    data: { _token : AIZ.data.csrf, order_id : order_id, reason: reason },
                    success: function(data) {
                        showAlert('success', 'Your order has been cancelled.', location.href);
                    },
                    error: function(xhr, status, error) {
                        showAlert('error', 'Request failed. Please try again.');
                    }
                });
            }
        }

        function refund_request(order_id)
        {
            if(confirm('Are you sure you want to refund this order?')){
                var reason = $('#refund_reason').val();
                if(reason){
                    $.post('{{ route('order_request.refund') }}', { _token : AIZ.data.csrf, order_id : order_id, reason: reason}, function(data){
                        showAlert('success', 'Your cancellation request has been submitted.', location.href);
                    });
                }else{
                    AIZ.plugins.notify('warning', "{{ ('Please choose a reason to make refund request') }}");
                }
            }
        }

        /*Custom JS*/
        //FOR SLIDE MENU
        $(document).ready(function(){
            $('.menu-tab').click(function(){
                $('.menu-hide').toggleClass('show');
            });
            $('.menu-close').click(function(){
                $('.menu-hide').toggleClass('show');
            });
            $('.menu-tab').click(function(){
                $('body').toggleClass('mobile-menu-open');
            });
            $('.menu-close').click(function(){
                $('body').toggleClass('mobile-menu-open');
            });
        });
        // $(document).ready(function(){
        //     $('#accordion > li').click(function(){
        //         $('#accordion > li > .megamenu_wrapper').toggleClass('active');
        //     });
        // });

        $(document).ready(function() {

            // $('#accordion li').children('ul').hide();

            $('.sub_menu_icon').click(function() {

                $(this).parent().siblings('.active').removeClass('active').find('.megamenu_wrapper').slideUp('fast');

                if ($(this).parent().hasClass('active')) {
                    $(this).next('.megamenu_wrapper').slideUp('fast');
                    $(this).parent().removeClass('active');
                } else {
                    $(this).next('.megamenu_wrapper').slideDown('fast');
                    $(this).parent().addClass('active');
                }

            });

        });

        $(function () {
            let numberOfReviewImage = 1;
            let maxNumberOfReviewImage = "{{get_setting('reviews_max_image')}}";
            $(document).on('click', '.btn-add', function (e) {
                e.preventDefault();

                if(numberOfReviewImage < maxNumberOfReviewImage){
                    var controlForm = $('.controls:first'),
                    currentEntry = $(this).parents('.entry:first'),
                    newEntry = $(currentEntry.clone()).appendTo(controlForm);

                    newEntry.find('input').val('');
                    controlForm.find('.entry:not(:last) .btn-add')
                    .removeClass('btn-add').addClass('btn-remove')
                    .removeClass('btn-success').addClass('btn-danger')
                    .html('<span class="fa fa-trash"></span>');

                    numberOfReviewImage = numberOfReviewImage + 1;
                }else{
                    AIZ.plugins.notify('warning', "{{ ('Max number of image exceeded') }}");
                }

            }).on('click', '.btn-remove', function (e) {
                $(this).parents('.entry:first').remove();
                numberOfReviewImage = numberOfReviewImage - 1;
                e.preventDefault();
                return false;
            });
        });
        /*Custom JS END*/


    </script>


    @yield('script')

    @stack('gtm_script')

    @php
        echo get_setting('footer_script');
    @endphp

</body>
</html>
