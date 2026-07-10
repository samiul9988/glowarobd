@extends('backend.layouts.app')
@php
    $isAdmin = Auth::check() && Auth::user()->user_type == 'admin';
@endphp
@section('content')
    <div class="row gutters-5">
        <div class="col-md-3">
            <div class="card bg-light border">
                <div class="card-body text-center">
                    <span class="d-block fs-12 text-uppercase font-weight-bold text-muted">Present - Absent</span>
                    <span class="d-block fs-15 font-weight-bold">{{ $todaySummary['present'] ?? 0 }} - {{ $todaySummary['absent'] ?? $records->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border">
                <div class="card-body text-center">
                    <span class="d-block fs-12 text-uppercase font-weight-bold text-muted">Leave</span>
                    <span class="d-block fs-15 font-weight-bold">{{ $todaySummary['leaves'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border">
                <div class="card-body text-center">
                    <span class="d-block fs-12 text-uppercase font-weight-bold text-muted">Holidays</span>
                    <span class="d-block fs-15 font-weight-bold">{{ $todaySummary['holidays'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border">
                <div class="card-body text-center">
                    <span class="d-block fs-12 text-uppercase font-weight-bold text-muted">Off Days</span>
                    <span class="d-block fs-15 font-weight-bold">{{ $todaySummary['offdays'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <form action="{{ route('attendance.index') }}" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-md-0 h6">Monthly Attendance Report</h5>
                </div>

                <div class="col-md-2 ml-auto mb-2 mb-md-0">
                    <div class="form-group mb-0">
                        <input type="month" class="form-control form-control-sm" id="month" name="month" value="{{ request('month', now()->format('Y-m')) }}" min="{{ $minDate ?? now()->format('Y-m') }}" max="{{ now()->format('Y-m') }}">
                    </div>
                </div>

                <div class="col-auto mb-2 mb-md-0">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="window.location.href='{{ route('attendance.index') }}'">
                        Reset
                    </button>
                </div>
            </div>
        </form>
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Staff</th>
                        <th class="text-center">Days Summary</th>
                        <th class="text-center">Attendance Summary</th>
                        <th class="text-center">Work Time</th>
                        <th class="text-center">Attendance Rate</th>
                        <th width="10%" class="text-center">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $rec)
                        <tr>
                            <td class="font-weight-bold text-muted">
                                    {{ $rec->employee_id }}
                                </td>
                            {{-- STAFF --}}
                            <td>
                                <span class="d-block fs-13 font-weight-bold">
                                    {{ $rec->staff_name }}
                                </span>
                                @if (!empty($rec->role_name))
                                    <span class="d-block">
                                        <i class="las la-shield-alt"></i> {{ $rec->role_name }}
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="mb-2">
                                    <span class="badge badge-inline badge-soft-success font-weight-bold">
                                        Total: {{ $rec->total_days ?? '0' }}
                                    </span>
                                    <span class="badge badge-inline badge-soft-primary font-weight-bold">
                                        Working : {{ $rec->working_days ?? '0' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="badge badge-inline badge-soft-danger font-weight-bold">
                                        Holiday: {{ $rec->holidays ?? '0' }}
                                    </span>
                                    <span class="badge badge-inline badge-soft-secondary font-weight-bold">
                                        Offday: {{ $rec->weekends ?? '0' }}
                                    </span>
                                </div>
                            </td>

                            {{-- WORKING --}}
                            <td class="text-center">
                                <div class="mb-2">
                                    <span class="badge badge-inline badge-soft-success font-weight-bold">
                                        Present: {{ $rec->present ?? '0' }}
                                    </span>
                                    <span class="badge badge-inline badge-soft-danger font-weight-bold">
                                        Absent: {{ $rec->absent ?? '0' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="badge badge-inline badge-soft-primary font-weight-bold">
                                        Late: {{ $rec->late_count ?? '0' }}
                                    </span>
                                    <span class="badge badge-inline badge-soft-secondary font-weight-bold">
                                        Leave: {{ $rec->leave_count ?? '0' }}
                                    </span>
                                </div>
                            </td>

                            <td class="text-center">
                                <div class="mb-2">
                                    <span class="badge badge-inline badge-soft-success font-weight-bold" data-toggle="tooltip" data-title="{{ spellWorkTime(gmdate('H:i', ($rec->total_work_minutes ?? 0) * 60)) }}">
                                        Regular: {{ gmdate('H:i', ($rec->total_work_minutes ?? 0) * 60) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="badge badge-inline badge-soft-primary font-weight-bold" data-toggle="tooltip" data-title="{{ spellWorkTime(gmdate('H:i', ($rec->total_ot_minutes ?? 0) * 60)) }}">
                                        Overtime: {{ gmdate('H:i', ($rec->total_ot_minutes ?? 0) * 60) }}
                                    </span>
                                </div>
                            </td>

                            {{-- ATTENDANCE RATE --}}
                            @php
                                $color = match(true) {
                                    $rec->attendance_rate >= 90 => 'success',
                                    $rec->attendance_rate >= 75 => 'primary',
                                    $rec->attendance_rate >= 50 => 'warning',
                                    default => 'danger',
                                };
                            @endphp
                            <td class="text-center text-{{ $color }} font-weight-bold">
                                {{ $rec->attendance_rate ?? 0 }}%
                            </td>

                            {{-- ACTIONS --}}
                            <td class="text-center">
                                @if($isAdmin || in_array('view_staff', $_authPermissions))
                                    <a class="btn btn-soft-info btn-icon btn-circle btn-sm"
                                        href="{{ route('staffs.show', encrypt($rec->staff_id)) }}?month={{ request('month', now()->format('Y-m')) }}#pane-attendance"
                                        target="_blank"
                                        data-toggle="tooltip"
                                        data-title="View Details">
                                        <i class="las la-eye"></i>
                                    </a>
                                @else
                                    <a class="btn btn-soft-info btn-icon btn-circle btn-sm disabled opacity-50"
                                        href="javascript:;"
                                        data-toggle="tooltip"
                                        data-title="Permission Denied">
                                        <i class="las la-lock"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('#create-btn').on('click', function() {
            window.location.href = "{{ route('staffs.create') }}";
        });

        $(document).on("click", ".action-btn", function (e) {
            e.preventDefault();
            var url = $(this).data("href");
            var action = $(this).data("action");
            var title = 'Are You Sure?';
            var text = action === 'active' ? "You want to activate this staff!" : "You want to deactivate this staff!";
            var confirmButtonText = action === 'active' ? 'Yes, Activate It!' : 'Yes, Deactivate It!';
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: confirmButtonText
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    </script>
@endsection
