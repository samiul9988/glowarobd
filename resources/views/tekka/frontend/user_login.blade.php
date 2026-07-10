@extends(config('app.theme') . 'frontend.layouts.app')

@section('meta')
<x-seo />
@endsection

@section('content')
    <section class="gry-bg py-5">
        <div class="profile">
            <div class="container">
                <div class="row bg-white mx-auto  justify-content-center col-12 col-xl-10 flex-column-reverse flex-md-row">
                    <div class="col-xxl-7 col-xl-7 col-lg-7 col-md-6 user_log_left border-md-right px-0 px-md-2">
                        <div class = "col-12 col-md-8 mx-auto p-0">
                            <h1 class="h4 fw-600 text-capitalize">
                                {{ 'Discover all the benefits ' }}
                            </h1>
                            <p>Create an account to enhance your shopping experience with the help of our customized
                                services:</p>
                            <ul>
                                <li>Stay up to date with the latest news</li>
                                <li>Buy faster</li>
                                <li>Save your favorite products</li>
                            </ul>
                            <img class="img-fluid text-center" src="{{ static_asset('assets/img/login-image.png') }}"
                                alt="{{ env('APP_NAME') }} Login"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                        </div>

                    </div>
                    <div class="col-xxl-5 col-xl-5 col-lg-5 col-md-6 mx-auto px-0 px-md-2 px-lg-3 px-xl-4 ">
                        <div class="user_log_form">
                            <div class=" px-0 px-md-4 ">
                                <h1 class="h4 fw-600">
                                    {{ 'Login to your account ' }}
                                </h1>
                            </div>

                            <div class="px-0 px-md-4 py-3 py-lg-4">
                                <div class="">
                                    <div id="lookup-form">
                                        <ul class="list-inline social colored text-center mb-2">
                                            <li class="list-inline-item" title="Login with Email">
                                                <a type="button" href="javascript:void(0)" class="google email-btn"
                                                    onclick="toggleEmailPhone(this)">
                                                    <i class="las la-envelope"></i>
                                                </a>
                                            </li>
                                            @if (get_setting('google_login') == 1)
                                                <li class="list-inline-item" title="Login with Google">
                                                    <a href="{{ route('social.login', ['provider' => 'google']) }}"
                                                        class="google">
                                                        <i class="lab la-google"></i>
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                        <div class="separator mb-2">
                                            <span class="bg-white px-3 opacity-60">Or</span>
                                        </div>

                                        <div class="form-default">
                                            <div class="form-group phone-form-group mb-1">
                                                <label>Phone</label>
                                                <input type="tel" id="input-phone"
                                                    class="form-control"
                                                    value="" placeholder="01xxxxxxxxx" name="phone"
                                                    autocomplete="off">
                                            </div>

                                            <div class="form-group email-form-group mb-1 d-none">
                                                <label>Email</label>
                                                <input type="email"
                                                    class="form-control"
                                                    value="" placeholder="username@gmail.com"
                                                    name="email" id="input-email" autocomplete="off">
                                            </div>
                                            <span id="input-error" class="mt-1 text-danger fs-11 font-weight-bold"></span>
                                            <div class="my-3">
                                                <button class="btn btn-dark btn-block fw-600" onclick="validateInput(this)">
                                                    Next
                                                </button>
                                            </div>
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
                                    </div>
                                    <div class="form-default" style="display: none" id="login-form">
                                        <form id="submit-login-form" class="form-default" role="form" action="" method="POST">
                                            @csrf
                                            <div class="form-group">
                                                <div class="form-group position-relative">
                                                    <input readonly class="form-control" id="contact">
                                                    <span class="toggle-password password-hide font-weight-bold text-info" onclick="showLookupForm()">
                                                        Change
                                                    </span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="redirect" value="{{ request()->get('redirect') }}">
                                            <div class="form-group">
                                                <label>Password</label>
                                                <div class="form-group position-relative mb-0">
                                                    <input type="password"
                                                        class="form-control"
                                                        placeholder="********" name="password" id="password">

                                                    <span class = "toggle-password password-hide">
                                                        <i class="fas fa-eye"></i>
                                                        <i class="fas fa-eye-slash"></i>
                                                    </span>
                                                </div>
                                                <span id="password-error" class="text-danger fs-11 font-weight-bold"></span>
                                            </div>
                                        </form>

                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <button class="btn btn-link text-info opacity-60 fs-14 p-0" type="button" onclick="loginWithOTP(this)">
                                                    Login with OTP
                                                </button>
                                            </div>
                                            <div class="col-6 text-right">
                                                <a href="{{ route('password.request') }}" class="text-muted opacity-60 fs-14">
                                                    Forgot password?
                                                </a>
                                            </div>
                                        </div>
                                        <div class="my-3">
                                            <button class="btn btn-dark btn-block fw-600" onclick="login()">
                                                Login
                                            </button>
                                        </div>
                                    </div>

                                    {{-- <form class="form-default" role="form" action="{{ route('login') }}" method="POST">
                                        @csrf
                                        @if (addon_is_activated('otp_system') && env('DEMO_MODE') != 'On')
                                            <div class="form-group phone-form-group mb-1">
                                                <label>Phone</label>
                                                <input type="tel" id="phone-code"
                                                    class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}"
                                                    value="{{ old('phone') }}" placeholder="01xxxxxxxxx" name="phone"
                                                    autocomplete="off">
                                            </div>

                                            <div class="form-group email-form-group mb-1 d-none">
                                                <label>Email</label>
                                                <input type="email"
                                                    class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}"
                                                    value="{{ old('email') }}" placeholder="username@gmail.com"
                                                    name="email" id="email" autocomplete="off">
                                                @if ($errors->has('email'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('email') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="form-group">
                                                <input type="email"
                                                    class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                                    value="{{ old('email') }}" placeholder="{{ translate('Email') }}"
                                                    name="email" id="email" autocomplete="off">
                                                @if ($errors->has('email'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('email') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="">
                                            <label>Password</label>
                                            <div class="form-group position-relative">
                                                <input type="password"
                                                    class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}"
                                                    placeholder="{{ 'Password' }}" name="password" id="password">

                                                <span class = "toggle-password password-hide">
                                                    <i class="fas fa-eye"></i>
                                                    <i class="fas fa-eye-slash"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <label class="aiz-checkbox">
                                                    <input type="checkbox" name="remember"
                                                        {{ old('remember') ? 'checked' : '' }}>
                                                    <span class=opacity-60>{{ translate('Remember Me') }}</span>
                                                    <span class="aiz-square-check"></span>
                                                </label>
                                            </div>
                                            <div class="col-6 text-right">
                                                <a href="{{ route('password.request') }}"
                                                    class="text-secondary opacity-60 fs-14">{{ 'Forgot password?' }}</a>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <button type="submit"
                                                class="btn btn-dark btn-block fw-600">{{ translate('Login') }}</button>
                                        </div>
                                    </form> --}}
                                </div>
                                <div class="text-center row align-items-center justify-content-center">
                                    <p class="text-muted mb-0">{{ 'Dont have an account?' }}</p>
                                    <a href="{{ route('user.registration') }}?redirect={{ request()->get('redirect') }}"
                                        class="text-secondary px-2">{{ 'Sign up' }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script type="text/javascript">
        let formData = {
            login_type: 'phone',
            contact: '',
            password: ''
        };

        $('#contact').on('change', function() {
             $(this).val(formData.contact);
        });

        function showLookupForm() {
            $('#login-form').hide();
            $('#lookup-form').show();
            $('#password').val('');
            $('#password-error').text('');
            if (formData.login_type === 'phone') {
                $('#input-phone').val(formData.contact);
            } else {
                $('#input-email').val(formData.contact);
            }
        }

        function toggleEmailPhone(el) {
            let isPhone = $(el).hasClass('phone-btn');
            if (isPhone) {
                $('.email-form-group').addClass('d-none');
                $('.phone-form-group').removeClass('d-none');
                $('input[name=phone]').val('');
                $('input[name=email]').val('');
                $(el).html('<i class="las la-envelope"></i>');
                $(el).removeClass('phone-btn').addClass('email-btn');
                $(el).parent().attr('title', 'Login with Email');
                $('#input-error').text('');
                formData.login_type = 'phone';
            } else {
                $('.email-form-group').removeClass('d-none');
                $('.phone-form-group').addClass('d-none');
                $('input[name=phone]').val('');
                $('input[name=email]').val('');
                $(el).html('<i class="las la-phone"></i>');
                $(el).removeClass('email-btn').addClass('phone-btn');
                $(el).parent().attr('title', 'Login with Phone');
                $('#input-error').text('');
                formData.login_type = 'email';
            }
        }

        async function validateInput(el) {
            let inputVal = '';
            if (formData.login_type === 'phone') {
                inputVal = $('input[name=phone]').val().trim();
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
                inputVal = $('input[name=email]').val().trim();
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
            $(el).prop('disabled', true).text('Checking...');
            try {
                const userExists = await authLookup(inputVal);

                if (!userExists) {
                    $('#input-error').text(
                        'No account found with this ' +
                        (formData.login_type === 'phone' ? 'phone number.' : 'email address.')
                    );
                    return;
                }

                formData.contact = inputVal;
                $('#contact').val(inputVal);
                $('#contact').attr('name', formData.login_type);
                $('#lookup-form').hide();
                $('#login-form').show();
            } finally {
                $(el).prop('disabled', false).text('Next');
            }
        }

        function authLookup(contact) {
            return $.ajax({
                url: '{{ route('auth.lookup', '') }}/' + contact,
                type: 'GET'
            }).then(response => {
                return response?.result === true;
            }).catch(() => {
                return false;
            });
        }

        function login() {
            let password = $('#password').val().trim();
            if (password === '') {
                $('#password-error').text('Password is required.');
                return;
            }
            $('#password-error').text('');

            $('#submit-login-form').attr('action', '{{ route('login') }}');
            $('#submit-login-form').submit();
        }

        function loginWithOTP(el) {
            $(el).prop('disabled', true);
            $.ajax({
                url: '{{ route('auth.login_with_otp') }}',
                type: 'POST',
                data: {
                    email_or_phone: formData.contact,
                },
                success: function(response) {
                    if(response.result) {
                        window.location.href = '{{ route('otp.verify_form') }}?hash=' + response.hash;
                    } else {
                        AIZ.plugins.notify('danger', response.message || 'Failed to send OTP. Please try again');
                    }
                    $(el).prop('disabled', false);
                },
                error: function() {
                    $(el).prop('disabled', false);
                    AIZ.plugins.notify('danger', 'Failed to send OTP. Please try again');
                }
            });
        }

        function autoFillSeller() {
            $('#email').val('seller@example.com');
            $('#password').val('123456');
        }

        function autoFillCustomer() {
            $('#email').val('customer@example.com');
            $('#password').val('123456');
        }

        function autoFillDeliveryBoy() {
            $('#email').val('deliveryboy@example.com');
            $('#password').val('123456');
        }
    </script>
@endsection
