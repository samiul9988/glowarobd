<?php
namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\JobPostResource;
use App\Models\JobPost;
use App\Models\JobApplication;
use App\Utility\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class JobPostController extends Controller
{
    public function getPosts(Request $request)
    {
        $jobPosts = JobPost::query()
            ->where('status', 'published')
            ->when($request->search, function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->search . '%');
            })
            ->when($request->employment_type, function ($query) use ($request) {
                $query->where('employment_type', $request->employment_type);
            })
            ->when($request->experience === 'no', function ($query) {
                $query->whereNull('experience');
            })
            ->latest()
            ->paginate($request->perPage ?? 10);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => JobPostResource::collection($jobPosts),
        ]);
    }

    public function jobDetails(string $slug)
    {
        $jobPost = JobPost::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();
        if (!$jobPost) {
            return response()->json([
                'success' => false,
                'message' => 'Job post not found',
                'status' => 404,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => new JobPostResource($jobPost),
        ]);
    }

    public function apply(Request $request)
    {
        $baseValidation = $request->validate([
            'job_post_id' => ['required', 'integer', Rule::exists('job_posts', 'id')],
            'name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'min:11', 'max:11', 'regex:/^01[3-9][0-9]{8}$/'],
            'application_input_fields' => ['nullable', 'array'],
        ]);

        $jobPost = JobPost::query()->find($baseValidation['job_post_id']);
        if (!$jobPost) {
            return response()->json([
                'success' => false,
                'message' => 'Job post not found.',
            ], 404);
        }

        if (JobApplication::where('job_post_id', $jobPost->id)->where('applicant_phone', $baseValidation['phone'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied for this job post with the provided phone number.',
            ], 422);
        }

        $applicationFields = collect(data_get($jobPost->application_form, 'fields', []))
            ->filter(fn($field) => is_array($field))
            ->values();

        $validationRules = [];
        $selectOptionsByIdentifier = [];

        foreach ($applicationFields as $index => $field) {
            $identifier = $this->resolveFieldIdentifier($field, $index);
            $type       = data_get($field, 'type', 'text');
            $isRequired = (bool) data_get($field, 'is_required', false);
            $attribute  = 'application_input_fields.' . $identifier;

            if ($type === 'file') {
                $validationRules[$attribute] = $isRequired
                    ? ['required', 'file', 'max:5120']
                    : ['nullable', 'file', 'max:5120'];

                $fileType = data_get($field, 'file_type', 'any');
                if ($fileType === 'image') {
                    $validationRules[$attribute][] = 'image';
                } elseif ($fileType === 'document') {
                    $validationRules[$attribute][] = 'mimes:pdf,doc,docx';
                }

                continue;
            }

            $rules = $isRequired ? ['required'] : ['nullable'];

            if ($type === 'number') {
                $rules[] = 'numeric';
            } elseif ($type === 'date') {
                $rules[] = 'date';
            } elseif ($type === 'time') {
                $rules[] = 'date_format:H:i';
            } elseif ($type === 'datetime') {
                $rules[] = 'date';
            } else {
                $rules[] = 'string';
                $rules[] = 'max:5000';
            }

            if ($type === 'select') {
                $options = $this->normalizeFieldOptions(data_get($field, 'options', ''));
                if (count($options)) {
                    $rules[] = Rule::in($options);
                    $selectOptionsByIdentifier[$identifier] = $options;
                }
            }

            $validationRules[$attribute] = $rules;
        }

        $validator = Validator::make(
            array_replace_recursive($request->all(), $request->allFiles()),
            $validationRules
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?: 'Please fill in all required fields.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $submittedInput = (array) $request->input('application_input_fields', []);
        $submittedFiles = (array) $request->file('application_input_fields', []);

        $submittedValues = [];
        $fieldSnapshot   = [];
        $attachmentIds   = [];

        foreach ($applicationFields as $index => $field) {
            $identifier = $this->resolveFieldIdentifier($field, $index);
            $type       = data_get($field, 'type', 'text');
            $label      = trim((string) data_get($field, 'label', 'Field ' . ($index + 1)));
            $rawValue   = data_get($submittedInput, $identifier);

            $storedValue = null;
            if ($type === 'file') {
                $uploadedFile = data_get($submittedFiles, $identifier);
                if ($uploadedFile) {
                    $upload = (new FileUpload)->upload($uploadedFile);
                    if ($upload) {
                        $storedValue     = (int) $upload->id;
                        $attachmentIds[] = (int) $upload->id;
                    }
                }
            } else {
                if (is_string($rawValue)) {
                    $rawValue = trim($rawValue);
                }

                if ($rawValue !== '') {
                    $storedValue = $rawValue;
                }
            }

            $submittedValues[$identifier] = $storedValue;

            $fieldSnapshot[$identifier] = [
                'identifier'  => $identifier,
                'label'       => $label,
                'type'        => $type,
                'is_required' => (bool) data_get($field, 'is_required', false),
                'position'    => (int) data_get($field, 'position', $index + 1),
                'options'     => $selectOptionsByIdentifier[$identifier] ?? $this->normalizeFieldOptions(data_get($field, 'options', '')),
            ];
        }

        [$applicantName, $applicantEmail, $applicantPhone] = $this->extractApplicantContactDetails($fieldSnapshot, $submittedValues);

        $jobApplication = JobApplication::query()->create([
            'job_post_id'          => $jobPost->id,
            'applicant_name'       => $applicantName,
            'applicant_email'      => $applicantEmail,
            'applicant_phone'      => $applicantPhone,
            'status'               => 'pending',
            'submitted_values'     => $submittedValues,
            'field_snapshot'       => $fieldSnapshot,
            'uploaded_attachments' => array_values(array_unique($attachmentIds)),
        ]);

        $jobApplication->application()->create([
            'applicant_name' => $applicantName ?: 'Applicant #' . $jobApplication->id,
            'subject'        => 'Application for ' . $jobPost->title,
            'type'           => \App\Enums\ApplicationTypes::JOB,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully.',
        ]);
    }

    private function resolveFieldIdentifier(array $field, int $index): string
    {
        $identifier = trim((string) data_get($field, 'id', ''));

        return $identifier !== '' ? $identifier : 'field_' . ($index + 1);
    }

    private function normalizeFieldOptions(array | string | null $options): array
    {
        if (is_array($options)) {
            return array_values(array_filter(array_map(function ($option) {
                return trim((string) $option);
            }, $options)));
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $options))));
    }

    private function extractApplicantContactDetails(array $fieldSnapshot, array $submittedValues): array
    {
        $name  = null;
        $email = null;
        $phone = null;

        foreach ($fieldSnapshot as $identifier => $field) {
            $value = data_get($submittedValues, $identifier);
            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $haystack = Str::lower(trim((string) data_get($field, 'label', '') . ' ' . $identifier));
            if ($email === null && Str::contains($haystack, ['email', 'e-mail']) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $email = trim($value);

                continue;
            }

            if ($phone === null && Str::contains($haystack, ['phone', 'mobile', 'contact', 'whatsapp'])) {
                $phone = trim($value);

                continue;
            }

            if ($name === null && Str::contains($haystack, ['name', 'full_name', 'fullname'])) {
                $name = trim($value);
            }
        }

        return [$name, $email, $phone];
    }
}
