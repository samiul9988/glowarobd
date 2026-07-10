@extends(config('app.theme') . 'frontend.layouts.app')
@php
    $categoryfilePath = storage_path('app/public/categories/category.json');
    if (file_exists($categoryfilePath)) {
        $jsonData = file_get_contents($categoryfilePath);
        $categories = collect(json_decode($jsonData));

        if ($categories->isEmpty()) {
            $categories = collect(\App\Models\Category::all());
        }
    } else {
        $categories = collect(\App\Models\Category::all());
    }
    $allbrands = collect(\App\Models\Brand::all());
    $platform = $agent->platform();
@endphp
@if (isset($category_id))
    @php
        $meta_title =
            $categories->firstWhere('id', $category_id)->meta_title ??
            $categories->firstWhere('id', $category_id)->name;
        $meta_description =
            $categories->firstWhere('id', $category_id)->meta_description ??
            $categories->firstWhere('id', $category_id)->name;
        // dd('category_id', $meta_title, $meta_description);
    @endphp
@elseif (isset($brand_id))
    @php
        $meta_title =
            $allbrands->firstWhere('id', $brand_id)->meta_title ?? $allbrands->firstWhere('id', $brand_id)->name;
        $meta_description =
            $allbrands->firstWhere('id', $brand_id)->meta_description ?? $allbrands->firstWhere('id', $brand_id)->name;
        $brandPageBanner = $allbrands->firstWhere('id', $brand_id)->page_banner;
        $brandName = $allbrands->firstWhere('id', $brand_id)->name;
        // dd($pageBanner);
        // dd('brand_id', $meta_title, $meta_description);
    @endphp
@else
    @php
        $meta_title = get_setting('meta_title');
        $meta_description = get_setting('meta_description');
        // dd('else', $meta_title, $meta_description);
    @endphp
@endif

