@extends(config('app.theme').'frontend.layouts.app')

@section('content')
    <section class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-xl-5 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <h1 class="h3 fw-600">{{ ('Reset Password') }}</h1>
                            <div class="mb-2 opacity-60 d-flex justify-content-center">
                                <h5>Please Enter Your OTP</h5>
                            </div>
                            <form id="#verification_form" method="POST" action="{{ route('password.update.phone') }}">
                                @csrf

                                <input type="hidden" value="{{ session('user_phone_for_password_reset') }}" name="phone" id="phone">

                                <input type="hidden" id="country_code" name="country_code" value="{{ session('user_country_code_for_password_reset') }}">

                                <div class="form-group mb-1">
                                    <input id="code" type="text" class="form-control" name="code" placeholder="Code" required autofocus>
                                </div>
                                <div class="form-group text-right">
                                    <p id="timer" >Resend OTP in <span id="second">59</span>s</p>
                                    <button id="resend_otp_btn" class="btn btn-link p-0 opacity-50 text-reset" type="button" style="display: none;">Resend OTP</button>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        {{ ('Reset Password') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection


@section('script')
    <script>
        $(document).ready(function() {
            $('#verification_form').submit(function(e) {
                const code = $('#code').val();

                // Check if OTP is exactly 6 digits (adjust length as needed)
                if (!code || code.length !== 6) {
                    e.preventDefault();
                    AIZ.plugins.notify('danger', 'Please enter a valid 6-digit OTP');
                    return false;
                }

                // Disable the resend button during submission
                $('#resend_otp_btn').prop('disabled', true);

                // You might also want to disable the submit button
                $(this).find('button[type="submit"]').prop('disabled', true);
            });
            $('#timer').show();
            // Initial countdown value
            let seconds = 58;

            // Function to update the timer
            function updateTimer() {
                $('#second').text(seconds);
                seconds--;

                if (seconds < 0) {
                    // Timer finished, hide timer and show button
                    $('#timer').hide();
                    $('#resend_otp_btn').show().removeClass('opacity-50');
                    clearInterval(timerInterval);
                }
            }

            // Start the countdown
            let timerInterval = setInterval(updateTimer, 1000);

            // Handle resend button click
            $('#resend_otp_btn').click(async function() {
                $(this).attr('disabled', true);
                await $.ajax({
                    url: '{{ route('password.email') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        email: $('#phone').val(),
                        country_code: $('#country_code').val()
                    },
                    success: function(response) {
                        $(this).attr('disabled', false);
                        if (response.success) {
                            AIZ.plugins.notify('success', response.message || 'OTP sent successfully');
                            // Reset the timer
                            seconds = 60;
                            $('#second').text(seconds);
                            // Show timer and hide button
                            $('#timer').show();
                            $('#resend_otp_btn').hide().addClass('opacity-50');

                            // Restart the countdown
                            timerInterval = setInterval(updateTimer, 1000);
                        } else {
                            AIZ.plugins.notify('danger', response.message || 'Failed to send OTP. Please try again');
                        }
                    },
                    error: function() {
                        $(this).attr('disabled', false);
                        AIZ.plugins.notify('danger', 'Failed to send OTP. Please try again');
                    }
                });
            });
        });
    </script>
@endsection
