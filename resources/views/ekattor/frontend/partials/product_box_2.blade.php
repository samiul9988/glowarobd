<style>
    @media only screen and (max-width: 600px) {
        #app-price{
            font-size: 14px !important;
            padding-top: 0px !important;
        }
    }

</style>
    <div class="aiz-card-box border border-2 rounded-md hov-shadow-md mt-1 mb-1 has-transition bg-white product_box">
        @if($product['has_discount'] === true)
            <div class="ribbon"><span class="ribbon4"><span>{{ translate('OFF') }}&nbsp;{{get_discount_percentage($product['stroked_price'], $product['main_price'])}}%</span></span></div>
        @endif
        <div class="">
            <div class="position-relative minw-40 w-sm-100">

                <a href="{{ route('product', $product['slug']) }}" class="d-block">
                    <img
                        class="img-fit lazyload w-100 mx-auto rounded-md p-2 h-180px h-md-230px product_long_grid"
                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                        data-src="{{ uploaded_asset($product['thumbnail_image']) }}"
                        alt=""
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                    >
                </a>
                @if (@$product['wholesale_product'])
                    <span class="absolute-bottom-left fs-11 text-white fw-600 px-2 lh-1-8" style="background-color: #455a64">
                    {{ translate('Wholesale') }}
                </span>
                @endif
            </div>

            <div class="pt-3 pb-0 pb-md-1 text-center product_info minw-60 pl-1 pr-2">
                <div class="rating rating-sm text-left ml-2 ">
                    {!! renderStarRating($product['rating']) !!}
                </div>
                <h3 class="fw-700 fs-14 text-truncate-2 lh-1-4 mb-3 h-40px text-left ml-2">
                    <a href="{{ route('product', $product['slug']) }}" class="d-block text-reset">{{  $product['name']  }}</a>
                </h3>
                {{-- @if (addon_is_activated('club_point'))
                    <div class="rounded px-2 mt-3 bg-soft-primary border-soft-primary border">
                        {{ translate('Club Point') }}:
                        <span class="fw-700 float-right">{{ @$product['earn_point'] }}</span>
                    </div>
                @endif --}}
                <div class="fs-15 text-left ml-2 d-flex align-items-center justify-content-start justify-content-sm-between overflow-hidden mb-2 mb-md-0" >
                    <span class="fw-700 text-secondary base-price grid-main-price ">{{ @$product['main_price'] }}</span>
                    @if($product['has_discount'] == true)
                        <del class="fw-700 fs-12 opacity-50 mr-1 ml-3 ml-sm-0 grid-compare-price" style="text-decoration-thickness: 1.5px;">{{@$product['stroked_price'] }}</del>
                    @endif
                </div>
                {{-- <div id="app-price" class="fs-15 text-left text-primary fw-700 ml-2 d-block mb-1 pt-1 pb-md-0">
                    <span>
                        @if(getMinimumPriceByVariant($product, $product->stocks->first(), 'app', 1, null) < getMinimumPriceByVariant($product, $product->stocks->first(), 'web', 1, null))
                            App Price: {{ single_price(getMinimumPriceByVariant($product, $product->stocks->first(), 'app', 1, null)) }}
                        @else
                            &nbsp;
                        @endif
                    </span>
                </div> --}}
            </div>
        </div>


        @if($product['in_stock'] == true)
            <a href="javascript:void(0)" onclick="showAddToCartModal({{ $product['id'] }})">
                <div class="w-100 border-0 skin-secondary-bg text-center text-white py-2 text-uppercase product-grid-btn fw-700">Add to Cart</div>
            </a>
        @else
            {{-- @if(@$product['pre_order'] != 0 && strtotime(date('Y-m-d H:i:s')) >= @$product['preorder_start_date'] && strtotime(date('Y-m-d H:i:s')) <= @$product['preorder_end_date'])
            <button type="button" class="w-100 border-0 btn-secondary text-white py-2 text-uppercase product-grid-btn fw-700" onclick="showAddToCartModal({{ $product['id'] }})">
                <i class="la la-cart-arrow-down"></i> {{ translate('Add to cart')}}
            </button>
            @else --}}
            <button class="w-100 border-0 btn-secondary text-white py-2 text-uppercase product-grid-btn fw-700" disabled>Out of Stock</button>
            {{-- @endif --}}
        @endif
    </div>
