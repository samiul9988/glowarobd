@if (Auth::user()->user_type === 'staff' && get_setting('enable_attendance_management', 0) == 1)
    @php
        $att = getTodayAttendanceData(Auth::user());
    @endphp
    <div class="mb-2 d-flex align-items-center">

        <div id="attendance-section" class="mr-2">
            @if (!$att['completed'])
                @if (!$att['checked_in'])
                    <button class="btn btn-sm btn-success" id="check-in-btn">
                        <i class="las la-sign-in-alt"></i> Check In
                    </button>
                @else
                    <button class="btn btn-sm btn-danger" id="check-out-btn">
                        <i class="las la-sign-out-alt"></i> Check Out
                    </button>
                @endif
            @endif
        </div>

        <div id="overtime-section">
            @if (!$att['doing_overtime'])
                <button class="btn btn-sm btn-primary" id="overtime-in-btn">
                    <i class="las la-clock"></i> Overtime In
                </button>
            @else
                <button class="btn btn-sm btn-danger" id="overtime-out-btn">
                    <i class="las la-stopwatch"></i> Overtime Out
                </button>
            @endif
        </div>

    </div>
@endif
