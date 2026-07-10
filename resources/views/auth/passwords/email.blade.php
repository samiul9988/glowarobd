@extends(config('app.theme').'frontend.layouts.app')

@section('content')

<div class="py-3 py-md-5 bg-white">
    <div class="container">
        <div class="row bg-white mx-auto  justify-content-center col-12 col-xl-10 reset-password-page flex-column-reverse flex-md-row" >
            <div class="col-xxl-7 col-xl-7 col-lg-7 col-md-6 user_log_left border-md-right px-0 px-md-2 pt-4 pt-md-0">
                <div class = "col-12 col-md-8 mx-auto p-0">
                    <h1 class="h4 fw-600 text-capitalize">
                        {{ ('Discover all the benefits ')}}
                    </h1>
                    <p>Create an account to enhance your shopping experience with the help of our customized services:</p>
                    <ul>
                        <li>Stay up to date with the latest news</li>
                        <li>Buy faster</li>
                        <li>Save your favorite products</li>
                    </ul>
                    <img
                    class="img-fluid text-center"
                    src="{{ static_asset('assets/img/forgot-pass.png') }}"
                    alt="{{ env('APP_NAME')}} Login"
                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                >
                </div>

            </div>
            <div class=" col-xxl-5 col-xl-5 col-lg-5 col-md-6 mx-auto pl-md-5 px-0 px-md-2 px-lg-3 px-xl-4">
                <h2 class="h3 mb-4">{{ ('Forgot Password?') }}</h2>
                <ul class="list-inline social colored text-center mb-2">
                    <li class="list-inline-item" title="Login with Email">
                        <a type="button" href="javascript:void(0)" class="google email-btn"
                            onclick="toggleEmailPhone(this)">
                            <i class="las la-envelope"></i>
                        </a>
                    </li>
                </ul>
                <div class="separator mb-2">
                    <span class="bg-white px-3 opacity-60">Or</span>
                </div>
                <form id="forgot-password-form" method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <div class="form-group phone-form-group mb-1">
                            <label>Phone</label>
                            <input type="tel" id="input-phone"
                                class="form-control"
                                value="" placeholder="01xxxxxxxxx" name="email"
                                autocomplete="off">
                        </div>

                        <div class="form-group email-form-group mb-1 d-none">
                            <label>Email</label>
                            <input type="email" id="input-email"
                                class="form-control"
                                value="" placeholder="username@gmail.com" name="email"
                                autocomplete="off">
                        </div>
                        <span id="input-error" class="mt-1 text-danger fs-11 font-weight-bold"></span>
                    </div>
                    <div class="text-right mb-3">
                        <a href="{{route('user.login')}}" class="text-muted fs-14">Back to Login</a>
                    </div>
                    <div class="form-group text-right">
                        <button class="btn btn-primary btn-block send-otp" type="button" onclick="submitEmailForm(this)">
                            {{ ('Send OTP') }}
                        </button>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </form>
                <div class="text-center row align-items-center justify-content-center">
                    <p class="text-muted mb-0">{{ ('Dont have an account?')}}</p>
                    <a href="{{ route('user.registration') }}" class="text-secondary px-2">{{ ('Sign up')}}</a>
                </div>
            </div>
    </div>
    </div>
</div>
@endsection

@section('script')
    <script type="text/javascript">
        $('#email').attr("disabled", true);
        let isPhone = true;
        function toggleEmailPhone(el) {
            isPhone = $(el).hasClass('phone-btn') ? true : false;
            if (isPhone) {
                $('.email-form-group').addClass('d-none');
                $('.phone-form-group').removeClass('d-none');
                $('input[name=email]').val('');
                $('#input-error').text('');
                $(el).html('<i class="las la-envelope"></i>');
                $(el).removeClass('phone-btn').addClass('email-btn');
                $(el).parent().attr('title', 'Find By Email');
            } else {
                $('.email-form-group').removeClass('d-none');
                $('.phone-form-group').addClass('d-none');
                $('#input-error').text('');
                $('input[name=email]').val('');
                $(el).html('<i class="las la-phone"></i>');
                $(el).removeClass('email-btn').addClass('phone-btn');
                $(el).parent().attr('title', 'Find By Phone');
            }
        }

        function submitEmailForm(el) {
            let inputVal = '';
            if (isPhone) {
                inputVal = $('#input-phone').val().trim();
                if (inputVal === '') {
                    $('#input-error').text('Phone number is required.');
                    return;
                }
                const phonePattern = /^01[0-9]{9}$/;
                if (!phonePattern.test(inputVal)) {
                    $('#input-error').text('Invalid phone number.');
                    return;
                }
            } else {
                inputVal = $('#input-email').val().trim();
                if (inputVal === '') {
                    $('#input-error').text('Email is required.');
                    return;
                }
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(inputVal)) {
                    $('#input-error').text('Invalid email address.');
                    return;
                }
            }
            $('#input-error').text('');
            $('#forgot-password-form').submit();
        }
    </script>
@endsection
