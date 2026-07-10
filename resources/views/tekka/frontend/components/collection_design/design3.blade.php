@php
    $icon = json_decode($category->icon, true);
    $bgImage = json_decode($category->bg_image, true);
    // dd($category);
@endphp
<div class="container mt-3 mt-md-5 custom-container px-0" style="background-image:url('{{ uploaded_asset($agent->isMobile() ? ($bgImage['mobile'] ?? '') : ($bgImage['web'] ?? '')) }}')">
    <div class="collection-design-three container custom-container px-md-0 padding-inline-40">
        <div class="heading d-flex flex-row align-items-center justify-content-between mb-3 mb-md-4 mt-3 mt-md-4">
            <div class="icon-and-title d-flex align-items-center ">
                <div class="icon">
                    <img src="{{ static_asset('assets/img/frame.png') }}" data-src="{{ uploaded_asset($agent->isMobile() ? ($icon['mobile'] ?? '') : ($icon['web'] ?? '')) }}" width="36px" height="36px"
                        alt="" class="img-fluid img lazyload"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                </div>

                <h3 class="fs-24 fs-sm-20  font-weight-bolder mb-0">{{ $category->name }}</h3>
            </div>

            <div class="">
                <a class="heading-btn rounded-pill mobile-btn" href="{{ route('products.category', $category->slug) }}">See all</a>
            </div>
        </div>


        <div class="card-container mb-40 overflow-scroll-behav hide-scrollbar">

            @foreach (get_cached_products($category->id, @$type)->slice(0, 12) as $key => $product)
                <div class="carousel-box px-0 ">

                    @include(config('app.theme') . 'frontend.partials.product_box_1', [
                        'product' => $product,
                    ])

                </div>
            @endforeach

            {{-- <div class="carousel-box">
                <div class="aiz-card-box hov-shadow-md mt-1 mb-1 has-transition bg-white product_box h-100">
                    <div class="position-absolute z-1 d-flex flex-column mt-3">

                    </div>

                    <div class="position-relative p-2 p-md-2">
                        <a href="http://localhost/e71/pure-vitamin-c-v10-cleansing-bar-some-by-mi.html"
                            class="d-flex align-items-center">
                            <img class="img-fit card-image mx-auto product_long_grid lazyloaded"
                                src="https://s3-alpha-sig.figma.com/img/a6ff/316f/423f5b8f19d67c53e273ddfe084465a6?Expires=1712534400&Key-Pair-Id=APKAQ4GOSFWCVNEHN3O4&Signature=m9yh~4-Omm2ZkTuhSTag7MC4IpEunRFaaj-xwfeXZRMn2kXjNoK1DIMFPNTV~jNkjxTfbhbbJcJB5tdYiXdohP9rNQXH~JGEa3RnrMUpWgwuuf9iX~4bxAk2qLAUy6fkUqaK4Lme3Mj6SXz8y0j60PtIEOPH8sIDR56uOb9QfmiIJ~YowGU22LK-uJq9~tdv3YUePM2tRbLinvd-mjGONttXEjw~528VFTyFaorypOAlBCtXz3pLgl5Uv6vwN771ej9pK199IQyFZD83GEdfxsqLr4KUDMrSn4UJ2bLNRGh9GVyYy2B451H8f7aQ39lA9LyZ7n287P6-bxB-BUdMzw__"
                                data-src="http://localhost/e71/public/uploads/all/9SBbuW5sLGs81wZyLJE9pgMrY4dVocwFXShrqjg4.jpg"
                                alt="SOME BY MI Pure Vitamin C V10 Cleansing Bar"
                                onerror="this.onerror=null;this.src='http://localhost/e71/public/assets/img/placeholder.jpg';">
                        </a>
                    </div>

                    <div class="  pb-2 px-md-2 pt-2 product_info">
                        <h3 class="fw-400 fs-16 text-truncate-2 lh-1-4 mb-3 ">
                            <a href="http://localhost/e71/pure-vitamin-c-v10-cleansing-bar-some-by-mi.html"
                                class="d-block text-reset">Xiaomi Redmi V22FAB-RA FHD Monitor - 21.45 Inch</a>
                        </h3>
                        <div class="rating rating-sm mb-1">
                            <div>
                                <i class="las la-star fs-10"></i><i class="las la-star fs-10"></i><i
                                    class="las la-star fs-10"></i><i class="las la-star fs-10"></i><i
                                    class="las la-star fs-10"></i>
                            </div>
                            <p class="fs-14">(24)</p>
                        </div>

                        <div class="fs-14 product-price">
                            <span class="fw-700">৳14,440</span>
                        </div>

                    </div>
                </div>
            </div> --}}

        </div>





    </div>
</div>
