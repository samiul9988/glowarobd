@extends('backend.layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>{{ $notice->title }}</h2>
            <div class="d-flex">
                <span class="badge badge-inline 
                    @if($notice->status == 'published') badge-success 
                    @elseif($notice->status == 'draft') badge-secondary 
                    @else badge-warning @endif mr-2">
                    {{ ucfirst($notice->status) }}
                </span>
                <span class="badge badge-inline badge-info">
                    {{ ucfirst($notice->visibility) }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <p class="card-text">{!! $notice->content !!}</p>
        </div>
        <div class="card-footer text-muted">
            @if($notice->publish_at)
                Scheduled for: {{ $notice->publish_at->format('Y-m-d H:i') }}
            @else
                Created at: {{ $notice->created_at->format('Y-m-d H:i') }}
            @endif
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('notices.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
@endsection