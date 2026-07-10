@extends(config('app.theme') . 'frontend.layouts.app')

@section('meta')
    @php
        $jobPost = $jobPost ?? null;
        $pageTitle = $jobPost->role ?: $jobPost->title ?: 'Job Details';
        $pageDescription = $jobPost->description
            ? \Illuminate\Support\Str::limit(strip_tags($jobPost->description), 160)
            : 'View job details and apply for the role.';
        $shareUrl = url()->current();
        $shareTitle = rawurlencode($pageTitle);
        $shareText = rawurlencode($pageDescription);
        $applyEmail = get_setting('contact_email') ?: config('mail.from.address');

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
        .job-detail-hero,
        .job-detail-card,
        .job-detail-aside {
            border: 0;
            border-radius: 20px;
            box-shadow: 0 12px 30px rgba(14, 35, 61, 0.08);
        }

        .job-detail-hero {
            background: linear-gradient(135deg, #12324a 0%, #1f4e6d 100%);
            color: #fff;
        }

        .job-pill {
            border-radius: 999px;
            font-weight: 600;
            padding: .35rem .8rem;
        }

        .job-action-btn {
            appearance: none;
            border: 0;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            font-weight: 700;
            padding: .85rem 1.2rem;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, background-color .2s ease, color .2s ease, border-color .2s ease;
            min-height: 48px;
        }

        .job-action-btn:hover,
        .job-action-btn:focus {
            transform: translateY(-1px);
            text-decoration: none;
        }

        .job-action-btn-primary {
            background: linear-gradient(135deg, #12324a 0%, #1f4e6d 100%);
            color: #fff !important;
            box-shadow: 0 12px 24px rgba(18, 50, 74, .18);
        }

        .job-action-btn-primary:hover,
        .job-action-btn-primary:focus {
            color: #fff !important;
            box-shadow: 0 14px 28px rgba(18, 50, 74, .24);
        }

        .job-action-btn-danger {
            background: linear-gradient(135deg, #4a1212 0%, #6d1f1f 100%);
            color: #fff !important;
            box-shadow: 0 12px 24px rgba(74, 18, 18, 0.18);
        }

        .job-action-btn-danger:hover,
        .job-action-btn-danger:focus {
            color: #fff !important;
            box-shadow: 0 14px 28px rgba(74, 18, 18, 0.24);
        }

        .job-action-btn-secondary {
            background: #fff;
            color: #12324a;
            border: 1px solid rgba(18, 50, 74, .14);
            box-shadow: 0 10px 20px rgba(14, 35, 61, .06);
        }

        .job-action-btn-secondary:hover,
        .job-action-btn-secondary:focus {
            background: #f8fbfe;
            color: #0f2740;
            border-color: rgba(18, 50, 74, .22);
        }

        .job-action-btn-block {
            width: 100%;
        }

        .job-action-btn-share {
            width: 48px;
            height: 48px;
            padding: 0;
            border-radius: 16px;
            box-shadow: 0 10px 18px rgba(14, 35, 61, .08);
        }

        .job-action-btn-facebook {
            background: linear-gradient(135deg, #12324a 0%, #1f4e6d 100%);
            color: #fff;
        }

        .job-action-btn-facebook:hover,
        .job-action-btn-facebook:focus {
            color: #fff !important;
        }

        .job-action-btn-x {
            background: linear-gradient(135deg, #2d3748 0%, #1f2937 100%);
            color: #fff;
        }

        .job-action-btn-x:hover,
        .job-action-btn-x:focus {
            color: #fff !important;
        }

        .job-action-btn-linkedin {
            background: linear-gradient(135deg, #24415c 0%, #12324a 100%);
            color: #fff;
        }

        .job-action-btn-linkedin:hover,
        .job-action-btn-linkedin:focus {
            color: #fff !important;
        }

        .job-action-btn-whatsapp {
            background: linear-gradient(135deg, #3b5b68 0%, #2f4a56 100%);
            color: #fff;
        }

        .job-action-btn-whatsapp:hover,
        .job-action-btn-whatsapp:focus {
            color: #fff !important;
        }

        .job-action-btn-copy {
            background: linear-gradient(135deg, #12324a 0%, #0c2233 100%);
            color: #fff;
        }

        .job-action-btn-copy:hover,
        .job-action-btn-copy:focus {
            color: #fff !important;
        }

        .job-section-title {
            font-size: .95rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #0f4c75;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .apply-box {
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(135deg, rgba(18, 50, 74, .08) 0%, rgba(18, 50, 74, .03) 100%);
            border: 1px solid rgba(15, 76, 117, .08);
            border-radius: 24px;
            box-shadow: 0 18px 40px rgba(14, 35, 61, .08);
        }

        .apply-box::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .35) 0%, rgba(255, 255, 255, 0) 72%);
            pointer-events: none;
        }

        .apply-box-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: #12324a;
            margin-bottom: .35rem;
        }

        .apply-box-copy {
            max-width: 34rem;
            color: #516173;
            margin-bottom: 0;
        }

        .apply-box-badges {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .apply-box-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .4rem .75rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .7);
            color: #12324a;
            font-size: .85rem;
            font-weight: 700;
            box-shadow: 0 8px 18px rgba(14, 35, 61, .06);
        }

        .apply-box-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .apply-box-visual {
            width: 72px;
            height: 72px;
            border-radius: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #12324a 0%, #1f4e6d 100%);
            color: #fff;
            font-size: 1.8rem;
            box-shadow: 0 14px 28px rgba(18, 50, 74, .22);
            flex: 0 0 auto;
        }

        .floating-apply-btn {
            position: fixed;
            left: 16px;
            right: 16px;
            bottom: 16px;
            z-index: 1030;
            display: none;
            border-radius: 999px;
            box-shadow: 0 18px 36px rgba(18, 50, 74, .22);
            padding: .95rem 1.25rem;
            font-size: 1rem;
        }

        .floating-apply-btn i {
            font-size: 1.05rem;
        }

        @media (max-width: 991.98px) {
            .floating-apply-btn {
                display: inline-flex;
            }

            .container.py-4.py-lg-5 {
                padding-bottom: 5.5rem !important;
            }
        }

        @media (min-width: 992px) {
            .floating-apply-btn {
                display: none !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container py-4 py-lg-5">
        <div class="card job-detail-hero p-4 p-lg-5 mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                <div class="mb-3 mb-lg-0">
                    <div class="d-flex flex-wrap align-items-center mb-3">
                        <span
                            class="badge badge-inline badge-light job-pill mr-2 mb-2">{{ ucwords(str_replace('_', ' ', $jobPost->employment_type ?? 'full_time')) }}</span>
                        <span
                            class="badge badge-inline badge-light job-pill mr-2 mb-2">{{ ucfirst($jobPost->status ?? 'published') }}</span>
                        @if (!empty($jobPost->deadline))
                            <span class="badge badge-inline badge-light job-pill mb-2">Apply by
                                {{ $jobPost->deadline->format('d M, Y') }}</span>
                        @endif
                    </div>
                    <h1 class="font-weight-bold mb-2">{{ $jobPost->role ?: $jobPost->title ?: 'Job Opening' }}</h1>
                </div>
                <div class="text-lg-right">
                    <div class="h3 mb-1">{{ $jobPost->vacancy ?? 1 }}</div>
                    <div class="text-white-75">Open {{ Str::plural('Vacancy', $jobPost->vacancy ?? 1) }}</div>
                </div>
            </div>
        </div>

        <div class="card job-detail-aside p-4 mb-4 d-lg-none">
            <h5 class="mb-3">Quick Details</h5>
            <div class="d-flex justify-content-between border-bottom py-2">
                <span class="text-muted">Vacancy</span>
                <strong>{{ $jobPost->vacancy ?? 1 }}</strong>
            </div>
            @if ($jobPost->experience)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted">Experience</span>
                    <strong>{{ $jobPost->experience }}</strong>
                </div>
            @endif
            @if ($jobPost->salary_min || $jobPost->salary_max)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted">Salary</span>
                    <strong>
                        @if ($jobPost && $jobPost->salary_min && $jobPost->salary_max)
                            {{ number_format($jobPost->salary_min) }} - {{ number_format($jobPost->salary_max) }}
                        @elseif ($jobPost && $jobPost->salary_min)
                            From {{ number_format($jobPost->salary_min) }}
                        @elseif ($jobPost && $jobPost->salary_max)
                            Up to {{ number_format($jobPost->salary_max) }}
                        @else
                            Negotiable
                        @endif
                    </strong>
                </div>
            @endif
            <div class="d-flex justify-content-between border-bottom py-2">
                <span class="text-muted">Status</span>
                <strong>{{ ucfirst($jobPost->status ?? 'published') }}</strong>
            </div>
            <div class="d-flex justify-content-between border-bottom py-2">
                <span class="text-muted">Deadline</span>
                <strong>{{ !empty($jobPost->deadline) ? $jobPost->deadline->format('d M, Y') : 'Open' }}</strong>
            </div>
            <div class="d-flex justify-content-between pt-2">
                <span class="text-muted">Published</span>
                <strong>{{ !empty($jobPost->published_at) ? $jobPost->published_at->format('d M, Y') : 'Recently' }}</strong>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4 order-1 order-lg-1">
                <div class="card job-detail-card p-4 p-lg-5">
                    <div class="d-flex flex-wrap align-items-center mb-4">
                        <span
                            class="job-pill badge badge-inline badge-light mr-2 mb-2">{{ $employmentTypeLabels[$jobPost->employment_type ?? 'full_time'] ?? ucwords(str_replace('_', ' ', $jobPost->employment_type ?? 'full_time')) }}</span>
                        @if (!empty($jobPost->experience))
                            <span
                                class="job-pill badge badge-inline badge-info mr-2 mb-2">{{ $jobPost->experience }}</span>
                        @endif
                        @if (!empty($jobPost->published_at))
                            <span class="job-pill badge badge-inline badge-secondary mb-2">Published
                                {{ $jobPost->published_at->format('d M, Y') }}</span>
                        @endif
                    </div>

                    <div class="job-section-title">Overview</div>
                    <div class="aiz-editor-data mb-4">
                        {!! $jobPost->description ?? '<p>No description provided.</p>' !!}
                    </div>

                    @php
                        $benefits = $jobPost->benefits ? array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $jobPost->benefits))) : [];
                    @endphp
                    @if(count($benefits) > 0)
                        <div class="job-section-title">Benefits</div>
                        <div class="aiz-editor-data mb-4">
                            <ul class="list-unstyled">
                                @foreach (array_filter($benefits) as $benefit)
                                    <li class="mb-2">
                                        <i class="la la-check-circle text-success mr-2"></i>{{ $benefit }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4 order-2 order-lg-2 mb-4 mb-lg-0">
                <div class="card job-detail-aside p-4 mb-4 d-none d-lg-block">
                    <h5 class="mb-3">Quick Details</h5>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Vacancy</span>
                        <strong>{{ $jobPost->vacancy ?? 1 }}</strong>
                    </div>
                    @if ($jobPost->experience)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted">Experience</span>
                            <strong>{{ $jobPost->experience }}</strong>
                        </div>
                    @endif
                    @if ($jobPost->salary_min || $jobPost->salary_max)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted">Salary</span>
                            <strong>
                                @if ($jobPost && $jobPost->salary_min && $jobPost->salary_max)
                                    {{ number_format($jobPost->salary_min) }} - {{ number_format($jobPost->salary_max) }}
                                @elseif ($jobPost && $jobPost->salary_min)
                                    From {{ number_format($jobPost->salary_min) }}
                                @elseif ($jobPost && $jobPost->salary_max)
                                    Up to {{ number_format($jobPost->salary_max) }}
                                @else
                                    Negotiable
                                @endif
                            </strong>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Status</span>
                        <strong>{{ ucfirst($jobPost->status ?? 'published') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Deadline</span>
                        <strong>{{ !empty($jobPost->deadline) ? $jobPost->deadline->format('d M, Y') : 'Open' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between pt-2">
                        <span class="text-muted">Published</span>
                        <strong>{{ !empty($jobPost->published_at) ? $jobPost->published_at->format('d M, Y') : 'Recently' }}</strong>
                    </div>
                </div>

                <div class="card job-detail-aside p-4 mb-4">
                    <h5 class="mb-3 text-capitalize">
                        Ready to take the next step?
                    </h5>
                    <p class="text-muted mb-3">Use the apply button to start the application process or return to the
                        listing to compare roles.</p>
                    @if (is_null($jobPost->deadline) || now()->lte($jobPost->deadline_end))
                        <a href="javascript:void(0)" class="job-action-btn job-action-btn-primary job-action-btn-block mb-2 d-none d-lg-inline-flex job-apply-btn">
                            Apply Now
                        </a>
                    @else
                        <a href="javascript:void(0)" class="job-action-btn job-action-btn-danger job-action-btn-block mb-2 d-none d-lg-inline-flex">
                            Application Closed
                        </a>
                    @endif
                    <a href="{{ route('job_posts.careers') }}"
                        class="job-action-btn job-action-btn-secondary job-action-btn-block">Browse More Jobs</a>
                </div>

                <div class="card job-detail-aside p-4">
                    <h5 class="mb-3">Share This Job</h5>
                    <div class="d-flex flex-wrap share-grid">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank"
                            rel="noopener" class="job-action-btn job-action-btn-share job-action-btn-facebook mr-2 mb-2"
                            title="Share on Facebook" aria-label="Share on Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ $shareTitle }}"
                            target="_blank" rel="noopener"
                            class="job-action-btn job-action-btn-share job-action-btn-x mr-2 mb-2" title="Share on X"
                            aria-label="Share on X"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode($shareUrl) }}&title={{ $shareTitle }}"
                            target="_blank" rel="noopener"
                            class="job-action-btn job-action-btn-share job-action-btn-linkedin mr-2 mb-2"
                            title="Share on LinkedIn" aria-label="Share on LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://wa.me/?text={{ $shareTitle }}%20{{ urlencode($shareUrl) }}" target="_blank"
                            rel="noopener" class="job-action-btn job-action-btn-share job-action-btn-whatsapp mr-2 mb-2"
                            title="Share on WhatsApp" aria-label="Share on WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <button type="button" class="job-action-btn job-action-btn-share job-action-btn-copy mb-2"
                            id="copy-job-link" data-url="{{ $shareUrl }}" title="Copy link" aria-label="Copy link"><i
                                class="far fa-copy"></i></button>
                    </div>
                    <small class="text-muted d-block mt-2">Copy the link or share it directly with your network.</small>
                </div>
            </div>
        </div>
    </div>

    @if (is_null($jobPost->deadline) || now()->lte($jobPost->deadline_end))
        <a href="javascript:void(0)" class="job-action-btn job-action-btn-primary floating-apply-btn d-lg-none job-apply-btn">
            <span>Apply Now</span>
        </a>
    @else
        <a href="javascript:void(0)" class="job-action-btn job-action-btn-danger floating-apply-btn d-lg-none">
            <span>Application Closed</span>
        </a>
    @endif
@endsection

@section('modal')
    @php
        $applicationForm = optional($jobPost)->application_form ?? [];
        $fields = $applicationForm['fields'] ?? [];
        // dd($applicationForm, $fields);
    @endphp
    <div class="modal fade" id="jobApplyModal" tabindex="-1" role="dialog"
        aria-labelledby="jobApplyLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ data_get($applicationForm, 'title', 'Apply For This Job') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="min-height: 270px !important;">
                    <form id="job-apply-form" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="job_post_id" value="{{ $jobPost->id }}">
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="name">Full Name *</label>
                            <div class="col-sm-9">
                                <input type="text" placeholder="Your Full Name" id="name" name="name" class="form-control form-control-sm required" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="phone">Phone *</label>
                            <div class="col-sm-9">
                                <input type="text" placeholder="Your Phone Number" id="phone" name="phone" class="form-control form-control-sm required" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="email">Email *</label>
                            <div class="col-sm-9">
                                <input type="email" placeholder="Your Email Address" id="email" name="email" class="form-control form-control-sm required" required>
                            </div>
                        </div>
                        @include(config('app.theme').'frontend.jobs.partials.application_fields', ['fields' => $fields])

                        <div id="application-loader" class="text-center d-none">
                            <span class="spinner-border spinner-border-sm"></span> Submitting...
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-dark" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-success" id="job-application-submit-btn">
                        {{ data_get($applicationForm, 'button_text', 'Submit') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function() {
            $().ready(function() {
                $('#copy-job-link').on('click', function() {
                    const url = this.getAttribute('data-url');
                    var $temp = $("<textarea>");
                    $("body").append($temp);
                    $temp.val(url).select();
                    try {
                        document.execCommand("copy");
                        if (window.AIZ && AIZ.plugins && typeof AIZ.plugins.notify === 'function') {
                            AIZ.plugins.notify('success', 'Job link copied to clipboard.');
                        }
                    } catch (err) {
                        console.error("Failed to copy: ", err);
                        if (window.AIZ && AIZ.plugins && typeof AIZ.plugins.notify === 'function') {
                            AIZ.plugins.notify('danger', 'Failed to copy job link.');
                        }
                    }
                    $temp.remove();
                });

                $(document).on('click', '.job-apply-btn', function() {
                    $('#job-apply-form')[0].reset();
                    $('#jobApplyModal').modal('show');
                });

                $('#job-application-submit-btn').on('click', function(e) {
                    e.preventDefault();
                    const form = $('#job-apply-form')[0];
                    const formData = new FormData(form);
                    const actionUrl = "{{ route('job_applications.store') }}";

                    const requiredFields = $(form).find('.required');
                    let allValid = true;
                    requiredFields.each(function() {
                        if (!this.value.trim()) {
                            allValid = false;
                            $(this).addClass('is-invalid');
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });

                    if (!allValid) {
                        if (window.AIZ && AIZ.plugins && typeof AIZ.plugins.notify === 'function') {
                            AIZ.plugins.notify('danger', 'Please fill in all required fields.');
                        }
                        return;
                    }
                    const submitBtn = $(this);
                    submitBtn.prop('disabled', true);
                    $('#application-loader').removeClass('d-none');
                    $.ajax({
                        url: actionUrl,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#jobApplyModal').modal('hide');
                            if (window.AIZ && AIZ.plugins && typeof AIZ.plugins.notify === 'function') {
                                AIZ.plugins.notify('success', response.message || 'Application submitted successfully.');
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'An error occurred while submitting your application.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            if (window.AIZ && AIZ.plugins && typeof AIZ.plugins.notify === 'function') {
                                AIZ.plugins.notify('danger', errorMessage);
                            }
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false);
                            $('#application-loader').addClass('d-none');
                        }
                    });
                });
            });
        })();
    </script>
@endsection
