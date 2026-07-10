@php
    $info = get_copy_content($product);
    // dump($info);
@endphp
<div class="aiz-card-box border border-light rounded hov-shadow-md mt-1 mb-1 has-transition bg-white product_box">
    @if(discount_in_percentage($product) > 0)
        <div class="ribbon">
            <span class="ribbon4">
                <span>{{ ('OFF') }}&nbsp;{{discount_in_percentage($product)}}%
                </span>
            </span>
        </div>
    @endif
    <div class="position-relative">
        <a href="{{ route('product', $product['slug']) }}" class="d-block">
            <img
                class="img-fit lazyload mx-auto h-180px h-md-230px product_long_grid"
                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                data-src="{{ static_asset($product['thumbnail_image']) }}"
                alt="{{  $product['name']  }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
            >
        </a>

        <div class="absolute-top-right aiz-p-hov-icon">
            <a href="javascript:void(0)" onclick="addToWishList({{ $product['id'] }})" data-toggle="tooltip" data-title="{{ ('Add to wishlist') }}" data-placement="left">
                <i class="la la-heart-o"></i>
            </a>
            <a href="javascript:void(0)" onclick="addToCompare({{ $product['id'] }})" data-toggle="tooltip" data-title="{{ ('Add to compare') }}" data-placement="left">
                <i class="las la-sync"></i>
            </a>
            <a href="javascript:void(0)" onclick="showAddToCartModal({{ $product['id'] }})" data-toggle="tooltip" data-title="{{ ('Add to cart') }}" data-placement="left">
                <i class="las la-shopping-cart"></i>
            </a>
        </div>
    </div>
    <div class=" pb-md-4 p-2 pb-3 px-md-2 pt-2 text-center product_info">
        <h3 class="fw-700 fs-13 text-truncate-2 lh-1-4 mb-2 h-35px">
            <a href="{{ route('product', $product['slug']) }}" class="d-block text-reset">{{  $product['name']  }}</a>
        </h3>
        <div class="rating rating-sm mb-2">
            {!! renderStarRating($product['rating']) !!}
        </div>
        @php
            $stockQuantity = $product['current_stock'];
        @endphp

        <div class="fs-15" >
            <span class="fw-700 text-secondary base-price grid-main-price ">{{ @$product['web_price'] }}</span>
            @if($product['has_discount'] == true)
                <del class="fw-700 fs-12 opacity-50 mr-1 ml-3 ml-sm-0 grid-compare-price" style="text-decoration-thickness: 1.5px;">{{@$product['stroked_price'] }}</del>
            @endif
        </div>
        @if($stockQuantity <= 0)
            @if($product['is_preorder'])
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
