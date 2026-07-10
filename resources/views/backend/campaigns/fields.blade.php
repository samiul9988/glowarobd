@php
    $route = @$campaign ? route('campaigns.update', $campaign->id) : route('campaigns.store');
@endphp
<form id="campaign-form" action="{{ $route }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(@$campaign)
        @method('PUT')
    @endif
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="title">Title <span class="text-danger"> *</span> <span class="text-danger error font-weight-bold" id="title_error">@error('title') {{ $message }} @enderror</span></label>
                <input type="text" name="title" id="title" class="form-control" placeholder="Enter title" value="{{ old('title', @$campaign->title) }}" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="slug">Slug <span class="text-danger error font-weight-bold" id="slug_error">@error('slug') {{ $message }} @enderror</span></label>
                <input type="text" name="slug" id="slug" class="form-control" placeholder="Enter slug" value="{{ old('slug', @$campaign->slug) }}" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="category">Category <span class="text-danger"> *</span> <span class="text-danger error font-weight-bold" id="category_error">@error('category') {{ $message }} @enderror</span></label>
                <select class="form-control aiz-selectpicker" name="category" id="category" data-live-search="true">
                    <option value="">{{ ('Select Category')}}</option>
                    @foreach (\App\Models\CampaignCategory::active()->pluck('name', 'id') as $id => $category)
                    <option value="{{ $id }}" @if($id == old('category', @$campaign->campaign_category_id)) selected @endif>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for="description">Description <span class="text-danger"> *</span> <span class="text-danger error font-weight-bold" id="description_error">@error('description') {{ $message }} @enderror</span></label>
                <textarea class="form-control aiz-text-editor" id="description" name="description" required>{!! old('description', @$campaign->description) !!}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="start_date">Start Date & Time <span class="text-danger error font-weight-bold" id="start_date_error">@error('start_date') {{ $message }} @enderror</span></label>
                <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', @$campaign->start_date) }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="end_date">End Date & Time <span class="text-danger error font-weight-bold" id="end_date_error">@error('end_date') {{ $message }} @enderror</span></label>
                <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', @$campaign->end_date) }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="">Thumbnail <span class="text-danger"> *</span> <span class="text-danger error font-weight-bold" id="thumbnail_error">@error('thumbnail') {{ $message }} @enderror</span></label>
                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                    </div>
                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                    <input type="hidden" name="thumbnail" id="thumbnail" value="{{ old('thumbnail', @$campaign->thumbnail) }}" class="selected-files">
                </div>
                <div class="file-preview box sm"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="status">Status <span class="text-danger"> *</span> <span class="text-danger error font-weight-bold" id="status_error">@error('status') {{ $message }} @enderror</span></label>
                <select class="form-control" id="status" name="status" required>
                    <option value="draft" @if(old('status', @$campaign->status) === 'draft') selected @endif>Draft</option>
                    <option value="active" @if(old('status', @$campaign->status) === 'active') selected @endif>Active</option>
                    <option value="completed" @if(old('status', @$campaign->status) === 'completed') selected @endif>Completed</option>
                </select>
            </div>
        </div>
    </div>
</form>
<div class="text-right">
    <button type="button" class="btn btn-secondary" id="clear-btn">Clear</button>
    <button type="submit" form="campaign-form" class="btn btn-primary" id="create-btn">@if(@$campaign) Update @else Create @endif</button>
</div>