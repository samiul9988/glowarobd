<style>
    /* Module Shortcuts Preloader */
    .module-shortcuts.preloader {
        position: relative;
        background: #f8f9fa;
        border-radius: 4px;
        overflow: hidden;
    }

    /* Shimmer animation for the entire card */
    .module-shortcuts.preloader::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
        animation: module-shortcuts-loading 1.5s infinite;
    }

    /* Text placeholder animation */
    .module-shortcuts .title-placeholder {
        width: 180px;
        height: 24px;
        background: #e9ecef;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
    }

    .module-shortcuts .title-placeholder::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 50%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
        animation: title-shimmer 2s infinite;
    }

    @keyframes module-shortcuts-loading {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    @keyframes title-shimmer {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(200%);
        }
    }

    .module-shortcuts .shortcut-placeholder {
        height: 98px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .module-shortcuts .icon-placeholder {
        width: 30px;
        height: 30px;
        background: #e9ecef;
        border-radius: 50%;
        margin-bottom: 10px;
        position: relative;
        overflow: hidden;
    }

    .module-shortcuts .icon-placeholder::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 50%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
        animation: icon-shimmer 2s infinite;
    }

    .module-shortcuts .text-placeholder {
        width: 80%;
        height: 16px;
        background: #e9ecef;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
    }

    .module-shortcuts .text-placeholder::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 50%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
        animation: text-shimmer 2s infinite;
    }

    @keyframes icon-shimmer {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(200%);
        }
    }

    @keyframes text-shimmer {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(200%);
        }
    }
</style>
<div class="row">
    <div class="col-12">
        <div class="card shadow-md module-shortcuts preloader">
            <div class="card-header">
                <h5 class="mb-0 h6 d-flex align-items-center">
                    <span class="title-placeholder ms-2"></span>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="card shadow-none bg-light">
                            <div class="shortcut-placeholder">
                                <div class="icon-placeholder"></div>
                                <div class="text-placeholder"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card shadow-none bg-light">
                            <div class="shortcut-placeholder">
                                <div class="icon-placeholder"></div>
                                <div class="text-placeholder"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card shadow-none bg-light">
                            <div class="shortcut-placeholder">
                                <div class="icon-placeholder"></div>
                                <div class="text-placeholder"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card shadow-none bg-light">
                            <div class="shortcut-placeholder">
                                <div class="icon-placeholder"></div>
                                <div class="text-placeholder"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card shadow-none bg-light">
                            <div class="shortcut-placeholder">
                                <div class="icon-placeholder"></div>
                                <div class="text-placeholder"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card shadow-none bg-light">
                            <div class="shortcut-placeholder">
                                <div class="icon-placeholder"></div>
                                <div class="text-placeholder"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
