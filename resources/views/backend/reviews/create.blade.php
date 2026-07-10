@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="d-flex justify-content-start align-items-center">
                <a href="{{ route('reviews.index') }}" class="mr-2 btn btn-soft-secondary btn-icon btn-circle btn-sm">
                    <i class="las la-long-arrow-alt-left"></i>
                </a>
                <h5 class="mb-0">Create Reviews</h5>
            </div>
        </div>
        <form id="review-form" action="{{ route('reviews.admin_store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('backend.reviews.fields')
        </form>
        <div class="text-right">
            <button type="button" class="btn btn-secondary" id="clear-btn">Clear</button>
            <button type="submit" form="review-form" class="btn btn-primary" id="create-btn">Create</button>
        </div>
    </div>
</div>
@endsection

@section('script')
@include('backend.reviews.script')
@endsection
