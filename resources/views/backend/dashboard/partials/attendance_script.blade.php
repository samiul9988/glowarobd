@if (Auth::user()->user_type === 'staff' && get_setting('enable_attendance_management', 0) == 1)
    {{-- CheckIn CheckOut Script --}}
    <script>
        function checkInUi() {
            $('#attendance-section').fadeOut();
            let html = `<button class="btn btn-sm btn-success" id="check-in-btn">
                    <i class="la la-sign-in-alt"></i> Check In
                </button>`;
            $('#attendance-section').html(html).fadeIn();
        }

        function checkOutUi() {
            $('#attendance-section').fadeOut();
            let html = `<button class="btn btn-sm btn-danger" id="check-out-btn">
                    <i class="la la-sign-out-alt"></i> Check Out
                </button>`;
            $('#attendance-section').html(html).fadeIn();
        }

        function overtimeInUi() {
            $('#overtime-section').fadeOut();
            let html = `<button class="btn btn-sm btn-primary" id="overtime-in-btn">
                    <i class="la la-clock"></i> Overtime In
                </button>`;
            $('#overtime-section').html(html).fadeIn();
        }

        function overtimeOutUi() {
            $('#overtime-section').fadeOut();
            let html = `<button class="btn btn-sm btn-danger" id="overtime-out-btn">
                    <i class="la la-stopwatch"></i> Overtime Out
                </button>`;
            $('#overtime-section').html(html).fadeIn();
        }

        function resetCheckInForm() {
            $('#check-in-type').val('regular');
            $('#check-in-alter-date-section').hide();
            $('#check-in-alter-date').val('');
            $('#check-in-alter-note').val('');
            $('#check-in-type-error').text('');
            $('#check-in-alter-date-error').text('');
            $('#check-in-alter-note-error').text('');
        }

        $(document).on('click', '#check-in-btn', function() {
            resetCheckInForm();
            $('#check-in-modal').modal('show');
        });

        $(document).on('click', '#check-out-btn', function() {
            Swal.fire({
                title: 'Are You Sure?',
                text: "You want to check out now!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Check Out!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('attendance.checkOut') }}',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                showAlert('success', response.message);
                                $('#attendance-section').html('').fadeIn();
                                overtimeInUi();
                            } else {
                                showAlert('error', response.message);
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 401) {
                                showAlert('error', 'Unauthorized access');
                            } else {
                                console.error('Error during check-out:', xhr);
                                showAlert('error', 'An error occurred. Please try again.');
                            }
                        }
                    });
                }
            });
        });

        $(document).on('click', '#overtime-in-btn', function() {
            $.ajax({
                type: 'POST',
                url: '{{ route('attendance.overtimeIn') }}',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        overtimeOutUi();
                    } else {
                        showAlert('error', response.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        showAlert('error', 'Unauthorized Access');
                    } else {
                        console.error('Error during initiate overtime:', xhr);
                        showAlert('error', 'An error occurred. Please try again.');
                    }
                }
            });
        });

        $(document).on('click', '#overtime-out-btn', function() {
            Swal.fire({
                title: 'Are You Sure?',
                text: "You want to stop overtime now!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Stop Overtime!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('attendance.overtimeOut') }}',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                showAlert('success', response.message);
                                overtimeInUi();
                            } else {
                                showAlert('error', response.message);
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 401) {
                                showAlert('error', 'Unauthorized access');
                            } else {
                                console.error('Error during stop overtime:', xhr);
                                showAlert('error', 'An error occurred. Please try again.');
                            }
                        }
                    });
                }
            });
        });

        $('#check-in-type').on('change', function() {
            const value = $(this).val();
            if (value === 'alternative') {
                $('#check-in-alter-date-section').fadeIn();
            } else {
                $('#check-in-alter-date-section').fadeOut();
            }
        })

        $('#btn-confirm-checkin').on('click', function() {
            const checkInType = $('#check-in-type').val();
            let alterDate = '';
            if (checkInType === 'alternative') {
                alterDate = $('#check-in-alter-date').val();
                if (!alterDate) {
                    $('#check-in-alter-date-error').text('Please select a date');
                    $('#check-in-alter-date').focus();
                    return;
                } else {
                    $('#check-in-alter-date-error').text('');
                }
            }

            $.ajax({
                type: 'POST',
                url: '{{ route('attendance.checkIn') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    checkInType: checkInType,
                    alternativeDate: alterDate,
                    note: $('#check-in-alter-note').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#check-in-modal').modal('hide');
                        showAlert('success', response.message);
                        checkOutUi();
                    } else {
                        showAlert('error', response.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        if (errors.alternativeDate) {
                            $('#check-in-alter-date-error').text(errors.alternativeDate[0]);
                        }
                        if (errors.note) {
                            $('#check-in-alter-note-error').text(errors.note[0]);
                        }
                    } else if (xhr.status === 401) {
                        showAlert('error', 'Unauthorized Access');
                    } else {
                        console.error('Error during check-in:', xhr);
                        showAlert('error', 'An error occurred. Please try again.');
                    }
                }
            });
        })
    </script>
@endif
