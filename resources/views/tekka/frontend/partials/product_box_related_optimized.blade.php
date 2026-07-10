<style>
    @media only screen and (max-width: 600px) {
        #app-price{
            font-size: 14px !important;
            padding-top: 0px !important;
        }
    }

</style>
<div class="aiz-card-box border border-2 rounded-md hov-shadow-md mt-1 mb-1 has-transition bg-white product_box">
    @php
        $base = (int)str_replace([currency_symbol(),','], '', $product->stroked_price);
        $reduced = $product->nonformated_price;
        $discount = $base - $reduced;
        $dp = ($discount * 100) / $base;
    @endphp
    <div class="ribbon"><span class="ribbon4"><span>{{ ('OFF') }}&nbsp;{{ (int)$dp }}%</span></span></div>
    <div class="d-flex d-md-block">
        <div class="position-relative minw-40 w-sm-100">
            <a href="{{ route('product', $product->slug) }}" class="d-block">
                <img
                    class="img-fit lazyload w-100 mx-auto rounded-md p-2 h-180px h-md-230px product_long_grid"
                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                    data-src="{{ asset('public/'.$product->thumbnail_image) }}"
                    alt="{{  $product->name  }}"
                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                >
            </a>
        </div>
        <div class="pt-3 pb-0 pb-md-1 text-center product_info minw-60 pl-1 pr-2">
            <div class="rating rating-sm text-left ml-2 ">
                {!! renderStarRating($product->rating) !!}
            </div>
            <h3 class="fw-700 fs-14 text-truncate-2 lh-1-4 mb-3 h-40px text-left ml-2">
                <a href="{{ route('product', $product->slug) }}" class="d-block text-reset">{{  $product->name  }}</a>
            </h3>

            <div class="fs-15 text-left ml-2 d-flex align-items-center justify-content-start justify-content-sm-between overflow-hidden mb-2 mb-md-0" >
                <span class="fw-700 text-secondary base-price grid-main-price ">{{ $product->main_price }}</span>
                @if($product->main_price != $product->stroked_price)
                    <del class="fw-700 fs-12 opacity-50 mr-1 ml-3 ml-sm-0 grid-compare-price" style="text-decoration-thickness: 1.5px;">{{ $product->stroked_price }}</del>
                @endif
            </div>
        </div>
    </div>
    <form class="option-choice-form">
        @csrf
        <input type="hidden" name="id" value="{{ $product->id }}">
        @if($product->in_stock)
            <div class="d-flex items-center justify-content-between p-1" style="gap: 5px;">
                <div class="row no-gutters align-items-center aiz-plus-minus bg-light rounded-lg">
                    <button class="btn col-auto btn-icon btn-sm btn-circle btn-light" data-id="{{ $product->id }}" type="button" data-type="minus" data-field="quantity" disabled="">
                        <i class="las la-minus"></i>
                    </button>
                    <input type="number" name="quantity" class="col bg-light border-0 text-center flex-grow-1 fs-16 input-number inputCart-{{$product->id}}" placeholder="1" value="1" min="1" max="10">
                    <button class="btn col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-id="{{ $product->id }}" data-type="plus" data-field="quantity">
                        <i class="las la-plus"></i>
                    </button>
                </div>
                <a href="javascript:void(0)" onclick="addToCart(this)" class="col-8 col-md-6 px-0">
                    <div class="rounded-lg w-100 border-0 skin-secondary-bg text-center text-white p-2 text-uppercase product-grid-btn fw-700">Add to Cart</div>
                </a>
            </div>
        @else
            <button class="w-100 border-0 btn-secondary text-white p-2 mt-2 text-uppercase product-grid-btn fw-700" disabled>Out of Stock</button>
        @endif
    </form>
</div>
