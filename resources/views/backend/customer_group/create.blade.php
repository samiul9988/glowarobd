@extends('backend.layouts.app')

@section('content')
<form action="{{route('customer.group.store')}}" method="post">
@csrf
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">Create Customer Group</h5>
    </div>
    <div class="card-body">
        <div class="form-group row">
            <label class="col-md-2 col-from-label">Group Name <span class="text-danger">*</span></label>
            <div class="col-md-6">
                <input type="text" class="form-control" name="group_name" placeholder="Group Name" required>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-from-label">Min Order Amount <span class="text-danger">*</span></label>
            <div class="col-md-6">
                <input type="number" min="0" step="1" class="form-control" name="min_order_amount" placeholder="0" required>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-from-label">Min Order Qty <span class="text-danger">*</span></label>
            <div class="col-md-6">
                <input type="number" min="0" step="1" class="form-control" name="min_order_qty" placeholder="0" required>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-from-label" for="start_date">{{ ('Discount Date Range')}}</label>
            <div class="col-sm-6">
                <input type="text" class="form-control aiz-date-range" value="" name="date_range" placeholder="Select Date" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
            </div>
        </div>


        <div class="form-group row">
            <label class="col-sm-2 col-from-label" for="discount">{{ ('Discount')}} </label>
            <div class="col-sm-6">
                <input type="number" placeholder="0" lang="en" name="discount" value="" min="0" step="1" class="form-control" >
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-from-label" for="discount">{{ ('Discount Type')}} </label>
            <div class="col-sm-6">
                <select class="aiz-selectpicker" name="discount_type">
                    <option value="amount">{{ ('Flat') }}</option>
                    <option value="percent">{{ ('Percent') }}</option>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-from-label" for="discount">{{ ('Delivery Discount Status')}} </label>
            <div class="col-sm-6">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" name="delivery_discount" value="1">
                    <span></span>
                </label>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-from-label" for="discount">{{ ('Delivery Discount Amount')}} </label>
            <div class="col-sm-6">
                <input type="number" placeholder="0" lang="en" name="delivery_discount_amount" value="" min="0" step="1" class="form-control" >
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2 col-from-label">Group Icon <span class="text-danger">*</span></label>
            <div class="col-md-6">
                <input type="text" class="form-control" name="group_icon" placeholder="" required>
                <small class="text-muted">
                    Use line awesome link
                </small>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2 col-form-label" for="signinSrEmail">Thumbnail Image <span class="text-danger"></span> <small>(300x300)</small></label>
            <div class="col-md-6">
                <div class="input-group" data-toggle="aizuploader" data-type="image">
                    <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">Browse</div>
                    </div>
                    <div class="form-control file-amount">Choose File</div>
                    <input type="hidden" name="group_image" class="selected-files">
                </div>
                <div class="file-preview box sm">
                </div>
                <small class="text-muted">This image is visible in all product box. Use 300x300 sizes image.</small>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-2 col-from-label">Message</label>
            <div class="col-sm-6">
                <span>Download the skeleton file and fill it with proper data. <strong><a href="{{ static_asset('download/customer-group-benefits.json') }}" download>Download Sample</a></strong></span>
                <textarea class="form-control" name="message" placeholder="Write here..." id="" rows="6"></textarea>
            </div>
        </div>


        <div class="form-group row">
            <label class="col-sm-2 col-from-label" for="discount">{{ ('Priority / Ordering')}} <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <input type="number" placeholder="1 or 2 or 3" lang="en" name="ordering" class="form-control" required>
            </div>
        </div>
    </div>
</div>


<div class="col-12">
    <div class="btn-toolbar float-right mb-3" role="toolbar" aria-label="Toolbar with button groups">
        <div class="btn-group" role="group" aria-label="Second group">
            <button type="submit" name="button" value="publish" class="btn btn-success">Save</button>
        </div>
    </div>
</div>
<form>
@endsection
