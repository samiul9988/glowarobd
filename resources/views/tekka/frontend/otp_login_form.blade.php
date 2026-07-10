@extends(config('app.theme').'frontend.layouts.app')

@section('content')
<div class="py-6">
    <div class="container">
        <div class="row">
            <div class="col-xxl-5 col-xl-6 col-md-8 mx-auto">
                <div class="bg-white rounded shadow-sm p-4 text-left">
                    <h1 class="h3 fw-600">
                        Enter Your OTP
                    </h1>
                    <div class="form-group mb-1">
                        <form id="otp_form" method="POST" action="{{ route('otp.verify_and_login') }}">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ old('user_id', $user_id) }}">
                            <input id="code" type="text" class="form-control" name="code" placeholder="Enter OTP" value="{{ old('code') }}" required autofocus>
                            <span class="text-danger font-weight-bold fs-10" id="code-error"></span>
                        </form>
                    </div>
                    <div class="form-group text-right">
                        <p id="timer" >Resend OTP in <span id="second">59</span>s</p>
                        <button id="resend_otp_btn" class="btn btn-link p-0 opacity-50 text-reset" type="button" style="display: none;">Resend OTP</button>
                    </div>
                    <div class="form-group text-right">
                        <button type="submit" class="btn btn-dark btn-block" onclick="login()">
                            Login
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
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
                    url: '{{ route('auth.login_with_otp') }}',
                    type: 'POST',
                    data: {
                        email_or_phone: '{{ $contact }}',
                    },
                    success: function(response) {
                        if(response.result) {
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
                        $(this).attr('disabled', false);
                    },
                    error: function() {
                        $(this).attr('disabled', false);
                        AIZ.plugins.notify('danger', 'Failed to send OTP. Please try again');
                    }
                });
            });
        });

        function login() {
            let code = $('#code').val();
            let user_id = $('input[name=user_id]').val();
            if (!code) {
                $('#code-error').text('Please enter the OTP.');
                return;
            }
            $('#code-error').text('');
            $('#otp_form').submit();
        }
    </script>
@endsection
