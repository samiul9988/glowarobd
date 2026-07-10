@if (count($banner_2_imags) > 0)
    <div class="container mt-0  mt-md-5">
        <div class="">
            <div class="banner-section-two row gutters-10">

                @foreach ($banner_2_imags as $key => $value)
                    <div class="col-12">
                        <div class="mb-2 mb-lg-0">
                            <a href="{{ json_decode(get_setting('home_banner2_links'), true)[$key] }}"
                                class="d-block text-reset">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                    data-src="{{ uploaded_asset($banner_2_imags[$key]) }}"
                                    alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
