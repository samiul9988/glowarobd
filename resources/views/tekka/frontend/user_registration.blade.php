@extends(config('app.theme') . 'frontend.layouts.app')

@section('meta')
<x-seo />
@endsection

@section('content')
    <section class="gry-bg py-3 py-md-5">
        <div class="profile">
            <div class="container">
                <div
                    class="row bg-white mx-auto  justify-content-center col-12 col-xl-10 registration-page  flex-column-reverse flex-md-row">
                    <div
                        class="col-xxl-7 col-xl-7 col-lg-7 col-md-6 user_log_left border-md-right user_log_left  px-0 px-md-2">
                        <div class = "col-12 col-md-8 mx-auto p-0">
                            <h1 class="h4 mb-3 text-capitalize">
                                {{ 'Discover all the benefits ' }}
                            </h1>
                            <p>Create an account to enhance your shopping experience with the help of our customized
                                services:</p>
                            <ul>
                                <li>Stay up to date with the latest news</li>
                                <li>Buy faster</li>
                                <li>Save your favorite products</li>
                            </ul>
                            <img class="img-fluid text-center " src="{{ static_asset('assets/img/registation.png') }}"
                                alt="{{ env('APP_NAME') }} Login"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                        </div>

                    </div>
                    <div class="col-xxl-5 col-xl-5 col-lg-5 col-md-6 mx-auto px-0 px-md-2 px-lg-3 px-xl-4">
                        <div class="user_log_form  pl-0 pl-md-4  pr-xl-4 pt-4 pt-md-0">
                            <div class=" ">
                                <h1 class="h4 ">
                                    Create Account
                                </h1>
                            </div>
                            <div class=" py-3 py-lg-4">
                                <div class="">
                                    <ul class="list-inline social colored text-center mb-2">
                                        <li class="list-inline-item" title="Signup with Email">
                                            <a type="button" href="javascript:void(0)" class="google email-btn"
                                                onclick="toggleEmailPhone(this)">
                                                <i class="las la-envelope"></i>
                                            </a>
                                        </li>
                                        @if (get_setting('facebook_login') == 1)
                                            <li class="list-inline-item">
                                                <a href="{{ route('social.login', ['provider' => 'facebook']) }}"
                                                    class="facebook">
                                                    <i class="lab la-facebook-f"></i>
                                                </a>
                                            </li>
                                        @endif
                                        @if (get_setting('google_login') == 1)
                                            <li class="list-inline-item">
                                                <a href="{{ route('social.login', ['provider' => 'google']) }}"
                                                    class="google">
                                                    <i class="lab la-google"></i>
                                                </a>
                                            </li>
                                        @endif
                                        @if (get_setting('twitter_login') == 1)
                                            <li class="list-inline-item">
                                                <a href="{{ route('social.login', ['provider' => 'twitter']) }}"
                                                    class="twitter">
                                                    <i class="lab la-twitter"></i>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                    <div class="separator mb-2">
                                        <span class="bg-white px-3 opacity-60">Or</span>
                                    </div>
                                    <form id="reg-form" class="form-default" action="{{ route('register') }}" method="POST">
                                        @csrf
                                        <div class="form-group mb-2">
                                            <label for="name">Your name</label>
                                            <input id="name" type="text"
                                                class="form-control"
                                                value="{{ old('name') }}" placeholder="Name"
                                                name="name" autofocus>

                                            <span class="text-danger font-weight-bold fs-10" id="name-error">
                                                @if ($errors->has('name'))
                                                    {{ $errors->first('name') }}
                                                @endif
                                            </span>
                                        </div>


                                        @if (addon_is_activated('otp_system'))
                                            <div class="form-group phone-form-group mb-2">
                                                <label for="phone-code">Phone</label>
                                                <input type="tel" id="phone-code"
                                                    class="form-control"
                                                    value="{{ old('phone') }}" placeholder="01xxxxxxxxx" name="phone"
                                                    autocomplete="off">
                                                <span class="text-danger font-weight-bold fs-10" id="phone-error">
                                                    @if ($errors->has('phone'))
                                                        {{ $errors->first('phone') }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="form-group email-form-group mb-2 d-none">
                                                <label for="email">Email</label>
                                                <input id="email" type="email"
                                                    class="form-control"
                                                    value="{{ old('email') }}" placeholder="Email"
                                                    name="email" autocomplete="off">
                                                <span class="text-danger font-weight-bold fs-10" id="email-error">
                                                    @if ($errors->has('email'))
                                                        {{ $errors->first('email') }}
                                                    @endif
                                                </span>
                                            </div>
                                        @else
                                            <div class="form-group mb-2">
                                                <input type="email"
                                                    class="form-control"
                                                    value="{{ old('email') }}" placeholder="Email"
                                                    name="email">
                                                <span class="text-danger font-weight-bold fs-10" id="email-error">
                                                    @if ($errors->has('email'))
                                                        {{ $errors->first('email') }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endif

                                        <div class="form-group">
                                            <div class="position-relative">
                                                <label for="password">Password</label>
                                                <input id="password" type="password" class="form-control"
                                                    placeholder="********" name="password">
                                                <span class = "toggle-password password-hide">
                                                    <i class="fas fa-eye"></i>
                                                    <i class="fas fa-eye-slash"></i>
                                                </span>
                                            </div>
                                            <span class="text-danger font-weight-bold fs-10" id="password-error">
                                                @if ($errors->has('password'))
                                                    {{ $errors->first('password') }}
                                                @endif
                                            </span>
                                        </div>
                                        <input type="hidden" name="redirect" value="{{ request()->get('redirect') }}">
                                        <div class="form-group">
                                            <div class="position-relative">
                                                <label for="password_confirmation">Confirm Password</label>
                                                <input id="password_confirmation" type="password" class="form-control"
                                                    placeholder="********"
                                                    name="password_confirmation">
                                                <span class = "toggle-password password-hide">
                                                    <i class="fas fa-eye"></i>
                                                    <i class="fas fa-eye-slash"></i>
                                                </span>
                                            </div>
                                            <span class="text-danger font-weight-bold fs-10" id="password-confirmation-error">
                                                @if ($errors->has('password_confirmation'))
                                                    {{ $errors->first('password_confirmation') }}
                                                @endif
                                            </span>
                                        </div>

                                        @if (get_setting('google_recaptcha') == 1)
                                            <div class="form-group">
                                                <div class="g-recaptcha" data-sitekey="{{ env('CAPTCHA_KEY') }}"></div>
                                            </div>
                                        @endif

                                        <div class="form-group form-check d-flex" style="align-items: flex-start; gap: 4px;">
                                            <input type="checkbox" class="form-check-input aiz-checkbox" id="exampleCheck1" required>
                                            <label class="form-check-label" for="exampleCheck1">
                                                <span class="opacity-70 fs-14">
                                                    By signing up you agree to our
                                                    <a class="terms-condition opacity-100" href="/privacypolicy">
                                                        Privacy Policy
                                                    </a>
                                                    and
                                                    <a class="terms-condition opacity-100" href="{{ route('terms') }}">
                                                        Terms & Conditions
                                                    </a>
                                                </span>
                                            </label>
                                        </div>

                                        <div class="mb-2">
                                            <button type="button" class="btn btn btn-dark btn-block fw-500 btn-block" onclick="validateForm(this)">
                                                Create Account
                                            </button>
                                        </div>
                                        <div class="text-center">
                                            <p class="mb-0">
                                                Already have an account?
                                                <a href="{{ route('user.login') }}?redirect={{ request()->get('redirect') }}" class="fw-500 opacity-70 text-secondary">
                                                    Log In
                                                </a>
                                            </p>
                                        </div>
                                    </form>
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
    @if (get_setting('google_recaptcha') == 1)
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif

    <script type="text/javascript">
        let isPhone = true;
        @if (get_setting('google_recaptcha') == 1)
            // making the CAPTCHA  a required field for form submission
            $(document).ready(function() {
                // alert('helloman');
                $("#reg-form").on("submit", function(evt) {
                    var response = grecaptcha.getResponse();
                    if (response.length == 0) {
                        //reCaptcha not verified
                        alert("please verify you are humann!");
                        evt.preventDefault();
                        return false;
                    }
                    //captcha verified
                    //do the rest of your validations here
                    $("#reg-form").submit();
                });
            });
        @endif

        function validateForm(el) {
            let isValid = true;

            let name = $('input[name="name"]').val().trim();
            if (!name || name === '') {
                $('#name-error').text('Name is required');
                isValid = false;
            } else {
                $('#name-error').text('');
            }
            if (isPhone) {
                const phone = $('input[name="phone"]').val().trim();
                const phonePattern = /^01[0-9]{9}$/;

                if (!phone || phone === '') {
                    $('#phone-error').text('Phone number is required');
                    isValid = false;
                } else if (!phonePattern.test(phone)) {
                    $('#phone-error').text('Invalid phone number');
                    isValid = false;
                } else {
                    $('#phone-error').text('');
                }
            } else {
                const email = $('input[name="email"]').val().trim();
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!email || email === '') {
                    $('#email-error').text('Email is required');
                    isValid = false;
                } else if (!emailPattern.test(email)) {
                    $('#email-error').text('Invalid email address');
                    isValid = false;
                } else {
                    $('#email-error').text('');
                }
            }

            let password = $('input[name="password"]').val().trim();
            let passwordConfirmation = $('input[name="password_confirmation"]').val().trim();
            if (!password || password === '') {
                $('#password-error').text('Password is required');
                isValid = false;
            } else {
                $('#password-error').text('');
            }
            if (password !== passwordConfirmation) {
                $('#password-confirmation-error').text('Passwords not matched');
                isValid = false;
            } else {
                $('#password-confirmation-error').text('');
            }

            if (!isValid) {
                return false;
            }
            $('#reg-form').submit();
        }

        function toggleEmailPhone(el) {
            isPhone = $(el).hasClass('phone-btn') ? true : false;
            if (isPhone) {
                $('.email-form-group').addClass('d-none');
                $('.phone-form-group').removeClass('d-none');
                $('input[name=phone]').val('');
                $('input[name=email]').val('');
                $('#phone-error').text('');
                $('#email-error').text('');
                $(el).html('<i class="las la-envelope"></i>');
                $(el).removeClass('phone-btn').addClass('email-btn');
                $(el).parent().attr('title', 'Signup with Email');
            } else {
                $('.email-form-group').removeClass('d-none');
                $('.phone-form-group').addClass('d-none');
                $('input[name=phone]').val('');
                $('input[name=email]').val('');
                $('#phone-error').text('');
                $('#email-error').text('');
                $(el).html('<i class="las la-phone"></i>');
                $(el).removeClass('email-btn').addClass('phone-btn');
                $(el).parent().attr('title', 'Signup with Phone');
            }
        }
    </script>
@endsection
