<div class="row gutters-5">
    <div class="col-md-6">
        <div class="form-group">
            <label for="title">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" placeholder="Title" name="title" id="title" value="{{ old('title', @$highlightedItem->title) }}" required>
            <span class="text-danger error" id="title_error">
                @error('title') {{ $message }} @enderror
            </span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="subtitle">Sub Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" placeholder="Sub Title" name="subtitle" id="subtitle" value="{{ old('subtitle', @$highlightedItem->subtitle) }}" required>
            <span class="text-danger error" id="subtitle_error">
                @error('subtitle')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="description">Description <span class="text-danger">*</span></label>
            <textarea rows="4" class="form-control" placeholder="Description" name="description" id="description" required>{{ old('description', @$highlightedItem->description) }}</textarea>
            <span class="text-danger error" id="description_error">
                @error('description')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>

    @include('backend.components.linkable', [
        'type' => @$highlightedItem->linkable_type,
        'linkable' => @$highlightedItem->linkable_id,
        'custom' => @$highlightedItem->custom_link,
    ])

    <div class="col-md-4">
        <div class="form-group">
            <label for="banner">Banner Image <span class="text-danger">* </span><span class="text-danger error" id="banner_error">@error('banner'){{ $message }}@enderror</span></label>
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary">Browse</div>
                </div>
                <div class="form-control file-amount">Choose Files</div>
                <input type="hidden" name="banner" id="banner" class="selected-files" value="{{ old('banner', @$highlightedItem->banner_img) }}">
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="status">
                Button Text @include('components.tooltip', ['title' => 'If left empty, Button will not be displayed and link will not be clickable'])
            </label>
            <input type="text" class="form-control" name="button_text" id="button_text" value="{{ old('button_text', @$highlightedItem->button_text) }}" placeholder="Shop Now">
            <span class="text-danger error" id="button_text_error">
                @error('button_text')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control aiz-selectpicker" name="status" id="status">
                <option value="1" selected>Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    </div>

    <div class="col-12">
        <span class="fs-16 fw-600">
            Highlights
            @include('components.tooltip', ['title' => 'You can add maximum 4 highlights'])
        </span>
    </div>

    <div class="col-md-12 mt-2 highlight-section" id="highlight-section">
        <div class="form-group">
            <span class="text-danger error" id="highlights_error">@error('highlights'){{ $message }}@enderror</span>
            <div class="highlights-target">
                @forelse (old('highlights', @$highlightedItem->highlights ?? []) as $key => $highlight)
                    <div class="row gutters-5">
                        <div class="col">
                            <div class="form-group">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">Browse</div>
                                    </div>
                                    <div class="form-control file-amount">Choose Files</div>
                                    <input type="hidden" name="highlight_icons[]" class="selected-files" value="{{ $highlight['icon'] ?? '' }}">
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Label text"
                                    name="highlight_labels[]" value="{{ $highlight['label'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger remove-highlight">
                                <i class="las la-times"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="row gutters-5">
                        <div class="col">
                            <div class="form-group">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">Browse</div>
                                    </div>
                                    <div class="form-control file-amount">Choose Files</div>
                                    <input type="hidden" name="highlight_icons[]" class="selected-files" value="">
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Label text" name="highlight_labels[]" value="">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger remove-highlight">
                                <i class="las la-times"></i>
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>
            <button type="button" class="btn btn-soft-secondary btn-sm" id="add-more-highlights" {{ count(@$highlightedItem->highlights ?? []) >= 4 ? 'disabled' : '' }}>Add New</button>
        </div>
    </div>
</div>

{{-- Show All Error Messages --}}
{{-- @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif --}}
