<script>
    (function() {
        'use strict';

        const form = $('#job-post-form');
        const titleInput = $('#title');
        const slugInput = $('#slug');
        const submitButton = $('#submit-job-post');
        const isEditMode = form.find('input[name="_method"]').val() === 'PUT';
        const applicationFieldsContainer = $('#application-input-fields');
        const addApplicationFieldButton = $('#add-application-field');
        const applicationFieldsEmptyState = $('#application-fields-empty-state');

        const applicationFieldTypes = [
            { value: 'text', label: 'Text' },
            { value: 'number', label: 'Number' },
            { value: 'select', label: 'Select' },
            { value: 'date', label: 'Date' },
            { value: 'time', label: 'Time' },
            { value: 'datetime', label: 'Date & Time' },
            { value: 'textarea', label: 'Textarea' },
            { value: 'file', label: 'File Upload' }
        ];

        const fileTypeOptions = [
            { value: 'any', label: 'Any File' },
            { value: 'image', label: 'Image Only' },
            { value: 'document', label: 'Document Only' }
        ];

        function hasEditorContent(value) {
            const plainText = $('<div>').html(value || '').text().replace(/\u00a0/g, ' ').trim();
            return plainText.length > 0;
        }

        function slugify(value) {
            return value
                .toString()
                .trim()
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        function clearError(fieldId) {
            $('#' + fieldId + '_error').text('');
            $('#' + fieldId).removeClass('is-invalid');
        }

        function setError(fieldId, message) {
            $('#' + fieldId + '_error').text(message);
            $('#' + fieldId).addClass('is-invalid');
        }

        function validateForm() {
            let isValid = true;

            const title = titleInput.val().trim();
            const slug = slugInput.val().trim();
            const role = $('#role').val().trim();
            const description = $('#description').val();
            const employmentType = $('#employment_type').val();
            const status = $('#status').val();
            const vacancy = parseInt($('#vacancy').val(), 10);
            const salaryMinValue = $('#salary_min').val();
            const salaryMaxValue = $('#salary_max').val();
            const deadlineValue = $('#deadline').val();
            const publishedAtValue = $('#published_at').val();

            const salaryMin = salaryMinValue ? parseFloat(salaryMinValue) : null;
            const salaryMax = salaryMaxValue ? parseFloat(salaryMaxValue) : null;

            if (!title) {
                setError('title', 'Job title is required.');
                isValid = false;
            }

            if (!role) {
                setError('role', 'Job role is required.');
                isValid = false;
            }

            if (!slug) {
                setError('slug', 'Slug is required.');
                isValid = false;
            } else if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) {
                setError('slug', 'Slug must contain lowercase letters, numbers, and hyphens only.');
                isValid = false;
            }

            if (!hasEditorContent(description)) {
                setError('description', 'Description is required.');
                isValid = false;
            }

            if (!employmentType) {
                setError('employment_type', 'Please select an employment type.');
                isValid = false;
            }

            if (!status) {
                setError('status', 'Please select a status.');
                isValid = false;
            } else if (status === 'scheduled') {
                if (!publishedAtValue) {
                    setError('published_at', 'Publish date is required when status is scheduled.');
                    isValid = false;
                } else if (new Date(publishedAtValue) < new Date()) {
                    setError('published_at', 'Publish date cannot be in the past for scheduled status.');
                    isValid = false;
                }
            }

            if (Number.isNaN(vacancy) || vacancy < 1) {
                setError('vacancy', 'Vacancy must be at least 1.');
                isValid = false;
            }

            if (salaryMin !== null && salaryMin < 0) {
                setError('salary_min', 'Minimum salary cannot be negative.');
                isValid = false;
            }

            if (salaryMax !== null && salaryMax < 0) {
                setError('salary_max', 'Maximum salary cannot be negative.');
                isValid = false;
            }

            if (salaryMin !== null && salaryMax !== null && salaryMax < salaryMin) {
                setError('salary_max', 'Maximum salary must be greater than or equal to minimum salary.');
                isValid = false;
            }

            if (deadlineValue && publishedAtValue) {
                const deadlineDate = new Date(deadlineValue + 'T23:59:59');
                const publishedDate = new Date(publishedAtValue);

                if (deadlineDate < publishedDate) {
                    setError('deadline', 'Deadline must be after the publish date.');
                    isValid = false;
                }
            }

            $('[data-field-row]').each(function() {
                const labelInput = $(this).find('input[name$="[label]"]');
                if (!labelInput.length) {
                    return;
                }

                const labelValue = labelInput.val().trim();
                labelInput.removeClass('is-invalid');

                if (!labelValue) {
                    labelInput.addClass('is-invalid');
                    isValid = false;
                }
            });

            return isValid;
        }

        function updateApplicationFieldVisibility($row) {
            const fieldType = $row.find('[data-field-type]').val();
            const optionsRow = $row.find('[data-options-row]');
            const fileTypeRow = $row.find('[data-file-type-row]');

            if (fieldType === 'select') {
                optionsRow.show();
            } else {
                optionsRow.hide();
                optionsRow.find('textarea').val('');
            }

            if (fieldType === 'file') {
                fileTypeRow.show();
            } else {
                fileTypeRow.hide();
                fileTypeRow.find('select').val('any');
            }
        }

        function updateApplicationFieldNumbers() {
            applicationFieldsContainer.find('[data-field-row]').each(function(index) {
                $(this).find('.application-field-title').text('Field #' + (index + 1));
            });

            if (applicationFieldsContainer.find('[data-field-row]').length === 0) {
                applicationFieldsEmptyState.removeClass('d-none');
            } else {
                applicationFieldsEmptyState.addClass('d-none');
            }
        }

        function buildOptionsHtml() {
            return applicationFieldTypes
                .map(function(typeOption) {
                    return '<option value="' + typeOption.value + '">' + typeOption.label + '</option>';
                })
                .join('');
        }

        function buildFileTypeOptionsHtml() {
            return fileTypeOptions
                .map(function(option) {
                    return '<option value="' + option.value + '">' + option.label + '</option>';
                })
                .join('');
        }

        function createApplicationFieldRow(index) {
            const fieldId = 'application_required_' + index;

            return $(
                '<div class="application-input-field border rounded bg-white shadow-sm p-3 p-lg-4 mb-3" data-field-row>' +
                    '<input type="hidden" name="application_input_fields[' + index + '][id]" value="">' +
                    '<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 pb-2 border-bottom">' +
                        '<strong class="text-dark application-field-title">Field</strong>' +
                        '<button type="button" class="btn btn-soft-danger btn-sm" data-remove-field>' +
                            '<i class="las la-trash mr-1"></i>Remove' +
                        '</button>' +
                    '</div>' +
                    '<div class="form-row">' +
                        '<div class="form-group col-lg-6">' +
                            '<label class="small text-muted mb-1">Label <span class="required-mark">*</span></label>' +
                            '<input type="text" class="form-control" name="application_input_fields[' + index + '][label]" placeholder="e.g. Full Name">' +
                        '</div>' +
                        '<div class="form-group col-lg-6">' +
                            '<label class="small text-muted mb-1">Type</label>' +
                            '<select class="form-control" name="application_input_fields[' + index + '][type]" data-field-type>' +
                                buildOptionsHtml() +
                            '</select>' +
                        '</div>' +
                    '</div>' +
                    '<div class="form-group" data-options-row style="display:none;">' +
                        '<label class="small text-muted mb-1">Options</label>' +
                        '<div>' +
                            '<textarea class="form-control" rows="3" name="application_input_fields[' + index + '][options]" placeholder="One option per line"></textarea>' +
                            '<small class="form-text text-muted">Only used for Select type. Add one option per line.</small>' +
                        '</div>' +
                    '</div>' +
                    '<div class="form-group" data-file-type-row style="display:none;">' +
                        '<label class="small text-muted mb-1">File Type</label>' +
                        '<div>' +
                            '<select class="form-control" name="application_input_fields[' + index + '][file_type]">' +
                                buildFileTypeOptionsHtml() +
                            '</select>' +
                        '</div>' +
                    '</div>' +
                    '<div class="form-row">' +
                        '<div class="form-group col-md-6">' +
                            '<label class="small text-muted mb-1">Placeholder</label>' +
                            '<input type="text" class="form-control" name="application_input_fields[' + index + '][placeholder]" placeholder="Optional">' +
                        '</div>' +
                        '<div class="form-group col-md-6">' +
                            '<label class="small text-muted mb-1">Position</label>' +
                            '<input type="number" min="1" class="form-control" name="application_input_fields[' + index + '][position]" value="' + (index + 1) + '">' +
                        '</div>' +
                        '<div class="form-group col-md-6">' +
                            '<label class="small text-muted mb-1">Expected Value</label>' +
                            '<input type="text" min="1" class="form-control" name="application_input_fields[' + index + '][expected_value]" placeholder="Used for calculate matching score (Optional)">' +
                            '<small class="form-text text-muted">Use | to separate multiple values</small>' +
                        '</div>' +
                        '<div class="form-group col-md-6">' +
                            '<label class="small text-muted mb-1" for="' + fieldId + '">Validation</label>' +
                            '<select class="form-control" id="' + fieldId + '" name="application_input_fields[' + index + '][is_required]">' +
                                '<option value="1">Required</option>' +
                                '<option value="0" selected>Nullable</option>' +
                            '</select>' +
                        '</div>' +
                    '</div>' +
                    '<div class="form-group mb-0">' +
                        '<label class="small text-muted mb-1">Help Text</label>' +
                        '<div>' +
                            '<input type="text" class="form-control" name="application_input_fields[' + index + '][help_text]" placeholder="Optional guidance for applicants">' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
        }

        titleInput.on('input', function() {
            const currentSlug = slugInput.val().trim();
            if (!currentSlug || currentSlug === slugify($(this).data('last-title') || '')) {
                slugInput.val(slugify($(this).val()));
                clearError('slug');
            }
            $(this).data('last-title', $(this).val());
            clearError('title');
        });

        slugInput.on('input', function() {
            $(this).val(slugify($(this).val()));
            clearError('slug');
        });

        applicationFieldsContainer.on('change', '[data-field-type]', function() {
            updateApplicationFieldVisibility($(this).closest('[data-field-row]'));
        });

        applicationFieldsContainer.on('click', '[data-remove-field]', function() {
            $(this).closest('[data-field-row]').remove();
            updateApplicationFieldNumbers();
        });

        addApplicationFieldButton.on('click', function() {
            const nextIndex = parseInt(applicationFieldsContainer.attr('data-next-index') || '0', 10);
            const $newRow = createApplicationFieldRow(nextIndex);
            applicationFieldsContainer.append($newRow);
            applicationFieldsContainer.attr('data-next-index', nextIndex + 1);
            updateApplicationFieldVisibility($newRow);
            updateApplicationFieldNumbers();
        });

        applicationFieldsContainer.find('[data-field-row]').each(function() {
            updateApplicationFieldVisibility($(this));
        });

        updateApplicationFieldNumbers();

        $('#description, #benefits, #employment_type, #vacancy, #salary_min, #salary_max, #deadline, #published_at, #status').on('input change', function() {
            clearError($(this).attr('id'));
        });

        form.on('submit', function(e) {
            e.preventDefault();

            $('.invalid-feedback').not(':empty').text('');
            $('.is-invalid').removeClass('is-invalid');

            if (!validateForm()) {
                const firstInvalid = $('.is-invalid').first();
                if (firstInvalid.length) {
                    $('html, body').animate({
                        scrollTop: firstInvalid.offset().top - 120
                    }, 350);
                    firstInvalid.focus();
                }
                return;
            }

            submitButton.prop('disabled', true).text('Saving...');
            this.submit();
        });
    })();
</script>
