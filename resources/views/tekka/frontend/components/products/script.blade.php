<script>
    function rvslPlayVideo(card) {
        var video = card.querySelector('.rvsl-card-video');
        var src = card.getAttribute('data-video-src');
        if (!video) return;
        if (src && !video.src) {
            video.src = src;
            video.load();
        }
        video.play().catch(function() {});
    }

    function rvslPauseVideo(card) {
        var video = card.querySelector('.rvsl-card-video');
        if (!video) return;
        video.pause();
        video.currentTime = 0;
    }

    function rvslToggleMute(btn) {
        var card = btn.closest('.rvsl-card');
        if (!card) return;
        var video = card.querySelector('.rvsl-card-video');
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

    document.addEventListener('DOMContentLoaded', function() {
        new Swiper('.rvsl-slider', {
            slidesPerView: 1.6,
            spaceBetween: 12,
            freeMode: true,
            grabCursor: true,
            breakpoints: {
                576: {
                    slidesPerView: 1.8,
                    spaceBetween: 12
                },
                768: {
                    slidesPerView: 2.6,
                    spaceBetween: 12
                },
                992: {
                    slidesPerView: 3.6,
                    spaceBetween: 12
                },
                1200: {
                    slidesPerView: 4.6,
                    spaceBetween: 12
                },
            },
        });
    });
</script>
