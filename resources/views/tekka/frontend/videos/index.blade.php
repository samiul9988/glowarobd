@extends(config('app.theme') . 'frontend.layouts.app')

@section('meta')
<x-seo />
@endsection

@section('content')
    <style>
        .banner {
            background: linear-gradient(90deg, #fddde6, #ddecfc);
            text-align: center;
            padding: 60px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .banner h1 {
            font-weight: 700;
            font-size: 2.5rem;
        }
        .nav-tabs .nav-link.active {
            border: none;
            border-bottom: 2px solid #f06292;
            background: transparent;
            color: #f06292;
        }
        .nav-tabs .nav-link {
            color: #333;
        }
        .video-thumb {
            position: relative;
            cursor: pointer;
        }
        .video-thumb::after {
            content: '\25B6';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            color: white;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .short-thumb::after {
            font-size: 1.5rem;
            width: 40px;
            height: 40px;
        }
        .short-card img {
            height: 350px;
            object-fit: cover;
        }
    </style>

@php
    $emptySection = 0;
@endphp
<div class="container mt-4">
    <!-- Banner -->
    <div class="banner">
        <h1>{{ $pageInfo->title ?? 'GlowaroTube' }}</h1>
        <p>{!! $pageInfo->content !!}</p>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4 justify-content-center">
        <li class="nav-item">
            <a class="nav-link @if($type === '') active @endif" href="{{ route('videos.userIndex') }}">Home</a>
        </li>
        @if($shortsCount)
        <li class="nav-item">
            <a class="nav-link @if($type === 'shorts') active @endif" href="{{ route('videos.userIndex', ['type' => 'shorts']) }}">Shorts</a>
        </li>
        @endif
        @if($videosCount)
        <li class="nav-item">
            <a class="nav-link @if($type === 'videos') active @endif" href="{{ route('videos.userIndex', ['type' => 'videos']) }}">Videos</a>
        </li>
        @endif
        @if($playlistsCount)
        <li class="nav-item">
            <a class="nav-link @if($type === 'playlists') active @endif" href="{{ route('videos.userIndex', ['type' => 'playlists']) }}">Playlists</a>
        </li>
        @endif
    </ul>

    @if(($type === 'shorts' && $shortsCount) || ($type === '' && $shorts->isNotEmpty()))
        <!-- Shorts heading -->
        <h5 class="font-weight-bold mb-4">Shorts</h5>

        <!-- Shorts Grid -->
        <div class="row mb-4 justify-content-center">
            @forelse ($shorts as $short)
            <div class="col-md-2 col-6 mb-4 short-card">
                <a href="{{ route('videos.show', $short->slug) }}">
                    <div class="video-thumb short-thumb">
                        <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset($short->thumbnail) }}" class="img-fluid w-100 rounded @if(app()->isProduction()) lazyload @endif" style="border-radius: 10px !important" alt="{{ Str::limit($short->title, 25) }}">
                    </div>
                </a>
            </div>
            @empty
                <div class="col-12 text-center">
                    <p class="h6">No shorts available at the moment.</p>
                </div>
            @endforelse
        </div>

        @if($shortsCount > 5 && $type === '')
            <div class="text-center mb-5">
                <a href="{{ route('videos.userIndex', ['type' => 'shorts']) }}" class="btn btn-dark">View More</a>
            </div>
        @elseif($type === 'shorts')
            {{ $shorts->appends(request()->input())->links() }}
        @endif
    @else
        @php
            $emptySection++;
        @endphp
    @endif

    @if(($type === 'videos' && $videosCount) || ($type === '' && $videos->isNotEmpty()))
        <!-- Videos heading -->
        <h5 class="font-weight-bold mb-4">Videos</h5>

        <!-- Video Grid -->
        <div class="row mb-5 justify-content-center">
            @forelse ($videos as $video)
                <div class="col-md-4 col-sm-6 mb-2">
                    <a href="{{ route('videos.show', $video->slug) }}" class="rounded-lg shadow-sm">
                        <div class="video-thumb">
                            <img style="border-radius: 20px 20px 0 0 !important" src={{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset($video->thumbnail) }}" class="img-fluid w-100 rounded @if(app()->isProduction()) lazyload @endif" alt="{{ Str::limit($video->title, 25) }}">
                        </div>
                        <p style="border-radius: 0 0 20px 20px !important; background: #d3d3d3 !important;" class="py-4 px-3 font-weight-bold fs-16 text-capitalize">{{ Str::limit($video->title, 100) }}</p>
                    </a>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p class="h6">No videos available at the moment.</p>
                </div>
            @endforelse
        </div>

        @if($videosCount > 8 && $type === '')
            <div class="text-center mb-5">
                <a href="{{ route('videos.userIndex', ['type' => 'videos']) }}" class="btn btn-dark">View More</a>
            </div>
        @elseif($type === 'videos')
            {{ $videos->appends(request()->input())->links() }}
        @endif
    @else
        @php
            $emptySection++;
        @endphp
    @endif

    @if(($type === 'playlists' && $playlistsCount) || ($type === '' && $playlists->isNotEmpty()))
        <!-- Multiple playlists heading -->
        <h5 class="font-weight-bold mb-4">Multiple Playlists</h5>

        <!-- Playlist Grid -->
        <div class="row mb-5 justify-content-center">
            @forelse ($playlists as $playlist)
                <div class="col-md-3 col-sm-6 mb-4">
                    <a href="{{ route('video-playlists.show', $playlist->slug) }}">
                        <div class="card h-100" style="border-radius: 10px !important; overflow: hidden;">
                            <div class="position-relative">
                                <img src="{{ static_asset('assets/img/placeholder.jpg') }}" class="card-img-top @if(app()->isProduction()) lazyload @endif" data-src="{{ uploaded_asset($playlist->thumbnail) }}" alt="{{ Str::limit($playlist->name, 25) }}">
                                <div class="position-absolute text-white bg-dark px-2 py-1 small" style="bottom: 8px; right: 8px; border-radius: 4px;">
                                    🎵 {{ $playlist->videos_count }} Videos
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title mb-2 text-capitalize">{{ Str::limit($playlist->name) }}</h6>
                                <p class="text-muted mb-0">View full playlist</p>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p class="h6">No playlists available at the moment.</p>
                </div>
            @endforelse
        </div>

        @if($playlistsCount > 15 && $type === '')
            <div class="text-center mb-5">
                <a href="{{ route('videos.userIndex', ['type' => 'playlists']) }}" class="btn btn-dark">View More</a>
            </div>
        @elseif($type === 'playlists')
            {{ $playlists->appends(request()->input())->links() }}
        @endif
    @else
        @php
            $emptySection++;
        @endphp
    @endif

    @if ($emptySection === 3)
        <div class="text-center mt-5">
            <p class="h6">No videos available at this moment.</p>
        </div>
    @endif
</div>
@endsection
