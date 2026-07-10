@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="d-flex justify-content-start align-items-center">
                <a href="{{ route('videos.index') }}" class="mr-2 btn btn-soft-secondary btn-icon btn-circle btn-sm">
                    <i class="las la-long-arrow-alt-left"></i>
                </a>
                <h5 class="mb-0">Edit Video</h5>
            </div>
        </div>
        <form id="video-form" action="{{ route('videos.update', $video->id) }}" method="POST" enctype="multipart/form-data">
            @method('PUT')
            @include('backend.videos.fields', ['video' => $video, 'videoFile' => $videoFile])
        </form>
        <div class="text-right">
            <button type="button" class="btn btn-sm btn-secondary" id="clear-btn" onclick="resetForm()">Clear</button>
            <button type="submit" form="video-form" class="btn btn-sm btn-primary" id="create-btn">Update</button>
        </div>
    </div>
</div>
@endsection

@section('script')
    @include('backend.videos.script')
@endsection
