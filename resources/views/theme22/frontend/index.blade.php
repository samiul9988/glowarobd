@extends(config('app.theme').'frontend.layouts.app')

@section('content')
    {{-- Categories , Sliders . Today's deal --}}
    <div class="home-banner-area mb-4 pt-3 pt-mv-0">
        <div class="container mobile_no_px">
            <div class="row gutters-10 position-relative mobile_no_mx">

                @php
                    $num_todays_deal = count($todays_deal_products);
                @endphp

                @php
                    $banner_class = 12;
                    if(@intval(json_decode(@get_setting('left_category_71')))==1):
                        $banner_class = 9;
                    endif;

                    if(@intval(json_decode(@get_setting('todays_deal_71')))==1):
                        if($num_todays_deal > 0):
                            if($banner_class==12):
                                $banner_class = 9;
                            else:
                                $banner_class = 7;
                            endif;
                        endif;
                    endif;
                @endphp

                @if(@intval(json_decode(@get_setting('left_category_71')))==1)
                <div class="col-lg-3 position-static d-none d-lg-block">
                    @include(config('app.theme').'frontend.partials.category_menu')
                </div>
                @endif

                <div class="mt-2 col-lg-{{  $banner_class }} banner-size-{{ $banner_class }}">
                    @if (get_setting('home_slider_images') != null)
                        <div class="aiz-carousel dots-inside-bottom mobile-img-auto-height" data-arrows="true" data-dots="true" data-autoplay="true">
                            @php $slider_images = json_decode(get_setting('home_slider_images'), true);  @endphp
                            @foreach ($slider_images as $key => $value)
                                <div class="carousel-box">
                                    <a href="{{ json_decode(get_setting('home_slider_links'), true)[$key] }}">
                                        <img
                                            class="d-block mw-100 img-fit rounded shadow-sm overflow-hidden"
                                            src="{{ uploaded_asset($slider_images[$key]) }}"
                                            alt="{{ env('APP_NAME')}} promo"
                                            @if(count($featured_categories) == 0)
                                            height="457"
                                            @else
                                            height="auto"
                                            @endif
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                        >
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(@intval(json_decode(@get_setting('todays_deal_71')))==1)
                    @if($num_todays_deal > 0)
                    <div class="col-lg-2 order-3 mt-3 mt-lg-0">
                        <div class="bg-white rounded shadow-sm">
                            <div class="bg-soft-primary rounded-top p-3 d-flex align-items-center justify-content-center">
                                <span class="fw-600 fs-16 mr-2 text-truncate">
                                    {{ ('Todays Deal') }}
                                </span>
                                <span class="badge badge-primary badge-inline">{{ ('Hot') }}</span>
                            </div>
                            <div class="c-scrollbar-light overflow-auto h-lg-400px p-2 bg-primary rounded-bottom">
                                <div class="gutters-5 lg-no-gutters row row-cols-2 row-cols-lg-1">
                                @foreach ($todays_deal_products as $key => $product)
                                    @if ($product != null)
                                    <div class="col mb-2">
                                        <a href="{{ route('product', $product->slug) }}" class="d-block p-2 text-reset bg-white h-100 rounded">
                                            <div class="row gutters-5 align-items-center">
                                                <div class="col-xxl">
                                                    <div class="img">
                                                        <img
                                                            class="lazyload img-fit h-140px h-lg-80px"
                                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                            data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                                                            alt="{{ $product->getTranslation('name') }}"
                                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                        >
                                                    </div>
                                                </div>
                                                <div class="col-xxl">
                                                    <div class="fs-16">
                                                        <span class="d-block text-primary fw-600">{{ home_discounted_base_price($product) }}</span>
                                                        @if(home_base_price($product) != home_discounted_base_price($product))
                                                            <del class="d-block opacity-70">{{ home_base_price($product) }}</del>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    @endif
                                @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endif

            </div>
        </div>
    </div>

    @if(@intval(json_decode(@get_setting('featured_category_71')))==1)
        @if (count($featured_categories) > 0)
        <div class="container mb-2 categories_sec">
            <div class="row">
                <div class="col">
                    <div class="d-block mb-3 align-items-baseline border-bottom section_title_holder text-center">
                        <h3 class="h5 fw-700 mb-0">
                            <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">Categories</span>
                        </h3>
                    </div>
                </div>
            </div>
            <ul class="list-unstyled mb-0 row gutters-5 categories_list">
                @foreach ($featured_categories as $key => $category)
                @php
                    $featuredIcon = $category->featured_icon;
                    $iconList = json_decode($featuredIcon, true);

                    if(is_array($iconList)){
                        $featuredIcon = $iconList['home_page'];
                    }
                @endphp
                    <li class="minw-0 col-3 col-md-2 mb-0 px-0 border border-width-1 bg-white text-center">
                        <a href="{{ route('products.category', $category->slug) }}" class="d-block rounded bg-white-ex p-1 text-reset shadow-sm py-2">
                            <img
                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                data-src="{{ uploaded_asset($featuredIcon) }}"
                                alt="{{ $category->getTranslation('name') }}"
                                class="lazyload"
                                width="78"
                                height="78"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                            >
                            <div class="text-truncate fs-12 fw-600 mt-2 opacity-70">{{ $category->getTranslation('name') }}</div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
    @endif


    {{-- Banner section 1 --}}
    @if (get_setting('home_banner1_images') != null)
    <div class="mb-2 md_moblie_0">
        <div class="container">
            <div class="row gutters-10">
                @php $banner_1_imags = json_decode(get_setting('home_banner1_images')); @endphp
                @foreach ($banner_1_imags as $key => $value)
                    <div class="col-xl col-6 col-md-6 px-1">
                        <div class="mb-2 mb-lg-0">
                            <a href="{{ json_decode(get_setting('home_banner1_links'), true)[$key] }}" class="d-block text-reset">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_1_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif




    {{-- Banner Section 2 --}}
    @if (get_setting('home_banner2_images') != null)
    @php $banner_2_imags = json_decode(get_setting('home_banner2_images')); @endphp
    @if(count($banner_2_imags)>0)
    <div class="mb-2">
        <div class="container">
            <div class="row gutters-10">

                @foreach ($banner_2_imags as $key => $value)
                    <div class="col-xl col-6 col-md-6 px-1">
                        <div class="mb-2 mb-lg-0">
                            <a href="{{ json_decode(get_setting('home_banner2_links'), true)[$key] }}" class="d-block text-reset">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_2_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @endif


    {{-- Flash Deal --}}
    @if($flash_deal != null && strtotime(date('Y-m-d H:i:s')) >= $flash_deal->start_date && strtotime(date('Y-m-d H:i:s')) <= $flash_deal->end_date)
    <section class="mb-2">
        <div class="container">
            <div class="px-2 py-2 px-md-2 py-md-2 bg-white shadow-sm rounded section_holder">
                <div class="d-flex flex-wrap mb-3 align-items-baseline border-bottom section_title_holder sale_title">
                    <h3 class="h5 fw-700 mb-0">
                        <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ ('Flash Sale') }}</span>
                    </h3>
                    <div class="d-flex w-md-auto flash_sale_count">
                        <h6 class="d-flex text-center pt-2 ml-md-5 mv_full_width">Ending in:</h6>
                        <div class="aiz-count-down ml-auto ml-lg-3 align-items-center" data-date="{{ date('Y/m/d H:i:s', $flash_deal->end_date) }}"></div>
                    </div>

                    <a href="{{ route('flash-deal-details', $flash_deal->slug) }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md w-100 w-md-auto view_btn-ex">{{ ('View More') }}</a>
                </div>

                @php
                    $flash_deal_products = collect($flash_deal->flash_deal_products)->take(20);
                @endphp

                <div class="aiz-carousel gutters-10 products_holder" data-items="6" data-xl-items="6" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                    @foreach ($flash_deal_products as $key => $flash_deal_product)
                        @if ($flash_deal_product->product != null && $flash_deal_product->product->published != 0)
                            <div class="carousel-box px-1">
                                @include(config('app.theme').'frontend.partials.product_box_1',['product' => $flash_deal_product->product,'flash_deal_countdown'=>false])
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Category wise Products --}}
    <div id="section_home_categories">
        <section class="product--home-categories mt-1 mb-3" style="background-image:url('')">
            <div class="d-block d-md-none">
                <div class="img-fit mx-auto skeleton-img" style="height: 180px; background-color: #f0f0f0;"></div>
            </div>
            <div class="container">
                <div class="bg-white-ex px-0 py-md-3">
                    {{-- Full div skeleton --}}
                    <div class="d-none d-md-flex mb-3 align-items-baseline border-bottom skeleton-text" style="height: 50px; background-color: #f0f0f0; border-radius: 4px;"></div>

                    <div class="row gutters-10">
                        {{-- Skeleton Product Boxes --}}
                        @php
                            $limit = $agent->isMobile() ? 1 : 5;
                        @endphp
                        @foreach (range(0, $limit) as $i)
                            <div class="col-lg-2 col-md-3 col-6 px-1 skeleton-product-box">
                                <div class="border border-light rounded hov-shadow-md mt-1 mb-1 bg-white">
                                    <div class="position-relative">
                                        <div class="img-fit mx-auto skeleton-img" style="height: 180px; background-color: #f0f0f0;"></div>
                                    </div>
                                    <div class="p-2 pb-3 px-md-2 pt-2 text-center">
                                        <div class="skeleton-text" style="width: 100%; height: 16px; background-color: #f0f0f0; margin-bottom: 8px;"></div>
                                        <div class="skeleton-text" style="width: 80%; height: 16px; background-color: #f0f0f0; margin: 0 auto 8px;"></div>
                                        <div class="skeleton-text" style="width: 60%; height: 16px; background-color: #f0f0f0; margin: 0 auto;"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <style>
            /* Animation for skeleton elements */
            .skeleton-img, .skeleton-title, .skeleton-button, .skeleton-text,
            .skeleton-div {
                position: relative;
                overflow: hidden;
                border-radius: 4px;
            }

            .skeleton-img::after, .skeleton-title::after,
            .skeleton-button::after, .skeleton-text::after,
            .skeleton-div::after {
                content: '';
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

            /* Responsive adjustments */
            @media (max-width: 767px) {
                .skeleton-product-box {
                    margin-bottom: 15px;
                }
            }
        </style>
    </div>


    {{-- Banner Section 3 --}}
    @if (get_setting('home_banner3_images') != null)
    @php $banner_3_imags = json_decode(get_setting('home_banner3_images')); @endphp
    @if(count($banner_3_imags)>0)
    <div class="mb-2">
        <div class="container">
            <div class="row gutters-10">

                @foreach ($banner_3_imags as $key => $value)
                    <div class="col-xl col-6 col-md-6 px-1">
                        <div class="mb-2 mb-lg-0">
                            <a href="{{ json_decode(get_setting('home_banner3_links'), true)[$key] }}" class="d-block text-reset">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_3_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @endif




    {{-- Featured Section --}}

    <div id="section_featured">

    </div>

    {{-- Best Selling  --}}
    <div id="section_best_selling"></div>

    <!-- Auction Product -->
    @if(addon_is_activated('auction'))
        <div id="auction_products"></div>
    @endif







    {{-- Classified Product --}}
    @if(get_setting('classified_product') == 1)
        @php
            $classified_products = \App\Models\CustomerProduct::where('status', '1')->where('published', '1')->take(10)->get();
            // if (shouldHideStockOutProducts()){
            //     $classified_products = filter_stock_out_products($classified_products);
            // }
        @endphp
           @if (count($classified_products) > 0)
               <section class="mb-4">
                   <div class="container">
                       <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                            <div class="d-flex mb-3 align-items-baseline border-bottom">
                                <h3 class="h5 fw-700 mb-0">
                                    <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ ('Classified Ads') }}</span>
                                </h3>
                                <a href="{{ route('customer.products') }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ ('View More') }}</a>
                            </div>
                           <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                               @foreach ($classified_products as $key => $classified_product)
                                   <div class="carousel-box">
                                        <div class="aiz-card-box border border-light rounded hov-shadow-md my-2 has-transition">
                                            <div class="position-relative">
                                                <a href="{{ route('customer.product', $classified_product->slug) }}" class="d-block">
                                                    <img
                                                        class="img-fit lazyload mx-auto h-140px h-md-210px"
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        data-src="{{ uploaded_asset($classified_product->thumbnail_img) }}"
                                                        alt="{{ $classified_product->getTranslation('name') }}"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                    >
                                                </a>
                                                <div class="absolute-top-left pt-2 pl-2">
                                                    @if($classified_product->conditon == 'new')
                                                       <span class="badge badge-inline badge-success">{{ ('new')}}</span>
                                                    @elseif($classified_product->conditon == 'used')
                                                       <span class="badge badge-inline badge-danger">{{ ('Used')}}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="p-md-3 p-2 text-left">
                                                <div class="fs-15 mb-1">
                                                    <span class="fw-700 text-primary">{{ single_price($classified_product->unit_price) }}</span>
                                                </div>
                                                <h3 class="fw-600 fs-13 text-truncate-2 lh-1-4 mb-0 h-35px">
                                                    <a href="{{ route('customer.product', $classified_product->slug) }}" class="d-block text-reset">{{ $classified_product->getTranslation('name') }}</a>
                                                </h3>
                                            </div>
                                       </div>
                                   </div>
                               @endforeach
                           </div>
                       </div>
                   </div>
               </section>
           @endif
       @endif




    {{-- Best Seller --}}
    <div id="section_best_sellers">

    </div>

    {{-- Top 10 categories and Brands --}}
    @if (get_setting('top10_categories') != null && get_setting('top10_brands') != null)
    <section class="mb-2">
        <div class="container">
            <div class="row gutters-10">
                @if(@intval(json_decode(@get_setting('topten_categories_71')))==1)
                @if (get_setting('top10_categories') != null)
                    <div class="col-lg-6">
                        <div class="d-flex mb-3 align-items-baseline border-bottom section_title_holder">
                            <h3 class="h5 fw-700 mb-0">
                                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ ('Top 10 Categories') }}</span>
                            </h3>
                            <a href="{{ route('categories.all') }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ ('View All Categories') }}</a>
                        </div>
                        <div class="row gutters-5">
                            @php $top10_categories = json_decode(get_setting('top10_categories')); @endphp
                            @foreach ($top10_categories as $key => $value)
                                @php
                                    $category = \App\Models\Category::find($value);
                                @endphp
                                @if ($category != null)
                                    @php
                                        $banner = $category->banner;
                                        $bannerList = json_decode($banner, true);
                                        if(is_array($bannerList)){
                                            $banner = $bannerList['web'];
                                        }
                                    @endphp
                                    <div class="col-sm-6">
                                        <a href="{{ route('products.category', $category->slug) }}" class="bg-white border d-block text-reset rounded p-2 hov-shadow-md mb-2">
                                            <div class="row align-items-center no-gutters">
                                                <div class="col-3 text-center">
                                                    <img
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        data-src="{{ uploaded_asset($banner) }}"
                                                        alt="{{ $category->getTranslation('name') }}"
                                                        class="img-fluid img lazyload h-60px"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                    >
                                                </div>
                                                <div class="col-7">
                                                    <div class="text-truncat-2 pl-3 fs-14 fw-600 text-left">{{ $category->getTranslation('name') }}</div>
                                                </div>
                                                <div class="col-2 text-center">
                                                    <i class="la la-angle-right text-primary"></i>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
                @endif

                @if(@intval(json_decode(@get_setting('topten_brands_71')))==1)
                @if (get_setting('top10_brands') != null)
                    <div class="col-lg-6">
                        <div class="d-flex mb-3 align-items-baseline border-bottom section_title_holder">
                            <h3 class="h5 fw-700 mb-0">
                                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ ('Top 10 Brands') }}</span>
                            </h3>
                            <a href="{{ route('brands.all') }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ ('View All Brands') }}</a>
                        </div>
                        <div class="row gutters-5">
                            @php $top10_brands = json_decode(get_setting('top10_brands')); @endphp
                            @foreach ($top10_brands as $key => $value)
                                @php $brand = \App\Models\Brand::find($value); @endphp
                                @if ($brand != null)
                                    <div class="col-sm-6">
                                        <a href="{{ route('products.brand', $brand->slug) }}" class="bg-white border d-block text-reset rounded p-2 hov-shadow-md mb-2">
                                            <div class="row align-items-center no-gutters">
                                                <div class="col-4 text-center">
                                                    <img
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        data-src="{{ uploaded_asset($brand->logo) }}"
                                                        alt="{{ $brand->getTranslation('name') }}"
                                                        class="img-fluid img lazyload h-60px"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                    >
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-truncate-2 pl-3 fs-14 fw-600 text-left">{{ $brand->getTranslation('name') }}</div>
                                                </div>
                                                <div class="col-2 text-center">
                                                    <i class="la la-angle-right text-primary"></i>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
                @endif
            </div>
        </div>
    </section>
    @endif

