@php
    $route = @$notice ? route('notices.update', $notice->id) : route('notices.store');
@endphp
<form id="notice-form" action="{{ $route }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(@$notice)
        @method('PUT')
    @endif
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="title">Title <span class="text-danger"> *</span> <span class="text-danger error font-weight-bold" id="title_error">@error('title') {{ $message }} @enderror</span></label>
                <input type="text" name="title" id="title" class="form-control" placeholder="Enter title" value="{{ old('title', @$notice->title) }}" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="slug">Slug <span class="text-danger error font-weight-bold" id="slug_error">@error('slug') {{ $message }} @enderror</span></label>
                <input type="text" name="slug" id="slug" class="form-control" placeholder="Enter slug" value="{{ old('slug', @$notice->slug) }}" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="category">Category <span class="text-danger"> *</span> <span class="text-danger error font-weight-bold" id="category_error">@error('category') {{ $message }} @enderror</span></label>
                <select class="form-control aiz-selectpicker" name="category" id="category" data-live-search="true">
                    <option value="">{{ ('Select Category')}}</option>
                    @foreach (\App\Models\NoticeCategory::active()->pluck('name', 'id') as $id => $category)
                    <option value="{{ $id }}" @if($id == old('category', @$notice->notice_category_id)) selected @endif>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for="content">Content <span class="text-danger"> *</span> <span class="text-danger error font-weight-bold" id="contect_error">@error('contect') {{ $message }} @enderror</span></label>
                <textarea class="form-control aiz-text-editor" id="content" name="content" required>{!! old('content', @$notice->content) !!}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="visibility">Visibility <span class="text-danger"> *</span> <span class="text-danger error font-weight-bold" id="visibility_error">@error('visibility') {{ $message }} @enderror</span></label>
                <select class="form-control" id="visibility" name="visibility" required>
                    <option value="both" @if(old('visibility', @$notice->visibility) === 'both') selected @endif>Global</option>
                    <option value="customers" @if(old('visibility', @$notice->visibility) === 'customers') selected @endif>Customers</option>
                    <option value="staffs" @if(old('visibility', @$notice->visibility) === 'staffs') selected @endif>Staffs</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="status">Status <span class="text-danger"> *</span> <span class="text-danger error font-weight-bold" id="status_error">@error('status') {{ $message }} @enderror</span></label>
                <select class="form-control" id="status" name="status" required>
                    <option value="draft" @if(old('status', @$notice->status) === 'draft') selected @endif>Draft</option>
                    <option value="published" @if(old('status', @$notice->status) === 'published') selected @endif>Published</option>
                    <option value="scheduled" @if(old('status', @$notice->status) === 'scheduled') selected @endif>Scheduled</option>
                </select>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group" id="publish_at_group" @if(is_null(old('publish_at', @$notice->publish_at))) style="display: none;" @endif>
                <label for="publish_at">Publish Date & Time <span class="text-danger"> *</span> <span class="text-danger error font-weight-bold" id="publish_at_error">@error('publish_at') {{ $message }} @enderror</span></label>
                <input type="datetime-local" class="form-control" id="publish_at" name="publish_at" value="{{ old('publish_at', @$notice->publish_at) }}">
            </div>
        </div>
    </div>
</form>
<div class="text-right">
    <button type="button" class="btn btn-secondary" id="clear-btn">Clear</button>
    <button type="submit" form="notice-form" class="btn btn-primary" id="create-btn">@if(@$notice) Update @else Create @endif</button>
</div>