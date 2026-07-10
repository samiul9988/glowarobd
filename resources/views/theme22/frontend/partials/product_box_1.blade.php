@php
    $info = get_copy_content($product);
    $discount = discount_in_percentage($product);
    $basePrice = home_base_price($product);
    $discountedPrice = home_discounted_base_price($product);
    $isWholesale = $product->wholesale_product;
    $flashDeal = $product->flash_deal_product?->flash_deals;
    $isValidFlashDeal = is_valid_flashdeal($flashDeal);
    $endDate = $flashDeal->end_date ?? strtotime(date('Y-m-d'));
    $thisStocks = collect($product->stocks);
    $stockQuantity = $thisStocks->first()->qty ?? 0;

    if ($isValidFlashDeal) {
        $stockQuantity = $product->flash_deal_product->quantity ?? 0;
    }
@endphp

<div class="aiz-card-box border border-light rounded hov-shadow-md mt-1 mb-1 has-transition bg-white product_box">
    @if($discount > 0)
        <div class="ribbon"><span class="ribbon4">{{ ('OFF') }}&nbsp;{{$discount}}%</span></div>
    @endif
    <div class="position-relative">
        <a href="{{ route('product', $product->slug) }}" class="d-block">
            <img
                class="img-fit lazyload mx-auto h-180px h-md-230px product_long_grid"
                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                data-src="{{ !empty($product->thumbnail_image) ? my_asset($product->thumbnail_image->file_name) : uploaded_asset($product->thumbnail_img) }}"
                alt="{{ $product->name }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
            >
        </a>
        @if ($isWholesale)
            <span class="absolute-bottom-left fs-11 text-white fw-600 px-2 lh-1-8" style="background-color: #455a64">
                {{ ('Wholesale') }}
            </span>
        @endif
        <div class="absolute-top-right aiz-p-hov-icon">
            <a href="javascript:void(0)" onclick="addToWishList({{ $product->id }})" data-toggle="tooltip" data-title="{{ ('Add to wishlist') }}" data-placement="left">
                <i class="la la-heart-o"></i>
            </a>
            <a href="javascript:void(0)" onclick="addToCompare({{ $product->id }})" data-toggle="tooltip" data-title="{{ ('Add to compare') }}" data-placement="left">
                <i class="las la-sync"></i>
            </a>
            <a href="javascript:void(0)" onclick="showAddToCartModal({{ $product->id }})" data-toggle="tooltip" data-title="{{ ('Add to cart') }}" data-placement="left">
                <i class="las la-shopping-cart"></i>
            </a>
        </div>
    </div>
    <div class=" pb-md-4 p-2 pb-3 px-md-2 pt-2 text-center product_info">
        <h3 class="fw-700 fs-13 text-truncate-2 lh-1-4 mb-2 h-35px">
            <a href="{{ route('product', $product->slug) }}" class="d-block text-reset">{{  $product->name }}</a>
        </h3>
        @if (addon_is_activated('club_point'))
            <div class="rounded px-2 mt-3 bg-soft-primary border-soft-primary border">
                {{ ('Club Point') }}:
                <span class="fw-700 float-right">{{ $product->earn_point }}</span>
            </div>
        @endif
        <div class="rating rating-sm mb-2">
            {!! renderStarRating($product->rating) !!}
        </div>
        @if(!isset($flash_deal_countdown))
            {{-- @if($checkflashdeal = check_flash_deal_product($product)) --}}
            @if($isValidFlashDeal)
                <div class="d-flex w-md-auto flash_sale_count">
                    <div class="aiz-count-down ml-auto ml-lg-3 align-items-center" data-date="{{ date('Y/m/d H:i:s', $endDate) }}"></div>
                </div>
            @endif
        @endif
        <div class="fs-15">
            @if($basePrice != $discountedPrice)
                <del class="fw-600 opacity-50 mr-1">{{ $basePrice }}</del>
            @endif
            <span class="fw-700 text-secondary">{{ $discountedPrice }}</span>
        </div>
        @if($stockQuantity <= 0)
            @if(check_preorder_product($product))
                <span class="w-auto badge badge-pill badge-success mt-1">PREORDER</span>
            @else
                <span class="w-auto badge badge-pill badge-danger mt-1">SOLD OUT</span>
            @endif
        @elseif(!auth()->check() || in_array(strtolower(auth()->user()->user_type), ['customer', 'seller']))
            <br>
        @endif

        @if(auth()->check() && !in_array(strtolower(auth()->user()->user_type), ['customer', 'seller']))
        <span role="button" class="w-auto badge badge-pill badge-info mt-1 copy-product-info" data-info="{{ $info }}">COPY<i class="las la-copy ml-1"></i></span>
        @endif
    </div>
</div>
