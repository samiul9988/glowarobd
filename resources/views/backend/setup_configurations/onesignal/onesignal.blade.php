@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0 h6">{{ ('Onesignal Setting')}}</h3>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('onesignal.update') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="control-label">{{ ('Onesignal')}}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="onesignal" type="checkbox" @if (get_setting('onesignal') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="ONE_SIGNAL_APP_ID">
                            <div class="col-md-4">
                                <label class="control-label">{{ ('ONE SIGNAL APP ID')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="ONE_SIGNAL_APP_ID" value="{{  env('ONE_SIGNAL_APP_ID') }}" placeholder="{{ ('ONE SIGNAL APP ID') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="ONE_SIGNAL_API_KEY">
                            <div class="col-md-4">
                                <label class="control-label">{{ ('ONE SIGNAL APP KEYs')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="ONE_SIGNAL_API_KEY" value="{{  env('ONE_SIGNAL_API_KEY') }}" placeholder="{{ ('ONE SIGNAL APP KEY') }}">
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
