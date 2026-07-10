@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{ ('Attribute Information')}}</h5>
</div>

<div class="col-lg-8 mx-auto">
    <div class="card">
        <div class="card-body p-0">

          <form class="p-4" action="{{ route('reviewcomments.update', $reviewcomment->id) }}" method="POST">
            <input name="_method" type="hidden" value="PATCH">
              @csrf
              <div class="form-group row">
                  <label class="col-sm-3 col-from-label" for="name">{{ ('Title')}}</label>
                  <div class="col-sm-9">
                      <input type="text" placeholder="{{ ('Title')}}" id="title" name="title" class="form-control" required value="{{ $reviewcomment->title }}">
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
