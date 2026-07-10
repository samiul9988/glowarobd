@extends(($isApp) ? config('app.theme').'frontend.layouts.blank' : config('app.theme').'frontend.layouts.app')
@php
    $terms =  \App\Models\Page::where('type', 'terms_conditions_page')->first();
@endphp

@section('meta')
<x-seo :meta="[
    'title' => $terms->meta_title ?? $terms->title,
    'description' => $terms->meta_description,
    'image' => $terms->meta_img,
]" />
@endsection

@section('content')
@if(!$isApp)
<section class="pt-4">
    <div class="col-12 col-lg-8 mx-auto px-4 text-center">
        <div class="row">
            <div class="col-12 text-center text-lg-left px-4">
                <h1 class="fw-600 h1">{{ $terms->title }}</h1>
            </div>
            {{--
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ ('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('terms') }}">"{{ ('Terms & conditions') }}"</a>
                    </li>
                </ul>
            </div>
            --}}
        </div>
    </div>
</section>
@endif
<section class="mb-4">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="@if(!$isApp) p-4 bg-white rounded shadow-sm overflow-hidden mw-100 text-left @else py-3 @endif">
            @php
                echo $terms->content;
            @endphp
        </div>
    </div>
</section>
@endsection
