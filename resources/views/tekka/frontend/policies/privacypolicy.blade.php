@extends(($isApp) ? config('app.theme').'frontend.layouts.blank' : config('app.theme').'frontend.layouts.app')

@php
    $privacy_policy =  \App\Models\Page::where('type', 'privacy_policy_page')->first();
@endphp

@section('meta')
<x-seo :meta="[
    'title' => $privacy_policy->meta_title ?? $privacy_policy->title,
    'description' => $privacy_policy->meta_description,
    'image' => $privacy_policy->meta_img,
]" />
@endsection

@section('content')
@if(!$isApp)
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ $privacy_policy->getTranslation('title') }}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ ('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('privacypolicy') }}">"{{ ('Privacy Policy') }}"</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
@endif
<section class="mb-4">
    <div class="container">
        <div class="@if(!$isApp) p-4 bg-white rounded shadow-sm overflow-hidden mw-100 text-left @else py-3 @endif">
            @php
                echo $privacy_policy->getTranslation('content');
            @endphp
        </div>
    </div>
</section>
@endsection
