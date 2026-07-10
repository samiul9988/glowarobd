<div class="aiz-card-box border border-light rounded hov-shadow-md mt-1 mb-1 has-transition bg-white product_box">
    @if(discount_in_percentage($product) > 0)
        <div class="ribbon"><span class="ribbon4">{{ translate('OFF') }}&nbsp;{{discount_in_percentage($product)}}%</span></div>
    @endif
    <div class="position-relative">
        <a href="{{ route('product', $product->slug) }}" class="d-block">
            <img
                class="img-fit lazyload mx-auto h-180px h-md-230px product_long_grid"
                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                alt="{{  $product->getTranslation('name')  }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
            >
        </a>
        @if ($product->wholesale_product)
            <span class="absolute-bottom-left fs-11 text-white fw-600 px-2 lh-1-8" style="background-color: #455a64">
                {{ translate('Wholesale') }}
            </span>
        @endif
        <div class="absolute-top-right aiz-p-hov-icon">
            <a href="javascript:void(0)" onclick="addToWishList({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to wishlist') }}" data-placement="left">
                <i class="la la-heart-o"></i>
            </a>
            <a href="javascript:void(0)" onclick="addToCompare({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to compare') }}" data-placement="left">
                <i class="las la-sync"></i>
            </a>
            <a href="javascript:void(0)" onclick="showAddToCartModal({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to cart') }}" data-placement="left">
                <i class="las la-shopping-cart"></i>
            </a>
        </div>
    </div>
    <div class=" pb-md-4 p-2 pb-3 px-md-2 pt-2 text-center product_info">
        <h3 class="fw-700 fs-13 text-truncate-2 lh-1-4 mb-2 h-35px">
            <a href="{{ route('product', $product->slug) }}" class="d-block text-reset">{{  $product->getTranslation('name')  }}</a>
        </h3>
        @if (addon_is_activated('club_point'))
            <div class="rounded px-2 mt-3 bg-soft-primary border-soft-primary border">
                {{ translate('Club Point') }}:
                <span class="fw-700 float-right">{{ $product->earn_point }}</span>
            </div>
        @endif
        <div class="rating rating-sm mb-2">
            {!! renderStarRating($product->rating) !!}
        </div>
        @php
            $stockQuantity = $product->stocks->first()->qty;
        @endphp
        @if(!isset($flash_deal_countdown))
        @if($checkflashdeal = check_flash_deal_product($product))
        @php
            $stockQuantity = $product->flash_deal_product->quantity ?? 0;
        @endphp
        <div class="d-flex w-md-auto flash_sale_count">
            <div class="aiz-count-down ml-auto ml-lg-3 align-items-center" data-date="{{ date('Y/m/d H:i:s', @$product->flash_deal_product->flash_deals->end_date) }}"></div>
        </div>
        @endif
        @endif
        <div class="fs-15">
            @if(home_base_price($product) != home_discounted_base_price($product))
                <del class="fw-600 opacity-50 mr-1">{{ home_base_price($product) }}</del>
            @endif
            <span class="fw-700 text-secondary">{{ home_discounted_base_price($product) }}</span>
        </div>
        @if($stockQuantity <= 0)
            <span class="w-auto badge badge-pill badge-dark mt-1">Sold Out</span>
        @else
            <br>
        @endif
    </div>
</div>
