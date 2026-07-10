    <!-- Featured Products Slider Section Start -->
    <div class="featured-product-slider mt-md-5" style="background-image:url('{{static_asset('assets/img/black_sec_bg.png') }}')">
        <div class="container custom-container">
            <div class="d-none  heading d-md-flex flex-row align-items-center justify-content-center mb-4">
                <h3 class="fs-24 fs-sm-20  text-center font-weight-bolder mb-0 section-title">Featured Products</h3>
            </div>

            <div class="heading d-flex d-md-none justify-content-between align-items-center mb-4 px-3 px-md-0">
                <div class="d-flex align-items-center logo-and-title">
                    <div class="shop-by-category-logo">
                        <!-- Section Header icon -->
                        <img src="{{ static_asset('assets/img/layer1.png') }}" data-src="" alt=""
                            class="img-fluid img lazyload"
                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                    </div>

                    <!-- Section Title -->
                    <div class="shop-by-category-title">
                        <h3 class="text-white mb-0">Featured Products</h3>
                    </div>
                </div>

                <!-- Header Button -->
                <div class="header-btn">
                    <a class="text-decoration-none text-white rounded-pill mobile-btn border-white" href="{{ route('suggestion.search', ['search' => '&sort_by=featured']) }}">See All</a>
                </div>
            </div>

            <div class="aiz-carousel card-container mb-40 overflow-scroll-behav hide-scrollbar" data-items="6" data-xl-items="5" data-lg-items="4"
                data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows="true"  data-unslick-sm="true">
                @php
                    // dd($feature_products);
                @endphp
                    @foreach ($feature_products as $product)
                        <div class="carousel-box ">
                            @include(config('app.theme').'frontend.partials.product_box_1',['product' => $product])
                        </div>
                    @endforeach
            </div>
        </div>

    </div>


    <!-- Featured Products Slider Section End -->
