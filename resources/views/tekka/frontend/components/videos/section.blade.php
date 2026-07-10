@php
    // $jsonPath = resource_path('views/tekka/frontend/components/videos/playlists_with_videos_and_products.json');
    // $jsonContent = @file_get_contents($jsonPath);
    // $playlistsData = $jsonContent ? json_decode($jsonContent, true) : null;
    // $playlists = $playlistsData['data'] ?? [];

    // $playlistsData = (new \App\Http\Controllers\Api\V3\VideoPlaylistController)
    //         ->featuredPlaylists(request())
    //         ->response()
    //         ->getData(true);

    //     $playlists = $playlistsData['data'] ?? [];

    $videoUrlPrefix = get_setting('video_url', config('app.url'));
    $imageUrlPrefix = get_setting('file_url', config('app.url'));

    if (! str_ends_with($imageUrlPrefix, '/')) {
        $imageUrlPrefix .= '/';
    }

    if (! str_ends_with($videoUrlPrefix, '/')) {
        $videoUrlPrefix .= '/';
    }

    // dd($videoUrlPrefix, $imageUrlPrefix);
    $firstPlaylistId = $playlists[0]['id'] ?? null;
@endphp

@if (!empty($playlists))

    @push('styles22')
        <link rel="stylesheet" href="{{ static_asset('assets/slider/swiper-bundle.min.css') }}">
        @include('tekka.frontend.components.videos.style')
    @endpush

    <section class="vp-section mt-3 mt-md-5 mb-0">
        <div class="container custom-container" x-data="{
                activePlaylist: {{ $firstPlaylistId }},
                switching: false,
                selectPlaylist(id) {
                    if (this.activePlaylist === id || this.switching) return;
                    this.switching = true;
                    this.activePlaylist = id;
                    this.$nextTick(() => {
                        window.vpInitPlaylist(id);
                        setTimeout(() => { this.switching = false; }, 400);
                    });
                }
            }">

            <div class="vp-header">
                <div class="vp-header-icon">
                    <i class="las la-film"></i>
                </div>
                <h3>Real Routines, Real Results</h3>
            </div>

            <div class="vp-pills-wrapper">
                <div class="swiper vp-pills-swiper">
                    <div class="swiper-wrapper">
                        @foreach ($playlists as $playlist)
                            <div class="swiper-slide" style="width: auto;">
                                <button class="vp-pill"
                                    :class="activePlaylist == {{ $playlist['id'] }} ? 'vp-pill-active' : ''"
                                    @click="selectPlaylist({{ $playlist['id'] }})">
                                    {{ $playlist['title'] }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @foreach ($playlists as $index => $playlist)
                <div id="playlist-panel-{{ $playlist['id'] }}" x-show="activePlaylist == {{ $playlist['id'] }}"
                    x-transition:enter="vp-panel-enter-active"
                    x-transition:enter-start="vp-panel-enter-start"
                    x-transition:enter-end="vp-panel-enter-end"
                    x-transition:leave="vp-panel-leave-active"
                    x-transition:leave-start="vp-panel-leave-start"
                    x-transition:leave-end="vp-panel-leave-end"
                    class="vp-content-panel">

                    @if (!empty($playlist['videos']))
                        <div class="vp-videos-wrapper">
                            <div class="swiper vp-videos-swiper" data-playlist-id="{{ $playlist['id'] }}">
                                <div class="swiper-wrapper">
                                    @foreach ($playlist['videos'] as $video)
                                        <div class="swiper-slide">
                                            <div class="vp-video-card" @mouseenter="window.vpPlayVideo($el)"
                                                @mouseleave="window.vpPauseVideo($el)">

                                                <div class="vp-video-area">
                                                    <img src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        data-src="{{ $imageUrlPrefix . $video['thumbnail'] }}"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                        alt="{{ $video['title'] }}" class="vp-video-thumb lazyload">
                                                    <video class="vp-video-player" muted loop playsinline preload="none"
                                                        src="{{ $videoUrlPrefix . $video['video_url'] }}"
                                                        poster="{{ $imageUrlPrefix . $video['thumbnail'] }}"></video>
                                                    <div class="vp-play-icon">
                                                        <i class="las la-play"></i>
                                                    </div>
                                                    <button class="vp-mute-btn"
                                                        onclick="event.stopPropagation(); window.vpToggleMute(this);">
                                                        <i class="las la-volume-off"></i>
                                                    </button>
                                                </div>

                                                @if (!empty($video['products']))
                                                    <div class="vp-products-wrapper">
                                                        <div class="vp-products-inner">
                                                            <div class="swiper vp-products-swiper">
                                                                <div class="swiper-wrapper">
                                                                    @foreach ($video['products'] as $product)
                                                                        <div class="swiper-slide">
                                                                            <a href="{{ route('product', $product['slug']) }}"
                                                                                class="vp-product-card">
                                                                                <div class="vp-product-thumb">
                                                                                    <img src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                                                        data-src="{{ $imageUrlPrefix . $product['thumbnail_image'] }}"
                                                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                                                        alt="{{ $product['name'] }}"
                                                                                        class="lazyload">
                                                                                </div>
                                                                                <div class="vp-product-info">
                                                                                    <div class="vp-product-name">
                                                                                        {{ $product['name'] }}</div>
                                                                                    <div class="vp-product-prices">
                                                                                        <span
                                                                                            class="vp-price-discounted">{{ $product['formatted_base_discounted_price'] }}</span>
                                                                                        @if ($product['base_price'] != $product['base_discounted_price'])
                                                                                            <del
                                                                                                class="vp-price-original">{{ $product['formatted_base_price'] }}</del>
                                                                                        @endif
                                                                                        {{-- @if ($product['save'] > 0)
                                                                                            <div class="vp-product-save">
                                                                                                Save
                                                                                                {{ $product['currency'] }}{{ number_format(intval($product['save'])) }}
                                                                                            </div>
                                                                                        @endif --}}
                                                                                    </div>
                                                                                </div>
                                                                            </a>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="vp-slider-indicator" data-product-indicator></div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="vp-swiper-nav vp-swiper-prev" data-playlist-nav="{{ $playlist['id'] }}">
                                <i class="las la-chevron-left"></i>
                            </div>
                            <div class="vp-swiper-nav vp-swiper-next" data-playlist-nav="{{ $playlist['id'] }}">
                                <i class="las la-chevron-right"></i>
                            </div>
                        </div>
                    @else
                        <div class="vp-videos-empty">
                            <div class="vp-empty-icon">
                                <i class="las la-video"></i>
                            </div>
                            <p class="vp-empty-message">No videos available in this playlist yet.</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    @push('scripts')
        <script src="{{ static_asset('assets/slider/swiper-bundle.min.js') }}"></script>
        @include('tekka.frontend.components.videos.script')
    @endpush
@endif
