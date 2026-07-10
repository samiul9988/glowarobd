@extends(config('app.theme').'frontend.layouts.app')

@section('meta_title'){{ $blog->meta_title }}@stop

@section('meta_description'){{ $blog->meta_description }}@stop

@section('meta_keywords'){{ $blog->meta_keywords }}@stop

@section('meta')
<x-seo :meta="[
    'title' => $blog->meta_title,
    'description' => $blog->meta_description,
    'image' => $blog->meta_img,
    'twitter' => [
        'card' => 'summary',
    ]
]" />

@if($isApp)
    <style>
        header, footer, .top-navbar, .footer-widget, .aiz-mobile-bottom-nav{
            display: none !important;
        }
    </style>
@endif
@endsection

@section('content')

<section class="py-4">
    <div class="container">
        <div class="mb-4">
            <img
                src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                data-src="{{ uploaded_asset($blog->banner) }}"
                alt="{{ $blog->title }}"
                class="img-fluid lazyload w-100"
            >
        </div>
        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="bg-white rounded shadow-sm p-4">
                    <div class="border-bottom">
                        <h1 class="h4">
                            {{ $blog->title }}
                        </h1>

                        @if($blog->category != null)
                        <div class="mb-2 opacity-50">
                            <i>{{ $blog->category->category_name }}</i>
                        </div>
                        @endif
                    </div>
                    <div class="mb-4 overflow-hidden">
                        {!! $blog->description !!}
                    </div>

                    @if (get_setting('facebook_comment') == 1)
                    <div>
                        <div class="fb-comments" data-href="{{ route("blog",$blog->slug) }}" data-width="" data-numposts="5"></div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@endsection


@section('script')
    @if (get_setting('facebook_comment') == 1)
        <div id="fb-root"></div>
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v9.0&appId={{ env('FACEBOOK_APP_ID') }}&autoLogAppEvents=1" nonce="ji6tXwgZ"></script>
    @endif

    @include(config('app.theme').'.frontend.schema.blog')
@endsection
