@if(!$sheet || empty(data_get($sheet, 'details')) || count(data_get($sheet, 'details', [])) === 0)
    <div class="text-center py-5 text-muted">
        <i class="las la-file-invoice-dollar" style="font-size:3rem"></i>
        <p class="mt-2 mb-0">No salary sheet found.</p>
    </div>
@else
    @php
        $details = data_get($sheet, 'details', []);
        // dd($details);
    @endphp
    <div class="table-responsive">
        <form id="salary-sheet-form">
            <table class="table aiz-table mb-0" id="salary-sheet-table">
                <thead>
                    <tr>
                        @if (! @$compact ?? false)
                            <th>Employee</th>
                        @endif
                        <th data-breakpoints="xs">Attendance</th>
                        <th data-breakpoints="xs sm" class="text-center">Leave</th>
                        {{-- <th class="text-right">Basic</th> --}}
                        <th data-breakpoints="xs sm" width="10%" class="text-right">Overtime (+)</th>
                        <th data-breakpoints="xs sm md" width="10%" class="text-right">Late Fee (-)</th>
                        <th data-breakpoints="xs sm md" width="10%" class="text-right">Adjustment (+/-)</th>
                        <th data-breakpoints="xs sm md" width="20%" @if (@$compact ?? false) class="text-center" @endif>Bonuses</th>
                        <th class="text-right">Net Salary</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $row)
                        <tr data-staff-id="{{ data_get($row, 'staff_id') }}">
                            @if (! @$compact ?? false)
                            <td>
                                <span class="d-block fs-13 font-weight-bold">
                                    {{ data_get($row, 'name', 'N/A') }}
                                </span>
                                @if (!empty(data_get($row, 'role')))
                                    <span class="d-block fs-11">
                                        <i class="las la-shield-alt"></i> {{ data_get($row, 'role') }}
                                    </span>
                                @endif
                                <small class="text-muted d-block">ID: {{ data_get($row, 'employee_id') }}</small>
                                <small class="text-muted d-block">
                                    Salary: {{ single_price(data_get($row, 'salary')) }}
                                </small>
                                <small class="text-muted d-block">
                                    Daily Salary: {{ single_price(round(data_get($row, 'working_summary.per_day_salary', 0))) }}
                                </small>
                            </td>
                            @endif

                            <td>
                                <span class="badge badge-inline badge-soft-info" data-toggle="tooltip" data-title="Working Days {{ data_get($row, 'attendance_summary.working_days', 0) }}">
                                    W: {{ data_get($row, 'attendance_summary.working_days', 0) }}
                                </span>
                                <span class="badge badge-inline badge-soft-success" data-toggle="tooltip" data-title="Present {{ data_get($row, 'attendance_summary.present', 0) }} Day(s)">
                                    P: {{ data_get($row, 'attendance_summary.present', 0) }}
                                </span>
                                <span class="badge badge-inline badge-soft-danger" data-toggle="tooltip" data-title="Absent {{ data_get($row, 'attendance_summary.absent', 0) }} Day(s)">
                                    A: {{ data_get($row, 'attendance_summary.absent', 0) }}
                                </span>
                                <span class="badge badge-inline badge-soft-secondary" data-toggle="tooltip" data-title="Late {{ data_get($row, 'attendance_summary.late_count', 0) }} Day(s)">
                                    L: {{ data_get($row, 'attendance_summary.late_count', 0) }}
                                </span>
                                <div class="mt-1 small text-muted">
                                    <span class="badge badge-inline badge-soft-info mb-1" data-toggle="tooltip" data-title="{{ spellWorkTime(gmdate('H:i', ((int) data_get($row, 'working_summary.work_minutes', 0)) * 60)) }}">
                                        {{-- Work: {{ number_format((float) data_get($row, 'working_summary.work_hours', 0), 2, '.', '') }}h --}}
                                        Work: {{ gmdate('H:i', ((int) data_get($row, 'working_summary.work_minutes', 0)) * 60) }}
                                    </span>
                                    <span class="badge badge-inline badge-soft-secondary mb-1" data-toggle="tooltip" data-title="{{ spellWorkTime(gmdate('H:i', ((int) data_get($row, 'working_summary.late_minutes', 0)) * 60)) }}">
                                        {{-- Work: {{ number_format((float) data_get($row, 'working_summary.work_hours', 0), 2, '.', '') }}h --}}
                                        Late: {{ gmdate('H:i', ((int) data_get($row, 'working_summary.late_minutes', 0)) * 60) }}
                                    </span>
                                    <span class="badge badge-inline badge-soft-primary mb-1" data-toggle="tooltip" data-title="Overtime {{ spellWorkTime(gmdate('H:i', ((int) data_get($row, 'working_summary.overtime_minutes', 0)) * 60)) }}">
                                        OT: {{ gmdate('H:i', ((int) data_get($row, 'working_summary.overtime_minutes', 0)) * 60) }}
                                    </span>
                                </div>
                                <small class="text-muted d-block">
                                    Expected Work Hours: {{ data_get($row, 'working_summary.expected_work_hours', 0) }}h
                                </small>
                            </td>

                            <td class="text-left text-lg-center">
                                <span class="badge badge-inline badge-soft-info" data-toggle="tooltip" data-title="Total Leave {{ data_get($row, 'leave_summary.total_leave', 0) }} Day(s)">
                                    T: {{ data_get($row, 'leave_summary.total_leave', 0) }}
                                </span>
                                <span class="badge badge-inline badge-soft-success" data-toggle="tooltip" data-title="Paid Leave {{ data_get($row, 'leave_summary.paid_leave', 0) }} Day(s)">
                                    P: {{ data_get($row, 'leave_summary.paid_leave', 0) }}
                                </span>
                                <span class="badge badge-inline badge-soft-danger" data-toggle="tooltip" data-title="Unpaid Leave {{ data_get($row, 'leave_summary.unpaid_leave', 0) }} Day(s)">
                                    U: {{ data_get($row, 'leave_summary.unpaid_leave', 0) }}
                                </span>

                                <span class="d-block text-muted font-weight-bold">
                                    <small>Deduction Amount: </small>
                                    <small class="text-leave-amount">{{ number_format((float) data_get($row, 'leave_amount', 0), 2, '.', '') }}</small>
                                </span>
                                @if ($editable)
                                    <div class="d-flex justify-content-left justify-content-lg-center">
                                        <input
                                            type="number"
                                            class="form-control form-control-sm text-right leave-amount"
                                            min="0"
                                            step="1"
                                            name="staffs[{{ $row['staff_id'] }}][leave_amount]"
                                            value="{{ (float) data_get($row, 'leave_amount', 0) }}"
                                            style="max-width:150px"
                                        >
                                    </div>
                                @endif
                            </td>

                            {{-- <td class="text-right font-weight-bold basic-salary-amount">
                                {{ number_format((float) data_get($row, 'basic_salary', 0), 2, '.', '') }}
                            </td> --}}

                            <td>
                                @if ($editable)
                                    <input
                                        type="number"
                                        class="form-control form-control-sm text-right overtime-amount"
                                        min="0"
                                        step="1"
                                        name="staffs[{{ $row['staff_id'] }}][overtime_amount]"
                                        value="{{ (float) data_get($row, 'overtime_amount', 0) }}"
                                    >
                                @else
                                    <span class="d-block text-right">{{ single_price(data_get($row, 'overtime_amount', 0)) }}</span>
                                @endif

                            </td>

                            <td>
                                @if ($editable)
                                    <input
                                        type="number"
                                        class="form-control form-control-sm text-right late-fee-amount"
                                        min="0"
                                        step="1"
                                        name="staffs[{{ $row['staff_id'] }}][late_fee_amount]"
                                        value="{{ (float) data_get($row, 'late_fee_amount', 0) }}"
                                    >
                                @else
                                    <span class="d-block text-right">{{ single_price(data_get($row, 'late_fee_amount', 0)) }}</span>
                                @endif
                            </td>

                            <td>
                                @if ($editable)
                                    <input
                                        type="number"
                                        class="form-control form-control-sm text-right adjustment-amount"
                                        step="1"
                                        name="staffs[{{ $row['staff_id'] }}][adjustment_amount]"
                                        value="{{ (float) data_get($row, 'adjustment_amount', 0) }}"
                                    >
                                @else
                                    <span class="d-block text-right">{{ single_price(data_get($row, 'adjustment_amount', 0)) }}</span>
                                @endif
                            </td>

                            <td>
                                @if ($editable)
                                    <div class="mb-2">
                                        <button type="button" class="btn btn-soft-primary btn-sm add-bonus-row fs-10 px-2 py-1">
                                            <i class="las la-plus"></i> Add Bonus
                                        </button>
                                    </div>
                                @endif
                                <div class="bonus-list">
                                    @forelse(data_get($row, 'bonuses', []) as $bonus)
                                        <div class="bonus-item border p-2 mb-2 rounded d-flex justify-content-between align-items-center">
                                            <input type="hidden" name="staffs[{{ $row['staff_id'] }}][bonuses][][title]" class="bonus-title" value="{{ data_get($bonus, 'title', '') }}">
                                            <input type="hidden" name="staffs[{{ $row['staff_id'] }}][bonuses][][amount]" class="bonus-amount" value="{{ data_get($bonus, 'amount', 0) }}">
                                            <span class="fs-11 fs-md-12">{{ data_get($bonus, 'title', 'Bonus') }}: {{ number_format((float) data_get($bonus, 'amount', 0), 2, '.', '') }}</span>
                                            @if ($editable)
                                                <button type="button" class="btn btn-soft-danger btn-sm remove-bonus-row fs-8 fs-md-10 px-1 px-md-2 py-1" title="Remove Bonus">
                                                    <i class="las la-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="text-center small text-muted mb-2">No bonus added.</div>
                                    @endforelse
                                </div>
                            </td>

                            <td class="text-right">
                                <span class="font-weight-bold text-muted d-block">
                                    <small>Basic </small>
                                    <small class="basic-salary-amount">{{ number_format((float) data_get($row, 'basic_salary', 0), 2, '.', '') }}</small>
                                </span>
                                <span class="text-muted d-block">
                                    <small>Bonus </small>
                                    <small class="bonus-total-amount">{{ number_format((float) data_get($row, 'bonus_total', 0), 2, '.', '') }}</small>
                                </span>
                                <span class="text-muted d-block">
                                    <small>Gross </small>
                                    <small class="gross-salary-amount">{{ number_format((float) data_get($row, 'gross_salary', 0), 2, '.', '') }}</small>
                                </span>
                                <div class="font-weight-bold text-success net-salary-amount">{{ number_format((float) data_get($row, 'net_salary'), 2, '.', '') }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </form>
    </div>
@endif
