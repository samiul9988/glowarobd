@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="d-flex justify-content-start align-items-center">
                <a href="{{ route('video-playlists.index') }}" class="mr-2 btn btn-soft-secondary btn-icon btn-circle btn-sm">
                    <i class="las la-long-arrow-alt-left"></i>
                </a>
                <h5 class="mb-0">Edit Category</h5>
            </div>
        </div>
        <form id="playlist-form" action="{{ route('video-playlists.update', $playlist->id) }}" method="POST" enctype="multipart/form-data">
            @method('PUT')
            @include('backend.videos.playlists.fields', ['playlist' => $playlist])
        </form>
        <div class="text-right">
            <button type="button" class="btn btn-secondary" id="clear-btn">Clear</button>
            <button type="submit" form="playlist-form" class="btn btn-primary" id="create-btn">Update</button>
        </div>
    </div>
</div>
@endsection

@section('script')
@include('backend.videos.playlists.script')
@endsection
