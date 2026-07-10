@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{ ('Add New Seller')}}</h5>
</div>

<div class="col-lg-6 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ ('Seller Information')}}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('sellers.store') }}" method="POST">
            	@csrf
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="name">{{ ('Name')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{ ('Name')}}" id="name" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="email">{{ ('Email Address')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{ ('Email Address')}}" id="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="password">{{ ('Password')}}</label>
                    <div class="col-sm-9">
                        <input type="password" placeholder="{{ ('Password')}}" id="password" name="password" class="form-control" required>
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
