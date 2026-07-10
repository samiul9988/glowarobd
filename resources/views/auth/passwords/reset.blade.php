@extends(config('app.theme').'frontend.layouts.app')

@section('content')
<div class="py-6">
    <div class="container">
        <div class="row">
            <div class="col-xxl-5 col-xl-6 col-md-8 mx-auto">
                <div class="bg-white rounded shadow-sm p-4 text-left">
                    <h1 class="h3 fw-600">{{ ('Reset Password') }}</h1>
                    <div class="mb-2 opacity-60 d-flex justify-content-center"><h5>Please Enter Your OTP</h5></div>
                    <form id="verification_form" method="POST" action="{{ route('password.update') }}">
                        @csrf

                        {{-- <div class="form-group">
                            <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" placeholder="{{ ('Email') }}" required autofocus>

                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div> --}}

                        <input type="hidden" name="email" id="email" value="{{ session('user_email_for_password_reset') }}">

                        <div class="form-group mb-1">
                            <input id="code" type="text" class="form-control{{ $errors->has('code') ? ' is-invalid' : '' }}" name="code" value="{{ $email ?? old('code') }}" placeholder="{{ ('Code')}}" required autofocus>

                            @if ($errors->has('code'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('code') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="form-group text-right">
                            <p id="timer" >Resend OTP in <span id="second">59</span>s</p>
                            <button id="resend_otp_btn" class="btn btn-link p-0 opacity-50 text-reset" type="button" style="display: none;">Resend OTP</button>
                        </div>

                        {{-- <div class="form-group">
                            <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="{{ ('New Password') }}" required>

                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="{{ ('Confirm Password') }}" required>
                        </div> --}}

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-dark btn-block">
                                {{ ('Reset Password') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $('#verification_form').submit(function(e) {
                const code = $('#code').val();

                // Check if OTP is exactly 6 digits (adjust length as needed)
                if (!code || code.length !== 6) {
                    e.preventDefault(); // Correct way to prevent submission
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
                        email: $('#email').val()
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
