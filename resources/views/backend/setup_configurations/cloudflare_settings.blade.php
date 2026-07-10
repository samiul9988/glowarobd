@extends('backend.layouts.app')

@section('content')

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{ ('Cloudflare Settings')}}</h1>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('business_settings.update') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('Zone ID')}}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="cloudflare_zone_id">
                                <input type="text" name="cloudflare_zone_id" class="form-control" value="{{ get_setting('cloudflare_zone_id') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('API Key')}}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="cloudflare_api_key">
                                <input type="text" name="cloudflare_api_key" class="form-control" value="{{ get_setting('cloudflare_api_key') }}">
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
