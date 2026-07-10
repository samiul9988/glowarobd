@extends(config('app.theme') . 'frontend.layouts.app')

@section('meta')
@php
	$jobPosts = $jobPosts ?? collect();
	$pageTitle = $pageTitle ?? 'Job Openings';
	$pageDescription = $pageDescription ?? 'Browse open job opportunities and filter positions by role, employment type, and status.';
@endphp

<x-seo :meta="[
	'title' => $pageTitle,
	'description' => $pageDescription,
	'twitter' => [
		'card' => 'summary_large_image',
	],
]" />
@endsection

@section('css')
<style>
	.jobs-hero {
		background: linear-gradient(135deg, #12324a 0%, #1f4e6d 100%);
		border-radius: 24px;
		color: #fff;
		box-shadow: 0 18px 45px rgba(15, 76, 117, 0.18);
	}

	.jobs-hero .badge {
		background: rgba(255, 255, 255, 0.12);
		color: #fff;
		border: 1px solid rgba(255, 255, 255, 0.18);
	}

	.jobs-panel,
	.job-card {
		border: 0;
		border-radius: 18px;
		box-shadow: 0 12px 30px rgba(14, 35, 61, 0.08);
	}

	.jobs-panel {
		position: sticky;
		top: 1rem;
	}

	.job-card {
		transition: transform .2s ease, box-shadow .2s ease;
	}

	.job-card:hover {
		transform: translateY(-2px);
		box-shadow: 0 16px 40px rgba(14, 35, 61, 0.12);
	}

	.job-meta {
		color: #6c757d;
		font-size: .875rem;
	}

	.job-badge {
		border-radius: 999px;
		font-weight: 600;
		padding: .35rem .75rem;
	}

	.job-card a {
		text-decoration: none;
	}
</style>
@endsection

@section('content')
@php
	$search = request('search', $search ?? '');
	$employmentType = request('employment_type', $employmentType ?? '');
	$status = request('status', $status ?? '');
	$experience = request('experience', $experience ?? '');

	$employmentTypes = [
		'full_time' => 'Full Time',
		'part_time' => 'Part Time',
		'internship' => 'Internship',
	];

	$statusLabels = [
		'draft' => 'Draft',
		'published' => 'Published',
		'scheduled' => 'Scheduled',
		'archived' => 'Archived',
	];
@endphp

<div class="container py-4 py-lg-5">
	<div class="jobs-hero p-4 p-lg-5 mb-4">
		<div class="row align-items-center">
			<div class="col-lg-8">
				<span class="badge badge-inline badge-pill mb-3 px-3 py-2">Careers</span>
				<h1 class="font-weight-bold mb-3">Find Your Next Role</h1>
				<p class="mb-0 text-white-75">Browse open positions, filter by your preferences, and find the right opportunity faster.</p>
			</div>
			<div class="col-lg-4 text-lg-right mt-4 mt-lg-0">
				<div class="d-inline-flex align-items-center bg-white rounded-pill px-3 py-2 text-dark shadow-sm">
					<i class="las la-briefcase text-primary mr-2"></i>
					<strong>{{ $jobPosts->total() }}</strong>
					<span class="ml-1">Open Roles</span>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-3 mb-4">
			<div class="card jobs-panel">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center mb-3">
						<h5 class="mb-0">Filters</h5>
						<a href="{{ url()->current() }}" class="small text-decoration-none">Clear</a>
					</div>

					<form method="GET" action="{{ url()->current() }}">
						<div class="form-group">
							<label for="search" class="small font-weight-bold text-muted">Search</label>
							<input type="text" name="search" id="search" class="form-control" placeholder="Title, role or keyword" value="{{ $search }}">
						</div>

						<div class="form-group">
							<label for="employment_type" class="small font-weight-bold text-muted">Employment Type</label>
							<select name="employment_type" id="employment_type" class="form-control">
								<option value="">All Types</option>
								@foreach ($employmentTypes as $value => $label)
									<option value="{{ $value }}" @selected($employmentType === $value)>{{ $label }}</option>
								@endforeach
							</select>
						</div>

						<div class="form-group">
							<label for="experience" class="small font-weight-bold text-muted">Experience</label>
							<select name="experience" id="experience" class="form-control">
								<option value="" @selected($experience !== 'no')>All Experience</option>
								<option value="no" @selected($experience === 'no')>No Experience</option>
							</select>
						</div>

						<button type="submit" class="btn btn-primary btn-block" style="background: linear-gradient(135deg, #12324a 0%, #1f4e6d 100%) !important; color: #fff !important;">Apply Filters</button>
					</form>
				</div>
			</div>
		</div>

		<div class="col-lg-9">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<div>
					<h4 class="mb-1">Open Positions</h4>
					<p class="text-muted mb-0">Showing roles that match your current filters.</p>
				</div>
			</div>

			@forelse ($jobPosts as $jobPost)
				@php
					$detailUrl = Route::has('job_posts.show') ? route('job_posts.show', $jobPost->slug) : '#';
					$salaryRange = $jobPost->salary_min && $jobPost->salary_max
						? number_format($jobPost->salary_min) . ' - ' . number_format($jobPost->salary_max)
						: ($jobPost->salary_min ? 'From ' . number_format($jobPost->salary_min) : ($jobPost->salary_max ? 'Up to ' . number_format($jobPost->salary_max) : 'Negotiable'));
				@endphp
				<div class="card job-card mb-3">
					<div class="card-body p-4">
						<div class="row align-items-center">
							<div class="col-md-8">
								<div class="d-flex flex-wrap align-items-center mb-2">
									<span class="badge badge-inline badge-light job-badge mr-2 mb-2">{{ $employmentTypes[$jobPost->employment_type] ?? ucwords(str_replace('_', ' ', $jobPost->employment_type)) }}</span>
									<span class="badge badge-inline badge-info job-badge mr-2 mb-2">{{ ucfirst($jobPost->status) }}</span>
									@if ($jobPost->deadline)
										<span class="badge badge-inline badge-warning job-badge mb-2">Deadline {{ $jobPost->deadline->format('d M, Y') }}</span>
									@endif
								</div>
								<h5 class="mb-2 font-weight-bold text-dark">{{ $jobPost->role ?: $jobPost->title }}</h5>
								<div class="job-meta mb-2">
									<span class="mr-3"><i class="las la-map-marker mr-1"></i>{{ $jobPost->location ?: 'Dhaka, Bangladesh' }}</span>
									<span><i class="las la-coins mr-1"></i>{{ $salaryRange }}</span>
								</div>
								<p class="text-muted mb-0">{{ \Illuminate\Support\Str::limit(strip_tags($jobPost->description), 140) }}</p>
							</div>
							<div class="col-md-4 text-md-right mt-3 mt-md-0">
								<div class="mb-3">
									<small class="text-muted d-block">Vacancy</small>
									<strong class="h4 mb-0">{{ $jobPost->vacancy }}</strong>
								</div>
								<div class="d-flex justify-content-md-end flex-wrap">
									<a href="{{ $detailUrl }}" class="btn btn-outline-primary mb-2">View Details</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			@empty
				<div class="card border-0 shadow-sm">
					<div class="card-body text-center py-5">
						<div class="mb-3 text-muted">
							<i class="las la-search la-3x"></i>
						</div>
						<h5>No job posts found</h5>
						<p class="text-muted mb-0">Try clearing the filters or check back later for new openings.</p>
					</div>
				</div>
			@endforelse

            <div class="mt-4">
                {{ $jobPosts->appends(request()->query())->links() }}
            </div>
		</div>
	</div>
</div>
@endsection
