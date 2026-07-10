<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentMail;
use App\Mail\InterviewMail;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Utility\FileUpload;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PDF;

class JobApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $applications = JobApplication::query()
            ->with('job:id,title,role,employment_type')
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('applicant_name', 'like', '%'.$request->search.'%')
                        ->orWhere('applicant_email', 'like', '%'.$request->search.'%')
                        ->orWhere('applicant_phone', 'like', '%'.$request->search.'%');
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->shortlisted, function ($query) {
                $query->where('shortlisted', true);
            })
            ->orderBy('matching_score', 'desc')
            ->latest()
            ->paginate(20);

        $counts = JobApplication::selectRaw("
            COALESCE(COUNT(*), 0) as total,
            COALESCE(SUM(CASE WHEN shortlisted = 1 THEN 1 ELSE 0 END), 0) as shortlist,
            COALESCE(SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END), 0) as hired
        ")
        ->first()
        ->toArray();

        return view('backend.jobs.applications.index', compact('applications', 'counts'));
    }

    public function show(int $id): View
    {
        $application = JobApplication::with('job')->findOrFail($id);
        return view('backend.jobs.applications.show', compact('application'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_post_id' => ['required', 'integer', Rule::exists('job_posts', 'id')],
            'name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'min:11', 'max:11', 'regex:/^01[3-9][0-9]{8}$/'],
            'email' => ['nullable', 'email'],
            'application_input_fields' => ['nullable', 'array'],
        ]);

        $jobPost = JobPost::find($validated['job_post_id']);

        $isExists = JobApplication::where('job_post_id', $jobPost->id)
            ->where(function ($query) use ($validated) {
                $query->where('applicant_phone', $validated['phone'])
                    ->orWhere('applicant_email', $validated['email']);
            })
            ->exists();
        if ($isExists) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied for this job post with the provided phone number or email.',
            ], 422);
        }

        $applicationFields = collect(data_get($jobPost->application_form, 'fields', []))
            ->filter(fn ($field) => is_array($field))
            ->values();

        $validationRules = [];
        $selectOptionsByIdentifier = [];

        foreach ($applicationFields as $index => $field) {
            $identifier = $this->resolveFieldIdentifier($field, $index);
            $type = data_get($field, 'type', 'text');
            $isRequired = (bool) data_get($field, 'is_required', false);
            $attribute = 'application_input_fields.'.$identifier;

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
                'errors' => $validator->errors(),
            ], 422);
        }

        $submittedInput = (array) $request->input('application_input_fields', []);
        $submittedFiles = (array) $request->file('application_input_fields', []);

        $submittedValues = [];
        $fieldSnapshot = [];
        $attachmentIds = [];

        foreach ($applicationFields as $index => $field) {
            $identifier = $this->resolveFieldIdentifier($field, $index);
            $type = data_get($field, 'type', 'text');
            $label = trim((string) data_get($field, 'label', 'Field '.($index + 1)));
            $rawValue = data_get($submittedInput, $identifier);

            $storedValue = null;
            if ($type === 'file') {
                $uploadedFile = data_get($submittedFiles, $identifier);
                if ($uploadedFile) {
                    $upload = (new FileUpload)->upload($uploadedFile);
                    if ($upload) {
                        $storedValue = (int) $upload->id;
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
                'identifier' => $identifier,
                'label' => $label,
                'type' => $type,
                'is_required' => (bool) data_get($field, 'is_required', false),
                'position' => (int) data_get($field, 'position', $index + 1),
                'expected_value' => trim((string) data_get($field, 'expected_value', '')),
                'options' => $selectOptionsByIdentifier[$identifier] ?? $this->normalizeFieldOptions(data_get($field, 'options', '')),
            ];
        }

        // Calculate matching score based on expected vs submitted values
        $totalExpectedFields = 0;
        $matchedFields = 0;

        foreach ($fieldSnapshot as $snapshot) {
            $expectedValues = collect(explode('|', $snapshot['expected_value']))
                ->map(fn ($v) => trim(strtolower((string) $v)))
                ->filter()
                ->toArray();

            if (empty($expectedValues) || $snapshot['type'] === 'file') {
                continue;
            }

            $totalExpectedFields++;
            $submittedValue = $submittedValues[$snapshot['identifier']] ?? null;

            // Compare values (case-insensitive for text fields, strict for others)
            if (! is_null($submittedValue) &&
                in_array(strtolower((string) $submittedValue), $expectedValues, true)) {
                $matchedFields++;
            }
        }

        $matchingScore = $totalExpectedFields > 0
            ? round(($matchedFields / $totalExpectedFields) * 100)
            : 0;

        try {
            JobApplication::query()->create([
                'job_post_id' => $jobPost->id,
                'applicant_name' => $request->name,
                'applicant_phone' => $request->phone,
                'applicant_email' => $request->get('email'),
                'subject' => 'Application for '.$jobPost->title,
                'status' => 'pending',
                'submitted_values' => $submittedValues,
                'field_snapshot' => $fieldSnapshot,
                'uploaded_attachments' => array_values(array_unique($attachmentIds)),
                'matching_score' => (int) $matchingScore,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving job application: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting your application. Please try again later.',
            ], 500);
        }
    }

    private function resolveFieldIdentifier(array $field, int $index): string
    {
        $identifier = trim((string) data_get($field, 'id', ''));

        return $identifier !== '' ? $identifier : 'field_'.($index + 1);
    }

    private function normalizeFieldOptions(array|string|null $options): array
    {
        if (is_array($options)) {
            return array_values(array_filter(array_map(function ($option) {
                return trim((string) $option);
            }, $options)));
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $options))));
    }

    public function updateInfo(Request $request, int $id)
    {
        $application = JobApplication::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'min:11', 'max:11', 'regex:/^01[3-9][0-9]{8}$/'],
            'email' => ['nullable', 'email'],
        ]);

        $isExists = JobApplication::where('job_post_id', $application->job_post_id)
            ->where(function ($query) use ($validated) {
                $query->where('applicant_phone', $validated['phone'])
                    ->orWhere('applicant_email', $validated['email']);
            })
            ->where('id', '!=', $id)
            ->exists();

        if ($isExists) {
            return response()->json([
                'success' => false,
                'message' => 'Another application with the provided phone number or email already exists for this job post.',
            ], 422);
        } else {
            $application->update([
                'applicant_name' => $validated['name'],
                'applicant_phone' => $validated['phone'],
                'applicant_email' => $validated['email'],
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Applicant information updated successfully.',
            ]);
        }
    }

    /**
     * Add a note to an application
     */
    public function addNote(Request $request, int $id): JsonResponse
    {
        $application = JobApplication::findOrFail($id);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:500'],
        ]);

        $notes = $application->notes ?? [];
        $notes[] = [
            'id' => uniqid(),
            'text' => $validated['note'],
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'created_at' => now()->toDateTimeString(),
        ];

        $application->update(['notes' => $notes]);

        $note = end($notes);
        return response()->json([
            'success' => true,
            'message' => 'Note added successfully.',
            'note_view' => view('backend.jobs.applications.partials.note-item', ['note' => $note])->render(),
            'note_count' => count($notes) . Str::plural(' note', count($notes)),
        ]);
    }

    /**
     * Delete a note from an application
     */
    public function deleteNote(Request $request, int $id): JsonResponse
    {
        $application = JobApplication::findOrFail($id);
        $notes = collect($application->notes ?? []);


        $note = $notes->where('id', $request->note_id)->first();
        if (! $note) {
            return response()->json([
                'success' => false,
                'message' => 'Note not found.',
            ], 404);
        } elseif ($note['user_id'] != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own notes.',
            ], 403);
        }

        $notes = $notes->where('id', '!=', $request->note_id)->values()->all();
        $application->update(['notes' => $notes]);

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully.',
            'note_count' => count($notes) . Str::plural(' Note', count($notes)),
        ]);
    }

    /**
     * Update application status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $application = JobApplication::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(\App\Enums\JobApplicationStatus::class)],
        ]);

        $application->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
        ]);
    }

    /**
     * Mark application as shortlisted
     */
    public function updateShortlist(int $id): JsonResponse
    {
        $application = JobApplication::findOrFail($id);
        $application->update(['shortlisted' => ! $application->shortlisted]);

        return response()->json([
            'success' => true,
            'message' => $application->shortlisted ? 'Applicant shortlisted successfully.' : 'Applicant removed from shortlist.',
            'shortlisted' => $application->shortlisted,
        ]);
    }

    /**
     * Send interview SMS to applicant
     */
    public function sendSms(Request $request, int $id): JsonResponse
    {
        $application = JobApplication::with('job')->findOrFail($id);

        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal: today'],
            'time' => ['required', 'date_format:H:i'],
            'sendEmail' => ['required', 'boolean'],
        ]);

        $formattedDateTime = Carbon::parse($validated['date'])->setTimeFromTimeString($validated['time']);
        if ($formattedDateTime->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'The ' . strtolower($validated['type']) . ' date and time must be in the future.',
            ], 422);
        }

        return $this->sendInterviewSms($application, $validated, $formattedDateTime);
    }

    private function sendInterviewSms(JobApplication $application, array $validated, Carbon $formattedDateTime)
    {
        try {
            $isEmail = $validated['sendEmail'] ?? false;
            $content = '';

            if ($isEmail) {
                $emailTemplate = \App\Models\MailTemplate::where('type', 'Interview')->first();
                if (!$emailTemplate || !$emailTemplate->content) {
                    throw new \Exception('No email template found for interview invitations. Please set up the template and try again.');
                }
                $content = $emailTemplate->content;
            } else {
                $smsTemplate = \App\Models\SmsTemplate::where('identifier', 'interview')->first();
                if (!$smsTemplate || !$smsTemplate->sms_body) {
                    throw new \Exception('No SMS template found for interview invitations. Please set up the template and try again.');
                }
                $content = $smsTemplate->sms_body;
            }

            $formattedContent = str_replace(
                ['[[candidate_name]]', '[[role]]', '[[company_name]]', '[[interview_date]]', '[[interview_time]]'],
                [
                    $application->applicant_name,
                    $application->job->role ?: $application->job->title ?: 'N/A',
                    config('app.name'),
                    $formattedDateTime->format('F j, Y'),
                    $formattedDateTime->format('g:i A'),
                ],
                $content
            );

            if ($isEmail) {
                $data = [
                    'candidate_name' => $application->applicant_name,
                    'role' => $application->job->role ?: $application->job->title ?: 'N/A',
                    'company_name' => config('app.name'),
                    'interview_date' => $formattedDateTime->format('F j, Y'),
                    'interview_time' => $formattedDateTime->format('g:i A'),
                    'content' => $formattedContent,
                    'subject' => 'Interview Invitation for '.$application->job->role ?: $application->job->title ?: 'N/A',
                ];
                Mail::to($application->applicant_email)->queue(new InterviewMail($data));
            } else {
                $response = sendSMS(
                    $application->applicant_phone,
                    $formattedContent,
                    type: 'job_interview'
                );

                $response = json_decode($response, true);
                if ($response['status'] === 'FAILED') {
                    throw new \Exception($response['error_message'] ?? 'Unknown error occurred while sending SMS.');
                }
            }

            $log = [
                'type' => 'sms_sent',
                'message' => $isEmail ? 'Interview email sent to applicant.' : 'Interview SMS sent to applicant.',
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'created_at' => now()->toDateTimeString(),
            ];
            $application->logs = array_merge($application->logs ?? [], [$log]);
            $application->save();
            return response()->json([
                'success' => true,
                'message' => 'Invitation sent successfully.',
                'log_view' => view('backend.jobs.applications.partials.log-item', ['log' => $log])->render(),
                'log_count' => count($application->logs) . Str::plural(' Event', count($application->logs)),
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending interview invitation: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send invitation. Please try again later.',
            ], 500);
        }
    }
}
