<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HighlightedItemRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust if needed for auth logic
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'linkable_type' => 'required|string|in:custom,product,brand,category',
            'linkable_id' => 'nullable|required_if:linkable_type,product,brand,category',
            'custom_link' => 'nullable|required_if:linkable_type,custom|url|max:255',
            'banner' => 'required|string',
            'highlight_icons' => 'required|array|max:4',
            'highlight_icons.*' => 'required|string',
            'highlight_labels' => 'required|array|max:4',
            'highlight_labels.*' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The title field is required.',
            'subtitle.required' => 'The subtitle field is required.',
            'description.required' => 'The description field is required.',
            'linkable_type.required' => 'The link type field is required.',
            'linkable_type.in' => 'The selected link type is invalid.',
            'linkable_id.required_if' => 'The link item field is required.',
            'custom_link.required_if' => 'The custom link field is required.',
            'custom_link.url' => 'The custom link must be a valid URL.',
            'banner.required' => 'Banner image is required.',
            'highlight_icons.required' => 'Highlights are required.',
            'highlight_icons.*.required' => 'Highlights are required.',
            'highlight_labels.required' => 'Highlights are required.',
            'highlight_labels.*.required' => 'Highlights are required.',
        ];
    }

    /**
     * Override to merge highlight errors into one message
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        // If any highlight field failed, unify messages
        $highlightError = false;
        foreach ($errors as $field => $messages) {
            if (
                str_starts_with($field, 'highlight_icons') ||
                str_starts_with($field, 'highlight_labels')
            ) {
                $highlightError = true;
                unset($errors[$field]); // remove individual highlight errors
            }
        }

        if ($highlightError) {
            $errors['highlights'] = ['Highlights are required.'];
        }

        $response = $this->expectsJson()
            ? response()->json([
                'success' => false,
                'message' => 'Highlights are required.',
                'errors'  => $errors,
            ], 422)
            : redirect()
                ->back()
                ->withInput()
                ->withErrors($errors);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}
