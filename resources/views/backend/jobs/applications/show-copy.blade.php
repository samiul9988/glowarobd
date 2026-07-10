@extends('backend.layouts.app')
@php
    $jobSubmittedValues = $application?->submitted_values ?? [];
    $jobFieldSnapshot = collect($application?->field_snapshot ?? [])->sortBy('position');
    $resolveAttachmentUrl = function ($attachment): ?string {
        if (is_numeric($attachment)) {
            return uploaded_asset((int) $attachment);
        }

        if (is_string($attachment) && trim($attachment) !== '') {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($attachment);
        }

        return null;
    };
    $scoreColor = match(true) {
        $application->matching_score >= 80 => 'success',
        $application->matching_score >= 50 => 'warning',
        default => 'danger',
    };
@endphp
@section('content')
	<style>
		.info-label {
			font-size: .72rem;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: .06em;
			color: #8898aa;
		}

		.info-value {
			font-size: .92rem;
			font-weight: 500;
			color: #32325d;
		}

		.application-meta {
			font-size: .78rem;
			color: #8f9bb3;
		}

		.application-hero {
			background: #f8f9fc;
			border-bottom: 1px solid #edf1f7;
		}

		.application-detail-card {
			border: 1px solid #eef1f7;
			border-radius: .65rem;
			padding: 1rem 1.25rem;
			background: #fff;
		}

		.attachment-card {
			border: 1px solid #e9ecef;
			border-radius: .5rem;
			padding: .75rem 1rem;
			display: flex;
			align-items: center;
			gap: .75rem;
			transition: box-shadow .2s;
			text-decoration: none !important;
		}

		.attachment-card:hover {
			box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
		}

		.attachment-icon {
			width: 38px;
			height: 38px;
			border-radius: 8px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.2rem;
			flex-shrink: 0;
		}
	</style>

    @if(auth()->user()->user_type === 'admin' || in_array('manage_applications', $_authPermissions))
        <div class="d-flex align-items-center mb-3 mt-2">
            <a href="{{ route('job_applications.index') }}" class="btn btn-sm btn-light mr-2">
                <i class="las la-arrow-left"></i> Back
            </a>
            <span class="text-muted small">Applications / <strong>Details</strong></span>
        </div>
    @endif

	<div class="card border-0 shadow-sm overflow-hidden">
		<div class="application-hero p-4">
			<div class="d-flex flex-wrap align-items-start justify-content-between" style="gap: 12px;">
                <div>
                    <div class="">
						<div class="info-label mb-1">Applicant</div>
						<div class="info-value font-weight-bold">{{ $application->applicant_name ?? 'N/A' }}</div>
						<small class="text-muted d-block">{{ $application->applicant_phone ?? '—' }}</small>
                        @if(!is_null($application->applicant_email))
						<small class="text-muted d-block mt-1">{{ $application->applicant_email }}</small>
                        @endif
					</div>
                </div>
				<div>
                    <span class="badge badge-inline badge-primary badge-pill text-uppercase mb-2">
                        {{ $application->status->label() }}
                    </span>
					<small class="application-meta d-block">
						{{ optional($application->created_at)->format('d M Y, h:i A') ?? '—' }}
					</small>
				</div>


			</div>
		</div>

		<div class="card-body p-4">
			<div class="row">
				<div class="col-lg-8 mb-3 mb-lg-0">
					<div class="application-detail-card mb-3">
						<div class="info-label mb-1">Subject</div>
						<p class="mb-0 info-value">
                            {{ $application->subject ?: 'Application for ' . $application->job->role }}
                        </p>
					</div>

					<div class="application-detail-card mb-3">
						<div class="d-flex mb-3 align-items-center justify-content-between">
                            <div class="info-label">Provided Information</div>
                            <div class="info-label">
                                Matching Score: <span class="text-{{ $scoreColor }}">{{ $application->matching_score }}%</span>
                            </div>
                        </div>
						<div class="table-responsive">
                            <table class="table table-bordered rounded table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th width="20%">Question</th>
                                        <th>Value</th>
                                        <th width="25%">Expected Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($jobFieldSnapshot as $identifier => $field)
                                        @php
                                            $fieldValue = data_get($jobSubmittedValues, $identifier);
                                        @endphp
                                        <tr>
                                            <td>{{ data_get($field, 'label', 'N/A') }}</td>
                                            <td>
                                                @if (data_get($field, 'type') === 'file' && $fieldValue)
                                                    @php
                                                        $fieldAttachmentUrl = $resolveAttachmentUrl($fieldValue);
                                                    @endphp
                                                    @if ($fieldAttachmentUrl)
                                                        <a href="{{ $fieldAttachmentUrl }}" target="_blank">View Attachment</a>
                                                    @else
                                                        <span class="text-muted">Attachment unavailable</span>
                                                    @endif
                                                @elseif (filled($fieldValue))
                                                    {{ is_array($fieldValue) ? json_encode($fieldValue) : $fieldValue }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ data_get($field, 'expected_value', 'N/A') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No submitted field values found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
					</div>

                    <div class="application-detail-card mb-3">
						<div class="info-label mb-1">Actions</div>

					</div>
				</div>

				<div class="col-lg-4">
					<div class="application-detail-card mb-4">
						<div class="info-label mb-2">Timeline</div>
						<div class="mb-2">
							<small class="text-muted d-block">Submitted At</small>
							<span class="info-value">{{ optional($application->created_at)->format('d M Y, h:i A') ?? '—' }}</span>
						</div>
						<div class="mb-2">
							<small class="text-muted d-block">Last Updated</small>
							<span class="info-value">{{ optional($application->updated_at)->format('d M Y, h:i A') ?? '—' }}</span>
						</div>
					</div>

                    <div class="application-detail-card">
                        <div class="info-label mb-2">Notes</div>
                        <div class="mb-2 form-group">
                            <textarea class="form-control form-control-sm" id="note" rows="3" placeholder="Enter any note here"></textarea>
                        </div>
                        <div class="mb-2 text-center">
                            <button class="btn btn-sm btn-primary w-100" id="add-note-btn">
                                <i class="las la-plus"></i> Add Note
                            </button>
                        </div>
                        <div id="notes-list">
                            @php
                                $notes = collect($application->notes ?? [])->map(function($note) {
                                    return [
                                        'id' => data_get($note, 'id'),
                                        'text' => data_get($note, 'text'),
                                        'user_id' => data_get($note, 'user_id'),
                                        'user_name' => data_get($note, 'user_name'),
                                        'created_at' => parseDate(data_get($note, 'created_at', '')),
                                    ];
                                })
                                ->sortByDesc('created_at')
                                ->values()
                                ->all();
                            @endphp
                            @foreach ($notes as $note)
                                <div class="mb-2 p-2 border rounded" id="{{ $note['id'] }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="font-weight-bold text-muted">{{ $note['user_name'] ?? 'Unknown' }}</span>
                                        <small class="text-muted">{{ $note['created_at']?->diffForHumans() ?? '—' }}</small>
                                    </div>
                                    <div class="d-flex">
                                        <p class="mb-0" style="white-space: pre-wrap;">{{ $note['text'] }}</p>
                                        @if($note['user_id'] === auth()->id())
                                            <span role="button" class="text-danger fs-14 ml-auto delete-note-btn" data-note-id="{{ $note['id'] }}">
                                                <i class="las la-trash"></i>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('script')
    <script>
        $('#add-note-btn').on('click', function() {
            const noteText = $('#note').val().trim();
            if (noteText === '') {
                AIZ.plugins.notify('danger', 'Note cannot be empty.');
                $('#note').focus();
                return;
            }

            $('#add-note-btn').prop('disabled', true);
            $.ajax({
                url: '{{ route('job_applications.add_note', $application->id) }}',
                method: 'POST',
                data: { note: noteText },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        const note = response.note;
                        const noteHtml = `
                            <div class="mb-2 p-2 border rounded" id="${note.id}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-weight-bold text-muted">${note.user_name || 'Unknown'}</span>
                                    <small class="text-muted">${note.created_at}</small>
                                </div>
                                <div class="d-flex">
                                    <p class="mb-0" style="white-space: pre-wrap;">${note.text}</p>
                                    <span role="button" class="text-danger fs-14 ml-auto delete-note-btn" data-note-id="${note.id}">
                                        <i class="las la-trash"></i>
                                    </span>
                                </div>
                            </div>
                        `;
                        $('#notes-list').fadeIn().prepend(noteHtml);
                        AIZ.plugins.notify('success', 'Note added successfully.');
                        $('#note').val('');
                    }
                }, error: function() {
                    AIZ.plugins.notify('danger', 'Failed to add note. Please try again.');
                },
                complete: function() {
                    $('#add-note-btn').prop('disabled', false);
                }
            });
        });

        $('#notes-list').on('click', '.delete-note-btn', function() {
            const noteId = $(this).data('note-id');
            const noteElement = $(`#${noteId}`);

            if (!confirm('Are you sure you want to delete this note?')) {
                return;
            }

            $.ajax({
                url: "{{ route('job_applications.delete_note', $application->id) }}",
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: { note_id: noteId },
                success: function(response) {
                    if (response.success) {
                        noteElement.fadeOut(300, function() {
                            $(this).remove();
                        });
                        AIZ.plugins.notify('success', 'Note deleted successfully.');
                    }
                }, error: function() {
                    AIZ.plugins.notify('danger', 'Failed to delete note. Please try again.');
                }
            });
        });

    </script>
@endsection
