<!doctype html>
@if(\App\Models\Language::where('code', Session::get('locale', Config::get('app.locale')))->first()?->rtl == 1)
<html dir="rtl" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@else
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@endif
<head>
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="app-url" content="{{ getBaseURL() }}">
	<meta name="file-base-url" content="{{ getFileBaseURL() }}">

    <meta name="robots" content="noindex,nofollow">

	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Favicon -->
	<link rel="icon" href="{{ uploaded_asset(get_setting('site_icon')) }}">
	<title>{{ get_setting('website_name').' | '.get_setting('site_motto') }}</title>

	<!-- google font -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">

	<!-- aiz core css -->
	<link rel="stylesheet" href="{{ static_asset('assets/css/vendors.css') }}">
    @if(\App\Models\Language::where('code', Session::get('locale', Config::get('app.locale')))->first()?->rtl == 1)
    <link rel="stylesheet" href="{{ static_asset('assets/css/bootstrap-rtl.min.css') }}">
    @endif
	<link rel="stylesheet" href="{{ static_asset('assets/css/aiz-core.css') }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <style>
        body {
            font-size: 12px;
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

</head>
<body class="">

	<div class="aiz-main-wrapper d-flex">
        <div class="flex-grow-1">
            @yield('content')
        </div>
    </div><!-- .aiz-main-wrapper -->

    @yield('modal')


    <script src="{{ static_asset('assets/js/vendors.js') }}" ></script>
    <script src="{{ static_asset('assets/js/aiz-core.js') }}" ></script>

    @yield('script')

    <script type="text/javascript">
        @foreach (session('flash_notification', collect())->toArray() as $message)
            AIZ.plugins.notify('{{ $message['level'] }}', '{{ $message['message'] }}');
        @endforeach
    </script>

</body>
</html>
