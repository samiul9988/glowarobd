@php
$languagefilePath = storage_path('app/public/languages/language.json');
if (file_exists($languagefilePath)) {
    $jsonData = file_get_contents($languagefilePath);
    $languages = collect(json_decode($jsonData, true));
    if($languages->isEmpty()) {
        $languages = collect(\App\Models\Language::all()->toArray());
    }
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
    {{-- <meta name="author" content="{{ get_setting('meta_author', env('APP_NAME')) }}">
    <link rel="canonical" href="{{ env('APP_URL') }}" /> --}}

    {{-- <title>@yield('meta_title', get_setting('website_name').' | '.get_setting('site_motto'))</title> --}}

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(app()->environment('staging'))
        <meta name="robots" content="noindex,nofollow">
    @else
        <meta name="robots" content="index,follow">
    @endif
    {{-- <meta name="title" content="@yield('meta_title', get_setting('website_name').' | '.get_setting('site_motto'))">
    <meta name="description" content="@yield('meta_description', get_setting('meta_description') )" />
    <meta name="keywords" content="@yield('meta_keywords', get_setting('meta_keywords') )"> --}}

    <link rel="alternate" href="{{ config('app.url') }}" hreflang="en" />

    {{-- @if(!isset($detailedProduct) && !isset($customer_product) && !isset($shop) && !isset($page) && !isset($blog))
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
    @endif --}}

    @yield('meta')

    <!-- Favicon -->
    <link rel="icon" href="{{ uploaded_asset(get_setting('site_icon')) }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ static_asset('assets/css/vendors.css') }}">
    @if($languages->where('code', Session::get('locale', Config::get('app.locale')))->first()['rtl'] == 1)
    <link rel="stylesheet" href="{{ static_asset('assets/css/bootstrap-rtl.min.css') }}">
    @endif
    <link rel="stylesheet" href="{{ static_asset('assets/css/aiz-core.css?val='.rand().'') }}">

    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>

    <link rel="stylesheet" href="{{ static_asset("assets/".str_replace('.','',config('app.theme'))."/frontend/css/custom-style.css") }}">
    <link rel="stylesheet" href="{{ static_asset("assets/".str_replace('.','',config('app.theme'))."/frontend/css/fancybox.css") }}">
    <link rel="stylesheet" href="{{ static_asset("assets/".str_replace('.','',config('app.theme'))."/frontend/css/light-slider.css") }}">
    <link rel="stylesheet" href="{{ static_asset('assets/tekka/frontend/css/app.css') }}">
    @stack('styles22')

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

    @if (get_setting('facebook_pixel') == 1 && false)
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
    <script defer src="https://unpkg.com/alpinejs@3.2.4/dist/cdn.min.js"></script>

    <style>
        .discount-badge {
            background: #c04770;
            border-radius: 999px;
            padding: 6px 8px;
            line-height: 1;
            font-weight: 600;
            color: #ffffff;
            margin-left: 18px;
            font-size: 11px;
        }

        @media (max-width: 768px) {
            .discount-badge {
                margin-left: 12px;
            }
        }

        @media (max-width: 576px) {
            .discount-badge {
                margin-left: 8px;
                font-size: 10px;
                padding: 4px 6px;
            }
        }
    </style>
    @yield('css')
</head>
<body>

    @if (get_setting('google_tagmanager') == 1)
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ env('TAG_MANAGER_ID') }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    @endif

    <!-- aiz-main-wrapper -->
    <!-- <div class="aiz-main-wrapper d-flex flex-column" style="min-height:auto !important;"> -->

        @if(get_setting('enable_snow_effect') == 'on')
            @include(config('app.theme').'frontend.partials.snowFall')
        @endif

        <!-- Header -->
        <!-- Sticky Header -->
        @if(get_setting('header_stikcy') == 'on')
            @include(config('app.theme').'frontend.inc.nav', ['sticky' => 1])
        @endif
         <!-- Normal Header -->


        @yield('content')

        @include(config('app.theme').'frontend.inc.footer')

    <!-- </div> -->

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
       @php
           $popupImage = get_setting('web_popup_image');
           if (!is_null($popupImage)) {
               $popupImage = uploaded_asset($popupImage);
           }
           $linkableType = get_setting('web_popup_content_for');
           $linkableId = match(strtolower($linkableType)) {
                'product' => get_setting('web_popup_product_id'),
                'category' => get_setting('web_popup_category_id'),
                'brand' => get_setting('web_popup_brand_id'),
                'flash deal' => get_setting('web_popup_flash_deal_id'),
                default => null,
            };
            $redirectUrl = match(strtolower($linkableType)) {
                'product' => route('product', \App\Models\Product::find($linkableId)->slug ?? ''),
                'category' => route('products.category', \App\Models\Category::find($linkableId)->slug ?? ''),
                'brand' => route('products.brand', \App\Models\Brand::find($linkableId)->slug ?? ''),
                'flash deal' => route('flash-deal-details', \App\Models\FlashDeal::find($linkableId)->slug ?? ''),
                default => null,
            };
       @endphp

        @if(!is_null($popupImage))
            <div class="modal website-popup removable-session d-none" data-key="website-popup" data-value="removed">
                <div class="absolute-full bg-black opacity-60"></div>
                <div class="modal-dialog modal-dialog-centered modal-dialog-zoom modal-md">
                    <div class="modal-content position-relative border-0 rounded-0" style="max-height: 90vh;">
                        <div class="aiz-editor-data">
                            {{-- {!! get_setting('website_popup_content') !!} --}}
                            @if ($linkableType && $linkableId && $redirectUrl)
                                <a href="{{ $redirectUrl }}" class="set-session" data-key="website-popup" data-value="removed" data-toggle="remove-parent" data-parent=".website-popup">
                                    <img src="{{ $popupImage }}" alt="Website Popup Image" style="max-height: 86vh; width: -webkit-fill-available;">
                                </a>
                            @else
                                <img src="{{ $popupImage }}" alt="Website Popup Image" style="max-height: 86vh; width: -webkit-fill-available;">
                            @endif
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
    <script src="{{ static_asset("assets/".str_replace('.','',config('app.theme'))."/frontend/js/custom.js") }}"></script>
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
                        $.post('{{ route("language.change") }}',{_token: AIZ.data.csrf, locale:locale}, function(data){
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
                        $.post('{{ route("currency.change") }}',{_token: AIZ.data.csrf, currency_code:currency_code}, function(data){
                            location.reload();
                        });

                    });
                });
            }
        });

        // Debounce function to delay the execution of the search function
        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        }

        // Update event listeners to use the debounced search function
        const debouncedSearch = debounce(search, 1000);
        $('#search, #search2').on('keyup', function(){
            debouncedSearch(this.id);
        });

        $('#search, #search2').on('focus', function(){
            debouncedSearch(this.id);
        });

        function search(inputId) {
            var searchKey = $('#' + inputId).val();

            if (searchKey.length > 0) {
                $('body').addClass("typed-search-box-shown");

                if (inputId === 'search') {
                    $('.typed-search-box').removeClass('d-none');
                    $('.search-preloader').removeClass('d-none');
                } else if (inputId === 'search2') {
                    $('.typed-search-box').removeClass('d-none');
                    $('.search-preloader').removeClass('d-none');
                }

                $.post('{{ route('search.ajax') }}', { _token: AIZ.data.csrf, search: searchKey }, function(data) {
                    if (data == '0') {
                        if (inputId === 'search') {
                            $('#search-content').html(null);
                            $('#search-content').siblings('.search-nothing').removeClass('d-none').html('Sorry, nothing found for <strong>"' + searchKey + '"</strong>');
                        } else if (inputId === 'search2') {
                            $('#search-content2').html(null);
                            $('#search-content2').siblings('.search-nothing').removeClass('d-none').html('Sorry, nothing found for <strong>"' + searchKey + '"</strong>');
                        }
                        $('.search-preloader').addClass('d-none');
                    } else {
                        if (inputId === 'search') {
                            $('#search-content').html(data);
                            $('#search-content').siblings('.search-nothing').addClass('d-none').html(null);
                        } else if (inputId === 'search2') {
                            $('#search-content2').html(data);
                            $('#search-content2').siblings('.search-nothing').addClass('d-none').html(null);
                        }
                        $('.search-preloader').addClass('d-none');
                    }
                });
            } else {
                if (inputId === 'search') {
                    $('.typed-search-box').addClass('d-none');
                } else if (inputId === 'search2') {
                    $('.typed-search-box').addClass('d-none');
                }
                $('body').removeClass("typed-search-box-shown");
            }
        }

        async function updateNavCart(view,count){
            $('.cart-count').html(count);
            $('#cart_items').html(view);
            $('#cart_items2').html(view);
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
                // Dispatch cart-updated event for gift offers component
                // window.dispatchEvent(new CustomEvent('cart-updated'));
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
            @if (get_setting('guest_order_activation') == 1)
                $.post('{{ route('wishlists.store') }}', {_token: AIZ.data.csrf, id:id}, function(response){
                    if(response.success){
                        $('#wishlist').html(response.html);
                        AIZ.plugins.notify('success', response.message || "Item has been added to wishlist");
                    }
                    else {
                        AIZ.plugins.notify('warning', response.message || "{{ ('Please login first') }}");
                    }
                });
            @else
                AIZ.plugins.notify('warning', "{{ ('Please login first') }}");
            @endif
        }

        function showAddToCartModal(id){
            if(!$('#modal-size').hasClass('modal-lg')){
                $('#modal-size').addClass('modal-lg');
            }
            $('#addToCart-modal-body').html(null);
            $('#addToCart').modal();
            $('.c-preloader').show();
            $.post('{{ route("cart.showCartModal") }}', {_token: AIZ.data.csrf, id:id}, function(data){
                $('.c-preloader').hide();
                $('#addToCart-modal-body').html(data);
                AIZ.plugins.slickCarousel();
                AIZ.plugins.zoom();
                AIZ.extra.plusMinus();
                getVariantPrice();
            });
        }

        $('#option-choice-form-m input').on('change', function(){
            getVariantPrice();
        });

        function getVariantPrice(){
            if($('#option-choice-form-m input[name=quantity]').val() > 0 && checkAddToCartValidity()){
                $.ajax({
                    type:"POST",
                    url: '{{ route("products.variant_price") }}',
                    data: $('#option-choice-form-m').serializeArray(),
                    success: function(data){

                        $('.product-gallery-thumb .carousel-box').each(function (i) {
                            if($(this).data('variation') && data.variation == $(this).data('variation')){
                                $('.product-gallery-thumb').slick('slickGoTo', i);
                            }
                        });

                        $('#option-choice-form-m #chosen_price_div').removeClass('d-none');
                        $('#option-choice-form-m #chosen_price_div #chosen_price').html(data.min_price);
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
            $('#option-choice-form-m input:radio').each(function() { // find unique names
                names[$(this).attr('name')] = true;
            });
            var count = 0;
            $.each(names, function() { // then count them
                count++;
            });

            if($('#option-choice-form-m input:radio:checked').length == count){
                return true;
            }

            return false;
        }

        // async function addToCart(m=''){
        //     if(checkAddToCartValidity()) {
        //         // $('#addToCart').modal();
        //         // $('.c-preloader').show();
        //         $.ajax({
        //             type:"POST",
        //             url: '{{ route("cart.addToCart") }}',
        //             data: (m != '') ? $(m).closest('form').serializeArray() : $('#option-choice-form-m').serializeArray(),
        //             success: async function(data){
        //                 $('#addToCart-modal-body').html(null);
        //                 // $('.c-preloader').hide();
        //                 // $('#modal-size').removeClass('modal-lg');
        //                $('#addToCart-modal-body').html(data.modal_view);
        //                AIZ.extra.plusMinus();
        //                await updateNavCart(data.nav_cart_view,data.cart_count);
        //                AIZ.plugins.notify('success',"Item added to your cart!");
        //             }
        //         });
        //     }else{
        //         AIZ.plugins.notify('warning', "Please choose all the options");
        //     }
        // }
        async function addToCart(button) {
            button.disabled = true;
            if (checkAddToCartValidity()) {
                button.classList.add('loading');
                button.innerHTML = '<i class="fas fa-spinner loading-spinner"></i>';

                try {
                    let data = await $.ajax({
                        type: "POST",
                        url: '{{ route("cart.addToCart") }}',
                        data: (button != '') ? $(button).closest('form').serializeArray() : $('#option-choice-form-m').serializeArray(),
                        success: async function(data){
                            if(data.status == 1){
                                $('#addToCart-modal-body').html(null);
                                $('#addToCart-modal-body').html(data.modal_view);
                                AIZ.extra.plusMinus();
                                await updateNavCart(data.nav_cart_view, data.cart_count);
                                AIZ.plugins.notify('success', "Item added to your cart!");

                                // Google Tag Manager DataLayer
                                @if (get_setting('google_tagmanager'))
                                    dataLayer.push({ ecommerce: null });
                                    dataLayer.push({
                                        event    : "add_to_cart",
                                        ecommerce: {
                                            items: data.item
                                        }
                                    });
                                @endif
                            }else{
                                // AIZ.plugins.notify('danger', "Oops! This item is out of stock.");
                                AIZ.plugins.notify('danger', "Oops! Something went wrong.");
                            }
                        }
                    });

                    // $('#addToCart-modal-body').html(null);
                    // $('#addToCart-modal-body').html(data.modal_view);
                    // AIZ.extra.plusMinus();
                    // await updateNavCart(data.nav_cart_view, data.cart_count);

                } catch (error) {
                    console.error('Error adding to cart:', error);
                    AIZ.plugins.notify('danger', "An error occurred. Please try again.");
                } finally {
                    button.disabled = false;
                    button.classList.remove('loading');
                    button.innerHTML = 'Add to Bag';
                }
            } else {
                AIZ.plugins.notify('warning', "Please choose all the options");
            }
        }


        async function buyNow(m=''){
            if(checkAddToCartValidity()) {
                $('#addToCart-modal-body').html(null);
                // $('#addToCart').modal();
                // $('.c-preloader').show();
                $.ajax({
                    type:"POST",
                    url: '{{ route("cart.addToCart") }}',
                    data: (m != '') ? $(m).closest('form').serializeArray() : $('#option-choice-form-m').serializeArray(),
                    success: async function(data){
                        if(data.status == 1){
                            AIZ.plugins.notify('success',"Item added to your cart!");
                            $('#addToCart-modal-body').html(data.modal_view);
                            await updateNavCart(data.nav_cart_view,data.cart_count);
                            window.location.replace("{{ route('checkout.shipping_info') }}");
                        }else{
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

            $.post('{{ route("purchase_history.details") }}', { _token : AIZ.data.csrf, order_id : order_id}, function(data){
                $('#order-details-modal-body').html(data);
                $('#order_details').modal();
                $('.c-preloader').hide();
            });
        }

        function cancel_order(order_id)
        {
            if(confirm('Are you sure you want to cancel this order?')){
            $.post('{{ route("purchase_history.cancel") }}', { _token : AIZ.data.csrf, order_id : order_id}, function(data){
                $('#order_details').modal('hide');
            });
        }
        }

        function refund_request(order_id)
        {
            if(confirm('Are you sure you want to refund this order?')){
                var reason = $('#refund_reason').val();
                if(reason){
                    $.post('{{ route("order_request.refund") }}', { _token : AIZ.data.csrf, order_id : order_id, reason: reason}, function(data){
                        $('#order_details').modal('hide');
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
        $(document).ready(function(){
            $('#accordion > li').click(function(){
                $('#accordion > li > .megamenu_wrapper').toggleClass('active');
            });
        });

        $(document).ready(function() {

            // $('#accordion li').children('ul').hide();

            $('.hasSubItem').click(function() {

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

    @stack('scripts')

    @php
        echo get_setting('footer_script');
    @endphp


    <script async src="{{ static_asset("assets/".str_replace('.','',config('app.theme'))."/frontend/js/lightslider.min.js") }}"></script>
    <script async src="{{ static_asset("assets/".str_replace('.','',config('app.theme'))."/frontend/js/fancybox.umd.js") }}"></script>

</body>
</html>
