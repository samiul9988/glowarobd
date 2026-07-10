@extends('backend.layouts.app')

@section('content')

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ ('Google Tag Manager Setting')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('google_tag_manager.update') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('Google Tag Manager')}}</label>
                            </div>
                            <div class="col-md-7">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="google_tagmanager" type="checkbox" @if (get_setting('google_tagmanager') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="TAG_MANAGER_ID">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ ('Tracking ID')}}</label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="TAG_MANAGER_ID" value="{{  get_setting('google_tagmanager_id') }}" placeholder="{{ ('Tag Manager ID') }}" required>
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
