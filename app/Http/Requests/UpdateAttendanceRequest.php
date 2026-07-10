<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ShiftEnum;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift' => ['nullable', 'string', new Enum(ShiftEnum::class)],

            'status' => 'required|string',

            // Check-in/out only when present
            'check_in' => 'required_if:status,present|nullable|date_format:Y-m-d\TH:i',

            'check_out' => 'required_if:status,present|nullable|date_format:Y-m-d\TH:i|after_or_equal:check_in',

            'check_out_type' => 'nullable|string|in:regular,alternative',

            'alternative_date' => 'required_if:check_out_type,alternative|nullable|date',

            'note' => 'nullable|string|min:3',

            'overtimes' => 'nullable|array',

            'overtimes.*.start_time' => 'nullable|date_format:Y-m-d\TH:i',

            'overtimes.*.end_time' => 'nullable|date_format:Y-m-d\TH:i|after:overtimes.*.start_time',
        ];
    }

    public function messages(): array
    {
        return [
            'check_in.required_if' => 'Please provide a check-in time when status is present.',
            'check_out.required_if' => 'Please provide a check-out time when status is present.',
            'check_out.after_or_equal' => 'The check out time must be after or equal to the check in time.',
            'alternative_date.required_if' => 'Please provide an alternative date when check-out type is alternative.',
            'overtimes.*.end_time.after' => 'The end time must be after the start time for each overtime entry.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->status !== 'present') {
            $this->request->remove('check_in');
            $this->request->remove('check_out');
        }
    }
}
