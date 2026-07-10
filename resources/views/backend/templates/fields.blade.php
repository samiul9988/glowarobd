<div class="row gutters-5">
    <div class="col-md-12">
        <div class="form-group">
            <label for="name">Name <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', @$template->name) }}" class="form-control" placeholder="Template Name" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="type">Template Type <span class="text-danger">*</span> </label>
            <select name="type" id="type" class="form-control aiz-selectpicker" data-live-search="true" required>
                <option value="">Select Template Type</option>
                @foreach (\App\Enums\TemplateTypes::options() as $key => $value)
                    <option value="{{ $key }}" {{ old('type', @$template->type?->value) == $key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control aiz-selectpicker" required>
                <option value="1" selected>Active</option>
                <option value="0" {{ @$template && old('status', @$template->status) === 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-group">
            <div class="d-flex justify-content-between mb-1">
                <label for="content">Content <span class="text-danger">*</span> </label>
                <button type="button" class="btn btn-sm btn-soft-info fs-11 px-2 py-1" id="view_sample_code" title="View Sample Code" style="display: none;">
                    <i class="las la-info"></i>
                </button>
            </div>
            <textarea class="aiz-text-editor" name="content" id="content" placeholder="Template Content" required>{{ old('content', @$template->content) }}</textarea>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mt-md-4">
            <div class="card-header d-block text-center">
                <h6 class="fs-15 font-weight-bold">PREVIEW</h6>
            </div>
            <div class="card-body">
                <div id="preview"></div>
            </div>
        </div>
    </div>
</div>
