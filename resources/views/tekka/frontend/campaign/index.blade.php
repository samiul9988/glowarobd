@extends(config('app.theme') . 'frontend.layouts.app')
@php
    $meta_title = get_setting('meta_title');
    $meta_description = get_setting('meta_description');
@endphp

@section('meta')
<x-seo />

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
    <style>
        .campaign-card {
            border-radius: 10px;
            overflow: hidden;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .campaign-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            transition: box-shadow 0.3s ease;
        }

        .campaign-card:hover .section-title {
            color: #e2136e;
        }

        .campaign-card img {
            width: 100%;
            height: 250px;
        }

        .card-body p {
            margin-bottom: 5px;
        }

        .section-title {
            font-weight: bold;
        }

        .btn-outline-pink {
            border: 1px solid #e2136e;
            color: #e2136e;
            border-radius: 30px;
            padding: 5px 20px;
        }

        .btn-outline-pink:hover {
            background-color: #e2136e;
            color: white;
        }
    </style>

    <section class="mb-4 pt-2">
        <div class="container sm-px-0">
            <form class="" id="search-form" action="" method="GET">
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
                                            @foreach ($categories as $item)
                                                <li class="mb-2 ml-2">
                                                    <a href="#" class="text-reset fs-14 item_active">
                                                        {{ $item->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-9 mobile_thin_space">
                        <div class="text-left">
                            <div class="d-block d-xl-flex align-items-center">
                                <div class="cat_title_holder">
                                    <input type="hidden" name="keyword" value="{{ @$query }}">
                                </div>
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
                        <div class="row gutters-5 row-cols-xxl-4 row-cols-xl-3 row-cols-lg-4 row-cols-md-3 row-cols-2 mobile_thin_space_product"
                            id="AjaxinateContainer">
                            @foreach ($campaigns as $key => $campaign)
                                <div class="col">
                                    <div class="aiz-card-box border border-light rounded hov-shadow-md mt-1 mb-1 has-transition bg-white product_box">
                                        <div class="position-relative">
                                            <a href="#" class="d-block">
                                                <img class="img-fit lazyload mx-auto h-180px h-md-230px product_long_grid"
                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($campaign->thumbnail) }}"
                                                    alt="{{ $campaign->title }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </a>
                                        </div>
                                        <div class=" pb-md-4 p-2 pb-3 px-md-2 pt-2 text-center product_info">
                                            <h3 class="fw-700 fs-13 text-truncate-2 lh-1-4 mb-2 h-35px">
                                                <a href="#" class="d-block text-reset">{{  $campaign->title }}</a>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="aiz-pagination aiz-pagination-center mt-4" id="AjaxinatePagination"
                            style="text-align: center; font-size: 14px;">
                            @if (@$nextPageUrl != '')
                                <a href="{{ @$nextPageUrl }}">Loading More</a>
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
@endsection
