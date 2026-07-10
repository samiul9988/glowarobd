@php
    $languagefilePath = storage_path('app/public/languages/language.json');
    if (file_exists($languagefilePath)) {
        $jsonData = file_get_contents($languagefilePath);
        $languages = collect(json_decode($jsonData, true));
    } else {
        $languages = collect(\App\Models\Language::all()->toArray());
    }
@endphp
<!doctype html>
{{-- Check if the languages is empty or not --}}

@if (!$languages->isEmpty() && $languages->where('code', Session::get('locale', Config::get('app.locale')))->first()['rtl'] == 1)
    <html dir="rtl" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@else
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@endif

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ config('app.url') }}">
    <meta name="file-base-url" content="{{ getFileBaseURL() }}">
    <meta name="robots" content="noindex,nofollow">
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Favicon -->
    <link rel="icon" href="{{ uploaded_asset(get_setting('site_icon')) }}">
    <title>@yield('meta_title', get_setting('website_name') . ' | ' . get_setting('site_motto'))</title>

    <!-- google font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">

    <!-- aiz core css -->
    <link rel="stylesheet" href="{{ static_asset('assets/css/vendors.css') }}">
    @if (!$languages->isEmpty() && $languages->where('code', Session::get('locale', Config::get('app.locale')))->first()['rtl'] == 1)
        <link rel="stylesheet" href="{{ static_asset('assets/css/bootstrap-rtl.min.css') }}">
    @endif
	<link rel="stylesheet" href="{{ static_asset('assets/css/aiz-core.css') }}">
	<link rel="stylesheet" href="{{ static_asset('assets/css/nestable/jquery.nestable.css') }}">
	<link rel="stylesheet" href="{{ static_asset('assets/css/nestable/style.css') }}">
	<link rel="stylesheet" href="{{ static_asset('assets/css/select2.min.css') }}">

    @if(config('app.theme') === 'tekka.')
        <!-- Tekka Admin CSS -->
        <link rel="stylesheet" href="{{ static_asset('assets/css/tekka-admin.css') }}">
    @elseif(config('app.theme') == 'theme22.')
        <!-- Theme22 CSS -->
        <link rel="stylesheet" href="{{ static_asset('assets/css/theme22-admin.css') }}">
    @endif

    <style>
        body {
            font-size: 12px;
        }
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

        .ticker-container {
            width: 100%;
            overflow: hidden;
            background-color: #FFE9BD;
            /* background-color: #f8ecb0b0; */
            /* padding: 10px 0; */
            font-family: Arial, sans-serif;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            display: flex;
            /* align-items: center; */
            border-radius: 5px;
        }

        .news-label {
            display: flex;
            align-items: center;
            font-weight: bold;
            padding: 10px 15px;
            /* color: #333; */
            /* background-color: #e0e0e0; */
            color: #fff;
            background-color: #CC9F47;
            border-right: 1px solid #ccc;
            white-space: nowrap;
            flex-shrink: 0; /* Prevents label from shrinking */
        }

        .ticker-wrapper {
            flex-grow: 1;
            overflow: hidden;
            padding: 10px 0;
        }

        .ticker {
            display: inline-block;
            white-space: nowrap;
            padding-left: 100%; /* Start off-screen */
            animation: ticker 15s linear infinite;
        }

        .ticker:hover {
            animation-play-state: paused;
        }

        .ticker-item {
            display: inline-block;
            margin-right: 40px;
            font-size: 14px;
            color: #333;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
        }

        .ticker-item:hover {
            text-decoration: none;
            /* color: #0066cc; */
        }

        @keyframes ticker {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-100%);
            }
        }

        @keyframes heartbeat {
            0% {
                transform: scale(1);
            }
            14% {
                transform: scale(1.3);
            }
            28% {
                transform: scale(1);
            }
            42% {
                transform: scale(1.3);
            }
            70% {
                transform: scale(1);
            }
        }


        /* Skeleton */
        .skeleton {
            background-color: #e9ecef;
            border-radius: 4px;
            margin-bottom: 10px;
            overflow: hidden;
            position: relative;
        }
        .skeleton::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .skeleton-title {
            height: 28px;
            width: 200px;
            margin-bottom: 15px;
        }
        .skeleton-item {
            height: 20px;
            width: 100%;
            margin-bottom: 8px;
        }
        .skeleton-divider {
            height: 2px;
            width: 100%;
            margin: 20px 0;
            background-color: #dee2e6;
        }
        .skeleton-section {
            margin-bottom: 30px;
        }

        .cursor-not-allowed {
            cursor: not-allowed !important;
        }

        /* Loader */
        .loader {
            width: 50px;
            aspect-ratio: 1;
            display:grid;
            -webkit-mask: conic-gradient(from 15deg,#0000,#000);
            animation: l26 1s infinite steps(12);
        }
        .loader, .loader:before, .loader:after {
            background: radial-gradient(closest-side at 50% 12.5%, #0f0f0f 96%,#0000) 50% 0/20% 80% repeat-y, radial-gradient(closest-side at 12.5% 50%, #0f0f0f 96%,#0000) 0 50%/80% 20% repeat-x;
        }
        .loader:before, .loader:after {
            content: "";
            grid-area: 1/1;
            transform: rotate(30deg);
        }
        .loader:after {
            transform: rotate(60deg);
        }
        @keyframes l26 {
            100% {transform:rotate(1turn)}
        }
    </style>
	<script src="{{ static_asset('assets/js/select2.min.js') }}" defer></script>
	<script src="https://unpkg.com/html5-qrcode@2.0.9/dist/html5-qrcode.min.js"></script>
	<script defer src="https://unpkg.com/alpinejs@3.2.4/dist/cdn.min.js"></script>
    <script src="{{ static_asset('assets/js/sweetalert2@11.js') }}"></script>
	@stack('cus_css')
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
@php
    $permissions = Auth::check() ? json_decode(Auth::user()->staff?->role?->permissions ?? '[]', true) ?? [] : [];
    if(any_in_array(['customer_care_dashboard', 'packaging_dashboard', 'account_inventory_dashboard'], $permissions)) {
        $hideSideMenu = true;
    } elseif (areActiveRoutes(['all_orders.index', 'all_orders.status'])) {
        $hideSideMenu = true;
    } else {
        $hideSideMenu = false;
    }
@endphp
<body class="@if($hideSideMenu) side-menu-closed @endif">

	<div class="aiz-main-wrapper">
		<div class="loading-overlay" id="loader">
            <div class="loader"></div>
			{{-- <i class="las la-spinner"></i> --}}
		</div>

        @include('backend.inc.admin_sidenav')
		<div class="aiz-content-wrapper">
            @include('backend.inc.admin_nav', ['languages' => $languages])
			<div class="aiz-main-content">
				<div class="px-15px px-lg-25px">
                    @if (get_setting('enable_product_expire_date') == 1 && get_setting('enable_expire_products_alert') == 'on' && get_setting('expire_products_count', 0) > 0 && !areActiveRoutes(['admin.expireProductsReport']))
                        <div class="alert alert-danger border-left border-danger shadow-sm mb-4" id="expire-products-alert">
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- Left section: Alert message -->
                                <div class="d-flex align-items-center">
                                    <i class="las la-exclamation-triangle text-danger mr-2" style="font-size: 1.5rem;"></i>
                                    <div>
                                        Alert:
                                        <span id="expire-products-count" class="font-weight-bold">{{ get_setting('expire_products_count', 0) }}</span>
                                        {{ (\Str::plural('product', get_setting('expire_products_count', 0)) . ' expiring in') }}
                                        <span id="expire-products-days">{{ get_setting('expire_products_alert_duration', 7) }}</span>
                                        {{ ('days') }}.
                                    </div>
                                </div>

                                <!-- Right section: Actions -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('admin.expireProductsReport') }}" class="btn btn-sm rounded btn-outline-danger mr-2" id="view-expire-products">
                                        {{ ('Explore') }}
                                    </a>
                                    <button type="button" class="close ml-2" id="dismiss-expire-alert" aria-label="Close" onclick="document.getElementById('expire-products-alert').remove();">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @yield('content')
				</div>
				<div class="bg-white text-center py-3 px-15px px-lg-25px mt-auto">
					<p class="mb-0">&copy; {{ get_setting('site_name') }} v{{ get_setting('current_version') }}</p>
				</div>
			</div><!-- .aiz-main-content -->
		</div><!-- .aiz-content-wrapper -->
	</div><!-- .aiz-main-wrapper -->

    @yield('modal')


	<script src="{{ static_asset('assets/js/vendors.js') }}" ></script>
	<script src="{{ static_asset('assets/js/aiz-core.js') }}" ></script>
	<script src="{{ static_asset('assets/js/ajaxinate.min.js') }}" ></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.6.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>

    @yield('script')

    <script type="text/javascript">
        function showLoader() {
            $('#loader').removeClass('d-none').addClass('d-flex');
        }

        function hideLoader() {
            $('#loader').removeClass('d-flex').addClass('d-none');
        }

        function normalizePhoneNumber(phone) {
            // Convert Bangla numerals to English
            const banglaToEnglish = {
                '০': '0', '১': '1', '২': '2', '৩': '3', '৪': '4',
                '৫': '5', '৬': '6', '৭': '7', '৮': '8', '৯': '9'
            };

            // Step 1: Convert Bangla numbers to English
            let normalized = phone.split('').map(char =>
                banglaToEnglish[char] || char
            ).join('');

            // Step 2: Remove all non-digit characters except '+' (for country code handling)
            normalized = normalized.replace(/[^\d+]/g, '');

            // Step 3: Remove country codes
            if (normalized.startsWith('+88')) {
                normalized = normalized.substring(3);
            } else if (normalized.startsWith('88')) {
                normalized = normalized.substring(2);
            } else if (normalized.startsWith('+')) {
                normalized = normalized.substring(1);
            }

            // Step 4: Ensure we only have digits left (in case any non-digits slipped through)
            normalized = normalized.replace(/\D/g, '');

            return normalized;
        }

        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
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

        async function takeReason(options = {}) {
            let reasons = {};
            if (!options || typeof options !== 'object') {
                options = {
                    title: 'Why are you cancelling this order?',
                    type: 'cancel', // 'cancel' or 'return'
                };
            }
            if (!options.title) {
                options.title = 'Why are you cancelling this order?';
            }
            if (!options.type || !['cancel', 'return'].includes(options.type)) {
                options.type = 'cancel';
            }
            if (options.type === 'cancel') {
                reasons = @json(\App\Enums\Reasons::cancelReason());
            } else {
                reasons = @json(\App\Enums\Reasons::returnReason());
            }
            let htmlOptions = Object.entries(reasons)
                .map(([key, label]) => `<option value="${key}">${label}</option>`)
                .join('');
            const { value: reason } = await Swal.fire({
                title: `<span style="font-size: 22px; display:block; text-align:left;">${options.title}</span>`,
                html: `
                <select id="swal-reason" class="form-control">
                    <option value="" selected disabled>Select a reason</option>
                    ${htmlOptions}
                </select>
                <textarea rows="4" id="swal-reason-other" class="form-control" placeholder="Please specify your reason" style="display:none; margin-top:12px;"></textarea>
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
                        // return { key: 'other', value: other };
                    }

                    return sel;
                    // return { key: sel, value: reasons[sel] };
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

        // Show flash messages
	    @foreach (session('flash_notification', collect())->toArray() as $message)
	        AIZ.plugins.notify('{{ $message['level'] }}', '{{ $message['message'] }}');
	    @endforeach


        if ($('#lang-change').length > 0) {
            $('#lang-change .dropdown-menu a').each(function() {
                $(this).on('click', function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var locale = $this.data('flag');
                    $.post('{{ route('language.change') }}',{_token:'{{ csrf_token() }}', locale:locale}, function(data){
                        location.reload();
                    });

                });
            });
        }

        function menuSearch(){
			var filter, item;
			filter = $("#menu-search").val().toUpperCase();
			items = $("#main-menu").find("a");
			items = items.filter(function(i,item){
				if($(item).find(".aiz-side-nav-text")[0].innerText.toUpperCase().indexOf(filter) > -1 && $(item).attr('href') !== '#'){
					return item;
				}
			});

			if(filter !== ''){
				$("#main-menu").addClass('d-none');
				$("#search-menu").html('')
				if(items.length > 0){
					for (i = 0; i < items.length; i++) {
						const text = $(items[i]).find(".aiz-side-nav-text")[0].innerText;
						const link = $(items[i]).attr('href');
						 $("#search-menu").append(`<li class="aiz-side-nav-item"><a href="${link}" class="aiz-side-nav-link"><i class="las la-ellipsis-h aiz-side-nav-icon"></i><span>${text}</span></a></li`);
					}
				}else{
					$("#search-menu").html(`<li class="aiz-side-nav-item"><span	class="text-center text-muted d-block">{{ ('Nothing Found') }}</span></li>`);
				}
			}else{
				$("#main-menu").removeClass('d-none');
				$("#search-menu").html('')
			}
        }
    </script>
</body>
</html>
