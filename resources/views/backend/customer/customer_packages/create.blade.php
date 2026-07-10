@extends('backend.layouts.app')
@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Create New Package')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('customer_packages.store') }}" method="POST" >
                  	@csrf
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{ ('Package Name')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Name')}}" id="name" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{ ('Amount')}}</label>
                        <div class="col-sm-9">
                            <input type="number" lang="en" min="0" step="0.01" placeholder="{{ ('Amount')}}" id="amount" name="amount" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{ ('Product Upload')}}</label>
                        <div class="col-sm-9">
                            <input type="number" lang="en" min="0" step="1" placeholder="{{ ('Product Upload')}}" id="product_upload" name="product_upload" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Package logo')}}</label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="logo" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{ ('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
