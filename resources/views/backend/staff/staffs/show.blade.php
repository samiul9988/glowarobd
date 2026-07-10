@extends('backend.layouts.app')

@section('content')

    @php
        $user = $staff->user;
        $ec = $staff->emergency_contact ?? [];
        $offdays = $staff->weekly_offday ?? [];
        $joiningDate = $staff->joining_date;
        $now = \Carbon\Carbon::now();

        // Calculate tenure
        $tenure = $joiningDate ? $joiningDate->diff($now) : null;
        $tenureStr = $tenure
            ? ($tenure->y > 0 ? $tenure->y . 'y ' : '') . ($tenure->m > 0 ? $tenure->m . 'm ' : '') . $tenure->d . 'd'
            : 'N/A';

        // Education list
        $educations = $staff->educational_background
            ? array_map('trim', explode(',', $staff->educational_background))
            : [];

        // Attachment groups
        $cvList = $staff->attachments->where('type', 'cv');
        $nidList = $staff->attachments->where('type', 'nid');
        $certList = $staff->attachments->where('type', 'certificate');

        $empStatus = $staff->employment_status ?? 'active';
        $empStatusColor = match ($empStatus) {
            'active' => 'success',
            'probation' => 'warning',
            'on_leave' => 'info',
            'resigned' => 'danger',
            'terminated' => 'dark',
            default => 'secondary',
        };
        $empStatusLabel = match ($empStatus) {
            'active' => 'Active',
            'probation' => 'Probation',
            'on_leave' => 'On Leave',
            'resigned' => 'Resigned',
            'terminated' => 'Terminated',
            default => ucfirst($empStatus),
        };

        $bank = $staff->bank_account ?? [];

        $isAdmin = Auth::check() && Auth::user()->user_type == 'admin';
    @endphp

    <style>
        .staff-profile-avatar {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .15);
        }

        .profile-hero {
            background: linear-gradient(135deg, #3a7bd5 0%, #00d2ff 100%);
            border-radius: .5rem .5rem 0 0;
            padding: 2rem 2rem 1rem;
            color: #fff;
        }

        .profile-hero .badge-role {
            background: rgba(255, 255, 255, .2);
            color: #fff;
            font-size: .75rem;
            padding: .3em .75em;
            border-radius: 20px;
            backdrop-filter: blur(4px);
        }

        .info-heading {
            font-size: .85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #778ea8;
        }

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

        .tab-nav .nav-link {
            font-weight: 600;
            font-size: .82rem;
            padding: .6rem 1.1rem;
            color: #525f7f;
            border: none;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            transition: all .2s;
        }

        .tab-nav .nav-link.active {
            color: #3a7bd5;
            border-bottom: 2px solid #3a7bd5;
            background: transparent;
        }

        .stat-card {
            border-radius: .5rem;
            padding: 1.1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .85rem;
        }

        .stat-card .icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .offday-chip {
            display: inline-block;
            padding: .2em .7em;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
            margin: 2px;
        }

        .edu-tag {
            display: inline-block;
            background: #eaf0fb;
            color: #3a7bd5;
            padding: .2em .75em;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
            margin: 2px;
        }

        .event-timeline {
            position: relative;
            padding-left: 1.5rem;
        }

        .event-timeline::before {
            content: '';
            position: absolute;
            left: .5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .event-timeline-item {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .event-timeline-item::before {
            content: '';
            position: absolute;
            left: -1.1rem;
            top: .35rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #3a7bd5;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #3a7bd5;
        }

        .attachment-card {
            border: 1px solid #e9ecef;
            border-radius: .5rem;
            padding: .75rem 1rem;
            display: flex;
            align-items: center;
            gap: .75rem;
            transition: box-shadow .2s;
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

        .application-meta {
            font-size: .78rem;
            color: #8f9bb3;
        }

        .attachment-pill {
            display: inline-flex;
            align-items: center;
            background: #eef4ff;
            color: #3a7bd5;
            border-radius: 999px;
            padding: .25rem .85rem;
            font-size: .78rem;
            font-weight: 600;
            margin: .15rem .35rem .15rem 0;
        }

        .attachment-pill i {
            margin-right: .35rem;
            font-size: .9rem;
        }

        .application-detail-card {
            border: 1px solid #eef1f7;
            border-radius: .65rem;
            padding: 1rem 1.25rem;
            background: #fff;
        }

        .actions-toolbar {
            border: 1px solid #edf1f7;
            border-radius: .75rem;
            padding: .85rem 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
            margin-bottom: 1rem;
        }

        .actions-toolbar .meta-chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid #dce6f5;
            border-radius: 999px;
            padding: .25rem .6rem;
            font-size: .72rem;
            font-weight: 700;
            color: #486289;
            letter-spacing: .03em;
            background: #fff;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: .9rem;
        }

        .action-card {
            grid-column: span 12;
            border: 1px solid #e8eef8;
            border-radius: .85rem;
            padding: .95rem 1rem;
            background: #fff;
            box-shadow: 0 8px 24px rgba(31, 64, 104, .06);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(31, 64, 104, .1);
            border-color: #d4e3fb;
        }

        .action-card .action-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .action-card .action-title {
            font-size: .92rem;
            font-weight: 700;
            color: #243b5f;
            margin-bottom: .1rem;
            line-height: 1.2;
        }

        .action-card .action-subtitle {
            font-size: .76rem;
            color: #8090a9;
            line-height: 1.35;
            margin-bottom: 0;
        }

        .action-card .btn-action {
            border-radius: .5rem;
            font-size: .7rem;
            font-weight: 700;
            padding: .35rem .55rem;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .action-card .btn-action i {
            font-size: .8rem;
        }

        .action-card .btn-action+.btn-action {
            margin-left: .3rem;
        }

        .action-card-inner {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            flex-direction: column;
        }

        .action-card-content {
            display: flex;
            gap: .75rem;
            flex: 1;
            width: 100%;
            flex-direction: column;
            align-items: flex-start;
        }

        .action-card-actions {
            display: flex;
            gap: .3rem;
            flex-wrap: nowrap;
            margin-top: .5rem;
            align-items: center;
        }

        .action-card-info {
            flex: 1;
        }

        .modal-md {
            max-width: 600px !important;
        }

        @media (min-width: 576px) {
            .action-card-inner {
                flex-direction: row;
                align-items: flex-start;
            }

            .action-card-content {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
            }

            .action-card-info {
                flex: 1;
                min-width: 0;
            }

            .action-card-actions {
                margin-top: 0;
                flex-wrap: nowrap;
                flex-shrink: 0;
            }
        }

        @media (min-width: 768px) {
            .action-card {
                grid-column: span 6;
            }
        }

        @media (min-width: 1200px) {
            .action-card {
                grid-column: span 4;
            }
        }
    </style>

    {{-- Breadcrumb / Back button --}}
    <div class="d-flex align-items-center mb-3 mt-2">
        <a href="{{ route('staffs.index') }}" class="btn btn-sm btn-light mr-2">
            <i class="las la-arrow-left"></i> {{ 'Back' }}
        </a>
        <span class="text-muted small">All Staffs / <strong>{{ $user->name }}</strong></span>
        <div class="ml-auto">
            @if ($isAdmin || in_array('edit_staff', $_authPermissions) || $user->id == Auth::id())
                <a href="{{ route('staffs.edit', encrypt($staff->id)) }}" class="btn btn-sm btn-primary">
                    <i class="las la-edit"></i> Edit Profile
                </a>
            @endif
            @if ($isAdmin || in_array('staffs_report', $_authPermissions) || $user->id == Auth::id())
                <a href="{{ route('staffs.report.show', $user->id) }}" class="btn btn-sm btn-outline-info ml-1">
                    <i class="las la-chart-bar"></i> View Report
                </a>
            @endif
        </div>
    </div>

    <div class="card shadow-sm mb-4">

        {{-- Hero header --}}
        <div class="profile-hero">
            <div class="d-flex align-items-end flex-wrap" style="gap:1.25rem">
                <img src="{{ $staff->profile_picture ? uploaded_asset($staff->profile_picture) : static_asset('assets/img/avatar-place.png') }}"
                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';"
                    class="staff-profile-avatar">
                <div class="flex-1 pb-1">
                    <h2 class="mb-1 text-white font-weight-bold">{{ $user->name }}</h2>
                    <div class="d-flex flex-wrap align-items-center" style="gap:.5rem">
                        @if ($staff->role)
                            <span class="badge-role">
                                <i class="las la-shield-alt mr-1"></i>{{ $staff->role->name }}
                            </span>
                        @endif
                        @if ($staff->employee_id)
                            <span class="badge-role">
                                <i class="las la-id-badge mr-1"></i>{{ $staff->employee_id }}
                            </span>
                        @endif
                        <span class="badge-role {{ $user->banned ? 'bg-danger' : '' }}" data-toggle="tooltip"
                            data-title="Account Status">
                            <i class="las {{ $user->banned ? 'la-user-slash' : 'la-user-check' }} mr-1"></i>
                            {{ $user->banned ? 'Inactive' : 'Active' }}
                        </span>
                        <span class="badge-role" data-toggle="tooltip" data-title="Employment Status">
                            <i class="las la-briefcase mr-1"></i>{{ $empStatusLabel }}
                        </span>
                    </div>
                </div>
                <div class="ml-auto text-right pb-1 d-none d-md-block">
                    @if ($staff->shift)
                        <span
                            class="badge badge-inline badge-{{ $staff->shift?->color() }} badge-pill px-3 py-3 font-weight-bold"
                            style="font-size:.85rem">
                            <i class="las la-{{ $staff->shift?->icon() }} mr-1"></i>{{ ucfirst($staff->shift?->label()) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick stat row --}}
        <div class="row no-gutters border-bottom bg-white">
            <div class="col-6 col-md-3 border-right text-center py-3">
                <div class="info-label mb-1">{{ 'Joined' }}</div>
                <div class="info-value">{{ $joiningDate ? $joiningDate->format('d M Y') : '—' }}</div>
            </div>
            <div class="col-6 col-md-3 border-right text-center py-3">
                <div class="info-label mb-1">{{ 'Tenure' }}</div>
                <div class="info-value">{{ $tenureStr }}</div>
            </div>
            <div class="col-6 col-md-3 border-right text-center py-3">
                <div class="info-label mb-1">{{ 'Working Hrs' }}</div>
                <div class="info-value">{{ $staff->working_hours ? $staff->working_hours . ' h/day' : '—' }}</div>
            </div>
            <div class="col-6 col-md-3 text-center py-3">
                <div class="info-label mb-1">{{ 'Salary' }}</div>
                <div class="info-value text-success font-weight-bold">
                    {{ $staff->salary ? number_format($staff->salary, 2) : '—' }}
                </div>
            </div>
        </div>

        {{-- Tab navigation --}}
        <ul class="nav tab-nav px-3 border-bottom bg-white" id="staffProfileTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-info" data-toggle="tab" href="#pane-info" role="tab">
                    <i class="las la-user mr-1"></i>Info
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-contact" data-toggle="tab" href="#pane-contact" role="tab">
                    <i class="las la-phone mr-1"></i>Contact
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-events" data-toggle="tab" href="#pane-events" role="tab">
                    <i class="las la-calendar-check mr-1"></i>Events
                    @if ($staff->events->count())
                        <span class="badge badge-inline badge-primary badge-pill ml-1">{{ $staff->events->count() }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-attachments" data-toggle="tab" href="#pane-attachments" role="tab">
                    <i class="las la-paperclip mr-1"></i>Attachments
                    @if ($staff->attachments->count())
                        <span
                            class="badge badge-inline badge-primary badge-pill ml-1">{{ $staff->attachments->count() }}</span>
                    @endif
                </a>
            </li>
            @if (get_setting('enable_attendance_management', 0) == 1)
                <li class="nav-item">
                    <a class="nav-link" id="tab-attendance" data-toggle="tab" href="#pane-attendance" role="tab">
                        <i class="las la-table mr-1"></i>Attendance
                    </a>
                </li>
            @endif
            @if (get_setting('enable_application_management', 0) == 1)
                <li class="nav-item">
                    <a class="nav-link" id="tab-applications" data-toggle="tab" href="#pane-applications" role="tab">
                        <i class="las la-file-alt mr-1"></i>Applications
                    </a>
                </li>
            @endif
            @if (get_setting('enable_attendance_management', 0) == 1 && get_setting('enable_salary_sheet_generation', 0) == 1)
                <li class="nav-item">
                    <a class="nav-link" id="tab-salary" data-toggle="tab" href="#pane-salary" role="tab">
                        <i class="las la-money-bill mr-1"></i>Salary Sheets
                    </a>
                </li>
            @endif
            @if (Auth::user()->user_type == 'admin' || in_array('edit_staff', $_authPermissions))
                <li class="nav-item">
                    <a class="nav-link" id="tab-actions" data-toggle="tab" href="#pane-actions" role="tab">
                        <i class="las la-cog mr-1"></i>Actions
                    </a>
                </li>
            @endif
        </ul>

        {{-- Tab panes --}}
        <div class="tab-content">
            {{-- ===== INFO TAB ===== --}}
            <div class="tab-pane fade show active p-4" id="pane-info" role="tabpanel">
                <div class="row">

                    {{-- Left column --}}
                    <div class="col-md-6">
                        <h5 class="info-heading mb-3">Employment</h5>

                        <div class="row mb-3">
                            <div class="col-5 info-label">Employee ID</div>
                            <div class="col-7 info-value">{{ $staff->employee_id ?: '—' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Role</div>
                            <div class="col-7 info-value">{{ $staff->role?->name ?? '—' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Joining Date</div>
                            <div class="col-7 info-value">
                                {{ $joiningDate ? $joiningDate->format('d M Y') : '—' }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Shift</div>
                            <div class="col-7">
                                @if ($staff->shift)
                                    <span
                                        class="badge badge-inline badge-{{ $staff->shift?->color() }} badge-pill px-2">{{ $staff->shift?->label() }}</span>
                                @else
                                    <span class="info-value">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Working Hours</div>
                            <div class="col-7 info-value">
                                {{ $staff->working_hours ? $staff->working_hours . ' hrs/day' : '—' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Salary</div>
                            <div class="col-7 info-value text-success font-weight-bold">
                                {{ $staff->salary ? number_format($staff->salary, 2) : '—' }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-5 info-label">Weekly Off Day(s)</div>
                            <div class="col-7">
                                @forelse($offdays as $day)
                                    <span class="offday-chip bg-light text-danger border">{{ $day }}</span>
                                @empty
                                    <span class="info-value">—</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Employment Status</div>
                            <div class="col-7">
                                <span
                                    class="badge badge-inline badge-{{ $empStatusColor }} badge-pill px-2">{{ $empStatusLabel }}</span>
                            </div>
                        </div>

                        {{-- Separation info (only if resigned / terminated) --}}
                        @if (in_array($empStatus, ['resigned', 'terminated']))
                            <hr class="my-3">
                            <h6 class="info-heading mb-3">Separation</h6>
                            @if ($staff->resign_date)
                                <div class="row mb-3">
                                    <div class="col-5 info-label">Resign Date</div>
                                    <div class="col-7 info-value">{{ $staff->resign_date->format('d M Y') }}</div>
                                </div>
                            @endif
                            @if ($staff->termination_date)
                                <div class="row mb-3">
                                    <div class="col-5 info-label">Termination Date</div>
                                    <div class="col-7 info-value">{{ $staff->termination_date->format('d M Y') }}</div>
                                </div>
                            @endif
                            @if ($staff->termination_reason)
                                <div class="row mb-3">
                                    <div class="col-5 info-label">Termination Reason</div>
                                    <div class="col-7 info-value">{{ $staff->termination_reason }}</div>
                                </div>
                            @endif
                            @if ($staff->resignation_letter)
                                <div class="row mb-3">
                                    <div class="col-5 info-label">Resignation Letter</div>
                                    <div class="col-7">
                                        <a href="{{ uploaded_asset($staff->resignation_letter) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="las la-download mr-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Right column --}}
                    <div class="col-md-6">
                        <h6 class="info-heading mb-3">Personal</h6>

                        <div class="row mb-3">
                            <div class="col-5 info-label">Email</div>
                            <div class="col-7 info-value">{{ $staff->personal_email ?: '—' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Address</div>
                            <div class="col-7 info-value">{{ $staff->address ?: '—' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Education</div>
                            <div class="col-7">
                                @forelse($educations as $edu)
                                    <span class="edu-tag">{{ $edu }}</span>
                                @empty
                                    <span class="info-value">—</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Date of Birth</div>
                            <div class="col-7 info-value">
                                {{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('d M Y') : '—' }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Gender</div>
                            <div class="col-7 info-value">{{ $user->gender ?: '—' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Blood Group</div>
                            <div class="col-7">
                                @if ($staff->blood_group)
                                    <span
                                        class="badge badge-inline badge-danger badge-pill px-2">{{ $staff->blood_group }}</span>
                                @else
                                    <span class="info-value">—</span>
                                @endif
                            </div>
                        </div>

                        <hr class="my-3">
                        <h6 class="info-heading mb-3">Account</h6>

                        <div class="row mb-3">
                            <div class="col-5 info-label">Email (Official)</div>
                            <div class="col-7 info-value">{{ $user->email ?: '—' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Phone</div>
                            <div class="col-7 info-value">{{ $user->phone ?: '—' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Status</div>
                            <div class="col-7">
                                @if ($user->banned)
                                    <span class="badge badge-inline badge-danger badge-pill px-2">Inactive</span>
                                @else
                                    <span class="badge badge-inline badge-success badge-pill px-2">Active</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Member Since</div>
                            <div class="col-7 info-value">{{ $user->created_at->format('d M Y') }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Last Login</div>
                            <div class="col-7 info-value">
                                {{ $user->recent_login ? \Carbon\Carbon::parse($user->recent_login)->diffForHumans() : '—' }}
                            </div>
                        </div>

                        @if (!empty($staff->note) && $isAdmin)
                            <hr class="my-3">
                            <h6 class="info-heading mb-2">Internal Note</h6>
                            <p class="info-value text-muted" style="white-space:pre-line">{{ $staff->note }}</p>
                        @endif
                    </div>

                </div>
            </div>

            {{-- ===== CONTACT TAB ===== --}}
            <div class="tab-pane fade p-4" id="pane-contact" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="info-heading mb-3">Emergency Contact</h6>

                        @if (array_filter($ec))
                            <div class="border rounded p-3 bg-light">
                                @foreach ([
            'father_name' => "Father's Name",
            'mother_name' => "Mother's Name",
            'spouse_name' => 'Husband / Wife',
            'contact_number' => 'Contact Number',
        ] as $key => $label)
                                    @if (!empty($ec[$key]))
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="info-label mr-2"
                                                style="min-width:120px">{{ $label }}</span>
                                            <span class="info-value">{{ $ec[$key] }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No emergency contact information available.</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="info-heading mb-3">Direct Contact</h6>
                        <div class="d-flex align-items-center mb-3">
                            <span class="attachment-icon bg-soft-primary text-primary mr-3">
                                <i class="las la-envelope"></i>
                            </span>
                            <div>
                                <div class="info-label">Email</div>
                                <div class="info-value">{{ $user->email ?: '—' }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <span class="attachment-icon bg-soft-success text-success mr-3">
                                <i class="las la-phone"></i>
                            </span>
                            <div>
                                <div class="info-label">Phone</div>
                                <div class="info-value">{{ $user->phone ?: '—' }}</div>
                            </div>
                        </div>
                        @if ($staff->address)
                            <div class="d-flex align-items-center mb-3">
                                <span class="attachment-icon bg-soft-warning text-warning mr-3">
                                    <i class="las la-map-marker"></i>
                                </span>
                                <div>
                                    <div class="info-label">Address</div>
                                    <div class="info-value">{{ $staff->address }}</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Bank Account --}}
                    @if (!empty(array_filter($bank)))
                        <div class="col-md-6 mt-3 mt-md-0">
                            <h6 class="info-heading mb-3">Bank Account</h6>
                            <div class="border rounded p-3 bg-light">
                                @if (!empty($bank['bank_name']))
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="info-label mr-2" style="min-width:120px">Bank</span>
                                        <span class="info-value">{{ $bank['bank_name'] }}</span>
                                    </div>
                                @endif
                                @if (!empty($bank['account_no']))
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="info-label mr-2" style="min-width:120px">Account No.</span>
                                        <span class="info-value">{{ $bank['account_no'] }}</span>
                                    </div>
                                @endif
                                @if (!empty($bank['branch']))
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="info-label mr-2" style="min-width:120px">Branch</span>
                                        <span class="info-value">{{ $bank['branch'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- ===== EVENTS TAB ===== --}}
            <div class="tab-pane fade p-4" id="pane-events" role="tabpanel">
                @if ($staff->events->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="las la-calendar-times" style="font-size:3rem"></i>
                        <p class="mt-2">No events recorded.</p>
                    </div>
                @else
                    <div class="event-timeline">
                        @foreach ($staff->events->sortByDesc('updated_at') as $event)
                            <div class="event-timeline-item">
                                <div class="d-flex align-items-start justify-content-between flex-wrap" style="gap:.5rem">
                                    <div>
                                        <div class="font-weight-bold text-dark">{{ $event->title }}</div>
                                        <div class="info-label mt-1">
                                            <i class="las la-calendar mr-1"></i>
                                            {{ $event->updated_at->format('d M Y') }}
                                            <small>({{ $event->updated_at->diffForHumans() }})</small>
                                        </div>
                                    </div>
                                    @if ($event->attachment)
                                        <a href="{{ uploaded_asset($event->attachment) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="las la-paperclip mr-1"></i>Attachment
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ===== ATTACHMENTS TAB ===== --}}
            <div class="tab-pane fade p-4" id="pane-attachments" role="tabpanel">
                @if ($staff->attachments->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="las la-folder-open" style="font-size:3rem"></i>
                        <p class="mt-2">No attachments uploaded.</p>
                    </div>
                @else
                    @foreach ([
            'cv' => ['CV / Resume', 'bg-soft-primary text-primary'],
            'nid' => ['NID', 'bg-soft-warning text-warning'],
            'certificate' => ['Certificates', 'bg-soft-success text-success'],
        ] as $type => [$label, $iconClass])
                        @php $group = $staff->attachments->where('type', $type); @endphp
                        @if ($group->isNotEmpty())
                            <h6 class="info-label mb-3 mt-2">{{ $label }}</h6>
                            <div class="row mb-4">
                                @foreach ($group as $att)
                                    <div class="col-md-6 col-lg-4 mb-2">
                                        <div class="attachment-card">
                                            <div class="attachment-icon {{ $iconClass }}">
                                                <i class="las la-file-alt"></i>
                                            </div>
                                            <div class="flex-1 overflow-hidden">
                                                <div class="info-value text-truncate">
                                                    {{ $att->label ?: strtoupper($type) . ' #' . $loop->iteration }}
                                                </div>
                                                <div class="info-label mt-1">
                                                    {{ $att->created_at->format('d M Y') }}
                                                </div>
                                            </div>
                                            <a href="{{ uploaded_asset($att->upload_id) }}" target="_blank"
                                                class="btn btn-sm btn-light ml-auto flex-shrink-0" title="Download">
                                                <i class="las la-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            {{-- ===== ATTENDANCE TAB ===== --}}
            <div class="tab-pane fade p-4" id="pane-attendance" role="tabpanel">

                {{-- ===== FILTER BAR ===== --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-4" style="gap:10px">

                    <h5 class="info-heading mb-0">Attendance</h5>

                    <div class="d-flex align-items-center" style="gap:10px">
                        <input type="month" id="attendance_month" class="form-control form-control-sm"
                            value="{{ request('month', now()->format('Y-m')) }}"
                            min="{{ $minDate ?? now()->format('Y-m') }}" max="{{ now()->format('Y-m') }}"
                            style="max-width:180px">

                        <button class="btn btn-primary btn-sm" id="attendanceFilterBtn">
                            <i class="las la-search"></i>
                        </button>
                    </div>

                </div>


                {{-- ===== SUMMARY CARDS ===== --}}
                <div class="row mb-4" id="attendanceSummary">

                    <div class="col-md-3 col-6 mb-2">
                        <div class="border rounded p-3 text-center bg-light">
                            <div class="info-label">Working Days</div>
                            <div class="h6 mt-1 mb-0 font-weight-bold text-dark" id="sum_working">—</div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6 mb-2">
                        <div class="border rounded p-3 text-center bg-light">
                            <div class="info-label">Present - Absent</div>
                            {{-- <div class="h5 mb-0 font-weight-bold text-success" id="sum_present">—</div> --}}
                            <div class="h6 mt-1 mb-0 font-weight-bold d-flex justify-content-center">
                                <span id="sum_present" class="text-success" data-toggle="tooltip" title="Present">—</span>
                                <span class="text-muted mx-2">-</span>
                                <span id="sum_absent" class="text-danger" data-toggle="tooltip" title="Absent">—</span>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="col-md-3 col-6 mb-2">
                        <div class="border rounded p-3 text-center bg-light">
                            <div class="info-label">Absent</div>
                            <div class="h5 mb-0 font-weight-bold text-danger" id="sum_absent">—</div>
                        </div>
                    </div> --}}

                    <div class="col-md-3 col-6 mb-2">
                        <div class="border rounded p-3 text-center bg-light">
                            <div class="info-label">Leave (T - P - U)</div>
                            <div class="h6 mt-1 mb-0 font-weight-bold d-flex justify-content-center">
                                <span id="total_leaves" class="text-info" data-toggle="tooltip" title="Total Leaves">—</span>
                                <span class="text-muted mx-2">-</span>
                                <span id="paid_leaves" class="text-success" data-toggle="tooltip" title="Paid Leaves">—</span>
                                <span class="text-muted mx-2">-</span>
                                <span id="unpaid_leaves" class="text-danger" data-toggle="tooltip" title="Unpaid Leaves">—</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6 mb-2">
                        <div class="border rounded p-3 text-center bg-light">
                            <div class="info-label">Overtime</div>
                            <div class="h6 mt-1 mb-0 font-weight-bold text-primary" id="sum_ot">—</div>
                        </div>
                    </div>
                </div>


                {{-- ===== PRELOADER ===== --}}
                <div id="attendanceLoader" class="text-center py-5" style="display:none;">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Loading attendance...</p>
                </div>


                {{-- ===== DATA LIST ===== --}}
                <div id="attendanceContent">

                    {{-- AJAX WILL LOAD HERE --}}

                </div>

            </div>

            {{-- ===== APPLICATIONS TAB ===== --}}
            <div class="tab-pane fade p-4" id="pane-applications" role="tabpanel">

                {{-- ===== FILTER BAR ===== --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-4" style="gap:10px">

                    <h5 class="info-heading mb-0">Applications</h5>

                    <div class="d-flex align-items-center" style="gap:10px">
                        <input type="month" id="application_month" class="form-control form-control-sm"
                            value="{{ now()->format('Y-m') }}" max="{{ now()->format('Y-m') }}"
                            style="max-width:180px">

                        @if ($isAdmin || Auth::id() == $user->id)
                            <button class="btn btn-primary btn-sm" id="create-application-btn" data-toggle="tooltip"
                                data-title="Create Application">
                                <i class="las la-plus"></i>
                            </button>
                        @endif
                    </div>

                </div>

                {{-- ===== PRELOADER ===== --}}
                <div id="applicationLoader" class="text-center py-5" style="display:none;">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Loading applications...</p>
                </div>

                {{-- ===== DATA LIST ===== --}}
                <div id="applicationContent">

                    {{-- AJAX WILL LOAD HERE --}}

                </div>

            </div>

            {{-- ===== SALARY TAB ===== --}}
            <div class="tab-pane fade p-4" id="pane-salary" role="tabpanel">

                {{-- ===== FILTER BAR ===== --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-4" style="gap:10px">

                    <h5 class="info-heading mb-0">Salary Sheets</h5>

                    <div class="d-flex align-items-center" style="gap:10px">
                        <input type="month" id="salary_month" class="form-control form-control-sm"
                            value="{{ request('month', now()->format('Y-m')) }}"
                            min="{{ $minDate ?? now()->format('Y-m') }}" max="{{ now()->format('Y-m') }}"
                            style="max-width:180px">

                        <button class="btn btn-primary btn-sm" id="salaryFilterBtn">
                            <i class="las la-search"></i>
                        </button>
                    </div>

                </div>

                {{-- ===== PRELOADER ===== --}}
                <div id="salaryLoader" class="text-center py-5" style="display:none;">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Loading salary sheets...</p>
                </div>

                {{-- ===== DATA LIST ===== --}}
                <div id="salaryContent">

                    {{-- AJAX WILL LOAD HERE --}}

                </div>

            </div>

            @if (Auth::user()->user_type === 'admin' || in_array('edit_staff', $_authPermissions))
                {{-- ===== ACTION TAB ===== --}}
                <div class="tab-pane fade p-4" id="pane-actions" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4" style="gap:10px">
                        <h5 class="info-heading mb-0">Actions</h5>
                    </div>

                    <div class="actions-toolbar d-flex justify-content-between align-items-center flex-wrap">
                        <small class="text-muted">Use quick HR actions for documents, communication, and lifecycle
                            updates.</small>
                    </div>

                    <div>
                        <div class="action-grid">
                            <div class="action-card">
                                <div class="action-card-inner">
                                    <span class="action-icon bg-soft-secondary text-secondary"><i
                                            class="las la-file-signature"></i></span>
                                    <div class="action-card-content">
                                        <div class="action-card-info">
                                            <div class="action-title">Appointment Letter</div>
                                            <p class="action-subtitle">Create and deliver appointment documents for
                                                onboarding.</p>
                                        </div>
                                        <div class="action-card-actions">
                                            <button type="button" class="btn btn-soft-secondary btn-action"
                                                id="generate-appointment-btn" data-type="appointment-letter">
                                                <i class="las la-magic mr-1"></i>Generate
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-action"
                                                id="send-appointment-btn" data-type="appointment-letter">
                                                <i class="las la-paper-plane mr-1"></i>Send
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="action-card">
                                <div class="action-card-inner">
                                    <span class="action-icon bg-soft-success text-success"><i
                                            class="las la-user-check"></i></span>
                                    <div class="action-card-content">
                                        <div class="action-card-info">
                                            <div class="action-title">Joining Letter</div>
                                            <p class="action-subtitle">Issue joining confirmation and share it with the
                                                staff.</p>
                                        </div>
                                        <div class="action-card-actions">
                                            <button type="button" class="btn btn-soft-success btn-action"
                                                data-type="joining-letter" id="generate-joining-btn">
                                                <i class="las la-magic mr-1"></i>Generate
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-action"
                                                id="send-joining-btn" data-type="joining-letter">
                                                <i class="las la-paper-plane mr-1"></i>Send
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="action-card" style="display: none !important;">
                                <div class="action-card-inner">
                                    <span class="action-icon bg-soft-danger text-danger"><i
                                            class="las la-file-export"></i></span>
                                    <div class="action-card-content">
                                        <div class="action-card-info">
                                            <div class="action-title">Resignation Letter</div>
                                            <p class="action-subtitle">Prepare separation document and send with approved
                                                details.</p>
                                        </div>
                                        <div class="action-card-actions">
                                            <button type="button" class="btn btn-soft-danger btn-action"
                                                data-type="resignation-letter" id="generate-resignation-btn"
                                                data-toggle="tooltip" title="Upcoming">
                                                <i class="las la-magic mr-1"></i>Generate
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-action"
                                                data-toggle="tooltip" data-title="Send resignation letter"
                                                id="send-resignation-btn">
                                                <i class="las la-paper-plane mr-1"></i>Send
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="action-card">
                                <div class="action-card-inner">
                                    <span class="action-icon bg-soft-warning text-warning"><i
                                            class="las la-stamp"></i></span>
                                    <div class="action-card-content">
                                        <div class="action-card-info">
                                            <div class="action-title">No Objection Certificate</div>
                                            <p class="action-subtitle">Issue NOC letter for visa, transfer, or official
                                                procedures.</p>
                                        </div>
                                        <div class="action-card-actions">
                                            <button type="button" class="btn btn-soft-warning btn-action"
                                                id="generate-noc-btn">
                                                <i class="las la-magic mr-1"></i>Generate
                                            </button>
                                            <button type="button" class="btn btn-outline-warning btn-action"
                                                id="send-noc-btn" data-type="noc">
                                                <i class="las la-paper-plane mr-1"></i>Send
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="action-card">
                                <div class="action-card-inner">
                                    <span class="action-icon bg-soft-primary text-primary"><i
                                            class="las la-medal"></i></span>
                                    <div class="action-card-content">
                                        <div class="action-card-info">
                                            <div class="action-title">Promotion</div>
                                            <p class="action-subtitle">Generate promotion letter.</p>
                                        </div>
                                        <div class="action-card-actions">
                                            <button type="button" class="btn btn-soft-primary btn-action"
                                                id="generate-promotion-btn" data-type="promotion-letter">
                                                <i class="las la-magic mr-1"></i>Generate
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-action"
                                                id="send-promotion-btn" data-type="promotion-letter">
                                                <i class="las la-paper-plane mr-1"></i>Send
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="action-card">
                                <div class="action-card-inner">
                                    <span class="action-icon bg-soft-info text-info"><i
                                            class="las la-money-bill"></i></span>
                                    <div class="action-card-content">
                                        <div class="action-card-info">
                                            <div class="action-title">Increment</div>
                                            <p class="action-subtitle">Generate salary increment letter.</p>
                                        </div>
                                        <div class="action-card-actions">
                                            <button type="button" class="btn btn-soft-info btn-action"
                                                id="generate-increment-btn" data-type="increment-letter">
                                                <i class="las la-magic mr-1"></i>Generate
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-action"
                                                id="send-increment-btn" data-type="increment-letter">
                                                <i class="las la-paper-plane mr-1"></i>Send
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="action-card" style="display: none !important;">
                                <div class="action-card-inner">
                                    <span class="action-icon bg-soft-dark text-dark"><i
                                            class="las la-clipboard-check"></i></span>
                                    <div class="action-card-content">
                                        <div class="action-card-info">
                                            <div class="action-title">Experience / Service Letter</div>
                                            <p class="action-subtitle">Create verified service documents for current or
                                                former staff.</p>
                                        </div>
                                        <div class="action-card-actions">
                                            <button type="button" class="btn btn-soft-secondary btn-action"
                                                data-toggle="tooltip" title="Upcoming" id="generate-experience-btn">
                                                <i class="las la-magic mr-1"></i>Generate
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-action"
                                                data-toggle="tooltip" data-title="Send experience letter"
                                                id="send-experience-btn">
                                                <i class="las la-paper-plane mr-1"></i>Send
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>{{-- end tab-content --}}
    </div>

@endsection

@section('modal')
    <div class="modal fade" id="attendance-edit-modal" tabindex="-1" role="dialog"
        aria-labelledby="checkInModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkInModalLabel">Edit Attendance</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div>
                        <ul id="attendance-errors" class="border border-danger rounded bg-soft-danger py-3 text-danger"
                            style="display:none;"></ul>
                    </div>
                    <form action="" id="attendance-edit-form">
                        <input type="hidden" value="" id="attendance_id" name="attendance_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="check-in-time">{{ __('Check In') }}</label>
                                    <input type="datetime-local" id="check-in-time" name="check_in"
                                        class="form-control form-control-sm">
                                    <small class="text-danger" id="check-in-time-error"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="check-out-time">{{ __('Check Out') }}</label>
                                    <input type="datetime-local" id="check-out-time" name="check_out"
                                        class="form-control form-control-sm">
                                    <small class="text-danger" id="check-out-time-error"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="check-out-type">{{ __('Attendance Type') }}</label>
                                    <select name="check_out_type" id="check-out-type"
                                        class="form-control form-control-sm">
                                        <option value="regular">{{ __('Regular') }}</option>
                                        <option value="alternative">{{ __('Alternative') }}</option>
                                    </select>
                                    <small class="text-danger" id="check-out-type-error"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="check-in-alter-date">
                                        {{ __('Alternative Date') }}
                                    </label>
                                    <input type="date" id="attendance-alter-date" name="alternative_date"
                                        class="form-control form-control-sm" placeholder="{{ __('Select a date') }}">
                                    <small class="text-danger" id="attendance-alter-date-error"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="check-out-type">{{ __('Shift') }}</label>
                                    <select name="shift" id="attendance-shift" class="form-control form-control-sm">
                                        <option value="">{{ 'Select Shift' }}</option>
                                        @foreach (\App\Enums\ShiftEnum::options() as $value => $label)
                                            <option value="{{ $value }}" @selected(old('shift') === $value)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger" id="attendance-shift-error"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="check-out-type">{{ __('Status') }} *</label>
                                    <select name="status" id="attendance-status" class="form-control form-control-sm">
                                        <option value="">Select Status</option>
                                        <option value="present" @selected(old('status') === 'present')>{{ __('Present') }}</option>
                                        <option value="absent" @selected(old('status') === 'absent')>{{ __('Absent') }}</option>
                                        <option value="leave" @selected(old('status') === 'leave')>{{ __('Leave') }}</option>
                                        <option value="offday" @selected(old('status') === 'offday')>{{ __('Off Day') }}</option>
                                        <option value="holiday" @selected(old('status') === 'holiday')>{{ __('Holiday') }}</option>
                                    </select>
                                    <small class="text-danger" id="attendance-status-error"></small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="check-in-alter-note">
                                        {{ __('Note') }}
                                    </label>
                                    <textarea rows="3" id="attendance-alter-note" class="form-control form-control-sm"
                                        placeholder="{{ __('Enter a note') }}"></textarea>
                                    <small class="text-danger" id="attendance-alter-note-error"></small>
                                </div>
                            </div>
                            <div class="col-md-12" id="overtime-records">
                                {{-- Here Will show all overtime records --}}
                                <h6>
                                    Overtime Records
                                </h6>
                                <small class="overtimes-error text-danger"></small>
                                <div class="overtimes-target" id="overtimes-target">
                                    <div class="row gutters-5">
                                        <div class="col">
                                            <div class="form-group">
                                                <label for="overtime-start-time">{{ __('Start Time') }}</label>
                                                <input type="time" name="overtime_start_times[]"
                                                    class="form-control form-control-sm">
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-group">
                                                <label for="overtime-end-time">{{ __('End Time') }}</label>
                                                <input type="time" name="overtime_end_times[]"
                                                    class="form-control form-control-sm">
                                            </div>
                                        </div>
                                        <div class="col-auto d-flex align-items-center">
                                            <button type="button"
                                                class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger remove-overtime">
                                                <i class="las la-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-soft-secondary btn-sm" id="add-more-overtimes">
                                    Add New
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light"
                        data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-sm btn-success" id="btn-update-attendance">
                        {{ __('Update') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="applicationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form id="application-form">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Create Application</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body" style="min-height: 270px !important;">
                        <input type="hidden" name="user_id" value="{{ $user->id }}">

                        <!-- Application Type -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Application Type *</label>
                            <div class="col-md-9">
                                <select name="application_type" id="application_type"
                                    class="form-control aiz-selectpicker" data-live-search="true">
                                    <option value="">Select</option>
                                    @foreach (\App\Enums\ApplicationTypes::options() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Leave Section -->
                        <div id="leave_section" class="d-none">

                            <!-- Duration -->
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Duration</label>
                                <div class="col-md-9">

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input duration" type="radio" name="duration"
                                            value="single" checked>
                                        <label class="form-check-label">Single Day</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input duration" type="radio" name="duration"
                                            value="multiple">
                                        <label class="form-check-label">Multiple Days</label>
                                    </div>

                                </div>
                            </div>

                            <!-- Single -->
                            <div id="single_day_section" class="date_section">
                                <div class="form-group row">
                                    <label class="col-md-3 col-form-label">Date</label>
                                    <div class="col-md-9">
                                        <input type="date" name="single_date" id="single_date" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <!-- Multiple -->
                            <div id="multiple_days_section" class="date_section d-none">
                                <div class="form-group row">
                                    <label class="col-md-3 col-form-label">Start</label>
                                    <div class="col-md-9">
                                        <input type="date" name="start_date" id="start_date" class="form-control">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-3 col-form-label">End</label>
                                    <div class="col-md-9">
                                        <input type="date" name="end_date" id="end_date" class="form-control">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Subject -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Subject *</label>
                            <div class="col-md-9">
                                <input type="text" name="subject" placeholder="Enter subject" class="form-control"
                                    required>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Reason *</label>
                            <div class="col-md-9">
                                <textarea name="reason" class="form-control" placeholder="Enter reason" required></textarea>
                            </div>
                        </div>

                        <!-- Attachments -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Attachments</label>
                            <div class="col-md-9">
                                <div class="input-group" data-toggle="aizuploader" data-multiple="true">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            Browse
                                        </div>
                                    </div>
                                    <div class="form-control file-amount">Choose Files</div>
                                    <input type="hidden" name="attachments" value="" class="selected-files">
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>

                        <!-- Loader -->
                        <div id="form-loader" class="text-center d-none">
                            <span class="spinner-border spinner-border-sm"></span> Processing...
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="submit-btn">Submit</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <div class="modal fade" id="applicationEditModal" tabindex="-1" role="dialog"
        aria-labelledby="applicationEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="application-edit-form">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="application_id" value="">

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Application</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body" style="min-height: 270px !important;">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Application Type *</label>
                            <div class="col-md-9">
                                <select name="application_type" id="edit_application_type"
                                    class="form-control aiz-selectpicker" data-live-search="true">
                                    <option value="">Select</option>
                                    @foreach (\App\Enums\ApplicationTypes::options() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="edit_leave_section" class="d-none">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Duration</label>
                                <div class="col-md-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input edit-duration" type="radio" name="duration"
                                            value="single" checked>
                                        <label class="form-check-label">Single Day</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input edit-duration" type="radio" name="duration"
                                            value="multiple">
                                        <label class="form-check-label">Multiple Days</label>
                                    </div>
                                </div>
                            </div>

                            <div id="edit_single_day_section" class="date_section">
                                <div class="form-group row">
                                    <label class="col-md-3 col-form-label">Date</label>
                                    <div class="col-md-9">
                                        <input type="date" name="single_date" id="edit_single_date"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div id="edit_multiple_days_section" class="date_section d-none">
                                <div class="form-group row">
                                    <label class="col-md-3 col-form-label">Start</label>
                                    <div class="col-md-9">
                                        <input type="date" name="start_date" id="edit_start_date"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-3 col-form-label">End</label>
                                    <div class="col-md-9">
                                        <input type="date" name="end_date" id="edit_end_date" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Subject *</label>
                            <div class="col-md-9">
                                <input type="text" name="subject" id="edit_subject" class="form-control"
                                    placeholder="Enter subject">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Reason *</label>
                            <div class="col-md-9">
                                <textarea name="reason" id="edit_reason" class="form-control" placeholder="Enter reason"></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Attachments</label>
                            <div class="col-md-9">
                                <div class="input-group" data-toggle="aizuploader" data-multiple="true">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            Browse
                                        </div>
                                    </div>
                                    <div class="form-control file-amount" id="edit-file-amount">Choose Files</div>
                                    <input type="hidden" name="attachments" value="" class="selected-files">
                                </div>
                                <div class="file-preview box sm" id="edit-file-preview"></div>
                            </div>
                        </div>

                        <div id="application-edit-loader" class="text-center d-none">
                            <span class="spinner-border spinner-border-sm"></span> Updating...
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="application-edit-submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="apptModal" tabindex="-1" role="dialog" aria-labelledby="apptEditLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="apptModalTitle"><i class="las la-file-signature"></i> Generate
                        Appointment Letter</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="type" id="eventType" value="appointment-letter">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Position</label>
                                <input name="position" value="{{ $staff->role->name }}"
                                    class="form-control form-control-sm" placeholder="Job Role" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="joiningDate" id="joiningDate"
                                    value="{{ $staff->joining_date }}" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Time <span class="text-danger">*</span></label>
                                <input type="time" name="joiningTime" id="joiningTime"
                                    value="{{ $staff->shift?->reportingTime() }}" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-primary" id="apptSendBtn">
                        <i class="las la-paper-plane mr-2"></i>Generate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="nocModal" tabindex="-1" role="dialog" aria-labelledby="nocEditLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="nocModalTitle"><i class="las la-stamp"></i> Generate No Objection
                        Certificate</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Purpose (Short) <span class="text-danger">*</span></label>
                                <input id="purposeShort" class="form-control form-control-sm"
                                    placeholder="E.g. Visa Application, Job Transfer, etc.">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Purpose (Detail) <span class="text-danger">*</span></label>
                                <textarea id="purpose" class="form-control form-control-sm" rows="3"
                                    placeholder="Enter detailed purpose..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-primary" id="nocSendBtn">
                        <i class="las la-paper-plane mr-2"></i>Generate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="proModal" tabindex="-1" role="dialog" aria-labelledby="proEditLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <input type="hidden" id="proType" value="promotion-letter">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="proModalTitle"><i class="las la-stamp"></i> Generate Promotion Letter
                        Certificate</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="min-height: 260px !important;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Current Position</label>
                                <input class="form-control form-control-sm" placeholder="{{ $staff->role?->name }}" value="{{ $staff->role?->name }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Current Salary</label>
                                <input class="form-control form-control-sm" placeholder="{{ $staff->salary }}" value="{{ $staff->salary }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6" id="positionSection">
                            <div class="form-group">
                                <label class="form-label">New Position <span class="text-danger">*</span></label>
                                <select id="newPosition" class="form-control form-control-sm aiz-selectpicker" data-live-search="true">
                                    <option value="">Select New Position</option>
                                    @foreach (\App\Models\Role::pluck('name', 'id') as $roleId => $roleName)
                                        <option value="{{ $roleId }}">{{ $roleName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="salarySection">
                            <div class="form-group">
                                <label class="form-label">New Salary <span class="text-danger">*</span></label>
                                <input type="number" min="{{ $staff->salary }}" step="100" class="form-control form-control-sm" id="newSalary" placeholder="Enter New Salary" value="{{ $staff->salary }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Effective Date <span class="text-danger">*</span></label>
                                <input class="form-control form-control-sm" type="date" id="effectiveDate" value="{{ now()->toDateString() }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-primary" id="proSendBtn">
                        <i class="las la-paper-plane mr-2"></i>Generate
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @if (get_setting('enable_application_management', 0) == 1)
        <script>
            $(function() {
                $(document).ready(function() {
                    const createModal = $('#applicationModal');
                    const createForm = $('#application-form');
                    const createLoader = $('#form-loader');
                    const createSubmitBtn = $('#submit-btn');
                    const applicationContainer = $('#applicationContent');
                    const editModal = $('#applicationEditModal');
                    const editForm = $('#application-edit-form');
                    const editLoader = $('#application-edit-loader');
                    const editSubmitBtn = $('#application-edit-submit');
                    const statusColors = {
                        pending: 'warning',
                        approved: 'success',
                        rejected: 'danger',
                        cancelled: 'dark'
                    };

                    function parsePayload(payload) {
                        if (!payload) {
                            return null;
                        }

                        if (typeof payload === 'string') {
                            try {
                                return JSON.parse(payload);
                            } catch (error) {
                                console.error('Unable to parse payload', error);
                                return null;
                            }
                        }

                        return payload;
                    }

                    function loadApplications() {

                        let month = $('#application_month').val();

                        $('#applicationLoader').show();
                        $('#applicationContent').hide();

                        $.ajax({
                            url: "{{ route('applications.filter', $user->id) }}",
                            data: {
                                month: month
                            },
                            success: function(res) {
                                applicationContainer.html(res.html);
                            },
                            complete: function() {
                                $('#applicationLoader').hide();
                                applicationContainer.fadeIn(150);
                                applicationContainer.find('[data-toggle="tooltip"]').tooltip();
                            }
                        });
                    }

                    function toggleEditSections(type) {
                        if (type === 'multiple') {
                            $('#edit_single_day_section').addClass('d-none');
                            $('#edit_multiple_days_section').removeClass('d-none');
                        } else {
                            $('#edit_single_day_section').removeClass('d-none');
                            $('#edit_multiple_days_section').addClass('d-none');
                        }
                    }

                    function resetCreateForm() {
                        createForm[0].reset();
                        createForm.find('.duration[value="single"]').prop('checked', true);
                        $('#leave_section').addClass('d-none');
                        $('#single_day_section').removeClass('d-none');
                        $('#multiple_days_section').addClass('d-none');
                        createForm.find('.is-invalid').removeClass('is-invalid');
                        createForm.find('.invalid-feedback').remove();
                    }

                    function resetEditForm() {
                        editForm[0].reset();
                        editForm.find('input[name="application_id"]').val('');
                        editForm.find('.edit-duration[value="single"]').prop('checked', true);
                        $('#edit_leave_section').addClass('d-none');
                        toggleEditSections('single');
                        editForm.find('input[name="attachments"]').val('');
                        refreshEditUploaderPreview();
                        editForm.find('.is-invalid').removeClass('is-invalid');
                        editForm.find('.invalid-feedback').remove();
                    }

                    function refreshEditUploaderPreview() {
                        if (typeof AIZ === 'undefined' || !AIZ.uploader || typeof AIZ.uploader
                            .previewGenerate !== 'function') {
                            return;
                        }

                        const uploaderWrapper = editForm.find('[data-toggle="aizuploader"]');
                        if (!uploaderWrapper.length) {
                            return;
                        }

                        AIZ.uploader.previewGenerate();
                    }

                    function populateEditForm(application) {
                        resetEditForm();

                        editForm.find('input[name="application_id"]').val(application.id || '');
                        const typeSelect = editForm.find('select[name="application_type"]');
                        typeSelect.val(application.type || '');
                        if (typeof $.fn.selectpicker !== 'undefined') {
                            typeSelect.selectpicker('refresh');
                        }

                        editForm.find('input[name="subject"]').val(application.subject || '');
                        editForm.find('textarea[name="reason"]').val(application.reason || '');
                        editForm.find('input[name="attachments"]').val(application.attachment_ids || '');
                        refreshEditUploaderPreview();

                        if (application.type === 'leave') {
                            $('#edit_leave_section').removeClass('d-none');
                            const leaveData = application.leave || {};
                            const durationType = leaveData.type || 'single';
                            editForm.find(`.edit-duration[value="${durationType}"]`).prop('checked', true);
                            $('#edit_single_date').val(leaveData.start_date || '');
                            $('#edit_start_date').val(leaveData.start_date || '');
                            $('#edit_end_date').val(leaveData.end_date || '');
                            toggleEditSections(durationType);
                        }
                    }

                    function handleErrors(xhr, targetForm) {
                        const errors = xhr.responseJSON?.errors;
                        targetForm.find('.is-invalid').removeClass('is-invalid');
                        targetForm.find('.invalid-feedback').remove();

                        if (errors) {
                            $.each(errors, function(key, value) {
                                const field = targetForm.find(`[name="${key}"]`);
                                if (field.length) {
                                    field.addClass('is-invalid');
                                    const feedback = $('<div class="invalid-feedback d-block"></div>')
                                        .text(value[0]);
                                    if (field.parent('.input-group').length) {
                                        field.parent().after(feedback);
                                    } else {
                                        field.after(feedback);
                                    }
                                }
                            });
                        }
                        showAlert('error', xhr.responseJSON?.message || 'Something went wrong.')
                    }

                    $('#application_month').change(loadApplications);
                    loadApplications();

                    applicationContainer.on('click', '.edit-application', function() {
                        const application = parsePayload($(this).attr('data-application'));
                        if (!application) {
                            return;
                        }
                        populateEditForm(application);
                        editModal.modal('show');
                    });

                    applicationContainer.on('click', '.delete-application', function() {
                        const id = $(this).data('id');

                        Swal.fire({
                            title: 'Are You Sure?',
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, Delete It!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const url = "{{ route('applications.destroy', ':id') }}"
                                    .replace(':id', id);
                                $.ajax({
                                    url: url,
                                    method: 'DELETE',
                                    success: function(res) {
                                        if (res.success) {
                                            loadApplications();
                                            showAlert('success', res.message)
                                        } else {
                                            showAlert('error', res.message)
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        console.error(error);
                                        showAlert('error', xhr.responseJSON
                                            ?.message || 'Something went wrong.'
                                        )
                                    }
                                });
                            }
                        })
                    });

                    $('#create-application-btn').on('click', function() {
                        resetCreateForm();
                        createModal.modal('show');
                    });

                    $('#application_type').on('change', function() {
                        let type = $(this).val();

                        if (type === 'leave') {
                            $('#leave_section').removeClass('d-none');
                        } else {
                            $('#leave_section').addClass('d-none');
                        }
                    });

                    $('#edit_application_type').on('change', function() {
                        if ($(this).val() === 'leave') {
                            $('#edit_leave_section').removeClass('d-none');
                        } else {
                            $('#edit_leave_section').addClass('d-none');
                        }
                    });

                    $(document).on('change', '.duration', function() {
                        const type = $(this).val();
                        $('#single_day_section, #multiple_days_section').addClass('d-none');
                        if (type === 'single') {
                            $('#single_day_section').removeClass('d-none');
                        } else if (type === 'multiple') {
                            $('#multiple_days_section').removeClass('d-none');
                        }
                    });

                    editForm.on('change', '.edit-duration', function() {
                        toggleEditSections($(this).val());
                    });

                    createForm.on('submit', function(e) {
                        e.preventDefault();

                        let isValid = true;
                        const typeField = createForm.find('select[name="application_type"]');
                        const subjectField = createForm.find('input[name="subject"]');
                        const reasonField = createForm.find('textarea[name="reason"]');
                        const singleDateField = createForm.find('input[name="single_date"]');
                        const startDateField = createForm.find('input[name="start_date"]');
                        const endDateField = createForm.find('input[name="end_date"]');
                        const duration = createForm.find('.duration:checked').val();

                        createForm.find('.is-invalid').removeClass('is-invalid');
                        createForm.find('.invalid-feedback').remove();

                        if (!typeField.val()) {
                            typeField.addClass('is-invalid');
                            typeField.after(
                                '<div class="invalid-feedback">Select an application type</div>');
                            isValid = false;
                        }

                        if (!subjectField.val()) {
                            subjectField.addClass('is-invalid');
                            subjectField.after(
                                '<div class="invalid-feedback">Subject is required</div>');
                            isValid = false;
                        }

                        if (!reasonField.val()) {
                            reasonField.addClass('is-invalid');
                            reasonField.after('<div class="invalid-feedback">Reason is required</div>');
                            isValid = false;
                        }

                        if (typeField.val() === 'leave') {
                            if (duration === 'single' && !singleDateField.val()) {
                                singleDateField.addClass('is-invalid');
                                singleDateField.after(
                                    '<div class="invalid-feedback">Date is required</div>');
                                isValid = false;
                            }
                            if (duration === 'multiple' && (!startDateField.val() || !endDateField
                                    .val())) {
                                startDateField.addClass('is-invalid');
                                startDateField.after(
                                    '<div class="invalid-feedback">Start date is required</div>');
                                endDateField.addClass('is-invalid');
                                endDateField.after(
                                    '<div class="invalid-feedback">End date is required</div>');
                                isValid = false;
                            }
                        }

                        if (!isValid) {
                            return;
                        }

                        createLoader.removeClass('d-none');
                        createSubmitBtn.prop('disabled', true);

                        $.ajax({
                            url: "{{ route('applications.store') }}",
                            method: 'POST',
                            data: createForm.serialize(),
                            success: function(res) {
                                createModal.modal('hide');
                                loadApplications();
                                showAlert('success', res.message ||
                                    'Application submitted');
                            },
                            error: function(xhr) {
                                handleErrors(xhr, createForm);
                            },
                            complete: function() {
                                createLoader.addClass('d-none');
                                createSubmitBtn.prop('disabled', false);
                            }
                        });
                    });

                    editForm.on('submit', function(e) {
                        e.preventDefault();

                        const typeField = editForm.find('select[name="application_type"]');
                        const subjectField = editForm.find('input[name="subject"]');
                        const reasonField = editForm.find('textarea[name="reason"]');
                        const singleDateField = editForm.find('#edit_single_date');
                        const startDateField = editForm.find('#edit_start_date');
                        const endDateField = editForm.find('#edit_end_date');
                        const duration = editForm.find('.edit-duration:checked').val();
                        let isValid = true;

                        editForm.find('.is-invalid').removeClass('is-invalid');
                        editForm.find('.invalid-feedback').remove();

                        if (!typeField.val()) {
                            typeField.addClass('is-invalid');
                            typeField.after(
                                '<div class="invalid-feedback d-block">Select an application type</div>'
                            );
                            isValid = false;
                        }

                        if (!subjectField.val()) {
                            subjectField.addClass('is-invalid');
                            subjectField.after(
                                '<div class="invalid-feedback">Subject is required</div>');
                            isValid = false;
                        }

                        if (!reasonField.val()) {
                            reasonField.addClass('is-invalid');
                            reasonField.after('<div class="invalid-feedback">Reason is required</div>');
                            isValid = false;
                        }

                        if (typeField.val() === 'leave') {
                            if (duration === 'single' && !singleDateField.val()) {
                                singleDateField.addClass('is-invalid');
                                singleDateField.after(
                                    '<div class="invalid-feedback">Date is required</div>');
                                isValid = false;
                            }
                            if (duration === 'multiple' && (!startDateField.val() || !endDateField
                                    .val())) {
                                startDateField.addClass('is-invalid');
                                startDateField.after(
                                    '<div class="invalid-feedback">Start date is required</div>');
                                endDateField.addClass('is-invalid');
                                endDateField.after(
                                    '<div class="invalid-feedback">End date is required</div>');
                                isValid = false;
                            }
                        }

                        if (!isValid) {
                            return;
                        }

                        const applicationId = editForm.find('input[name="application_id"]').val();
                        if (!applicationId) {
                            showAlert('error', 'Unable to determine the application to update.');
                            return;
                        }

                        editLoader.removeClass('d-none');
                        editSubmitBtn.prop('disabled', true);

                        $.ajax({
                            url: "{{ route('applications.update', ':id') }}".replace(':id',
                                applicationId),
                            method: 'POST',
                            data: editForm.serialize(),
                            success: function(res) {
                                editModal.modal('hide');
                                loadApplications();
                                showAlert('success', res.message || 'Application updated');
                            },
                            error: function(xhr) {
                                handleErrors(xhr, editForm);
                            },
                            complete: function() {
                                editLoader.addClass('d-none');
                                editSubmitBtn.prop('disabled', false);
                            }
                        });
                    });

                    editModal.on('hidden.bs.modal', function() {
                        resetEditForm();
                    });

                    if (typeof AIZ !== 'undefined' && AIZ.plugins && AIZ.plugins.aizUploader) {
                        AIZ.plugins.aizUploader();
                    }

                    function handleDocumentModal(type) {
                        const modal = $('#apptModal');
                        const timeValue = '{{ $staff->shift?->reportingTime() }}';
                        const dateValue = '{{ $staff->joining_date?->format('Y-m-d') }}';

                        $('#eventType').val(type);
                        $('#joiningDate').val(dateValue).trigger('change'); // Add trigger
                        $('#joiningTime').val(timeValue).trigger('change'); // Add trigger

                        modal.modal('show');
                    }

                    $('#generate-appointment-btn, #generate-joining-btn').on('click', function() {
                        const type = $(this).data('type');
                        if (type === 'joining-letter') {
                            $('#apptModalTitle').html(
                                '<i class="las la-file-signature"></i> Generate Joining Letter');
                        } else {
                            $('#apptModalTitle').html(
                                '<i class="las la-file-signature"></i> Generate Appointment Letter');
                        }
                        handleDocumentModal(type);
                    });

                    $('#generate-promotion-btn, #generate-increment-btn').on('click', function() {
                        const type = $(this).data('type');
                        $('#newPosition').val('');
                        $('#newSalary').val('{{ $staff->salary }}');
                        $('#positionSection, #salarySection').removeClass('d-none col-12').addClass('col-md-6');
                        if (type === 'promotion-letter') {
                            $('#proModalTitle').html('<i class="las la-file-signature"></i> Generate Promotion Letter');
                        } else {
                            $('#positionSection').addClass('d-none');
                            $('#salarySection').addClass('col-12').removeClass('col-md-6');
                            $('#proModalTitle').html('<i class="las la-file-signature"></i> Generate Increment Letter');
                        }
                        $('#proType').val(type);
                        $('#proModal').modal('show');
                    });

                    $('#generate-noc-btn').on('click', function() {
                        $('#nocModal').modal('show');
                    });

                    $('#apptSendBtn').on('click', function() {
                        const date = $('#joiningDate').val();
                        const time = $('#joiningTime').val();
                        const type = $('#eventType').val();

                        if (!date || !time) {
                            !date ? $('#joiningDate').focus() : $('#joiningTime').focus();
                            AIZ.plugins.notify('danger', 'Please select a date and time.');
                            return;
                        }

                        $(this).prop('disabled', true).html(
                            '<i class="las la-spinner la-spin"></i> Generating...');
                        $.ajax({
                            url: '{{ route('staffs.generate_doc', ':id') }}'.replace(':id',
                                {{ $staff->id }}),
                            method: 'POST',
                            data: {
                                date,
                                time,
                                type
                            },
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    AIZ.plugins.notify('success', response.message ||
                                        'Document generated successfully.');
                                    $('#apptModal').modal('hide');
                                }
                            },
                            error: function(xhr, status, error) {
                                const errorMessage = xhr.responseJSON?.message ||
                                    'Failed to generate. Please try again.';
                                AIZ.plugins.notify('danger', errorMessage);
                            },
                            complete: function() {
                                $('#apptSendBtn').prop('disabled', false).html(
                                    '<i class="las la-paper-plane"></i> Generate');
                            }
                        });
                    });

                    $('#proSendBtn').on('click', function() {
                        const type = $('#proType').val();
                        const role_id = $('#newPosition').val();
                        const new_role = $('#newPosition option:selected').text();
                        const new_salary = parseFloat($('#newSalary').val() || 0);
                        const effective_date = $('#effectiveDate').val();

                        if (!new_salary || !effective_date) {
                            AIZ.plugins.notify('danger', 'Please fill in all required fields.');
                            return;
                        }

                        if (type === 'promotion-letter' && !role_id) {
                            AIZ.plugins.notify('danger', 'Please select the new position.');
                            $('#newPosition').focus();
                            return;
                        }

                        if (new_salary < parseFloat('{{ $staff->salary }}')) {
                            AIZ.plugins.notify('danger', 'New salary must be greater than or equal to current salary.');
                            $('#newSalary').focus();
                            return;
                        }

                        $(this).prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Generating...');
                        $.ajax({
                            url: '{{ route('staffs.generate_doc', ':id') }}'.replace(':id', {{ $staff->id }}),
                            method: 'POST',
                            data: {
                                new_role,
                                new_salary,
                                effective_date,
                                type
                            },
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    AIZ.plugins.notify('success', response.message ||
                                        'Document generated successfully.');
                                    $('#proModal').modal('hide');
                                }
                            },
                            error: function(xhr, status, error) {
                                const errorMessage = xhr.responseJSON?.message ||
                                    'Failed to generate. Please try again.';
                                AIZ.plugins.notify('danger', errorMessage);
                            },
                            complete: function() {
                                $('#proSendBtn').prop('disabled', false).html('<i class="las la-paper-plane"></i> Generate');
                            }
                        });
                    });

                    $('#nocSendBtn').on('click', function() {
                        const purpose_short = $('#purposeShort').val().trim();
                        const purpose = $('#purpose').val().trim();

                        if (!purpose_short || !purpose) {
                            AIZ.plugins.notify('danger', 'Please fill in all required fields.');
                            return;
                        }

                        $(this).prop('disabled', true).html(
                            '<i class="las la-spinner la-spin"></i> Generating...');
                        $.ajax({
                            url: '{{ route('staffs.generate_doc', ':id') }}'.replace(':id',
                                {{ $staff->id }}),
                            method: 'POST',
                            data: {
                                purpose_short,
                                purpose,
                                type: 'noc'
                            },
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    AIZ.plugins.notify('success', response.message ||
                                        'Document generated successfully.');
                                    $('#nocModal').modal('hide');
                                }
                            },
                            error: function(xhr, status, error) {
                                const errorMessage = xhr.responseJSON?.message ||
                                    'Failed to generate. Please try again.';
                                AIZ.plugins.notify('danger', errorMessage);
                            },
                            complete: function() {
                                $('#nocSendBtn').prop('disabled', false).html(
                                    '<i class="las la-paper-plane"></i> Generate');
                            }
                        });
                    });

                    $('#send-appointment-btn, #send-joining-btn, #send-noc-btn, #send-promotion-btn, #send-increment-btn').on('click', function() {
                        const type = $(this).data('type');
                        const email = "{{ $staff->personal_email }}";
                        if (!email) {
                            Swal.fire({
                                title: 'No Personal Email Found',
                                text: "This staff member does not have a personal email address on his/her profile. Please update their profile with a valid personal email before sending documents.",
                                icon: 'error',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                        }
                        const url = "{{ route('staffs.send_doc', ':id') }}".replace(':id', {{ $staff->id }});
                        const btn = $(this);
                        Swal.fire({
                            title: 'Are You Sure?',
                            text: "This will send the latest document to the staff's personal email.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, Send It!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                btn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Sending...');
                                $.ajax({
                                    url: url,
                                    method: 'POST',
                                    data: {
                                        type
                                    },
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            AIZ.plugins.notify('success', response
                                                .message ||
                                                'Document sent successfully.');
                                        } else {
                                            AIZ.plugins.notify('danger', response
                                                .message ||
                                                'Failed to send document.');
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        const errorMessage = xhr.responseJSON
                                            ?.message ||
                                            'Failed to send. Please try again.';
                                        AIZ.plugins.notify('danger', errorMessage);
                                    },
                                    complete: function() {
                                        btn.prop('disabled', false).html('<i class="las la-paper-plane mr-1"></i>Send');
                                    }
                                });
                            }
                        });
                    });
                });
            });
        </script>
    @endif
    @if (get_setting('enable_attendance_management', 0) == 1)
        <script>
            $(function() {

                function loadAttendance() {
                    let month = $('#attendance_month').val();

                    $('#attendanceLoader').show();
                    $('#attendanceContent').hide();

                    $.ajax({
                        url: "{{ route('attendance.filter', $staff->id) }}",
                        data: {
                            month: month
                        },
                        success: function(res) {
                            $('#attendanceContent').html(res.html);
                            AIZ.plugins.fooTable();

                            $('#sum_working').text(res.summary.working);
                            $('#sum_present').text(res.summary.present);
                            $('#sum_absent').text(res.summary.absent);
                            $('#sum_ot').text(res.summary.ot);
                            $('#total_leaves').text(res.summary.leaves.total);
                            $('#paid_leaves').text(res.summary.leaves.paid);
                            $('#unpaid_leaves').text(res.summary.leaves.unpaid);
                        },
                        complete: function() {
                            $('#attendanceLoader').hide();
                            $('#attendanceContent').fadeIn(150);
                            // re initiate tooltips
                            $('#attendanceContent').find('[data-toggle="tooltip"]').tooltip();
                        }
                    });
                }

                $('#attendanceFilterBtn').click(loadAttendance);
                $('#attendance_month').change(loadAttendance);

                $().ready(function() {
                    // initial load
                    loadAttendance();
                });

                $('#attendanceContent').on('click', '.edit-attendence', function() {
                    let att = $(this).data('attendance');
                    let overtimes = $(this).data('overtimes');
                    $('#attendance_id').val(att.id);
                    $('#check-in-time').val(att.check_in);
                    $('#check-in-time').attr('min', att.min_value);
                    $('#check-out-time').val(att.check_out);
                    $('#check-out-time').attr('min', att.min_value);
                    $('#attendance-alter-date').val(att.alternative_date);
                    $('#attendance-alter-note').val(att.note);
                    $('#check-out-type').val(att.is_alternative ? 'alternative' : 'regular');
                    $('#attendance-shift').val(att.shift);
                    $('#attendance-status').val(att.status);
                    $('#overtimes-target').html('');
                    if (overtimes.length > 0) {
                        overtimes.forEach(function(overtime) {
                            let overtimeHtml = `<div class="row gutters-5">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="overtime-start-time">{{ __('Start Time') }}</label>
                                        <input type="datetime-local" name="overtime_start_times[]" class="form-control form-control-sm" value="${overtime.start_time}">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="overtime-end-time">{{ __('End Time') }}</label>
                                        <input type="datetime-local" name="overtime_end_times[]" class="form-control form-control-sm" value="${overtime.end_time}">
                                    </div>
                                </div>
                                <div class="col-auto d-flex align-items-center">
                                    <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger remove-overtime">
                                        <i class="las la-times"></i>
                                    </button>
                                </div>
                            </div>`;
                            $('#overtimes-target').append(overtimeHtml);
                        });
                    }
                    $('#attendance-errors').hide();
                    $('#attendance-edit-modal').modal('show');
                });

                $('#add-more-overtimes').click(function() {
                    const defaultValue = $('#check-in-time').val() ?? '';
                    const minValue = $('#check-in-time').attr('min') ?? '';
                    let newOvertime = `<div class="row gutters-5">
                        <div class="col">
                            <div class="form-group">
                                <label for="overtime-start-time">{{ __('Start Time') }}</label>
                                <input type="datetime-local" name="overtime_start_times[]" class="form-control form-control-sm" value="${defaultValue}" min="${minValue}">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="overtime-end-time">{{ __('End Time') }}</label>
                                <input type="datetime-local" name="overtime_end_times[]" class="form-control form-control-sm" value="${defaultValue}" min="${minValue}">
                            </div>
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger remove-overtime">
                                <i class="las la-times"></i>
                            </button>
                        </div>
                    </div>`;

                    $('.overtimes-target').append(newOvertime);
                });

                $('.overtimes-target').on('click', '.remove-overtime', function() {
                    $(this).closest('.row').remove();
                });

                $('#btn-update-attendance').click(function() {
                    let isValid = true;
                    let id = $('#attendance_id').val();
                    let data = {
                        check_in: $('#check-in-time').val(),
                        check_out: $('#check-out-time').val(),
                        alternative_date: $('#attendance-alter-date').val(),
                        note: $('#attendance-alter-note').val(),
                        check_out_type: $('#check-out-type').val(),
                        shift: $('#attendance-shift').val(),
                        status: $('#attendance-status').val(),
                        overtimes: []
                    };

                    $('.overtimes-target .row').each(function() {
                        let startTime = $(this).find('input[name="overtime_start_times[]"]').val();
                        let endTime = $(this).find('input[name="overtime_end_times[]"]').val();
                        if (startTime && endTime) {
                            data.overtimes.push({
                                start_time: startTime,
                                end_time: endTime
                            });
                        }
                    });

                    if (data.status === 'present') {
                        if (!data.check_in) {
                            $('#check-in-time-error').text('Check-in time is required when status is Present.');
                            isValid = false;
                        }
                        if (!data.check_out) {
                            $('#check-out-time-error').text(
                                'Check-out time is required when status is Present.');
                            isValid = false;
                        }
                    }

                    if (!isValid) {
                        return;
                    }

                    // Clear previous errors
                    $('#attendance-edit-form small.text-danger').text('');

                    $btn = $(this);
                    $btn.prop('disabled', true).text('Updating...');
                    $.ajax({
                        url: "{{ route('attendance.update', ':id') }}".replace(':id', id),
                        method: 'PUT',
                        data: data,
                        success: function(res) {
                            $('#attendance-edit-modal').modal('hide');
                            loadAttendance();
                            showAlert('success', res.message);
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;
                                $('#attendance-errors').text('');
                                for (let field in errors) {
                                    $('#attendance-errors').append('<li>' + errors[field] +
                                        '</li>');
                                }
                                $('#attendance-errors').fadeIn();
                            } else {
                                showAlert('error', 'An error occurred. Please try again.');
                            }
                        },
                        complete: function() {
                            $btn.prop('disabled', false).text('Update');
                        }
                    });
                });
            });
        </script>
    @endif

    @if (get_setting('enable_attendance_management', 0) == 1 && get_setting('enable_salary_sheet_generation', 0) == 1)
        {{-- Load Salary Sheets --}}
        <script>
            $(function() {

                function loadSalarySheet() {

                    let month = $('#salary_month').val();

                    $('#salaryLoader').show();
                    $('#salaryContent').hide();

                    $.ajax({
                        url: "{{ route('salary.sheets.filter', $staff->id) }}",
                        data: {
                            month: month
                        },
                        success: function(res) {
                            $('#salaryContent').html(res.html);
                        },
                        complete: function() {
                            $('#salaryLoader').hide();
                            $('#salaryContent').fadeIn(150);
                            // re initiate tooltips
                            $('#salaryContent').find('[data-toggle="tooltip"]').tooltip();
                            AIZ.plugins.fooTable();
                        }
                    });
                }

                $('#salaryFilterBtn').click(loadSalarySheet);
                $('#salary_month').change(loadSalarySheet);

                $().ready(function() {
                    // initial load
                    loadSalarySheet();
                })
            });
        </script>
    @endif

    <script>
        // Persist active tab on reload via URL hash
        (function() {
            var hash = window.location.hash;
            if (hash) {
                var tab = document.querySelector('#staffProfileTabs a[href="' + hash + '"]');
                if (tab) {
                    $(tab).tab('show');
                }
            }
            $('#staffProfileTabs a').on('shown.bs.tab', function(e) {
                history.replaceState(null, null, e.target.getAttribute('href'));
            });
        })();
    </script>
@endsection
