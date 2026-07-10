@php
    $thisStocks = collect($product->stocks);
    if($thisStocks->isNotEmpty()){
        $stockQuantity = $thisStocks->first()->qty;
    }
    else{
        $stockQuantity = 0;
    }
@endphp
@php
    $info = get_copy_content($product);
    // dump($info);
@endphp
<div class="aiz-card-box hov-shadow-md  has-transition bg-white product_box ">
    <div class="position-absolute z-1 d-flex flex-column mt-2 mt-md-3" style = "left: -1px;">
        @if (discount_in_percentage($product) > 0)
            {{-- <div class="ribbon mt-1">
                <span class="ribbon4">
                    {{ ('OFF') }}&nbsp;{{ discount_in_percentage($product) }}%
                </span>
            </div> --}}
            <div class="discount-badge mt-0">
                OFF &nbsp;{{ discount_in_percentage($product) }}%
            </div>
        @endif

        {{-- @if ($stockQuantity <= 0)
            <div class="preOrder-soldOut mt-1 {{ $stockQuantity }} {{ check_preorder_product($product) }}">
                @if (check_preorder_product($product))
                    <span class="ribon-box">PREORDER</span>
                @else
                    <span class="ribon-box">SOLD OUT</span>
                @endif
            </div>
        @endif --}}
        @if (check_preorder_product($product) && $stockQuantity <= 0)
            <div class="preOrder-soldOut mt-1 {{ $stockQuantity }} {{ check_preorder_product($product) }}">
                    <span class="ribon-box">PREORDER</span>
            </div>
        @endif
    </div>

    <div class="position-relative p-0 p-md-2 px-md-2">
        <a href="{{ route('product', $product->slug) }}" class="d-block">
            <img class="img-fit card-image lazyload mx-auto product_long_grid"
                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                data-src="{{ !empty($product->thumbnail_image) ? my_asset($product->thumbnail_image->file_name) : uploaded_asset($product->thumbnail_img) }}"
                alt="{{ $product->name }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
        </a>
        @if ($product->wholesale_product)
            <span class="absolute-bottom-left fs-11 text-white fw-600 px-2 lh-1-8" style="background-color: #455a64">
                {{ ('Wholesale') }}
            </span>
        @endif
    </div>

    <div class=" p-2 pb-2 px-md-2 pt-2 product_info">
        <h3 class="fw-400 fs-16 text-truncate-2 lh-1-4  pruduct-price-phone">
            <a href="{{ route('product', $product->slug) }}" class="d-block text-reset">{{ $product->name }}</a>
        </h3>
        @if (addon_is_activated('club_point'))
            <div class="rounded px-2 mt-3 bg-soft-primary border-soft-primary border">
                {{ ('Club Point') }}:
                <span class="fw-700 float-right">{{ $product->earn_point }}</span>
            </div>
        @endif
        <div class="rating rating-sm">
            <div>
                {!! renderStarRating($product->reviews_avg_rating ?? $product->rating) !!}
            </div>
            <p class="fs-14">({{ $product->reviews_count ?? $product->rating }})</p>
            @if(auth()->check() && !in_array(strtolower(auth()->user()->user_type), ['customer', 'seller']))
                <span role="button" class="w-auto badge badge-pill badge-info mt-1 copy-product-info" data-info="{{ $info }}">COPY</span>
            @endif
        </div>

        @if (!isset($flash_deal_countdown))
            @if ($checkflashdeal = check_flash_deal_product(collect($product)))
                @php
                    $stockQuantity = $product->flash_deal_product->quantity ?? 0;
                    $enddate = !empty($product->flash_deal_product->flash_deals)
                        ? $product->flash_deal_product->flash_deals->end_date
                        : date(Y - m - d);
                @endphp
                <div class="d-flex w-md-auto flash_sale_count">
                    <div class="aiz-count-down ml-auto ml-lg-3 align-items-center"
                        data-date="{{ date('Y/m/d H:i:s', $enddate) }}"></div>
                </div>
            @endif
        @endif
        <form id="option-choice-form" class="mb-0">
            @csrf
            <input type="hidden" name="id" value="{{ $product->id }}">
            @if ($product->choice_options != null)
            @foreach (json_decode($product->choice_options) as $key => $choice)
            <div class="row no-gutters d-none">
                <div class="col-sm-2">
                    <div class="opacity-50 my-2">{{ \App\Models\Attribute::find($choice->attribute_id)->getTranslation('name') }}:</div>
                </div>
                <div class="col-sm-10">
                    <div class="aiz-radio-inline">
                        @foreach ($choice->values as $key => $value)
                        <label class="aiz-megabox pl-0 mr-2">
                            <input
                                type="radio"
                                name="attribute_id_{{ $choice->attribute_id }}"
                                value="{{ $value }}"
                                @if ($key == 0) checked @endif
                            >
                            <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center py-2 px-3 mb-2">
                                {{ $value }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
            @endif

            @if (count(json_decode($product->colors)) > 0)
            <div class="row no-gutters d-none">
                <div class="col-sm-2">
                    <div class="opacity-50 my-2">{{ ('Color') }}:</div>
                </div>
                <div class="col-sm-10">
                    <div class="aiz-radio-inline">
                        @foreach (json_decode($product->colors) as $key => $color)
                        <label class="aiz-megabox pl-0 mr-2" data-toggle="tooltip" data-title="{{ \App\Models\Color::where('code', $color)->first()->name }}">
                            <input
                                type="radio"
                                name="color"
                                value="{{ \App\Models\Color::where('code', $color)->first()->name }}"
                                @if ($key == 0) checked @endif
                            >
                            <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center p-1 mb-2">
                                <span class="size-30px d-inline-block rounded" style="background: {{ $color }};"></span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            <input type="hidden" name="quantity" class="col bg-light border-0 text-center flex-grow-1 fs-16 input-number inputCart-{{$product->id}}" placeholder="1" value="1" min="1" max="10">

            {{-- <div class="fs-14 product-price" style = "flex-wrap: wrap;">
                @if (home_base_price($product) != home_discounted_base_price($product))
                    <del class="fw-600 opacity-50 mr-1">{{ home_base_price($product) }}</del>
                @endif
                <span class="fw-700">{{ home_discounted_base_price($product) }}</span>
            </div> --}}

            @php
                $homeBasePrice = home_base_price($product);
                $homeDiscountedBasePrice = home_discounted_base_price($product);
                try {
                    $discountedMinimumPrice = single_price(getMinimumPriceByVariant($product, $product->stocks?->first(), 'web', 1, $currentlyAuthenticatedUser));
                } catch (\Throwable $e) {
                    $discountedMinimumPrice = single_price(getMinimumPriceByVariant($product, collect($product->stocks)->first() ?? null, 'web', 1, $currentlyAuthenticatedUser));
                }
            @endphp
            <div class="fs-11 fs-md-12 product-price" style="flex-wrap: wrap;">
                @if ($homeBasePrice != $discountedMinimumPrice)
                    <del class="fw-600 opacity-50 mr-1">{{ $homeBasePrice }}</del>
                @endif
                <span class="fw-700" data-base-price="{{ $homeBasePrice }}" data-discounted-price="{{ $homeDiscountedBasePrice }}">
                    {{ $discountedMinimumPrice }}
                </span>
            </div>

            @if($stockQuantity > 0)
            <div class="d-flex align-items-center justify-content-center addtoCartBtn">
                <button class="primary-ct-btn" type="button" onclick="addToCart(this)">
                    add to bag
                </button>
            </div>
            @else
                {{-- <button class="w-100 border-0 btn btn-secondary text-white mt-2 text-uppercase rounded fw-700 fs-10" disabled>Out of Stock</button> --}}
                <button class="w-100 border-0 btn btn-secondary text-white mt-2 text-uppercase rounded fw-700 fs-10" disabled>Stock Out</button>
            @endif
        </form>
    </div>
</div>
