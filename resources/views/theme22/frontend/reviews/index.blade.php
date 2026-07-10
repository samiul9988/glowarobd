@extends(config('app.theme').'frontend.layouts.app')

@section('content')
@if($videoReviews->count())
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
                @php
                    $videoCount = 0;
                @endphp
                @foreach ($videoReviews as $review)
                    @foreach ($review->videos as $video)
                        @php
                            ++$videoCount;
                        @endphp
                        @if($videoCount > 20) @break @endif
                        <div class="col-lg-3 col-md-4 mb-3">
                            <iframe class="embed-responsive-item" src="{{ get_yt_embed($video).'?autoplay=1' }}" allowfullscreen></iframe>
                        </div>
                    @endforeach
                @endforeach
            </div>

            @if($videoCount > 20)
                <div class="text-center">
                    <a href="{{ route('reviews.filter_type', 'video') }}" class="btn btn-primary btn-sm">
                        View All Reviews
                    </a>
                </div>
            @endif
        </div>
    </section>
@endif

@if($imageReviews->count())
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
                @php
                    $imageCount = 0;
                @endphp
                @foreach ($imageReviews as $review)
                    @php
                        $images = explode(',', $review->photos ?? '') ?? [];
                    @endphp
                    @foreach ($images as $image)
                        @php
                            ++$imageCount;
                        @endphp
                        @if($imageCount > 20) @break @endif
                        <div class="col-md-3 mb-3">
                            <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset($image) }}" class="img-fluid rounded lazyload" alt="" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </div>
                    @endforeach
                @endforeach
            </div>

            @if($imageCount > 20)
            <div class="text-center">
                <a href="{{ route('reviews.filter_type', 'image') }}" class="btn btn-primary btn-sm">
                    View All Reviews
                </a>
            </div>
            @endif
        </div>
    </section>
@endif

@if($textReviews->count())
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
                @foreach ($textReviews as $review)
                    @if($loop->index >= 20) @break @endif
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

            @if ($textReviews->count() > 20)
            <div class="text-center">
                <a href="{{ route('reviews.filter_type', 'text') }}" class="btn btn-primary btn-sm">
                    View All Reviews
                </a>
            </div>
            @endif
        </div>
    </section>
@endif
@endsection
