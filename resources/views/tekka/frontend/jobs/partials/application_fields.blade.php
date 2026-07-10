@foreach ($fields as $index => $field)
    @php
        $fieldIdentifier = data_get($field, 'id', 'field_' . ($index + 1));
    @endphp
    <div class="form-group row">
        <label class="col-md-3 col-form-label">
            {{ data_get($field, 'label', 'Field ' . ($index + 1)) }}
            @if (data_get($field, 'is_required', false))
                <span class="text-danger"> *</span>
            @endif
        </label>
        <div class="col-md-9">
            @if (data_get($field, 'type', 'text') === 'file')
                @php
                    $fileType = match(data_get($field, 'file_type', 'any')) {
                        'document' => '.pdf,.doc,.docx',
                        'image' => 'image/*',
                        default => '*/*',
                    };
                @endphp
                <input type="file"
                    name="application_input_fields[{{ $fieldIdentifier }}]"
                    class="form-control form-control-sm {{ data_get($field, 'is_required', false) ? 'required' : '' }}"
                    @if (data_get($field, 'is_required', false)) required @endif
                    accept="{{ $fileType }}"
                >
            @elseif (data_get($field, 'type', 'text') === 'textarea')
                <textarea name="application_input_fields[{{ $fieldIdentifier }}]"
                    class="form-control form-control-sm {{ data_get($field, 'is_required', false) ? 'required' : '' }}"
                    @if (data_get($field, 'is_required', false)) required @endif
                    placeholder="{{ data_get($field, 'placeholder') }}"
                ></textarea>
            @elseif (data_get($field, 'type', 'text') === 'select')
                @php
                    $options = data_get($field, 'options', []);
                    if (is_array($options)) {
                        $lines = array_values(array_filter(array_map('trim', $options)));
                    } else {
                        $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $options))));
                    }
                @endphp
                <select name="application_input_fields[{{ $fieldIdentifier }}]" class="form-control form-control-sm {{ data_get($field, 'is_required', false) ? 'required' : '' }} aiz-selectpickerr"
                    data-live-search="true" @if (data_get($field, 'is_required', false)) required @endif>
                    <option value="">{{ data_get($field, 'placeholder', 'Select an option') }}</option>
                    @foreach ($lines as $value)
                        <option value="{{ $value }}">{{ ucwords($value) }}</option>
                    @endforeach
                </select>
            @else
                <input type="{{ data_get($field, 'type', 'text') }}"
                    name="application_input_fields[{{ $fieldIdentifier }}]"
                    class="form-control form-control-sm {{ data_get($field, 'is_required', false) ? 'required' : '' }}"
                    @if (data_get($field, 'is_required', false)) required @endif
                    placeholder="{{ data_get($field, 'placeholder') }}"
                >
            @endif
            @if (data_get($field, 'help_text', null))
                <small class="form-text text-muted">{{ data_get($field, 'help_text') }}</small>
            @endif
        </div>
    </div>
@endforeach
