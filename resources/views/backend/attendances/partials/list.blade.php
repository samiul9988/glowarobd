@if($attendances->isEmpty())

    <div class="text-center py-5 text-muted">
        <i class="las la-calendar-times" style="font-size:3rem"></i>
        <p class="mt-2">No attendance records found.</p>
    </div>

@else

<div class="table-responsive">
    <table class="table aiz-table table-sm mb-0">

        {{-- HEADER --}}
        <thead>
            <tr class="text-muted" style="font-size:.75rem; text-transform:uppercase;">
                <th>Date</th>
                <th data-breakpoints="sm" class="text-center">Shift</th>
                <th data-breakpoints="xs" class="text-center">In</th>
                <th data-breakpoints="xs" class="text-center">Out</th>
                <th data-breakpoints="sm" class="text-center">Work</th>
                <th data-breakpoints="md" class="text-center">OT</th>
                <th data-breakpoints="lg" class="text-center">Late</th>
                <th class="text-center">Status</th>
                <th data-breakpoints="md" class="text-center">Note</th>
                @if(Auth::user()->user_type === 'admin')
                    <th class="text-center">Action</th>
                @endif
            </tr>
        </thead>

        <tbody>

            @foreach($attendances as $att)
                @php
                    $shift = $att->shift ?? \App\Enums\ShiftEnum::DAY;
                    $editable = [
                        'id' => $att->id,
                        'check_in' => is_null($att->check_in) ? $shift->checkIn($att->date)->format('Y-m-d H:i') : $att->check_in->format('Y-m-d H:i'),
                        'check_out' => is_null($att->check_out) ? $shift->checkOut($att->date)->format('Y-m-d H:i') : $att->check_out->format('Y-m-d H:i'),
                        'min_value' => $att->date->format('Y-m-d') . 'T00:00',
                        'shift' => $shift->value ?? '',
                        'is_alternative' => $att->is_alternative,
                        'alternative_date' => $att->alternative_date?->format('Y-m-d'),
                        'note' => $att->note,
                        'status' => $att->status,
                    ];
                    $overtimes = $att->overtimes->map(function ($overtime) {
                        return [
                            'id' => $overtime->id,
                            'start_time' => optional($overtime->start_time)->format('Y-m-d H:i'),
                            'end_time' => optional($overtime->end_time)->format('Y-m-d H:i'),
                            'device_info' => $overtime->device_info
                        ];
                    });
                @endphp
                <tr style="border-top:1px solid #f1f1f1; background: {{ $att->date->isToday() ? '#f4f8ff' : '' }}">
                    {{-- DATE --}}
                    <td>
                        <div class="font-weight-bold text-dark">
                            {{ $att->date->format('d M') }}
                            @if($att->is_alternative && $att->alternative_date)
                                <small class="text-muted">-></small>
                                @if ($att->date->year !== $att->alternative_date->year)
                                    {{ $att->alternative_date->format('d M, Y') }}
                                @else
                                    {{ $att->alternative_date->format('d M') }}
                                @endif
                            @endif
                        </div>
                        <div class="info-label">
                            {{ $att->date->format('D') }}
                        </div>
                    </td>

                    {{-- SHIFT --}}
                    <td class="text-center">
                        <span class="badge badge-inline badge-soft-{{ $att->shift?->color() }}">
                            {{ $att->shift?->label() }}
                        </span>

                        @if($att->is_alternative && $att->alternative_date)
                            <div>
                                <span class="badge badge-inline badge-soft-secondary mt-1 fs-9">Alternative</span>
                            </div>
                        @endif
                    </td>

                    {{-- CHECK IN --}}
                    <td class="text-center font-weight-bold">
                        {{ optional($att->check_in)->format('h:i A') ?? '--' }}
                        @php
                            $checkInTooltip = "";
                            if (data_get($att->device_info, 'start_by.ip', null)) {
                                $checkInTooltip .= "IP: " . data_get($att->device_info, 'start_by.ip') . "<br>";
                            }
                            if (data_get($att->device_info, 'start_by.user_agent', null)) {
                                $checkInTooltip .= "Device: " . data_get($att->device_info, 'start_by.user_agent');
                            }
                        @endphp
                        @if ($att->check_in && $checkInTooltip)
                            <span class="d-block text-success fs-14" data-toggle="tooltip" data-html="true" data-title="{{ $checkInTooltip }}">
                                <i class="las la-laptop-code"></i>
                            </span>
                        @endif
                    </td>

                    {{-- CHECK OUT --}}
                    <td class="text-center font-weight-bold">
                        {{ optional($att->check_out)->format('h:i A') ?? '--' }}
                        @php
                            $checkOutTooltip = "";
                            if (data_get($att->device_info, 'stop_by.ip', null)) {
                                $checkOutTooltip .= "IP: " . data_get($att->device_info, 'stop_by.ip') . "<br>";
                            }
                            if (data_get($att->device_info, 'stop_by.user_agent', null)) {
                                $checkOutTooltip .= "Device: " . data_get($att->device_info, 'stop_by.user_agent');
                            }
                        @endphp
                        @if ($att->check_out && $checkOutTooltip)
                            <span class="d-block text-success fs-14" data-toggle="tooltip" data-html="true" data-title="{{ $checkOutTooltip }}">
                                <i class="las la-laptop-code"></i>
                            </span>
                        @endif
                    </td>

                    {{-- WORK --}}
                    <td class="text-center">
                        <span class="text-dark font-weight-bold" data-toggle="tooltip" data-title="{{ spellWorkTime(gmdate('H:i', $att->work_minutes * 60)) }}" data-placement="bottom">
                            {{ gmdate('H:i', $att->work_minutes * 60) }}h
                        </span>
                    </td>

                    {{-- OT --}}
                    <td class="text-center">
                        @if($att->overtime_minutes)
                            <span class="text-primary font-weight-bold" data-toggle="tooltip" data-title="{{ spellWorkTime(gmdate('H:i', $att->overtime_minutes * 60)) }}" data-placement="bottom">
                                {{ gmdate('H:i', $att->overtime_minutes * 60) }}h
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- LATE --}}
                    <td class="text-center">
                        @if($att->late_minutes)
                            <span class="text-danger" data-toggle="tooltip" data-title="{{ spellWorkTime(gmdate('H:i', $att->late_minutes * 60)) }}" data-placement="bottom">
                                {{ gmdate('H:i', $att->late_minutes * 60) }}h
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- STATUS --}}
                    <td class="text-center">
                        @php
                            $statusColor = [
                                'present' => 'success',
                                'absent' => 'danger',
                                'leave' => 'warning',
                                'holiday' => 'secondary'
                            ];
                        @endphp

                        <span class="badge badge-inline badge-soft-{{ $statusColor[$att->status] ?? 'dark' }} badge-pill px-2 font-weight-bold">
                            {{ ucfirst($att->status) }}
                        </span>
                    </td>
                    {{-- NOTE --}}
                    <td class="text-center">
                        @if($att->note)
                            {{ Str::limit($att->note, 30) }}
                            @if (strlen($att->note) > 30)
                                @include('components.tooltip', [
                                    'title' => $att->note,
                                    'class' => 'ml-1',
                                    'position' => 'left'
                                ])
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    {{-- ACTION --}}
                    @if(Auth::id() != $att->staff?->user_id && (Auth::user()->user_type === 'admin' || in_array('edit_attendance', $_authPermissions)))
                        <td class="text-center">
                            @if($att->logs_count > 0)
                                <a class="btn btn-sm btn-soft-primary btn-icon" data-toggle="tooltip" data-title="View Changelogs" href="{{ route('attendance.changelogs', encrypt($att->id)) }}" target="_blank">
                                    <i class="lab la-stack-exchange"></i>
                                </a>
                            @else
                                <button class="btn btn-sm btn-soft-secondary btn-icon" disabled data-toggle="tooltip" data-title="No Changelogs">
                                    <i class="lab la-stack-exchange"></i>
                                </button>
                            @endif

                            <button class="btn btn-sm btn-soft-success btn-icon edit-attendence" data-toggle="tooltip" data-title="Edit Attendance" data-attendance="{{ json_encode($editable) }}" data-overtimes="{{ json_encode($overtimes) }}">
                                <i class="las la-edit"></i>
                            </button>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endif
