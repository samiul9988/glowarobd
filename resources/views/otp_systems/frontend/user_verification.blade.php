@extends(config('app.theme').'frontend.layouts.app')

@php
    $contactType = @$contact_type ?? 'Phone';
@endphp
@section('content')
    <section class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-xl-5 mx-auto">
                    <div class="card">
                        <div class="text-center pt-5">
                            <h1 class="h2 fw-600">
                                {{ ucfirst($contactType) }} Verification
                            </h1>
                            @if ($contactType === 'email')
                                <p>A verification link has been sent to your email. Please check your inbox or spam folder.</p>
                            @else
                                <p>A verification code has been sent to your phone.</p>
                                <a href="{{ route('verification.phone.resend') }}" class="btn btn-link">{{ ('Resend Code')}}</a>
                            @endif
                        </div>
                        @if ($contactType === 'phone')
                            <div class="px-5 py-lg-4">
                                <div class="row align-items-center">
                                    <div class="col-12 col-lg">
                                        <form class="form-default" role="form" action="{{ route('verification.submit') }}" method="POST">
                                            @csrf
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" name="verification_code" placeholder="Enter your verification code" required>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-dark btn-block">{{ ('Verify') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
