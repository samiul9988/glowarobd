<style>
    .vp-section {
        background: #fff;
        padding-bottom: 20px;
    }

    .vp-section .container {
        max-width: 100%;
        padding: 0;
    }

    .vp-header {
        display: flex;
        align-items: center;
        /* padding: 0 40px; */
        margin-bottom: 16px;
    }

    .vp-header-icon {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        font-size: 24px;
        color: #f06292;
        border-radius: 6px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .vp-header-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .vp-header h3 {
        font-weight: 700;
        font-size: 24px;
        margin: 0;
        color: #1a1a2e;
    }

    @media (max-width: 575.98px) {
        .vp-header {
            padding: 0 16px;
        }

        .vp-header h3 {
            font-size: 20px;
        }
    }

    .vp-pills-wrapper {
        padding: 0 40px;
        margin-bottom: 24px;
        overflow: hidden;
    }

    @media (max-width: 575.98px) {
        .vp-pills-wrapper {
            padding: 0 16px;
        }
    }

    .vp-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 20px;
        border-radius: 50px;
        border: 1.5px solid #e0e0e0;
        background: #f5f5f5;
        color: #555;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
        outline: none;
    }

    .vp-pill:hover {
        background: #eeeeee;
        border-color: #ccc;
    }

    .vp-pill-active {
        background: linear-gradient(135deg, #f06292, #ec407a);
        color: #fff;
        border-color: transparent;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(240, 98, 146, 0.35);
    }

    .vp-pill-active:hover {
        background: linear-gradient(135deg, #ec407a, #e91e63);
    }

    .vp-content-panel {
        padding: 0 0 20px;
    }

    .vp-panel-enter-active {
        transition: opacity 0.35s ease-out, transform 0.35s ease-out;
    }

    .vp-panel-enter-start {
        opacity: 0;
        transform: translateY(12px);
    }

    .vp-panel-enter-end {
        opacity: 1;
        transform: translateY(0);
    }

    .vp-panel-leave-active {
        transition: opacity 0.2s ease-in, transform 0.2s ease-in;
    }

    .vp-panel-leave-start {
        opacity: 1;
        transform: translateY(0);
    }

    .vp-panel-leave-end {
        opacity: 0;
        transform: translateY(-8px);
    }

    .vp-videos-wrapper {
        position: relative;
        padding: 0 44px;
    }

    @media (max-width: 575.98px) {
        .vp-videos-wrapper {
            padding: 0 12px;
        }
    }

    .vp-swiper-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        transition: all 0.3s ease;
        border: none;
        font-size: 18px;
        color: #333;
    }

    @media (max-width: 767.98px) {
        .vp-swiper-nav {
            display: none;
        }
    }

    .vp-swiper-nav:hover {
        background: #f06292;
        color: #fff;
        box-shadow: 0 4px 14px rgba(240, 98, 146, 0.4);
    }

    .vp-swiper-nav.swiper-button-disabled {
        opacity: 0.35;
        cursor: default;
        pointer-events: none;
    }

    .vp-swiper-prev {
        left: 0;
    }

    .vp-swiper-next {
        right: 0;
    }

    .vp-video-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.08);
        transition: box-shadow 0.3s ease;
        margin: 4px 0;
        display: flex;
        flex-direction: column;
    }

    .vp-video-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.14);
    }

    .vp-video-area {
        position: relative;
        width: 100%;
        padding-top: 177.78%;
        overflow: hidden;
        background: #000;
        cursor: pointer;
    }

    .vp-video-thumb {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 2;
        transition: opacity 0.4s ease;
    }

    .vp-video-player {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 1;
    }

    .vp-play-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 3;
        width: 50px;
        height: 50px;
        background: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.4s ease, transform 0.3s ease;
        pointer-events: none;
    }

    .vp-play-icon i {
        color: #fff;
        font-size: 22px;
        margin-left: 3px;
    }

    .vp-video-card:hover .vp-play-icon {
        opacity: 0;
        transform: translate(-50%, -50%) scale(1.2);
    }

    .vp-video-card:hover .vp-video-thumb {
        opacity: 0;
    }

    .vp-mute-btn {
        position: absolute;
        bottom: 8px;
        right: 8px;
        z-index: 5;
        width: 32px;
        height: 32px;
        background: rgba(0, 0, 0, 0.55);
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s ease, opacity 0.3s ease;
        opacity: 0;
        padding: 0;
    }

    .vp-mute-btn i {
        color: #fff;
        font-size: 16px;
    }

    .vp-video-area:hover .vp-mute-btn {
        opacity: 1;
    }

    .vp-mute-btn:hover {
        background: rgba(0, 0, 0, 0.8);
    }

    .vp-video-title-link {
        display: block;
        text-decoration: none;
        color: inherit;
    }

    .vp-video-title {
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
        padding: 10px 12px 6px;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        color: #1a1a2e;
        transition: color 0.2s ease;
    }

    .vp-video-title-link:hover .vp-video-title {
        color: #f06292;
    }

    .vp-products-wrapper {
        /* padding: 0 10px 10px; */
        position: relative;
        padding-top: 10px;
        background: #f9f9f9;
        flex-shrink: 0;
    }

    .vp-products-inner {
        position: relative;
        height: 94px;
        overflow: hidden;
    }

    .vp-products-inner .swiper-slide {
        height: 88px;
    }

    .vp-products-swiper {
        height: 88px;
    }

    .vp-slider-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 6px 0 2px;
        min-height: 16px;
    }

    .vp-slider-dot {
        display: block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #ccc;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .vp-slider-dot:hover {
        background: #aaa;
    }

    .vp-slider-dot-active {
        background: #f06292 !important;
        width: 16px !important;
        height: 6px;
        border-radius: 3px;
    }

    .vp-product-card {
        display: flex;
        align-items: flex-start;
        text-decoration: none;
        color: #333;
        background: #fafafa;
        border-radius: 8px;
        padding: 8px;
        transition: background 0.2s ease, box-shadow 0.2s ease;
        gap: 8px;
        height: 100%;
        box-sizing: border-box;
    }

    .vp-product-card:hover {
        background: #f0f0f0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    }

    .vp-product-thumb {
        flex-shrink: 0;
        width: 52px;
        height: 52px;
        border-radius: 6px;
        overflow: hidden;
        background: #eee;
    }

    .vp-product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .vp-product-info {
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }

    .vp-product-name {
        font-size: 12px;
        font-weight: 500;
        line-height: 1.35;
        color: #333;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 3px;
    }

    .vp-product-prices {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .vp-price-discounted {
        font-size: 13px;
        font-weight: 700;
        color: #1a1a2e;
    }

    .vp-price-original {
        font-size: 11px;
        color: #999;
    }

    .vp-product-save {
        display: inline-block;
        font-size: 10px;
        font-weight: 600;
        color: #28a745;
        background: #e8f5e9;
        padding: 1px 6px;
        border-radius: 4px;
        line-height: 1.4;
        margin-top: 2px;
    }

    .vp-videos-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 200px;
        padding: 40px 20px;
    }

    .vp-empty-icon {
        font-size: 48px;
        color: #ddd;
        margin-bottom: 12px;
    }

    .vp-empty-message {
        margin: 0;
        font-size: 14px;
        color: #999;
        font-weight: 500;
    }
</style>
