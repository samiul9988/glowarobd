<div class="bg-white rounded shadow-sm">
    <div class="border-bottom p-3">
        <h3 class="fs-16 fw-600 mb-0">
            <span class="mr-4">{{ ('Related products') }}</span>
        </h3>
    </div>
    <div class="py-3 mb-1" style="padding: 0 5px;">
        <div class="row gutters-5 related_product_holder">
            @foreach ($relatedProducts as $key => $related_product)
                @php
                    $normalPrice = home_price($related_product);
                    $discountPrice = home_discounted_price($related_product);
                    $info = '';
                    $info .= 'Product Name : ' . $related_product->name . "\n";
                    $info .= 'Price: ' . single_price($related_product->unit_price) . "\n";
                    if ($normalPrice != $discountPrice) {
                        $info .= 'Discount Price: ' . $discountPrice . "\n";
                    }
                    // $info .= 'Order Link : '.route('product', $related_product->slug)."\n";
                @endphp
                <div class="carousel-box col-md-2 col-4">
                    @if (discount_in_percentage($related_product) > 0)
                        <div class="ribbon"><span
                                class="ribbon4">{{ ('OFF') }}&nbsp;{{ discount_in_percentage($related_product) }}%</span>
                        </div>
                    @endif
                    <div class="aiz-card-box border border-light rounded hov-shadow-md my-2 has-transition">
                        <div class="product_image_holder">
                            <a href="{{ route('product', $related_product->slug) }}" class="d-block">
                                <img class="img-fit lazyload mx-auto h-sm-auto h-md-215px"
                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                    data-src="{{ !empty($related_product->thumbnail_image) ? my_asset($related_product->thumbnail_image->file_name) : uploaded_asset($related_product->thumbnail_img) }}"
                                    alt="{{ $related_product->name }}"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                    style="height: 215px;">
                            </a>
                        </div>
                        <div class="py-2 px-1 text-center releted_product_info">
                            <h3 class="fw-700 fs-13 text-truncate-2 lh-1-4 mb-0 h-35px">
                                <a href="{{ route('product', $related_product->slug) }}"
                                    class="d-block text-reset">{{ $related_product->name }}</a>
                            </h3>
                            @if (addon_is_activated('club_point'))
                                <div class="rounded px-2 mt-2 bg-soft-primary border-soft-primary border">
                                    {{ ('Club Point') }}:
                                    <span class="fw-700 float-right">{{ $related_product->earn_point }}</span>
                                </div>
                            @endif
                            <div class="rating rating-sm mt-1">
                                {!! renderStarRating($related_product->rating) !!}
                            </div>
                            <div class="fs-15 releted_product_price pt-2">
                                @if (home_base_price($related_product) != home_discounted_base_price($related_product))
                                    <del class="fw-600 opacity-50 mr-1">{{ home_base_price($related_product) }}</del>
                                @endif
                                <span
                                    class="fw-700 text-secondary">{{ home_discounted_base_price($related_product) }}</span>
                            </div>
                            @php
                                $stockQuantity = $related_product->stocks->first()->qty;
                                if (check_flash_deal_product($related_product)) {
                                    $stockQuantity = $related_product->flash_deal_product->quantity ?? 0;
                                }
                            @endphp
                            @if ($stockQuantity <= 0)
                                @if (check_preorder_product($related_product))
                                    <span class="w-auto badge badge-pill badge-success mt-1">PREORDER</span>
                                @else
                                    <span class="w-auto badge badge-pill badge-danger mt-1">SOLD OUT</span>
                                @endif
                            @elseif(!auth()->check() || in_array(strtolower(auth()->user()->user_type), ['customer', 'seller']))
                                <br>
                            @endif

                            @if (auth()->check() && !in_array(strtolower(auth()->user()->user_type), ['customer', 'seller']))
                                <span role="button" class="w-auto badge badge-pill badge-info mt-1 copy-product-info"
                                    data-info="{{ $info }}">COPY<i class="las la-copy ml-1"></i></span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
