@if (get_setting('top10_brands') != null)
    <div class="shop-by-brand container custom-container mt-md-5">
        <div class="heading d-flex flex-row align-items-center justify-content-between mb-2 mt-4 mb-md-4">
            <div class="icon-and-title d-flex align-items-center ">
                <div class="icon">
                    <img src="{{ static_asset('assets/img/spade.png') }}" data-src="" width="36px" height="36px"
                        alt="" class="img-fluid img lazyload"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                </div>

                <h3 class="fs-24 fs-sm-20  font-weight-bolder mb-0">Shop By Brand</h3>
            </div>

            <div class="">
                <a class="heading-btn rounded-pill mobile-btn" href="{{ route('brands.all') }}">See all</a>
            </div>
        </div>

        <div class="card-container mb-40 mb-md-2 overflow-scroll-behav hide-scrollbar">

            @php $top10_brands = json_decode(get_setting('top10_brands')); @endphp
            @foreach ($top10_brands as $key => $value)
                @php $brand = \App\Models\Brand::find($value); @endphp
                @if ($brand != null)
                    <div class="carousel-box">
                        <div class="aiz-card-box hov-shadow-md mt-1 mb-1 has-transition bg-white product_box h-100">
                            <div class="position-relative p-2 p-md-2 img-box">
                                <a href="{{ route('products.brand', $brand->slug) }}" class="d-flex align-items-center justify-content-center">
                                        <div class="shop-by-brand-wrap">
                                            <img class="img-fit card-image mx-auto product_long_grid lazyloaded"
                                            src="{{ uploaded_asset($brand->logo) }}"
                                            data-src="{{ uploaded_asset($brand->logo) }}"
                                            alt="{{ $brand->getTranslation('name') }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                        </div>
                                    <h4 class="brand-title text-truncate-2 d-block text-reset text-center fw-500">
                                        {{ $brand->getTranslation('name') }}
                                    </h4>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

        </div>
    </div>
@endif
