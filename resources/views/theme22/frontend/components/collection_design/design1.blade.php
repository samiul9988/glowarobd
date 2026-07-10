@php
    $bgImage = ($category && $category->bg_image) ? json_decode($category->bg_image, true) : null;
    $banner = ($category && $category->banner) ? json_decode($category->banner, true) : null;
@endphp

<section class="product--home-categories mt-1 mb-3" style="background-image:url('{{ uploaded_asset($agent->isMobile() ? ($bgImage['mobile'] ?? '') : ($bgImage['web'] ?? '')) }}')">
    <div class="d-block d-md-none" data-banners="{{ json_encode($banner) }}" data-isMobile="{{ $agent->isMobile() ? ($banner['mobile'] ?? '') : ($banner['web'] ?? '') }}">
        <img class="img-fit mx-auto lazyloaded"
        src="{{ uploaded_asset($agent->isMobile() ? ($banner['mobile'] ?? '') : ($banner['web'] ?? '')) }}"
        data-src="{{ uploaded_asset($agent->isMobile() ? ($banner['mobile'] ?? '') : ($banner['web'] ?? '')) }}"
        alt="{{ $category->name }}"
        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
    </div>
    <div class="container">
        <div class="bg-white-ex px-0 py-md-3">
            <div class="d-none d-md-flex mb-3 align-items-baseline border-bottom section_title_holder">
                <h3 class="h5 fw-700 mb-0">
                    <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ $category->name }}</span>
                </h3>
                <a href="{{ route('products.category', $category->slug) }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ ('View More') }}</a>
            </div>
            <div class="row aiz-carousel-ex gutters-10 half-outside-arrow product--home-categories-box" data-items="6" data-xl-items="6" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                {{-- @forelse (get_cached_products($category->id, @$type) as $key => $product) --}}
                @forelse (get_db_products($category->id, @$type) as $key => $product)
                    <div class="carousel-box col-lg-2 col-md-3 col-6 px-1">
                        {{-- {{ $product->name }} --}}
                        @include(config('app.theme').'frontend.partials.product_box_1',['product' => $product])
                    </div>
                @empty
                    <div class="col-12 d-flex align-items-center justify-content-center p-4">
                        <h6>No Products Found</h6>
                    </div>
                @endforelse
            </div>
            <div class="d-block d-md-none text-center pb-3">
                <a href="{{ route('products.category', $category->slug) }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md border border-black">{{ ('View More') }}</a>
            </div>
        </div>
    </div>
</section>
