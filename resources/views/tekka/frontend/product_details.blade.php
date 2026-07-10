@php
    $currencyfilePath = storage_path('app/public/currencies/currency.json');
    if (file_exists($currencyfilePath)) {
        $jsonData = file_get_contents($currencyfilePath);
        $currencies = collect(json_decode($jsonData, true));
    } else {
        $currencies = collect(\App\Models\Currency::all()->toArray());
    }
    $appPrice = getMinimumPriceByVariant(
        $detailedProduct,
        $detailedProduct->stocks->first(),
        'app',
        1,
        $currentlyAuthenticatedUser,
    );
    $webPrice = getMinimumPriceByVariant(
        $detailedProduct,
        $detailedProduct->stocks->first(),
        'web',
        1,
        $currentlyAuthenticatedUser,
    );

    $sDiscount = check_shipping_discount_product([$detailedProduct->id], 0);

@endphp
@extends(config('app.theme') . 'frontend.layouts.app', ['currentlyAuthenticatedUser' => $currentlyAuthenticatedUser])

@section('meta')
<x-seo :meta="[
    'title' => $detailedProduct->meta_title,
    'description' => $detailedProduct->meta_description,
    'keywords' => $detailedProduct->tags,
    'image' => $detailedProduct->meta_img,
    'twitter' => [
        'card' => 'product',
    ]
]">
    {{-- Extra product meta --}}
    <meta name="twitter:data1" content="{{ single_price($detailedProduct->unit_price) }}">
    <meta name="twitter:label1" content="Price">

    <meta property="og:price:amount" content="{{ single_price($detailedProduct->unit_price) }}" />
    <meta property="product:price:currency" content="{{ optional($currencies->where('status', 1)->first())->code }}" />
    <meta property="product:price:amount" content="{{ single_price($detailedProduct->unit_price) }}">
</x-seo>

<style>
    @keyframes shake {
        0%,100% { transform: rotate(0deg); }
        15%      { transform: rotate(-18deg); }
        30%      { transform: rotate(16deg); }
        45%      { transform: rotate(-12deg); }
        60%      { transform: rotate(10deg); }
        75%      { transform: rotate(-6deg); }
        90%      { transform: rotate(4deg); }
    }
    @keyframes pulse-ring {
        0%   { box-shadow: 0 0 0 0 rgba(0,0,0,0.18); }
        70%  { box-shadow: 0 0 0 9px rgba(0,0,0,0); }
        100% { box-shadow: 0 0 0 0 rgba(0,0,0,0); }
    }
    .phone-shake {
        display: inline-block;
        animation: shake 1.8s ease-in-out infinite;
        transform-origin: center bottom;
        line-height: 1;
    }
    .open-app-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0 1.25rem;
        height: 42px;
        background: #111;
        color: #fff;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        white-space: nowrap;
        flex-shrink: 0;
        animation: pulse-ring 1.8s ease-out infinite;
    }
</style>
@endsection

