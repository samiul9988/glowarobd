@section('css')
<style>
    .custom-hover:hover {
        color: #f8f9fa !important; /* Bootstrap's text-light color */
    }
</style>
@endsection
@php
    $bgImage = json_decode($category->bg_image, true);
    $banner = json_decode($category->banner, true);
@endphp
<div x-data="collectionDesignTwo()" class="container  mt-0 mt-md-4 custom-container px-0 mt-md-5 padding-inline-40" style="background-image:url('{{ uploaded_asset($agent->isMobile() ? ($bgImage['mobile'] ?? '') : ($bgImage['web'] ?? '')) }}')">
    <div class="collection-design-two mb-3">
        <div class="image-with-menu-container">
            <div class="image-with-menu d-flex h-100 row">
                <div class="col-md-5">
                    <div class="h-100">
                        <h3 class="product-title mt-4">{{ $category->name }}</h3>

                        <div class="d-flex flex-column justify-content-between h-75">
                            <!-- Product Categories Menu -->
                            <div class="product-categories-inside">
                                @php
                                    $subcategories = \App\Models\Category::where('parent_id', $category->id)
                                        ->limit(10)
                                        ->get();
                                @endphp


                                @if ($subcategories->count() > 0)
                                    @foreach ($subcategories as $subcat)
                                        <div class="mb-2">
                                            <a class="text-nowrap text-decoration-none"
                                                href="{{ route('products.category', $subcat->slug) }}">
                                                {{ $subcat->name }}</a>
                                        </div>
                                    @endforeach
                                @endif

                            </div>

                            
                            <div class="view-more">
                                <a class="text-nowrap rounded-pill" href="{{ route('products.category', $category->slug) }}">View More</a>
                            </div>
                            
                        </div>
                    </div>

                </div>

                <!-- Section Image -->
                <div class="col-md-7">
                    <a href="{{ route('products.category', $category->slug) }}">
                        <img src="{{ uploaded_asset($agent->isMobile() ? ($banner['mobile'] ?? '') : ($banner['web'] ?? '')) }}" data-src="{{ uploaded_asset($agent->isMobile() ? ($banner['mobile'] ?? '') : ($banner['web'] ?? '')) }}"
                            alt="" class="img-fluid img lazyload h-100"
                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div class="d-none collection-design-dropdown-container">
            <div class="collection-design-dropdown d-flex align-items-center justify-content-center">
                <!-- <span class="choose"></span> -->
                <div class="row w-100 py-3 align-items-center ">
                    <div class="col-7  pl-0">
                        <h3 class="fs-18  fw-500 mb-0 choose mb-0">{{ $category->name }}</h3>
                    </div>
                    <div class="col-5 pr-0 d-flex justify-content-end">
                        <a class="heading-btn rounded-pill mobile-btn " href="{{ route('products.category', $category->slug) }}">View all</a>
                    </div>
                </div>
                <select class="form-control pr-3" name="subcategory" id="design2subcategory" @change="design2subcategory()">
                    <option value="{{ $category->id }}">All {{ $category->name }}</option>
                    @foreach ($subcategories as $subcat)
                        <option value="{{ $subcat->id }}">{{ $subcat->name }}</option>
                    @endforeach
                </select>
                {{--<div class="dropdown">
                    <div class="select">
                        <span>Select Category</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <input type="hidden" name="gender">
                    <ul class="dropdown-menu">
                        <li id="male">Shirts</li>
                        <li id="female">Pants</li>
                        <li id="male">Glass</li>
                        <li id="female">Bag & Wallets</li>
                        <li id="male">Face Wash</li>
                        <li id="female">Skincare</li>
                        <li id="male">Shoes</li>
                        <li id="female">Female</li>
                    </ul>
                </div>--}}

                <span class="msg"></span>
            </div>
        </div>

        {{-- <!-- Section Product Card Container --> --}}
        <div class="card-container overflow-scroll-behav hide-scrollbar mt-1" id="design2-card-container">

            @foreach (get_cached_products($category->id, @$type)->slice(0, 8) as $key => $product)
                <div class="carousel-box ">

                    @include(config('app.theme') . 'frontend.partials.product_box_1', [
                        'product' => $product,
                    ])

                </div>
            @endforeach
        </div>
    </div>
</div>

<script type="text/javascript">
    function collectionDesignTwo() {
        return {
            design2subcategory() {
                $.ajax({
                    url: '{{ route("products.category_wise_products") }}',
                    method: 'GET',
                    data: {
                        category_id: $('#design2subcategory').val(),
                    },
                    success: function(data) {
                        $('#design2-card-container').html(data.view);
                    }
                });
            },
        }
    }
</script>
