<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobPostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'internship'])],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'gte:salary_min'],
            'experience' => ['nullable', 'string', 'max:255'],
            'vacancy' => ['required', 'integer', 'min:1'],
            'deadline' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived', 'scheduled'])],
            'published_at' => ['required_if:status,scheduled', 'nullable', 'date'],

            'application_form_title' => ['nullable', 'string', 'max:255'],
            'application_button_text' => ['nullable', 'string', 'max:255'],

            'application_input_fields' => ['nullable', 'array'],
            'application_input_fields.*.id' => ['nullable', 'string', 'max:100'],
            'application_input_fields.*.label' => ['required_with:application_input_fields', 'string', 'max:255'],
            'application_input_fields.*.type' => [
                'required_with:application_input_fields',
                Rule::in(['text', 'number', 'select', 'date', 'time', 'datetime', 'textarea', 'file'])
            ],
            'application_input_fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'application_input_fields.*.expected_value' => ['nullable', 'string'],
            'application_input_fields.*.help_text' => ['nullable', 'string', 'max:255'],
            'application_input_fields.*.options' => ['nullable', 'string'],
            'application_input_fields.*.file_type' => ['nullable', Rule::in(['any', 'image', 'document'])],
            'application_input_fields.*.position' => ['nullable', 'integer', 'min:1'],
            'application_input_fields.*.is_required' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            // Basic fields
            'title.required' => 'Job title is required.',
            'role.required' => 'Job role is required.',
            'title.string' => 'Job title must be a valid text.',
            'role.string' => 'Job role must be a valid text.',
            'title.max' => 'Job title cannot exceed 255 characters.',
            'role.max' => 'Job role cannot exceed 255 characters.',

            'description.required' => 'Job description is required.',

            'employment_type.required' => 'Employment type is required.',
            'employment_type.in' => 'Selected employment type is invalid.',

            'salary_min.numeric' => 'Minimum salary must be a number.',
            'salary_min.min' => 'Minimum salary cannot be negative.',

            'salary_max.numeric' => 'Maximum salary must be a number.',
            'salary_max.min' => 'Maximum salary cannot be negative.',
            'salary_max.gte' => 'Maximum salary must be greater than or equal to minimum salary.',

            'vacancy.required' => 'Number of vacancies is required.',
            'vacancy.integer' => 'Vacancy must be a valid number.',
            'vacancy.min' => 'Vacancy must be at least 1.',

            'status.required' => 'Job status is required.',
            'status.in' => 'Selected status is invalid.',

            'published_at.date' => 'Publish date must be a valid date.',

            // Scheduled rule (custom logic)
            'published_at.required_if' => 'Publish date is required when status is scheduled.',

            // Application form
            'application_form_title.max' => 'Application form title cannot exceed 255 characters.',
            'application_button_text.max' => 'Button text cannot exceed 255 characters.',

            // Nested fields
            'application_input_fields.array' => 'Application fields must be a valid list.',

            'application_input_fields.*.label.required_with' => 'Each application field must have a label.',
            'application_input_fields.*.label.max' => 'Field label cannot exceed 255 characters.',

            'application_input_fields.*.type.required_with' => 'Each field must have a type.',
            'application_input_fields.*.type.in' => 'Invalid field type selected.',

            'application_input_fields.*.position.integer' => 'Field position must be a number.',
            'application_input_fields.*.position.min' => 'Field position must be at least 1.',

            'application_input_fields.*.file_type.in' => 'File type must be image, document, or any.',
        ];
    }
}
