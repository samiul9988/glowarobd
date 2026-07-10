@extends('backend.layouts.app')

@section('content')
<div class="container" style="padding-inline: 15rem;">
    <div class="card">
        <div class="card-header">
            <div class="text-center">
                <span class="h5">{{ $campaign->title }}</span>
            </div>
        </div>
        <div class="card-body">
            @if($campaign->end_date)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="h6 font-weight-bold text-{{ $campaign->end_date->isPast() ? 'danger' : 'success' }}">
                    @if(!$campaign->end_date->isPast())
                        {{ ('Valid Until - ') }} {{ $campaign->end_date->format('d M Y, h:i A') }}
                    @endif
                </span>
            </div>
            @endif
            <div class="text-center mb-3">
                <img src="{{ uploaded_asset($campaign->thumbnail) }}" alt="{{ $campaign->title }}" class="img-fluid mb-3" style="max-height: 300px; width: auto;">
            </div>
            <div class="fs-18">
                <p class="card-text">{!! $campaign->description !!}</p>
            </div>
        </div>
    </div>
</div>
@endsection