@section('content')
    <section x-data="productDetails()" class="mb-2 mb-md-4 pt-3 product_details" x-cloak>
        <div class="container mobile_thin_padding ">
            <div class=" bg-white  rounded p-3 mobile_top_thin_padding">
                <div class="row">
                    <div class="col-xl-5 col-lg-6 mb-4 mobile_thin_padding">
                        <div class="sticky-top z-3 row gutters-5">
                            @php
                                $photos = explode(',', $detailedProduct->photos);
                            @endphp
                            <div class="col order-1 order-md-2">
                                <div class="product-gallery" >
                                    @foreach ($photos as $key => $photo)
                                        <div  data-fancybox="gallery"  data-src="{{ uploaded_asset($photo) }}"  data-thumb="{{ uploaded_asset($photo) }}"    data-src="{{ uploaded_asset($photo) }}"  class=" carousel-box img-zoom rounded">
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
                                            <div  data-fancybox="gallery"  data-src="{{ uploaded_asset($photo) }}"  data-thumb="{{ uploaded_asset($photo) }}"    data-src="{{ uploaded_asset($photo) }}"  class=" carousel-box img-zoom rounded" >
                                                <img
                                                    class="img-fluid lazyload"
                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($photo) }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-7 col-lg-6">
                        <div class="text-left">
                            <form id="option-choice-form-m" class="mb-0">
                            @csrf
                            <div class="d-flex justify-content-between align-items-start" >
                                <div>
                                    <h1 class="mb-2 fs-18 product-name-details fw-600 pr-3">
                                        {{ $detailedProduct->name }}
                                    </h1>
                                </div>
                                <div class="product-share d-none d-md-flex position-relative">
                                    <button type="button" class="btn pl-0 fw-500" title="Add To Wishlist" onclick="addToWishList({{ $detailedProduct->id }})">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <button type="button" class="share btn pl-0 fw-500" title="Share" @click="showShare = !showShare">
                                        <i class="fas fa-share-alt"></i>
                                    </button >
                                    <div x-show="showShare" class="z-3 row no-gutters mt-4 position-absolute" style="top: 10px; left: -100px; width: 300px;">
                                        {{--<div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ ('Share') }}:</div>
                                        </div>--}}
                                        <div class="col-sm-10 single-product-share">
                                            <div class="aiz-share"></div>
                                        </div>
                                    </div>
                                    @if(auth()->check() && !in_array(strtolower(auth()->user()->user_type), ['customer', 'seller']))
                                    <button type="button" class="btn pl-0 fw-500" title="Copy Product Info" onclick="copyProductInfo()">
                                        <i class="far fa-copy"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>

                            <div class="row align-items-center product-details-rating">
                                <div class="col-12">
                                    @php
                                        $total = 0;
                                        $total += $detailedProduct->reviews->count();
                                    @endphp
                                    <span class="rating">
                                        {!! renderStarRating($detailedProduct->reviews->avg('rating')) !!}
                                    </span>
                                    <span class="ml-1 opacity-50">({{ $total }})</span>
                                </div>
                                @if ($detailedProduct->est_shipping_days)
                                <div class="col-auto ml">
                                    <small class="mr-2 opacity-50">{{ ('Estimate Shipping Time') }}: </small>{{ $detailedProduct->est_shipping_days }} {{ ('Days') }}
                                </div>
                                @endif
                            </div>

                            <div class="row align-items-center py-2 product-brand ">
                                <div class="col-auto d-flex mb-1 mb-sm-0">
                                     <p class="mr-2  m-0 fw-500 " >{{ ('Category') }}: </p>
                                    <a href="{{ route('products.category', $detailedProduct->category->slug) }}">{{ $detailedProduct->category->name }}</a>
                                </div>
                                <div class="col-auto p-0 d-none d-sm-block">
                                    |
                                </div>
                                @if (get_setting('conversation_system') == 1)
                                    <div class="col-auto">
                                        <button class="btn btn-sm btn-soft-primary" onclick="show_chat_modal()">{{ ('Message Seller') }}</button>
                                    </div>
                                @endif

                                @if ($detailedProduct->brand != null)
                                    <div class="col-auto d-flex">
                                        <p class="mr-2  m-0 fw-500  ">{{ ('Brand') }}: </p>
                                        <a href="{{ route('products.brand', $detailedProduct->brand->slug) }}" class="">{{ $detailedProduct->brand->getTranslation('name') }}</a>
                                    </div>
                                @endif
                            </div>



                            @if ($detailedProduct->flash_deal_product != null)
                            <div class="d-flex w-md-auto flash_sale_count align-items-center flex-wrap justify-content-center justify-content-md-start">
                                <h6 class="d-flex text-center pt-2 mv_full_width">Offers Remaining:</h6>
                                <div class="aiz-count-down ml-lg-3 align-items-center" data-date="{{ date('Y/m/d H:i:s', @$detailedProduct->flash_deal_product->flash_deals->end_date) }}"></div>
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
                                {{-- @if (home_price($detailedProduct) != home_discounted_price($detailedProduct)) --}}
                                @if (home_price($detailedProduct) != single_price($webPrice))
                                    <div class="row  mt-2 mt-lg-3 priceBox">
                                        {{--<div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ ('Price') }}:</div>
                                        </div>--}}
                                        <div class="col-7 col-md-8 pr-0 d-flex align-items-center">
                                            <div class="fs-20  product-price ">
                                                <span class="pr-1 fs-24 font-weight-bold"> {{ single_price($webPrice) }}</span>
                                                <del class = "opacity-50 fs-18 font-bold">
                                                    {{ home_price($detailedProduct) }}
                                                </del>
                                                @if ($detailedProduct->unit != null)
                                                    <span>/{{ $detailedProduct->unit }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-5 col-md-4 d-block d-md-none">
                                            <div class="product-share position-relative">
                                                <button type="button" class="btn pl-0 fw-500" onclick="addToWishList({{ $detailedProduct->id }})">
                                                    <i class="far fa-heart"></i>
                                                </button>
                                                <button type="button" class="share btn pl-0 fw-500" @click="showShare = !showShare">
                                                    <i class="fas fa-share-alt"></i>
                                                </button >
                                                <div x-show="showShare" class="z-3 row no-gutters mt-4 position-absolute" style="top: 10px; left: -100px; width: 300px;">
                                                    {{--<div class="col-sm-2">
                                                        <div class="opacity-50 my-2">{{ ('Share') }}:</div>
                                                    </div>--}}
                                                    <div class="col-sm-10 single-product-share">
                                                        <div class="aiz-share"></div>
                                                    </div>
                                                </div>
                                                @if(auth()->check() && !in_array(strtolower(auth()->user()->user_type), ['customer', 'seller']))
                                                <button type="button" class="btn pl-0 fw-500" onclick="copyProductInfo()">
                                                    <i class="far fa-copy"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="row  mt-2 mt-lg-3 priceBox">
                                        <div class="col-8 col-md-6 px-0 prodctPrice d-flex align-items-center">
                                            <div class="fs-20 product-price">
                                                <strong class="fw-600 fs-24">
                                                    {{ single_price($webPrice) }}
                                                </strong>
                                                @if ($detailedProduct->unit != null)
                                                    <span class = "">/{{ $detailedProduct->unit }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-4 col-md-6 d-block d-md-none">
                                            <div class="product-share position-relative">
                                                <button type="button" class="btn pl-0  fw-500" onclick="addToWishList({{ $detailedProduct->id }})">
                                                    <i class="far fa-heart"></i>
                                                </button>
                                                <button type="button" class="share btn pl-0 fw-500" @click="showShare = !showShare">
                                                    <i class="fas fa-share-alt"></i>
                                                </button >
                                                <div x-show="showShare" class="z-3 row no-gutters mt-4 position-absolute" style="top: 10px; left: -100px; width: 300px;">
                                                    {{--<div class="col-sm-2">
                                                        <div class="opacity-50 my-2">{{ ('Share') }}:</div>
                                                    </div>--}}
                                                    <div class="col-sm-10 single-product-share">
                                                        <div class="aiz-share"></div>
                                                    </div>
                                                </div>
                                                @if(auth()->check() && !in_array(strtolower(auth()->user()->user_type), ['customer', 'seller']))
                                                <button type="button" class="btn pl-0 fw-500" onclick="copyProductInfo()">
                                                    <i class="far fa-copy"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            @if (addon_is_activated('club_point') && $detailedProduct->earn_point > 0)
                            <hr>
                                <div class="row no-gutters mt-4">
                                    <div class="col-sm-1">
                                        <div class=" my-1">{{ ('Club Point') }}:</div>
                                    </div>
                                    <div class="col-sm-11">
                                        <div class="d-inline-block rounded px-2 bg-soft-primary border-soft-primary border">
                                            <span class="strong-700">{{ $detailedProduct->earn_point }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="color--size-variant-wrapper pt-2">
                                <div class="">
                                    @if (count(json_decode($detailedProduct->colors)) > 0)
                                        <div class="product-variant-wrapper">
                                            <div class="">
                                                <div class="fs-14 fw-600 my-2">{{ ('Color') }}:</div>
                                            </div>
                                            <div class="">
                                                <div class="aiz-radio-inline">
                                                    @foreach (json_decode($detailedProduct->colors) as $key => $color)
                                                    <label class="aiz-megabox pl-0 " data-toggle="tooltip" data-title="{{ \App\Models\Color::where('code', $color)->first()->name }}">
                                                        <input
                                                            type="radio"
                                                            name="color"
                                                            value="{{ \App\Models\Color::where('code', $color)->first()->name }}"
                                                            @if ($key == 0) checked @endif
                                                        >
                                                        <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center p-1 mb-1">
                                                            <span class="size-30px d-inline-block rounded" style="background: {{ $color }};"></span>
                                                        </span>
                                                    </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                    @endif
                                </div>
                                <div class="">
                                    <input type="hidden" name="id" value="{{ $detailedProduct->id }}">
                                    @if ($detailedProduct->choice_options != null)
                                        @foreach (json_decode($detailedProduct->choice_options) as $key => $choice)

                                        <div class="size-variant-wrapper">
                                            <div class="">
                                                <div class="fs-14 fw-600 my-2">{{ \App\Models\Attribute::find($choice->attribute_id)->getTranslation('name') }}:</div>
                                            </div>
                                            <div class="">
                                                <div class="aiz-radio-inline">
                                                    @foreach ($choice->values as $key => $value)
                                                    <label class="aiz-megabox pl-0 mr-1">
                                                        <input
                                                            type="radio"
                                                            name="attribute_id_{{ $choice->attribute_id }}"
                                                            value="{{ $value }}"
                                                            @if ($key == 0) checked @endif
                                                        >
                                                        <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center py-2 px-3 mb-1">
                                                            {{ $value }}
                                                        </span>
                                                    </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <!-- static variant end -->
                            @if ($appPrice < $webPrice)
                                <div class="my-3">
                                    <div style="background: #fff; border: 0.5px solid #e5e5e5; border-radius: 12px; padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap;">
                                        {{-- Left: icon + info --}}
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <div style="width: 44px; height: 44px; background: #f5f5f5; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="fas fa-mobile-alt" style="font-size: 22px; color: #888;"></i>
                                            </div>
                                            <div>
                                                <p style="font-size: 11px; color: #888; margin: 0 0 4px; letter-spacing: 0.05em; text-transform: uppercase;">App Price</p>
                                                <div style="display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap;">
                                                    <span style="font-size: 20px; font-weight: 500; color: #111;">{{ single_price($appPrice) }}</span>
                                                    <span class="badge badge-inline badge-soft-danger font-weight-bold fs-11" style="padding-block: 10px; padding-inline: 6px;">
                                                        Save {{ $detailedProduct->app_discount }}{{ $detailedProduct->app_discount_type == 'percent' ? '%' : currency_symbol() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Right: CTA button --}}
                                        <a href="{{ $agent->isAndroidOS() ? get_setting('play_store_link', '#') : get_setting('app_store_link', '#') }}" target="_blank" class="open-app-btn">
                                            <span class="phone-shake">
                                                <i class="fas fa-mobile-alt" style="font-size: 16px;"></i>
                                            </span>
                                            Open App
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if ($detailedProduct->short_description != null)

                            <!-- <hr> -->
                            <div class="row align-items-center">
                                <div class="col-12 product-description">
                                    <?php echo $detailedProduct->short_description; ?>
                                </div>
                            </div>
                            @endif
                            <!-- static return section start-->
                            <hr>
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
                            <!-- <div class="d-flex align-items-center returnbox">
                                <div class="d-flex align-items-center returnbox-item">
                                    <span>
                                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5.64123 24.502C5.6724 24.6335 5.72985 24.7573 5.81011 24.866C5.89037 24.9746 5.99178 25.0659 6.10825 25.1344C6.22472 25.2029 6.35383 25.247 6.48782 25.2643C6.62181 25.2816 6.75791 25.2715 6.88792 25.2348C12.8491 23.5889 19.1447 23.5883 25.1062 25.2331C25.2361 25.2698 25.3722 25.2798 25.5061 25.2625C25.6401 25.2453 25.7691 25.2011 25.8856 25.1327C26.002 25.0643 26.1034 24.973 26.1836 24.8644C26.2639 24.7558 26.3214 24.6321 26.3526 24.5007L29.5387 10.9611C29.5813 10.7798 29.5725 10.5902 29.5133 10.4137C29.454 10.2372 29.3466 10.0807 29.2032 9.96193C29.0598 9.84314 28.8861 9.76674 28.7016 9.74134C28.5172 9.71595 28.3293 9.74256 28.1591 9.81819L21.8362 12.6284C21.6078 12.7299 21.3498 12.7422 21.1129 12.663C20.8759 12.5837 20.6772 12.4186 20.5559 12.2002L16.8743 5.57344C16.7877 5.41755 16.661 5.28765 16.5073 5.19721C16.3536 5.10677 16.1785 5.05908 16.0002 5.05908C15.8218 5.05908 15.6467 5.10677 15.493 5.19721C15.3393 5.28765 15.2126 5.41755 15.126 5.57344L11.4444 12.2002C11.3231 12.4186 11.1244 12.5837 10.8875 12.663C10.6505 12.7422 10.3925 12.7299 10.1642 12.6284L3.84022 9.81776C3.67009 9.74215 3.48224 9.71553 3.2978 9.74091C3.11337 9.76628 2.93968 9.84263 2.79629 9.96137C2.65289 10.0801 2.5455 10.2365 2.48619 10.413C2.42688 10.5895 2.41801 10.779 2.46057 10.9602L5.64123 24.502Z" stroke="#FA7E16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 20.2098C14.6593 19.9301 17.3407 19.9301 20 20.2098" stroke="#FA7E16" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>

                                    </span>
                                    <p class="m-0">Cruelty</p>
                                </div>
                                <div class="d-flex align-items-center  returnbox-item">
                                    <span>
                                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9.979 12.4645H3.979V6.46451" stroke="#FA7E16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M23.778 8.22183C22.7565 7.20038 21.5439 6.39013 20.2093 5.83733C18.8748 5.28452 17.4444 5 15.9998 5C14.5553 5 13.1249 5.28452 11.7903 5.83733C10.4557 6.39013 9.24309 7.20038 8.22164 8.22183L3.979 12.4645" stroke="#FA7E16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M22.021 19.5355H28.021V25.5355" stroke="#FA7E16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8.22168 23.7782C9.24312 24.7996 10.4558 25.6099 11.7903 26.1627C13.1249 26.7155 14.5553 27 15.9999 27C17.4444 27 18.8748 26.7155 20.2094 26.1627C21.544 25.6099 22.7566 24.7996 23.778 23.7782L28.0207 19.5355" stroke="#FA7E16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>

                                    </span>
                                    <p class="m-0">Easy Return</p>
                                </div>
                                <div class="d-flex align-items-center returnbox-item">
                                    <span>
                                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.999 22C21.5219 22 25.999 17.5228 25.999 12C25.999 6.47715 21.5219 2 15.999 2C10.4762 2 5.99902 6.47715 5.99902 12C5.99902 17.5228 10.4762 22 15.999 22Z" stroke="#FA7E16" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M15.999 18C19.3127 18 21.999 15.3137 21.999 12C21.999 8.68629 19.3127 6 15.999 6C12.6853 6 9.99902 8.68629 9.99902 12C9.99902 15.3137 12.6853 18 15.999 18Z" stroke="#FA7E16" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M22 19.9994V30L15.9991 27L10 30V20.0002" stroke="#FA7E16" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>

                                    </span>
                                    <p class="m-0">Quality First</p>
                                </div>
                            </div>
                            <hr> -->
                            <!-- static return section end -->
                            <!-- <hr> -->



                            <!-- Quantity + Add to cart -->
                            <div class="my-2 my-md-4 quantitybox">
                                <div class="quantity-content">
                                    <div class=" my-1">{{ ('Quantity') }}:</div>
                                </div>
                                <div class="quantity-button">
                                    <div class="product-quantity d-flex align-items-center">
                                        <div class="row no-gutters align-items-center aiz-plus-minus mr-3" style="width: 130px;">
                                            <button class="btn col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-id="{{ $detailedProduct->id }}" data-type="minus" data-field="quantity" disabled="">
                                            <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" name="quantity" class="col border-0 text-center flex-grow-1 fs-16 input-number inputCart-{{ $detailedProduct->id }}" placeholder="1" value="{{ $detailedProduct->min_qty }}" min="{{ $detailedProduct->min_qty }}" max="10">
                                            <button class="btn  col-auto btn-icon btn-sm btn-circle btn-light" type="button" data-id="{{ $detailedProduct->id }}" data-type="plus" data-field="quantity">
                                            <i class="fas fa-plus"></i>
                                            </button>
                                        </div>

                                        @if($detailedProduct->flash_deal_product != null)
                                        <div class="avialable-amount opacity-60">
                                            @if ($detailedProduct->stock_visibility_state == 'quantity')
                                            (<span id="available-quantity">{{ $detailedProduct->flash_deal_product->quantity }}</span> {{ ('available') }})
                                            @elseif($detailedProduct->stock_visibility_state == 'text' && $detailedProduct->flash_deal_product->qyantity >= 1)
                                                (<span id="available-quantity">{{ ('In Stock') }}</span>)
                                            @endif
                                        </div>
                                        @endif

                                    </div>
                                </div>
                            </div>

                                <div class="row no-gutters pb-3 d-none" id="chosen_price_div">
                                    <div class="col-4 col-md-2">
                                        <div class="opacity-50 my-2">{{ ('Total Price')}}:</div>
                                    </div>
                                    <div class="col-8 col-md-10">
                                        <div class="product-total-price">
                                            <strong id="chosen_price" class="h4 fw-600">

                                            </strong>
                                        </div>
                                    </div>
                                </div>


                            <div class="mt-4 buynowWrapper pl-2 pl-md-0">
                                <div class="d-flex align-items-center justify-content-center d-md-none backtohome">
                                   <a href="/">
                                   <svg width="18" height="18" viewBox="0 0 22 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20.9312 9.34202L12.1812 1.39046C11.8586 1.09532 11.4372 0.931641 11 0.931641C10.5627 0.931641 10.1413 1.09532 9.81874 1.39046L1.06874 9.34202C0.889588 9.50592 0.746504 9.70531 0.648588 9.9275C0.550672 10.1497 0.500066 10.3898 0.499989 10.6326V20.7061C0.492875 21.1473 0.648637 21.5756 0.937489 21.9092C1.10143 22.0956 1.30338 22.2448 1.52977 22.3467C1.75616 22.4485 2.00174 22.5008 2.24999 22.4998H7.49999C7.73205 22.4998 7.95461 22.4076 8.11871 22.2436C8.2828 22.0795 8.37499 21.8569 8.37499 21.6248V16.3748C8.37499 16.1428 8.46718 15.9202 8.63127 15.7561C8.79537 15.592 9.01792 15.4998 9.24999 15.4998H12.75C12.9821 15.4998 13.2046 15.592 13.3687 15.7561C13.5328 15.9202 13.625 16.1428 13.625 16.3748V21.6248C13.625 21.8569 13.7172 22.0795 13.8813 22.2436C14.0454 22.4076 14.2679 22.4998 14.5 22.4998H19.75C20.0402 22.5024 20.3263 22.4309 20.5812 22.292C20.8586 22.141 21.0902 21.9181 21.2519 21.6468C21.4135 21.3755 21.4992 21.0657 21.5 20.7498V10.6326C21.4999 10.3898 21.4493 10.1497 21.3514 9.9275C21.2535 9.70531 21.1104 9.50592 20.9312 9.34202Z" fill="#83919E"/>
                                    </svg>
                                   </a>
                                </div>
                                @if ($detailedProduct->external_link != null)
                                    <a type="button" class=" btn btn-primary buy-now fw-600" href="{{ $detailedProduct->external_link }}">
                                        <i class="la la-share"></i> {{ ($detailedProduct->external_link_btn) }}
                                    </a>
                                @else
                                    <button type="button" class="shop-now btn btn-primary buy-now fw-600 border-none product-details-btn" onclick="buyNow()">

                                    <i class="fas fa-shopping-bag fs-16 fw-500 "></i>
                                    <span class=" d-md-inline-block fs-16">{{ ('Shop Now') }}</span>
                                    </button>
                                    <button type="button" class="btn btn-soft-primary  add-to-cart fw-600 product-details-btn" onclick="addToCart(this)">
                                        <i class="fas fa-shopping-cart fs-16 fw-500 "></i>
                                        <span class=" d-md-inline-block "> {{ ('Add to Bag') }}</span>
                                    </button>

                                @endif
                                {{-- <button type="button" class="btn btn-secondary out-of-stock fw-600 d-none" disabled>
                                    <i class="la la-cart-arrow-down"></i> {{ ('Out of Stock') }}
                                </button> --}}
                                <button type="button" class="btn btn-danger out-of-stock fw-600 d-none text-uppercase" disabled>
                                    <i class="la la-cart-arrow-down"></i> {{ ('Stock Out') }}
                                </button>
                                <button type="button" class="btn btn-soft-primary pre-order fw-600 d-none" onclick="buyNow()">
                                    <i class="la la-cart-arrow-down"></i> {{ ('Pre-order Now') }}
                                </button>

                                <br>
                                @if ($detailedProduct->note != null)
                                    <span class="pre-order-text mt-1 text-danger small d-none"><?php echo $detailedProduct->note; ?></span>
                                @endif
                            </div>

                            @php
                                $refund_sticker = get_setting('refund_sticker');
                            @endphp
                            @if (addon_is_activated('refund_request'))
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="opacity-50 mt-2">{{ ('Refund') }}:</div>
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
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-1 mb-md-4">
        <div class="container mobile_thin_padding">
            <div class="row gutters-10">
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
                                <div class="opacity-50 fs-12 border-bottom">{{ ('Sold by') }}</div>
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
                                    <div class="opacity-60 fs-12">({{ $total }} {{ ('customer reviews') }})</div>
                                </div>
                            </div>
                            <div class="row no-gutters align-items-center border-top">
                                <div class="col">
                                    <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="d-block btn btn-soft-primary rounded-0">{{ ('Visit Store') }}</a>
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

                @php
                    $Highlights=data_get($customFieldsData, 'highlight.value');
                    $how_to_use_type = data_get($customFieldsData, 'how_to_use.type');
                    $HowToUseData=data_get($customFieldsData, 'how_to_use.value');
                    $KeyIngredientsType=data_get($customFieldsData, 'key_ingredient.type');
                    $KeyIngredients=data_get($customFieldsData, 'key_ingredient.value');
                    $IngredientsType=data_get($customFieldsData, 'ingredients.type');
                    $Ingredients=data_get($customFieldsData, 'ingredients.value');
                @endphp
                <div class="col-xl-12 order-0 order-xl-1">
                    <div class="bg-white mb-3  rounded border">
                        <div class="nav border-bottom aiz-nav-tabs">
                            <a href="#tab_default_1" data-toggle="tab" class="p-2 p-md-3 fs-16 fw-600 text-reset active show">{{ ('Description') }}</a>
                            @if ($detailedProduct->video_link != null)
                                <a href="#tab_default_2" data-toggle="tab" class="p-2 p-md-3 fs-16 fw-600 text-reset">{{ ('Video') }}</a>
                            @endif
                            @if ($detailedProduct->pdf != null)
                                <a href="#tab_default_3" data-toggle="tab" class="p-2 p-md-3 fs-16 fw-600 text-reset">{{ ('Downloads') }}</a>
                            @endif

                            {{-- Highlight Nav Start --}}
                            @if($Highlights)
                                <a href="#tab_highlight" data-toggle="tab" class="p-2 p-md-3 fs-16 fw-600 text-reset d-none d-md-block">{{ ('Highlights') }}</a>
                            @endif
                            {{-- Highlight Nav End --}}

                            {{-- How To Use Nav Start --}}
                            @if($HowToUseData)
                                <a href="#tab_how_to_use" data-toggle="tab" class="p-2 p-md-3 fs-16 fw-600 text-reset d-none d-md-block">{{ ('How To Use') }}</a>
                            @endif
                            {{-- How To Use Nav End --}}

                            {{-- Key Ingredients Nav Start --}}
                            @if($KeyIngredients)
                                <a href="#tab_key_ingredients" data-toggle="tab" class="p-2 p-md-3 fs-16 fw-600 text-reset d-none d-md-block">{{ ('Key Ingredients') }}</a>
                            @endif
                            {{-- Key Ingredients Nav End --}}

                            {{-- Ingredients Nav Start --}}
                            @if($Ingredients)
                                <a href="#tab_ingredients" data-toggle="tab" class="p-2 p-md-3 fs-16 fw-600 text-reset d-none d-md-block">{{ ('Ingredients') }}</a>
                            @endif
                            {{-- Ingredients Nav End --}}
                        </div>

                        <div class="tab-content pt-0">
                            <div class="tab-pane fade active show" id="tab_default_1">
                                <div class="p-2 p-md-4">
                                    <div class="mw-100 overflow-hidden text-left aiz-editor-data">
                                        <?php echo $detailedProduct->description; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab_default_2">
                                <div class="p-2 p-md-4">
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
                                    <a href="{{ uploaded_asset($detailedProduct->pdf) }}" class="btn btn-primary">{{ ('Download') }}</a>
                                </div>
                            </div>

                            @if($Highlights)
                            {{-- Highlight Tab Start --}}
                            <div class="tab-pane fade" id="tab_highlight">
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
                            {{-- Highlight Tab End --}}
                            @endif

                            @if($HowToUseData)
                            {{-- How To Use Tab Start --}}
                            <div class="tab-pane fade" id="tab_how_to_use">
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
                            {{-- How To Use Tab End --}}
                            @endif

                            @if($KeyIngredients)
                            {{-- Key Ingredients Tab Start --}}
                            <div class="tab-pane fade" id="tab_key_ingredients">
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
                            {{-- Key Ingredients Tab End --}}
                            @endif

                            @if($Ingredients)
                            {{-- Ingredients Tab Start --}}
                            <div class="tab-pane fade" id="tab_ingredients">
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
                            {{-- Ingredients Tab End --}}
                            @endif
                        </div>
                    </div>
                    {{--
                    <div class="bg-white rounded ">
                        <div class=" p-3">
                            <h3 class="fs-16 fw-600 mb-0">
                                <span class="mr-4">{{ ('Ratings & Reviews')}}</span>
                            </h3>
                        </div>
                        <div class="mb-3">
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
                                        <li class="media list-group-item">
                                            <div class="d-flex">
                                                <span class="avatar avatar-md mr-3">
                                                    <img
                                                        class="lazyload"
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                        @if (@$review->user->avatar_original != null)
                                                            data-src="{{ uploaded_asset($review->user->avatar_original) }}"
                                                        @else
                                                            data-src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        @endif
                                                    >
                                                </span>
                                                <div class="media-body text-left">
                                                    <div class="d-flex justify-content-between">
                                                        <h3 class="fs-15 fw-600 mb-0">{{ $review->name }}</h3>
                                                        <span class="rating rating-sm">
                                                            @for ($i = 0; $i < $review->rating; $i++)
                                                                <i class="las la-star active"></i>
                                                            @endfor
                                                            @for ($i = 0; $i < 5 - $review->rating; $i++)
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
                                                @if (count($reviewPhotos) > 0)
                                                <div class="lbt-gallery d-flex">
                                                    @foreach ($reviewPhotos as $photo)
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
                                    @endforeach
                                </ul>

                                @if (count($detailedProduct->reviews) <= 0)
                                    <div class="text-center fs-18 opacity-70">
                                        {{  translate('There have been no reviews for this product yet.') }}
                                    </div>
                                @endif

                                @if ($commentable)
                                    <div class="pt-4">
                                        <div class="border-bottom mb-4">
                                            <h3 class="fs-17 fw-600">
                                                {{ ('Write a review')}}
                                            </h3>
                                        </div>
                                        @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <ul class="mb-1">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <form class="form-default" role="form" action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="" class="text-uppercase c-gray-light">{{ ('Your name')}}</label>
                                                        <input type="text" name="name" value="{{ @Auth::user()->name ?? '' }}" class="form-control" @if (@Auth::user()->name) readonly @endif required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="" class="text-uppercase c-gray-light">{{ ('Email')}}</label>
                                                        <input type="text" name="email" value="{{ @Auth::user()->email ?? '' }}" class="form-control" @if (@Auth::user()->email) readonly @endif>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="opacity-60">{{ ('Rating')}}</label>
                                                <div class="rating rating-input">
                                                    <label>
                                                        <input type="radio" name="rating" required value="1">
                                                        <i class="las la-star"></i>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="rating" value="2">
                                                        <i class="las la-star"></i>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="rating" value="3">
                                                        <i class="las la-star"></i>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="rating" value="4">
                                                        <i class="las la-star"></i>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="rating" value="5">
                                                        <i class="las la-star"></i>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="opacity-60">{{ ('Comment')}}</label>
                                                <textarea class="form-control rounded" rows="4" name="comment" placeholder="{{ ('Share details of your own experience about this product')}}" required></textarea>
                                            </div>

                                            @if ($canUploadImage)
                                            <div class="controls">
                                                <div class="entry input-group upload-input-group">
                                                    <input class="form-control" name="reviewPhotos[]" type="file">
                                                    <button class="btn btn-upload btn-success btn-add" type="button">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="text-right">
                                                <button type="submit" class="btn btn-primary mt-3">
                                                    {{ ('Submit review')}}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div> --}}

                    {{-- Mobile View Start --}}
                    <div class="d-block d-md-none">
                        @if($Highlights)
                        {{-- Highlight Tab For Mobile View Start --}}
                        <div class="bg-white mb-3  rounded border">
                            <div class="nav border-bottom aiz-nav-tabs">
                                <a role="button" class="p-2 p-md-3 fs-16 fw-600 text-reset active show">{{ ('Highlights') }}</a>
                            </div>

                            <div class="tab-content pt-0">
                                <div class="tab-pane fade active show">
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
                        {{-- Highlight Tab For Mobile View End --}}
                        @endif

                        @if($HowToUseData)
                        {{-- How To Use Tab For Mobile View Start --}}
                        <div class="bg-white mb-3  rounded border">
                            <div class="nav border-bottom aiz-nav-tabs">
                                <a role="button" class="p-2 p-md-3 fs-16 fw-600 text-reset active show">{{ ('How To Use') }}</a>
                            </div>

                            <div class="tab-content pt-0">
                                <div class="tab-pane fade active show">
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
                        {{-- How To Use Tab For Mobile View Start --}}
                        @endif

                        @if($KeyIngredients)
                        {{-- Key Ingredients Tab For Mobile View Start --}}
                        <div class="bg-white mb-3  rounded border">
                            <div class="nav border-bottom aiz-nav-tabs">
                                <a role="button" class="p-2 p-md-3 fs-16 fw-600 text-reset active show">{{ ('Key Ingredients') }}</a>
                            </div>

                            <div class="tab-content pt-0">
                                <div class="tab-pane fade active show">
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
                        {{-- Key Ingredients Tab For Mobile View Start --}}
                        @endif

                        @if($Ingredients)
                        {{-- Ingredients Tab For Mobile View Start --}}
                        <div class="bg-white mb-3  rounded border">
                            <div class="nav border-bottom aiz-nav-tabs">
                                <a role="button" class="p-2 p-md-3 fs-16 fw-600 text-reset active show">{{ ('Ingredients') }}</a>
                            </div>

                            <div class="tab-content pt-0">
                                <div class="tab-pane fade active show">
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
                        {{-- Ingredients Tab For Mobile View Start --}}
                        @endif
                    </div>
                    {{-- Mobile View End --}}

                    {{-- Rating & Reviews --}}
                    <div class="product-faq bg-white mb-3  rounded border">
                        <div class="p-4  ">
                            <div class="bg-white rounded ">
                                <div class="mb-3">
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
                                    <div class="pt-3">
                                        <ul class="list-group list-group-flush">
                                            @foreach ($detailedProduct->reviews as $key => $review)
                                                <li class="media list-group-item p-0 {{ $loop->last ? 'mb-0' : 'mb-2' }}">
                                                    <div class="d-flex">
                                                        <span class="avatar avatar-md mr-3">
                                                            <img
                                                                class="lazyload"
                                                                src="{{ static_asset('assets/img/user.png') }}"
                                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/user.png') }}';"
                                                                @if (@$review->user->avatar_original != null)
                                                                    data-src="{{ uploaded_asset($review->user->avatar_original) }}"
                                                                @else
                                                                    data-src="{{ static_asset('assets/img/user.png') }}"
                                                                @endif
                                                            >
                                                        </span>
                                                        <div class="media-body text-left">
                                                            <div class="d-flex justify-content-between">
                                                                <h3 class="fs-15 fw-600 mb-0">
                                                                    {{ $review->name ?: 'Anonymous' }}
                                                                </h3>
                                                                <span class="rating rating-sm">
                                                                    @for ($i = 0; $i < $review->rating; $i++)
                                                                        <i class="las la-star active"></i>
                                                                    @endfor
                                                                    @for ($i = 0; $i < 5 - $review->rating; $i++)
                                                                        <i class="las la-star"></i>
                                                                    @endfor
                                                                </span>
                                                            </div>
                                                            <div class="opacity-60 mb-1">{{ @$review->created_at->diffForHumans() }}</div>
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
                                                        @if (count($reviewPhotos) > 0)
                                                        <div class="lbt-gallery d-flex">
                                                            @foreach ($reviewPhotos as $photo)
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
                                            @endforeach
                                        </ul>

                                        @if (count($detailedProduct->reviews) <= 0)
                                            <div class="text-center fs-18 opacity-70">
                                                {{ ('There have been no reviews for this product yet.') }}
                                            </div>
                                        @endif

                                        @if (@$commentable)
                                            <div class="pt-4">
                                                <div class="border-bottom mb-4">
                                                    <h3 class="fs-17 fw-600">
                                                        {{ ('Write a review') }}
                                                    </h3>
                                                </div>
                                                @if ($errors->any())
                                                    <div class="alert alert-danger">
                                                        <ul class="mb-1">
                                                            @foreach ($errors->all() as $error)
                                                                <li>{{ $error }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                                <form class="form-default" role="form" action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="" class="text-uppercase c-gray-light">{{ ('Your name') }}</label>
                                                                <input type="text" name="name" value="{{ @Auth::user()->name ?? '' }}" class="form-control" @if (@Auth::user()->name) readonly @endif required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="" class="text-uppercase c-gray-light">{{ ('Email') }}</label>
                                                                <input type="text" name="email" value="{{ @Auth::user()->email ?? '' }}" class="form-control" @if (@Auth::user()->email) readonly @endif>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="opacity-60">{{ ('Rating') }}</label>
                                                        <div class="rating rating-input">
                                                            <label>
                                                                <input type="radio" name="rating" required value="1">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                            <label>
                                                                <input type="radio" name="rating" value="2">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                            <label>
                                                                <input type="radio" name="rating" value="3">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                            <label>
                                                                <input type="radio" name="rating" value="4">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                            <label>
                                                                <input type="radio" name="rating" value="5">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="opacity-60">{{ ('Comment') }}</label>
                                                        <textarea class="form-control rounded" rows="4" name="comment" placeholder="{{ ('Share details of your own experience about this product') }}" required></textarea>
                                                    </div>

                                                    @if ($canUploadImage)
                                                    <div class="controls">
                                                        <div class="entry input-group upload-input-group">
                                                            <input class="form-control" name="reviewPhotos[]" type="file">
                                                            <button class="btn btn-upload btn-success btn-add" type="button">
                                                                <i class="fa fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="text-right">
                                                        <button type="submit" class="btn btn-primary mt-3">
                                                            {{ ('Submit review') }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FAQs --}}
                    @php
                        $Faqs=data_get($customFieldsData, 'faqs.value', []);
                    @endphp
                    @if($Faqs)
                        <div class="product-faq bg-white mb-3  rounded border">
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

                    @if(isset($relatedVideos) && is_array($relatedVideos) && !empty($relatedVideos))
                        @include(config('app.theme').'frontend.components.products.related_videos', ['relatedVideos' => $relatedVideos, 'productThumb' => uploaded_asset($detailedProduct->thumbnail_img)])
                    @endif

                    @php
                        $relatedProducts = filter_products(\App\Models\Product::with('thumbnail_image', 'stocks', 'productprices')->withCount('reviews')->withAvg('reviews', 'rating')->where('category_id', $detailedProduct->category_id)->where('id', '!=', $detailedProduct->id))->inRandomOrder()->limit(6)->get();
                    @endphp
                    @if($relatedProducts->isNotEmpty())
                    <div class="bg-white rounded ">
                        <div class="d-flex align-items-center justify-content-between pt mt-2 mt-md-0 py-md-3">
                            <h3 class=" fw-600 mb-0">
                                <span class="mr-4 d-flex align-items-center related-prudct ">
                                    <span><svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M18 34.5C20.9837 34.5 23.8452 33.3147 25.955 31.205C28.0648 29.0952 29.25 26.2337 29.25 23.25C29.25 21.951 28.905 20.7045 28.5 19.545C25.9995 22.0155 24.1005 23.25 22.8 23.25C28.7925 12.75 25.5 8.25 16.5 2.25C17.25 9.75 12.306 13.161 10.293 15.0555C8.65101 16.6 7.51087 18.6022 7.02044 20.8025C6.53001 23.0028 6.71191 25.2996 7.54254 27.3953C8.37317 29.491 9.81423 31.2887 11.6789 32.5556C13.5435 33.8224 15.7457 34.4998 18 34.5ZM19.065 7.8525C23.9265 11.9775 23.9505 15.183 20.1945 21.7635C19.053 23.763 20.4975 26.25 22.8 26.25C23.832 26.25 24.876 25.95 25.9785 25.3575C25.6517 26.592 25.0428 27.7337 24.1998 28.6929C23.3567 29.652 22.3025 30.4023 21.1201 30.8848C19.9378 31.3673 18.6596 31.5687 17.3862 31.4731C16.1127 31.3776 14.8789 30.9878 13.7817 30.3344C12.6846 29.6809 11.7541 28.7817 11.0635 27.7075C10.373 26.6334 9.94123 25.4136 9.80225 24.1441C9.66326 22.8747 9.82086 21.5904 10.2626 20.3922C10.7044 19.1941 11.4183 18.1149 12.348 17.2395C12.537 17.0625 13.4955 16.212 13.5375 16.1745C14.1735 15.6045 14.697 15.099 15.2145 14.5455C17.0595 12.5685 18.3855 10.3755 19.0635 7.8525H19.065Z" fill="#FF8A00"/>
                                        </svg>
                                    </span>
                                    <span class="text-capitalize">{{ ('Related Products') }}</span>
                                </span>
                            </h3>
                            <!-- <a class="see-all-related-product" href="">See All</a> -->
                        </div>
                        <div class="pt-1 py-md-2 mb-2 related-product-grid" style="padding: 0 10px;">
                            <div class="row related_product_holder">
                                @foreach ($relatedProducts as $key => $related_product)
                                <div class="carousel-box col-md-2 col-6 px-1 px-md-2">
                                    @include(config('app.theme').'frontend.partials.product_box_1',['product' => $related_product])
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- static trusted start -->
                    <!-- <div>
                        <div class="trusted-wrapper mt-4">
                            <div class="trusted-item">
                                <div class="trusted-img-box">
                                    <img src="{{ asset('public/assets/img/trusted-1.png') }}" alt="trusted image">
                                </div>
                                <div class="trusted-content-box">
                                    <h3>100%</h3>
                                    <p>authentic product</p>
                                </div>
                            </div>

                            <div class="trusted-item">
                                <div class="trusted-img-box">
                                    <img src="{{ asset('public/assets/img/trusted-2.png') }}" alt="trusted image">
                                </div>
                                <div class="trusted-content-box">
                                    <h3>Fastest</h3>
                                    <p>Delivery within 24th</p>
                                </div>
                            </div>
                            <div class="trusted-item">
                                <div class="trusted-img-box">
                                    <img src="{{ asset('public/assets/img/trusted-3.png') }}" alt="trusted image">
                                </div>
                                <div class="trusted-content-box">
                                    <h3>10000+</h3>
                                    <p>Authentic Products</p>
                                </div>
                            </div>
                            <div class="trusted-item">
                                <div class="trusted-img-box">
                                    <img src="{{ asset('public/assets/img/trusted-4.png') }}" alt="trusted image">
                                </div>
                                <div class="trusted-content-box">
                                    <h3>Free</h3>
                                    <p>support 24hours</p>
                                </div>
                            </div>

                        </div>
                    </div> -->
                    <!-- static  trusted end-->
                    {{-- Home ads banner 3
                    @if(json_decode(get_setting('home_adsbanner3_images')) != null)
                    @includeIf('tekka.frontend.components.home_ads_banner.banner3')
                    @endif--}}
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
                    <h5 class="modal-title fw-600 h5">{{ ('Any query about this product') }}</h5>
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
                        <button type="button" class="btn btn-outline-primary fw-600" data-dismiss="modal">{{ ('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary fw-600">{{ ('Send') }}</button>
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
                    <h6 class="modal-title fw-600">{{ ('Login') }}</h6>
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
                                    <input type="text" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{ ('Email Or Phone') }}" name="email" id="email">
                                @else
                                    <input type="email" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{ ('Email') }}" name="email">
                                @endif
                                @if (addon_is_activated('otp_system'))
                                    <span class="opacity-60">{{ ('Use country code before number') }}</span>
                                @endif
                            </div>

                            <div class="form-group">
                                <input type="password" name="password" class="form-control h-auto form-control-lg" placeholder="{{ ('Password') }}">
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span class=opacity-60>{{ ('Remember Me') }}</span>
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                                <div class="col-6 text-right">
                                    <a href="{{ route('password.request') }}" class="text-reset opacity-60 fs-14">{{ ('Forgot password?') }}</a>
                                </div>
                            </div>

                            <div class="mb-5">
                                <button type="submit" class="btn btn-primary btn-block fw-600">{{ ('Login') }}</button>
                            </div>
                        </form>

                        <div class="text-center mb-3">
                            <p class="text-muted mb-0">{{ ('Dont have an account?') }}</p>
                            <a href="{{ route('user.registration') }}">{{ ('Register Now') }}</a>
                        </div>
                        @if (get_setting('google_login') == 1 || get_setting('facebook_login') == 1 || get_setting('twitter_login') == 1)
                            <div class="separator mb-3">
                                <span class="bg-white px-3 opacity-60">{{ ('Or Login With') }}</span>
                            </div>
                            <ul class="list-inline social colored text-center mb-5">
                                @if (get_setting('facebook_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'facebook']) }}" class="facebook">
                                            <i class="lab la-facebook-f"></i>
                                        </a>
                                    </li>
                                @endif
                                @if (get_setting('google_login') == 1)
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

@section('script')

    {{-- @if (env('GOOGLE_TAG_MANAGE') == 'ON') --}}
    @if (get_setting('google_tagmanager'))
    {{-- google tag manager --}}
    <script type = "text/javascript">
        dataLayer.push({ ecommerce: null });
        dataLayer.push({
            event    : "view_item",
            ecommerce: {
                items: [{
                    item_name     : "{{ $detailedProduct->name }}",
                    item_id       : "{{ $detailedProduct->id }}",
                    price         : "{{ $webPrice }}",
                    item_brand    : "{{ $detailedProduct->brand->name ?? '' }}",
                    item_category : "{{ $detailedProduct->category->name ?? '' }}",
                    item_variant  : "{{ $detailedProduct->variant_product ?? '' }}",
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

    <script type="text/javascript">
        $(document).ready(function() {
            getVariantPrice();
            $('.product-gallery .slick-list').css('height', '100%');
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

        @if (!empty($errors->all()))
            @foreach ($errors->all() as $error)
                AIZ.plugins.notify('danger', '{{ $error }}');
            @endforeach @endif

                // dynamic updating
                const ratingCounts = {
                    fiveStars: Number('{{ collect($detailedProduct->reviews)->where('rating', 5)->count() ?? 0 }}'),
                    fourStars: Number('{{ collect($detailedProduct->reviews)->where('rating', 4)->count() ?? 0 }}'),
                    threeStars: Number('{{ collect($detailedProduct->reviews)->where('rating', 3)->count() ?? 0 }}'),
                    twoStars: Number('{{ collect($detailedProduct->reviews)->where('rating', 2)->count() ?? 0 }}'),
                    oneStar: Number('{{ collect($detailedProduct->reviews)->where('rating', 1)->count() ?? 0 }}')
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
<script type="text/javascript">
    function productDetails(){
        return {
            showShare: false,
        }
    }
</script>
