@php
    $currencyfilePath = storage_path('app/public/currencies/currency.json');
    if (file_exists($currencyfilePath)) {
        $jsonData = file_get_contents($currencyfilePath);
        $currencies = collect(json_decode($jsonData, true));
    } else {
        $currencies = collect(\App\Models\Currency::all()->toArray());
    }
    $appPrice = getMinimumPriceByVariant($detailedProduct, $detailedProduct->stocks->first(), 'app', 1, $currentlyAuthenticatedUser);
    $webPrice = getMinimumPriceByVariant($detailedProduct, $detailedProduct->stocks->first(), 'web', 1, $currentlyAuthenticatedUser);
@endphp
@extends(config('app.theme').'frontend.layouts.app', ['currentlyAuthenticatedUser' => $currentlyAuthenticatedUser])

@section('meta_title'){{ $detailedProduct->meta_title }}@stop

@section('meta_description'){{ $detailedProduct->meta_description }}@stop

@section('meta_keywords'){{ $detailedProduct->tags }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $detailedProduct->meta_title }}">
    <meta itemprop="description" content="{{ $detailedProduct->meta_description }}">
    <meta itemprop="image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $detailedProduct->meta_title }}">
    <meta name="twitter:description" content="{{ $detailedProduct->meta_description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">
    <meta name="twitter:data1" content="{{ single_price($detailedProduct->unit_price) }}">
    <meta name="twitter:label1" content="Price">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $detailedProduct->meta_title }}" />
    <meta property="og:type" content="og:product" />
    <meta property="og:url" content="{{ route('product', $detailedProduct->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}" />
    <meta property="og:description" content="{{ $detailedProduct->meta_description }}" />
    <meta property="og:site_name" content="{{ get_setting('meta_title') }}" />
    <meta property="og:price:amount" content="{{ single_price($detailedProduct->unit_price) }}" />
    <meta property="product:price:currency" content="{{ $currencies->where('status', 1)->first()['code'] }}" />
    <meta property="fb:app_id" content="{{ env('FACEBOOK_PIXEL_ID') }}">
@endsection
@php
    // dd($customFieldsData);
