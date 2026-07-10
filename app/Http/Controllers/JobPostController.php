<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobPostRequest;
use App\Models\JobPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JobPostController extends Controller
{
    public function index(Request $request): View
    {
        $jobPosts = JobPost::query()
            ->withCount('applications')
            ->when($request->search, function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request->search.'%');
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->type, function ($query) use ($request) {
                $query->where('employment_type', $request->type);
            })
            ->latest()
            ->paginate(20);

        return view('backend.jobs.index', compact('jobPosts'));
    }

    public function careers(Request $request)
    {
        $jobPosts = JobPost::query()
            ->where('status', 'published')
            ->when($request->search, function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request->search.'%');
            })
            ->when($request->employment_type, function ($query) use ($request) {
                $query->where('employment_type', $request->employment_type);
            })
            ->when($request->experience === 'no', function ($query) {
                $query->whereNull('experience');
            })
            ->latest()
            ->paginate(10);

        return view('frontend.jobs.list', compact('jobPosts'));
    }

    public function show(string $slug): View
    {
        $jobPost = JobPost::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return view('frontend.jobs.details', compact('jobPost'));
    }

    public function create(): View
    {
        return view('backend.jobs.create');
    }

    public function store(JobPostRequest $request): RedirectResponse
    {
        // dd($request->validated()));
        $validated = $request->validated();

        $validated['slug'] = $this->resolveUniqueSlug(
            filled($validated['slug'] ?? null) ? $validated['slug'] : $validated['title']
        );

        if ($validated['status'] === 'scheduled' && blank($validated['published_at'] ?? null)) {
            return back()
                ->withErrors(['published_at' => 'Publish date is required when status is scheduled.'])
                ->withInput();
        }

        $validated['application_form'] = [
            'title' => $validated['application_form_title'] ?? null,
            'button_text' => $validated['application_button_text'] ?? null,
            'fields' => $this->prepareApplicationInputFields(
                $validated['application_input_fields'] ?? []
            ),
        ];

        unset($validated['application_form_title'], $validated['application_button_text'], $validated['application_input_fields']);

        JobPost::query()->create($validated);

        flash('Job post created successfully.')->success();

        return redirect()->route('job_posts.index');
    }

    public function edit(JobPost $jobPost): View
    {
        return view('backend.jobs.edit', compact('jobPost'));
    }

    public function update(JobPostRequest $request, JobPost $jobPost): RedirectResponse
    {
        $validated = $request->validated();

        $validated['slug'] = $this->resolveUniqueSlug(
            filled($validated['slug'] ?? null) ? $validated['slug'] : $validated['title'],
            $jobPost->id
        );

        if ($validated['status'] === 'scheduled' && blank($validated['published_at'] ?? null)) {
            return back()
                ->withErrors(['published_at' => 'Publish date is required when status is scheduled.'])
                ->withInput();
        }

        $validated['application_form'] = [
            'title' => $validated['application_form_title'] ?? null,
            'button_text' => $validated['application_button_text'] ?? null,
            'fields' => $this->prepareApplicationInputFields(
                $validated['application_input_fields'] ?? []
            ),
        ];

        unset($validated['application_form_title'], $validated['application_button_text'], $validated['application_input_fields']);

        $jobPost->update($validated);

        flash('Job post updated successfully.')->success();

        return back();
    }

    private function resolveUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $slug = Str::slug($value) ?: Str::random(8);
        $existingJobPost = JobPost::query()
            ->where('slug', 'like', '%'.$slug.'%')
            ->when($ignoreId, function ($query) use ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            })
            ->latest()
            ->first();

        return $existingJobPost ? $slug.'-'.$existingJobPost->id : $slug;
    }

    private function prepareApplicationInputFields(array $rawFields): array
    {
        $allowedTypes = ['text', 'number', 'select', 'date', 'time', 'datetime', 'textarea', 'file'];
        $allowedFileTypes = ['any', 'image', 'document'];
        $usedIdentifiers = [];

        $normalizedFields = collect($rawFields)
            ->map(function ($field, $index) use ($allowedTypes, $allowedFileTypes, &$usedIdentifiers) {
                $label = trim((string) data_get($field, 'label', ''));
                if ($label === '') {
                    return null;
                }

                $rawIdentifier = trim((string) data_get($field, 'id', ''));
                $identifier = $rawIdentifier !== ''
                    ? (string) preg_replace('/[^A-Za-z0-9_-]/', '', $rawIdentifier)
                    : '';
                if ($identifier === '' || in_array($identifier, $usedIdentifiers, true)) {
                    $identifier = Str::uuid()->toString();
                }
                $usedIdentifiers[] = $identifier;

                $type = data_get($field, 'type', 'text');
                if (! in_array($type, $allowedTypes, true)) {
                    $type = 'text';
                }

                $position = (int) data_get($field, 'position', $index + 1);
                if ($position < 1) {
                    $position = $index + 1;
                }

                $fileType = data_get($field, 'file_type', 'any');
                if (! in_array($fileType, $allowedFileTypes, true)) {
                    $fileType = 'any';
                }

                return [
                    'id' => $identifier,
                    'label' => $label,
                    'type' => $type,
                    'placeholder' => trim((string) data_get($field, 'placeholder', '')),
                    'expected_value' => trim((string) data_get($field, 'expected_value', '')),
                    'help_text' => trim((string) data_get($field, 'help_text', '')),
                    'options' => trim((string) data_get($field, 'options', '')),
                    'file_type' => $type === 'file' ? $fileType : null,
                    'position' => $position,
                    'is_required' => (bool) data_get($field, 'is_required', false),
                ];
            })
            ->filter()
            ->sortBy('position')
            ->values()
            ->all();

        return $normalizedFields;
    }
}
