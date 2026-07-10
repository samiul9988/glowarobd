@extends('backend.layouts.app')
@php
    $isAdmin = Auth::check() && Auth::user()->user_type == 'admin';
@endphp
@section('content')
    <div class="card">
        <form action="{{ route('staffs.index') }}" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-md-0 h6">All Staffs</h5>
                </div>

                <div class="col-md-2 ml-auto mb-2 mb-md-0">
                    <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true" name="role" id="role">
                        <option value="">Filter By Role</option>
                        @foreach ($roles as $id => $name)
                            <option value="{{ $id }}" @if ($id == request('role')) selected @endif>
                                {{ ucwords($name) }}
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
                        onclick="window.location.href='{{ route('staffs.index') }}'">Reset</button>
                    @if($isAdmin || in_array('create_staff', $_authPermissions))
                        <div class="dropdown mb-2 mb-md-0 d-inline-block">
                            <button type="button" data-toggle="dropdown" class="btn btn-light btn-sm p-0 py-1">
                                <i class="las la-ellipsis-v font-weight-bold fs-24"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item btn-item" href="javascript:;" id="create-btn">
                                    <i class="las la-plus text-success"></i> Add New Staff
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </form>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group btn-sm" role="group" aria-label="Basic example">
                    <a href="{{ route('staffs.index', ['status' => 'active']) }}" class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center {{ empty(request('search')) && request('status') != 'banned' ? 'active' : '' }}">
                        Active
                        <span class="w-auto badge badge-primary ml-1">
                            {{ data_get($counts, 'active', 0) }}
                        </span>
                    </a>
                    <a href="{{ route('staffs.index', ['status' => 'banned']) }}" class="btn btn-secondary btn-sm d-flex flex-wrap align-items-center justify-content-center d-flex flex-wrap align-items-center justify-content-center {{ empty(request('search')) && request('status') == 'banned' ? 'active' : '' }}">
                        Inactive
                        <span class="w-auto badge badge-primary ml-1">
                            {{ data_get($counts, 'banned', 0) }}
                        </span>
                    </a>
                </div>
            </div>
            @if (!empty(request('search')) || !empty(request('role')))
                <div class="ml-3">
                    <span class="text-success">
                        <i class="las la-users"></i> {{ $staffs->total() }} staffs found
                    </span>
                </div>
            @endif
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contacts</th>
                        <th data-breakpoints="md">Employment Status</th>
                        <th data-breakpoints="md">Working Shift</th>
                        <th width="10%" class="text-center">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staffs as $key => $staff)
                        @if ($staff->user != null)
                            <tr>
                                <td class="font-weight-bold text-muted">
                                    <span class="d-block">
                                        {{ $staff->employee_id }}
                                    </span>
                                    <small>
                                        Last Login: {{ $staff->user?->recent_login ? \Carbon\Carbon::parse($staff->user->recent_login)->diffForHumans() : 'Never' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="d-block fs-13 font-weight-bold">
                                        {{ $staff->user->name }}
                                    </span>
                                    @if (!empty($staff->role?->name))
                                        <span class="d-block">
                                            <i class="las la-shield-alt"></i> {{ $staff->role->name }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($staff->user?->email))
                                        <span class="d-block">
                                            <a href="mailto:{{ $staff->user->email }}" class="text-reset">
                                                <i class="las la-envelope"></i> {{ $staff->user->email }}
                                            </a>
                                        </span>
                                    @endif
                                    @if (!empty($staff->user?->phone))
                                        <span class="d-block">
                                            <a href="tel:{{ $staff->user->phone }}" class="text-reset">
                                                <i class="las la-phone"></i> {{ $staff->user->phone }}
                                            </a>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $empStatus = $staff->employment_status ?? 'active';
                                        $statusColor = match($empStatus) {
                                            'active'     => 'success',
                                            'probation'  => 'warning',
                                            'on_leave'   => 'info',
                                            'resigned'   => 'danger',
                                            'terminated' => 'dark',
                                            default      => 'primary',
                                        };
                                        $statusLabel = match($empStatus) {
                                            'active'     => 'Active',
                                            'probation'  => 'Probation',
                                            'on_leave'   => 'On Leave',
                                            'resigned'   => 'Resigned',
                                            'terminated' => 'Terminated',
                                            default      => ucfirst($empStatus),
                                        };
                                    @endphp
                                    <span class="badge badge-inline badge-{{ $statusColor }}">
                                        <i class="las la-briefcase mr-1"></i> {{ ucwords($statusLabel) }}
                                    </span>
                                </td>
                                <td>
                                    @if($staff->shift)
                                        <span class="badge badge-inline badge-{{ $staff->shift?->color() }}">
                                            <i class="las la-{{ $staff->shift?->icon() }} mr-1"></i> {{ $staff->shift?->label() }}
                                        </span>
                                    @else
                                        <span class="badge badge-inline badge-danger">
                                            <i class="las la-clock mr-1"></i> No Shift Assigned
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($isAdmin || in_array('view_staff', $_authPermissions) || $staff->user?->id === Auth::id())
                                        <a class="btn btn-soft-info btn-icon btn-circle btn-sm"
                                            href="{{ route('staffs.show', encrypt($staff->id)) }}"
                                            title="View Profile">
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
                                    @if($isAdmin || in_array('edit_staff', $_authPermissions))
                                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                            href="{{ route('staffs.edit', encrypt($staff->id)) }}"
                                            title="Edit Profile">
                                            <i class="las la-edit"></i>
                                        </a>
                                        @php
                                            $isBanned = $staff->user?->banned ?? false;
                                            $title = $isBanned ? 'Make this Staff Active' : 'Make this Staff Inactive';
                                            $icon = $isBanned ? 'la-user-check' : 'la-user-slash';
                                            $action = $isBanned ? 'active' : 'inactive';
                                            $btn = $isBanned ? 'btn-soft-success' : 'btn-soft-danger';
                                        @endphp
                                        <a href="#" class="btn {{ $btn }} btn-icon btn-circle btn-sm action-btn"
                                            data-href="{{ route('staffs.ban', $staff->user->id) }}"
                                            data-action="{{ $action }}"
                                            title="{{ $title }}">
                                            <i class="las {{ $icon }}"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $staffs->appends(request()->input())->links() }}
            </div>
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