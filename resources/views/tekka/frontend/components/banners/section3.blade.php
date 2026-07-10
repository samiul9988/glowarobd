@if (count($banner_3_imags) > 0)
    <div class="mb-0 mb-md-4 mt-0">
        <div class="container px-0 px-md-3">
            <div class="banner-bottom overflow-scroll-behav hide-scrollbar overflow-x-md-scroll" >
                @foreach ($banner_3_imags as $key => $value)
                    <div class="col-xl col-10 col-md-6 px-0 banner-box">
                        <div class="mb-3 mb-lg-0">
                            <a href="{{ json_decode(get_setting('home_banner3_links'), true)[$key] }}"
                                class="d-block text-reset">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                    data-src="{{ uploaded_asset($banner_3_imags[$key]) }}"
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
