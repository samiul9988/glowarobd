<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Name <span class="text-danger"> *</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Enter category name"
                value="{{ old('name', @$category->name) }}" required>
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
            <input type="text" name="slug" id="slug" class="form-control" placeholder="Category slug automatically generated"
                value="{{ old('slug', @$category->slug) }}">
            <span class="text-danger error" id="slug_error">
                @error('slug')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="parent">Parent</label>
            <select class="form-control aiz-selectpicker" data-live-search="true" name="parent" id="parent">
                <option value="">Select Parent Category</option>
                @foreach (\App\Models\TicketCategory::active()->with('parent:id,name')->get() as $item)
                    @if(@$category->id == $item->id)
                        @continue
                    @endif
                    <option value="{{ $item->id }}" @if (@$category->parent_id == $item->id) selected @endif>
                        {{ $item->name . ($item->parent_id ? ' ('.$item->parent->name.')' : '') }}
                    </option>
                @endforeach
            </select>
            <span class="text-danger error" id="parent_error">
                @error('parent')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" name="status" id="status">
                <option value="1" selected>Active</option>
                <option value="0" @if(@$category && old('status', @$category->status) == 0) selected @endif>Inactive</option>
            </select>
            <span class="text-danger error" id="status_error">
                @error('status')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" name="description" id="description" rows="5" placeholder="Enter description here">{{ old('description', @$category->description) }}</textarea>
            <span class="text-danger error" id="description_error">
                @error('description')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
</div>
