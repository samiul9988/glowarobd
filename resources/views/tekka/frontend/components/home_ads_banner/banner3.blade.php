@php
    // dd(json_decode(get_setting('home_adsbanner3_images'), true));
@endphp
<div class="container mb-4 {{ Route::is('product') ? 'px-0' : '' }}">
    <div class="banner-wrapper-bottom">
            @foreach (json_decode(get_setting('home_adsbanner3_images'), true) as $imageId)
                <div class="banner-box">
                    <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset($imageId) }}"
                        alt="" class="img-fluid img lazyload"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                </div>
            @endforeach
        </div>
    </div>
