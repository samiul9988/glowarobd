@extends('backend.layouts.app')

@section('content')

    <div class="row">
        <div class="col-xxl-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="fs-18 mb-0 text-center">{{ ('S3 File System Credentials')}}</h3>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        <input type="hidden" name="payment_method" value="paypal">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="AWS_ACCESS_KEY_ID">
                            <div class="col-lg-4">
                                <label class="control-label">{{ ('AWS_ACCESS_KEY_ID')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="AWS_ACCESS_KEY_ID" value="{{  env('AWS_ACCESS_KEY_ID') }}" placeholder="{{ ('AWS_ACCESS_KEY_ID') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="AWS_SECRET_ACCESS_KEY">
                            <div class="col-lg-4">
                                <label class="control-label">{{ ('AWS_SECRET_ACCESS_KEY')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="AWS_SECRET_ACCESS_KEY" value="{{  env('AWS_SECRET_ACCESS_KEY') }}" placeholder="{{ ('AWS_SECRET_ACCESS_KEY') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="AWS_DEFAULT_REGION">
                            <div class="col-lg-4">
                                <label class="control-label">{{ ('AWS_DEFAULT_REGION')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="AWS_DEFAULT_REGION" value="{{  env('AWS_DEFAULT_REGION') }}" placeholder="{{ ('AWS_DEFAULT_REGION') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="AWS_BUCKET">
                            <div class="col-lg-4">
                                <label class="control-label">{{ ('AWS_BUCKET')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="AWS_BUCKET" value="{{  env('AWS_BUCKET') }}" placeholder="{{ ('AWS_BUCKET') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="AWS_URL">
                            <div class="col-lg-4">
                                <label class="control-label">{{ ('AWS_URL')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="AWS_URL" value="{{  env('AWS_URL') }}" placeholder="{{ ('AWS_URL') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-lg-12 text-right">
                                <button class="btn btn-primary" type="submit">{{ ('Save')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xxl-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="fs-18 mb-0 text-center">{{ ('S3 File System Activation')}}</h3>
                </div>
                <div class="card-body">
                    <label class="aiz-switch mb-0">
                        <input type="checkbox" onchange="updateSettings(this, 'FILESYSTEM_DRIVER')" <?php if(env('FILESYSTEM_DRIVER') == 's3') echo "checked";?>>
                        <span></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="fs-18 mb-0 text-center">{{ ('Cache & Session Driver')}}</h3>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        <input type="hidden" name="payment_method" value="paypal">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="CACHE_DRIVER">
                            <div class="col-lg-4">
                                <label class="control-label">{{ ('CACHE_DRIVER')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <select class="form-control aiz-selectpicker mb-2 mb-md-0" name="CACHE_DRIVER">
                                    <option value="file" @if (env('CACHE_DRIVER') == "file") selected @endif>{{ ('file') }}</option>
                                    <option value="redis" @if (env('CACHE_DRIVER') == "redis") selected @endif>{{ ('redis') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="SESSION_DRIVER">
                            <div class="col-lg-4">
                                <label class="control-label">{{ ('SESSION_DRIVER')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <select class="form-control aiz-selectpicker mb-2 mb-md-0" name="SESSION_DRIVER">
                                    <option value="file" @if (env('SESSION_DRIVER') == "file") selected @endif>{{ ('file') }}</option>
                                    <option value="redis" @if (env('SESSION_DRIVER') == "redis") selected @endif>{{ ('redis') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-lg-12 text-right">
                                <button class="btn btn-primary" type="submit">{{ ('Save')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xxl-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="fs-18 mb-0 text-center">{{ ('Redis Configuration (If you use redis as any of the drivers)')}}</h3>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        <input type="hidden" name="payment_method" value="paypal">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="REDIS_HOST">
                            <div class="col-lg-4">
                                <label class="control-label">{{ ('REDIS_HOST')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="REDIS_HOST" value="{{  env('REDIS_HOST') }}" placeholder="{{ ('REDIS_HOST') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="REDIS_PASSWORD">
                            <div class="col-lg-4">
                                <label class="control-label">{{ ('REDIS_PASSWORD')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="REDIS_PASSWORD" value="{{  env('REDIS_PASSWORD') }}" placeholder="{{ ('REDIS_PASSWORD') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="REDIS_PORT">
                            <div class="col-lg-4">
                                <label class="control-label">{{ ('REDIS_PORT')}}</label>
                            </div>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="REDIS_PORT" value="{{  env('REDIS_PORT') }}" placeholder="{{ ('REDIS_PORT') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-lg-12 text-right">
                                <button class="btn btn-primary" type="submit">{{ ('Save')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        function updateSettings(el, type){
            if($(el).is(':checked')){
                var value = 1;
            }
            else{
                var value = 0;
            }
            $.post('{{ route('business_settings.update.activation') }}', {_token:'{{ csrf_token() }}', type:type, value:value}, function(data){
                if(data == '1'){
                    AIZ.plugins.notify('success', '{{ ('Settings updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }
    </script>
@endsection
