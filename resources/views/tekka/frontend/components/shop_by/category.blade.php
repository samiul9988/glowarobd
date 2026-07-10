<div class="shop-by-category px-0 py-4 px-md-4 mb-md-4 mt-md-5">

    <!-- Section Header start -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-3 px-md-0">
        <div class="d-flex align-items-center logo-and-title">
            <div class="shop-by-category-logo">

                <!-- Section Header icon -->
                <img src="{{ static_asset('assets/img/layer1.png') }}" data-src="" alt=""
                    class="img-fluid img lazyload"
                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
            </div>

            <!-- Section Title -->
            <div class="shop-by-category-title">
                <h3 class="text-white mb-0">Shop By Category</h3>
            </div>
        </div>

        <!-- Header Button -->
        <div class="header-btn">
            <a class="text-decoration-none text-white rounded-pill mobile-btn" href="{{ route('categories.all') }}">See
                All</a>
        </div>
    </div>
    <!-- Section Header End -->

    @php
        //
        // $categories = \App\Models\Category::where('parent_id', 0)->limit(6)->get();
        $top10 = json_decode(get_setting('top10_categories'), true)
        // dd($categories);
    @endphp
    <!-- Product Categories container -->
    <div class="product-categories hide-scrollbar">
        @foreach ($top10 as $id)
            @php
                $category = \App\Models\Category::find($id);
                if($category == null) continue;
                $subcategories = \App\Models\Category::where('parent_id', $category->id)
                    ->limit(4)
                    ->get();
                
                $featuredIcon = json_decode($category->featured_icon, true);
            @endphp
            <div class="category-box bg-white rounded-sm">
                <div class="  align-items-center p-4 row  mx-0 flex-row-reverse flex-md-row">
                    <!-- Product Image -->
                   <div class="col-12 px-0 d-flex d-md-none " >
                    <a href="{{ route('products.category', $category->slug) }}" >
                        <h3 class="product-title">{{ $category->getTranslation('name') }}</h3>
                    </a>
                   </div>
                    <div class="product-img-container  px-0 col-7 col-md-5">
                        <a href="{{ route('products.category', $category->slug) }}"  class="shopby-category-img">
                            <img src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                data-src="{{ @uploaded_asset($featuredIcon['web']) }}"
                                alt="{{ $category->getTranslation('name') }}" class="lazyload"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                               >
                        </a>
                    </div>

                    <div class=" pl-0 pl-md-2 col-5 col-md-7">
                        <!-- Product Title -->
                        <a href="{{ route('products.category', $category->slug) }}" class="d-none d-md-block">
                            <h3 class="product-title">{{ $category->getTranslation('name') }}</h3>
                        </a>

                        <div class="product-categories-inside">
                            @foreach ($subcategories as $subcat)
                                <div class="mb-2">
                                    <a class="text-nowrap text-decoration-none "
                                        href="{{ route('products.category', $subcat->slug) }}">{{ $subcat->name }}</a>
                                </div>
                            @endforeach
                        </div>

                        @if (count(\App\Models\Category::where('parent_id', $category->id)->get()) > 4)
                            <div class="view-more">
                                <a class="text-nowrap" href="{{ route('products.category', $category->slug) }}">View
                                    More</a>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>
