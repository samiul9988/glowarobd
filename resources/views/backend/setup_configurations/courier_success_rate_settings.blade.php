@extends('backend.layouts.app')

@section('content')

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{ ('Courier Success Rate Settings')}}</h1>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('business_settings.update') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('API Key')}}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="courier_success_rate_api_key">
                                <input type="text" name="courier_success_rate_api_key" class="form-control" value="{{ get_setting('courier_success_rate_api_key') }}" placeholder="Enter api key">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('Interval')}}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="courier_success_rate_interval">
                                <input type="text" name="courier_success_rate_interval" class="form-control" value="{{ get_setting('courier_success_rate_interval') }}" placeholder="Interval in days">
                                <small class="text-muted">{{ ('Default 7 days') }}</small>
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
