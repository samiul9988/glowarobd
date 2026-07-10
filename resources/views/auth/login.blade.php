@extends('backend.layouts.layout')

@section('content')

<div class="h-100 bg-light py-5 d-flex align-items-center admin_login_bg" style="background-image: url({{ uploaded_asset(get_setting('admin_login_background')) }})">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-xl-4">
                <div class="card text-left">
                    <div class="card-body p-0">
                        <div class="mb-3 text-left">
                            @if(get_setting('system_logo_black') != null)
                                <a href="{{ route('home') }}"><img src="{{ uploaded_asset(get_setting('system_logo_black')) }}" class="mw-100 mb-4" height="50" style="padding:5px 15px"></a>
                            @else
                                <a href="{{ route('home') }}"><img src="{{ static_asset('assets/img/logo.png') }}" class="mw-100 mb-4" height="40" style="padding:5px 15px"></a>
                            @endif
                            <h1 class="h3 text-secondary my-3"><span class="h6 text-primary">{{ ('Welcome to') }}</span><br>{{ env('APP_NAME') }}</h1>
                            <p class="mt-5" style="color:#010147 !important; font-size: 20px">{{ ('Login to your account.') }}</p>
                        </div>
                        <form class="pad-hor" method="POST" role="form" action="{{ route('login') }}">
                            @csrf
                            <div class="form-group">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus placeholder="{{ ('Email') }}">
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required placeholder="{{ ('Password') }}">
                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <div class="text-left">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                            <span>{{ ('Remember Me') }}</span>
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                                @if(env('MAIL_FROM_ADDRESS') != null && env('MAIL_PASSWORD') != null)
                                    <div class="col-sm-6">
                                        <div class="text-right">
                                            <a href="{{ route('password.request') }}" class="text-reset fs-14">{{ ('Forgot password ?')}}</a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                {{ ('Login') }}
                            </button>
                        </form>
                        @if (env("DEMO_MODE") == "On")
                            <div class="mt-4">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td>admin@example.com</td>
                                            <td>123456</td>
                                            <td><button class="btn btn-info btn-xs" onclick="autoFill()">{{ ('Copy') }}</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    :root{
        --primary: {{ get_setting('base_color', '#f00') }};
        --hov-primary: {{ get_setting('base_hov_color', '#00adee') }};
        --soft-primary: {{ hex2rgba(get_setting('base_color','#e62d04'),.15) }};
        --secondary: #00adee;
    }
    .admin_login_bg {
        background-size: 50%;
        background-repeat: no-repeat;
        background-position: bottom right -30px;
        background-color: #fff;
    }
    .admin_login_bg .card{
        background: none;
        border: none;
        box-shadow: none;
    }
    .form-control, .btn-primary{
        border-radius: 40px !important;
    }
</style>


@endsection

@section('script')
    <script type="text/javascript">
        function autoFill(){
            $('#email').val('admin@example.com');
            $('#password').val('123456');
        }
    </script>
@endsection
