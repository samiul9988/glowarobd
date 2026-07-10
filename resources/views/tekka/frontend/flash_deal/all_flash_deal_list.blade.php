@extends(config('app.theme').'frontend.layouts.app')

@section('meta')
<x-seo />
@endsection

@section('content')

<section class="pt-4 mb-4">
    <div class="container text-center">
        @if($all_flash_deals->count() > 0)
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ ('Flash Deals')}}</h1>
            </div>
            {{--
                <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">
                            {{ ('Home')}}
                        </a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('flash-deals') }}">
                            "{{ ('Flash Deals') }}"
                        </a>
                    </li>
                </ul>
            </div>
                --}}
        </div>
        @else
        <div class="d-flex flex-column my-5 text-center">
            <h3 class="h4 fw-700 mt-3">{{ ('No Flash Deal Found')}}</h3>
        </div>
        @endif
    </div>
</section>

<section class="mb-4">
    <div class="container">
        <div class="row row-cols-1 row-cols-lg-2 gutters-10 justify-content-center">
            @foreach($all_flash_deals as $single)
            <div class="col">
                <div class="bg-white rounded shadow-sm mb-3">
                    <a href="{{ route('flash-deal-details', $single->slug) }}" class="d-block text-reset">
                        <img
                            src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                            data-src="{{ uploaded_asset($single->desktop_banner) }}"
                            alt="{{ $single->title }}"
                            class="img-fluid lazyload rounded w-100" style = "max-height: 200px; object-fit: cover">
                    </a>
                    <div class="text-center my-4 text-{{ $single->text_color }}">
                        <a href = "{{ route('flash-deal-details', $single->slug) }}"><h2 class="h4 fw-600" style="color: #1b1b28">{{ $single->title }}</h2></a>
                        <div class="aiz-count-down aiz-count-down-lg ml-md-3 align-items-center justify-content-center" data-date="{{ date('Y/m/d H:i:s', $single->end_date) }}"></div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</section>
@endsection
