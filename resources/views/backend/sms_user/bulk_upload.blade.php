@extends('backend.layouts.app')

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ ('SMS User Bulk Upload')}}</h5>
        </div>
        <div class="card-body">
            <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                <strong>{{ ('Step')}}:</strong>
                <p>1. {{ ('Download the skeleton file and fill it with proper data')}}.</p>
                <p>2. {{ ('You can download the example file to understand how the data must be filled')}}.</p>
                <p>3. {{ ('Once you have downloaded and filled the skeleton file, upload it in the form below and submit')}}.</p>
                <p>4. {{ ('After uploading products you can edit them if you need')}}.</p>
            </div>
            <br>
            <div class="">
                <a href="{{ static_asset('download/sms_user_bulk_demo.xlsx') }}" download><button class="btn btn-info">{{ ('Download CSV')}}</button></a>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6"><strong>{{ ('Upload SMS User File')}}</strong></h5>
        </div>
        <div class="card-body">
            <form class="form-horizontal" action="{{ route('sms_user.bulk_sms_user_upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group row">
                    <div class="col-sm-9">
                        <div class="custom-file">
    						<label class="custom-file-label">
    							<input type="file" name="sms_user_bulk_file" class="custom-file-input" required>
    							<span class="custom-file-name">{{ ('Choose File')}}</span>
    						</label>
    					</div>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-info">{{ ('Upload CSV')}}</button>
                </div>
            </form>
        </div>
    </div>

@endsection
