@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{ ('Shipping Method')}}</h5>
</div>

<div class="row">
  <div class="col-lg-8 mx-auto">
      <div class="card">
          <div class="card-body p-0">

              <form class="p-4" action="{{ route('shipping_method.update', $shippingMethod->id) }}" method="POST" enctype="multipart/form-data">
                  <input name="_method" type="hidden" value="PATCH">
                  @csrf
                  <div class="form-group mb-3">
                      <label for="name">{{ ('Name')}}</label>
                      <input type="text" placeholder="{{ ('Name')}}" value="{{ $shippingMethod->name }}" name="name" class="form-control" required>
                  </div>

                    <div class="form-group mb-3">
                        <label class="col-12 col-form-label px-0" for="signinSrEmail">{{ ('Logo')}} <small>(300x300)</small></label>
                        <div class="col-12 px-0">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="logo" value="{{ $shippingMethod->logo }}" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                            <small class="text-muted">{{ ('This image is visible in all product box. Use 300x300 sizes image. Keep some blank space around main object of your image as we had to crop some edge in different devices to make it responsive.')}}</small>
                        </div>
                    </div>


                  <div class="form-group mb-3 text-right">
                      <button type="submit" class="btn btn-primary">{{ ('Update')}}</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
</div>

@endsection
