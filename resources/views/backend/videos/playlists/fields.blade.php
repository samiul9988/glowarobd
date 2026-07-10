<div class="row gutters-5">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Name <span class="text-danger">* </span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Enter a name" value="{{ old('name', @$playlist->name) }}" required>
            <span class="text-danger error" id="name_error">
                @error('name')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" name="slug" id="slug" class="form-control" placeholder="Slug generate automatically" value="{{ old('slug', @$playlist->slug) }}">
            <span class="text-danger error" id="slug_error">
                @error('slug')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="col-md-12 text-review-section review-section" id="comment-section">
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" name="description" id="description" rows="5" placeholder="Enter description here">{{ old('description', @$playlist->description) }}</textarea>
            <span class="text-danger error" id="description_error">
                @error('description')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="thumbnail">Thumbnail <span class="text-danger">* </span><span class="text-danger error" id="thumbnail_error">@error('thumbnail'){{ $message }}@enderror</span></label>
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ ('Choose Files') }}</div>
                <input type="hidden" name="thumbnail" id="thumbnail" class="selected-files" value="{{ old('thumbnail', @$playlist->thumbnail) }}">
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" name="status" id="status">
                <option value="1" selected>Active</option>
                <option value="0" @if (@$playlist && old('status', @$playlist->status) == 0) selected @endif>Inactive</option>
            </select>
            <span class="text-danger error" id="status_error">
                @error('status')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="col-12 mt-3">
        <div class="form-group row">
            <div class="col-md-1">
                <label class="col-from-label">Featured ?</label>
            </div>
            <div class="col-md-11">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" {{ @$playlist->featured ? 'checked' : '' }} id="is_featured" name="featured" value="{{ @$playlist->featured ?? 0 }}" onchange="this.value = this.checked ? 1 : 0">
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
</div>
