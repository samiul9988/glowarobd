@extends(config('app.theme') . 'frontend.layouts.app')
@php
    $categoryfilePath = storage_path('app/public/categories/category.json');
    if (file_exists($categoryfilePath)) {
        $jsonData = file_get_contents($categoryfilePath);
        $categories = collect(json_decode($jsonData));
        // dd('From cache');
    } else {
        $categories = collect(\App\Models\Category::all());
        if (!file_exists(storage_path('app/public/categories'))) {
            mkdir(storage_path('app/public/categories'), 0775, true);
        }
        file_put_contents($categoryfilePath, $categories->toJson());
        // dd('From database');
    }

    $brandFilePath = storage_path('app/public/brands/brand.json');
    if (file_exists($brandFilePath)) {
        $jsonData = file_get_contents($brandFilePath);
        $allbrands = collect(json_decode($jsonData));
        // dd('From cache');
    } else {
        $allbrands = collect(\App\Models\Brand::all());
        if (!file_exists(storage_path('app/public/brands'))) {
            mkdir(storage_path('app/public/brands'), 0775, true);
        }
        file_put_contents($brandFilePath, $allbrands->toJson());
        // dd('From database');
    }
@endphp
@if (isset($category_id))
    @php
        $meta_title =
            $categories->firstWhere('id', $category_id)->meta_title ??
            $categories->firstWhere('id', $category_id)?->name;
        $meta_description =
            $categories->firstWhere('id', $category_id)->meta_description ??
            $categories->firstWhere('id', $category_id)?->name;
    @endphp
@elseif (isset($brand_id))
    @php
        $meta_title =
            $allbrands->firstWhere('id', $brand_id)->meta_title ?? $allbrands->firstWhere('id', $brand_id)?->name;
        $meta_description =
            $allbrands->firstWhere('id', $brand_id)->meta_description ?? $allbrands->firstWhere('id', $brand_id)?->name;
    @endphp
@else
    @php
        $meta_title = get_setting('meta_title');
        $meta_description = get_setting('meta_description');
    @endphp
@endif

