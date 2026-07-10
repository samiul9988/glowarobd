@extends('backend.layouts.app')
@php
    $isAdmin = Auth::check() && Auth::user()->user_type == 'admin';
@endphp
@section('content')
    <div class="card">
        <form action="{{ route('job_applications.index') }}" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-md-0 h6">All Applications</h5>
                </div>

                <div class="col-md-2 ml-auto mb-2 mb-md-0">
                    <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" name="status"
                        id="status">
                        <option value="">Filter By status</option>
                        @foreach (\App\Enums\JobApplicationStatus::options() as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') == $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 mb-2 mb-md-0">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control form-control-sm" id="search" name="search"
                            value="{{ request('search') }}" placeholder="Search...">
                    </div>
                </div>

                <div class="col-auto mb-2 mb-md-0">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="window.location.href='{{ route('job_applications.index') }}'">Clear</button>
                </div>
            </div>
        </form>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group btn-sm" role="group" aria-label="Basic example">
                    <a href="{{ route('job_applications.index') }}" class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center {{ empty(request('status')) && empty(request('shortlisted')) ? 'active' : '' }}">
                        All
                        <span class="w-auto badge badge-inline badge-primary ml-1">
                            {{ data_get($counts, 'total', 0) }}
                        </span>
                    </a>
                    <a href="{{ route('job_applications.index', ['shortlisted' => 1]) }}" class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center {{ (int) request('shortlisted') === 1 ? 'active' : '' }}">
                        Shortlisted
                        <span class="w-auto badge badge-inline badge-primary ml-1">
                            {{ data_get($counts, 'shortlist', 0) }}
                        </span>
                    </a>
                    <a href="{{ route('job_applications.index', ['status' => 'hired']) }}" class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center {{ request('status') == 'hired' ? 'active' : '' }}">
                        Hired
                        <span class="w-auto badge badge-inline badge-primary ml-1">
                            {{ data_get($counts, 'hired', 0) }}
                        </span>
                    </a>
                </div>
            </div>
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="5%">#</th>
                        <th>Applicant</th>
                        <th>Role</th>
                        <th class="text-center">Matching Score</th>
                        <th class="text-center">Submitted At</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr data-id="{{ $application->id }}">
                            <td class="text-center">
                                {{ $loop->iteration + (($applications->currentPage() - 1) * $applications->perPage()) }}
                            </td>
                            <td class="font-weight-bold">
                                <span class="d-block text-{{ $application->shortlisted ? 'success' : 'dark' }}">{{ $application->applicant_name ?? 'N/A' }}</span>
                                <span class="d-block text-muted">
                                    <i class="las la-phone"></i> {{ $application->applicant_phone }}
                                </span>
                            </td>
                            <td>
                                {{ Str::limit($application->job->role ?? 'No Role', 50) }}
                                @if (strlen($application->job->role ?? '') > 50)
                                    @include('components.tooltip', [
                                        'title' => $application->job->role ?? 'No role',
                                        'class' => 'fs-14'
                                    ])
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $scoreColor = match (true) {
                                        $application->matching_score >= 80 => 'success',
                                        $application->matching_score >= 50 => 'warning',
                                        default => 'danger'
                                    };
                                @endphp
                                <span class="font-weight-bold badge badge-inline badge-soft-{{ $scoreColor }}">
                                    {{ $application->matching_score }}
                                </span>
                            </td>
                            <td class="text-center">{{ optional($application->created_at)->format('d M Y, h:i A') ?? '—' }}</td>
                            <td class="text-center">
                                @php
                                    $statusColor = match ($application->status->value) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'pending' => 'warning',
                                        default => 'info'
                                    };
                                @endphp
                                <span class="badge badge-inline badge-soft-{{ $statusColor }}">
                                    {{ $application->status->label() }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('job_applications.show', $application->id) }}" class="btn btn-sm btn-primary btn-icon">
                                    <i class="las la-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-{{ $application->shortlisted ? 'success' : 'info' }} btn-icon shortlist-btn" data-toggle="tooltip" title="{{ $application->shortlisted ? 'Remove from Shortlist' : 'Add to Shortlist' }}">
                                    <i class="las la-star"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No applications found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $applications->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection

@section('modal')

@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.shortlist-btn', function() {
                const button = $(this);
                const row = button.closest('tr');
                const applicationId = row.data('id');
                const url = "{{ route('job_applications.update_shortlist', ':id') }}".replace(':id', applicationId);
                button.prop('disabled', true); // Disable button to prevent multiple clicks
                $.ajax({
                    url: url,
                    method: 'PATCH',
                    success: function(response) {
                        if (response.success) {
                            button.addClass(response.shortlisted ? 'btn-success' : 'btn-info')
                                .removeClass(response.shortlisted ? 'btn-info' : 'btn-success');
                            button.attr('data-original-title', response.shortlisted
                                ? 'Remove from Shortlist'
                                : 'Add to Shortlist'
                            );
                            button.tooltip('dispose');
                            button.tooltip(); // Re-initialize tooltip with new title
                            AIZ.plugins.notify('success', response.message);
                        }
                    },
                    error: function() {
                        AIZ.plugins.notify('error', 'An error occurred while updating the shortlist status.');
                    },
                    complete: function() {
                        button.prop('disabled', false); // Re-enable button after request completes
                    }
                });
            });
        });
    </script>
@endsection
