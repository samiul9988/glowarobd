<?php

namespace App\Http\Requests;

use App\Enums\ShiftEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // The staff id comes from the route segment
        $staffId = $this->route('staff');
        $userId = \App\Models\Staff::findOrFail($staffId)->user_id;

        return [
            // Account
            'name' => 'required|string|max:255',
            'email' => 'required_if:mobile,null|nullable|email|max:255|unique:users,email,'.$userId,
            'mobile' => ['required_if:email,null', 'nullable', 'string', 'regex:/^(\+88)?01[3-9]\d{8}$/', 'unique:users,phone,'.$userId],
            'password' => 'nullable|string|min:6',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date|before:today',
            'role_id' => 'nullable|integer|exists:roles,id',
            'personal_email' => 'nullable|email|max:255|unique:staff,personal_email,'.$staffId,

            // Profile picture (upload ID)
            'profile_picture' => 'nullable|integer',

            // Employment
            'joining_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'shift' => [
                'nullable',
                'string',
                new Enum(ShiftEnum::class)
            ],
            'working_hours' => 'nullable|numeric|min:0|max:24',
            'weekly_offday' => 'nullable|array',
            'weekly_offday.*' => 'string|in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday',

            // Personal
            'address' => 'nullable|string|max:500',
            'educational_background' => 'nullable|string|max:1000',

            // Emergency contact
            'ec_father_name' => 'nullable|string|max:255',
            'ec_mother_name' => 'nullable|string|max:255',
            'ec_spouse_name' => 'nullable|string|max:255',
            'ec_contact_number' => 'nullable|string|regex:/^(\+88)?01[3-9]\d{8}$/',

            // HR details
            'employment_status' => 'nullable|string|in:active,probation,on_leave,resigned,terminated',
            'blood_group' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'resign_date' => 'nullable|date',
            'resignation_letter' => 'nullable|integer',
            'termination_date' => 'nullable|date',
            'termination_reason' => 'nullable|string|max:1000',
            'bank_name' => 'nullable|string|max:255',
            'account_no' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',

            // Events
            'event_date' => 'nullable|array',
            'event_date.*' => 'nullable|date',
            'event_title' => 'nullable|array',
            'event_title.*' => 'nullable|string|max:255',
            'event_attachment' => 'nullable|array',
            'event_attachment.*' => 'nullable|integer',
            'event_type' => 'nullable|array',
            'event_type.*' => 'nullable|string',

            // Attachments (comma-separated upload IDs from AIZ uploader)
            'attachment_cv' => 'nullable|string',
            'attachment_nid' => 'nullable|string',
            'attachment_certificate' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.required_if' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'mobile.required_if' => 'Phone number is required.',
            'mobile.unique' => 'This phone number is already in use.',
            'mobile.regex' => 'Invalid phone number. Must be a valid phone number (e.g. 01712345678 or +8801712345678).',
            'password.min' => 'Password must be at least 6 characters.',
            'role_id.exists' => 'The selected role is invalid.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'salary.numeric' => 'Salary must be a numeric value.',
            'salary.min' => 'Salary cannot be negative.',
            'working_hours.max' => 'Working hours cannot exceed 24 per day.',
            'shift.in' => 'Shift must be Morning, Evening, or Night.',
            'employment_status.in' => 'Invalid employment status selected.',
            'blood_group.in' => 'Invalid blood group selected.',
            'gender.in' => 'Gender must be Male, Female, or Other.',
            'event_date.*.date' => 'Each event date must be a valid date.',
            'event_title.*.max' => 'Event title may not exceed 255 characters.',
        ];
    }
}
