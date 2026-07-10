@extends(config('app.theme') . 'frontend.layouts.app')
@section('content')
    <section class="pt-4 mb-4">
        <div class="container text-center">
            <div class="row">
                <div class="col-lg-6 text-center text-lg-left">
                    <h1 class="fw-600 h4">Campaign & Offers</h1>
                </div>
                <div class="col-lg-6">
                    <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                        <li class="breadcrumb-item opacity-50">
                            <a class="text-reset" href="{{ route('home') }}">{{ ('Home') }}</a>
                        </li>
                        <li class="breadcrumb-item opacity-50">
                            <a class="text-reset"
                                href="{{ route('customer.campaign') }}">{{ ('Campaign & Offers') }}</a>
                        </li>
                        <li class="text-dark fw-600 breadcrumb-item">
                            <span class="text-reset">"{{ Str::limit($campaign->title, 50) }}"</sp>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <section class="mb-4">
        <div class="container" style="padding-inline: 15rem;">
            <div>
                <h1 class="fw-600 h4 mb-3">{{ $campaign->title }}</h1>
                @if ($campaign->start_date || $campaign->end_date)
                    <p class="text-muted mb-3">
                        {!! $campaign->formatted_date !!}
                    </p>
                @endif
            </div>
            <div class="text-center mb-3">
                <img src="{{ uploaded_asset($campaign->thumbnail) }}" alt="{{ $campaign->title }}" class="img-fluid mb-3"
                    style="max-height: 300px; width: auto;">
            </div>
            <div>
                {!! $campaign->description !!}
            </div>
        </div>
    </section>
    <style>
        .campaign-card {
            border-radius: 10px;
            overflow: hidden;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .campaign-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            transition: box-shadow 0.3s ease;
        }

        .campaign-card:hover .section-title {
            color: #e2136e;
        }

        .campaign-card img {
            width: 100%;
            height: 250px;
        }

        .card-body p {
            margin-bottom: 5px;
        }

        .section-title {
            font-weight: bold;
        }

        .btn-outline-pink {
            border: 1px solid #e2136e;
            color: #e2136e;
            border-radius: 30px;
            padding: 5px 20px;
        }

        .btn-outline-pink:hover {
            background-color: #e2136e;
            color: white;
        }
    </style>
    @if ($relatedCampaigns->count())
        <section class="mb-4" style="background-color: #ffeff6">
            <div class="container py-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title">{{ ('Latest Campaigns') }}</h2>
                    <a href="{{ route('customer.campaign') }}" class="btn btn-outline-pink">{{ ('See All') }}</a>
                </div>

                <div class="row">
                    @foreach ($relatedCampaigns as $relatedCampaign)
                        <div class="col-md-4 mb-4">
                            <a href="{{ route('campaigns.show', $relatedCampaign->slug) }}">
                                <div class="campaign-card">
                                    <img src="{{ uploaded_asset($relatedCampaign->thumbnail) }}" alt="Campaign Image">
                                    <div class="card-body">
                                        <span class="d-block h5 section-title">
                                                {{ Str::limit($relatedCampaign->title) }}
                                        </span>
                                        @if ($relatedCampaign->start_date || $relatedCampaign->end_date)
                                            <p class="text-danger">{!! $relatedCampaign->formatted_date !!}</p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