@endsection

@section('script')
    <script>
        $(document).ready(function(){

            @if(@intval(json_decode(@get_setting('featured_products_71')))==1 && $agent->isDesktop())

                var startDig = 0;
                $(document).scroll(function() {
                    if(startDig==0){
                        startDig = 1;
                        $.get('{{ route('home.section.featured') }}', {_token:'{{ csrf_token() }}'}, function(data){
                            $('#section_featured').html(data);
                            //AIZ.plugins.slickCarousel();
                        });
                    }
                });

                /*
                var startDig = 0;
                $(document).scroll(function() {


                    var reachLevel = parseFloat($(window).scrollTop())/( parseFloat($(document).height()) - parseFloat($(window).height()) )*100;


                    var top = document.querySelector('.featured_product_loading').getBoundingClientRect().top - 0;
                    var bottom = document.querySelector('.featured_product_loading').getBoundingClientRect().bottom + 0;
                    var readyForDig = $('.featured_product_loading').attr("data-end");

                    if (top <= window.innerHeight && bottom >= 0 && startDig==0 && readyForDig=='false') {
                        if($('.featured_product_loading').length>0){
                        startDig = 1;

                        var $page = parseInt($('input[name="featured_products_page"]').val());
                        $page += 1;

                        var nextPageUrl = '{{ route('home.section.featured') }}';

                        $('.featured_product_loading span').show();
                        $.ajax({
                            type: 'GET',
                            url: nextPageUrl,
                            data: {_token:'{{ csrf_token() }}', page:$page},
                            success: function(data) {

                                $('#featured_products_append').append(data);
                                $('.fp-'+$page).show('slow');

                                $('input[name="featured_products_page"]').val($page);
                                $('.featured_product_loading span').hide();
                                AIZ.plugins.countDown();
                                if(data==''){
                                    $('.featured_product_loading').attr("data-end","true");
                                    $('.featured_product_loading .featured_no_data_found').show();
                                    AIZ.plugins.countDown();
                                }

                                startDig=0;

                            }
                        });
                        }
                    }


                });
                */

                /*var startDig = 0 ;
                $(window).scroll(function() {


                    var reachLevel = parseFloat($(window).scrollTop())/( parseFloat($(document).height()) - parseFloat($(window).height()) )*100;

                    //if($(window).scrollTop() == $(document).height() - $(window).height()) {
                    if(reachLevel>=85 && startDig==0){

                        startDig = 1;

                        var $page = parseInt($('input[name="featured_products_page"]').val());
                        $page += 1;
                        $('.featured_product_loading').show();
                        $.get('{{ route('home.section.featured') }}', {_token:'{{ csrf_token() }}', page:$page}, function(data){
                            $('#featured_products_append').append(data);
                            $('.fp-'+$page).show('slow');

                            $('input[name="featured_products_page"]').val($page);
                            $('.featured_product_loading').hide();
                            if(data==''){
                                $('.featured_no_data_found').show();
                            }
                            setTimeout(() => {
                                startDig=0;
                            }, 5000);

                        });
                    }


                });*/

            @endif

            @if(@intval(json_decode(@get_setting('best_selling_products_71')))==1)
                $.post('{{ route('home.section.best_selling') }}', {_token:'{{ csrf_token() }}'}, function(data){
                    $('#section_best_selling').html(data);
                    AIZ.plugins.slickCarousel();
                });
            @endif

            {{--
            $.post('{{ route('home.section.auction_products') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#auction_products').html(data);
                AIZ.plugins.slickCarousel();
            });
            --}}

            $.get('{{ route('home.section.home_categories') }}', function(data){
                // console.log(data);
                $('#section_home_categories').html(data).fadeIn(400, function(){
                    AIZ.plugins.slickCarousel();
                });
                // AIZ.plugins.slickCarousel();
            });

            @if(@intval(json_decode(@get_setting('best_sellers_71')))==1)
                $.post('{{ route('home.section.best_sellers') }}', {_token:'{{ csrf_token() }}'}, function(data){
                    $('#section_best_sellers').html(data);
                    AIZ.plugins.slickCarousel();
                });
            @endif

        });
    </script>
@endsection
