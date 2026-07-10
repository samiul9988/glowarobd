@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Merchant Information')}}</h5>
            </div>

            <form action="{{ route('merchants.update', $merchant->id) }}" method="POST">
                <input name="_method" type="hidden" value="PATCH">
            	@csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{ ('Name')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Name')}}" id="name" name="name" value="{{ $merchant->name }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="email">{{ ('Email')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Email')}}" id="email" name="email" value="{{ $merchant->email }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="mobile">{{ ('Phone')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Phone')}}" id="mobile" name="mobile" value="{{ $merchant->phone }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="password">{{ ('Password')}}</label>
                        <div class="col-sm-9">
                            <input type="password" placeholder="{{ ('Password')}}" id="password" name="password" class="form-control">
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
