@extends('backend.layouts.app')

@section('content')
	@php
		$leave = $application->applicable instanceof \App\Models\Leave ? $application->applicable : null;
		$leaveStart = $leave?->start_date;
		$leaveEnd = $leave?->end_date ?? $leave?->start_date;
		$leaveType = ($leaveStart && $leaveEnd && !$leaveStart->equalTo($leaveEnd)) ? 'multiple' : 'single';
        $dateBreakdowns = $leave?->date_breakdowns ?? [];
        $leaveDuration = $leave?->duration ?? 0;

        $approvedStart = $leave?->approved_start_date;
        $approvedEnd = $leave?->approved_end_date ?? $leave?->approved_start_date;
        $approvedLeaveType = ($approvedStart && $approvedEnd && !$approvedStart->equalTo($approvedEnd)) ? 'multiple' : 'single';

		$durationLabel = null;
		if ($leaveStart) {
			$durationLabel = $leaveType === 'single'
				? $leaveStart->format('d M Y')
				: $leaveStart->format('d M Y') . ' - ' . ($leaveEnd ?? $leaveStart)->format('d M Y');
		}

		$approvedDurationLabel = null;
		if ($approvedStart) {
			$approvedDurationLabel = $approvedLeaveType === 'single'
				? $approvedStart->format('d M Y')
				: $approvedStart->format('d M Y') . ' - ' . ($approvedEnd ?? $approvedStart)->format('d M Y');
		}

		$statusColor = [
			'approved' => 'success',
			'rejected' => 'danger',
			'pending' => 'warning',
			'cancelled' => 'dark',
		][$application->status] ?? 'secondary';

		$attachmentIds = $application->attachments ?? [];
	@endphp

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
            <a href="{{ route('applications.index') }}" class="btn btn-sm btn-light mr-2">
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
						<div class="info-label mb-1">Employee</div>
						<div class="info-value font-weight-bold">{{ $application->user?->name ?? 'N/A' }}</div>
						<small class="text-muted d-block mt-1">{{ $application->user?->email ?? '—' }}</small>
						<small class="text-muted d-block">{{ $application->user?->phone ?? '—' }}</small>
					</div>
                </div>
				<div>
					<span class="badge badge-inline badge-primary badge-pill text-uppercase mb-2">
                        {{ $application->type->label() }}
                    </span>
                    <span class="badge badge-inline badge-pill badge-{{ $statusColor }} mt-1">
                        {{ ucfirst($application->status) }}
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
                            {{ $application->subject ?: 'Untitled Application' }}
                        </p>
					</div>

					<div class="application-detail-card mb-3">
						<div class="info-label mb-1">Reason</div>
						<p class="mb-0 info-value">
                            {{ $application->content ?: 'No reason provided' }}
                        </p>
					</div>

                    @if ($application->type->value === 'leave' && $durationLabel)
						<div class="application-detail-card mb-3">
                            <div class="info-label mb-2">Duration</div>
                            <div class="d-flex justify-content-between flex-md-row flex-column mb-3">
                                <div class="card mb-2 mb-md-0 mr-2 p-2 w-100">
                                    <div class="d-flex justify-content-between flex-md-row flex-column">
                                        <div>
                                            <span class="d-block font-weight-bold text-muted mb-1">
                                                Requested
                                            </span>
                                            <span class="font-weight-bold">
                                                {{ $durationLabel }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="badge badge-inline badge-info mt-1">
                                                {{ $leaveType === 'multiple' ? 'Multiple Days' : 'Single Day' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @if($approvedStart)
                                    <div class="card mb-2 mb-md-0 mr-2 p-2 w-100">
                                        <div class="d-flex justify-content-between flex-md-row flex-column">
                                            <div>
                                                <span class="d-block font-weight-bold mb-1 text-muted">
                                                    Approved
                                                </span>
                                                <span class="font-weight-bold">
                                                    {{ $approvedDurationLabel }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="badge badge-inline badge-info mt-1">
                                                    {{ $approvedLeaveType === 'multiple' ? 'Multiple Days' : 'Single Day' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="info-label mb-2">Dates Breakdown</div>
                            <div class="d-flex justify-content-between flex-md-row flex-column mb-3">
                                @foreach ($dateBreakdowns as $label => $dates)
                                    @continue(count($dates) === 0)
                                    <div class="card mb-2 mb-md-0 mr-2 p-2 w-100">
                                        <span class="d-block font-weight-bold mb-1 text-muted">
                                            {{ ucwords(str_replace('_', ' ', $label)) }}
                                        </span>
                                        <span class="font-weight-bold">
                                            {{ implode(', ', $dates) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            @if($application->status === 'approved')
                                <div class="info-label mb-2">Leaves Breakdown</div>
                                <div class="d-flex justify-content-between flex-md-row flex-column mb-3">
                                    <div class="card mb-2 mb-md-0 mr-2 p-2 w-100">
                                        <span class="d-block font-weight-bold mb-1 text-muted">
                                            Paid Leaves
                                        </span>
                                        <span class="font-weight-bold">
                                            {{ $leave->paid_days }} Day(s)
                                        </span>
                                    </div>
                                    <div class="card mb-0 mr-2 p-2 w-100">
                                        <span class="d-block font-weight-bold mb-1 text-muted">
                                            Unpaid Leaves
                                        </span>
                                        <span class="font-weight-bold">
                                            {{ $leave->unpaid_days }} Day(s)
                                        </span>
                                    </div>
                                </div>
                            @endif
						</div>
					@endif

					@if ($application->note)
						<div class="application-detail-card mb-3">
							<div class="info-label mb-1">Note</div>
							<p class="mb-0 info-value" style="white-space: pre-wrap;">{{ $application->note }}</p>
						</div>
					@endif

					@if ($application->user_id !== auth()->id() && $application->type->value === 'leave' && $leave && $leaveStart && $application->status !== 'approved' && (auth()->user()?->user_type === 'admin' || in_array('manage_applications', $_authPermissions)))
						<div class="application-detail-card mb-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <div class="info-label mb-1">Leave Window</div>
                                @if($leaveDuration > 1)
                                    <div>
                                        <button class="btn btn-sm btn-icon btn-success"
                                            id="edit-dates-btn"
                                            data-action="edit"
                                            data-toggle="tooltip"
                                            data-title="Edit Dates">
                                            <i class="las la-edit"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
							<div class="d-flex flex-wrap align-items-center justify-content-between">
								<div id="leave-window">
									<div class="font-weight-bold">{{ $durationLabel ?? '—' }}</div>
									<small class="text-muted">
                                        <span id="leave-duration">{{ $leaveDuration }}</span> day(s)
                                    </small>
								</div>
                                <div id="date-selector" style="display: none;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="form-group mr-2">
                                            <label for="approved-start-date">Start Date</label>
                                            <input type="date" class="form-control form-control-sm" id="approved-start-date" value="{{ $leaveStart->format('Y-m-d') }}" min="{{ $leaveStart->format('Y-m-d') }}" max="{{ $leaveEnd->format('Y-m-d') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="approved-end-date">End Date</label>
                                            <input type="date" class="form-control form-control-sm" id="approved-end-date" value="{{ $leaveEnd->format('Y-m-d') }}" min="{{ $leaveStart->format('Y-m-d') }}" max="{{ $leaveEnd->format('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <small class="text-danger" id="approved-date-error"></small>
                                </div>
							</div>
						</div>
					@endif

					@if (count($attachmentIds))
						<div class="application-detail-card">
							<div class="info-label mb-2">Attachments</div>
							<div class="row">
								@foreach ($attachmentIds as $index => $attachmentId)
									@php
										$attachmentUrl = uploaded_asset($attachmentId);
									@endphp
									@if ($attachmentUrl)
										<div class="col-sm-6 mb-2">
											<a href="{{ $attachmentUrl }}" target="_blank" class="attachment-card w-100">
												<span class="attachment-icon bg-soft-primary text-primary">
													<i class="las la-paperclip"></i>
												</span>
												<div>
													<div class="info-value mb-0">Attachment - {{ $index + 1 }}</div>
													<small class="text-muted">Open</small>
												</div>
											</a>
										</div>
									@endif
								@endforeach
							</div>
						</div>
					@endif
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
						<div>
                            @php
                                $modifierLabel = match($application->status) {
                                    'approved' => 'Approved By',
                                    'rejected' => 'Rejected By',
                                    default => 'Modified By'
                                }
                            @endphp
							<small class="text-muted d-block">{{ $modifierLabel }}</small>
							<span class="info-value">{{ $application->modifier?->name ?? '—' }}</span>
						</div>
					</div>

                    @if ($application->user_id !== auth()->id() && (auth()->user()?->user_type === 'admin' || in_array('manage_applications', $_authPermissions)))
                        <div class="application-detail-card">
                            <div class="info-label mb-2">Actions</div>
                            @if($application->type->value === 'leave' && $application->status !== 'approved')
                                <div class="mb-2 form-group">
                                    <label for="paid_leave">Paid Leave</label>
                                    <input type="number" class="form-control form-control-sm" min="0" step="1" max="{{ $leave->duration }}" placeholder="Enter the number of paid leave days to approve." value="{{ $leave->paid_days ?? 0 }}"  id="paid_leave">
                                    <small class="form-text text-muted">
                                        Remaining will be considered as unpaid leave.
                                    </small>
                                </div>
                            @endif
                            <div class="mb-2 form-group">
                                <label for="note">Note @include('components.tooltip', [
                                    'title' => 'This will override any existing note'
                                ])</label>
                                <textarea class="form-control form-control-sm" id="note" rows="3" placeholder="Enter any note here"></textarea>
                            </div>
                            <div class="mb-2">
                                @if($application->status === 'pending')
                                    <button class="btn btn-sm btn-success" id="approve-btn">
                                        <i class="las la-check"></i> Approve
                                    </button>
                                    <button class="btn btn-sm btn-danger" id="reject-btn">
                                        <i class="las la-times"></i> Reject
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-success" @if($application->status === 'approved') disabled @else id="approve-btn" @endif>
                                        <i class="las la-check"></i> {{ $application->status === 'approved' ? 'Approved' : 'Approve' }}
                                    </button>
                                    <button class="btn btn-sm btn-danger" @if($application->status === 'rejected') disabled @else id="reject-btn" @endif>
                                        <i class="las la-times"></i> {{ $application->status === 'rejected' ? 'Rejected' : 'Reject' }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
				</div>
			</div>
		</div>
	</div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            @if (!is_null($leave))
                const $editDatesBtn = $('#edit-dates-btn');
                const $dateSelector = $('#date-selector');
                const $leaveWindow = $('#leave-window');
                const leaveStartDate = "{{ $leaveStart->format('Y-m-d') }}";
                const leaveEndDate = "{{ $leaveEnd->format('Y-m-d') }}";
                const approvedStartDateInput = $('#approved-start-date');
                const approvedEndDateInput = $('#approved-end-date');
                const paidLeaveInput = $('#paid_leave');
                const leaveBreakDowns = @json($dateBreakdowns ?? []);
                let maxPaidLeaveDays = {{ $leave->duration ?? 1 }};
                let paidLeaveDays = {{ $leave->paid_days ?? 0 }};

                $editDatesBtn.on('click', function () {
                    let action = $(this).data('action');
                    $('#approved-date-error').text('');
                    approvedStartDateInput.removeClass('is-invalid');
                    approvedEndDateInput.removeClass('is-invalid');
                    if (action === 'edit') {
                        $leaveWindow.hide();
                        $dateSelector.show();
                        $(this).data('action', 'undo');
                        $(this).find('i').removeClass('la-edit').addClass('la-undo');
                        $(this).addClass('btn-danger').removeClass('btn-success');
                        $(this).attr('data-original-title', 'Undo Changes').tooltip('dispose').tooltip();
                    } else if (action === 'undo') {
                        $dateSelector.hide();
                        $leaveWindow.show();
                        $(this).data('action', 'edit');
                        $(this).find('i').removeClass('la-undo').addClass('la-edit');
                        $(this).addClass('btn-success').removeClass('btn-danger');
                        $(this).attr('data-original-title', 'Edit Dates').tooltip('dispose').tooltip();

                        // Set the date values in the date selector
                        approvedStartDateInput.val(leaveStartDate);
                        approvedEndDateInput.val(leaveEndDate);
                        paidLeaveInput.val('{{ $leave->paid_days ?? 0 }}');
                    }
                });

                approvedStartDateInput.on('change', function() {
                    let startDate = $(this).val();
                    if (!startDate) {
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }

                    console.log('Start Date:', startDate);
                    console.log('End Date:', approvedEndDateInput.val());
                    if (!validateApprovedDates()) {
                        $(this).addClass('is-invalid');
                        $('#approved-date-error').text('Invalid date range.');
                    } else {
                        $('#approved-date-error').text('');
                        updatePaidLeaveDays();
                    }
                });

                approvedEndDateInput.on('change', function() {
                    let endDate = $(this).val();
                    if (!endDate) {
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }

                    if (!validateApprovedDates()) {
                        $(this).addClass('is-invalid');
                        $('#approved-date-error').text('Invalid date range.');
                    } else {
                        $('#approved-date-error').text('');
                        updatePaidLeaveDays();
                    }
                });

                approvedStartDateInput.on('focusout', function() {
                    let startDate = $(this).val();
                    if (!startDate) {
                        $(this).val(leaveStartDate);
                        $(this).removeClass('is-invalid');
                        // updatePaidLeaveDays();
                    }
                });

                approvedEndDateInput.on('focusout', function() {
                    let endDate = $(this).val();
                    if (!endDate) {
                        $(this).val(leaveEndDate);
                        $(this).removeClass('is-invalid');
                        // updatePaidLeaveDays();
                    }
                });

                paidLeaveInput.on('input', function() {
                    let paidDays = parseInt($(this).val() || 0);

                    if (paidDays > maxPaidLeaveDays) {
                        $(this).addClass('is-invalid').attr('title', 'Paid leave days cannot exceed the total leave days.');
                    } else {
                        $(this).removeClass('is-invalid').attr('title', '');
                    }
                });

                paidLeaveInput.on('focusout', function() {
                    let paidDays = parseInt($(this).val() || 0);
                    if (paidDays > maxPaidLeaveDays) {
                        $(this).val(paidLeaveDays);
                        $(this).removeClass('is-invalid');
                    }
                });
            @endif

            $(document).on('click', '#reject-btn', function() {
                let note = $('#note').val().trim();
                if (!note) {
                    AIZ.plugins.notify('danger', 'Please provide a reason for rejection.');
                    $('#note').addClass('is-invalid').focus();
                    return;
                }
                const rejectBtn = $(this);
                rejectBtn.prop('disabled', true);
                $.ajax({
                    url: '{{ route("applications.manage", $application->id) }}',
                    method: 'PUT',
                    data: {
                        'note': note,
                        'status': 'rejected'
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            AIZ.plugins.notify('success', 'Application rejected successfully.');
                            window.location.href = '{{ route("applications.index") }}';
                        } else {
                            AIZ.plugins.notify('danger', 'Failed to reject application.');
                            rejectBtn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle error response
                        rejectBtn.prop('disabled', false);
                        AIZ.plugins.notify('danger', 'Failed to reject application.');
                    }
                });
            });

            $(document).on('click', '#approve-btn', function() {
                const data = {
                    note: $('#note').val().trim(),
                    status: 'approved'
                };
                @if (!is_null($leave))
                    data.approved_start_date = approvedStartDateInput.val() || '';
                    data.approved_end_date = approvedEndDateInput.val() || '';
                    data.paid_days = paidLeaveInput.val();

                    if (!validateApprovedDates()) {
                        AIZ.plugins.notify('danger', 'Please select a valid date range.');
                        return;
                    }

                    if (calculatePaidLeaveDays() < data.paid_days) {
                        AIZ.plugins.notify('danger', 'Paid leave days cannot exceed the approved leave days.');
                        return;
                    }
                @endif

                const approveBtn = $(this);
                approveBtn.prop('disabled', true);
                $.ajax({
                    url: '{{ route("applications.manage", $application->id) }}',
                    method: 'PUT',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            AIZ.plugins.notify('success', 'Application approved successfully.');
                            window.location.href = '{{ route("applications.index") }}';
                        } else {
                            AIZ.plugins.notify('danger', 'Failed to approve application.');
                            approveBtn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle error response
                        approveBtn.prop('disabled', false);
                        AIZ.plugins.notify('danger', xhr.responseJSON.message || 'Failed to approve application.');
                    }
                });
            });

            @if (!is_null($leave))
                function validateApprovedDates() {
                    let startDate = approvedStartDateInput.val();
                    let endDate = approvedEndDateInput.val();

                    if (!startDate || !endDate) {
                        return false;
                    }

                    let start = new Date(startDate);
                    let end = new Date(endDate);

                    if (start > end) {
                        return false;
                    }

                    return true;
                }

                function updatePaidLeaveDays() {
                    let paidDays = calculatePaidLeaveDays();
                    paidLeaveInput.val(paidDays);
                    maxPaidLeaveDays = paidDays;
                }

                function formatDate(date) {
                    let d = String(date.getDate()).padStart(2, '0');
                    let m = String(date.getMonth() + 1).padStart(2, '0');
                    let y = date.getFullYear();
                    return `${y}-${m}-${d}`;
                }

                function calculatePaidLeaveDays() {
                    let startDate = approvedStartDateInput.val();
                    let endDate = approvedEndDateInput.val();

                    if (!startDate || !endDate) return 0;

                    let start = new Date(startDate);
                    let end = new Date(endDate);

                    // Handle reverse selection
                    if (start > end) {
                        [start, end] = [end, start];
                    }

                    let holidays = new Set(leaveBreakDowns.holidays || []);
                    let offDays = new Set(leaveBreakDowns.off_days || []);

                    let count = 0;
                    let current = new Date(start);

                    while (current <= end) {
                        let formatted = formatDate(current);

                        if (!holidays.has(formatted) && !offDays.has(formatted)) {
                            count++;
                        }

                        current.setDate(current.getDate() + 1);
                    }

                    return count;
                }
            @endif
        });
    </script>
@endsection