@section('meta_title'){{ $meta_title }}@stop
@section('meta_description'){{ $meta_description }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $meta_title }}">
    <meta itemprop="description" content="{{ $meta_description }}">

    <!-- Twitter Card data -->
    <meta name="twitter:title" content="{{ $meta_title }}">
    <meta name="twitter:description" content="{{ $meta_description }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $meta_title }}" />
    <meta property="og:description" content="{{ $meta_description }}" />
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

        .category-box-wrapper {
            max-width: 100%;
            overflow: scroll;
            gap: 7px;
        }

        .category-box-wrapper .category-item {
            color: #000;
            border-radius: 25px;
            border: 1px solid #555454;
        }

        .category-box-wrapper::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none !important;
        }
    </style>

    <section class="mb-4 pt-2">
        <div class="container sm-px-0">
            <form class="" id="search-form" action="" method="GET">
                @foreach ($customFields as $field)
                    <input type="hidden" name="{{ $field->slug }}" value="{{ request()->query($field->slug) }}">
                @endforeach
                <div class="row">
                    <div class="col-xl-3">
                        <div class="aiz-filter-sidebar collapse-sidebar-wrap sidebar-xl sidebar-right z-1035">
                            <div class="overlay overlay-fixed dark c-pointer" data-toggle="class-toggle"
                                data-target=".aiz-filter-sidebar" data-same=".filter-sidebar-thumb"></div>
                            <div class="collapse-sidebar c-scrollbar-light text-left">
                                <div class="d-flex d-xl-none justify-content-between align-items-center pl-3 border-bottom">
                                    <h3 class="h6 mb-0 fw-600">{{ ('Filters') }}</h3>
                                    <button type="button" class="btn btn-sm p-2 filter-sidebar-thumb"
                                        data-toggle="class-toggle" data-target=".aiz-filter-sidebar">
                                        <i class="las la-times la-2x"></i>
                                    </button>
                                </div>
                                <div class="bg-white shadow-sm rounded">
                                    <div class="fs-15 fw-600 p-3 border-bottom">
                                        {{ ('Categories') }}
                                    </div>
                                    <div class="p-3">
                                        <ul id="categories" class="list-unstyled">
                                            @foreach ($categories->where('level', 0)->sortByDesc('order_level')->all() as $key => $item)
                                                @php
                                                    $allchilds = \App\Utility\CategoryUtility::flat_children($item->id);
                                                    $childsSlugs = [];
                                                    foreach ($allchilds as $child) {
                                                        $childsSlugs[] = $child['slug'];
                                                    }
                                                    $isItem = in_array(request()->segment(2), $childsSlugs);
                                                    $subcategories = \App\Utility\CategoryUtility::get_immediate_children_ids(
                                                        $item->id,
                                                    );
                                                @endphp
                                                <li class="mb-2 ml-2">
                                                    <a href="{{ route('products.category', $item->slug) }}"
                                                        class="text-reset fs-14 @if (count($subcategories) > 0) has-submenu @endif {{ $isItem ? 'item_active' : '' }}{{ request()->segment(2) == $item->slug ? 'item_active' : '' }}"
                                                        @if (count($subcategories) > 0) data-toggle="collapse" data-target="#{{ Str::slug($item->slug) }}-collapse" aria-expanded="false" @endif>
                                                        {{ $item->name }}
                                                    </a>
                                                    @if (count($subcategories) > 0)
                                                        <div class="collapse {{ $isItem ? 'show' : '' }}{{ request()->segment(2) == $item->slug ? 'show' : '' }}"
                                                            id="{{ Str::slug($item->slug) }}-collapse">
                                                            <ul
                                                                class="btn-toggle-nav list-unstyled fw-normal mr-2 pb-1 small">
                                                                @foreach ($subcategories as $key => $first_level_id)
                                                                    @php
                                                                        $firstLevelCategory = $categories->firstWhere(
                                                                            'id',
                                                                            $first_level_id,
                                                                        );
                                                                        $firstLevelSubcategories = \App\Utility\CategoryUtility::get_immediate_children_ids(
                                                                            $firstLevelCategory->id,
                                                                        );

                                                                        $firstAllChilds = \App\Utility\CategoryUtility::flat_children(
                                                                            $first_level_id,
                                                                        );
                                                                        $firstChildsSlugs = [];
                                                                        foreach ($firstAllChilds as $first) {
                                                                            $firstChildsSlugs[] = $first['slug'];
                                                                        }
                                                                        $isFirstChild = in_array(
                                                                            request()->segment(2),
                                                                            $firstChildsSlugs,
                                                                        );
                                                                    @endphp
                                                                    <li
                                                                        class="mb-2 ml-2 @if ($loop->first) mt-2 @endif">
                                                                        <a href="{{ route('products.category', $firstLevelCategory->slug) }}"
                                                                            class="text-reset fs-14 @if (count($firstLevelSubcategories) > 0) has-submenu @endif {{ $isFirstChild ? 'item_active' : '' }}{{ request()->segment(2) == $firstLevelCategory->slug ? 'item_active' : '' }}"
                                                                            @if (count($firstLevelSubcategories) > 0) data-toggle="collapse" data-target="#{{ Str::slug($firstLevelCategory->slug) }}-collapse" aria-expanded="false" @endif>{{ $firstLevelCategory->name }}</a>

                                                                        @if (count($subcategories) > 0)
                                                                            <div class="collapse {{ $isFirstChild ? 'show' : '' }}"
                                                                                id="{{ Str::slug($firstLevelCategory->slug) }}-collapse">
                                                                                <ul
                                                                                    class="btn-toggle-nav list-unstyled fw-normal mr-2 pb-1 small">
                                                                                    @foreach ($firstLevelSubcategories as $key => $second_level_id)
                                                                                        @php
                                                                                            $secondLevelCategory = $categories->firstWhere(
                                                                                                'id',
                                                                                                $second_level_id,
                                                                                            );
                                                                                        @endphp
                                                                                        <li
                                                                                            class="mb-2 ml-2 @if ($loop->first) mt-2 @endif">
                                                                                            <a href="{{ route('products.category', $secondLevelCategory->slug) }}"
                                                                                                class="text-reset fs-14 {{ request()->segment(2) == $secondLevelCategory->slug ? 'item_active' : '' }}">{{ $secondLevelCategory->name }}</a>
                                                                                        </li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            </div>
                                                                        @endif
                                                                        <!-- </li> -->
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                <div class="bg-white shadow-sm rounded">
                                    <div class="fs-15 fw-600 p-3 border-bottom">
                                        {{ ('Brands') }}
                                    </div>
                                    <div class="p-3" id="style-3">
                                        @foreach ($allbrands as $key => $brand)
                                            <div id="brand-radio">
                                                <div class="brand-radio @if ($key > 10) hide @endif">
                                                    <input type="radio" id="brandRadio{{ $key }}" name="brand"
                                                        onchange="filter()" value="{{ $brand->slug }}"
                                                        @isset($brand_id) @if ($brand_id == $brand->id) checked @endif @endisset>
                                                    <label for="brandRadio{{ $key }}">{{ $brand->name }}</label>
                                                </div>
                                            </div>
                                        @endforeach

                                        <div class="mt-3">
                                            <a class="show-more" href="javascript:void(0)" id="show-more">Show More</a>
                                            <a class="show-less hide" href="javascript:void(0)" id="show-less">Show Less</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white shadow-sm rounded mb-3">
                                    <div class="fs-15 fw-600 p-3 border-bottom">
                                        {{ ('Filter By Price') }}
                                    </div>
                                    <div class="p-3">
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
                                                    <span class="range-slider-value value-low fs-14 fw-600 opacity-70"
                                                        @if (isset($min_price)) data-range-value-low="{{ $min_price }}" @elseif($products->min('unit_price') > 0)
                                                    data-range-value-low="{{ $products->min('unit_price') }}"
                                                    @else
                                                    data-range-value-low="0" @endif
                                                        id="input-slider-range-value-low"></span>
                                                </div>
                                                <div class="col-6 text-right">
                                                    <span class="range-slider-value value-high fs-14 fw-600 opacity-70"
                                                        @if (isset($max_price)) data-range-value-high="{{ $max_price }}" @elseif($products->max('unit_price') > 0)
                                                    data-range-value-high="{{ $products->max('unit_price') }}"
                                                    @else
                                                    data-range-value-high="0" @endif
                                                        id="input-slider-range-value-high"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @foreach ($attributes as $attribute)
                                    <div class="bg-white shadow-sm rounded mb-3">
                                        <div class="fs-15 fw-600 p-3 border-bottom">
                                            {{ ('Filter by') }} {{ $attribute->getTranslation('name') }}
                                        </div>
                                        <div class="p-3">
                                            <div class="aiz-checkbox-list">
                                                @foreach ($attribute->attribute_values as $attribute_value)
                                                    <label class="aiz-checkbox">
                                                        <input type="checkbox" name="selected_attribute_values[]"
                                                            value="{{ $attribute_value->value }}"
                                                            @if (in_array($attribute_value->value, $selected_attribute_values)) checked @endif
                                                            onchange="filter()">
                                                        <span class="aiz-square-check"></span>
                                                        <span>{{ $attribute_value->value }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if (get_setting('color_filter_activation'))
                                    <div class="bg-white shadow-sm rounded mb-3">
                                        <div class="fs-15 fw-600 p-3 border-bottom">
                                            {{ ('Filter by color') }}
                                        </div>
                                        <div class="p-3">
                                            <div class="aiz-radio-inline">
                                                @foreach ($colors as $key => $color)
                                                    <label class="aiz-megabox pl-0 mr-2" data-toggle="tooltip"
                                                        data-title="{{ $color->name }}">
                                                        <input type="radio" name="color" value="{{ $color->code }}"
                                                            onchange="filter()"
                                                            @if (isset($selected_color) && $selected_color == $color->code) checked @endif>
                                                        <span
                                                            class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center p-1 mb-2">
                                                            <span class="size-30px d-inline-block rounded"
                                                                style="background: {{ $color->code }};"></span>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- <button type="submit" class="btn btn-styled btn-block btn-base-4">Apply filter</button> --}}
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-9 mobile_thin_space">

                        <section class="collection-banner mb-4">
                            @php
                                $slug = request()->route('category_slug');
                                $category = $categories->firstWhere('slug', $slug);
                                $sub_categories = [];
                                if ($category) {
                                    // $sub_categories = \App\Models\Category::where('parent_id', $category->id)
                                    //                     ->select('id','name','slug','parent_id')->get()
                                    //                     ->toArray();
                                    // dd($sub_categories);
                                    $sub_categories = $categories->where('parent_id', $category->id);
                                    $page_banner = json_decode($category->page_banner, true);
                                    // dd($page_banner);
                                }
                            @endphp
                            <div class="collectionImage">
                                @if (isset($brand_id))
                                    @php
                                        $brand = $allbrands->firstWhere('id', $brand_id);
                                        // $brand = \App\Models\Brand::find($brand_id);
                                        // dd($brand);
                                    @endphp
                                    <img src="" data-src="{{ uploaded_asset($brand->page_banner ?? '') }}"
                                        height="auto" alt="" class="img-fluid img lazyload"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                        style="height: 250px; width: 100%; object-fit:fill;">
                                @else
                                    <img src=""
                                        data-src="{{ $agent->isMobile() ? uploaded_asset($page_banner['mobile'] ?? '') : uploaded_asset($page_banner['web'] ?? '') }}"
                                        height="auto" alt="" class="img-fluid img lazyload"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                        style="height: 250px; width: 100%; object-fit:fill;">
                                @endif
                            </div>
                            <div class="collectionContent">
                                <div class="innerContent">
                                    @if (isset($brand_id))
                                        <h2 class="text-capitalize">{{ $brand->name ?? '' }}</h2>
                                    @elseif($category && $category->discount)
                                        <h2 class="text-capitalize">{{ $category->name ?? '' }}</h2>
                                        <h4 class="text-uppercase">Get
                                            {{ $category->discount_type == 'percent' ? $category->discount . '%' : single_price($category->discount) }}
                                            off</h4>
                                    @else
                                        <h2 class="text-capitalize">
                                            {{ isset($category->name) ? $category->name : (request()->has('keyword') ? 'Search results' : translate('All Products')) }}
                                        </h2>
                                    @endif
                                </div>
                            </div>
                        </section>

                        <div class="text-left">
                            <div class="d-block d-xl-flex align-items-center">
                                <div class="cat_title_holder">
                                    {{-- <h1 class="h6 fw-600 text-body">
                                    @if (isset($category_id))
                                    {{ collect($categories->firstWhere('id', $category_id)->category_translations)->first()->name }}
                                    @elseif(isset($query))
                                    {{ ('Search result for ') }}"{{ $query }}"
                                    @else
                                    {{ ('All Products') }}
                                    @endif
                                </h1> --}}
                                    <input type="hidden" name="keyword" value="{{ $query }}">
                                </div>
                                {{-- <div class="form-group ml-auto mr-0 w-200px d-none d-xl-block">
                                    @if (Route::currentRouteName() != 'products.brand')
                                        <label class="mb-0 opacity-50">{{ ('Brands')}}</label>
                            <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" name="brand" onchange="filter()">
                                <option value="">{{ ('All Brands')}}</option>
                                @foreach ($allbrands as $brand)
                                <option value="{{ $brand->slug }}" @isset($brand_id) @if ($brand_id == $brand->id) selected @endif @endisset>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            @endif
                        </div> --}}
                                @if (count($sub_categories))
                                    <div class="d-block d-md-none pb-4">
                                        <div class="d-flex align-items-center category-box-wrapper">
                                            @foreach ($sub_categories as $sub_category)
                                                <a href="{{ route('products.category', $sub_category->slug) }}"
                                                    class=" px-3 py-2 category-item" style="white-space:nowrap;">
                                                    {{ $sub_category->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <div class="form-group w-200px ml-0 ml-auto sort_by_holder">
                                    <div class="d-flex align-items-center" style="gap: 5px;">
                                        <label class="mb-0 opacity-50"
                                            style="white-space: nowrap;">{{ ('Sort by') }}</label>
                                        <select class="form-control form-control-sm aiz-selectpicker" name="sort_by"
                                            onchange="filter()">
                                            <option value="newest"
                                                @isset($sort_by) @if ($sort_by == 'newest') selected @endif @endisset>
                                                {{ ('Newest') }}</option>
                                            <option value="oldest"
                                                @isset($sort_by) @if ($sort_by == 'oldest') selected @endif @endisset>
                                                {{ ('Oldest') }}</option>
                                            <option value="price-asc"
                                                @isset($sort_by) @if ($sort_by == 'price-asc') selected @endif @endisset>
                                                {{ ('Price low to high') }}</option>
                                            <option value="price-desc"
                                                @isset($sort_by) @if ($sort_by == 'price-desc') selected @endif @endisset>
                                                {{ ('Price high to low') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-xl-none ml-auto ml-xl-3 mr-0 form-group align-self-end filter_holder">
                                    <button type="button" class="btn btn-icon p-0" data-toggle="class-toggle"
                                        data-target=".aiz-filter-sidebar">
                                        <i class="la la-filter la-2x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="min_price" value="">
                        <input type="hidden" name="max_price" value="">

                        <div class="row gutters-5 row-cols-xxl-4 row-cols-xl-3 row-cols-lg-4 row-cols-md-3 row-cols-2 mobile_thin_space_product"
                            id="AjaxinateContainerr">
                            @foreach ($products as $key => $product)
                                <div class="col">
                                    @include(config('app.theme') . 'frontend.partials.product_box_1', [
                                        'product' => $product,
                                    ])
                                </div>
                            @endforeach
                        </div>
                        <div class="aiz-pagination aiz-pagination-center mt-4 mb-5" id="AjaxinatePaginationn"
                            style="text-align: center; font-size: 14px;">
                            @if ($nextPageUrl != '')
                                <a href="{{ $nextPageUrl }}"><i class="las la-spinner la-spin la-3x"></i></a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        function filter() {
            $('#search-form').submit();
        }

        function rangefilter(arg) {
            $('input[name=min_price]').val(arg[0]);
            $('input[name=max_price]').val(arg[1]);
            filter();
        }
    </script>

    <script>
        let nextPageUrl = @json($nextPageUrl);
        let isLoading = false;
        loadMoreProducts();

        async function loadMoreProducts() {
            if (!nextPageUrl || isLoading) return;

            isLoading = true; // Lock

            await $.ajax({
                url: nextPageUrl,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#AjaxinatePaginationn').html('<i class="las la-spinner la-spin la-3x"></i>');
                },
                success: function(response) {
                    if (response.view) {
                        $('#AjaxinateContainerr').append(response.view);
                    }

                    nextPageUrl = response.next_page_url;
                    isLoading = false; // Unlock

                    if (nextPageUrl) {
                        $('#AjaxinatePaginationn').html('<i class="las la-spinner la-spin la-3x"></i>');
                    } else {
                        $('#AjaxinatePaginationn').html('');
                        nextPageUrl = null; // No more pages
                    }
                },
                error: function() {
                    isLoading = false; // Unlock on error too
                    console.error("Could not load more products.");
                }
            });
        }

        $(window).on('scroll', async function() {
            const scrollBottom = $(window).scrollTop() + $(window).height();
            const docHeight = $(document).height();

            if (scrollBottom + 1200 >= docHeight && nextPageUrl) {
                await loadMoreProducts();
            }
        });
    </script>

@endsection
