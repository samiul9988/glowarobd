@extends('backend.layouts.layout')

@section('content')

<style>
    :root {
        --primary: {{ get_setting('base_color', '#7c3aed') }};
        --primary-dark: #6d28d9;
        --primary-light: #ede9fe;
        --secondary: #db2777;
    }

    * { box-sizing: border-box; }

    .login-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
        padding: 20px;
    }

    .login-wrapper::before {
        content: '';
        position: absolute;
        width: 600px;
        height: 600px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
        top: -200px;
        right: -100px;
        animation: float 8s ease-in-out infinite;
    }

    .login-wrapper::after {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        background: rgba(255,255,255,0.07);
        border-radius: 50%;
        bottom: -150px;
        left: -80px;
        animation: float 6s ease-in-out infinite reverse;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-30px) scale(1.05); }
    }

    .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 48px 40px;
        width: 100%;
        max-width: 440px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.15), 0 0 0 1px rgba(255,255,255,0.2);
        position: relative;
        z-index: 1;
        animation: slideUp 0.6s ease-out;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .login-card .logo-area {
        text-align: center;
        margin-bottom: 32px;
    }

    .login-card .logo-area img {
        height: 48px;
        margin-bottom: 16px;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        transition: transform 0.3s;
    }

    .login-card .logo-area img:hover {
        transform: scale(1.05);
    }

    .login-card .welcome-badge {
        display: inline-block;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: 20px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    .login-card h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1e293b;
        margin: 12px 0 4px;
        letter-spacing: -0.5px;
    }

    .login-card .subtitle {
        color: #64748b;
        font-size: 15px;
        margin-bottom: 32px;
        font-weight: 400;
    }

    .input-group-wrap {
        position: relative;
        margin-bottom: 20px;
    }

    .input-group-wrap .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 2;
        color: #94a3b8;
        font-size: 18px;
        transition: color 0.3s;
        pointer-events: none;
    }

    .input-group-wrap input {
        width: 100%;
        padding: 16px 18px 16px 50px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-size: 15px;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.3s;
        outline: none;
    }

    .input-group-wrap input:focus {
        border-color: var(--primary);
        background: #fff;
        box-shadow: 0 0 0 4px var(--primary-light);
    }

    .input-group-wrap input::placeholder {
        color: #94a3b8;
    }

    .input-group-wrap .invalid-feedback {
        display: block;
        color: #ef4444;
        font-size: 13px;
        margin-top: 6px;
        padding-left: 4px;
        font-weight: 500;
    }

    .input-group-wrap input.is-invalid {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .remember-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        font-size: 14px;
    }

    .remember-row .aiz-checkbox {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        color: #64748b;
        font-weight: 500;
    }

    .remember-row .aiz-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
        cursor: pointer;
    }

    .remember-row .aiz-square-check {
        display: none;
    }

    .forgot-link {
        color: var(--primary);
        font-weight: 600;
        text-decoration: none;
        font-size: 14px;
        transition: color 0.2s;
    }

    .forgot-link:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    .btn-login {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: #fff;
        border: none;
        border-radius: 14px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        letter-spacing: 0.3px;
        box-shadow: 0 8px 25px rgba(124, 58, 237, 0.35);
        position: relative;
        overflow: hidden;
    }

    .btn-login::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-login:hover::before {
        left: 100%;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba(124, 58, 237, 0.45);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .demo-table {
        margin-top: 24px;
        background: #f8fafc;
        border-radius: 12px;
        padding: 12px;
        font-size: 13px;
    }

    .demo-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .demo-table td {
        padding: 8px 12px;
        color: #475569;
        font-weight: 500;
    }

    .demo-table .btn-xs {
        background: var(--primary);
        color: #fff;
        border: none;
        padding: 4px 14px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 12px;
        transition: all 0.2s;
    }

    .demo-table .btn-xs:hover {
        background: var(--primary-dark);
        transform: scale(1.05);
    }

    @media (max-width: 500px) {
        .login-card {
            padding: 32px 24px;
            border-radius: 20px;
        }
        .login-card h1 { font-size: 24px; }
        .remember-row { flex-direction: column; gap: 10px; align-items: flex-start; }
    }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <div class="logo-area">
            @if(get_setting('system_logo_black') != null)
                <a href="{{ route('home') }}"><img src="{{ uploaded_asset(get_setting('system_logo_black')) }}" alt="Logo"></a>
            @else
                <a href="{{ route('home') }}"><img src="{{ static_asset('assets/img/logo.png') }}" alt="Logo"></a>
            @endif
            <br>
            <span class="welcome-badge">Welcome back</span>
            <h1>{{ env('APP_NAME') }}</h1>
            <p class="subtitle">Sign in to access your dashboard</p>
        </div>

        <form method="POST" role="form" action="{{ route('login') }}">
            @csrf
            <div class="input-group-wrap">
                <span class="input-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                </span>
                <input id="email" type="email" class="{{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus placeholder="Email address">
                @if ($errors->has('email'))
                    <span class="invalid-feedback"><strong>{{ $errors->first('email') }}</strong></span>
                @endif
            </div>

            <div class="input-group-wrap">
                <span class="input-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <input id="password" type="password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}" name="password" required placeholder="Password">
                @if ($errors->has('password'))
                    <span class="invalid-feedback"><strong>{{ $errors->first('password') }}</strong></span>
                @endif
            </div>

            <div class="remember-row">
                <label class="aiz-checkbox">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember Me</span>
                    <span class="aiz-square-check"></span>
                </label>
                @if(env('MAIL_FROM_ADDRESS') != null && env('MAIL_PASSWORD') != null)
                    <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="btn-login">
                Sign In
            </button>
        </form>

        @if (env("DEMO_MODE") == "On")
            <div class="demo-table">
                <table>
                    <tbody>
                        <tr>
                            <td>admin@example.com</td>
                            <td>123456</td>
                            <td><button class="btn-xs" onclick="autoFill()">Copy</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function autoFill(){
            $('#email').val('admin@example.com');
            $('#password').val('123456');
        }
    </script>
@endsection
