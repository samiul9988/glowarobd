@extends('backend.layouts.app')

@section('content')

{{-- @if(config('app.name')=='ECOM71')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ ('Twilio Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('update_credentials') }}" method="POST">
                        <input type="hidden" name="otp_method" value="twillo">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="TWILIO_SID">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('TWILIO SID')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="TWILIO_SID" value="{{  env('TWILIO_SID') }}" placeholder="TWILIO SID" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="TWILIO_AUTH_TOKEN">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('TWILIO AUTH TOKEN')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="TWILIO_AUTH_TOKEN" value="{{  env('TWILIO_AUTH_TOKEN') }}" placeholder="TWILIO AUTH TOKEN" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="VALID_TWILLO_NUMBER">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('VALID TWILIO NUMBER')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="VALID_TWILLO_NUMBER" value="{{  env('VALID_TWILLO_NUMBER') }}" placeholder="VALID TWILLO NUMBER" >
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
                    <h5 class="mb-0 h6">{{ ('Nexmo Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('update_credentials') }}" method="POST">
                        <input type="hidden" name="otp_method" value="nexmo">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NEXMO_KEY">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('NEXMO KEY')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="NEXMO_KEY" value="{{  env('NEXMO_KEY') }}" placeholder="NEXMO KEY" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NEXMO_SECRET">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('NEXMO SECRET')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="NEXMO_SECRET" value="{{  env('NEXMO_SECRET') }}" placeholder="NEXMO SECRET" required>
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
@endif --}}

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ ('ViaTech Limited Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('update_credentials') }}" method="POST">
                        <input type="hidden" name="otp_method" value="viatech">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="VIATECH_SMS_API_KEY">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('VIATECH SMS API KEY')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="VIATECH_SMS_API_KEY" value="{{  env('VIATECH_SMS_API_KEY') }}" placeholder="VIATECH SMS API KEY" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="VIATECH_SMS_SID">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('VIATECH SMS SID (SENDER ID)')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="VIATECH_SMS_SID" value="{{  env('VIATECH_SMS_SID') }}" placeholder="VIATECH SMS SID" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="VIATECH_SMS_URL">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('VIATECH SMS URL')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="VIATECH_SMS_URL" value="{{  env('VIATECH_SMS_URL') }}" placeholder="VIATECH SMS URL" >
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
                    <h5 class="mb-0 h6">{{ ('SSL Wireless Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('update_credentials') }}" method="POST">
                        <input type="hidden" name="otp_method" value="ssl_wireless">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="SSL_SMS_API_TOKEN">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('SSL SMS API TOKEN')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="SSL_SMS_API_TOKEN" value="{{  env('SSL_SMS_API_TOKEN') }}" placeholder="SSL SMS API TOKEN" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="SSL_SMS_SID">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('SSL SMS SID')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="SSL_SMS_SID" value="{{  env('SSL_SMS_SID') }}" placeholder="SSL SMS SID" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="SSL_SMS_URL">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('SSL SMS URL')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="SSL_SMS_URL" value="{{  env('SSL_SMS_URL') }}" placeholder="SSL SMS URL" >
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if(config('app.name')=='ECOM71')
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ ('Fast2SMS Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('update_credentials') }}" method="POST">
                        <input type="hidden" name="otp_method" value="fast2sms">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="AUTH_KEY">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('AUTH KEY')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="AUTH_KEY" value="{{  env('AUTH_KEY') }}" placeholder="AUTH KEY" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="ENTITY_ID">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('ENTITY ID')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="ENTITY_ID" value="{{  env('ENTITY_ID') }}" placeholder="{{ ('Entity ID') }}" >
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="ROUTE">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('ROUTE')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <select class="form-control aiz-selectpicker" name="ROUTE" required>
                                    <option value="dlt_manual" @if (env('ROUTE') == "dlt_manual") selected @endif>{{ ('DLT Manual')}}</option>
                                    <option value="p" @if (env('ROUTE') == "p") selected @endif>{{ ('Promotional Use')}}</option>
                                    <option value="t" @if (env('ROUTE') == "t") selected @endif>{{ ('Transactional Use')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="LANGUAGE">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('LANGUAGE')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <select class="form-control aiz-selectpicker" name="LANGUAGE" required>
                                    <option value="english" @if (env('LANGUAGE') == "english") selected @endif>English</option>
                                    <option value="unicode" @if (env('LANGUAGE') == "unicode") selected @endif>Unicode</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="SENDER_ID">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('SENDER ID')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="SENDER_ID" value="{{  env('SENDER_ID') }}" placeholder="6 digit SENDER ID" >
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
    @if(config('app.name')=='ECOM71')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ ('MIMO Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('update_credentials') }}" method="POST">
                        <input type="hidden" name="otp_method" value="mimo">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MIMO_USERNAME">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('MIMO_USERNAME')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="MIMO_USERNAME" value="{{  env('MIMO_USERNAME') }}" placeholder="MIMO_USERNAME" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MIMO_PASSWORD">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('MIMO_PASSWORD')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="MIMO_PASSWORD" value="{{  env('MIMO_PASSWORD') }}" placeholder="MIMO_PASSWORD" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MIMO_SENDER_ID">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('MIMO_SENDER_ID')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="MIMO_SENDER_ID" value="{{  env('MIMO_SENDER_ID') }}" placeholder="MIMO_SENDER_ID" required>
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
                    <h5 class="mb-0 h6">{{ ('MIMSMS Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('update_credentials') }}" method="POST">
                        <input type="hidden" name="otp_method" value="mimsms">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MIM_API_KEY">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('MIM_API_KEY')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="MIM_API_KEY" value="{{  env('MIM_API_KEY') }}" placeholder="MIM_API_KEY" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MIM_SENDER_ID">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('MIM_SENDER_ID')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="MIM_SENDER_ID" value="{{  env('MIM_SENDER_ID') }}" placeholder="MIM_SENDER_ID" required>
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
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ ('MSEGAT Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('update_credentials') }}" method="POST">
                        <input type="hidden" name="otp_method" value="msegat">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MSEGAT_API_KEY">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('MSEGAT_API_KEY')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="MSEGAT_API_KEY" value="{{  env('MSEGAT_API_KEY') }}" placeholder="MSEGAT_API_KEY" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MSEGAT_USERNAME">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('MSEGAT_USERNAME')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="MSEGAT_USERNAME" value="{{  env('MSEGAT_USERNAME') }}" placeholder="MSEGAT_USERNAME" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MSEGAT_USER_SENDER">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('MSEGAT_USER_SENDER')}}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="MSEGAT_USER_SENDER" value="{{  env('MSEGAT_USER_SENDER') }}" placeholder="MSEGAT_USER_SENDER" required>
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
    @endif
    
    <!-- Test SMS -->
    {{-- <div class="row">
        <div class="col-12">
            <h2 class="text-center">Test SMS</h2>
        </div>
        <div class="col-lg-6 m-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ ('Test SMS')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('sms.test') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('Enter Mobile Number')}}</label>
                            </div>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" name="mobile" placeholder="ENTER RECIPIENT MOBILE NUMBER" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('Enter SMS Content')}}</label>
                            </div>
                            <div class="col-lg-9">
                                <textarea name="sms" id="sms" class="form-control" cols="30" rows="5" required>{{ 'This test message for SMS test.' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('SEND TEST SMS')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}
@endsection

@section('script')
    <script type="text/javascript">

    </script>
@endsection
