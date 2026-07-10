@php
    $isEdit = isset($jobPost) && $jobPost !== null;

    $formAction = $isEdit ? route('job_posts.update', $jobPost) : route('job_posts.store');
@endphp
<form id="job-post-form" class="form-horizontal" action="{{ $formAction }}" method="POST">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Account Info ===== --}}
    <h6 class="mb-3 font-weight-bold text-uppercase text-muted">Basic Info</h6>

    <div class="form-group row">
        <label class="col-sm-3 col-from-label" for="title">Job Title *</label>
        <div class="col-sm-9">
            <input type="text" placeholder="Job Title" id="title" name="title" class="form-control"
                value="{{ old('title', optional($jobPost)->title) }}">
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-3 col-from-label" for="slug">Slug *</label>
        <div class="col-sm-9">
            <input type="text" placeholder="auto-generated-from-title" id="slug" name="slug" class="form-control"
                value="{{ old('slug', optional($jobPost)->slug) }}">
            <small class="form-text text-muted">Unique identifier for the job post, used in URLs. Only lowercase letters, numbers, and hyphens are allowed.</small>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-3 col-from-label" for="role">Role *</label>
        <div class="col-sm-9">
            <select name="role" id="role" class="form-control aiz-selectpicker" data-live-search="true">
                <option value="">Select Role</option>
                @foreach (\App\Models\Role::pluck('name')->toArray() as $role)
                    <option value="{{ trim($role) }}" @selected(old('role', optional($jobPost)->role) === trim($role))>
                        {{ $role }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-3 col-from-label" for="description">Description *</label>
        <div class="col-sm-9">
            <textarea id="description" name="description" class="form-control aiz-text-editor" rows="5"
                placeholder="Describe responsibilities, role summary, and expectations">{!! old('description', optional($jobPost)->description) !!}</textarea>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-3 col-from-label" for="location">Location</label>
        <div class="col-sm-9">
            <input id="location" name="location" class="form-control" rows="3"
                placeholder="Enter the job location e.g. Dhaka, Bangladesh" value="{{ old('location', optional($jobPost)->location) }}">
        </div>
    </div>

    {{-- ===== Employment Details ===== --}}
    <hr>
    <h6 class="mb-3 font-weight-bold text-uppercase text-muted">Employment Details</h6>

    <div class="form-group row">
        <label for="employment_type" class="col-md-3 col-form-label">Employment Type <span
                class="required-mark">*</span></label>
        <div class="col-md-4">
            <select id="employment_type" name="employment_type" class="form-control">
                <option value="">Select Type</option>
                <option value="full_time" @selected(old('employment_type', optional($jobPost)->employment_type ?: 'full_time') === 'full_time')>Full Time</option>
                <option value="part_time" @selected(old('employment_type', optional($jobPost)->employment_type) === 'part_time')>Part Time</option>
                <option value="internship" @selected(old('employment_type', optional($jobPost)->employment_type) === 'internship')>Internship</option>
            </select>
            <span class="invalid-feedback d-block" id="employment_type_error">
                @error('employment_type')
                    {{ $message }}
                @enderror
            </span>
        </div>

        <label for="vacancy" class="col-md-1 col-form-label">Vacancy <span class="required-mark">*</span></label>
        <div class="col-md-4">
            <input type="number" id="vacancy" name="vacancy" class="form-control" min="1" step="1"
                value="{{ old('vacancy', optional($jobPost)->vacancy ?? 1) }}" placeholder="Enter number of vacancies e.g. 5">
            <span class="invalid-feedback d-block" id="vacancy_error">
                @error('vacancy')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>

    <div class="form-group row mb-4">
        <label for="experience" class="col-md-3 col-form-label">Experience</label>
        <div class="col-md-9">
            <input type="text" id="experience" name="experience" class="form-control" placeholder="e.g. 2-4 years"
                value="{{ old('experience', optional($jobPost)->experience) }}">
            <span class="invalid-feedback d-block" id="experience_error">
                @error('experience')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>

    <hr>
    <h6 class="mb-3 font-weight-bold text-uppercase text-muted">Compensation & Publishing</h6>

    <div class="form-group row">
        <label for="benefits" class="col-md-3 col-form-label">Benefits</label>
        <div class="col-md-9">
            <textarea id="benefits" name="benefits" class="form-control" rows="4"
                placeholder="List the benefits of the position">{{ old('benefits', (optional($jobPost)->benefits)) }}</textarea>
            <small>One benefit per line</small>
            <span class="invalid-feedback d-block" id="benefits_error">
                @error('benefits')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>
    <div class="form-group row">
        <label for="salary_min" class="col-md-3 col-form-label">Salary Range</label>
        <div class="col-md-4">
            <input type="number" id="salary_min" name="salary_min" class="form-control" min="0"
                step="0.01" placeholder="Minimum"
                value="{{ old('salary_min', optional($jobPost)->salary_min) }}">
            <span class="invalid-feedback d-block" id="salary_min_error">
                @error('salary_min')
                    {{ $message }}
                @enderror
            </span>
        </div>
        <div class="col-md-5">
            <input type="number" id="salary_max" name="salary_max" class="form-control" min="0"
                step="0.01" placeholder="Maximum"
                value="{{ old('salary_max', optional($jobPost)->salary_max) }}">
            <span class="invalid-feedback d-block" id="salary_max_error">
                @error('salary_max')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>

    <div class="form-group row">
        <label for="deadline" class="col-md-3 col-form-label">Application Deadline</label>
        <div class="col-md-4">
            <input type="date" id="deadline" name="deadline" class="form-control"
                value="{{ old('deadline', optional($jobPost)->deadline ? optional($jobPost)->deadline->format('Y-m-d') : null) }}">
            <span class="invalid-feedback d-block" id="deadline_error">
                @error('deadline')
                    {{ $message }}
                @enderror
            </span>
        </div>

        <label for="published_at" class="col-md-1 col-form-label">Publish At</label>
        <div class="col-md-4">
            <input type="datetime-local" id="published_at" name="published_at" class="form-control"
                value="{{ old('published_at', optional($jobPost)->published_at ? optional($jobPost)->published_at->format('Y-m-d\\TH:i') : null) }}">
            <span class="invalid-feedback d-block" id="published_at_error">
                @error('published_at')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>

    <div class="form-group row mb-4">
        <label for="status" class="col-md-3 col-form-label">Status <span class="required-mark">*</span></label>
        <div class="col-md-4">
            <select id="status" name="status" class="form-control">
                <option value="draft" @selected(old('status', optional($jobPost)->status ?: 'draft') === 'draft')>Draft</option>
                <option value="published" @selected(old('status', optional($jobPost)->status) === 'published')>Published</option>
                <option value="scheduled" @selected(old('status', optional($jobPost)->status) === 'scheduled')>Scheduled</option>
                <option value="archived" @selected(old('status', optional($jobPost)->status) === 'archived')>Archived</option>
            </select>
            <span class="invalid-feedback d-block" id="status_error">
                @error('status')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>

    <hr>
    <h6 class="mb-3 font-weight-bold text-uppercase text-muted">Application Form Fields</h6>
    @php
        $applicationForm = optional($jobPost)->application_form ?? [];
        $applicationInputFields = old('application_input_fields', data_get($applicationForm, 'fields', []));
        if (!is_array($applicationInputFields)) {
            $applicationInputFields = [];
        }
        $sortedApplicationInputFields = collect($applicationInputFields)
            ->sortBy('position')
            ->values()
            ->all();
        // dd($applicationInputFields, $sortedApplicationInputFields);
    @endphp

    <div class="form-group row">
        <label for="application_form_title" class="col-md-3 col-form-label">Form Title</label>
        <div class="col-md-9">
            <input type="text" id="application_form_title" name="application_form_title" class="form-control"
                placeholder="e.g. Apply for this role"
                value="{{ old('application_form_title', data_get($applicationForm, 'title')) }}">
            <span class="invalid-feedback d-block" id="application_form_title_error">
                @error('application_form_title')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>

    <div class="form-group row">
        <label for="application_button_text" class="col-md-3 col-form-label">Submit Button Text</label>
        <div class="col-md-9">
            <input type="text" id="application_button_text" name="application_button_text" class="form-control"
                placeholder="e.g. Submit Application"
                value="{{ old('application_button_text', data_get($applicationForm, 'button_text', 'Submit Application')) }}">
            <span class="invalid-feedback d-block" id="application_button_text_error">
                @error('application_button_text')
                    {{ $message }}
                @enderror
            </span>
        </div>
    </div>

    <div class="border rounded bg-light p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <div>
                <h6 class="mb-1 font-weight-bold text-dark">Input Fields</h6>
                <p class="mb-0 text-muted">Add and arrange custom fields for the application form.</p>
            </div>
        </div>

        <div class="alert alert-info mb-4">
            <i class="las la-info-circle"></i> By default, the application form will include fields to capture the applicant's name, email and phone number. You can add more fields as needed.
        </div>

        <div id="application-input-fields" data-next-index="{{ count($sortedApplicationInputFields) }}">
            @foreach ($sortedApplicationInputFields as $index => $field)
                @php
                    $fieldType = data_get($field, 'type', 'text');
                    $fieldFileType = data_get($field, 'file_type', 'any');
                @endphp
                <div class="application-input-field border rounded bg-white shadow-sm p-3 p-lg-4 mb-3" data-field-row>
                    <input type="hidden" name="application_input_fields[{{ $index }}][id]"
                        value="{{ data_get($field, 'id') }}">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <strong class="text-dark application-field-title">Field #{{ $loop->iteration }}</strong>
                        <button type="button" class="btn btn-soft-danger btn-sm" data-remove-field>
                            <i class="las la-trash mr-1"></i>Remove
                        </button>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-lg-6">
                            <label class="small text-muted mb-1">Label <span class="required-mark">*</span></label>
                            <input type="text" class="form-control" name="application_input_fields[{{ $index }}][label]"
                                value="{{ data_get($field, 'label') }}" placeholder="e.g. Full Name">
                        </div>

                        <div class="form-group col-lg-6">
                            <label class="small text-muted mb-1">Type</label>
                            <select class="form-control" name="application_input_fields[{{ $index }}][type]" data-field-type>
                                <option value="text" @selected($fieldType === 'text')>Text</option>
                                <option value="number" @selected($fieldType === 'number')>Number</option>
                                <option value="select" @selected($fieldType === 'select')>Select</option>
                                <option value="date" @selected($fieldType === 'date')>Date</option>
                                <option value="time" @selected($fieldType === 'time')>Time</option>
                                <option value="datetime" @selected($fieldType === 'datetime')>Date & Time</option>
                                <option value="textarea" @selected($fieldType === 'textarea')>Textarea</option>
                                <option value="file" @selected($fieldType === 'file')>File Upload</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" data-options-row @if ($fieldType !== 'select') style="display:none;" @endif>
                        <label class="small text-muted mb-1">Options</label>
                        <div>
                            <textarea class="form-control" rows="3"
                                name="application_input_fields[{{ $index }}][options]"
                                placeholder="One option per line">{{ data_get($field, 'options') }}</textarea>
                            <small class="form-text text-muted">Only used for Select type. Add one option per line.</small>
                        </div>
                    </div>

                    <div class="form-group" data-file-type-row @if ($fieldType !== 'file') style="display:none;" @endif>
                        <label class="small text-muted mb-1">File Type</label>
                        <div>
                            <select class="form-control" name="application_input_fields[{{ $index }}][file_type]">
                                <option value="any" @selected($fieldFileType === 'any')>Any File</option>
                                <option value="image" @selected($fieldFileType === 'image')>Image Only</option>
                                <option value="document" @selected($fieldFileType === 'document')>Document Only</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small text-muted mb-1">Placeholder</label>
                            <input type="text" class="form-control" name="application_input_fields[{{ $index }}][placeholder]"
                                value="{{ data_get($field, 'placeholder') }}" placeholder="Optional">
                        </div>

                        <div class="form-group col-md-6">
                            <label class="small text-muted mb-1">Position</label>
                            <input type="number" min="1" class="form-control"
                                name="application_input_fields[{{ $index }}][position]"
                                value="{{ data_get($field, 'position', $loop->iteration) }}">
                        </div>

                        <div class="form-group col-md-6">
                            <label class="small text-muted mb-1">Expected Value</label>
                            <input type="text" min="1" class="form-control"
                                name="application_input_fields[{{ $index }}][expected_value]"
                                value="{{ data_get($field, 'expected_value', $loop->iteration) }}" placeholder="Used for calculate matching score (Optional)">
                                <small class="form-text text-muted">Use | to separate multiple values</small>
                        </div>

                        <div class="form-group col-md-6">
                            <label class="small text-muted mb-1" for="application_required_{{ $index }}">Validation</label>
                            <select class="form-control" id="application_required_{{ $index }}"
                                name="application_input_fields[{{ $index }}][is_required]">
                                <option value="1" @selected((bool) data_get($field, 'is_required', false))>Required</option>
                                <option value="0" @selected(!(bool) data_get($field, 'is_required', false))>Nullable</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small text-muted mb-1">Help Text</label>
                        <div>
                            <input type="text" class="form-control" name="application_input_fields[{{ $index }}][help_text]"
                                value="{{ data_get($field, 'help_text') }}" placeholder="Optional guidance for applicants">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="application-fields-empty-state" class="text-center py-4 mb-2 border rounded bg-white text-muted @if (count($sortedApplicationInputFields) > 0) d-none @endif">
            No custom fields added yet. Click "Add Field" to create one.
        </div>
        <span class="invalid-feedback d-block" id="application_input_fields_error">
            @error('application_input_fields')
                {{ $message }}
            @enderror
        </span>

        <div class="d-flex flex-wrap align-items-center justify-content-end mb-3 pb-2 border-bottom">
            <button type="button" id="add-application-field" class="btn btn-soft-primary btn-sm mt-2 mt-sm-0">
                <i class="las la-plus mr-1"></i>Add Field
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button type="submit" id="submit-job-post" class="btn btn-primary px-4">
            {{ $isEdit ? 'Update Job Post' : 'Save Job Post' }}
        </button>
    </div>
</form>
