@extends('backend.layouts.app')

@section('content')

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{ ('Notification Settings')}}</h1>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('business_settings.update') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Notification On/Off')}}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="notification_status">
                                    <input type="checkbox" name="notification_status" @if( get_setting('notification_status') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        {{-- <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Firebase Settings')}}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="firebase_settings">
                                    <input type="checkbox" name="firebase_settings" @if( get_setting('firebase_settings') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('One Signal Settings')}}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="one_signal_settings">
                                    <input type="checkbox" name="one_signal_settings" @if( get_setting('one_signal_settings') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div> --}}




                        <div class="text-left">
    						<button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
    					</div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
