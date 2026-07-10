@extends(config('app.theme').'frontend.layouts.app')

@if (isset($category_id))
    @php
        $category = \App\Models\Category::find($category_id);
        $meta_title = $category->meta_title;
        $title = $category->name;
        $meta_description = $category->meta_description;
        $banner = uploaded_asset($category->page_banner);
    @endphp
@elseif (isset($brand_id))
    @php
        $brand = \App\Models\Brand::find($brand_id);
        $meta_title = $brand->meta_title;
        $title = $brand->name;
        $meta_description = $brand->meta_description;
        $banner = uploaded_asset($brand->page_banner);
    @endphp
@else
    @php
        $meta_title       = get_setting('meta_title');
        $title            = get_setting('meta_title');
        $meta_description = get_setting('meta_description');
        $banner = '';
    @endphp
@endif

@section('meta_title'){{ $meta_title ?? $title }}@stop
@section('meta_description'){{ $meta_description }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $meta_title ?? $title }}">
    <meta itemprop="description" content="{{ $meta_description }}">

    <!-- Twitter Card data -->
    <meta name="twitter:title" content="{{ $meta_title ?? $title }}">
    <meta name="twitter:description" content="{{ $meta_description }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $meta_title ?? $title }}" />
    <meta property="og:description" content="{{ $meta_description }}" />
@endsection

