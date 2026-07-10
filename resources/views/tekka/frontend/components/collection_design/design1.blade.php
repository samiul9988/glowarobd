@php
    $bgImage = ($category && $category->bg_image) ? json_decode($category->bg_image, true) : null;
    $banner = ($category && $category->banner) ? json_decode($category->banner, true) : null;
@endphp
<div class="container custom-container mt-2 mt-md-4 px-0 mb-md-2 background-image-style" style="background-image:url('{{ uploaded_asset($agent->isMobile() ? ($bgImage['mobile'] ?? '') : ($bgImage['web'] ?? '')) }}')">
    <div class=" collection-design-one">
        <div class="image-with-menu-container">
            <div class="image-with-menu d-flex h-100">

                {{-- <!-- Section Image --> --}}
                <div class="">
                    <a href="{{ route('products.category', $category->slug) }}">
                        <img
                            src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset($agent->isMobile() ? ($banner['mobile'] ?? '') : ($banner['web'] ?? '')) }}" alt=""
                            class="img-fluid img lazyload h-100"
                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"></a>
                </div>
            </div>
        </div>


        {{-- Section Product Card Container --}}
        <div class=" d-block d-md-none container pt-3 pb-2">
           <div class="row  align-items-center">
                <div class="col-7">
                    <h5 class="fs-18 fw-500 pl-2 mb-0">{{ $category->name }}</h5>
                </div>
                <div class="col-5 d-flex justify-content-end ">
                    <a  class="mobile-btn" href="{{ route('products.category', $category->slug) }}">See all</a>
                </div>
           </div>
        </div>
        <div class="card-container hide-scrollbar" >

            @foreach (get_cached_products($category->id, @$type)->slice(0, 10) as $key => $product)
                <div class="carousel-box ">

                    @include(config('app.theme') . 'frontend.partials.product_box_1', [
                        'product' => $product,
                    ])

                </div>
            @endforeach



        </div>
    </div>
</div>
