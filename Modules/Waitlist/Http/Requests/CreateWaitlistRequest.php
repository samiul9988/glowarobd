<?php

namespace Modules\Waitlist\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateWaitlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'   => 'required|integer',
            'contact_type' => 'required|in:email,phone',
            'contact'      => [
                'required',
                function ($attribute, $value, $fail) {
                    $type = $this->input('contact_type');

                    if ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('Invalid email address.');
                    }

                    if ($type === 'phone' && !preg_match('/^(\+88)?01[3-9]\d{8}$/', $value)) {
                        $fail('Invalid phone number.');
                    }
                },
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (!\App\Models\Product::where('id', $this->product_id)->exists()) {
                    $validator->errors()->add('product_id', 'Product does not exist.');
                }
            }
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required'   => 'Product is required.',
            'contact_type.required' => 'Contact type is required.',
            'contact_type.in'       => 'Contact type must be either email or phone.',
            'contact.required'      => 'Contact information is required.',
        ];
    }
}
