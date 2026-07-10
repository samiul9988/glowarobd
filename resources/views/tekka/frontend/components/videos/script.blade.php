<script>
    (function() {
        var videoSwipers = {};
        var productSwipers = {};
        var initializedPlaylists = {};

        function updateIndicator(indicatorEl, activeIndex, total) {
            if (!indicatorEl) return;
            var dots = indicatorEl.querySelectorAll('.vp-slider-dot');
            dots.forEach(function(dot, i) {
                if (i === activeIndex) {
                    dot.classList.add('vp-slider-dot-active');
                } else {
                    dot.classList.remove('vp-slider-dot-active');
                }
            });
        }

        function buildIndicator(indicatorEl, totalSlides, swiperInst, isLoop) {
            if (!indicatorEl) return;
            indicatorEl.innerHTML = '';
            if (totalSlides <= 1) {
                var singleDot = document.createElement('span');
                singleDot.className = 'vp-slider-dot vp-slider-dot-active';
                indicatorEl.appendChild(singleDot);
                return;
            }
            for (var i = 0; i < totalSlides; i++) {
                (function(idx) {
                    var dot = document.createElement('span');
                    dot.className = 'vp-slider-dot' + (idx === 0 ? ' vp-slider-dot-active' : '');
                    dot.addEventListener('click', function() {
                        if (isLoop) {
                            swiperInst.slideToLoop(idx);
                        } else {
                            swiperInst.slideTo(idx);
                        }
                    });
                    indicatorEl.appendChild(dot);
                })(i);
            }
        }

        function initPlaylistSwipers(playlistId) {
            var prefix = playlistId + '-product-';
            if (initializedPlaylists[playlistId]) {
                if (videoSwipers[playlistId]) {
                    videoSwipers[playlistId].update();
                }
                Object.keys(productSwipers).forEach(function(key) {
                    var s = productSwipers[key];
                    if (key.indexOf(prefix) === 0) {
                        s.update();
                        if (s.params.autoplay && s.autoplay) {
                            s.autoplay.stop();
                        }
                    }
                });
                setTimeout(function() {
                    Object.keys(productSwipers).forEach(function(key) {
                        var s = productSwipers[key];
                        if (key.indexOf(prefix) === 0) {
                            if (s.params.autoplay && s.autoplay) {
                                s.autoplay.start();
                            }
                        }
                    });
                }, 50);
                setTimeout(function() {
                    if (videoSwipers[playlistId]) {
                        videoSwipers[playlistId].update();
                    }
                }, 400);
                return;
            }

            var panel = document.getElementById('playlist-panel-' + playlistId);
            if (!panel) return;

            var videoSwiperEl = panel.querySelector('.vp-videos-swiper');
            var prevBtn = panel.querySelector('.vp-swiper-prev[data-playlist-nav="' + playlistId + '"]');
            var nextBtn = panel.querySelector('.vp-swiper-next[data-playlist-nav="' + playlistId + '"]');

            if (videoSwiperEl) {
                videoSwipers[playlistId] = new Swiper(videoSwiperEl, {
                    slidesPerView: 1.6,
                    spaceBetween: 16,
                    freeMode: false,
                    navigation: {
                        prevEl: prevBtn,
                        nextEl: nextBtn,
                    },
                    breakpoints: {
                        576: { slidesPerView: 1.6, spaceBetween: 16 },
                        768: { slidesPerView: 2.3, spaceBetween: 16 },
                        992: { slidesPerView: 3, spaceBetween: 18 },
                        1200: { slidesPerView: 4.5, spaceBetween: 20 },
                    },
                });
            }

            panel.querySelectorAll('.vp-products-swiper').forEach(function(el, index) {
                var key = playlistId + '-product-' + index;
                var slideCount = el.querySelectorAll('.swiper-slide').length;
                var indicatorEl = el.closest('.vp-products-wrapper').querySelector(
                    '[data-product-indicator]');
                var shouldLoop = slideCount > 1;

                var swiperInst = new Swiper(el, {
                    direction: 'vertical',
                    slidesPerView: 1,
                    spaceBetween: 6,
                    autoplay: shouldLoop ? {
                        delay: 3000,
                        disableOnInteraction: false,
                    } : false,
                    loop: shouldLoop,
                    loopedSlides: shouldLoop ? Math.min(slideCount, 3) : undefined,
                    allowTouchMove: true,
                    mousewheel: false,
                    on: {
                        slideChange: function() {
                            if (indicatorEl) {
                                updateIndicator(indicatorEl, this.realIndex, slideCount);
                            }
                        },
                    },
                });

                productSwipers[key] = swiperInst;

                if (indicatorEl) {
                    buildIndicator(indicatorEl, slideCount, swiperInst, shouldLoop);
                }
            });

            initializedPlaylists[playlistId] = true;

            setTimeout(function() {
                if (videoSwipers[playlistId]) {
                    videoSwipers[playlistId].update();
                }
            }, 400);
        }

        function playVideo(card) {
            var video = card.querySelector('.vp-video-player');
            var thumb = card.querySelector('.vp-video-thumb');
            if (video) {
                video.currentTime = 0;
                video.play().catch(function() {});
            }
            if (thumb) {
                thumb.style.opacity = '0';
            }
        }

        function pauseVideo(card) {
            var video = card.querySelector('.vp-video-player');
            var thumb = card.querySelector('.vp-video-thumb');
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
            if (thumb) {
                thumb.style.opacity = '1';
            }
        }

        function toggleMute(btn) {
            var videoArea = btn.closest('.vp-video-area');
            if (!videoArea) return;
            var video = videoArea.querySelector('.vp-video-player');
            if (!video) return;
            var icon = btn.querySelector('i');
            if (video.muted) {
                video.muted = false;
                if (icon) {
                    icon.className = 'las la-volume-up';
                }
            } else {
                video.muted = true;
                if (icon) {
                    icon.className = 'las la-volume-off';
                }
            }
        }

        window.vpInitPlaylist = initPlaylistSwipers;
        window.vpPlayVideo = playVideo;
        window.vpPauseVideo = pauseVideo;
        window.vpToggleMute = toggleMute;

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swiper === 'undefined') return;

            var pillsSwiper = new Swiper('.vp-pills-swiper', {
                slidesPerView: 'auto',
                freeMode: true,
                spaceBetween: 10,
                grabCursor: true,
            });

            var firstId = {{ $firstPlaylistId ?? 'null' }};
            if (firstId) {
                initPlaylistSwipers(firstId);
            }
        });
    })();
</script>
