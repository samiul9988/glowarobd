@php
    $width = "<script>document.write(screen.width); </script>";
    $banner_1_imags = json_decode(get_setting('home_banner1_images'));
@endphp

@if ($width > 768)
<div class="mt-md-4 container mb-2 md_moblie_0 banner1-design custom-container {{ $width }}" >
    <div class=" p-md-0 ">
        <div class="banners">
            @foreach ($banner_1_imags as $key => $value)
                <div class="col-xl col-10 col-md-6 px-0 banner-box">
                    <div class="mb-2 mb-lg-0 ">
                        <a href="{{ json_decode(get_setting('home_banner1_links'), true)[$key] }}"
                            class="d-block text-reset">
                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} promo"
                                class="img-fluid lazyload w-100"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@else
<div class="mb-2 md_moblie_0 banner1-design {{ $width }}" >
    <div class="p-md-0 ">
        <div class="banners">
            @foreach ($banner_1_imags as $key => $value)
                <div class="col-xl col-10 col-md-6 px-0">
                    <div class="mb-2 mb-lg-0">
                        <a href="{{ json_decode(get_setting('home_banner1_links'), true)[$key] }}"
                            class="d-block text-reset">
                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} promo"
                                class="img-fluid lazyload w-100"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

