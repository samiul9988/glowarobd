@php
    $banner = json_decode($category->banner, true);
    if($category->bg_image ?? null){
        $bgImage = json_decode($category->bg_image, true);
    }else{
        $bgImage = null;
    }
    $bannerImage = $agent->isMobile() ? ($banner['mobile'] ?? '') : ($banner['web'] ?? '');
    $bgImage = $agent->isMobile() ? ($bgImage['mobile'] ?? '') : ($bgImage['web'] ?? '');
@endphp
<div class="container custom-container mt-0  mt-md-5 px-md-0" >
    {{-- <!-- Collection Design 4 Start --> --}}
    <div class="collection-design-four">
        <div class="container custom-container px-md-0">
            <div class = "banner-section d-block d-md-none">
                <img class="img-fit card-image mx-auto ls-is-cached lazyloaded"
                    src="{{ $bannerImage ? uploaded_asset($bannerImage) : static_asset('assets/img/placeholder.jpg') }}"
                    data-src="{{ $bannerImage ? uploaded_asset($bannerImage) : '' }}"
                    alt="{{ $category->name }}"
                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                >
                @if($agent->isMobile())
                <div class="mobile-category-name d-none">
                    <h3>{{ $category->name }}</h3>
                </div>
                @endif
            </div>
            <div class = "collection-design-four-container" style="background-image:url('{{ $bgImage ? uploaded_asset($bgImage) : '' }}')">
                <div class ='products-section'>
                    <div class = "top d-none d-md-flex pb-2">
                        <div>
                            <h3>{{ $category->name }}</h3>
                        </div>
                        <div>
                            <a class="heading-btn rounded-pill"
                                href = "{{ route('products.category', $category->slug) }}">See all</a>
                        </div>
                    </div>
                    <div class="card-container mb-40  overflow-scroll-behav hide-scrollbar">
                        @foreach (get_cached_products($category->id, @$type)->slice(0, 8) as $key => $product)
                            <div class="carousel-box px-0 ">
                                @include(config('app.theme') . 'frontend.partials.product_box_1', [
                                    'product' => $product,
                                ])
                            </div>
                        @endforeach
                    </div>
                    <div class=" d-block d-md-none text-center pt-3">
                        <a class="heading-btn rounded-pill mobile-btn border-white text-white"
                            href = "{{ route('products.category', $category->slug) }}">
                            See all
                        </a>
                    </div>
                </div>
                <div class = "banner-section d-none d-md-flex">
                    <a href="{{ route('products.category', $category->slug) }}"
                        class="d-flex align-items-center">
                        <img class="img-fit card-image mx-auto ls-is-cached lazyloaded"
                        src="{{ $bannerImage ? uploaded_asset($bannerImage) : static_asset('assets/img/placeholder.jpg') }}"
                        data-src="{{ $bannerImage ? uploaded_asset($bannerImage) : '' }}"
                        alt="{{ $category->name }}"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Collection Design 4 End -->
</div>
