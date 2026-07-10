@php
    $photos = null;
    $attachments = null;
    if (@$review) {
        if ($review->review_type === 'image') {
            $photos = $review->photos;
        } elseif ($review->review_type === 'text') {
            $attachments = $review->photos;
        }
    }
@endphp
<div class="row gutters-5">
    <div class="col-md-6">
        <div class="form-group">
            <label for="type">Review Type</label>
            <select class="form-control" name="type" id="type">
                <option value="text" selected>Text</option>
                <option value="image" @if(@$review->review_type === 'image') selected @endif>Image</option>
                <option value="video" @if(@$review->review_type === 'video') selected @endif>Video</option>
            </select>
            <span class="text-danger error" id="type_error">
                @error('type')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="product">Product</label>
            <select class="form-control aiz-selectpicker" data-live-search="true" name="product" id="product">
                <option value="">Loading ...</option>
            </select>
            <span class="text-danger error" id="product_error">
                @error('product')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="customer">Customer</label>
            <select class="form-control aiz-selectpicker" data-live-search="true" name="customer" id="customer">
                <option value="">Loading ...</option>
            </select>
            <span class="text-danger error" id="customer_error">
                @error('customer')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Or Display Name</label>
            <input type="text" name="name" id="name" class="form-control"
                placeholder="Choose a customer or enter a name" value="{{ old('name', @$review->name) }}" required>
            <span class="text-danger error" id="name_error">
                @error('name')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="responsive col-md-4">
        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" name="status" id="status">
                <option value="1" selected>Published</option>
                <option value="0" @if (@$review && old('status', @$review->status) == 0) selected @endif>Pending</option>
            </select>
            <span class="text-danger error" id="status_error">
                @error('status')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="responsive col-md-4 text-review-section review-section" id="rating-section">
        <div class="form-group">
            <label for="rating">Rating</label>
            <select class="form-control" name="rating" id="rating">
                <option value="" selected>Select star</option>
                @foreach (range(1,5) as $i)
                    <option value="{{ $i }}" @if(old('rating', @$review->rating) == $i) selected @endif>
                        @foreach (range(1, $i) as $start)
                            ⭐
                        @endforeach
                    </option>
                @endforeach
            </select>
            <span class="text-danger error" id="rating_error">
                @error('rating')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="responsive col-md-4">
        <div class="form-group">
            <label class="col-from-label" for="review_date">
                Review Date @include('components.tooltip', [
                    'title' => 'This date will be used as the review created date. If left empty, current date will be used.',
                ])
            </label>
            <input type="date" class="form-control" name="review_date" value="{{ old('review_date', @$review->created_at?->format('Y-m-d')) }}" id="review_date" max="{{ date('Y-m-d') }}">
            <span class="text-danger error" id="review_date_error">
                @error('review_date')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="col-md-12 text-review-section review-section" id="comment-section">
        <div class="form-group">
            <label for="comment">Comment <span class="text-danger">* </span></label>
            <textarea class="form-control" name="comment" id="comment" rows="5" placeholder="Enter comment here">{{ old('comment', @$review->comment) }}</textarea>
            <span class="text-danger error" id="comment_error">
                @error('comment')
                    {{ $message }}
                @enderror
            </span>
        </div>
        <div class="form-group">
            <label for="attachments">Attachments <span class="text-danger error" id="attachments_error">@error('attachments'){{ $message }}@enderror</span></label>
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                </div>
                <div class="form-control file-amount">Choose Files</div>
                <input type="hidden" name="attachments" id="attachments" class="selected-files" value="{{ old('attachments', $attachments) }}">
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-12 video-review-section review-section" id="videos-section" style="display: none;">
        <div class="form-group">
            <label>{{ ('Links') }} - ({{ ('Videos') }}) <span class="text-danger">* </span><span class="text-danger error" id="video_error">@error('videos'){{ $message }}@enderror</span></label>
            <div class="videos-links-target">
                @forelse (old('videos', @$review->videos ?? []) as $video)
                    <div class="row gutters-5">
                        <div class="col">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="http://" name="videos[]" value="{{ $video ?? '' }}">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                data-toggle="remove-parent" data-parent=".row">
                                <i class="las la-times"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="row gutters-5">
                        <div class="col">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="http://" name="videos[]" value="">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                data-toggle="remove-parent" data-parent=".row">
                                <i class="las la-times"></i>
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>
            <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                data-content='<div class="row gutters-5">
                                    <div class="col">
                                        <div class="form-group">
                                            <input type="text" class="form-control" placeholder="http://" name="videos[]">
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
                                            <i class="las la-times"></i>
                                        </button>
                                    </div>
                                </div>'
                data-target=".videos-links-target">
                {{ ('Add New') }}
            </button>
        </div>
    </div>
    <div class="col-md-12 image-review-section review-section" id="photos-section" style="display: none;">
        <div class="form-group">
            <label for="photos">Photos <span class="text-danger">* </span><span class="text-danger error" id="photos_error">@error('photos'){{ $message }}@enderror</span></label>
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ ('Choose Files') }}</div>
                <input type="hidden" name="photos" id="photos" class="selected-files" value="{{ old('photos', $photos) }}">
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-12 mt-3">
        <div class="form-group d-flex align-items-center">
            <div class="mr-3">
                <label class="col-from-label">Featured ?</label>
            </div>
            <div>
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" id="is_featured" name="featured" value="1" onchange="this.value = this.checked ? 1 : 0" @if (@$review && old('featured', @$review->featured) == 1) checked @endif>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
</div>