@section('content')
    @if($banner != '')
    <section class="container p-0">
        <div class="text-center bg-image">
            <img src="{{ $banner }}" class="w-100" alt="{{ $meta_title ?? $title }}">
        </div>
    </section>
    @endif

    <section class="mb-4 pt-3">
        <div class="container px-0">
            <form class="" id="search-form" action="" method="GET">
                <div class="row w-100 m-auto">
                    <div class="col-xl-3 pl-0">
                        <div class="aiz-filter-sidebar collapse-sidebar-wrap sidebar-xl sidebar-right z-1035">
                            <div class="overlay overlay-fixed dark c-pointer" data-toggle="class-toggle" data-target=".aiz-filter-sidebar" data-same=".filter-sidebar-thumb"></div>
                            <div class="collapse-sidebar c-scrollbar-light text-left">
                                <div class="d-flex d-xl-none justify-content-between align-items-center pl-3 border-bottom">
                                    <h3 class="h6 mb-0 fw-600">{{ translate('Filters') }}</h3>
                                    <button type="button" class="btn btn-sm p-2 filter-sidebar-thumb" data-toggle="class-toggle" data-target=".aiz-filter-sidebar" >
                                        <i class="las la-times la-2x"></i>
                                    </button>
                                </div>
                                <div class="bg-white shadow-sm rounded mb-3">
                                    <div class="fs-15 fw-600 p-3 border-bottom">
                                        {{ translate('Categories')}}
                                    </div>
                                    <div class="p-3">
                                        <ul class="list-unstyled">
                                            @if (!isset($category_id))
                                                @foreach (\App\Models\Category::where('level', 0)->get() as $category)
                                                    <li class="mb-2 ml-2">
                                                        <a class="text-reset fs-14" href="{{ route('products.category', $category->slug) }}">{{ $category->name }}</a>
                                                    </li>
                                                @endforeach
                                            @else
                                                <li class="mb-2">
                                                    <a class="text-reset fs-14 fw-600" href="{{ route('search') }}">
                                                        <i class="las la-angle-left"></i>
                                                        {{ translate('All Categories')}}
                                                    </a>
                                                </li>
                                                @if (\App\Models\Category::find($category_id)->parent_id != 0)
                                                    <li class="mb-2">
                                                        <a class="text-reset fs-14 fw-600" href="{{ route('products.category', \App\Models\Category::find(\App\Models\Category::find($category_id)->parent_id)->slug) }}">
                                                            <i class="las la-angle-left"></i>
                                                            {{ \App\Models\Category::find(\App\Models\Category::find($category_id)->parent_id)->name }}
                                                        </a>
                                                    </li>
                                                @endif
                                                <li class="mb-2">
                                                    <a class="text-reset fs-14 fw-600" href="{{ route('products.category', \App\Models\Category::find($category_id)->slug) }}">
                                                        <i class="las la-angle-left"></i>
                                                        {{ \App\Models\Category::find($category_id)->name }}
                                                    </a>
                                                </li>
                                                @foreach (\App\Utility\CategoryUtility::get_immediate_children_ids($category_id) as $key => $id)
                                                    <li class="ml-4 mb-2">
                                                        <a class="text-reset fs-14" href="{{ route('products.category', \App\Models\Category::find($id)->slug) }}">{{ \App\Models\Category::find($id)->name }}</a>
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    </div>
                                </div>

                                <div class="bg-white shadow-sm rounded mb-3" style="display: none;">
                                    <div class="fs-15 fw-600 p-3 border-bottom">
                                        {{ translate('Brands')}}
                                    </div>
                                    <div class="p-3 mb-3">
                                        @foreach (\App\Models\Brand::all() as $key => $brand)
                                        <div id="brand-radio" >
                                            <div class="brand-radio custom-control custom-radio  @if($key > 4) hide @endif">
                                                <input type="radio" id="customRadio{{$key}}" name="brand_radio" class="custom-control-input">
                                                <label class="custom-control-label" for="customRadio{{$key}}">{{$brand->name}}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                    <div class="mt-3">
                                        <a class="show-more" href="#" id="show-more">Show More</a>
                                        <a class="show-less hide" href="#" id="show-less">Show Less</a>
                                    </div>



                                        <!-- <a class="read-more-hide hide" href="#" more-id="1">Read Less</a> -->
                                    </div>
                                </div>

                                <div class="bg-white shadow-sm rounded mb-3">
                                    <div class="fs-15 fw-600 p-3 border-bottom">
                                        {{ translate('Price range')}}
                                    </div>
                                    <div class="p-3">
                                        <div class="aiz-range-slider">
                                            <div
                                                id="input-slider-range"
                                                data-range-value-min="@if(\App\Models\Product::count() < 1) 0 @else {{ \App\Models\Product::min('unit_price') }} @endif"
                                                data-range-value-max="@if(\App\Models\Product::count() < 1) 0 @else {{ \App\Models\Product::max('unit_price') }} @endif"
                                            ></div>

                                            <div class="row mt-2">
                                                <div class="col-6">
                                                    <span class="range-slider-value value-low fs-14 fw-600 opacity-70"
                                                        @if (isset($min_price))
                                                            data-range-value-low="{{ $min_price }}"
                                                        @elseif($products->min('unit_price') > 0)
                                                            data-range-value-low="{{ $products->min('unit_price') }}"
                                                        @else
                                                            data-range-value-low="0"
                                                        @endif
                                                        id="input-slider-range-value-low"
                                                    ></span>
                                                </div>
                                                <div class="col-6 text-right">
                                                    <span class="range-slider-value value-high fs-14 fw-600 opacity-70"
                                                        @if (isset($max_price))
                                                            data-range-value-high="{{ $max_price }}"
                                                        @elseif($products->max('unit_price') > 0)
                                                            data-range-value-high="{{ $products->max('unit_price') }}"
                                                        @else
                                                            data-range-value-high="0"
                                                        @endif
                                                        id="input-slider-range-value-high"
                                                    ></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @foreach ($attributes as $attribute)
                                    <div class="bg-white shadow-sm rounded mb-3">
                                        <div class="fs-15 fw-600 p-3 border-bottom">
                                            {{ translate('Filter by') }} {{ $attribute->getTranslation('name') }}
                                        </div>
                                        <div class="p-3">
                                            <div class="aiz-checkbox-list">
                                                @foreach ($attribute->attribute_values as $attribute_value)
                                                    <label class="aiz-checkbox">
                                                        <input
                                                            type="checkbox"
                                                            name="selected_attribute_values[]"
                                                            value="{{ $attribute_value->value }}" @if (in_array($attribute_value->value, $selected_attribute_values)) checked @endif
                                                            onchange="filter()"
                                                        >
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
                                            {{ translate('Filter by color')}}
                                        </div>
                                        <div class="p-3">
                                            <div class="aiz-radio-inline">
                                                @foreach ($colors as $key => $color)
                                                <label class="aiz-megabox pl-0 mr-2" data-toggle="tooltip" data-title="{{ $color->name }}">
                                                    <input
                                                        type="radio"
                                                        name="color"
                                                        value="{{ $color->code }}"
                                                        onchange="filter()"
                                                        @if(isset($selected_color) && $selected_color == $color->code) checked @endif
                                                    >
                                                    <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center p-1 mb-2">
                                                        <span class="size-30px d-inline-block rounded" style="background: {{ $color->code }};"></span>
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

                        {{-- <ul class="breadcrumb bg-transparent p-0">
                            <li class="breadcrumb-item opacity-50">
                                <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                            </li>
                            @if(!isset($category_id))
                                <li class="breadcrumb-item fw-600  text-dark">
                                    <a class="text-reset" href="{{ route('search') }}">"{{ translate('All Categories')}}"</a>
                                </li>
                            @else
                                <li class="breadcrumb-item opacity-50">
                                    <a class="text-reset" href="{{ route('search') }}">{{ translate('All Categories')}}</a>
                                </li>
                            @endif
                            @if(isset($category_id))
                                <li class="text-dark fw-600 breadcrumb-item">
                                    <a class="text-reset" href="{{ route('products.category', $category->slug) }}">"{{ $category->getTranslation('name') }}"</a>
                                </li>
                            @endif
                        </ul> --}}

                        <div class="text-left">
                            <div class="d-block d-xl-flex align-items-center">
                                <div class="cat_title_holder">
                                    <h1 class="h6 fw-600 text-body">
                                        @if(isset($category_id))
                                            {{ $category->getTranslation('name') }}
                                            <br>
                                            @if(isset($brand_id))
                                                <div class="btn-group btn-group-sm mt-2" role="group" aria-label="Filter">
                                                    <button type="button" class="btn btn-primary text-uppercase">{{ \App\Models\Brand::find($brand_id)->name }}</button>
                                                    <a href="{{ request()->fullUrlWithQuery(['brand' => null]) }}" class="btn btn-primary"><i class="la la-close"></i></a>
                                                </div>
                                            @endif
                                        @elseif(isset($query))
                                            {{ translate('Search result for ') }}"{{ $query }}"
                                        @else
                                            {{ translate('All Products') }} {{ Route::currentRouteName() == 'products.brand' ? 'by brand' : (Route::currentRouteName() == 'products.tags' ? 'by tag' : '') }}
                                            <br>
                                            @if(isset($tag))
                                                <div class="btn-group btn-group-sm mt-2" role="group" aria-label="Filter">
                                                    <button type="button" class="btn btn-primary text-uppercase">{{ $tag }}</button>
                                                    <a href="{{ route('search') }}" class="btn btn-primary"><i class="la la-close"></i></a>
                                                </div>
                                            @endif
                                            @if(isset($brand_id))
                                                <div class="btn-group btn-group-sm mt-2" role="group" aria-label="Filter">
                                                    <button type="button" class="btn btn-primary text-uppercase">{{ \App\Models\Brand::find($brand_id)->name }}</button>
                                                    <a href="{{ route('search') }}" class="btn btn-primary"><i class="la la-close"></i></a>
                                                </div>
                                            @endif
                                        @endif
                                    </h1>
                                    <input type="hidden" name="keyword" value="{{ $query }}">
                                </div>
                                <div class="form-group ml-auto mr-0 w-200px d-none d-xl-block">
                                    @if (Route::currentRouteName() != 'products.brand')
                                        <label class="mb-0 opacity-50">{{ translate('Brands')}}</label>
                                        <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" name="brand" onchange="filter()">
                                            <option value="">{{ translate('All Brands')}}</option>
                                            @foreach (\App\Models\Brand::all() as $brand)
                                                <option value="{{ $brand->slug }}" @isset($brand_id) @if ($brand_id == $brand->id) selected @endif @endisset>{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                                <div class="form-group w-200px ml-0 ml-xl-3 sort_by_holder">
                                    <label class="mb-0 opacity-50">{{ translate('Sort by')}}</label>
                                    <select class="form-control form-control-sm aiz-selectpicker" name="sort_by" onchange="filter()">
                                        <option value="stock-desc" @isset($sort_by) @if ($sort_by == 'stock-desc') selected @endif @endisset>{{ translate('Featured')}}</option>
                                        <option value="newest" @isset($sort_by) @if ($sort_by == 'newest') selected @endif @endisset>{{ translate('Newest')}}</option>
                                        <option value="oldest" @isset($sort_by) @if ($sort_by == 'oldest') selected @endif @endisset>{{ translate('Oldest')}}</option>
                                        <option value="price-asc" @isset($sort_by) @if ($sort_by == 'price-asc') selected @endif @endisset>{{ translate('Price low to high')}}</option>
                                        <option value="price-desc" @isset($sort_by) @if ($sort_by == 'price-desc') selected @endif @endisset>{{ translate('Price high to low')}}</option>
                                    </select>
                                </div>
                                <div class="d-xl-none ml-auto ml-xl-3 mr-0 form-group align-self-end filter_holder">
                                    <button type="button" class="btn btn-icon p-0" data-toggle="class-toggle" data-target=".aiz-filter-sidebar">
                                        <i class="la la-filter la-2x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="min_price" value="">
                        <input type="hidden" name="max_price" value="">
                        <div class="row gutters-5 row-cols-xxl-4 row-cols-xl-3 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1 mobile_thin_space_product">
                            @foreach ($products as $key => $product)
                                <div class="col">
                                    @include(config('app.theme').'frontend.partials.product_box_1',['product' => $product])
                                </div>
                            @endforeach
                        </div>
                        <div class="aiz-pagination aiz-pagination-center mt-4">
                            {{ $products->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        function filter(){
            $('#search-form').submit();
        }
        function rangefilter(arg){
            $('input[name=min_price]').val(arg[0]);
            $('input[name=max_price]').val(arg[1]);
            filter();
        }
    </script>
@endsection
