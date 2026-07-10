@extends(config('app.theme').'frontend.layouts.app')

@section('meta')
<x-seo :meta="[
    'title' => $shop->meta_title,
    'description' => $shop->meta_description,
    'image' => $shop->logo,
    'twitter' => [
        'card' => 'website',
        'image' => $shop->meta_img,
    ],
    'og' => [
        'site_name' => $shop->name,
    ]
]" />
@endsection

@section('content')

    @php
        $total = 0;
        $rating = 0;
        foreach ($shop->user->products as $key => $seller_product) {
            $total += $seller_product->reviews->count();
            $rating += $seller_product->reviews->sum('rating');
        }
    @endphp

    <section class="py-5 mb-4 bg-white">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="d-flex justify-content-center">
                        <img
                            height="70"
                            class="lazyload"
                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                            data-src="@if ($shop->logo !== null) {{ uploaded_asset($shop->logo) }} @else {{ static_asset('assets/img/placeholder.jpg') }} @endif"
                            alt="{{ $shop->name }}"
                        >
                        <div class="pl-4">
                            <h1 class="fw-600 h4 mb-0">{{ $shop->name }}
                                @if ($shop->user->seller->verification_status == 1)
                                    <span class="ml-2"><i class="fa fa-check-circle" style="color:green"></i></span>
                                @else
                                    <span class="ml-2"><i class="fa fa-times-circle" style="color:red"></i></span>
                                @endif
                            </h1>
                            <div class="rating rating-sm mb-1">
                                @if ($total > 0)
                                    {!! renderStarRating($rating/$total) !!}
                                @else
                                    {!! renderStarRating(0) !!}
                                @endif
                            </div>
                            <div class="location opacity-60">{{ $shop->address }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="container">
            <div class="row">
                <div class="col-xxl-5 col-xl-6 col-md-8 mx-auto">
                    <div class="bg-white rounded shadow-sm p-4 text-center">
                        <h3 class="fw-600 h4">
                            {{$seller->user->name}} {{ ('has not been verified yet.')}}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
