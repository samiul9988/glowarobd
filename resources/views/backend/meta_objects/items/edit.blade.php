@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{ ('Edit Item')}}</h5>
</div>
<div class="">
    <form class="form form-horizontal mar-top" action="{{route('meta-object-items.update', $item->id)}}" method="POST" enctype="multipart/form-data" id="choice_form">
        <div class="row gutters-5">
            <div class="col-lg-12">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Meta Object Item')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Item Title')}} <span class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="title" placeholder="{{ ('Item Title') }}" value="{{ old('title', $item->title) }}" required>
                                @error('title')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Item Subtitle')}}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="subtitle" value="{{ old('subtitle', $item->subtitle) }}" placeholder="{{ ('Item Subtitle') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Item Description')}}</label>
                            <div class="col-md-8">
                                <textarea class="form-control" name="description" rows="4" placeholder="{{ ('Item Description') }}">{{ old('description', $item->description) }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row" id="category">
                            <label class="col-md-3 col-from-label">{{ ('Meta Object')}}</label>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" name="meta_object_id" id="meta_object_id" data-live-search="true" data-selected="{{ $item->meta_object_id }}">
                                    <option value="">{{ ('Select Meta Object') }}</option>
                                    @foreach ($metaObjects as $key => $name)
                                    <option value="{{ $key }}" @if(old('meta_object_id') == $key) selected @endif>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Image')}} <small>(300x300)</small></label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="image" class="selected-files" value="{{ $item->image }}">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Url')}}</label>
                            <div class="col-md-8">
                                <input type="url" class="form-control" name="url" value="{{ old('url', $item->url) }}" placeholder="{{ ('Url') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Status')}}</label>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" name="is_active">
                                    <option value="1" @if(old('is_active', $item->is_active) == 1) selected @endif>{{ ('Active')}}</option>
                                    <option value="0" @if(old('is_active', $item->is_active) == 0) selected @endif>{{ ('Inactive')}}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="btn-toolbar float-right mb-3" role="toolbar" aria-label="Toolbar with button groups">
                    <div class="btn-group mr-2" role="group" aria-label="Second group">
                        <a href="{{ route('meta-object-items.index') }}" class="btn btn-secondary">{{ ('Cancel') }}</a>
                    </div>
                    <div class="btn-group" role="group" aria-label="Second group">
                        <button type="submit" class="btn btn-success">{{ ('Update') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('script')
@endsection
