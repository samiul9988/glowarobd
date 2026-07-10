@extends(config('app.theme').'frontend.layouts.app')

@section('meta')
<x-seo />
@endsection

@section('content')
@if($type === 'video')
    <section class="pt-4 {{ $agent->isMobile() ? 'mb-2' : 'mb-4' }}">
        <div class="container px-4">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="text-center font-weight-bold">
                        Voices of our <span class="text-info">Top Rated Reviewers</span>
                    </h2>
                </div>
            </div>
            <div class="row gutters-5 mt-4">
                @foreach ($reviews as $review)
                    @foreach ($review->videos as $video)
                        <div class="col-md-3 mb-3">
                            <iframe class="embed-responsive-item" src="{{ get_yt_embed($video).'?autoplay=1' }}" allowfullscreen></iframe>
                        </div>
                    @endforeach
                @endforeach
            </div>

            <div class="text-center">
                {{ $reviews->appends(request()->input())->links() }}
            </div>
        </div>
    </section>
@endif

@if($type === 'image')
    <section class="pt-4 {{ $agent->isMobile() ? 'mb-2' : 'mb-4' }}">
        <div class="container px-4">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="text-center font-weight-bold">
                        Wall of our <span class="text-info">Top Rated Customer Reviews</span>
                    </h2>
                </div>
            </div>
            <div class="row gutters-5 mt-4">
                @foreach ($reviews as $review)
                    @php
                        $images = explode(',', $review->photos ?? '') ?? [];
                    @endphp
                    @foreach ($images as $image)
                        <div class="col-md-3 mb-3">
                            <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset($image) }}" class="img-fluid rounded lazyload" alt="" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </div>
                    @endforeach
                @endforeach
            </div>

            <div class="text-center">
                {{ $reviews->appends(request()->input())->links() }}
            </div>
        </div>
    </section>
@endif

@if($type === 'text')
    <section class="pt-4 {{ $agent->isMobile() ? 'mb-2' : 'mb-4' }}">
        <div class="container px-4">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="text-center font-weight-bold">
                        Customers <span class="text-info">Feedback</span>
                    </h2>
                </div>
            </div>
            <div class="row gutters-5 mt-4">
                @foreach ($reviews as $review)
                    <div class="col-md-3 mb-3">
                        @if($review->product && $review->product->slug)
                        <a href="{{ route('product', $review->product->slug) }}">
                        @endif
                            <div class="card h-100 bg-dark text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ static_asset('assets/img/user.png') }}" alt="Customer" class="img-fluid rounded-circle" style="width: 40px; height: 40px;">
                                        <div class="ml-2">
                                            <span class="d-block">
                                                {{ $review->name ?? $review->user?->name ?? 'Annonymous' }}
                                            </span>
                                            <span class="d-block text-muted" style="font-size: 10px;">
                                                {{ date('d-m-Y', strtotime($review->created_at)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="d-block">
                                            @foreach (range(1, $review->rating ?? 0) as $rating)
                                                <i class="las la-star text-warning"></i>
                                            @endforeach
                                        </span>
                                        <span class="">
                                            {{ $review->comment }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @if($review->product && $review->product->slug)
                        </a>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="text-center">
                {{ $reviews->appends(request()->input())->links() }}
            </div>
        </div>
    </section>
@endif
@endsection