@section('meta')
<x-seo :meta="[
    'title' => $meta_title,
    'description' => $meta_description,
    'twitter' => [
        'card' => 'product',
    ]
]" />

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css"
        integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('content')
    <style>
        #categories li .has-submenu:after {
            content: "\f078";
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            float: right;
        }

        #categories li a.collapsed:after {
            content: "\f054";
        }

        #categories li a.item_active {
            color: #ff3d71;
            font-weight: 700;
        }

        #style-3 {
            height: 350px;
            overflow-y: scroll;
        }

        #style-3::-webkit-scrollbar {
            width: 6px;
            background-color: #F5F5F5;
        }

        #style-3::-webkit-scrollbar-thumb {
            border-radius: 10px;
            background: #007BFF;
        }

        [type="radio"]:checked,
        [type="radio"]:not(:checked) {
            position: absolute;
            left: -9999px;
        }

        [type="radio"]:checked+label,
        [type="radio"]:not(:checked)+label {
            position: relative;
            padding-left: 28px;
            cursor: pointer;
            line-height: 16px;
            display: inline-block;
            color: #666;
        }

        [type="radio"]:checked+label:before,
        [type="radio"]:not(:checked)+label:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 18px;
            height: 18px;
            border: 1px solid #ddd;
            border-radius: 100%;
            background: #fff;
        }

        [type="radio"]:checked+label:after,
        [type="radio"]:not(:checked)+label:after {
            content: '';
            width: 12px;
            height: 12px;
            background: #007BFF;
            position: absolute;
            top: 3px;
            left: 3px;
            border-radius: 100%;
            -webkit-transition: all 0.2s ease;
            transition: all 0.2s ease;
        }

        [type="radio"]:not(:checked)+label:after {
            opacity: 0;
            -webkit-transform: scale(0);
            transform: scale(0);
        }

        [type="radio"]:checked+label:after {
            opacity: 1;
            -webkit-transform: scale(1);
            transform: scale(1);
        }
    </style>
    <section class="collection-banner">
        @php
        //dd($agent->isMobile());
            $slug = request()->route('category_slug');
            $category = $categories->firstWhere('slug', $slug);
            // dd($category);
            if($category)
            $page_banner = json_decode($category->page_banner, true);
            //dd($page_banner);

            // dd($agent->isMobile() ? uploaded_asset($page_banner['mobile'] ?? '') : uploaded_asset($page_banner['web'] ?? ''));
        @endphp
        <div class="collectionImage">
            @if (isset($brand_id))
                <img src="" data-src="{{ uploaded_asset($brandPageBanner ?? '') }}" height="auto" alt=""
                    class="img-fluid img lazyload"
                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                    style="max-height: 250px">
            @else
            <img src="" data-src="{{ $agent->isMobile() ? uploaded_asset($page_banner['mobile'] ?? '') : uploaded_asset($page_banner['web'] ?? '') }}" height="auto" alt="" class="img-fluid img lazyload"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';" style="max-height: 250px">
            @endif
        </div>
        <div class="collectionContent">
            <div class="innerContent">

                @if (isset($brand_id) )
                    <h2 class="text-capitalize">{{ $brandName ?? '' }}</h2>
                @elseif($category && $category->discount)
                    <h2 class="text-capitalize">{{ $category->name ?? '' }}</h2>
                    <h4 class="text-uppercase">Get {{ $category->discount_type == 'percent' ? $category->discount.'%' : single_price($category->discount) }} off</h4>
                @else
                    <h2 class="text-capitalize">{{ isset($category->name) ? $category->name : (request()->has('keyword') ? 'Search results' : translate('All Products')) }}</h2>
                @endif
            </div>
        </div>
    </section>

    <section class="product_listingPage mb-4 pt-3">
        <div class="container sm-px-0">
            <form class="" id="search-form" action="" method="GET">
                @foreach ($customFields as $field)
                <input type="hidden" name="{{ $field->slug }}" value="{{ request()->query($field->slug) }}">
                @endforeach
                <div class="row" style = "position: relative; align-items: start;">
                    <div class="col-xl-3 filter-sidebarCustom" style = "">
                        <div class=" aiz-filter-sidebar collapse-sidebar-wrap sidebar-xl sidebar-right z-1035">
                            <div class="overlay overlay-fixed dark c-pointer" data-toggle="class-toggle"
                                data-target=".aiz-filter-sidebar" data-same=".filter-sidebar-thumb"></div>
                            <div class="collapse-sidebar c-scrollbar-light text-left px-3">
                                <div class="d-flex d-xl-none justify-content-between align-items-center pl-3 ">
                                    <h3 class="h6 mb-0 fw-600">{{ ('Filters') }}</h3>
                                    <button type="button" class="btn btn-sm p-2 filter-sidebar-thumb"
                                        data-toggle="class-toggle" data-target=".aiz-filter-sidebar">
                                        <i class="las la-times la-2x"></i>
                                    </button>
                                </div>
                                <div class="bg-white sidebar-box">
                                    <div class="fs-15 fw-600 collaps-btn">
                                        <span> {{ ('Categories') }}</span>
                                        <span>
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </span>
                                    </div>
                                    <div class="py-3 collaps-content" id="categoryProduct">
                                        <ul id="categories" class="list-unstyled">
                                            @php
                                                $item = $categories->where('id',$category_id)->first();
                                                $allchilds = [];
                                                if($item){
                                                $allchilds = \App\Utility\CategoryUtility::flat_children($item->id);
                                                }
                                                    $childsSlugs = [];
                                                    foreach ($allchilds as $child) {
                                                        $childsSlugs[] = $child['slug'];
                                                    }
                                                    $isItem = in_array(request()->segment(2), $childsSlugs);
                                                    $subcategories = $item ? \App\Utility\CategoryUtility::get_immediate_children_ids($item->id) : [];
                                            @endphp
                                            {{-- Selected category at first --}}
                                            <li class="categoryItem mb-2 ml-2 @if($isItem) hide hasHide @endif">
                                                <a href="{{ route('products.category', $item ? $item->slug : '') }}"
                                                class="text-reset fs-14 @if (count($subcategories) > 0) has-submenu @endif {{ $isItem ? 'item_active' : '' }}{{ request()->segment(2) == ($item && $item->slug) ? 'item_active' : '' }}"
                                                @if (count($subcategories) > 0) data-toggle="collapse" data-target="#{{ Str::slug($item->slug ?? '') }}-collapse" aria-expanded="false" @endif>
                                                {{ $item->name ?? '' }}
                                                </a>
                                                @if (count($subcategories) > 0)
                                                    <div class="collapse {{ $isItem ? 'show' : '' }}{{ request()->segment(2) == $item->slug ? 'show' : '' }}" id="{{ Str::slug($item->slug) }}-collapse">
                                                        <ul class="btn-toggle-nav list-unstyled fw-normal mr-2 pb-1 small">
                                                            @foreach ($subcategories as $key => $first_level_id)
                                                                @php
                                                                    $firstLevelCategory = $categories->firstWhere('id', $first_level_id);
                                                                    $firstLevelSubcategories = \App\Utility\CategoryUtility::get_immediate_children_ids($firstLevelCategory->id);
                                                                    $firstAllChilds = \App\Utility\CategoryUtility::flat_children($first_level_id);
                                                                    $firstChildsSlugs = [];
                                                                    foreach ($firstAllChilds as $first) {
                                                                        $firstChildsSlugs[] = $first['slug'];
                                                                    }
                                                                    $isFirstChild = in_array(request()->segment(2), $firstChildsSlugs);
                                                                @endphp
                                                                <li class="mb-2 ml-2 @if ($loop->first) mt-2 @endif">
                                                                    <a href="{{ route('products.category', $firstLevelCategory->slug) }}"
                                                                    class="text-reset fs-14 @if (count($firstLevelSubcategories) > 0) has-submenu @endif {{ $isFirstChild ? 'item_active' : '' }}{{ request()->segment(2) == $firstLevelCategory->slug ? 'item_active' : '' }}"
                                                                    @if (count($firstLevelSubcategories) > 0) data-toggle="collapse" data-target="#{{ Str::slug($firstLevelCategory->slug) }}-collapse" aria-expanded="false" @endif>
                                                                    {{ $firstLevelCategory->name }}
                                                                    </a>
                                                                    @if (count($subcategories) > 0)
                                                                        <div class="collapse {{ $isFirstChild ? 'show' : '' }}" id="{{ Str::slug($firstLevelCategory->slug) }}-collapse">
                                                                            <ul class="btn-toggle-nav list-unstyled fw-normal mr-2 pb-1 small">
                                                                                @foreach ($firstLevelSubcategories as $key => $second_level_id)
                                                                                    @php
                                                                                        $secondLevelCategory = $categories->firstWhere('id', $second_level_id);
                                                                                    @endphp
                                                                                    <li class="mb-2 ml-2 @if ($loop->first) mt-2 @endif">
                                                                                        <a href="{{ route('products.category', $secondLevelCategory->slug) }}" class="text-reset fs-14 {{ request()->segment(2) == $secondLevelCategory->slug ? 'item_active' : '' }}">
                                                                                            {{ $secondLevelCategory->name }}
                                                                                        </a>
                                                                                    </li>
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </li>

                                            @foreach (array_reverse($categories->where('level', 0)->where('id','!=',$category_id)->sortByDesc('order_level')->all()) as $key => $item)
                                                @php
                                                    $allchilds = \App\Utility\CategoryUtility::flat_children($item->id);
                                                    $childsSlugs = [];
                                                    foreach ($allchilds as $child) {
                                                        $childsSlugs[] = $child['slug'];
                                                    }
                                                    $isItem = in_array(request()->segment(2), $childsSlugs);
                                                    $subcategories = \App\Utility\CategoryUtility::get_immediate_children_ids($item->id);
                                                @endphp
                                                <li class="categoryItem mb-2 ml-2 @if(!$loop->first && $isItem) hide hasHide @endif">
                                                    <a href="{{ route('products.category', $item->slug) }}"
                                                    class="text-reset fs-14 @if (count($subcategories) > 0) has-submenu @endif {{ $isItem ? 'item_active' : '' }}{{ request()->segment(2) == $item->slug ? 'item_active' : '' }}"
                                                    @if (count($subcategories) > 0) data-toggle="collapse" data-target="#{{ Str::slug($item->slug) }}-collapse" aria-expanded="false" @endif>
                                                    {{ $item->name }}
                                                    </a>
                                                    @if (count($subcategories) > 0)
                                                        <div class="collapse {{ $isItem ? 'show' : '' }}{{ request()->segment(2) == $item->slug ? 'show' : '' }}" id="{{ Str::slug($item->slug) }}-collapse">
                                                            <ul class="btn-toggle-nav list-unstyled fw-normal mr-2 pb-1 small">
                                                                @foreach ($subcategories as $key => $first_level_id)
                                                                    @php
                                                                        $firstLevelCategory = $categories->firstWhere('id', $first_level_id);

                                                                        $firstLevelSubcategories = \App\Utility\CategoryUtility::get_immediate_children_ids($firstLevelCategory->id);
                                                                        $firstAllChilds = \App\Utility\CategoryUtility::flat_children($first_level_id);
                                                                        $firstChildsSlugs = [];
                                                                        foreach ($firstAllChilds as $first) {
                                                                            $firstChildsSlugs[] = $first['slug'];
                                                                        }
                                                                        $isFirstChild = in_array(request()->segment(2), $firstChildsSlugs);
                                                                    @endphp
                                                                    <li class="mb-2 ml-2 @if ($loop->first) mt-2 @endif">
                                                                        <a href="{{ route('products.category', $firstLevelCategory->slug) }}"
                                                                        class="text-reset fs-14 @if (count($firstLevelSubcategories) > 0) has-submenu @endif {{ $isFirstChild ? 'item_active' : '' }}{{ request()->segment(2) == $firstLevelCategory->slug ? 'item_active' : '' }}"
                                                                        @if (count($firstLevelSubcategories) > 0) data-toggle="collapse" data-target="#{{ Str::slug($firstLevelCategory->slug) }}-collapse" aria-expanded="false" @endif>
                                                                        {{ $firstLevelCategory->name }}
                                                                        </a>
                                                                        @if (count($subcategories) > 0)
                                                                            <div class="collapse {{ $isFirstChild ? 'show' : '' }}" id="{{ Str::slug($firstLevelCategory->slug) }}-collapse">
                                                                                <ul class="btn-toggle-nav list-unstyled fw-normal mr-2 pb-1 small">
                                                                                    @foreach ($firstLevelSubcategories as $key => $second_level_id)
                                                                                        @php
                                                                                            $secondLevelCategory = $categories->firstWhere('id', $second_level_id);
                                                                                        @endphp
                                                                                        <li class="mb-2 ml-2 @if ($loop->first) mt-2 @endif">
                                                                                            <a href="{{ route('products.category', $secondLevelCategory->slug) }}" class="text-reset fs-14 {{ request()->segment(2) == $secondLevelCategory->slug ? 'item_active' : '' }}">
                                                                                                {{ $secondLevelCategory->name }}
                                                                                            </a>
                                                                                        </li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            </div>
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="mt-3">
                                            <a class="seeMoreCategory" href="javascript:void(0)" onclick="toggleVisibility()">
                                                <span id="toggleIcon"><i class="fa-solid fa-plus"></i></span>
                                                <span id="toggleText">See More</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white sidebar-box">
                                    <div class="fs-15 fw-600 py-3  collaps-btn">
                                        <span>{{ ('Price range') }}</span>
                                        <span>
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </span>
                                    </div>
                                    <div class="py-3 collaps-content">
                                        <div class="aiz-range-slider">
                                            @php
                                                $pCount = \App\Models\Product::count();
                                            @endphp
                                            <div id="input-slider-range"
                                                data-range-value-min="@if ($pCount < 1) 0 @else {{ \App\Models\Product::min('unit_price') }} @endif"
                                                data-range-value-max="@if ($pCount < 1) 0 @else {{ \App\Models\Product::max('unit_price') }} @endif">
                                            </div>

                                            <div class="row mt-2">
                                                <div class="col-6">
                                                    <span class="range-slider-value value-low fs-14 fw-600 opacity-70" @if (isset($min_price)) data-range-value-low="{{ $min_price }}" @elseif($products->min('unit_price') > 0)
                                                    data-range-value-low="{{$products->min('unit_price')}}"
                                                    @else data-range-value-low="0" @endif id="input-slider-range-value-low"></span>
                                                </div>
                                                <div class="col-6 text-right">
                                                    <span class="range-slider-value value-high fs-14 fw-600 opacity-70" @if (isset($max_price)) data-range-value-high="{{ $max_price }}" @elseif($products->max('unit_price') > 0)
                                                    data-range-value-high="{{$products->max('unit_price')}}"
                                                    @else data-range-value-high="0" @endif  id="input-slider-range-value-high"></span>
                                                </div>
                                            </div>

                                            <form action="">
                                                <div class="row mt-2 price-range">
                                                    <div class="col-6 pr-1">
                                                        <label>Min</label>
                                                        <input class="w-100" type="number" id="pMinPrice" value="{{ $min_price ?? $products->min('unit_price') }}" onchange="priceRangeFilter()">
                                                    </div>
                                                    <div class="col-6 pl-1">
                                                        <label>Max</label>
                                                        <input class="w-100" type="number" id="pMaxPrice" value="{{ $max_price ?? $products->max('unit_price') }}" onchange="priceRangeFilter()">
                                                    </div>
                                                    <button type="button" class="price-range-filter" onclick="filter()">Apply</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white sidebar-box sidebarBrands">
                                    <div class="fs-15 fw-600 py-3  collaps-btn">
                                        <span> {{ ('Brand') }}</span>
                                        <span>
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </span>
                                    </div>
                                    <div class="py-3 collaps-content" id="style-3">
                                        @if(isset($brand_id))
                                        <div id="brand-radio">
                                            <div class="brand-radio">
                                                <span class="category-wrap">
                                                    <input type="checkbox" id="brandRadioDefault"
                                                        name="brand" onchange="filter()" value="{{ $allbrands->firstWhere('id', $brand_id)->slug }}" checked>
                                                    <label
                                                        for="brandRadioDefault">{{ $allbrands->firstWhere('id', $brand_id)->name }}
                                                    </label>
                                                </span>
                                            </div>
                                        </div>
                                        @endif
                                        @foreach ($allbrands as $key => $brand)
                                            @if(isset($brand_id) && $brand_id == $brand->id)
                                            <div class="d-none"></div>
                                            @else
                                            <div id="brand-radio">
                                                <div class="brand-radio @if ($key > 10) hide @endif">
                                                    <span class="category-wrap">
                                                        <input type="checkbox" id="brandRadio{{ $key }}" name="brand" @if(Route::currentRouteName() === 'products.brand') onchange="window.location.href = '{{ route('products.brand', $brand->slug) }}'" @else onchange="filter()" @endif value="{{ $brand->slug }}" @isset ($brand_id) @if ($brand_id == $brand->id) checked @endif @endisset />
                                                        <label for="brandRadio{{ $key }}">{{ $brand->name }}</label>
                                                    </span>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach

                                        <div class="mt-3">
                                            <a class="show-more" href="javascript:void(0)" id="show-more">
                                                <span><i class="fa-solid fa-plus"></i></span>
                                                <span> See More</span>
                                            </a>
                                            <a class="show-less hide" href="javascript:void(0)" id="show-less">
                                                <span><i class="fa-solid fa-minus"></i></span>
                                                <span>See Less</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <!-- static filter by rating start -->
                                <div class="bg-white sidebar-box">
                                    <div class="fs-15 fw-600 py-3  collaps-btn">
                                        <span> {{ ('Ratings') }}</span>
                                        <span>
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </span>
                                    </div>
                                    <div class="py-3 collaps-content">
                                        <div class="d-flex flex-column ">
                                            <div class="filter-by-rating">
                                                <input type="checkbox" name="rating" value="5" onchange="filter()" id="ratingFive" @isset($rating) @if($rating == 5) checked @endif @endisset>
                                                <label for="ratingFive">
                                                    <img src="{{ asset('public/assets/img/rating.png') }}"
                                                        alt="Rating 5">
                                                </label>
                                            </div>
                                            <div class="filter-by-rating">
                                                <input type="checkbox" name="rating" value="4" onchange="filter()" id="ratingFour" @isset($rating) @if($rating == 4) checked @endif @endisset>
                                                <label for="ratingFour">
                                                    <img src="{{ asset('public/assets/img/rating(4).png') }}"
                                                        alt="Rating 4">
                                                </label>
                                            </div>
                                            <div class="filter-by-rating">
                                                <input type="checkbox" name="rating" value="3" onchange="filter()" id="ratingThree" @isset($rating) @if($rating == 3) checked @endif @endisset>
                                                <label for="ratingThree">
                                                    <img src="{{ asset('public/assets/img/rating(3).png') }}"
                                                        alt="Rating 3">
                                                </label>
                                            </div>
                                            <div class="filter-by-rating">
                                                <input type="checkbox" name="rating" value="2" onchange="filter()" id="ratingTwo" @isset($rating) @if($rating == 2) checked @endif @endisset>
                                                <label for="ratingTwo">
                                                    <img src="{{ asset('public/assets/img/rating(2).png') }}"
                                                        alt="Rating 2">
                                                </label>
                                            </div>
                                            <div class="filter-by-rating">
                                                <input type="checkbox" name="rating" value="1" onchange="filter()" id="ratingOne" @isset($rating) @if($rating == 1) checked @endif @endisset>
                                                <label for="ratingOne">
                                                    <img src="{{ asset('public/assets/img/rating(1).png') }}"
                                                        alt="Rating 1">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- static filter by rating end -->
                                {{--<!-- static filter by Size start -->
                                <div class="bg-white sidebar-box  ">
                                    <div class="fs-15 fw-600 py-3  collaps-btn">
                                        <span> {{ ('Size') }}</span>
                                        <span>
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </span>
                                    </div>
                                    <div class="py-3 collaps-content">
                                        <form action="">
                                            <div class="d-flex flex-column ">
                                                <div class="filter-by-rating">
                                                    <input type="checkbox" name="size" id="fortyTwo">
                                                    <label for="fortyTwo">
                                                        42-44
                                                    </label>
                                                </div>
                                                <div class="filter-by-rating">
                                                    <input type="checkbox" name="size" id="fortyOne">
                                                    <label for="fortyOne">
                                                        41
                                                    </label>
                                                </div>
                                                <div class="filter-by-rating">
                                                    <input type="checkbox" name="size" id="forty">
                                                    <label for="forty">
                                                        40
                                                    </label>
                                                </div>
                                                <div class="filter-by-rating">
                                                    <input type="checkbox" name="size" id="thiryNine">
                                                    <label for="thiryNine">
                                                        39
                                                    </label>
                                                </div>
                                                <div class="filter-by-rating">
                                                    <input type="checkbox" name="size" id="thiryEight">
                                                    <label for="thiryEight">
                                                        38
                                                    </label>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- static filter by Size end -->
                                <!-- static filter by clothing style start -->
                                <div class="bg-white sidebar-box  ">
                                    <div class="fs-15 fw-600 py-3  collaps-btn">
                                        <span> {{ ('Clothing Style') }}</span>
                                        <span>
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </span>
                                    </div>
                                    <div class="py-3 collaps-content">
                                        <form action="">
                                            <div class="d-flex flex-column ">
                                                <div class="filter-by-rating">
                                                    <input type="checkbox" id="casual">
                                                    <label for="casual">
                                                        Casual
                                                    </label>
                                                </div>
                                                <div class="filter-by-rating">
                                                    <input type="checkbox" id="basic">
                                                    <label for="basic">
                                                        Basic
                                                    </label>
                                                </div>
                                                <div class="filter-by-rating">
                                                    <input type="checkbox" id="sport">
                                                    <label for="sport">
                                                        Sport
                                                    </label>
                                                </div>
                                                <div class="filter-by-rating">
                                                    <input type="checkbox" id="korean">
                                                    <label for="korean">
                                                        Korean
                                                    </label>
                                                </div>
                                                <div class="filter-by-rating">
                                                    <input type="checkbox" id="business">
                                                    <label for="business">
                                                        Business
                                                    </label>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                @if (get_setting('color_filter_activation'))
                                    <div class="bg-white sidebar-box  mb-3">
                                        <div class="fs-15 fw-600 py-3 ">
                                            {{ ('Filter by color') }}
                                        </div>
                                        <div class="py-3">
                                            <div class="aiz-radio-inline">
                                                @foreach ($colors as $key => $color)
                                                    <label class="aiz-megabox pl-0 mr-2" data-toggle="tooltip"
                                                        data-title="{{ $color->name }}">
                                                        <input type="radio" name="color" value="{{ $color->code }}"
                                                            onchange="filter()"
                                                            @if (isset($selected_color) && $selected_color == $color->code) checked @endif>
                                                        <span
                                                            class="aiz-megabox-elem  d-flex align-items-center justify-content-center p-1 mb-2">
                                                            <span class="size-30px d-inline-block "
                                                                style="background: {{ $color->code }};"></span>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <button type="submit" class="btn btn-styled btn-block btn-base-4">Apply filter</button> --}}
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-9 mobile_thin_space">

                        <div class="text-left">
                            <div class="d-flex align-items-start justify-content-between justify-content-md-end">
                                <div class="form-group w-200px ml-0  sort_by_holder d-flex pl-2 align-items-center">
                                    <label class="mb-0 pr-2 " style="white-space:nowrap">{{ ('Sort by') }}</label>
                                    <select class="form-control form-control-sm aiz-selectpicker" name="sort_by"
                                        onchange="filter()">
                                        <option value="newest"
                                            @isset($sort_by) @if ($sort_by == 'newest') selected @endif @endisset>
                                            {{ ('Newest') }}</option>
                                        <option value="oldest"
                                            @isset($sort_by) @if ($sort_by == 'oldest') selected @endif @endisset>
                                            {{ ('Oldest') }}</option>
                                        <option value="featured"
                                            @isset($sort_by) @if ($sort_by == 'featured') selected @endif @endisset>
                                            {{ ('Featured') }}</option>
                                        <option value="price-asc"
                                            @isset($sort_by) @if ($sort_by == 'price-asc') selected @endif @endisset>
                                            {{ ('Price low to high') }}</option>
                                        <option value="price-desc"
                                            @isset($sort_by) @if ($sort_by == 'price-desc') selected @endif @endisset>
                                            {{ ('Price high to low') }}</option>
                                    </select>
                                </div>
                                <div class="cat_title_holder">
                                    <div class="d-xl-none ml-auto ml-xl-3 mr-0 form-group filter_holder">
                                        <button type="button" class="btn btn-icon p-0" data-toggle="class-toggle"
                                            data-target=".aiz-filter-sidebar">
                                            <i class="la la-filter la-2x"></i>
                                        </button>
                                     </div>
                                    <input type="hidden" name="keyword" value="{{ $query }}">
                                </div>



                            </div>
                        </div>
                        <input type="hidden" name="min_price" value="">
                        <input type="hidden" name="max_price" value="">
                        <!--oldClass=> replace by product-grid-container=>  row gutters-5 row-cols-xxl-4 row-cols-xl-3 row-cols-lg-4 row-cols-md-3 row-cols-2 -->
                        @if(count($products) > 0)
                        <div class="product-grid-container mobile_thin_space_product" id="AjaxinateContainer">
                            @foreach ($products as $key => $product)
                                <div class="">
                                    @include(config('app.theme') . 'frontend.partials.product_box_1', [
                                        'product' => $product,
                                    ])
                                </div>
                            @endforeach
                        </div>
                        <div class="aiz-pagination aiz-pagination-center mt-4" id="AjaxinatePagination" style="text-align: center; font-size: 14px;">
                            @if($nextPageUrl!='')
                                <a href="{{ $nextPageUrl }}">Loading More</a>
                            @endif
                        </div>
                        @else
                            <div class="container py-4 px-0" style="background-color: #ddd;">
                                <p class="text-center my-0 mx-auto p-0 text-danger">{{ ('No Products Found') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        // document.addEventListener("DOMContentLoaded", function() {
        //     if('{{ $platform }}' !== 'iOS') {
        //         var endlessScroll = new Ajaxinate({
        //             container: "#AjaxinateContainer",
        //             pagination: "#AjaxinatePagination",
        //             method: "scroll",
        //             loadingText: 'Loading..',
        //             scrollOffset: 100,
        //             scrollDelay: 500,
        //         });
        //     }
        // });

        function filter() {
            $('#search-form').submit();
        }

        function rangefilter(arg) {
            $('input[name=min_price]').val(arg[0]);
            $('input[name=max_price]').val(arg[1]);
            $('#pMinPrice').val(arg[0]);
            $('#pMaxPrice').val(arg[1]);
            filter();
        }

        function priceRangeFilter() {
            $('input[name=min_price]').val($('#pMinPrice').val());
            $('input[name=max_price]').val($('#pMaxPrice').val());
        }

        // product sidebar
        const collapsBtns = document.querySelectorAll('.collaps-btn');
        const collapsContents = document.querySelectorAll('.collaps-content');

        collapsBtns.forEach(function(btn, index) {
            btn.addEventListener('click', function() {
                collapsContents[index].classList.toggle("collaps-active");
                collapsBtns[index].classList.toggle("chevron-sign");
            });
        });

        function toggleVisibility() {
            const hiddenItems = document.querySelectorAll('.hasHide');
            const toggleIcon = document.getElementById('toggleIcon');
            const toggleText = document.getElementById('toggleText');

            hiddenItems.forEach(item => {
                if (item.classList.contains('hide')) {
                    item.classList.remove('hide');
                } else {
                    item.classList.add('hide');
                }
            });

            if (toggleText.textContent === 'See More') {
                toggleText.textContent = 'See Less';
                toggleIcon.innerHTML = '<i class="fa-solid fa-minus"></i>';
            } else {
                toggleText.textContent = 'See More';
                toggleIcon.innerHTML = '<i class="fa-solid fa-plus"></i>';
            }
        }

    </script>
@endsection
