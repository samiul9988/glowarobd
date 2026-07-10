@extends('backend.layouts.app')

@section('content')
    @php
        $jobPost = null; // No existing job post for create form
    @endphp
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 h6">Job Information</h5>
                    @if (Route::has('job_posts.index'))
                        <a href="{{ route('job_posts.index') }}" class="btn btn-light btn-sm">Back to List</a>
                    @endif
                </div>

                <div class="card-body p-4">
                    @include('backend.jobs.fields', ['jobPost' => $jobPost])
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('backend.jobs.partials.script')
@endsection
