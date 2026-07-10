@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="d-flex justify-content-start align-items-center">
                <a href="{{ route('highlightedProduct.index') }}" class="mr-2 btn btn-soft-secondary btn-icon btn-circle btn-sm">
                    <i class="las la-long-arrow-alt-left"></i>
                </a>
                <h5 class="mb-0">Create Highlight Item</h5>
            </div>
        </div>
        <form id="highlight-product-form" action="{{ route('highlightedProduct.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('backend.product.highlighted.fields')
        </form>
        <div class="text-right">
            <button type="button" class="btn btn-sm btn-secondary" id="clear-btn">Clear</button>
            <button type="submit" form="highlight-product-form" class="btn btn-sm btn-primary" id="create-btn">Create</button>
        </div>
    </div>
</div>
@endsection

@section('script')
@include('backend.product.highlighted.script')
@endsection
