@extends(config('app.theme') . 'frontend.layouts.app')

@section('meta')
    <x-seo />
@endsection

@section('content')

    @php
        $categoryfilePath = storage_path('app/public/categories/category.json');
        if (file_exists($categoryfilePath)) {
            $jsonData = file_get_contents($categoryfilePath);
            $categories = collect(json_decode($jsonData, true));
            if ($categories->isEmpty()) {
                $categories = collect(\App\Models\Category::all()->toArray());
            }
        } else {
            $categories = collect(\App\Models\Category::all()->toArray());
        }
        $collection_designs = collect(\App\Models\CollectionDesign::all()->toArray());
    @endphp
    {{-- Checking featured_product visibility from setting --}}
    @php
        $fp = json_decode(get_setting('featured_products_71'));
        // dd($fp);
        if ($fp != null && $fp[0] == 1) {
            $feature_products1 = \App\Models\Product::where('published', 1)
                ->when(shouldHideStockOutProducts(), function ($query) {
                    return $query->availableInStock();
                })
                ->where('featured', 1)
                ->inRandomOrder()
                ->limit(8)
                ->get();
            $feature_products2 = $feature_products1;
        }
    @endphp
    {{-- Feature Categories --}}
            @if (@intval(json_decode(@get_setting('featured_category_71'))) == 1)
                @includeIf('tekka.frontend.components.feature_categories.section', [
                    'featured_categories' => $featured_categories,
                ])
                @if (count($featured_categories) > 0)
                    <div class="container-fluid p-0 d-lg-none categories_sec">
                        <ul class="list-unstyled mb-0 row gutters-5 categories_list">
                            @foreach ($featured_categories as $key => $category)
                                @php
                                    $featured_icon = json_decode($category->featured_icon, true);
                                @endphp
                                <li class="mb-0 px-0 bg-white text-center" style = "">
                                    <a href="{{ route('products.category', $category->slug) }}"
                                        class="d-block rounded bg-white-ex p-1 text-reset py-2">
                                        <img src="{{ uploaded_asset($agent->isMobile() ? ($featured_icon['home_page'] ?? '') : ($featured_icon['web'] ?? '')) }}"
                                            data-src="{{ uploaded_asset($agent->isMobile() ? ($featured_icon['home_page'] ?? '') : ($featured_icon['web'] ?? '')) }}"
                                            alt="{{ $category->getTranslation('name') }}" class="lazyload img-fluid" width="78"
                                            height="78"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';
                                        ">
                                        <div class="product-title mt-1">
                                            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100px;">
                                                {{ $category->getTranslation('name') }}
                                            </span>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                            <li class="mb-0 px-0 bg-white text-center pr-3">
                                <a href="{{ route('categories.all') }}" class="d-block rounded bg-white-ex p-1 text-reset py-2">
                                    <img src="{{ static_asset('assets/img/more.png') }}" alt="More Button" class="lazyload"
                                        width="78" height="78"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                                    <div class="product-title text-truncate mt-1">More</div>
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif
            @endif
    {{-- Categories , Sliders . Today's deal --}}
        <div class="home-banner-area pb-3">
            <div class=" mobile_no_px">
                <div class="row gutters-10 position-relative mobile_no_mx">

                    @php
                        $num_todays_deal = count($todays_deal_products);
                    @endphp

                    @php
                        $banner_class = 12;
                        if (@intval(json_decode(@get_setting('left_category_71'))) == 1):
                            $banner_class = 9;
                        endif;

                        if (@intval(json_decode(@get_setting('todays_deal_71'))) == 1):
                            if ($num_todays_deal > 0):
                                if ($banner_class == 12):
                                    $banner_class = 9;
                                else:
                                    $banner_class = 7;
                                endif;
                            endif;
                        endif;
                    @endphp

                    @if (@intval(json_decode(@get_setting('left_category_71'))) == 1)
                        <div class="col-lg-3 position-static d-none d-lg-block">
                            @include(config('app.theme') . 'frontend.partials.category_menu')
                        </div>
                    @endif

                    <div class="col-lg-{{ $banner_class }} banner-size-{{ $banner_class }} p-0">
                        @if (get_setting('home_slider_images') != null)
                            <div class="aiz-carousel dots-inside-bottom mobile-img-auto-height" data-arrows="true" data-dots="true"
                                data-autoplay="true">
                                @php
                                    $slider_images = json_decode(get_setting('home_slider_images'), true);
                                    $phone_slider_images = json_decode(get_setting('home_slider_images_mobile'), true);

                                @endphp
                                @foreach ($slider_images as $key => $value)
                                    <div class="carousel-box">
                                        <a href="{{ json_decode(get_setting('home_slider_links'), true)[$key] }}">
                                            <img class="mw-100 img-fit shadow-sm overflow-hidden banner-desktop"
                                                src="{{ uploaded_asset($slider_images[$key]) }}"
                                                alt="{{ env('APP_NAME') }} promo"
                                                @if (count($featured_categories) == 0) height="457"
                                                @else
                                                height="auto" @endif
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">


                                            <!-- This is the mobile banner -->
                                            <!-- Now it is static -->
                                            <img class="mw-100 img-fit rounded shadow-sm overflow-hidden banner-mobile"
                                                src="{{ !empty($phone_slider_images) ? uploaded_asset($phone_slider_images[$key]) : static_asset('assets/img/placeholder-rect.jpg') }}"
                                                alt="{{ env('APP_NAME') }} promo"
                                                @if (count($featured_categories) == 0) height="457"
                                                @else
                                                height="auto" @endif
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if (@intval(json_decode(@get_setting('todays_deal_71'))) == 1)
                        @if ($num_todays_deal > 0)
                            <div class="col-lg-2 order-3 mt-3 mt-lg-0 todays_deal_71">
                                <div class="bg-white rounded shadow-sm">
                                    <div
                                        class="bg-soft-primary rounded-top p-3 d-flex align-items-center justify-content-center">
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
                                                        <a href="{{ route('product', $product->slug) }}"
                                                            class="d-block p-2 text-reset bg-white h-100 rounded">
                                                            <div class="row gutters-5 align-items-center">
                                                                <div class="col-xxl">
                                                                    <div class="img">
                                                                        <img class="lazyload img-fit h-140px h-lg-80px"
                                                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                                            data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                                                                            alt="{{ $product->getTranslation('name') }}"
                                                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                                                    </div>
                                                                </div>
                                                                <div class="col-xxl">
                                                                    <div class="fs-16">
                                                                        <span
                                                                            class="d-block text-primary fw-600">{{ home_discounted_base_price($product) }}</span>
                                                                        @if (home_base_price($product) != home_discounted_base_price($product))
                                                                            <del
                                                                                class="d-block opacity-70">{{ home_base_price($product) }}</del>
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
    {{-- Feature product with slider mobile version show --}}
    <div class="d-block d-md-none pb-3">
        @if ($fp != null && $fp[0] == 1)
        @includeIf('tekka.frontend.components.feature_products.slider_section', [
            'feature_products' => $feature_products2,
        ])
        @endif
    </div>



    {{-- Banner section 1 --}}
    @if (get_setting('home_banner1_images') != null)
        @includeIf('tekka.frontend.components.banners.section1')
    @endif
</div>


@php
    $home_categories = json_decode(get_setting('home_categories'), true);
    // dd('line: '.'218',$home_categories);
@endphp


{{-- <div class="container custom-container"> --}}
@foreach ($home_categories as $key => $value)
    @php
        $category = (object) $categories->where('id', $value['cid'])->first() ?? null;
        $design = (object) $collection_designs->where('id', $value['did'])->first();
        @endphp
    @if ($key == 2)
        {{-- Banner Section 2 --}}
        @if (get_setting('home_banner2_images') != null)
            @php $banner_2_imags = json_decode(get_setting('home_banner2_images')); @endphp
            @includeIf('tekka.frontend.components.banners.section2', ['banner_2_imags' => $banner_2_imags])
        @endif

        <div class="container custom-container">
            {{-- Collection Design Three --}}
                <section class="mb-3">
                    @include(config('app.theme') . 'frontend.components.collection_design.' . $design->file_name,
                        [
                            'category' => $category,
                            'type' => 'home',
                        ])
                </section>
        </div>

        {{-- Feature product with slider--}}
        <div class="d-none d-md-block">
            @if ($fp != null && $fp[0] == 1)
            @include('tekka.frontend.components.feature_products.slider_section', [
                'feature_products' => $feature_products2,
            ])
            @endif
        </div>

        {{-- Flash Deal --}}
        {{-- Deals and Offers --}}
        @includeIf('tekka.frontend.components.flash_deal.section', ['flash_deal' => $flash_deal])

        {{-- Popular Products --}}
        @if (@intval(json_decode(@get_setting('popular_products_71'))) == 1)
            @include('tekka.frontend.components.popular_products.section')
        @endif

        <!-- Shop By Category Section -->
        <div class="d-none d-md-block">
            <div class="container custom-container">
                @if (get_setting('top10_categories') != null)
                    @include('tekka.frontend.components.shop_by.category')
                @endif
            </div>
        </div>

    @elseif($key == 3)
        <div class="container custom-container {{ $design->file_name }}">
            {{-- Collection Design Two and Others --}}
            <section class="mb-3">
                @include(config('app.theme') . 'frontend.components.collection_design.' . $design->file_name,
                    [
                        'category' => $category,
                        'type' => 'home',
                    ])
            </section>
            {{-- Collection Design Two End  --}}
        </div>
        @if (@intval(json_decode(@get_setting('home_featured_videos'))) == 1 && is_array(@$playlists) && !empty(@$playlists))
            <div class="videos-section container custom-container mt-md-5">
                @includeIf('tekka.frontend.components.videos.section', [
                    'playlists' => @$playlists ?? [],
                ])
            </div>
        @endif
        {{-- Banner Section 3 --}}
        @if (get_setting('home_banner3_images') != null)
            @php $banner_3_imags = json_decode(get_setting('home_banner3_images')); @endphp
            @includeIf('tekka.frontend.components.banners.section3', ['banner_3_imags' => $banner_3_imags])
        @endif
    @else
    <div class="container custom-container {{ $design->file_name }}">
            {{-- Collection Design Two and Others --}}
            <section class="mb-3">
                @include(config('app.theme') . 'frontend.components.collection_design.' . $design->file_name,
                    [
                        'category' => $category,
                        'type' => 'home',
                    ])
            </section>
            {{-- Collection Design Two End  --}}
    </div>
    @endif
@endforeach
{{-- </div> --}}
{{-- Collection Design 1 --}}
{{-- <div class="container custom-container">
    @if (get_setting('home_categories') != null)
        @php
            $home_categories = json_decode(get_setting('home_categories'), true);
            // dd($home_categories);
        @endphp
        @foreach ($home_categories as $key => $value)
            @if ($value['did'] == 1)
                @php
                    $category = (object) $categories->where('id', $value['cid'])->first();
                    $design = (object) $collection_designs->where('id', $value['did'])->first();
                @endphp
                <section class="mb-2">
                    @include(config('app.theme') . 'frontend.components.collection_design.' . $design->file_name,
                        [
                            'category' => $category,
                        ]
                    )
                </section>
                @break
            @endif
        @endforeach
    @endif
</div> --}}
{{-- Collection Design 1 End --}}


{{-- Category wise Products --}}
{{-- <div id="section_home_categories" class = "categories-design-1">

</div> --}}






<!-- Flash Deal -->
{{-- Flash Deal --}}
{{-- Deals and Offers --}}
{{-- @includeIf('tekka.frontend.components.flash_deal.section', ['flash_deal' => $flash_deal]) --}}


{{-- Popular Products --}}
{{-- @if (@intval(json_decode(@get_setting('popular_products_71'))) == 1)
    @includeIf('tekka.frontend.components.popular_products.section')
@endif --}}

{{-- Banner Section 2 --}}
{{-- @if (get_setting('home_banner2_images') != null)
    @php $banner_2_imags = json_decode(get_setting('home_banner2_images')); @endphp
    @includeIf('tekka.frontend.components.banners.section2', ['banner_2_imags' => $banner_2_imags])
@endif --}}






{{-- Feature product without slider
@if ($fp != null && $fp[0] == 1)
    @includeIf('tekka.frontend.components.feature_products.section', [
        'feature_products' => $feature_products1,
    ])
@endif
--}}
{{-- Feature product with slider--}}
{{-- <div class="d-none d-md-block">
    @if ($fp != null && $fp[0] == 1)
    @include('tekka.frontend.components.feature_products.slider_section', [
        'feature_products' => $feature_products2,
    ])
    @endif
</div> --}}

<!-- Shop By Category Section -->
{{-- <div class="d-none d-md-block">
    <div class="container custom-container">
        @if (get_setting('top10_categories') != null)
            @include('tekka.frontend.components.shop_by.category')
        @endif


    </div>
</div> --}}
<!-- container only for lg and hight breakpoints -->

{{-- Collection Design Two --}}
{{-- <div class="container custom-container px-0">
    @if (get_setting('home_categories') != null)
    @php
            $home_categories = json_decode(get_setting('home_categories'), true);
            // dd($home_categories);
        @endphp
        @foreach ($home_categories as $key => $value)
            @if ($value['did'] == 2)
                @php
                    $category = (object) $categories->where('id', $value['cid'])->first();
                    $design = (object) $collection_designs->where('id', $value['did'])->first();
                @endphp
                <section class="mb-3">
                    @include(config('app.theme') . 'frontend.components.collection_design.' . $design->file_name,
                        [
                            'category' => $category,
                        ]
                    )
                </section>
                @break
            @endif
        @endforeach
    @endif
</div> --}}
{{-- Collection Design Two End  --}}


{{-- Collection Design 3 --}}
{{-- <div class="container custom-container">
@if (get_setting('home_categories') != null)
    @php
        $home_categories = json_decode(get_setting('home_categories'), true);
        // dd($home_categories);
    @endphp
    @foreach ($home_categories as $key => $value)
        @if ($value['did'] == 3)
            @php
                $category = (object) $categories->where('id', $value['cid'])->first();
                $design = (object) $collection_designs->where('id', $value['did'])->first();
            @endphp
            <section class="mb-3">
                @include(config('app.theme') . 'frontend.components.collection_design.' . $design->file_name,
                    [
                        'category' => $category,
                    ]
                )
            </section>
        @break
    @endif
@endforeach
@endif
</div> --}}


{{-- Banner Section 3 --}}
{{-- @if (get_setting('home_banner3_images') != null)
@php $banner_3_imags = json_decode(get_setting('home_banner3_images')); @endphp
@includeIf('tekka.frontend.components.banners.section3', ['banner_3_imags' => $banner_3_imags])
@endif --}}




{{-- Collection Design 4 --}}
{{-- <div class="container custom-container">
@if (get_setting('home_categories') != null)
@php
    $home_categories = json_decode(get_setting('home_categories'), true);
    // dd($home_categories);
@endphp
@foreach ($home_categories as $key => $value)
    @if ($value['did'] == 4)
        @php
            $category = (object) $categories->where('id', $value['cid'])->first();
            $design = (object) $collection_designs->where('id', $value['did'])->first();
        @endphp
        <section class="mb-3">
            @include(config('app.theme') . 'frontend.components.collection_design.' . $design->file_name, [
                'category' => $category,
            ])
        </section>
    @break
@endif
@endforeach
@endif
</div> --}}


<!-- Shop By Brand Start -->
@if (@intval(json_decode(@get_setting('topten_brands_71'))) == 1)
    @includeIf('tekka.frontend.components.shop_by.brand')
@endif


{{-- Home ads banner 3
@if(json_decode(get_setting('home_adsbanner3_images')) != null)
@includeIf('tekka.frontend.components.home_ads_banner.banner3')
@endif --}}

{{-- Category wise Products --}}
{{-- <div id="section_home_categories" class = "categories-design-1">

</div> --}}








{{-- Featured Section --}}

<div id="section_featured">

</div>

{{-- Best Selling  --}}
{{-- <div id="section_best_selling"></div> --}}

<!-- Auction Product -->
@if (addon_is_activated('auction'))
<div id="auction_products"></div>
@endif




{{-- Classified Product --}}
@if (get_setting('classified_product') == 1)
@php
    $classified_products = \App\Models\CustomerProduct::where('status', '1')->where('published', '1')->take(10)->get();
@endphp
@includeIf('tekka.frontend.components.classified_product.section', [
    'classified_products' => $classified_products,
])
@endif




{{-- Best Seller --}}
<div id="section_best_sellers">

</div>



@endsection

@section('script')
<script>
    $(document).ready(function() {

        // @if (@intval(json_decode(@get_setting('featured_products_71'))) == 1)
        //     var startDig = 0;
        //     $(document).scroll(function() {
        //         if (startDig == 0) {
        //             startDig = 1;
        //             $.get('{{ route('home.section.featured') }}', {
        //                 _token: '{{ csrf_token() }}'
        //             }, function(data) {
        //                 $('#section_featured').html(data);
        //                 //AIZ.plugins.slickCarousel();
        //             });
        //         }
        //     });
        // @endif

        @if (@intval(json_decode(@get_setting('best_selling_products_71'))) == 1)
            $.post('{{ route('home.section.best_selling') }}', {
                _token: '{{ csrf_token() }}'
            }, function(data) {
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

        $.get('{{ route('home.section.home_categories') }}', function(data) {
            $('#section_home_categories').html(data);
            AIZ.plugins.slickCarousel();
        });

        @if (@intval(json_decode(@get_setting('best_sellers_71'))) == 1)
            $.post('{{ route('home.section.best_sellers') }}', {
                _token: '{{ csrf_token() }}'
            }, function(data) {
                $('#section_best_sellers').html(data);
                AIZ.plugins.slickCarousel();
            });
        @endif

    });


    /*Dropdown Menu*/
    $('.dropdown').click(function() {
        $(this).attr('tabindex', 1).focus();
        $(this).toggleClass('active');
        // $(this).find('.dropdown-menu').slideToggle(300);
    });
    $('.dropdown').focusout(function() {
        $(this).removeClass('active');
        // $(this).find('.dropdown-menu').slideUp(300);
    });
    $('.dropdown .dropdown-menu li').click(function() {
        $(this).parents('.dropdown').find('span').text($(this).text());
        $(this).parents('.dropdown').find('input').attr('value', $(this).attr('id'));
    });
    /*End Dropdown Menu*/


    $('.dropdown-menu li').click(function() {
        var input = '<strong>' + $(this).parents('.dropdown').find('input').val() + '</strong>',
            msg = '<span class="msg">Hidden input value: ';
        $('.msg').html(msg + input + '</span>');
    });
</script>
@endsection
