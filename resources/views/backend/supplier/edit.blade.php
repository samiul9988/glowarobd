@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">Update Supplier Information</h5>
                </div>
                <div class="card-body">
                    <form method="post"  action="{{route('supplier.update', $data->id)}}" method="post">
                    @csrf
                    <div class="form-group row">
                        <label class="col-md-2 col-from-label">Name <span class="text-danger">*</span></label>
                        <div class="col-md-8">
                            <input type="text" value="{{$data->name}}" class="form-control" name="name" placeholder="Name..." onchange="update_sku()" required="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-from-label">Contact Number <span class="text-danger">*</span></label>
                        <div class="col-md-8">
                            <input type="text" value="{{$data->contact_number}}" class="form-control" name="contact_number" placeholder="Contact No..." onchange="update_sku()" required="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-from-label">Address <span class="text-danger">*</span></label>
                        <div class="col-md-8">
                            <input type="text" value="{{$data->address}}" class="form-control" name="address" placeholder="Address..." onchange="update_sku()" required="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-from-label">Sticker Template</label>
                        <div class="col-md-8">
                            <select name="template_id" id="template" class="form-control aiz-selectpicker" data-live-search="true">
                                <option value="">{{ ('Select Template') }}</option>
                                @foreach (\App\Models\Template::active()->where('type', 'product_sticker')->get() as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-from-label" for="supplier_logo">{{ ('Logo')}} <small>({{ ('120x80') }})</small></label>
                        <div class="col-md-8">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="logo" value="{{$data->logo}}" id="supplier_logo" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-10">
                            <div class="btn-toolbar float-right mb-3" role="toolbar" aria-label="Toolbar with button groups">
                                <div class="btn-group mr-2" role="group" aria-label="Third group">
                                    <a href="{{ route('supplier.index')}}" class="btn btn-primary">{{ ('Cancel') }}</a>
                                </div>
                                <div class="btn-group" role="group" aria-label="Second group">
                                    <button type="submit" name="button" value="publish" class="btn btn-success">{{ ('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
