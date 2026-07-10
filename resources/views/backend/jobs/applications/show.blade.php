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
    $scoreColor = match (true) {
        $application->matching_score >= 80 => 'success',
        $application->matching_score >= 50 => 'warning',
        default => 'danger',
    };

    $appointmentLetterBody = <<<EOT
Dear __applicant_name__,

We are pleased to offer you the position of __job_title__. Your skills and experience impressed us, and we believe you will be a great addition to our team.

Please find the details of your appointment attached.

Welcome aboard!
— HR Team, __app_name__
EOT;
    $appointmentLetterBody = str_replace(
        ['__applicant_name__', '__job_title__', '__app_name__'],
        [ucwords($application->applicant_name), $application->job->role ?: $application->job->title ?: 'N/A', config('app.name')],
        $appointmentLetterBody
    );

    $roles = Cache::remember('roles_list', now()->addDay(), function() {
        return \App\Models\Role::pluck('name', 'id')->toArray();
    });
@endphp
@section('content')
    @include('backend.jobs.applications.partials.style')
    <div class="container px-3">
        <div class="d-flex align-items-center justify-content-between bg-white py-4 rounded-lg px-4">
            <div>
                <div class="brand fs-18 font-weight-bold"><i class="las la-briefcase mr-2"></i>{{ config('app.name') }} Careers</div>
                <div class="crumbs">
                    <a href="{{ route('job_posts.index') }}" class="text-muted">Jobs</a> &nbsp;/&nbsp;
                    <span role="button" class="text-muted">{{ $application->job->role ?: $application->job->title ?: 'N/A' }}</span> &nbsp;/&nbsp;
                    <span class="text-dark">Applicant #{{ $application->id }}</span>
                </div>
            </div>
            <div>
                <button class="btn-ghost" onclick="window.location.href='{{ route('job_applications.index') }}'"><i class="las la-arrow-left mr-1"></i> Back to Applicants</button>
            </div>
        </div>
    </div>

    <main class="container py-4">
        <div class="row">
            <div class="col-lg-8">
                <section class="card-soft">
                    <div class="card-body-p">
                        <div class="profile-head">
                            @php
                                $initials = collect(explode(' ', $application->applicant_name ?? ''))
                                    ->filter() // remove empty parts
                                    ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                                    ->take(2)
                                    ->implode('');
                            @endphp
                            <div class="job-application-avatar">{{ $initials }}</div>
                            <div class="profile-info">
                                <h1 class="name">
                                    {{ $application->applicant_name ?? 'N/A' }}
                                    <span role="button" id="editNameBtn" class="text-success" data-open-modal="editModal">
                                        <i class="las la-edit"></i>
                                    </span>
                                </h1>
                                <div class="sub mt-1">
                                    Applied for <strong style="color:#0f172a">{{ $application->job->role ?: $application->job->title ?: 'N/A' }}</strong>
                                </div>
                                <div class="meta-row">
                                    <span class="meta-pill"><i class="las la-phone"></i> {{ $application->applicant_phone ?? 'No phone provided' }}</span>
                                    <span class="meta-pill"><i class="las la-envelope"></i> {{ $application->applicant_email ?? 'No email provided' }}</span>
                                    <span class="meta-pill"><i class="las la-clock"></i> Applied {{ $application->created_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                            <div class="score-wrap">
                                <div class="score-ring" style="--val:{{ $application->matching_score ?? 0 }}"><span>{{ $application->matching_score ?? 0 }}%</span></div>
                                <div class="sub mt-1">Match score</div>
                            </div>
                        </div>

                        <div class="tag-row">
                            <span class="badge-soft badge-{{ $application->status->value }}">
                                <i class="las la-check-circle"></i> {{ $application->status->label() }}
                            </span>
                            <span id="shortlist-status" class="badge-soft badge-{{ $application->shortlisted ? 'shortlisted' : 'not-shortlisted' }}">
                                <i class="las la-star"></i> {{ $application->shortlisted ? 'Shortlisted' : 'Not Shortlisted' }}
                            </span>
                            <span class="badge-soft badge-neutral">
                                <i class="las la-id-badge"></i> Application ID #{{ $application->id }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Status -->
                <section class="card-soft">
                    <div class="card-head">
                        <span><i class="las la-bolt mr-2" style="color:#d97706"></i> Application Status</span>
                        <span class="muted">Click a button to update</span>
                    </div>
                    <div class="card-body-p">
                        <div class="status-grid">
                            <button class="s-btn s-pending {{ $application->status->value === 'pending' ? 'active' : '' }}" data-status="pending" data-label="Pending">
                                <i class="las la-clock"></i> Pending
                            </button>
                            <button class="s-btn s-confirmed {{ $application->status->value === 'confirmed' ? 'active' : '' }}" data-status="confirmed" data-label="Confirmed">
                                <i class="las la-check"></i> Confirmed
                            </button>
                            <button class="s-btn s-hired {{ $application->status->value === 'hired' ? 'active' : '' }}" data-status="hired" data-label="Hired">
                                <i class="las la-trophy"></i> Hired
                            </button>
                            <button class="s-btn s-rejected {{ $application->status->value === 'rejected' ? 'active' : '' }}" data-status="rejected" data-label="Rejected">
                                <i class="las la-times"></i> Rejected
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Application Answers -->
                <section class="card-soft">
                    <div class="card-head">
                        <span><i class="las la-th-list mr-2"></i> Application Answers</span>
                        <span class="muted">
                            {{ count($jobFieldSnapshot) . Str::plural(' Answer', count($jobFieldSnapshot)) }}
                        </span>
                    </div>
                    <div class="card-body-p">
                        @forelse ($jobFieldSnapshot as $identifier => $field)
                            @php
                                $fieldValue = data_get($jobSubmittedValues, $identifier);
                            @endphp
                            <div class="qa-item">
                                <div class="d-flex justify-content-between align-items-start" style="gap:12px;flex-wrap:wrap">
                                    <div>
                                        <div class="qa-label">
                                            {{ data_get($field, 'label', 'N/A') }}
                                            @if(data_get($field, 'is_required', false))
                                                <span style="text-danger">*</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="qa-meta">Type: {{ ucfirst(data_get($field, 'type')) }} • Position 1</div>
                                </div>
                                @if (data_get($field, 'type') === 'file' && $fieldValue)
                                    @php
                                        $fieldAttachmentUrl = $resolveAttachmentUrl($fieldValue);
                                    @endphp
                                    @if ($fieldAttachmentUrl)
                                        <a class="btn btn-soft-success w-100 font-weight-bold my-2" href="{{ $fieldAttachmentUrl }}" target="_blank">
                                            <i class="las la-file-alt mr-2"></i>View Attachment
                                        </a>
                                    @else
                                        <button class="btn btn-soft-danger w-100 font-weight-bold my-2" type="button">
                                            <i class="las la-exclamation-triangle mr-2"></i>Attachment Unavailable
                                        </button>
                                    @endif
                                @elseif (filled($fieldValue))
                                    <div class="my-2">
                                        {{ is_array($fieldValue) ? json_encode($fieldValue) : $fieldValue }}
                                    </div>
                                @else
                                    <div class="my-2 text-center text-muted font-weight-bold">
                                        No Answers Provided.
                                    </div>
                                @endif
                                <div class="expected-row">
                                    <span class="qa-meta">Expected:</span>
                                    @php
                                        $expectedValues = explode('|', data_get($field, 'expected_value', ''));
                                    @endphp
                                    @forelse ($expectedValues as $expected)
                                        <span class="expected-chip">{{ trim($expected) }}</span>
                                    @empty
                                        <span class="text-muted">Not Specified</span>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <div class="my-2 text-center text-muted font-weight-bold">
                                No Answers Provided.
                            </div>
                        @endforelse
                    </div>
                </section>

                <!-- Notes -->
                <section class="card-soft">
                    <div class="card-head">
                        <span><i class="lar la-sticky-note mr-2"></i> Internal Notes</span>
                        <span class="muted" id="noteCount">{{ count($application->notes ?? []) }} {{ Str::plural('Note', count($application->notes ?? [])) }}</span>
                    </div>
                    <div class="card-body-p">
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
                        <div id="noteList">
                            @foreach ($notes as $note)
                                @include('backend.jobs.applications.partials.note-item', ['note' => $note])
                            @endforeach
                        </div>

                        <div class="note-form mt-4">
                            <span class="section-title">Add a note</span>
                            <textarea id="noteInput" placeholder="Write something for your team..."></textarea>
                            <div class="d-flex justify-content-end mt-2">
                                <button id="add-note-btn" class="btn-dark-soft" type="button">
                                    <i class="las la-plus"></i> Add Note
                                </button>
                            </div>
                        </div>

                    </div>
                </section>

                <section class="card-soft">
                    <div class="card-head">
                        <span><i class="las la-calendar-alt mr-2"></i> Events</span>
                        <span class="muted" id="eventCount">{{ count($application->logs ?? []) }} {{ Str::plural('Event', count($application->logs ?? [])) }}</span>
                    </div>
                    <div class="card-body-p">
                        @php
                            $logs = collect($application->logs ?? [])->map(function($log) {
                                return [
                                    'type' => data_get($log, 'type'),
                                    'message' => data_get($log, 'message'),
                                    'user_name' => data_get($log, 'user_name'),
                                    'created_at' => parseDate(data_get($log, 'created_at', '')),
                                ];
                            })
                            ->sortByDesc('created_at')
                            ->values()
                            ->all();
                        @endphp
                        <div id="eventList">
                            @forelse ($logs as $log)
                                @include('backend.jobs.applications.partials.log-item', ['log' => $log])
                            @empty
                                <div class="my-2 text-center text-muted font-weight-bold">
                                    No events recorded.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

            </div>

            <!-- RIGHT SIDEBAR -->
            <aside class="col-lg-4">

                <section class="card-soft">
                    <div class="card-head">
                        <span><i class="las la-magic mr-2"></i> Actions</span>
                    </div>
                    <div class="card-body-p">
                        @if($application->status->value === 'hired')
                            @if($application->staff_id)
                                {{-- <button class="action-btn warning" data-open-modal="apptModal" type="button">
                                    <span class="ico"><i class="las la-file-signature"></i></span>
                                    <span class="label-wrap">
                                        <span>Send Joining Letter</span>
                                        <span class="label-sub">Permanent Staff</span>
                                    </span>
                                </button>
                                <button class="action-btn success" data-open-modal="apptModal" type="button">
                                    <span class="ico"><i class="las la-file-signature"></i></span>
                                    <span class="label-wrap">
                                        <span>Send Appointment Letter</span>
                                        <span class="label-sub">Hired Candidates & Staff</span>
                                    </span>
                                </button> --}}
                                <button class="action-btn accent" onclick="window.location.href='{{ route('staffs.show', encrypt($application->staff_id)) }}#pane-actions'" type="button">
                                    <span class="ico"><i class="las la-user"></i></span>
                                    <span class="label-wrap">
                                        <span>View Staff Profile</span>
                                        <span class="label-sub">Staff Record</span>
                                    </span>
                                </button>
                            @else
                                <button class="action-btn accent" data-open-modal="staffModal" type="button">
                                    <span class="ico"><i class="las la-user-plus"></i></span>
                                    <span class="label-wrap">
                                        <span>Create Staff Record</span>
                                        <span class="label-sub">Hired Candidates</span>
                                    </span>
                                </button>
                            @endif
                        @endif

                        @if($application->status->value !== 'rejected')
                            <button class="action-btn primary" data-open-modal="smsModal" type="button">
                                <span class="ico"><i class="las la-sms"></i></span>
                                <span class="label-wrap">
                                    <span>Send Interview Invitation</span>
                                    <span class="label-sub">Schedule Interview</span>
                                </span>
                            </button>
                        @endif

                        <button class="action-btn" type="button" id="shortlist-btn">
                            <span class="ico"><i class="las la-star"></i></span>
                            <span class="label-wrap">
                                <span class="btn-title">{{ $application->shortlisted ? 'Remove From Shortlist' : 'Add To Shortlisted' }}</span>
                                <span class="label-sub btn-sub-title">{{ $application->shortlisted ? 'Shortlisted' : 'Not Shortlisted' }}</span>
                            </span>
                        </button>
                    </div>
                </section>

                <section class="card-soft">
                    <div class="card-head">
                        <span><i class="las la-briefcase mr-2"></i> Job Summary</span>
                    </div>
                    <div class="card-body-p">
                        <h6 class="job-title">{{ $application->job->role ?: $application->job->title ?: 'N/A' }}</h6>
                        <div class="job-sub">Job ID #{{ $application->job->id }} · {{ ucfirst($application->job->status) }}</div>

                        <ul class="job-meta">
                            @if($application->job->location)
                            <li><span class="lbl"><i class="las la-map-marker"></i> Location</span><span
                                    class="val">{{ $application->job->location }}</span></li>
                            @endif
                            <li><span class="lbl"><i class="las la-clock"></i> Type</span><span
                                    class="val text-capitalize">{{ str_replace('_',' ',$application->job->applicant_type ?? 'full_time') }}</span></li>
                            <li><span class="lbl"><i class="las la-user-tie"></i> Experience</span><span
                                    class="val">{{ $application->job->experience ?: 'Not specified' }}</span></li>
                            <li>
                                <span class="lbl"><i class="las la-coins"></i> Salary</span>
                                <span class="val">
                                    @if($application->job->salary_min && $application->job->salary_max)
                                        {{ number_format($application->job->salary_min) }} - {{ number_format($application->job->salary_max) }} BDT
                                    @elseif($application->job->salary_min)
                                        From {{ number_format($application->job->salary_min) }} BDT
                                    @elseif($application->job->salary_max)
                                        Up to {{ number_format($application->job->salary_max) }} BDT
                                    @else
                                        Not specified
                                    @endif
                                </span>
                            </li>
                            <li><span class="lbl"><i class="las la-users"></i> Vacancy</span><span
                                    class="val">{{ $application->job->vacancy ?: 'Not specified' }}</span></li>
                            <li><span class="lbl"><i class="las la-calendar"></i> Deadline</span><span
                                    class="val">{{ $application->job->deadline ? \Carbon\Carbon::parse($application->job->deadline)->format('M j, Y') : 'Not specified' }}</span></li>
                        </ul>

                        <button class="btn-block-soft" onclick="window.open('{{ route('job_posts.show', $application->job->slug) }}', '_blank')" type="button">
                            View Full Description
                        </button>
                    </div>
                </section>

                <section class="card-soft">
                    <div class="card-head">
                        <span><i class="las la-stream mr-2"></i> Activity</span>
                    </div>
                    <div class="card-body-p">
                        <div class="tl-item">
                            <div class="tl-icon info"><i class="lab la-telegram-plane"></i></div>
                            <div>
                                <div class="tl-title">Application submitted</div>
                                <div class="tl-time">{{ optional($application->created_at)->format('d M Y, h:i A') ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="tl-item">
                            <div class="tl-icon success"><i class="las la-check-circle"></i></div>
                            <div>
                                <div class="tl-title">Last Updated</div>
                                <div class="tl-time">{{ optional($application->updated_at)->format('d M Y, h:i A') ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </section>

            </aside>

        </div>
    </main>

    <!-- ===== Modals ===== -->

    <!-- Edit Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-card">
            <div class="m-head">
                <h3><i class="las la-edit"></i> Edit Applicant Info</h3>
                <button class="m-close" data-close-modal>&times;</button>
            </div>
            <div class="m-body">
                <div class="form-group-c">
                    <label>Name</label>
                    <input class="input-c" value="{{ $application->applicant_name }}"id="applicantNameInput" required>
                </div>
                <div class="form-group-c">
                    <label>Phone</label>
                    <input class="input-c" value="{{ $application->applicant_phone }}"id="applicantPhoneInput" required>
                </div>
                <div class="form-group-c">
                    <label>Email</label>
                    <input class="input-c" value="{{ $application->applicant_email }}"id="applicantEmailInput" required>
                </div>
            </div>
            <div class="m-foot">
                <button class="btn-light-bordered" data-close-modal>Cancel</button>
                <button class="btn-success-solid" id="updateBtn"><i class="las la-paper-plane"></i> Update Info</button>
            </div>
        </div>
    </div>

    <!-- SMS Modal -->
    <div class="modal-overlay" id="smsModal">
        <div class="modal-card">
            <div class="m-head">
                <h3><i class="las la-comment-sms"></i> Send Interview Invitation</h3>
                <button class="m-close" data-close-modal>&times;</button>
            </div>
            <div class="m-body">
                <div class="form-group-c">
                    <label>Recipient</label>
                    <input class="input-c" value="{{ $application->applicant_name }} . {{ $application->applicant_phone }}" readonly>
                    <input type="hidden" value="{{ $application->applicant_phone }}" readonly>
                </div>
                <div class="row-2">
                    <div class="form-group-c">
                        <label>Date <span class="text-danger">*</span></label>
                        <input type="date" class="input-c" id="interviewDate" min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group-c">
                        <label>Time <span class="text-danger">*</span></label>
                        <input type="time" class="input-c" id="interviewTime">
                    </div>
                </div>
                @if($application->applicant_email)
                    <label class="checkbox-c">
                        <input id="sendEmailCheckbox" type="checkbox" checked> Send email instead of SMS (if checked, A mail will be sent to the applicant's email address)
                    </label>
                @else
                    <input style="display: none;" id="sendEmailCheckbox" type="checkbox">
                @endif
            </div>
            <div class="m-foot">
                <button class="btn-light-bordered" data-close-modal>Cancel</button>
                <button class="btn-dark-soft" id="smsSendBtn"><i class="las la-paper-plane"></i> Send Invitation</button>
            </div>
        </div>
    </div>

    <!-- Appointment Modal -->
    <div class="modal-overlay" id="staffModal">
        <div class="modal-card lg">
            <div class="m-head">
                <h3><i class="las la-user-plus"></i> Create Staff Record</h3>
                <button class="m-close" data-close-modal>&times;</button>
            </div>
            <div class="m-body">
                <div class="row-2">
                    <div class="form-group-c">
                        <label>Name <span class="text-danger">*</span></label>
                        <input class="input-c" id="staffName" value="{{ $application->applicant_name }}">
                    </div>
                    <div class="form-group-c">
                        <label>Phone <span class="text-danger">*</span></label>
                        <input class="input-c" id="staffPhone" value="{{ $application->applicant_phone }}" placeholder="Enter the applicant's phone number">
                    </div>
                </div>

                <div class="row-2">
                    <div class="form-group-c">
                        <label>Email (Personal)</label>
                        <input class="input-c" id="staffPersonalEmail" value="{{ $application->applicant_email }}" placeholder="Enter the applicant's personal email address">
                    </div>
                    <div class="form-group-c">
                        <label>Email (Official) <span class="text-danger">*</span></label>
                        <input class="input-c" id="staffEmail" value="" placeholder="Enter an unique official email address">
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-group-c">
                        <label>Password <span class="text-danger">*</span></label>
                        <input class="input-c" id="staffPassword" value="{{ Str::password(10) }}" placeholder="Enter a secure password">
                    </div>
                    <div class="form-group-c">
                        <label>Salary <span class="text-danger">*</span></label>
                        <input type="number" min="0" step="1" class="input-c" id="staffSalary" value="0" placeholder="Enter the offered salary">
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-group-c">
                        <label>Role <span class="text-danger">*</span></label>
                        <select class="input-c" id="staffRole">
                            <option value="">Select Role</option>
                            @foreach ($roles as $id => $name)
                                <option value="{{ $id }}" @selected($application->job->role === trim($name))>
                                    {{ ucwords($name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group-c">
                        <label>Shift <span class="text-danger">*</span></label>
                        <select class="input-c" id="staffShift">
                            <option value="">Select Shift</option>
                            @foreach (\App\Enums\ShiftEnum::options() as $value => $label)
                                <option value="{{ $value }}" @selected($loop->first)>{{ ucwords($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="m-foot">
                <button class="btn-light-bordered" data-close-modal>Cancel</button>
                <button class="btn-success-solid" id="createStaffBtn">
                    <i class="las la-paper-plane mr-2"></i>Create Staff Record
                </button>
            </div>
        </div>
    </div>

    <!-- Appointment Modal -->
    <div class="modal-overlay" id="apptModal">
        <div class="modal-card lg">
            <div class="m-head">
                <h3><i class="las la-file-signature"></i> Send Appointment Letter</h3>
                <button class="m-close" data-close-modal>&times;</button>
            </div>
            <div class="m-body">
                <div class="form-group-c">
                    <label>Position</label>
                    <input class="input-c" value="{{ $application->job->role ?: $application->job->title ?: 'N/A' }}" readonly disabled>
                </div>
                <div class="row-2">
                    <div class="form-group-c">
                        <label>Joining Date <span class="text-danger">*</span></label>
                        <input type="date" class="input-c" id="joiningDate" min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group-c">
                        <label>Joining Time <span class="text-danger">*</span></label>
                        <input type="time" class="input-c" id="joiningTime">
                    </div>
                </div>
            </div>
            <div class="m-foot">
                <button class="btn-light-bordered" data-close-modal>Cancel</button>
                <button class="btn-success-solid" id="apptSendBtn">
                    <i class="las la-paper-plane mr-2"></i>Send Appointment Letter
                </button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('backend.jobs.applications.partials.script')
    <script>
        $(document).ready(function() {
            $('#add-note-btn').on('click', function() {
                const $btn = $(this);
                const noteText = $('#noteInput').val().trim();
                if (noteText === '') {
                    AIZ.plugins.notify('danger', 'Note cannot be empty.');
                    $('#noteInput').focus();
                    return;
                }

                $btn.prop('disabled', true);
                $.ajax({
                    url: '{{ route('job_applications.add_note', $application->id) }}',
                    method: 'POST',
                    data: { note: noteText },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#noteList').fadeIn().prepend(response.note_view);
                            $('#noteCount').text(response.note_count);
                            AIZ.plugins.notify('success', 'Note added successfully.');
                            $('#noteInput').val('');
                        }
                    }, error: function() {
                        AIZ.plugins.notify('danger', 'Failed to add note. Please try again.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });

            $('#noteList').on('click', '.delete-note-btn', function() {
                const noteId = $(this).data('note-id');
                const noteElement = $(`#${noteId}`);

                Swal.fire({
                    title: 'Are You Sure?',
                    text: "This note will be permanently deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, Delete It!'
                }).then((result) => {
                    if (result.isConfirmed) {
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
                                    $('#noteCount').text(response.note_count);
                                }
                            }, error: function() {
                                AIZ.plugins.notify('danger', 'Failed to delete note. Please try again.');
                            }
                        });
                    }
                });
            });

            $('#shortlist-btn').on('click', function() {
                const $btn = $(this);
                $btn.prop('disabled', true);
                $.ajax({
                    url: '{{ route('job_applications.update_shortlist', $application->id) }}',
                    method: 'PATCH',
                    success: function(response) {
                        if (response.success) {
                            const isShortlisted = response.shortlisted;
                            $btn.find('.btn-title').text(isShortlisted ? 'Remove From Shortlist' : 'Add To Shortlist');
                            $btn.find('.btn-sub-title').text(isShortlisted ? 'Shortlisted' : 'Not Shortlisted');

                            const statusBadge = $('#shortlist-status');
                            statusBadge.toggleClass('badge-shortlisted', isShortlisted);
                            statusBadge.toggleClass('badge-not-shortlisted', !isShortlisted);
                            statusBadge.html(`<i class="las la-star"></i> ${isShortlisted ? 'Shortlisted' : 'Not Shortlisted'}`);
                            AIZ.plugins.notify('success', isShortlisted ? 'Added to shortlist.' : 'Removed from shortlist.');
                        }
                    },
                    error: function() {
                        AIZ.plugins.notify('error', 'An error occurred while updating the shortlist status.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false); // Re-enable button after request completes
                    }
                });
            });

            $('.s-btn').on('click', function() {
                const $btn = $(this);
                const status = $btn.data('status');
                const activeStatus = $('.s-btn.active').data('status');
                if (status === activeStatus) return; // No change
                const url = "{{ route('job_applications.update_status', $application->id) }}";
                $btn.prop('disabled', true);
                $.ajax({
                    url: url,
                    method: 'PATCH',
                    data: { status: status },
                    success: function(response) {
                        if (response.success) {
                            $('.s-btn').removeClass('active');
                            $btn.addClass('active');
                            AIZ.plugins.notify('success', `Status updated to ${$btn.data('label')}.`);
                            // Update main status badge
                            const mainBadge = $('.tag-row .badge-soft').first();
                            mainBadge.removeClass('badge-pending badge-confirmed badge-hired badge-rejected')
                                .addClass(`badge-${status}`)
                                .html(`<i class="las la-check-circle"></i> ${$btn.data('label')}`);

                            window.location.reload();
                        }
                    },
                    error: function() {
                        AIZ.plugins.notify('error', 'An error occurred while updating the status.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false); // Re-enable button after request completes
                    }
                });
            });

            $('#smsSendBtn').on('click', function() {
                const date = $('#interviewDate').val();
                const time = $('#interviewTime').val();
                const sendEmail = $('#sendEmailCheckbox').is(':checked') ? 1 : 0;
                const type = "interview";

                if (!date || !time) {
                    !date ? $('#interviewDate').focus() : $('#interviewTime').focus();
                    AIZ.plugins.notify('danger', 'Please select both date and time for the interview.');
                    return;
                }

                $(this).prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Sending...');
                $.ajax({
                    url: '{{ route('job_applications.send_sms', $application->id) }}',
                    method: 'POST',
                    data: { date, time, sendEmail },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            AIZ.plugins.notify('success', 'Interview invitation sent successfully.');
                            $('#smsModal [data-close-modal]').click();
                            if (response.log_view) {
                                $('#eventList').fadeIn().prepend(response.log_view);
                                $('#eventCount').text(response.log_count);
                            }
                        }
                    }, error: function(xhr, status, error) {
                        const errorMessage = xhr.responseJSON?.message || 'Failed to send interview invitation. Please try again.';
                        AIZ.plugins.notify('danger', errorMessage);
                    },
                    complete: function() {
                        $('#smsSendBtn').prop('disabled', false).html('<i class="las la-paper-plane"></i> Send Invitation');
                    }
                });
            });

            $('#apptSendBtn').on('click', function() {
                const date = $('#joiningDate').val();
                const time = $('#joiningTime').val();
                const type = "appointment";

                if (!date || !time) {
                    !date ? $('#joiningDate').focus() : $('#joiningTime').focus();
                    AIZ.plugins.notify('danger', 'Please select both date and time for the joining.');
                    return;
                }

                $(this).prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Sending...');
                $.ajax({
                    url: '{{ route('job_applications.send_sms', $application->id) }}',
                    method: 'POST',
                    data: { date, time, type },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            AIZ.plugins.notify('success', 'Appointment letter sent successfully.');
                            $('#apptModal [data-close-modal]').click();
                            if (response.log_view) {
                                $('#eventList').fadeIn().prepend(response.log_view);
                                $('#eventCount').text(response.log_count);
                            }
                        }
                    }, error: function(xhr, status, error) {
                        const errorMessage = xhr.responseJSON?.message || 'Failed to send appointment letter. Please try again.';
                        AIZ.plugins.notify('danger', errorMessage);
                    },
                    complete: function() {
                        $('#apptSendBtn').prop('disabled', false).html('<i class="las la-paper-plane"></i> Send Appointment Letter');
                    }
                });
            });

            $('#updateBtn').on('click', function() {
                const name = $('#applicantNameInput').val().trim();
                const phone = $('#applicantPhoneInput').val().trim();
                const email = $('#applicantEmailInput').val().trim();

                if (name === '' || phone === '' || email === '') {
                    AIZ.plugins.notify('danger', 'All fields are required.');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Updating...');

                $.ajax({
                    url: '{{ route('job_applications.update_info', $application->id) }}',
                    method: 'POST',
                    data: { name, phone, email },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            AIZ.plugins.notify('success', response.message || 'Applicant information updated successfully.');
                            $('#editModal [data-close-modal]').click();
                            location.reload();
                        }
                    }, error: function(xhr, status, error) {
                        const errorMessage = xhr.responseJSON?.message || 'Failed to update information. Please try again.';
                        AIZ.plugins.notify('danger', errorMessage);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="las la-paper-plane"></i> Update Info');
                    }
                });
            });

            $('#createStaffBtn').on('click', function() {
                const name = $('#staffName').val().trim();
                const email = $('#staffEmail').val().trim();
                const personal_email = $('#staffPersonalEmail').val().trim();
                const phone = $('#staffPhone').val().trim();
                const password = $('#staffPassword').val().trim();
                const roleId = $('#staffRole').val();
                const shift = $('#staffShift').val();
                const salary = $('#staffSalary').val().trim();

                if (!name || !email || !phone || !password || !roleId || !shift || !salary) {
                    AIZ.plugins.notify('danger', 'Please fill in all required fields.');
                    return;
                }

                if (isNaN(salary) || Number(salary) < 0) {
                    AIZ.plugins.notify('danger', 'Please enter a valid non-negative number for salary.');
                    $('#staffSalary').focus();
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Creating...');

                $.ajax({
                    url: '{{ route('staffs.store') }}',
                    method: 'POST',
                    data: { name, email, personal_email, phone, salary, password, role_id: roleId, shift, job_application_id: {{ $application->id }} },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            AIZ.plugins.notify('success', response.message || 'Staff record created successfully.');
                            $('#staffModal [data-close-modal]').click();
                            location.reload();
                        }
                    }, error: function(xhr, status, error) {
                        const errorMessage = xhr.responseJSON?.message || 'Failed to create staff record. Please try again.';
                        AIZ.plugins.notify('danger', errorMessage);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="las la-paper-plane mr-2"></i>Create Staff Record');
                    }
                });
            });
        });
    </script>
@endsection
