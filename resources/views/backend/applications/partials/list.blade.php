@if($applications->isEmpty())

    <div class="text-center py-5 text-muted">
        <i class="las la-calendar-times" style="font-size:3rem"></i>
        <p class="mt-2">No application records found.</p>
    </div>

@else

<div class="table-responsive">
    <table class="table table-borderless table-sm mb-0">

        {{-- HEADER --}}
        <thead>
            <tr class="text-muted" style="font-size:.75rem; text-transform:uppercase;">
                <th>Subject</th>
                <th class="text-center">Type</th>
                <th class="text-center">Status</th>
                <th width="20%" class="text-center">Note</th>
                <th width="15%" class="text-center">Action</th>
            </tr>
        </thead>

        <tbody>

            @foreach($applications as $application)
                @php
                    $leave = $application->applicable instanceof \App\Models\Leave ? $application->applicable : null;
                    $attachmentIds = $application->attachments ?? [];
                    $attachmentMeta = collect($attachmentIds)->map(function ($attachment) {
                        $url = uploaded_asset($attachment);
                        $path = $url ? parse_url($url, PHP_URL_PATH) : null;
                        $name = 'Attachment';

                        if ($path) {
                            $name = basename($path);
                        } elseif (!empty($url)) {
                            $name = basename($url);
                        }

                        return [
                            'id' => $attachment,
                            'url' => $url,
                            'name' => $name,
                        ];
                    })->values()->all();

                    $leaveType = 'single';
                    $leaveStart = $leave?->start_date;
                    $leaveEnd = $leave?->end_date ?? $leave?->start_date;

                    if ($leaveStart && $leaveEnd && !$leaveStart->equalTo($leaveEnd)) {
                        $leaveType = 'multiple';
                    }

                    $leavePayload = $leave && $leaveStart ? [
                        'start_date' => $leaveStart->format('Y-m-d'),
                        'end_date' => ($leaveEnd ?? $leaveStart)->format('Y-m-d'),
                        'human_start' => $leaveStart->format('d M Y'),
                        'human_end' => ($leaveEnd ?? $leaveStart)->format('d M Y'),
                        'type' => $leaveType,
                        'duration' => $leave->duration
                    ] : null;

                    $editable = [
                        'id' => $application->id,
                        'type' => $application->type->value,
                        'subject' => $application->subject,
                        'reason' => $application->content,
                        'attachments' => $attachmentMeta,
                        'attachment_ids' => implode(',', $attachmentIds),
                        'leave' => $leavePayload,
                    ];
                @endphp
                <tr style="border-top:1px solid #f1f1f1;">
                    <td>
                        {{ Str::limit($application->subject, 30) }}
                        @if (strlen($application->subject) > 30)
                            @include('components.tooltip', [
                                'title' => $application->subject,
                                'class' => 'ml-1'
                            ])
                        @endif
                    </td>

                    <td class="text-center">
                        <span class="badge badge-inline badge-info">
                            {{ $application->type->label() }}
                        </span>

                        @if($application->type === 'leave')
                            <span class="d-block">
                                <small>Test</small>
                            </span>
                        @endif
                    </td>

                    <td class="text-center">
                        @php
                            $statusColor = [
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'pending' => 'warning',
                            ];
                        @endphp

                        <span class="badge badge-inline badge-{{ $statusColor[$application->status] ?? 'info' }}">
                            {{ ucfirst($application->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($application->note)
                            {{ Str::limit($application->note, 30) }}
                            @if (strlen($application->note) > 30)
                                @include('components.tooltip', [
                                    'title' => $application->note,
                                    'class' => 'ml-1',
                                    'position' => 'left'
                                ])
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('applications.show', $application->id) }}" target="_blank" class="btn btn-sm btn-primary btn-icon view-application">
                            <i class="las la-eye"></i>
                        </a>
                        @if($application->status === 'pending' && $application->user_id === Auth::id())
                            <button class="btn btn-sm btn-primary btn-icon edit-application" data-application='@json($editable)'>
                                <i class="las la-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-icon delete-application" data-id="{{ $application->id }}">
                                <i class="las la-trash"></i>
                            </button>
                        @else
                            <button class="btn btn-sm btn-dark btn-icon" disabled>
                                <i class="las la-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-dark btn-icon" disabled>
                                <i class="las la-trash"></i>
                            </button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endif
