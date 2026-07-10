@extends(config('app.theme').'frontend.layouts.app')

@section('content')
    <section class="gry-bg py-3 py-md-5">
        <div class="profile">
            <div class="container">
                <div class="row bg-white mx-auto  justify-content-center col-12 col-xl-10 registration-page  flex-column-reverse flex-md-row">
                    <div class="col-xxl-7 col-xl-7 col-lg-7 col-md-6 user_log_left border-md-right user_log_left  px-0 px-md-2">
                            <div class = "col-12 col-md-8 mx-auto p-0">
                                <h1 class="h4 mb-3 text-capitalize">
                                    {{ ('Discover all the benefits ')}}
                                </h1>
                                <p>Create an account to enhance your shopping experience with the help of our customized services:</p>
                                <ul>
                                    <li>Stay up to date with the latest news</li>
                                    <li>Buy faster</li>
                                    <li>Save your favorite products</li>
                                </ul>
                                <img
                                    class="img-fluid text-center "
                                    src="{{ static_asset('assets/img/registation.png') }}"
                                    alt="{{ env('APP_NAME')}} Login"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                 >
                            </div>

                    </div>
                    <div class="col-xxl-5 col-xl-5 col-lg-5 col-md-6 mx-auto px-0 px-md-2 px-lg-3 px-xl-4">
                        <div class="user_log_form  pl-0 pl-md-4  pr-xl-4 pt-4 pt-md-0">
                            <div class=" ">
                                <h1 class="h4 ">
                                    {{ ('Create  account')}}
                                </h1>
                            </div>
                            <div class=" py-3 py-lg-4">
                                <div class="">
                                    <form id="reg-form" class="form-default" role="form" action="{{ route('register') }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label for="name">Your name</label>
                                            <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name') }}" placeholder="{{  translate('Full Name') }}" name="name">
                                            @if ($errors->has('name'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('name') }}</strong>
                                                </span>
                                            @endif
                                        </div>


                                        @if (addon_is_activated('otp_system'))
                                            <div class="form-group phone-form-group mb-1">
                                                <label for="phone-code">Phone</label>
                                                <input type="tel" id="phone-code" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" value="{{ old('phone') }}" placeholder="" name="phone" autocomplete="off">
                                            </div>

                                            <input type="hidden" name="country_code" value="">

                                            <div class="form-group email-form-group mb-1 d-none">
                                                <label for="email">Email</label>
                                                <input id="email" type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email"  autocomplete="off">
                                                @if ($errors->has('email'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('email') }}</strong>
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="form-group text-right">
                                                <button class="btn btn-link p-0 opacity-50 text-reset text-underline" type="button" onclick="toggleEmailPhone(this)">{{ ('Use Email Instead') }}</button>
                                            </div>
                                        @else
                                            <div class="form-group">
                                                <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email">
                                                @if ($errors->has('email'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('email') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="form-group position-relative">
                                            <label for="password">Password</label>
                                            <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="{{  translate('Type your Password') }}" name="password">
                                            <span class = "toggle-password password-hide">
                                                <i class="fas fa-eye"></i>
                                                <i class="fas fa-eye-slash"></i>
                                            </span>
                                            @if ($errors->has('password'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('password') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="form-group position-relative mb-4">
                                            <label for="password">Re-enter password</label>
                                            <input id="password" type="password" class="form-control" placeholder="{{  translate('Confirm Password') }}" name="password_confirmation">
                                            <span class = "toggle-password password-hide">
                                                <i class="fas fa-eye"></i>
                                                <i class="fas fa-eye-slash"></i>
                                            </span>
                                        </div>

                                        @if(get_setting('google_recaptcha') == 1)
                                            <div class="form-group">
                                                <div class="g-recaptcha" data-sitekey="{{ env('CAPTCHA_KEY') }}"></div>
                                            </div>
                                        @endif

                                        <div class="mb-1 mt-3">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" name="checkbox_example_1" required>
                                                <span class=opacity-70>{{ ('By signing up you agree to our')}} <a class="terms-condition" class="opacity-100" href="{{ route('terms') }}"> terms and conditions</a></span>
                                                <span class="aiz-square-check"></span>
                                            </label>
                                        </div>

                                        <div class="mb-2">
                                            <button type="submit" class="btn btn btn-dark btn-block fw-500 btn-block ">{{  translate('Create Account') }}</button>
                                        </div>
                                        <div class="text-center">
                                            <p class="mb-0">{{ ('Already have an account?')}} <a href="{{ route('user.login') }}" class="fw-500 opacity-70 text-secondary">{{ ('Log In')}}</a></p>
                                        </div>
                                        <div class="text-center mt-4 col-12 col-md-11 mx-auto">
                                            <p class="fs-12 fw-400">By creating an account, you agree to the Tekkabd.com.bd
                                                <a class="text-underline fw-500 text-dark" href="/">Privacy Policy</a>
                                                and
                                                <a class="text-underline fw-500 text-dark" href="/">Delivery Terms & Conditions</a>
                                            </p>
                                        </div>
                                    </form>
                                    @if(get_setting('google_login') == 1 || get_setting('facebook_login') == 1 || get_setting('twitter_login') == 1)
                                        <div class="separator mb-3">
                                            <span class="bg-white px-3 opacity-60">{{ ('Or Join With')}}</span>
                                        </div>
                                        <ul class="list-inline social colored text-center mb-5">
                                            @if (get_setting('facebook_login') == 1)
                                                <li class="list-inline-item">
                                                    <a href="{{ route('social.login', ['provider' => 'facebook']) }}" class="facebook">
                                                        <i class="lab la-facebook-f"></i>
                                                    </a>
                                                </li>
                                            @endif
                                            @if(get_setting('google_login') == 1)
                                                <li class="list-inline-item">
                                                    <a href="{{ route('social.login', ['provider' => 'google']) }}" class="google">
                                                        <i class="lab la-google"></i>
                                                    </a>
                                                </li>
                                            @endif
                                            @if (get_setting('twitter_login') == 1)
                                                <li class="list-inline-item">
                                                    <a href="{{ route('social.login', ['provider' => 'twitter']) }}" class="twitter">
                                                        <i class="lab la-twitter"></i>
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    @endif
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
    @if(get_setting('google_recaptcha') == 1)
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif

    <script type="text/javascript">

        @if(get_setting('google_recaptcha') == 1)
        // making the CAPTCHA  a required field for form submission
        $(document).ready(function(){
            // alert('helloman');
            $("#reg-form").on("submit", function(evt)
            {
                var response = grecaptcha.getResponse();
                if(response.length == 0)
                {
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

        var isPhoneShown = true,
            countryData = window.intlTelInputGlobals.getCountryData(),
            input = document.querySelector("#phone-code");

        for (var i = 0; i < countryData.length; i++) {
            var country = countryData[i];
            if(country.iso2 == 'bd'){
                country.dialCode = '88';
            }
        }

        var iti = intlTelInput(input, {
            separateDialCode: true,
            utilsScript: "{{ static_asset('assets/js/intlTelutils.js') }}?1590403638580",
            onlyCountries: @php echo json_encode(\App\Models\Country::where('status', 1)->pluck('code')->toArray()) @endphp,
            customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
                if(selectedCountryData.iso2 == 'bd'){
                    return "01xxxxxxxxx";
                }
                return selectedCountryPlaceholder;
            }
        });

        var country = iti.getSelectedCountryData();
        $('input[name=country_code]').val(country.dialCode);

        input.addEventListener("countrychange", function(e) {
            // var currentMask = e.currentTarget.placeholder;

            var country = iti.getSelectedCountryData();
            $('input[name=country_code]').val(country.dialCode);

        });

        function toggleEmailPhone(el){
            if(isPhoneShown){
                $('.phone-form-group').addClass('d-none');
                $('.email-form-group').removeClass('d-none');
                isPhoneShown = false;
                $(el).html('{{ ('Use Phone Instead') }}');
            }
            else{
                $('.phone-form-group').removeClass('d-none');
                $('.email-form-group').addClass('d-none');
                isPhoneShown = true;
                $(el).html('{{ ('Use Email Instead') }}');
            }
        }
    </script>
@endsection
