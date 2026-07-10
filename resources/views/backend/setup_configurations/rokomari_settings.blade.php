@extends('backend.layouts.app')

@section('content')

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{ ('Rokomari Settings')}}</h1>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('business_settings.update') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('API Key')}}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="rokomari_api_key">
                                <input type="text" name="rokomari_api_key" class="form-control" value="{{ get_setting('rokomari_api_key') }}" placeholder="{{ ('Enter your Rokomari API Key')}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('API Secret')}}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="rokomari_api_secret">
                                <input type="text" name="rokomari_api_secret" class="form-control" value="{{ get_setting('rokomari_api_secret') }}" placeholder="{{ ('Enter your Rokomari API Secret')}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('Access Token')}}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="rokomari_access_token">
                                <input type="text" name="rokomari_access_token" class="form-control" value="{{ get_setting('rokomari_access_token') }}" placeholder="{{ ('Enter your Rokomari Access Token')}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('Refresh Token')}}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="rokomari_refresh_token">
                                <input type="text" name="rokomari_refresh_token" class="form-control" value="{{ get_setting('rokomari_refresh_token') }}" placeholder="{{ ('Enter your Rokomari Refresh Token')}}">
                            </div>
                        </div>
                        <div class="text-left">
    						<button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
    					</div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
