@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ ('Facebook Chat Setting')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('facebook_chat.update') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-3">
                                <label class="col-from-label">{{ ('Facebook Chat')}}</label>
                            </div>
                            <div class="col-md-7">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="facebook_chat" type="checkbox" @if (get_setting('facebook_chat') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="FACEBOOK_PAGE_ID">
                            <div class="col-md-3">
                                <label class="col-from-label">{{ ('Facebook Page ID')}}</label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="FACEBOOK_PAGE_ID" value="{{  env('FACEBOOK_PAGE_ID') }}" placeholder="{{ ('Facebook Page ID') }}" required>
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
            <div class="card bg-gray-light">
              <div class="card-header">
                  <h5 class="mb-0 h6">{{ ('Please be carefull when you are configuring Facebook chat. For incorrect configuration you will not get messenger icon on your user-end site.') }}</h5>
              </div>
                <div class="card-body">
                    <ul class="list-group mar-no">
                        <li class="list-group-item text-dark">1. {{ ('Login into your facebook page') }}</li>
                        <li class="list-group-item text-dark">2. {{ ('Find the About option of your facebook page') }}.</li>
                        <li class="list-group-item text-dark">3. {{ ('At the very bottom, you can find the “Facebook Page ID”') }}.</li>
                        <li class="list-group-item text-dark">4. {{ ('Go to Settings of your page and find the option of "Advance Messaging"') }}.</li>
                        <li class="list-group-item text-dark">5. {{ ('Scroll down that page and you will get "white listed domain"') }}.</li>
                        <li class="list-group-item text-dark">6. {{ ('Set your website domain name') }}.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
