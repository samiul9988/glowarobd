@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{ ('SMS User Information')}}</h5>
</div>

<div class="col-lg-8 mx-auto">
    <div class="card">
        <div class="card-body p-0">

            <form class="p-4" action="{{ route('sms_user.update', $smsuser->id) }}" method="POST" enctype="multipart/form-data">
                <input name="_method" type="hidden" value="PATCH">
                <input type="hidden" name="lang" value="{{ $lang }}">
                @csrf

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="name">{{ ('Mobile Number')}} <i class="las la-language text-danger" title="{{ ('Translatable')}}"></i></label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{ ('Mobile Number')}}" id="name" name="mobile_number" value="{{ $smsuser->mobile_number }}" class="form-control" required>
                    </div>
                </div>

                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary">{{ ('Save')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
