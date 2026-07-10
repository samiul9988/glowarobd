@extends('backend.layouts.app')

@section('content')
	@php
		$formatLabel = static function ($key) {
			return ucfirst(str_replace('_', ' ', $key));
		};

		$formatValue = static function ($value) {
			if (is_null($value) || $value === '') {
				return 'N/A';
			}

			if (is_bool($value)) {
				return $value ? 'Yes' : 'No';
			}

			if (is_array($value)) {
				return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}

			if (is_numeric($value)) {
				return (string) $value;
			}

			if (! is_string($value)) {
				return (string) $value;
			}

			try {
				return \Carbon\Carbon::parse($value)->format('d M Y, h:i A');
			} catch (\Throwable $e) {
				return $value;
			}
		};
	@endphp

	<style>
		.attendance-log-page {
			background: linear-gradient(135deg, #f4f8ff 0%, #f9fbff 40%, #eef7f3 100%);
			border-radius: 14px;
			border: 1px solid #e7edf9;
			padding: 22px;
		}

		.attendance-log-header {
			border-bottom: 1px dashed #dfe7f7;
			padding-bottom: 14px;
			margin-bottom: 18px;
		}

		.attendance-log-card {
			border: 0;
			border-radius: 14px;
			box-shadow: 0 12px 26px rgba(28, 46, 88, 0.08);
			overflow: hidden;
		}

		.attendance-log-card + .attendance-log-card {
			margin-top: 16px;
		}

		.change-side {
			border-radius: 12px;
			height: 100%;
			border: 1px solid transparent;
		}

		.change-side-old {
			background: #fff7f7;
			border-color: #ffd8d8;
		}

		.change-side-new {
			background: #f2fbf6;
			border-color: #ccefd9;
		}

		.change-row {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			gap: 12px;
			padding: 9px 0;
			border-bottom: 1px dashed rgba(20, 27, 42, 0.08);
		}

		.change-row:last-child {
			border-bottom: 0;
			padding-bottom: 0;
		}

		.change-key {
			color: #5b667f;
			font-size: 12px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.3px;
			min-width: 110px;
		}

		.change-value {
			color: #1f2a44;
			font-size: 13px;
			font-weight: 600;
			text-align: right;
			word-break: break-word;
			white-space: pre-wrap;
		}

		.change-side-title {
			font-weight: 700;
			font-size: 13px;
			margin-bottom: 8px;
		}

		.change-side-old .change-side-title {
			color: #d63333;
		}

		.change-side-new .change-side-title {
			color: #1f9d5a;
		}

		@media (max-width: 767.98px) {
			.attendance-log-page {
				padding: 14px;
			}

			.change-row {
				flex-direction: column;
				gap: 4px;
			}

			.change-value {
				text-align: left;
			}
		}
	</style>

	<div class="attendance-log-page">
		<div class="attendance-log-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
			<div>
				<h4 class="mb-1">Attendance Changelogs</h4>
				<p class="text-muted mb-0">
					Date: <strong>{{ optional($attendance->date)->format('d M, Y') }}</strong>
					| Status: <span class="badge badge-soft-primary badge-inline">{{ ucfirst($attendance->status ?? 'N/A') }}</span>
					| Total Logs: <strong>{{ $attendance->logs->count() }}</strong>
				</p>
			</div>
			<div class="mt-3 mt-md-0">
				<a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
					<i class="las la-arrow-left mr-1"></i> Back
				</a>
			</div>
		</div>

		@forelse ($attendance->logs->sortByDesc('created_at') as $log)
			@php
				$oldData = $log->old_data ?? [];
				$newData = $log->new_data ?? [];
				$changedFields = collect(array_unique(array_merge(array_keys($oldData), array_keys($newData))))->values();
			@endphp

			<div class="card attendance-log-card">
				<div class="card-header bg-white border-0 pb-0">
					<div class="w-100 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
						<div class="mb-2 mb-md-0">
							<h6 class="mb-1">Log #{{ $loop->iteration }}</h6>
							<small class="text-muted">
                                Changed by <strong>{{ $log->user?->name ?? 'System' }}</strong> at {{ optional($log->created_at)->format('d M Y, h:i A') }}
                            </small>
						</div>
						<span class="badge badge-inline badge-soft-info">
							{{ $changedFields->count() }} field(s) updated
						</span>
					</div>
				</div>

				<div class="card-body pt-3">
					<div class="row">
						<div class="col-12 col-lg-6 mb-3 mb-lg-0">
							<div class="change-side change-side-old p-3">
								<div class="change-side-title">Old Data</div>

								@forelse ($changedFields as $field)
									<div class="change-row">
										<div class="change-key">{{ $formatLabel($field) }}</div>
										<div class="change-value">{{ $formatValue($oldData[$field] ?? null) }}</div>
									</div>
								@empty
									<div class="text-muted">No previous data found.</div>
								@endforelse
							</div>
						</div>

						<div class="col-12 col-lg-6">
							<div class="change-side change-side-new p-3">
								<div class="change-side-title">New Data</div>

								@forelse ($changedFields as $field)
									<div class="change-row">
										<div class="change-key">{{ $formatLabel($field) }}</div>
										<div class="change-value">{{ $formatValue($newData[$field] ?? null) }}</div>
									</div>
								@empty
									<div class="text-muted">No updated data found.</div>
								@endforelse
							</div>
						</div>
					</div>
				</div>
			</div>
		@empty
			<div class="card border-0 shadow-none bg-transparent">
				<div class="card-body text-center py-5">
					<h5 class="mb-2">No changelogs found</h5>
					<p class="text-muted mb-0">This attendance record has not been updated yet.</p>
				</div>
			</div>
		@endforelse
	</div>

@endsection
