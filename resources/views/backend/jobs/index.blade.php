@extends('backend.layouts.app')
@php
    $isAdmin = auth()->user()->user_type === 'admin';
@endphp
@section('content')
    <div class="card">
        <form action="{{ route('job_posts.index') }}" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-md-0 h6">All Job Posts</h5>
                </div>

                <div class="col-md-2 ml-auto mb-2 mb-md-0">
                    <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" name="type"
                        id="type">
                        <option value="">Filter By Job Type</option>
                        @foreach (['full_time', 'part_time', 'internship'] as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>
                                {{ ucwords(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 ml-auto mb-2 mb-md-0">
                    <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" name="status"
                        id="status">
                        <option value="">Filter By Status</option>
                        @foreach (['draft', 'published', 'archived', 'scheduled'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ ucfirst($status) }}
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
                        onclick="window.location.href='{{ route('job_posts.index') }}'">Reset</button>
                    @if ($isAdmin || in_array('create_job_post', $_authPermissions))
                        <div class="dropdown mb-2 mb-md-0 d-inline-block">
                            <button type="button" data-toggle="dropdown" class="btn btn-light btn-sm p-0 py-1">
                                <i class="las la-ellipsis-v font-weight-bold fs-24"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item btn-item" href="{{ route('job_posts.create') }}" id="create-btn">
                                    <i class="las la-plus text-success"></i> Add New Job Post
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </form>
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th data-breakpoints="md" class="text-center">Job Type</th>
                        <th data-breakpoints="xs" class="text-center">Vacancy</th>
                        <th class="text-center">Applicants</th>
                        <th data-breakpoints="md">Status</th>
                        <th data-breakpoints="sm" class="text-center">Deadline</th>
                        <th width="20%" class="text-center">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jobPosts as $key => $jobPost)
                        <tr>
                            <td>
                                {{ $jobPost->title }}
                                <small class="text-muted d-block">{{ $jobPost->role }}</small>
                            </td>
                            <td class="text-center">{{ ucwords(str_replace('_', ' ', $jobPost->employment_type)) }}</td>
                            <td class="text-center">{{ $jobPost->vacancy }}</td>
                            <td class="text-center">{{ (int) ($jobPost->applications_count ?? 0) }}</td>
                            <td data-breakpoints="md">
                                @php
                                    $statusColor = match ($jobPost->status) {
                                        'published' => 'success',
                                        'draft' => 'warning',
                                        'scheduled' => 'info',
                                        'archived' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge badge-inline badge-soft-{{ $statusColor }}">
                                    {{ ucfirst($jobPost->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($jobPost->deadline)
                                    <span @if($jobPost->deadline->isPast()) class="text-danger" @endif>
                                        {{ $jobPost->deadline->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-success">Open</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($jobPost->applications_count ?? 0) > 0)
                                    <a href="{{ route('applications.index', ['type' => 'job', 'job_post_id' => $jobPost->id]) }}"
                                        class="btn btn-sm btn-icon btn-soft-primary btn-circle mb-1 mb-md-0" title="Applications">
                                        <i class="las la-users"></i>
                                    </a>
                                @endif
                                <a href="{{ to_frontend(route('job_posts.show', $jobPost->slug), 'job') }}"
                                    class="btn btn-sm btn-icon btn-soft-info btn-circle mb-1 mb-md-0" title="View" target="_blank">
                                    <i class="las la-eye"></i>
                                </a>
                                <a href="{{ route('job_posts.edit', $jobPost->id) }}"
                                    class="btn btn-sm btn-icon btn-soft-success btn-circle mb-1 mb-md-0" title="Edit">
                                    <i class="las la-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $jobPosts->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
        });
    </script>
@endsection
