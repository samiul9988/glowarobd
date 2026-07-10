@if (
    $flash_deal != null &&
        strtotime(date('Y-m-d H:i:s')) >= $flash_deal->start_date &&
        strtotime(date('Y-m-d H:i:s')) <= $flash_deal->end_date)
    <section class="mb-2 flash-sale__home-sec mt-3 mt-md-5"  style="background-image:url('{{static_asset('assets/img/black_sec_bg.png') }}')" >
        <div class="container custom-container">
            <div class="flash_sale__bg shadow-sm rounded section_holder overflow-hidden ">

                <div class="section_title_holder sale_title text-white">

                    <div class="flas-sale-title-wrap">
                        <h3 class="h5 fw-600">
                            <span class="border-bottom border-primary border-width-2  d-inline-block pb-2 pb-md-2 mb-1 mb-md-4">
                                {{ ($flash_deal->title) }}
                            </span>
                        </h3>
                        <!-- <span class="pb-5 pb-md-2 mb-3 d-inline-block fs-16 flas-subtitle"></span> -->
                    </div>

                    <div class="d-flex w-md-auto flash_sale_count">
                        <!-- <h6 class="d-flex text-center pt-2 ml-md-5 mv_full_width">Ending in:</h6> -->
                        <div class="aiz-count-down align-items-center mb-5"
                            data-date="{{ date('Y/m/d H:i:s', $flash_deal->end_date) }}"></div>
                    </div>

                    <!-- <a  class="mt-5 md ml-auto mr-0 btn btn-primary btn-sm shadow-md w-100 w-md-auto view_btn-ex"></a> -->
                    <div class="header-btn d-none d-md-block">
                        <a href="{{ route('flash-deal-details', $flash_deal->slug) }}"
                            class=" text-decoration-none text-white rounded-pill">{{ ('View More') }}</a>
                    </div>
                </div>

                @php
                    $flash_deal_products = collect($flash_deal->flash_deal_products)->take(20);
                @endphp

                <div class="aiz-carousel gutters-10 products_holder overflow-scroll-behav hide-scrollbar" data-items="4" data-xl-items="4" data-lg-items="3"
                    data-md-items="4" data-sm-items="2" data-xs-items="2" data-arrows='true'  data-unslick-sm="true">
                    @foreach (collect($flash_deal->flash_deal_products) as $key => $flash_deal_product)
                        container categories_sec
                        @if ($flash_deal_product->product != null && $flash_deal_product->product->published != 0)
                            <div class="carousel-box px-0 px-md-2">
                                @include(config('app.theme') . 'frontend.partials.product_box_1', [
                                    'product' => $flash_deal_product->product,
                                    'flash_deal_countdown' => false,
                                ])
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="header-btn d-block d-md-none pb-3 text-center">
                    <a class="mobile-btn text-white  border-white" href="{{ route('flash-deal-details', $flash_deal->slug) }}"
                        class=" text-decoration-none text-white rounded-pill">{{ ('View More') }}</a>
                </div>

            </div>
        </div>
    </section>
@endif
