<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class JobPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $main = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => Str::limit(strip_tags($this->description), 140),
            'location' => $this->location,
            'original_employment_type' => $this->employment_type,
            'employment_type' => ucwords(str_replace('_', ' ', $this->employment_type ?? 'full_time')),
            'salary_min' => (float) $this->salary_min ?? 0,
            'salary_max' => (float) $this->salary_max ?? 0,
            'experience' => (string) $this->experience ?? '',
            'vacancy' => (int) $this->vacancy ?? 1,
            'deadline' => optional($this->deadline)->format('d M, Y'),
            'status' => $this->status,
            'published_at' => optional($this->published_at)->format('d M, Y'),
        ];

        $details = [
            'description' => $this->description,
            'benefits' => $this->benefits ? array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $this->benefits))) : [],
            'application_form' => [
                'title' => data_get($this->application_form, 'title', 'Apply For This Position'),
                'button_text' => data_get($this->application_form, 'button_text', 'Submit Application'),
                'fields' => collect(data_get($this->application_form, 'fields', []))->filter(fn($field) => is_array($field))
                    ->map(function ($field) {
                        return [
                            'id' => data_get($field, 'id'),
                            'label' => data_get($field, 'label'),
                            'type' => data_get($field, 'type'),
                            'placeholder' => data_get($field, 'placeholder'),
                            'help_text' => data_get($field, 'help_text'),
                            'options' => $this->normalizeFieldOptions(data_get($field, 'options', [])),
                            'file_type' => data_get($field, 'file_type', 'any'),
                            'position' => (int) data_get($field, 'position', 0),
                            'is_required' => (bool) data_get($field, 'is_required', false),
                        ];
                    })->values(),
            ],
        ];

        if ($request->routeIs('api.job_posts.details')) {
            return array_merge($main, $details);
        }

        return $main;
    }

    protected function normalizeFieldOptions($options): array
    {
        if (is_array($options)) {
            $lines = array_values(array_filter(array_map('trim', $options)));
        } else {
            $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $options))));
        }
        return $lines;
    }
}