@endphp
@section('content')
    <style>
        .product_details .priceBox {
            align-items: center;
            margin-inline: 0;
            background: #fff 0 0 no-repeat padding-box;
            border: 1px solid lightgray;
            border-radius: 8px;
            -webkit-box-shadow: 6px 7px 26px -20px #42445a;
            box-shadow: 6px 7px 26px -20px #42445a;
            padding: 15px;
        }

        @media screen and (max-width: 768px) {
            .buynowWrapper {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                z-index: 99;
                background-color: #fff;
                width: 100%;
                padding-block: 10px;
                gap: 10px !important;
                justify-content: center;
                box-shadow: rgba(0, 0, 0, 0.06) 0px 2px 4px 0px inset;
            }
        }
    </style>
    <section class="mb-2 pt-3 product_details">
        <div class="container mobile_thin_padding">
            <div class="bg-white shadow-sm rounded p-3 mobile_top_thin_padding">
                <div class="row">
                    <div class="col-xl-5 col-lg-6 mb-4 mobile_thin_padding">
                        <div class="sticky-top z-3 row gutters-5">
                            @php
                                $photos = explode(',', $detailedProduct->photos);
                            @endphp
                            <div class="col order-1 order-md-2">
                                <div class="aiz-carousel product-gallery" data-nav-for='.product-gallery-thumb' data-fade='true' data-auto-height='true'>
                                    @foreach ($photos as $key => $photo)
                                        <div class="carousel-box img-zoom rounded">
                                            <img
                                                class="img-fluid lazyload"
                                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ uploaded_asset($photo) }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                            >
                                        </div>
                                    @endforeach
                                    @foreach ($detailedProduct->stocks as $key => $stock)
                                        @if ($stock->image != null)
                                            <div class="carousel-box img-zoom rounded">
                                                <img
                                                    class="img-fluid lazyload"
                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($stock->image) }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @if(count($photos) > 1)
                            <div class="col-12 col-md-auto w-md-80px order-2 order-md-1 mt-3 mt-md-0">
                                <div class="aiz-carousel product-gallery-thumb" data-items='5' data-nav-for='.product-gallery' data-vertical='true' data-vertical-sm='false' data-focus-select='true' data-arrows='true'>
                                    @foreach ($photos as $key => $photo)
                                    <div class="carousel-box c-pointer border p-1 rounded">
                                        <img
                                            class="lazyload mw-100 size-50px mx-auto"
                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                            data-src="{{ uploaded_asset($photo) }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                        >
                                    </div>
                                    @endforeach
                                    @foreach ($detailedProduct->stocks as $key => $stock)
                                        @if ($stock->image != null)
                                            <div class="carousel-box c-pointer border p-1 rounded" data-variation="{{ $stock->variant }}">
                                                <img
                                                    class="lazyload mw-100 size-50px mx-auto"
                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($stock->image) }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-xl-7 col-lg-6">
                        <div class="text-left">
                            <h1 class="mb-2 fs-20 fw-600">
                                {{ $detailedProduct->name }}
                            </h1>

                            <div class="row align-items-center">
                                <div class="col-12">
                                    @php
                                        $total = 0;
                                        $total += $detailedProduct->reviews->count();
                                    @endphp
                                    <span class="rating">
                                        {!! renderStarRating($detailedProduct->rating) !!}
                                    </span>
                                    <span class="ml-1 opacity-50">({{ $total }} {{ ('reviews')}})</span>
                                </div>
                                @if ($detailedProduct->est_shipping_days)
                                <div class="col-auto ml">
                                    <small class="mr-2 opacity-50">{{ ('Estimate Shipping Time')}}: </small>{{ $detailedProduct->est_shipping_days }} {{  translate('Days') }}
                                </div>
                                @endif
                            </div>

                            <div class="row align-items-center my-2">
                                <div class="col-auto">
                                    @php
                                        $category = \App\Models\Category::find($detailedProduct->category_id);
                                        $brand = \App\Models\Brand::find($detailedProduct->brand_id);
                                    @endphp
                                    @if($category)
                                    <small class="mr-2 opacity-50">Category: </small>
                                    <a href="{{ route('products.category', $category->slug) }}" class="fw-700 text-secondary">{{ $category->getTranslation('name') }}</a>
                                    @endif
                                </div>

                                <div class="col-auto">
                                    @if($brand)
                                    <small class="mr-2 opacity-50">Brand: </small>
                                    <a href="{{ route('products.brand', $brand->slug) }}" class="fw-700 text-secondary">{{ $brand->getTranslation('name') }}</a>
                                    @endif
                                </div>
                            </div>
                            {{-- @if($checkflashdeal = check_flash_deal_product($detailedProduct)) --}}
                            @php
                                $flash_deal = $detailedProduct->flash_deal_product?->flash_deals ?? null;
                            @endphp
                            @if($checkflashdeal = is_valid_flashdeal($flash_deal))
                            <div class="d-flex w-md-auto flash_sale_count align-items-center">
                                <h3 class="d-flex mb-0 mr-3">Flash Sale</h3>
                                <h6 class="d-flex text-center pt-2 mv_full_width">Ending in:</h6>
                                <div class="aiz-count-down ml-auto ml-lg-3 align-items-center" data-date="{{ date('Y/m/d H:i:s', @$detailedProduct->flash_deal_product->flash_deals->end_date) }}"></div>
                            </div>

                            @endif
                            @if ($detailedProduct->wholesale_product)
                                <table class="aiz-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ ('Min Qty') }}</th>
                                            <th>{{ ('Max Qty') }}</th>
                                            <th>{{ ('Unit Price') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($detailedProduct->stocks->first()->wholesalePrices as $wholesalePrice)
                                            <tr>
                                                <td>{{ $wholesalePrice->min_qty }}</td>
                                                <td>{{ $wholesalePrice->max_qty }}</td>
                                                <td>{{ single_price($wholesalePrice->price) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                @if(home_price($detailedProduct) != home_discounted_price($detailedProduct))
                                    {{-- @dd($detailedProduct) --}}
                                    <div class="row no-gutters mt-3 priceBox mb-4">
                                        {{-- <div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ ('Price')}}:</div>
                                        </div> --}}
                                        <div class="col-7 col-md-8 pr-0">
                                            <div class="d-block d-md-flex align-items-center" style="gap: 5px;">
                                                <strong class="h2 fw-600 text-primary">
                                                    {{ single_price($webPrice) }}
                                                </strong>
                                                <div class="fs-20">
                                                    <del class="opacity-60">
                                                        {{ home_price($detailedProduct) }}
                                                    </del>
                                                    @if($detailedProduct->unit != null)
                                                        <span class="opacity-70">/{{ $detailedProduct->unit }}</span>
                                                    @endif
                                                </div>
                                                <div class="badge badge-danger px-2 fs-11" style="width: auto">
                                                    <strong>
                                                        {{ ('OFF') }}&nbsp;{{ discount_in_percentage($detailedProduct) }}%
                                                    </strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-5 col-md-4 d-flex justify-content-end">
                                            <button title="Add To Wishlist" type="button" class="btn pl-0 fw-500" onclick="addToWishList({{ $detailedProduct->id }})">
                                                <i class="la la-heart-o la-2x opacity-80"></i>
                                            </button>
                                            <button title="Add To Compare" type="button" class="btn pl-0 fw-500" onclick="addToCompare({{ $detailedProduct->id }})">
                                                <i class="la la-refresh la-2x opacity-80"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- <div class="row no-gutters my-2">
                                        <div class="col-sm-2">
                                            <div class="opacity-50">{{ ('Discount Price')}}:</div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="">
                                                <strong class="h2 fw-600 text-primary">
                                                    {{ single_price($webPrice); }}
                                                </strong>
                                                @if($detailedProduct->unit != null)
                                                    <span class="opacity-70">/{{ $detailedProduct->unit }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div> --}}
                                @else
                                    <div class="row no-gutters mt-3 priceBox mb-4">
                                        {{-- <div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ ('Price')}}:</div>
                                        </div> --}}
                                        <div class="col-7 col-md-8 pr-0">
                                            <div class="">
                                                <strong class="h2 fw-600 text-primary">
                                                    {{ single_price($webPrice); }}
                                                </strong>
                                                @if($detailedProduct->unit != null)
                                                    <span class="opacity-70">/{{ $detailedProduct->unit }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-5 col-md-4 d-flex justify-content-end">
                                            <button title="Add To Wishlist" type="button" class="btn pl-0 fw-500" onclick="addToWishList({{ $detailedProduct->id }})">
                                                <i class="la la-heart-o la-2x opacity-80"></i>
                                            </button>
                                            <button title="Add To Compare" type="button" class="btn pl-0 fw-500" onclick="addToCompare({{ $detailedProduct->id }})">
                                                <i class="la la-refresh la-2x opacity-80"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            @if($appPrice < $webPrice)
                                <div class="row no-gutters">
                                    <div class="d-flex">
                                        <span class="w-auto badge badge-danger rounded-0" style="font-size: medium; height: 25px; padding: 3px 10px;">APP PRICE</span>
                                        <span id="appprice" class="w-auto badge badge-success rounded-0" style="font-size: medium; height: 25px; padding: 3px 10px;">{{ single_price($appPrice) }}</span>
                                    </div>
                                    <div class="col-12">
                                        <p class="mt-2 mb-0 text-danger fw-400">Download {{ get_setting('website_name') }} App for <a class="text-danger" style="text-decoration: underline; font-weight: bold;" href="{{ get_setting('app_store_link') ?? '#' }}" target="_blank">iOS</a> and <a href="{{ get_setting('play_store_link') ?? '#' }}" target="_blank" style="text-decoration: underline; font-weight: bold;" class="text-danger">Android</a></p>
                                    </div>
                                </div>
                            @endif

                            @if($detailedProduct->short_description !=NULL)
                            {{-- <hr> --}}
                            <div class="row align-items-center">
                                <div class="col-12">
                                    <?php echo $detailedProduct->short_description; ?>
                                </div>
                            </div>
                            @endif



                            @php
                                $SkinTypeItemsType=data_get($customFieldsData, 'skin_type.type');
                                $SkinTypeItems=data_get($customFieldsData, 'skin_type.value');
                            @endphp
                            <!-- Skin Type -->
                            <div>
                                @if($SkinTypeItems)
                                    <div class="row no-gutters">
                                        <div class="opacity-50 my-2">{{ ('Skin Type')}}:</div>
                                        <div class="d-flex align-items-center justify-content-start flex-wrap">
                                            @if(is_array($SkinTypeItems))
                                                @foreach ($SkinTypeItems as $key => $item)
                                                    <a class="my-2 fs-14 d-block ml-2" href="@if ($item['url'] != null) {{ $item['url'] }} @else {{ route('search', [
                                                        'skin_type' => $item['title'],
                                                    ]) }} @endif">
                                                        {{ $item['title'] }}@if(!$loop->last), @endif</a>
                                                @endforeach
                                            @elseif(strtolower($SkinTypeItemsType) === 'html_box')
                                                <p>{!! $SkinTypeItems !!}</p>
                                            @else
                                                <p>{{ $SkinTypeItems }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @php
                                $SkinConcernItemsType=data_get($customFieldsData, 'skin_concern.type');
                                $SkinConcernItems=data_get($customFieldsData, 'skin_concern.value');
                            @endphp
                            <!-- Skin Concern -->
                            <div class="">
                                @if($SkinConcernItems)
                                    <div class="row no-gutters">
                                        <div class="opacity-50 my-2">{{ ('Skin Concern')}}:</div>
                                        <div class="d-flex align-items-center justify-content-start flex-wrap">
                                            @if(is_array($SkinConcernItems))
                                                @foreach ($SkinConcernItems as $key => $item)
                                                    <a class="my-2 fs-14 d-block ml-2" href="@if ($item['url'] != null) {{ $item['url'] }} @else {{ route('search', [
                                                        'skin_concern' => $item['title'],
                                                    ]) }} @endif">
                                                        {{ $item['title'] }}@if(!$loop->last), @endif</a>
                                                @endforeach
                                            @elseif(strtolower($SkinConcernItemsType) === 'html_box')
                                                <p>{!! $SkinConcernItems !!}</p>
                                            @else
                                                <p>{{ $SkinConcernItems }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @if($SkinTypeItems || $SkinConcernItems)
                            <hr>
                            @endif

                            {{-- <div class="row align-items-center">
                                <div class="col-auto">
                                    <small class="mr-2 opacity-50">{{ ('Sold by')}}: </small><br>
                                    @if ($detailedProduct->added_by == 'seller' && get_setting('vendor_system_activation') == 1)
                                        <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="text-reset">{{ $detailedProduct->user->shop->name }}</a>
                                    @else
                                        {{  translate('Inhouse product') }}
                                    @endif
                                </div>
                                @if (get_setting('conversation_system') == 1)
                                    <div class="col-auto">
                                        <button class="btn btn-sm btn-soft-primary" onclick="show_chat_modal()">{{ ('Message Seller')}}</button>
                                    </div>
                                @endif

                                @if ($detailedProduct->brand != null)
                                    <div class="col-auto">
                                        <small class="mr-2 opacity-50">{{ ('Brand')}}: </small><br>
                                        <a href="{{ route('products.brand',$detailedProduct->brand->slug) }}" class="fw-700 text-secondary">{{ $detailedProduct->brand->getTranslation('name') }}</a>
                                    </div>
                                @endif
                            </div> --}}

                            @if (addon_is_activated('club_point') && $detailedProduct->earn_point > 0)
                            <hr>
                                <div class="row no-gutters mt-4">
                                    <div class="col-sm-2">
                                        <div class="opacity-50 my-2">{{  translate('Club Point') }}:</div>
                                    </div>
                                    <div class="col-sm-10">
                                        <div class="d-inline-block rounded px-2 bg-soft-primary border-soft-primary border">
                                            <span class="strong-700">{{ $detailedProduct->earn_point }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- <hr> --}}

                            <form id="option-choice-form">
                                @csrf
                                <input type="hidden" name="id" value="{{ $detailedProduct->id }}">

                                @if ($detailedProduct->choice_options != null)
                                    @foreach (json_decode($detailedProduct->choice_options) as $key => $choice)

                                    <div class="row no-gutters">
                                        <div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ \App\Models\Attribute::find($choice->attribute_id)->getTranslation('name') }}:</div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="aiz-radio-inline">
                                                @foreach ($choice->values as $key => $value)
                                                <label class="aiz-megabox pl-0 mr-2">
                                                    <input
                                                        type="radio"
                                                        name="attribute_id_{{ $choice->attribute_id }}"
                                                        value="{{ $value }}"
                                                        @if($key == 0) checked @endif
                                                    >
                                                    <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center py-2 px-3 mb-2">
                                                        {{ $value }}
                                                    </span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    @endforeach
                                @endif

                                @if (count(json_decode($detailedProduct->colors)) > 0)
                                    <div class="row no-gutters">
                                        <div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ ('Color')}}:</div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="aiz-radio-inline">
                                                @foreach (json_decode($detailedProduct->colors) as $key => $color)
                                                <label class="aiz-megabox pl-0 mr-2" data-toggle="tooltip" data-title="{{ \App\Models\Color::where('code', $color)->first()->name }}">
                                                    <input
                                                        type="radio"
                                                        name="color"
                                                        value="{{ \App\Models\Color::where('code', $color)->first()->name }}"
                                                        @if($key == 0) checked @endif
                                                    >
                                                    <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center p-1 mb-2">
                                                        <span class="size-30px d-inline-block rounded" style="background: {{ $color }};"></span>
                                                    </span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- <hr> --}}
                                @endif

                                <!-- Quantity + Add to cart -->
                                <div class="row no-gutters mb-2">
                                    <div class="col-sm-2">
                                        <div class="opacity-50 my-2">{{ ('Quantity')}}:</div>
                                    </div>
                                    <div class="col-sm-10">
                                        <div class="product-quantity d-flex align-items-center">
                                            <div class="row no-gutters align-items-center aiz-plus-minus mr-3" style="width: 130px;">
                                                <button class="btn col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-id="{{ $detailedProduct->id }}" data-type="minus" data-field="quantity" disabled="">
                                                    <i class="las la-minus"></i>
                                                </button>
                                                <input type="number" name="quantity" class="col border-0 text-center flex-grow-1 fs-16 input-number inputCart-{{$detailedProduct->id}}" placeholder="1" value="{{ $detailedProduct->min_qty }}" min="{{ $detailedProduct->min_qty }}" max="10">
                                                <button class="btn  col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-id="{{ $detailedProduct->id }}" data-type="plus" data-field="quantity">
                                                    <i class="las la-plus"></i>
                                                </button>
                                            </div>

                                            @php
                                                $in_stock = false;
                                            @endphp
                                            @php if($checkflashdeal): @endphp

                                            <div class="avialable-amount opacity-60">
                                                @if($detailedProduct->stock_visibility_state == 'quantity')
                                                (<span id="available-quantity">{{ $detailedProduct->flash_deal_product->quantity }}</span> {{ ('available')}})
                                                @php
                                                    $in_stock = $detailedProduct->flash_deal_product->quantity > 0;
                                                @endphp
                                                @elseif($detailedProduct->stock_visibility_state == 'text' && $detailedProduct->flash_deal_product->qyantity >= 1)
                                                @php
                                                    $in_stock = true;
                                                @endphp
                                                    (<span id="available-quantity">{{ ('In Stock') }}</span>)
                                                @endif
                                            </div>
                                            @php else: @endphp
                                            @php
                                            $qty = 0;
                                            foreach ($detailedProduct->stocks as $key => $stock) {
                                                $qty += $stock->qty;
                                            }
                                        @endphp
                                        <div class="avialable-amount opacity-60">
                                            @if($detailedProduct->stock_visibility_state == 'quantity')
                                            (<span id="available-quantity">{{ $qty }}</span> {{ ('available')}})
                                            @php
                                                $in_stock = $qty > 0;
                                            @endphp
                                            @elseif($detailedProduct->stock_visibility_state == 'text' && $qty >= 1)
                                            @php
                                                $in_stock = true;
                                            @endphp
                                                (<span id="available-quantity">{{ ('In Stock') }}</span>)
                                            @endif
                                        </div>

                                            @php endif; @endphp
                                        </div>
                                    </div>
                                </div>

                                {{-- <hr> --}}

                                <div class="row no-gutters pb-3 d-none" id="chosen_price_div">
                                    <div class="col-sm-2">
                                        <div class="opacity-50 my-2">{{ ('Total Price')}}:</div>
                                    </div>
                                    <div class="col-sm-10">
                                        <div class="product-price">
                                            <strong id="chosen_price" class="h4 fw-600 text-primary">

                                            </strong>
                                        </div>
                                    </div>
                                </div>

                            </form>

                            <div class="mt-3 buynowWrapper">
                                <a href="/" class="btn btn-soft-primary mr-2 fw-600 d-block d-md-none">
                                    <svg width="18" height="18" viewBox="0 0 22 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20.9312 9.34202L12.1812 1.39046C11.8586 1.09532 11.4372 0.931641 11 0.931641C10.5627 0.931641 10.1413 1.09532 9.81874 1.39046L1.06874 9.34202C0.889588 9.50592 0.746504 9.70531 0.648588 9.9275C0.550672 10.1497 0.500066 10.3898 0.499989 10.6326V20.7061C0.492875 21.1473 0.648637 21.5756 0.937489 21.9092C1.10143 22.0956 1.30338 22.2448 1.52977 22.3467C1.75616 22.4485 2.00174 22.5008 2.24999 22.4998H7.49999C7.73205 22.4998 7.95461 22.4076 8.11871 22.2436C8.2828 22.0795 8.37499 21.8569 8.37499 21.6248V16.3748C8.37499 16.1428 8.46718 15.9202 8.63127 15.7561C8.79537 15.592 9.01792 15.4998 9.24999 15.4998H12.75C12.9821 15.4998 13.2046 15.592 13.3687 15.7561C13.5328 15.9202 13.625 16.1428 13.625 16.3748V21.6248C13.625 21.8569 13.7172 22.0795 13.8813 22.2436C14.0454 22.4076 14.2679 22.4998 14.5 22.4998H19.75C20.0402 22.5024 20.3263 22.4309 20.5812 22.292C20.8586 22.141 21.0902 21.9181 21.2519 21.6468C21.4135 21.3755 21.4992 21.0657 21.5 20.7498V10.6326C21.4999 10.3898 21.4493 10.1497 21.3514 9.9275C21.2535 9.70531 21.1104 9.50592 20.9312 9.34202Z" fill="#83919E"></path>
                                    </svg>
                                </a>
                                @if ($detailedProduct->external_link != null)
                                    <a type="button" class="btn btn-primary buy-now fw-600" href="{{ $detailedProduct->external_link }}">
                                        <i class="la la-share"></i> {{ ($detailedProduct->external_link_btn)}}
                                    </a>
                                @else
                                    <button type="button" class="btn btn-soft-primary mr-2 add-to-cart fw-600" onclick="addToCart()">
                                        <i class="las la-shopping-bag"></i>
                                        <span class="d-none d-md-inline-block"> {{ ('Add to cart')}}</span>
                                    </button>
                                    <button type="button" class="btn btn-primary buy-now fw-600" onclick="buyNow()">
                                        <i class="la la-shopping-cart"></i> {{ ('Buy Now')}}
                                    </button>
                                @endif
                                <button type="button" class="btn btn-secondary out-of-stock fw-600 d-none" disabled>
                                    <i class="la la-cart-arrow-down"></i> {{ ('Out of Stock')}}
                                </button>
                                <button type="button" class="btn btn-soft-primary pre-order fw-600 d-none" onclick="buyNow()">
                                    <i class="la la-cart-arrow-down"></i> {{ ('Pre-order Now')}}
                                </button>
                                @if(auth()->check() && !in_array(strtolower(auth()->user()->user_type), ['customer', 'seller']))
                                {{-- Copy Button --}}
                                <button type="button" class="d-none d-md-inline btn btn-secondary ml-2 copy-btn fw-600 rounded-pill rounded-md" onclick="copyProductInfo()">
                                    <i class="fas fa-copy fs-12 fs-md-16"></i> {{ ('Copy') }}
                                </button>
                                {{-- End Copy Button --}}
                                @endif
                                <br>
                                @if($detailedProduct->note != NULL)
                                    <span class="pre-order-text mt-1 text-danger small d-none"><?php echo $detailedProduct->note; ?></span>
                                @endif
                            </div>


                            @if(Auth::check() && addon_is_activated('affiliate_system') && (\App\Models\AffiliateOption::where('type', 'product_sharing')->first()->status || \App\Models\AffiliateOption::where('type', 'category_wise_affiliate')->first()->status) && Auth::user()->affiliate_user != null && Auth::user()->affiliate_user->status)
                                <div class="d-table width-100 mt-3">
                                    <div class="d-table-cell">
                                        <!-- Add to wishlist button -->
                                        {{-- <button type="button" class="btn pl-0 btn-link fw-600" onclick="addToWishList({{ $detailedProduct->id }})">
                                            {{ ('Add to wishlist')}}
                                        </button> --}}
                                        <!-- Add to compare button -->
                                        {{-- <button type="button" class="btn btn-link btn-icon-left fw-600" onclick="addToCompare({{ $detailedProduct->id }})">
                                            {{ ('Add to compare')}}
                                        </button> --}}

                                            @php
                                                if(Auth::check()){
                                                    if(Auth::user()->referral_code == null){
                                                        Auth::user()->referral_code = substr(Auth::user()->id.Str::random(10), 0, 10);
                                                        Auth::user()->save();
                                                    }
                                                    $referral_code = Auth::user()->referral_code;
                                                    $referral_code_url = URL::to('/product').'/'.$detailedProduct->slug."?product_referral_code=$referral_code";
                                                }
                                            @endphp
                                            <div>
                                                <button type=button id="ref-cpurl-btn" class="btn btn-sm btn-secondary" data-attrcpy="{{ ('Copied')}}" onclick="CopyToClipboard(this)" data-url="{{$referral_code_url}}">{{ ('Copy the Promote Link')}}</button>
                                            </div>
                                    </div>
                                </div>
                            @endif
                            @php
                                $sDiscount = check_shipping_discount_product([$detailedProduct->id], 0);
                            @endphp
                            @if($sDiscount['status'])
                                <span class="text-success text-capitalize">Enjoy Upto <strong>{{single_price($sDiscount['amount'])}}</strong> Shipping Discount On This Product. <strong>N.B:</strong> Minimum Order Amount <strong>{{single_price($sDiscount['min_amount'])}}</strong></span>
                                <span class="link link--style-3 text-capitalize" data-toggle="tooltip" data-placement="top" title="Discount will not applicable if non discounted product added on cart">
                                    <i class="las la-exclamation-circle"></i>
                                </span>
                            @endif

                            @php
                                $refund_sticker = get_setting('refund_sticker');
                            @endphp
                            @if (addon_is_activated('refund_request'))
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="opacity-50 mt-2">{{ ('Refund')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        <a href="{{ route('returnpolicy') }}" target="_blank">
                                            @if ($refund_sticker != null)
                                                <img src="{{ uploaded_asset($refund_sticker) }}" height="36">
                                            @else
                                                <img src="{{ static_asset('assets/img/refund-sticker.jpg') }}" height="36">
                                            @endif</a>
                                        <a href="{{ route('returnpolicy') }}" class="ml-2" target="_blank">{{ ('View Policy') }}</a>
                                    </div>
                                </div>
                            @endif
                            <div class="row no-gutters mt-4 d-none">
                                <div class="col-sm-2">
                                    <div class="opacity-50 my-2">{{ ('Share')}}:</div>
                                </div>
                                <div class="col-sm-10">
                                    <div class="aiz-share"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-2">
        <div class="container mobile_thin_padding">
            <div class="row gutters-10">
                {{--
                <div class="col-xl-3 order-1 order-xl-0">
                    @if ($detailedProduct->added_by == 'seller' && $detailedProduct->user->seller != null)
                        <div class="bg-white shadow-sm mb-3">
                            <div class="position-relative p-3 text-left">
                                @if ($detailedProduct->user->seller->verification_status)
                                    <div class="absolute-top-right p-2 bg-white z-1">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" viewBox="0 0 287.5 442.2" width="22" height="34">
                                            <polygon style="fill:#F8B517;" points="223.4,442.2 143.8,376.7 64.1,442.2 64.1,215.3 223.4,215.3 "/>
                                            <circle style="fill:#FBD303;" cx="143.8" cy="143.8" r="143.8"/>
                                            <circle style="fill:#F8B517;" cx="143.8" cy="143.8" r="93.6"/>
                                            <polygon style="fill:#FCFCFD;" points="143.8,55.9 163.4,116.6 227.5,116.6 175.6,154.3 195.6,215.3 143.8,177.7 91.9,215.3 111.9,154.3
                                            60,116.6 124.1,116.6 "/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="opacity-50 fs-12 border-bottom">{{ ('Sold by')}}</div>
                                <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="text-reset d-block fw-600">
                                    {{ $detailedProduct->user->shop->name }}
                                    @if ($detailedProduct->user->seller->verification_status == 1)
                                        <span class="ml-2"><i class="fa fa-check-circle" style="color:green"></i></span>
                                    @else
                                        <span class="ml-2"><i class="fa fa-times-circle" style="color:red"></i></span>
                                    @endif
                                </a>
                                <div class="location opacity-70">{{ $detailedProduct->user->shop->address }}</div>
                                <div class="text-center border rounded p-2 mt-3">
                                    <div class="rating">
                                        @if ($total > 0)
                                            {!! renderStarRating($detailedProduct->user->seller->rating) !!}
                                        @else
                                            {!! renderStarRating(0) !!}
                                        @endif
                                    </div>
                                    <div class="opacity-60 fs-12">({{ $total }} {{ ('customer reviews')}})</div>
                                </div>
                            </div>
                            <div class="row no-gutters align-items-center border-top">
                                <div class="col">
                                    <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="d-block btn btn-soft-primary rounded-0">{{ ('Visit Store')}}</a>
                                </div>
                                <div class="col">
                                    <ul class="social list-inline mb-0">
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->facebook }}" class="facebook" target="_blank">
                                                <i class="lab la-facebook-f opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->google }}" class="google" target="_blank">
                                                <i class="lab la-google opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->twitter }}" class="twitter" target="_blank">
                                                <i class="lab la-twitter opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="{{ $detailedProduct->user->shop->youtube }}" class="youtube" target="_blank">
                                                <i class="lab la-youtube opacity-60"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="bg-white rounded shadow-sm mb-3">
                        <div class="p-3 border-bottom fs-16 fw-600">
                            {{ ('Top Selling Products')}}
                        </div>
                        <div class="p-3">
                            <ul class="list-group list-group-flush">
                                @foreach (filter_products(\App\Models\Product::where('user_id', $detailedProduct->user_id)->orderBy('num_of_sale', 'desc'))->limit(6)->get() as $key => $top_product)
                                <li class="py-3 px-0 list-group-item border-light">
                                    <div class="row gutters-10 align-items-center">
                                        <div class="col-5">
                                            <a href="{{ route('product', $top_product->slug) }}" class="d-block text-reset">
                                                <img
                                                    class="img-fit lazyload h-xxl-110px h-xl-80px h-120px"
                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($top_product->thumbnail_img) }}"
                                                    alt="{{ $top_product->getTranslation('name') }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </a>
                                        </div>
                                        <div class="col-7 text-left">
                                            <h4 class="fs-13 text-truncate-2">
                                                <a href="{{ route('product', $top_product->slug) }}" class="d-block text-reset">{{ $top_product->getTranslation('name') }}</a>
                                            </h4>
                                            <div class="rating rating-sm mt-1">
                                                {!! renderStarRating($top_product->rating) !!}
                                            </div>
                                            <div class="mt-2">
                                                <span class="fs-17 fw-600 text-primary">{{ home_discounted_base_price($top_product) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                --}}

                <div class="col-xl-12 order-1 order-xl-0">
                    @if ($detailedProduct->added_by == 'seller' && $detailedProduct->user->seller != null)
                        <div class="bg-white shadow-sm mb-3">
                            <div class="position-relative p-3 text-left">
                                @if ($detailedProduct->user->seller->verification_status)
                                    <div class="absolute-top-right p-2 bg-white z-1">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" viewBox="0 0 287.5 442.2" width="22" height="34">
                                            <polygon style="fill:#F8B517;" points="223.4,442.2 143.8,376.7 64.1,442.2 64.1,215.3 223.4,215.3 "/>
                                            <circle style="fill:#FBD303;" cx="143.8" cy="143.8" r="143.8"/>
                                            <circle style="fill:#F8B517;" cx="143.8" cy="143.8" r="93.6"/>
                                            <polygon style="fill:#FCFCFD;" points="143.8,55.9 163.4,116.6 227.5,116.6 175.6,154.3 195.6,215.3 143.8,177.7 91.9,215.3 111.9,154.3
                                            60,116.6 124.1,116.6 "/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="opacity-50 fs-12 border-bottom">{{ ('Sold by')}}</div>
                                <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="text-reset d-block fw-600">
                                    {{ $detailedProduct->user->shop->name }}
                                    @if ($detailedProduct->user->seller->verification_status == 1)
                                        <span class="ml-2"><i class="fa fa-check-circle" style="color:green"></i></span>
                                    @else
                                        <span class="ml-2"><i class="fa fa-times-circle" style="color:red"></i></span>
                                    @endif
                                </a>
                                <div class="location opacity-70">{{ $detailedProduct->user->shop->address }}</div>
                                <div class="text-center border rounded p-2 mt-3">
                                    <div class="rating">
                                        @if ($total > 0)
                                            {!! renderStarRating($detailedProduct->user->seller->rating) !!}
                                        @else
                                            {!! renderStarRating(0) !!}
                                        @endif
                                    </div>
                                    <div class="opacity-60 fs-12">({{ $total }} {{ ('customer reviews')}})</div>
                                </div>
                            </div>
                            <div class="row no-gutters align-items-center border-top">
                                <div class="col">
                                    <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="d-block btn btn-soft-primary rounded-0">{{ ('Visit Store')}}</a>
                                </div>
                                <div class="col">
                                    <ul class="social list-inline mb-0">
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->facebook }}" class="facebook" target="_blank">
                                                <i class="lab la-facebook-f opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->google }}" class="google" target="_blank">
                                                <i class="lab la-google opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->twitter }}" class="twitter" target="_blank">
                                                <i class="lab la-twitter opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="{{ $detailedProduct->user->shop->youtube }}" class="youtube" target="_blank">
                                                <i class="lab la-youtube opacity-60"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>


                <div class="col-xl-12 order-0 order-xl-1">
                    <!-- Highlights -->

                   @php
                       $Highlights=data_get($customFieldsData, 'highlight.value');
                   @endphp
                   @if($Highlights)
                   <div class="bg-white mb-2 shadow-sm rounded">
                       <div class="nav border-bottom aiz-nav-tabs">
                           <a href="#tab_default_1" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset active show">{{ ('Highlights')}}</a>
                       </div>

                       <div class="tab-content pt-0">
                           <div class="tab-pane fade active show" id="tab_default_1">
                               <div class="p-4">
                                   <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                       <div class="row ">
                                           @foreach ($Highlights as $key => $highlight)
                                               <div class = "col-6 col-md-4 col-lg-2  d-flex align-items-center justify-content-center flex-column p-2">
                                                    <img
														class="mr-2"
														height="40"
														width="40"
														src="{{ uploaded_asset($highlight['image']) }}"
														data-src="{{ uploaded_asset($highlight['image']) }}"
														onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
													/>
                                                    <h6 class = "mt-2 fs-14 text-center">{{ $highlight['title'] }}</h6>
                                               </div>
                                           @endforeach
                                       </div>
                                   </div>
                               </div>
                           </div>
                       </div>
                   </div>
                   @endif

                    <div class="bg-white mb-2 shadow-sm rounded">
                        <div class="nav border-bottom aiz-nav-tabs">
                            <a href="#tab_default_1" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset active show">{{ ('Description')}}</a>
                            @if($detailedProduct->video_link != null)
                                <a href="#tab_default_2" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset">{{ ('Video')}}</a>
                            @endif
                            @if($detailedProduct->pdf != null)
                                <a href="#tab_default_3" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset">{{ ('Downloads')}}</a>
                            @endif
                        </div>

                        <div class="tab-content pt-0">
                            <div class="tab-pane fade active show" id="tab_default_1">
                                <div class="p-4">
                                    <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                        <?php echo $detailedProduct->description; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab_default_2">
                                <div class="p-4">
                                    <div class="embed-responsive embed-responsive-16by9">
                                        @if ($detailedProduct->video_provider == 'youtube' && isset(explode('=', $detailedProduct->video_link)[1]))
                                            <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{{ explode('=', $detailedProduct->video_link)[1] }}"></iframe>
                                        @elseif ($detailedProduct->video_provider == 'dailymotion' && isset(explode('video/', $detailedProduct->video_link)[1]))
                                            <iframe class="embed-responsive-item" src="https://www.dailymotion.com/embed/video/{{ explode('video/', $detailedProduct->video_link)[1] }}"></iframe>
                                        @elseif ($detailedProduct->video_provider == 'vimeo' && isset(explode('vimeo.com/', $detailedProduct->video_link)[1]))
                                            <iframe src="https://player.vimeo.com/video/{{ explode('vimeo.com/', $detailedProduct->video_link)[1] }}" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab_default_3">
                                <div class="p-4 text-center ">
                                    <a href="{{ uploaded_asset($detailedProduct->pdf) }}" class="btn btn-primary">{{  translate('Download') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- How to use -->
                    @php
                        $how_to_use_type = data_get($customFieldsData, 'how_to_use.type');
                        $HowToUseData=data_get($customFieldsData, 'how_to_use.value');
                    @endphp
                    @if($HowToUseData)
                    <div class="bg-white mb-2 shadow-sm rounded">
                        <div class="nav border-bottom aiz-nav-tabs">
                            <a href="#tab_default_1" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset active show text-capitalize">{{ ('How To Use')}}</a>
                        </div>

                        <div class="tab-content pt-0">
                            <div class="tab-pane fade active show" id="tab_default_1">
                                <div class="p-4">
                                    <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                        @if(strtolower($how_to_use_type) === 'html_box')
                                            {!! $HowToUseData !!}
                                        @else
                                            {{ $HowToUseData }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Key Ingredient -->

                    @php
                        $KeyIngredientsType=data_get($customFieldsData, 'key_ingredient.type');
                        $KeyIngredients=data_get($customFieldsData, 'key_ingredient.value');
                    @endphp
                    @if($KeyIngredients)
                    <div class="bg-white mb-2 shadow-sm rounded">
                        <div class="nav border-bottom aiz-nav-tabs">
                            <a href="#tab_default_1" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset active show">{{ ('Key Ingredients')}}</a>
                        </div>

                        <div class="tab-content pt-0">
                            <div class="tab-pane fade active show" id="tab_default_1">
                                <div class="p-4">
                                    <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                        <div class="d-flex align-items-center justify-content-start flex-wrap">
                                            @if(is_array($KeyIngredients))
                                                @foreach ($KeyIngredients as $key => $ingredient)
                                                    <a class="my-2 fs-14 d-block ml-2" href="@if ($ingredient['url'] != null) {{ $ingredient['url'] }} @else {{ route('search', [
                                                        'key_ingredient' => $ingredient['title']
                                                    ]) }} @endif">
                                                    {{ $ingredient['title'] }}@if(!$loop->last), @endif
                                                </a>
                                                @endforeach
                                            @elseif(strtolower($KeyIngredientsType) === 'html_box')
                                                {!! $KeyIngredients !!}
                                            @else
                                                <p>{{ $KeyIngredients }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Ingredient -->
                    @php
                        $IngredientsType=data_get($customFieldsData, 'ingredients.type');
                        $Ingredients=data_get($customFieldsData, 'ingredients.value');
                    @endphp
                    @if($Ingredients)
                        <div class="bg-white mb-2 shadow-sm rounded">
                            <div class="nav border-bottom aiz-nav-tabs">
                                <a href="#tab_default_1" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset active show">{{ ('Ingredients')}}</a>
                            </div>

                            <div class="tab-content pt-0">
                                <div class="tab-pane fade active show" id="tab_default_1">
                                    <div class="p-4">
                                        <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                            <div class="d-flex align-items-center justify-content-start flex-wrap">
                                                @if(is_array($Ingredients))
                                                    @foreach ($Ingredients as $key => $ingredient)
                                                        <a class="my-2 fs-14 d-block ml-2" href="@if ($ingredient['url'] != null) {{ $ingredient['url'] }} @else {{ route('search', [
                                                            'ingredients' => $ingredient['title']
                                                        ]) }} @endif">
                                                        {{ $ingredient['title'] }}@if(!$loop->last), @endif
                                                    </a>
                                                    @endforeach
                                                @elseif(strtolower($IngredientsType) === 'html_box')
                                                    {!! $Ingredients !!}
                                                @else
                                                    <p>{{ $Ingredients }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="bg-white rounded shadow-sm mb-2">
                        <div class="nav border-bottom aiz-nav-tabs">
                            <a href="#rating_reviews_tab" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset active show">{{ ('Ratings & Reviews')}}</a>
                            <a href="#video_reviews_tab" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset">{{ ('Video Reviews')}}</a>
                        </div>

                        <div class="tab-content pt-0">
                            <div class="tab-pane fade active show" id="rating_reviews_tab">
                                <div class="p-4">
                                    <!-- Overall Ratings -->
                                    <div class="customars-ratting">
                                        <div class="product-headding">
                                            <h1>Ratings & Reviews of {{ $detailedProduct->name }}</h1>
                                        </div>
                                        <div class="ratting-section">
                                            <div class="final-rating">
                                                <!-- <span class="rating-label">Final Rating:</span> -->
                                                <p id="final-rating" class="rating-value"></p>
                                                <div class="rating-stars">
                                                </div>
                                                <p class="rating-count"><span id="total-rating-count"></span> Ratings</p>
                                            </div>
                                            <div class="rating-progresss">
                                            <div class="rating-line fiveStars">
                                                <div class="rating-stars">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                </div>
                                                <div class="progresss-line five-stars" style="width: 0%;"></div>
                                                <div class="rating-counter">
                                                    <span class="counter-value">0</span>
                                                </div>
                                            </div>
                                            <div class="rating-line fourStars">
                                                <div class="rating-stars">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i style="color: #00000011;" class="fas fa-star" ></i>
                                                </div>
                                                <div class="progresss-line four-stars" style="width: 0%;"></div>
                                                <div class="rating-counter">
                                                    <span class="counter-value">0</span>
                                                </div>
                                            </div>
                                            <div class="rating-line threeStars">
                                                <div class="rating-stars">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i style="color: #00000011;" class="fas fa-star" ></i>
                                                    <i style="color: #00000011;" class="fas fa-star" ></i>
                                                </div>
                                                <div class="progresss-line three-stars" style="width: 0%;"></div>
                                                <div class="rating-counter">
                                                    <span class="counter-value">0</span>
                                                </div>
                                            </div>
                                            <div class="rating-line twoStars">
                                                <div class="rating-stars">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i style="color: #00000011;" class="fas fa-star" ></i>
                                                    <i style="color: #00000011;" class="fas fa-star" ></i>
                                                    <i style="color: #00000011;" class="fas fa-star" ></i>
                                                </div>
                                                <div class="progresss-line two-stars" style="width: 0%;"></div>
                                                <div class="rating-counter">
                                                    <span class="counter-value">0</span>
                                                </div>
                                            </div>
                                            <div class="rating-line oneStar">
                                                <div class="rating-stars">
                                                    <i class="fas fa-star"></i>
                                                    <i style="color: #00000011;" class="fas fa-star" ></i>
                                                    <i style="color: #00000011;" class="fas fa-star" ></i>
                                                    <i style="color: #00000011;" class="fas fa-star" ></i>
                                                    <i style="color: #00000011;" class="fas fa-star" ></i>
                                                </div>
                                                <div class="progresss-line one-star" style="width: 0%;"></div>
                                                <div class="rating-counter">
                                                    <span class="counter-value">0</span>
                                                </div>
                                            </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <ul class="list-group list-group-flush">
                                            @foreach ($detailedProduct->reviews as $key => $review)
                                                @php
                                                    $name = $review->name ?: $review->user?->name ?? 'Anonymous';
                                                @endphp
                                                @if(filled($review->comment))
                                                    <li class="media list-group-item">
                                                        <div class="d-flex">
                                                            <span class="avatar avatar-md mr-3">
                                                                <img
                                                                    class="lazyload"
                                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                                    @if(@$review->user->avatar_original !=null)
                                                                        data-src="{{ uploaded_asset($review->user->avatar_original) }}"
                                                                    @else
                                                                        data-src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                                    @endif
                                                                >
                                                            </span>
                                                            <div class="media-body text-left">
                                                                <div class="d-flex justify-content-between">
                                                                    <h3 class="fs-15 fw-600 mb-0">{{ $name }}</h3>
                                                                    <span class="rating rating-sm">
                                                                        @for ($i=0; $i < $review->rating; $i++)
                                                                            <i class="las la-star active"></i>
                                                                        @endfor
                                                                        @for ($i=0; $i < 5-$review->rating; $i++)
                                                                            <i class="las la-star"></i>
                                                                        @endfor
                                                                    </span>
                                                                </div>
                                                                <div class="opacity-60 mb-2">{{ @$review->created_at->diffForHumans() }}</div>
                                                                <p class="comment-text">
                                                                    {{ @$review->comment }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex">
                                                            @php
                                                                $reviewPhotos = [];
                                                                isset($review->photos) ? $reviewPhotos = explode(',', $review->photos) : [];
                                                            @endphp
                                                            @if(count($reviewPhotos) > 0)
                                                            <div class="lbt-gallery d-flex">
                                                                @foreach($reviewPhotos as $photo)
                                                                <div class="lbt-box">
                                                                    <img
                                                                        class="img-fluid lazyload"
                                                                        src="{{ uploaded_asset($photo) }}"
                                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                                        width="50"
                                                                        style="width: 70px; height:70px; object-fit: cover; cursor: pointer; opacity: 9; transition: opacity .2s; border: 3px solid #515e66; margin-right: 7px;"
                                                                    >
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>

                                        @if(count($detailedProduct->reviews) <= 0)
                                            <div class="text-center fs-18 opacity-70">
                                                {{  translate('There have been no reviews for this product yet.') }}
                                            </div>
                                        @endif

                                        {{-- @if ($commentable) --}}
                                            <div class="pt-4" id="commentable"></div>
                                        {{-- @endif --}}
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="video_reviews_tab">
                                <div class="p-4">
                                    <div class="row">
                                        @php
                                            $videoCount = 0;
                                        @endphp
                                        @foreach ($detailedProduct->reviews as $key => $review)
                                            @php
                                                $videoLinks = $review->videos ?? [];
                                            @endphp
                                            @if(count($videoLinks) == 0)
                                                @continue
                                            @endif

                                            @foreach($videoLinks as $videoLink)
                                                @php
                                                    $videoCount++;
                                                @endphp
                                                <div class="col-md-4 mb-3">
                                                    <div class="embed-responsive embed-responsive-16by9">
                                                        <iframe src="{{ get_yt_embed($videoLink) }}" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                        @if($videoCount == 0)
                                            <div class="col-12 text-center fs-18 opacity-70">
                                                {{ ('There have been no video reviews for this product yet.') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                     <!-- FAQs -->

                    @php
                        $Faqs=data_get($customFieldsData, 'faqs.value', []);
                    @endphp
                    @if($Faqs)
                        <div class = "mt-2 product-faq shadow-sm">
                            <h3 class="fs-16 fw-600 mb-0 text-center">
                                <span class="mr-4">FAQs</span>
                            </h3>
                            <div class="accordion">
                                @foreach ($Faqs as $key => $faq)
                                    <div class="accordion-item">
                                        <div class="accordion-item-header fs-16 fw-600 mb-0">
                                        {{ $faq['title'] }}
                                        </div>
                                        <div class="accordion-item-body">
                                            <div class="accordion-item-body-content">
                                            {{ $faq['description'] }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <script>
                                    const accordionItemHeaders = document.querySelectorAll(".accordion-item-header");

                                        accordionItemHeaders.forEach(accordionItemHeader => {
                                        accordionItemHeader.addEventListener("click", event => {

                                            accordionItemHeader.classList.toggle("active");
                                            const accordionItemBody = accordionItemHeader.nextElementSibling;
                                            if(accordionItemHeader.classList.contains("active")) {
                                            accordionItemBody.style.maxHeight = accordionItemBody.scrollHeight + "px";
                                            }
                                            else {
                                            accordionItemBody.style.maxHeight = 0;
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </div>
                    @endif

                    <div id="related-products">
                        <section class="product--home-categories mt-1 mb-3" style="background-image:url('')">
                            <div class="d-block d-md-none">
                                <div class="img-fit mx-auto skeleton-img" style="height: 180px; background-color: #f0f0f0;"></div>
                            </div>
                            <div class="container">
                                <div class="bg-white-ex px-0 py-md-3">
                                    {{-- Full div skeleton --}}
                                    <div class="d-none d-md-flex mb-3 align-items-baseline border-bottom skeleton-text" style="height: 50px; background-color: #f0f0f0; border-radius: 4px;"></div>

                                    <div class="row gutters-10">
                                        {{-- Skeleton Product Boxes --}}
                                        @php
                                            $limit = $agent->isMobile() ? 1 : 5;
                                        @endphp
                                        @foreach (range(0, $limit) as $i)
                                            <div class="col-lg-2 col-md-3 col-6 px-1 skeleton-product-box">
                                                <div class="border border-light rounded hov-shadow-md mt-1 mb-1 bg-white">
                                                    <div class="position-relative">
                                                        <div class="img-fit mx-auto skeleton-img" style="height: 180px; background-color: #f0f0f0;"></div>
                                                    </div>
                                                    <div class="p-2 pb-3 px-md-2 pt-2 text-center">
                                                        <div class="skeleton-text" style="width: 100%; height: 16px; background-color: #f0f0f0; margin-bottom: 8px;"></div>
                                                        <div class="skeleton-text" style="width: 80%; height: 16px; background-color: #f0f0f0; margin: 0 auto 8px;"></div>
                                                        <div class="skeleton-text" style="width: 60%; height: 16px; background-color: #f0f0f0; margin: 0 auto;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </section>

                        <style>
                            /* Animation for skeleton elements */
                            .skeleton-img, .skeleton-title, .skeleton-button, .skeleton-text,
                            .skeleton-div {
                                position: relative;
                                overflow: hidden;
                                border-radius: 4px;
                            }

                            .skeleton-img::after, .skeleton-title::after,
                            .skeleton-button::after, .skeleton-text::after,
                            .skeleton-div::after {
                                content: '';
                                position: absolute;
                                top: 0;
                                left: 0;
                                right: 0;
                                bottom: 0;
                                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
                                animation: shimmer 1.5s infinite;
                            }

                            @keyframes shimmer {
                                0% { transform: translateX(-100%); }
                                100% { transform: translateX(100%); }
                            }

                            /* Responsive adjustments */
                            @media (max-width: 767px) {
                                .skeleton-product-box {
                                    margin-bottom: 15px;
                                }
                            }
                        </style>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('modal')
    <div class="modal fade" id="chat_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title fw-600 h5">{{ ('Any query about this product')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="" action="{{ route('conversations.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="form-group">
                            <input type="text" class="form-control mb-3" name="title" value="{{ $detailedProduct->name }}" placeholder="{{ ('Product Name') }}" required>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" rows="8" name="message" required placeholder="{{ ('Your Question') }}">{{ route('product', $detailedProduct->slug) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary fw-600" data-dismiss="modal">{{ ('Cancel')}}</button>
                        <button type="submit" class="btn btn-primary fw-600">{{ ('Send')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="login_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-600">{{ ('Login')}}</h6>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-3">
                        <form class="form-default" role="form" action="{{ route('cart.login.submit') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                @if (addon_is_activated('otp_system'))
                                    <input type="text" class="form-control h-auto form-control-lg " value="{{ old('email') }}" placeholder="{{ ('Email Or Phone')}}" name="email" id="email">
                                @else
                                    <input type="email" class="form-control h-auto form-control-lg " value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email">
                                @endif
                                @if (addon_is_activated('otp_system'))
                                    <span class="opacity-60">{{  translate('Use country code before number') }}</span>
                                @endif
                            </div>

                            <div class="form-group">
                                <input type="password" name="password" class="form-control h-auto form-control-lg" placeholder="{{ ('Password')}}">
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span class=opacity-60>{{  translate('Remember Me') }}</span>
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                                <div class="col-6 text-right">
                                    <a href="{{ route('password.request') }}" class="text-reset opacity-60 fs-14">{{ ('Forgot password?')}}</a>
                                </div>
                            </div>

                            <div class="mb-5">
                                <button type="submit" class="btn btn-primary btn-block fw-600">{{  translate('Login') }}</button>
                            </div>
                        </form>

                        <div class="text-center mb-3">
                            <p class="text-muted mb-0">{{ ('Dont have an account?')}}</p>
                            <a href="{{ route('user.registration') }}">{{ ('Register Now')}}</a>
                        </div>
                        @if(get_setting('google_login') == 1 ||
                            get_setting('facebook_login') == 1 ||
                            get_setting('twitter_login') == 1)
                            <div class="separator mb-3">
                                <span class="bg-white px-3 opacity-60">{{ ('Or Login With')}}</span>
                            </div>
                            <ul class="list-inline social colored text-center mb-5">
                                @if (get_setting('facebook_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'facebook']) }}" class="facebook">
                                            <i class="lab la-facebook-f"></i>
                                        </a>
                                    </li>
                                @endif
                                @if(get_setting('google_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'google']) }}" class="google">
                                            <i class="lab la-google"></i>
                                        </a>
                                    </li>
                                @endif
                                @if (get_setting('twitter_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'twitter']) }}" class="twitter">
                                            <i class="lab la-twitter"></i>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
{{-- @dd($detailedProduct, @$brand) --}}
@section('script')
    @if (get_setting('google_tagmanager'))
        {{-- google tag manager --}}
        <script type = "text/javascript">
            dataLayer.push({ ecommerce: null });
            dataLayer.push({
                event    : "view_item",
                ecommerce: {
                    items: [{
                        item_name     : "{{$detailedProduct->name}}",
                        item_id       : "{{$detailedProduct->id}}",
                        price         : "{{$webPrice}}",
                        item_brand    : "{{$detailedProduct->brand->name ?? ''}}",
                        item_category : "{{$detailedProduct->category->name ?? ''}}",
                        item_variant  : "{{$detailedProduct->variant_product ?? ''}}",
                        item_list_name: "",
                        item_list_id  : "",
                        index         : 0,
                        quantity      : "{{ $detailedProduct->current_stock ?? 1 }}",
                    }]
                }
            });
        </script>
    @endif

    @include(config('app.theme').'frontend.schema.product', [
        'schemaProduct' => $detailedProduct,
    ])

    @if(isset($Faqs) && is_array($Faqs) && count($Faqs))
        @include(config('app.theme').'frontend.schema.faq', [
            'schemaFaqs' => $Faqs,
        ])
    @endif

    @if(get_setting('enable_clouflare_cache', 0) == 1 && !$agent->isMobile())
        <script type="text/javascript">
            $(document).ready(async function() {
                await $.ajax({
                    url: "{{ route('update-header') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        $('#auth-status').html(response.auth);
                        $('#compare').html(response.compare_view);
                        $('#wishlist').html(response.wishlist_view);
                        $('#cart_items').html(response.cart_view);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        </script>
    @endif

    <script type="text/javascript">
        $(document).ready(function() {
            getVariantPrice(); // Your existing function

            // 1. Load Related Products
            const productId = '{{ $detailedProduct->id }}';
            const relatedUrl = "{{ route('related.products', ':id') }}".replace(':id', productId);

            $.ajax({
                url: relatedUrl,
                type: 'GET',
                success: function(data) {
                    $('#related-products').html(data).fadeIn(400);
                },
                error: function() {
                    console.log('Failed to load related products');
                    $('#related-products').html('');
                }
            });

            // 2. Load Comment Status (runs at the same time)
            const commentUrl = "{{ route('isCommentable.product', ':id') }}".replace(':id', productId);

            $.ajax({
                url: commentUrl,
                type: 'GET',
                success: function(data) {
                    $('#commentable').html(data).fadeIn(400);
                },
                error: function() {
                    console.log('Failed to load commentable status');
                    $('#commentable').html('');
                }
            });
        });

        function copyProductInfo() {
            var data = @json($productInfoToCopy);
            // console.log(data);return;
            var $temp = $("<textarea>");
            $("body").append($temp);
            $temp.val(data).select();
            try {
                document.execCommand("copy");
                AIZ.plugins.notify('success', '{{ ('Copied to clipboard') }}');
            } catch (err) {
                AIZ.plugins.notify('danger', '{{ ('Oops, unable to copy') }}');
            }
            $temp.remove();
        }

        function CopyToClipboard(e) {
            var url = $(e).data('url');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(url).select();
            try {
                document.execCommand("copy");
                AIZ.plugins.notify('success', '{{ ('Link copied to clipboard') }}');
            } catch (err) {
                AIZ.plugins.notify('danger', '{{ ('Oops, unable to copy') }}');
            }
            $temp.remove();
            // if (document.selection) {
            //     var range = document.body.createTextRange();
            //     range.moveToElementText(document.getElementById(containerid));
            //     range.select().createTextRange();
            //     document.execCommand("Copy");

            // } else if (window.getSelection) {
            //     var range = document.createRange();
            //     document.getElementById(containerid).style.display = "block";
            //     range.selectNode(document.getElementById(containerid));
            //     window.getSelection().addRange(range);
            //     document.execCommand("Copy");
            //     document.getElementById(containerid).style.display = "none";

            // }
            // AIZ.plugins.notify('success', 'Copied');
        }
        function show_chat_modal(){
            @if (Auth::check())
                $('#chat_modal').modal('show');
            @else
                $('#login_modal').modal('show');
            @endif
        }


        // dynamic updating
        const ratingCounts = {
            fiveStars: Number('{{ collect($detailedProduct->reviews)->where("rating", 5)->count() ?? 0 }}'),
            fourStars: Number('{{ collect($detailedProduct->reviews)->where("rating", 4)->count() ?? 0 }}'),
            threeStars: Number('{{ collect($detailedProduct->reviews)->where("rating", 3)->count() ?? 0 }}'),
            twoStars: Number('{{ collect($detailedProduct->reviews)->where("rating", 2)->count() ?? 0 }}'),
            oneStar: Number('{{ collect($detailedProduct->reviews)->where("rating", 1)->count() ?? 0 }}')
        };

        function updateRatings() {
        const totalRatingCount = Object.values(ratingCounts).reduce((total, count) => total + count, 0);
        let overallRating = 0;
        let ratingSum = 0;

        overallRating =  ((5*ratingCounts.fiveStars  + 4*ratingCounts.fourStars + 3*ratingCounts.threeStars + 2*ratingCounts.twoStars + 1*ratingCounts.oneStar) / (ratingCounts.fiveStars + ratingCounts.fourStars + ratingCounts.threeStars + ratingCounts.twoStars + ratingCounts.oneStar)).toFixed(1)

        Object.keys(ratingCounts).forEach((key) => {
            const ratingline = document.querySelector(`.${key}`);
            const progresssline = ratingline.querySelector('.progresss-line');
            const counterValue = ratingline.querySelector('.counter-value');
            const ratingCounter = ratingline.querySelector('.rating-counter');

            progresssline.style.width = `${(ratingCounts[key] / totalRatingCount) * 100}%`;
            counterValue.textContent = ratingCounts[key];
            ratingCounter.style.display = 'flex';
        });


        const finalRating = document.getElementById('final-rating');
        finalRating.textContent = `${isNaN(overallRating) ? 0 : overallRating}/5`;


        const ratingStars = document.querySelector('.final-rating .rating-stars');
        ratingStars.innerHTML = '';

        for (let i = 0; i < 5; i++) {
            const starIcon = document.createElement('i');
            if (i < Math.floor(overallRating)) {
            starIcon.className = 'fas fa-star';
            } else if (i === Math.floor(overallRating) && overallRating % 1 !== 0) {
            starIcon.className = 'fas fa-star-half-alt';
            } else {
            starIcon.className = 'fas fa-star';
            starIcon.style.color = '#00000011';
            }
            ratingStars.appendChild(starIcon);
        }


        const totalRatingCountElement = document.getElementById('total-rating-count');
            totalRatingCountElement.textContent = totalRatingCount;
        }

        updateRatings();
    </script>
@endsection
