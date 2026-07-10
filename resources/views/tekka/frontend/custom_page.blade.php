@extends(config('app.theme').'frontend.layouts.app')

@section('meta')
<x-seo :meta="[
    'title' => $page->meta_title ?? $page->title,
    'description' => $page->meta_description,
    'image' => $page->meta_img,
]" />
@endsection

@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ $page->title }}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ ('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('custom-pages.show_custom_page', $page->slug ) }}">"{{ $page->title }}"</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="mb-4">
	<div class="container">
        <div class="p-4 bg-white rounded shadow-sm overflow-hidden mw-100 text-left">
		    {!! $page->content !!}
        </div>
	</div>
</section>
@endsection
