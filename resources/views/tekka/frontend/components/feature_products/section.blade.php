<!-- Featured Products Start -->
<div class="featured-products container custom-container">
    <div class="heading d-flex flex-row align-items-center justify-content-between mb-3  mt-3 mt-md-5 padding-inline-40">
        <div class="icon-and-title d-flex align-items-center ">
            <div class="icon">
                <img src="{{ static_asset('assets/img/frame69.png') }}" data-src="" width="36px" height="36px"
                    alt="" class="img-fluid img lazyload"
                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
            </div>

            <h3 class="fs-24 fs-sm-20  font-weight-bolder mb-0">Featured Products</h3>
        </div>

        <div class="">
            <a class="heading-btn rounded-pill mobile-btn"
                href="{{ route('suggestion.search', ['search' => '&sort_by=featured']) }}">See
                all</a>
        </div>
    </div>


    <div class="card-container mb-40 overflow-scroll-behav hide-scrollbar padding-inline-40">

        @foreach ($feature_products->take(12) as $product)
            <div class="carousel-box">
                <div class="aiz-card-box hov-shadow-md mt-1 mb-1 has-transition bg-white product_box">
                    <div class="position-absolute z-1 d-flex flex-column mt-3" style = "left: -1px;">

                    </div>

                    <div class="position-relative p-md-2">
                        <a href="{{ route('product', $product->slug) }}" class="d-flex align-items-center ">
                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                                alt="{{ $product->name }} promo" class="img-fluid lazyload w-100"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                        </a>
                    </div>

                    <div class="  p-2 pb-2 px-md-2 pt-2 product_info">
                        <h3 class="fw-400 fs-16 text-truncate-2 lh-1-4 mb-3 pruduct-price-phone">
                            <a href="{{ route('product', $product->slug) }}"
                                class="d-block text-reset">{{ $product->name }}</a>
                        </h3>
                        <div class="rating rating-sm mb-1">
                            <div>
                                {!! renderStarRating($product->rating) !!}
                            </div>
                            <p class="fs-14">({{ $product->rating }})</p>
                        </div>

                        <form id="option-choice-form">
                            @csrf
                            <input type="hidden" name="id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" class="col bg-light border-0 text-center flex-grow-1 fs-16 input-number inputCart-{{$product->id}}" placeholder="1" value="1" min="1" max="10">
                            <div class="fs-11 fs-md-12 product-price">
                                @if (home_base_price($product) != home_discounted_base_price($product))
                                    <del class="fw-600 opacity-50 mr-1">{{ home_base_price($product) }}</del>
                                @endif
                                <span class="fw-700">{{ home_discounted_base_price($product) }}</span>
                            </div>
                            @if($product->stocks->first()->qty > 0)
                            <div class="d-flex align-items-center justify-content-center addtoCartBtn">
                                <button class="primary-ct-btn" type="button" onclick="addToCart(this)">
                                    add to bag
                                </button>
                            </div>
                            @else
                            <button class="w-100 border-0 btn btn-secondary text-white mt-2 text-uppercase rounded fw-700 fs-10" disabled>Stock Out</button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

</div>
