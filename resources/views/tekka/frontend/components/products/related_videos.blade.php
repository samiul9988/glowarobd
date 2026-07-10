@if (isset($relatedVideos) && is_array($relatedVideos) && !empty($relatedVideos))
    @push('styles22')
        <link rel="stylesheet" href="{{ static_asset('assets/slider/swiper-bundle.min.css') }}">
        @include(config('app.theme') . 'frontend.components.products.style')
    @endpush

    <div class="rvsl-section">
        <div class="rvsl-header">
            <img src="{{ @$productThumb ?: static_asset('assets/img/placeholder.jpg') }}" alt="" class="rvsl-header-thumb"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
            <h3 class="rvsl-header-title">Experience The Product</h3>
        </div>

        <div class="swiper rvsl-slider">
            <div class="swiper-wrapper">
                @foreach ($relatedVideos as $video)
                    <div class="swiper-slide rvsl-slide">
                        <div class="rvsl-card" data-video-src="{{ $video['video_url'] ?? '' }}"
                            onmouseenter="rvslPlayVideo(this)" onmouseleave="rvslPauseVideo(this)">
                            <img class="rvsl-card-thumb"
                                src="{{ $video['thumbnail'] ?: static_asset('assets/img/placeholder.jpg') }}"
                                alt="{{ $video['title'] ?? '' }}"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                loading="lazy">
                            <video class="rvsl-card-video" muted playsinline preload="none" loop></video>
                            <button class="rvsl-vol-btn" onclick="event.stopPropagation(); rvslToggleMute(this)">
                                <i class="las la-volume-off"></i>
                            </button>
                            <div class="rvsl-play-icon">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ static_asset('assets/slider/swiper-bundle.min.js') }}"></script>
        @include(config('app.theme') . 'frontend.components.products.script')
    @endpush
@endif
