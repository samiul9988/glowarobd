    @extends('backend.layouts.app')

    @section('content')

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ ('Paypal Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        <input type="hidden" name="payment_method" value="paypal">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYPAL_CLIENT_ID">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Paypal Client Id')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="PAYPAL_CLIENT_ID" value="{{  env('PAYPAL_CLIENT_ID') }}" placeholder="{{ ('Paypal Client ID') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYPAL_CLIENT_SECRET">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Paypal Client Secret')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="PAYPAL_CLIENT_SECRET" value="{{  env('PAYPAL_CLIENT_SECRET') }}" placeholder="{{ ('Paypal Client Secret') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Paypal Sandbox Mode')}}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="paypal_sandbox" type="checkbox" @if (get_setting('paypal_sandbox') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ ('Stripe Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="stripe">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="STRIPE_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Stripe Key')}}</label>
                            </div>
                            <div class="col-md-8">
                            <input type="text" class="form-control" name="STRIPE_KEY" value="{{  env('STRIPE_KEY') }}" placeholder="{{ ('STRIPE KEY') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="STRIPE_SECRET">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Stripe Secret')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="STRIPE_SECRET" value="{{  env('STRIPE_SECRET') }}" placeholder="{{ ('STRIPE SECRET') }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header ">
                    <h5 class="mb-0 h6">{{ ('Bkash Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="bkash">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="BKASH_CHECKOUT_APP_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('BKASH CHECKOUT APP KEY')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="BKASH_CHECKOUT_APP_KEY" value="{{  env('BKASH_CHECKOUT_APP_KEY') }}" placeholder="{{ ('BKASH CHECKOUT APP KEY')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="BKASH_CHECKOUT_APP_SECRET">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('BKASH CHECKOUT APP SECRET')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="BKASH_CHECKOUT_APP_SECRET" value="{{  env('BKASH_CHECKOUT_APP_SECRET') }}" placeholder="{{ ('BKASH CHECKOUT APP SECRET')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="BKASH_CHECKOUT_USER_NAME">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('BKASH CHECKOUT USER NAME')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="BKASH_CHECKOUT_USER_NAME" value="{{  env('BKASH_CHECKOUT_USER_NAME') }}" placeholder="{{ ('BKASH CHECKOUT USER NAME')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="BKASH_CHECKOUT_PASSWORD">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('BKASH CHECKOUT PASSWORD')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="BKASH_CHECKOUT_PASSWORD" value="{{  env('BKASH_CHECKOUT_PASSWORD') }}" placeholder="{{ ('BKASH CHECKOUT PASSWORD')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Bkash Sandbox Mode')}}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="bkash_sandbox" type="checkbox" @if (get_setting('bkash_sandbox') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ ('Nagad Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="nagad">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NAGAD_MODE">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('NAGAD MODE')}}</label>
                            </div>
                            <div class="col-md-8">
                            <input type="text" class="form-control" name="NAGAD_MODE" value="{{  env('NAGAD_MODE') }}" placeholder="{{ ('NAGAD MODE')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NAGAD_MERCHANT_ID">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('NAGAD MERCHANT ID')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="NAGAD_MERCHANT_ID" value="{{  env('NAGAD_MERCHANT_ID') }}" placeholder="{{ ('NAGAD MERCHANT ID')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NAGAD_MERCHANT_NUMBER">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('NAGAD MERCHANT NUMBER')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="NAGAD_MERCHANT_NUMBER" value="{{  env('NAGAD_MERCHANT_NUMBER') }}" placeholder="{{ ('NAGAD MERCHANT NUMBER')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NAGAD_PG_PUBLIC_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('NAGAD PG PUBLIC KEY')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="NAGAD_PG_PUBLIC_KEY" value="{{  env('NAGAD_PG_PUBLIC_KEY') }}" placeholder="{{ ('NAGAD PG PUBLIC KEY')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NAGAD_MERCHANT_PRIVATE_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('NAGAD MERCHANT PRIVATE KEY')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="NAGAD_MERCHANT_PRIVATE_KEY" value="{{  env('NAGAD_MERCHANT_PRIVATE_KEY') }}" placeholder="{{ ('NAGAD MERCHANT PRIVATE KEY')}}" required>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header ">
                    <h5 class="mb-0 h6">{{ ('Sslcommerz Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="sslcommerz">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="SSLCZ_STORE_ID">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Sslcz Store Id')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="SSLCZ_STORE_ID" value="{{  env('SSLCZ_STORE_ID') }}" placeholder="{{ ('Sslcz Store Id')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="SSLCZ_STORE_PASSWD">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Sslcz store password')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="SSLCZ_STORE_PASSWD" value="{{  env('SSLCZ_STORE_PASSWD') }}" placeholder="{{ ('Sslcz store password')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Sslcommerz Sandbox Mode')}}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="sslcommerz_sandbox" type="checkbox" @if (get_setting('sslcommerz_sandbox') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header ">
                    <h5 class="mb-0 h6">{{ ('Aamarpay Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="aamarpay">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="AAMARPAY_STORE_ID">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Aamarpay Store Id')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="AAMARPAY_STORE_ID" value="{{  env('AAMARPAY_STORE_ID') }}" placeholder="{{ ('Aamarpay Store Id')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="AAMARPAY_SIGNATURE_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Aamarpay signature key')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="AAMARPAY_SIGNATURE_KEY" value="{{  env('AAMARPAY_SIGNATURE_KEY') }}" placeholder="{{ ('Aamarpay signature key')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Aamarpay Sandbox Mode')}}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="aamarpay_sandbox" type="checkbox" @if (get_setting('aamarpay_sandbox') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ ('Iyzico Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="iyzico">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="IYZICO_API_KEY">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ ('IYZICO_API_KEY')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="IYZICO_API_KEY" value="{{  env('IYZICO_API_KEY') }}" placeholder="{{ ('IYZICO API KEY') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="IYZICO_SECRET_KEY">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ ('IYZICO_SECRET_KEY')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="IYZICO_SECRET_KEY" value="{{  env('IYZICO_SECRET_KEY') }}" placeholder="{{ ('IYZICO SECRET KEY') }}" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('IYZICO Sandbox Mode')}}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="iyzico_sandbox" type="checkbox" @if (get_setting('iyzico_sandbox') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ ('Instamojo Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="instamojo">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="IM_API_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('API KEY')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="IM_API_KEY" value="{{  env('IM_API_KEY') }}" placeholder="{{ ('IM API KEY') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="IM_AUTH_TOKEN">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('AUTH TOKEN')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="IM_AUTH_TOKEN" value="{{  env('IM_AUTH_TOKEN') }}" placeholder="{{ ('IM AUTH TOKEN') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Instamojo Sandbox Mode')}}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="instamojo_sandbox" type="checkbox" @if (get_setting('instamojo_sandbox') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ ('PayStack Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="paystack">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYSTACK_PUBLIC_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('PUBLIC KEY')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="PAYSTACK_PUBLIC_KEY" value="{{  env('PAYSTACK_PUBLIC_KEY') }}" placeholder="{{ ('PUBLIC KEY') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYSTACK_SECRET_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('SECRET KEY')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="PAYSTACK_SECRET_KEY" value="{{  env('PAYSTACK_SECRET_KEY') }}" placeholder="{{ ('SECRET KEY') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MERCHANT_EMAIL">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('MERCHANT EMAIL')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="MERCHANT_EMAIL" value="{{  env('MERCHANT_EMAIL') }}" placeholder="{{ ('MERCHANT EMAIL') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYSTACK_CURRENCY_CODE">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('PAYSTACK CURRENCY CODE')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="PAYSTACK_CURRENCY_CODE" value="{{  env('PAYSTACK_CURRENCY_CODE') }}" placeholder="{{ ('PAYSTACK CURRENCY CODE') }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ ('Payhere Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="payhere">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYHERE_MERCHANT_ID">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('PAYHERE MERCHANT ID')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="PAYHERE_MERCHANT_ID" value="{{  env('PAYHERE_MERCHANT_ID') }}" placeholder="{{ ('PAYHERE MERCHANT ID') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYHERE_SECRET">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('PAYHERE SECRET')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="PAYHERE_SECRET" value="{{  env('PAYHERE_SECRET') }}" placeholder="{{ ('PAYHERE SECRET') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYHERE_CURRENCY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('PAYHERE CURRENCY')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="PAYHERE_CURRENCY" value="{{  env('PAYHERE_CURRENCY') }}" placeholder="{{ ('PAYHERE CURRENCY') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Payhere Sandbox Mode')}}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="payhere_sandbox" type="checkbox" @if (get_setting('payhere_sandbox') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ ('Ngenius Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="ngenius">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NGENIUS_OUTLET_ID">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ ('NGENIUS OUTLET ID')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="NGENIUS_OUTLET_ID" value="{{  env('NGENIUS_OUTLET_ID') }}" placeholder="{{ ('NGENIUS OUTLET ID') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NGENIUS_API_KEY">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ ('NGENIUS API KEY')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="NGENIUS_API_KEY" value="{{  env('NGENIUS_API_KEY') }}" placeholder="{{ ('NGENIUS API KEY') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NGENIUS_CURRENCY">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ ('NGENIUS CURRENCY')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="NGENIUS_CURRENCY" value="{{  env('NGENIUS_CURRENCY') }}" placeholder="{{ ('NGENIUS CURRENCY') }}" required>
                                <br>
                                <div class="alert alert-primary" role="alert">
                                    Currency must be <b>AED</b> or <b>USD</b> or <b>EUR</b><br>
                                    If kept empty, <b>AED</b> will be used automatically
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ ('VoguePay Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="voguepay">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="VOGUE_MERCHANT_ID">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('MERCHANT ID')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="VOGUE_MERCHANT_ID" value="{{  env('VOGUE_MERCHANT_ID') }}" placeholder="{{ ('MERCHANT ID') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Sandbox Mode')}}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="voguepay_sandbox" type="checkbox" @if (get_setting('voguepay_sandbox') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ ('RazorPay Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="razorpay">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="RAZOR_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('RAZOR KEY')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="RAZOR_KEY" value="{{  env('RAZOR_KEY') }}" placeholder="{{ ('RAZOR KEY') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="RAZOR_SECRET">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('RAZOR SECRET')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="RAZOR_SECRET" value="{{  env('RAZOR_SECRET') }}" placeholder="{{ ('RAZOR SECRET') }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- Authorize Net --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ ('Authorize Net')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="authorizenet">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MERCHANT_LOGIN_ID">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ ('MERCHANT_LOGIN_ID')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="MERCHANT_LOGIN_ID" value="{{  env('MERCHANT_LOGIN_ID') }}" placeholder="{{ ('MERCHANT LOGIN ID') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MERCHANT_TRANSACTION_KEY">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ ('MERCHANT_TRANSACTION_KEY')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="MERCHANT_TRANSACTION_KEY" value="{{  env('MERCHANT_TRANSACTION_KEY') }}" placeholder="{{ ('MERCHANT TRANSACTION KEY') }}" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ ('Authorize Net Sandbox Mode')}}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="authorizenet_sandbox" type="checkbox" @if (get_setting('authorizenet_sandbox') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ ('Payku')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="payku">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYKU_BASE_URL">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ ('PAYKU_BASE_URL')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="PAYKU_BASE_URL" value="{{  env('PAYKU_BASE_URL') }}" placeholder="{{ ('PAYKU_BASE_URL') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYKU_PUBLIC_TOKEN">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ ('PAYKU_PUBLIC_TOKEN')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="PAYKU_PUBLIC_TOKEN" value="{{  env('PAYKU_PUBLIC_TOKEN') }}" placeholder="{{ ('PAYKU_PUBLIC_TOKEN') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYKU_PRIVATE_TOKEN">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ ('PAYKU_PRIVATE_TOKEN')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="PAYKU_PRIVATE_TOKEN" value="{{  env('PAYKU_PRIVATE_TOKEN') }}" placeholder="{{ ('PAYKU_PRIVATE_TOKEN') }}" required>
                            </div>
                        </div>

                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    @endsection
