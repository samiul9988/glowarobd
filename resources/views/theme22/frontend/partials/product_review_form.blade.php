<div class="border-bottom mb-4">
    <h3 class="fs-17 fw-600">
        {{ ('Write a review')}}
    </h3>
</div>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form class="form-default" role="form" action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="product_id" value="{{ $product['id'] }}">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="" class="text-uppercase c-gray-light">{{ ('Your name')}}</label>
                <input type="text" name="name" value="{{ @Auth::user()->name ?? '' }}" class="form-control" @if(@Auth::user()->name) readonly @endif required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="" class="text-uppercase c-gray-light">{{ ('Email')}}</label>
                <input type="text" name="email" value="{{ @Auth::user()->email ?? '' }}" class="form-control" @if(@Auth::user()->email) readonly @endif>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="opacity-60">{{ ('Rating')}}</label>
        <div class="rating rating-input">
            <label>
                <input type="radio" name="rating" required value="1">
                <i class="las la-star"></i>
            </label>
            <label>
                <input type="radio" name="rating" value="2">
                <i class="las la-star"></i>
            </label>
            <label>
                <input type="radio" name="rating" value="3">
                <i class="las la-star"></i>
            </label>
            <label>
                <input type="radio" name="rating" value="4">
                <i class="las la-star"></i>
            </label>
            <label>
                <input type="radio" name="rating" value="5">
                <i class="las la-star"></i>
            </label>
        </div>
    </div>

    <div class="form-group">
        <label class="opacity-60">{{ ('Comment')}}</label>
        <textarea class="form-control rounded" rows="4" name="comment" placeholder="{{ ('Share details of your own experience about this product')}}" required></textarea>
    </div>

    @if($canUploadImage)
    <div class="controls">
        <div class="entry input-group upload-input-group">
            <input class="form-control" name="reviewPhotos[]" type="file">
            <button class="btn btn-upload btn-success btn-add" type="button">
                <i class="fa fa-plus"></i>
            </button>
        </div>
    </div>
    @endif

    <div class="text-right">
        <button type="submit" class="btn btn-primary mt-3">
            {{ ('Submit review')}}
        </button>
    </div>
</form>