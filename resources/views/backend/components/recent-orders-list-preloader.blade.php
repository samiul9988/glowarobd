@foreach (range(0, 2) as $i)
<div class="card skeleton-loader">
    <div class="card-header">
        <div class="row w-100">
            <!-- Left side - Order info -->
            <div class="d-flex align-items-center flex-grow-1 col-7">
                <div class="mr-3" style="min-width: 200px;">
                    <div class="skeleton-line" style="width: 70%; height: 20px; margin-bottom: 8px;"></div>
                    <div class="skeleton-line" style="width: 50%; height: 16px;"></div>
                </div>
            </div>

            <!-- Middle - Badges -->
            <div class="d-flex col-3 px-0 flex-wrap align-items-center">
                <span class="skeleton-badge" style="width: 80px; height: 24px;"></span>
            </div>

            <!-- Right side - Action button -->
            <div class="d-flex col-2 px-0 justify-content-end">
                <div class="skeleton-circle" style="width: 32px; height: 32px;"></div>
            </div>
        </div>
    </div>
</div>
@endforeach
<style>
    .skeleton-loader {
        animation: shimmer 1.5s infinite linear;
        background: linear-gradient(to right, #f6f7f8 0%, #eaeaea 20%, #f6f7f8 40%, #f6f7f8 100%);
        background-size: 800px 104px;
        position: relative;
    }

    .skeleton-line {
        background-color: #e0e0e0;
        border-radius: 4px;
    }

    .skeleton-badge {
        background-color: #e0e0e0;
        border-radius: 12px;
        display: inline-block;
    }

    .skeleton-circle {
        background-color: #e0e0e0;
        border-radius: 50%;
    }

    @keyframes shimmer {
        0% {
            background-position: -400px 0;
        }
        100% {
            background-position: 400px 0;
        }
    }
</style>
