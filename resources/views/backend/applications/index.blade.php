@extends('backend.layouts.app')
@php
    $isAdmin = Auth::check() && Auth::user()->user_type == 'admin';
@endphp
@section('content')
    <div class="card">
        <form action="{{ route('applications.index') }}" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-md-0 h6">All Applications</h5>
                </div>

                <div class="col-md-2 ml-auto mb-2 mb-md-0">
                    <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" name="status"
                        id="status">
                        <option value="">Filter By status</option>
                        @foreach (['pending', 'approved', 'rejected'] as $opt)
                            <option value="{{ $opt }}" {{ request('status') == $opt ? 'selected' : '' }}>
                                {{ ucfirst($opt) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 ml-auto mb-2 mb-md-0">
                    <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" name="type"
                        id="type">
                        <option value="">Filter By Type</option>
                        @foreach (\App\Enums\ApplicationTypes::options() as $value => $label)
                            <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>
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
                        onclick="window.location.href='{{ route('applications.index') }}'">Clear</button>
                </div>
            </div>
        </form>
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="5%">#</th>
                        <th>Applicant</th>
                        <th>Subject</th>
                        <th class="text-center">Application Type</th>
                        <th class="text-center">Submitted At</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr>
                            <td class="text-center">{{ $loop->iteration + (($applications->currentPage() - 1) * $applications->perPage()) }}</td>
                            <td class="font-weight-bold">
                                {{ $application->user?->name ?? 'N/A' }}
                            </td>
                            <td>
                                {{ Str::limit($application->subject, 50) }}
                                @if (strlen($application->subject) > 50)
                                    @include('components.tooltip', [
                                        'title' => $application->subject,
                                        'class' => 'fs-14'
                                    ])
                                @endif
                            </td>
                            <td class="text-center font-weight-bold text-muted">{{ $application->type->label() }}</td>
                            <td class="text-center">{{ optional($application->created_at)->format('d M Y, h:i A') ?? '—' }}</td>
                            <td class="text-center">
                                @php
                                    $statusColor = match ($application->status) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'pending' => 'warning',
                                        default => 'info'
                                    };
                                @endphp
                                <span class="badge badge-inline badge-{{ $statusColor }}">
                                    {{ ucfirst($application->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('applications.show', $application->id) }}" class="btn btn-sm btn-outline-info">
                                    <i class="las la-eye"></i> View
                                </a>
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

    </script>
@endsection
