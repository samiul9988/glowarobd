@php
    $linkableType = @$type ? class_basename(@$type) : 'Custom';
    $linkableId = @$linkable;
    $customLink = @$custom;
@endphp
<div class="col-md-6">
    <div class="form-group">
        <label for="link_type">Link Type <span class="text-danger">*</span></label>
        <select class="form-control aiz-selectpicker" data-live-search="true" name="linkable_type" id="link_type" required>
            <option value="">Select A Link Type</option>
            <option value="product" {{ strtolower($linkableType) === 'product' ? 'selected' : '' }}>Product</option>
            <option value="category" {{ strtolower($linkableType) === 'category' ? 'selected' : '' }}>Category</option>
            <option value="brand" {{ strtolower($linkableType) === 'brand' ? 'selected' : '' }}>Brand</option>
            <option value="custom" {{ strtolower($linkableType) === 'custom' ? 'selected' : '' }}>Custom</option>
        </select>
        <span class="text-danger error" id="link_type_error">
            @error('linkable_type')
                {{ $message }}
            @enderror
        </span>
    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <label for="link_item">Link With <span class="text-danger">*</span></label>
        <div class="link_item_section"
            style="{{ @$type == 'custom' ? 'display: none;' : 'display: block;' }}">
            <select class="form-control aiz-selectpicker" data-live-search="true" name="linkable_id" id="link_item">
                <option value="">Loading ...</option>
            </select>
            <span class="text-danger error" id="link_item_error">
                @error('linkable_id')
                    {{ $message }}
                @enderror
            </span>
        </div>
        <div class="custome_link_section"
            style="{{ @$type == 'custom' ? 'display: block;' : 'display: none;' }}">
            <input type="text" class="form-control" placeholder="https://example.com" name="custom_link"
                id="custom_link" value="{{ old('custom_link', @$customLink) }}">
            <span class="text-danger error" id="custom_link_error">
                @error('custom_link')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
</div>
