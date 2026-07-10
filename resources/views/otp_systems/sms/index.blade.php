@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3 class="fs-18 mb-0">{{ ('Send Bulk SMS')}}</h3>
            </div>
            <form class="form-horizontal" action="{{ route('sms.send') }}" method="POST" enctype="multipart/form-data">
            	@csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-2 control-label" for="name"></label>
                        <div class="col-sm-10">
                            <input type="checkbox" class="" name="register_type" value="All"> {{ ('All Registered Mobile User')}}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 control-label" for="name">{{ ('Mobile Users')}}</label>
                        <div class="col-sm-10">
                            <select class="form-control aiz-selectpicker" data-live-search="true" name="user_phones[]" multiple>
                                @foreach($users as $phone => $name)
                                    <option value="{{$phone}}">{{$name}} - {{$phone}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 control-label" for="name"></label>
                        <div class="col-sm-10">
                            <input type="checkbox" class="" name="unregister_type" value="All"> {{ ('All Unregistered Mobile User')}}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 control-label" for="name">{{ ('Unregistered Mobile Users')}}</label>
                        <div class="col-sm-10">
                            <select class="form-control aiz-selectpicker unregisteruser" data-live-search="true" name="unregister_user_phones[]" multiple>
                                @foreach($smsusers as $phone)
                                    <option value="{{$phone}}">{{$phone}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 control-label" for="name">{{ ('SMS content')}}</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" name="content" required></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">{{ ('Template ID')}}</label>
                        <div class="col-md-10">
                            <input type="text" name="template_id"  class="form-control" placeholder="{{ ('Template Id')}}">
                            <small class="form-text text-danger">{{ ('**N.B : Template ID is Required Only for Fast2SMS DLT Manual **') }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" type="submit">{{ ('Send')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    // $(document).ready(function(){
    //     $(document).on('change', '.unregisteruser', function(){
    //         var unregval = $(this).val();
    //         //alert(unregval);
    //         if(unregval=='All'){
    //         $(".unregisteruser option").each(function()
    //         {
    //             $(this).attr('selected','selected');
    //         });
    //         $(".unregisteruser option")
    //     $('.unregisteruser').trigger('change');
    //     }
    //     });

    // });
</script>
@endsection
