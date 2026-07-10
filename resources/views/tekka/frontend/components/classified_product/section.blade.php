@if (count($classified_products) > 0)
    <section class="mb-4">
        <div class="container">
            <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                <div class="d-flex mb-3 align-items-baseline border-bottom">
                    <h3 class="h5 fw-700 mb-0">
                        <span
                            class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ ('Classified Ads') }}</span>
                    </h3>
                    <a href="{{ route('customer.products') }}"
                        class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ ('View More') }}</a>
                </div>
                <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"
                    data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                    @foreach ($classified_products as $key => $classified_product)
                        <div class="carousel-box">
                            <div class="aiz-card-box border border-light rounded hov-shadow-md my-2 has-transition">
                                <div class="position-relative">
                                    <a href="{{ route('customer.product', $classified_product->slug) }}"
                                        class="d-block">
                                        <img class="img-fit lazyload mx-auto h-140px h-md-210px"
                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                            data-src="{{ uploaded_asset($classified_product->thumbnail_img) }}"
                                            alt="{{ $classified_product->getTranslation('name') }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                    </a>
                                    <div class="absolute-top-left pt-2 pl-2">
                                        @if ($classified_product->conditon == 'new')
                                            <span class="badge badge-inline badge-success">{{ ('new') }}</span>
                                        @elseif($classified_product->conditon == 'used')
                                            <span class="badge badge-inline badge-danger">{{ ('Used') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="p-md-3 p-2 text-left">
                                    <div class="fs-15 mb-1">
                                        <span
                                            class="fw-700 text-primary">{{ single_price($classified_product->unit_price) }}</span>
                                    </div>
                                    <h3 class="fw-600 fs-13 text-truncate-2 lh-1-4 mb-0 h-35px">
                                        <a href="{{ route('customer.product', $classified_product->slug) }}"
                                            class="d-block text-reset">{{ $classified_product->getTranslation('name') }}</a>
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
