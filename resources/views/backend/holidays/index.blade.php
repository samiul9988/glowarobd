@extends('backend.layouts.app')

@push('cus_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
    <style>
        .fc-daygrid-event {
            transition: opacity .2s ease;
        }
        .fc-daygrid-event:hover {
            opacity: .82;
        }
        #calendar-wrapper {
            position: relative;
            min-height: 400px;
        }
        #calendar-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }
        .date-chip {
            display: inline-block;
            background: #f8d7da;
            color: #842029;
            border-radius: 20px;
            padding: 2px 10px;
            margin: 2px;
            font-size: 12px;
            font-weight: 500;
        }
        .fc-day-past .fc-daygrid-day-frame {
            background-color: rgba(0, 0, 0, 0.04);
        }
        .fc-day-past .fc-daygrid-day-number {
            color: #adb5bd;
        }
        .fc-day-holiday .fc-daygrid-day-frame {
            background-color: rgba(220, 53, 69, 0.10);
        }
        .fc-day-holiday .fc-daygrid-day-number {
            color: #dc3545;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0 h5">{{ __('Holiday Calendar') }}</h4>
            <p class="text-muted small mb-0">
                @if($canManage ?? false)
                    {{ __('Click a date or drag across multiple dates to add holidays. Click an existing holiday to edit or delete.') }}
                @else
                    {{ __('View all official holidays for the year.') }}
                @endif
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="mb-0">{{ __('Holidays') }}</h6>
            <span class="badge badge-inline badge-danger px-2 py-1">
                <i class="las la-calendar-alt"></i> {{ __('Red = Holiday') }}
            </span>
        </div>
        <div class="card-body p-3">
            <div id="calendar-wrapper">
                <div id="calendar-loading" class="d-none">
                    <div class="spinner-border text-danger" role="status">
                        <span class="sr-only">{{ __('Loading...') }}</span>
                    </div>
                </div>
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    @if($canManage ?? false)
        {{-- Add / Multi-date Modal --}}
        <div class="modal fade" id="addHolidayModal" tabindex="-1" role="dialog" aria-labelledby="addHolidayModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addHolidayModalLabel">{{ __('Add Holiday(s)') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('Selected Date(s)') }}</label>
                            <div id="selected-date-chips" class="p-2 border rounded" style="min-height:36px; background:#fafafa;"></div>
                        </div>
                        <div class="form-group mb-0">
                            <label for="add-holiday-title">{{ __('Holiday Title') }}</label>
                            <input type="text" id="add-holiday-title" class="form-control" placeholder="{{ __('e.g. Eid Al-Fitr') }}">
                            <small class="text-muted">{{ __('This title will be applied to all selected dates.') }}</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-danger" id="btn-add-holidays">
                            <i class="las la-plus"></i> {{ __('Add Holiday(s)') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div class="modal fade" id="editHolidayModal" tabindex="-1" role="dialog" aria-labelledby="editHolidayModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editHolidayModalLabel">{{ __('Edit Holiday') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit-holiday-id">
                        <div class="form-group mb-0">
                            <label for="edit-holiday-title">{{ __('Holiday Title') }}</label>
                            <input type="text" id="edit-holiday-title" class="form-control" placeholder="{{ __('e.g. New Year\'s Day') }}">
                            <small class="text-muted" id="edit-holiday-date-label"></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="btn-delete-holiday">
                            <i class="las la-trash"></i> {{ __('Delete') }}
                        </button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-primary" id="btn-save-holiday">
                            <i class="las la-save"></i> {{ __('Save Changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
        $(function () {
            var $calendarEl = $('#calendar')[0];
            var $loadingEl  = $('#calendar-loading');
            var csrfToken   = $('meta[name="csrf-token"]').attr('content');
            var canManage   = {{ ($canManage ?? false) ? 'true' : 'false' }};

            var pendingDates = [];

            function showLoading() { $loadingEl.removeClass('d-none'); }
            function hideLoading() { $loadingEl.addClass('d-none'); }

            function renderChips() {
                var $container = $('#selected-date-chips');
                if (!$container.length) { return; }
                $container.html(
                    pendingDates.length
                        ? $.map(pendingDates, function (d) { return '<span class="date-chip">' + d + '</span>'; }).join('')
                        : '<span class="text-muted small">{{ __('No dates selected') }}</span>'
                );
            }

            var calendarOptions = {
                initialView: 'multiMonthYear',
                height: 'auto',
                selectable: canManage,
                editable: false,
                eventColor: '#dc3545',
                multiMonthMaxColumns: 3,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'multiMonthYear,dayGridMonth',
                },
                viewDidMount: function (info) {
                    if (info.view.type === 'dayGridMonth') {
                        calendar.today();
                    }
                },
                loading: function (isLoading) {
                    isLoading ? showLoading() : hideLoading();
                },
                eventDidMount: function (info) {
                    // Highlight the whole day cell when a holiday event is mounted
                    var dateStr = info.event.startStr;
                    var cell = document.querySelector('.fc-day[data-date="' + dateStr + '"]');
                    if (cell) { $(cell).addClass('fc-day-holiday'); }
                },
                events: function (info, successCallback, failureCallback) {
                    showLoading();
                    $.ajax({
                        url: '{{ route('admin.holidays.events') }}',
                        method: 'GET',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        success: function (data) { hideLoading(); successCallback(data); },
                        error: function (err) { hideLoading(); failureCallback(err); },
                    });
                },
            };

            if (canManage) {
                calendarOptions.dateClick = function (info) {
                    let allEvents = calendar.getEvents();
                    let existingHolidays = allEvents.filter(function (e) { return e.startStr === info.dateStr; });
                    let dateExists = existingHolidays.length > 0;

                    var clicked = new Date(info.dateStr + 'T00:00:00');
                    var today   = new Date(); today.setHours(0, 0, 0, 0);
                    if (clicked < today) {
                        showAlert('error', '{{ __('Past holidays cannot be modified.') }}');
                        return;
                    }

                    if (dateExists) {
                        return;
                    }
                    pendingDates = [info.dateStr];
                    renderChips();
                    $('#add-holiday-title').val('');
                    $('#addHolidayModal').modal('show');
                };

                calendarOptions.select = function (info) {
                    let allEvents = calendar.getEvents();
                    var today = new Date(); today.setHours(0, 0, 0, 0);
                    pendingDates = [];
                    var skippedAssigned = 0;
                    var cursor = new Date(info.start);
                    while (cursor < info.end) {
                        // Build date string from local parts to avoid UTC offset shifting the date
                        var y      = cursor.getFullYear();
                        var m      = String(cursor.getMonth() + 1).padStart(2, '0');
                        var d      = String(cursor.getDate()).padStart(2, '0');
                        var dateStr = y + '-' + m + '-' + d;
                        if (cursor >= today) {
                            if (allEvents.some(function (e) { return e.startStr === dateStr; })) {
                                skippedAssigned++;
                            } else {
                                pendingDates.push(dateStr);
                            }
                        }
                        cursor.setDate(cursor.getDate() + 1);
                    }
                    if (pendingDates.length === 0) {
                        calendar.unselect();
                        if (skippedAssigned > 0) {
                            showAlert('error', '{{ __('Selected dates already have holidays assigned.') }}');
                        }
                        return;
                    }
                    renderChips();
                    $('#add-holiday-title').val('');
                    $('#addHolidayModal').modal('show');
                };

                calendarOptions.eventClick = function (info) {
                    var eventDate = new Date(info.event.startStr + 'T00:00:00');
                    var today     = new Date(); today.setHours(0, 0, 0, 0);
                    if (eventDate < today) {
                        showAlert('error', '{{ __('Past holidays cannot be modified.') }}');
                        return;
                    }
                    $('#edit-holiday-id').val(info.event.id);
                    $('#edit-holiday-title').val(info.event.title);
                    $('#edit-holiday-date-label').text(info.event.startStr);
                    $('#editHolidayModal').modal('show');
                };
            }

            var calendar = new FullCalendar.Calendar($calendarEl, calendarOptions);
            calendar.render();

            if (!canManage) { return; }

            // Add holiday(s)
            $('#btn-add-holidays').on('click', function () {
                var title = $('#add-holiday-title').val().trim();
                if (!title) {
                    showAlert('error', '{{ __('Please enter a holiday title.') }}');
                    return;
                }
                if (!pendingDates.length) {
                    showAlert('error', '{{ __('No dates selected.') }}');
                    return;
                }

                var isBulk   = pendingDates.length > 1;
                var endpoint = isBulk ? '{{ route('admin.holidays.bulk') }}' : '{{ route('admin.holidays.store') }}';
                var payload  = isBulk
                    ? { title: title, dates: pendingDates }
                    : { title: title, date: pendingDates[0] };

                $('#addHolidayModal').modal('hide');
                showLoading();

                $.ajax({
                    url: endpoint,
                    method: 'POST',
                    contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                    data: JSON.stringify(payload),
                    success: function (data, textStatus, xhr) {
                        hideLoading();
                        if (xhr.status === 201) {
                            calendar.unselect();
                            calendar.refetchEvents();
                            if (data.skipped && data.skipped.length) {
                                showAlert('success', data.message + '\n{{ __('Skipped') }}:\n' + data.skipped.join('\n'));
                            }
                        }
                    },
                    error: function (xhr) {
                        hideLoading();
                        var data = xhr.responseJSON || {};
                        showAlert('error', data.message || '{{ __('Failed to create holiday(s).') }}');
                    },
                });
            });

            // Save (update)
            $('#btn-save-holiday').on('click', function () {
                var id    = $('#edit-holiday-id').val();
                var title = $('#edit-holiday-title').val().trim();
                if (!title) {
                    showAlert('error', '{{ __('Title is required.') }}');
                    return;
                }

                showLoading();
                $.ajax({
                    url: '/admin/holidays/' + id,
                    method: 'PUT',
                    contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                    data: JSON.stringify({ title: title }),
                    success: function () {
                        hideLoading();
                        $('#editHolidayModal').modal('hide');
                        calendar.refetchEvents();
                    },
                    error: function (xhr) {
                        hideLoading();
                        $('#editHolidayModal').modal('hide');
                        var data = xhr.responseJSON || {};
                        showAlert('error', data.message || '{{ __('Failed to update holiday.') }}');
                    },
                });
            });

            // Delete
            $('#btn-delete-holiday').on('click', function () {
                Swal.fire({
                    title: '{{ __('Confirm Deletion') }}',
                    text: '{{ __('Are you sure you want to delete this holiday? This action cannot be undone.') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('Yes, Delete It!') }}',
                    cancelButtonText: '{{ __('Cancel') }}',
                }).then((result) => {
                    if (!result.isConfirmed) { return; }
                    var id = $('#edit-holiday-id').val();
                    showLoading();
                    $('#editHolidayModal').modal('hide');
                    $.ajax({
                        url: '/admin/holidays/' + id,
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                        success: function () {
                            hideLoading();
                            calendar.refetchEvents();
                        },
                        error: function (xhr) {
                            hideLoading();
                            var data = xhr.responseJSON || {};
                            showAlert('error', data.message || '{{ __('Failed to delete holiday.') }}');
                        },
                    });
                });
            });
        });
    </script>
@endsection
