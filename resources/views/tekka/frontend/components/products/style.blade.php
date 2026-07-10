    <style>
        .rvsl-section {
            background: #fff;
            border-radius: 4px;
            margin-bottom: 12px;
        }

        .rvsl-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-block: 12px;
        }

        .rvsl-header-thumb {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .rvsl-header-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2029;
            margin: 0;
            line-height: 1.3;
        }

        .rvsl-slider {
            padding: 0 16px 16px;
        }

        .rvsl-card {
            position: relative;
            width: 100%;
            aspect-ratio: 9 / 16;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
        }

        .rvsl-card-thumb {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
            transition: opacity 0.3s ease;
        }

        .rvsl-card-video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        .rvsl-card:hover .rvsl-card-thumb {
            opacity: 0;
        }

        .rvsl-play-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            width: 40px;
            height: 40px;
            background: rgba(0, 0, 0, 0.45);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.25s ease, transform 0.25s ease;
            pointer-events: none;
        }

        .rvsl-play-icon i {
            color: #fff;
            font-size: 14px;
            margin-left: 3px;
        }

        .rvsl-card:hover .rvsl-play-icon {
            opacity: 0;
            transform: translate(-50%, -50%) scale(1.3);
        }

        .rvsl-vol-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            z-index: 3;
            width: 32px;
            height: 32px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease, opacity 0.25s ease;
            opacity: 0;
            pointer-events: none;
        }

        .rvsl-card:hover .rvsl-vol-btn {
            opacity: 1;
            pointer-events: auto;
        }

        .rvsl-vol-btn:hover {
            background: rgba(0, 0, 0, 0.7);
        }

        .rvsl-vol-btn i {
            color: #fff;
            font-size: 16px;
        }

        .rvsl-slide {
            width: 200px;
        }

        .swiper-wrapper {
            height: auto;
        }
    </style>
