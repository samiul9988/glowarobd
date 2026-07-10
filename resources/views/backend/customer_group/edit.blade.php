@extends('backend.layouts.app')

@section('content')
<form action="{{route('customer.group.update', encrypt($group->id) )}}" method="post">
@csrf
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">Update Customer Group</h5>
    </div>
    <div class="card-body">
        <div class="form-group row">
            <label class="col-md-2 col-from-label">Group Name <span class="text-danger">*</span></label>
            <div class="col-md-6">
                <input type="text" class="form-control" name="group_name" value="{{$group->group_name}}" placeholder="Group Name" required="">
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-from-label">Min Order Amount <span class="text-danger">*</span></label>
            <div class="col-md-6">
                <input type="number" min="0" value="{{$group->min_order_amount}}" step="0.1" class="form-control" name="min_order_amount" placeholder="" required="">
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-from-label">Min Order Qty <span class="text-danger">*</span></label>
            <div class="col-md-6">
                <input type="number" min="0" value="{{$group->min_order_qty}}" step="1" class="form-control" name="min_order_qty" placeholder="" required="">
            </div>
        </div>

            @php
                if($group->start_date != 0 & $group->end_date != 0){
                    @$start_date = date('d-m-Y H:i:s', $group->start_date);
                    @$end_date = date('d-m-Y H:i:s', $group->end_date);
                }
            @endphp

        <div class="form-group row">
            <label class="col-sm-2 col-from-label" for="start_date">{{ ('Discount Date Range')}}</label>
            <div class="col-sm-6">
                <input type="text" class="form-control aiz-date-range" value="@if(@$start_date != ''){{ @$start_date.' to '.@$end_date}}@endif" name="date_range" placeholder="Select Date" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
            </div>
        </div>


        <div class="form-group row">
            <label class="col-sm-2 col-from-label" for="discount">{{ ('Discount')}} </label>
            <div class="col-sm-6">
                <input type="number" lang="en" name="discount" value="{{ $group->discount }}" min="0" step="1" class="form-control" required>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-from-label" for="discount">{{ ('Discount Type')}} </label>
            <div class="col-sm-6">
                <select class="aiz-selectpicker" name="discount_type">
                    <option value="amount" <?php if($group->discount_type == 'amount') echo "selected";?> >{{ ('Flat') }}</option>
                    <option value="percent" <?php if($group->discount_type == 'percent') echo "selected";?> >{{ ('Percent') }}</option>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-from-label" for="discount">{{ ('Delivery Discount Status')}} </label>
            <div class="col-sm-6">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" name="delivery_discount" value="1" @if($group->delivery_discount == 1) checked @endif>
                    <span></span>
                </label>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-from-label" for="discount">{{ ('Delivery Discount Amount')}} </label>
            <div class="col-sm-6">
                <input type="number" placeholder="0" lang="en" name="delivery_discount_amount" value="{{ $group->delivery_discount_amount }}" min="0" step="1" class="form-control" >
            </div>
        </div>



        <div class="form-group row">
            <label class="col-md-2 col-from-label">Group Icon <span class="text-danger">*</span></label>
            <div class="col-md-6">
                <input type="text" class="form-control" value="{{$group->group_icon}}" name="group_icon" placeholder="" required="">
                <small class="text-muted">
                    Use line awesome link
                </small>
            </div>
        </div>

<div class="form-group row">
    <label class="col-md-2 col-form-label" for="signinSrEmail">Thumbnail Image <small>(300x300)</small></label>
    <div class="col-md-6">
        <div class="input-group" data-toggle="aizuploader" data-type="image">
            <div class="input-group-prepend">
            <div class="input-group-text bg-soft-secondary font-weight-medium">Browse</div>
            </div>
            <div class="form-control file-amount">Choose File</div>
            <input type="hidden" name="group_image" class="selected-files" value="{{$group->group_image}}">
        </div>
        <div class="file-preview box sm">
        </div>
        <small class="text-muted">This image is visible in all product box. Use 300x300 sizes image.</small>
    </div>
</div>
<div class="form-group row">
    <label class="col-md-2 col-from-label">Message</label>
    <div class="col-sm-6">
        {{--<span>Download the skeleton file and fill it with proper data. <strong><a href="{{ static_asset('download/customer-group-benefits.json') }}" download>Download Sample</a></strong></span>
        <textarea class="form-control" name="message" placeholder="Write here..." id="" rows="6">{{@$group->message}}</textarea>--}}
        <div x-data="discountForm()" x-init="init({{ json_encode(json_decode(@$group->message)) }})">

            <!-- Title Input -->
            <div class="form-group">
                <input type="text" id="title" class="form-control" x-model="formData.title" placeholder="Message Title">
            </div>

            <!-- Video URL Input -->
            <div class="form-group">
                <input type="text" id="video_url" class="form-control" x-model="formData.video_url" placeholder="Message Video URL">
            </div>

            <!-- Offers CRUD List -->
            <h6 class="mb-1 pb-1">Message Offers</h6>
            <ul class="list-group mb-3">
                <template x-for="(offer, index) in formData.offers" :key="index">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span x-show="!isEditing(index)" x-text="offer"></span>
                            <input type="text" class="form-control" x-show="isEditing(index)" x-model="editedOffer" @keyup.enter="updateOffer(index)">
                        </div>
                        <div>
                            <button type="button" class="p-1 btn btn-sm btn-outline-primary" @click="editOffer(index)" x-show="!isEditing(index)"><i class="las la-edit"></i></button>
                            <button type="button" class="p-1 btn btn-sm btn-outline-success" @click="updateOffer(index)" x-show="isEditing(index)"><i class="las la-save"></i></button>
                            <button type="button" class="p-1 btn btn-sm btn-outline-danger" @click="deleteOffer(index)"><i class="las la-trash"></i></button>
                        </div>
                    </li>
                </template>
            </ul>

            <!-- Add Offer Input -->
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Add new offer" x-model="newOffer" @keyup.enter="addOffer">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" @click="addOffer">Add</button>
                </div>
            </div>

            <!-- Display the form data as JSON -->
            <input type="hidden" name="message" class="bg-light p-3 mt-4" :value="JSON.stringify(formData, null, 2)" />
        </div>
    </div>
  </div>

  <div class="form-group row">
            <label class="col-sm-2 col-from-label" for="discount">{{ ('Priority / Ordering')}} <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <input type="number" placeholder="1 or 2 or 3" lang="en" name="ordering" value="{{@$group->ordering}}" min="0" step="1" class="form-control" requierd>
            </div>
        </div>


    </div>
</div>
<div class="col-12">
                <div class="btn-toolbar float-right mb-3" role="toolbar" aria-label="Toolbar with button groups">
                    <div class="btn-group" role="group" aria-label="Second group">
                        <button type="submit" name="button" value="publish" class="btn btn-success">Update</button>
                    </div>
                </div>
            </div>
            <form>

<script>
function discountForm() {
    return {
        // Initial data from JSON
        formData: null,
        newOffer: '',        // For adding new offers
        editedOffer: '',     // For editing existing offers
        editingIndex: null,  // Track which offer is being edited

        init(data) {
            this.formData = data;
        },

        // Add a new offer to the list
        addOffer() {
            if (this.newOffer.trim() !== '') {
                this.formData.offers.push(this.newOffer);
                this.newOffer = ''; // Clear input
            }
        },

        // Start editing an offer
        editOffer(index) {
            this.editingIndex = index;
            this.editedOffer = this.formData.offers[index];
        },

        // Update the offer with edited value
        updateOffer(index) {
            if (this.editedOffer.trim() !== '') {
                this.formData.offers[index] = this.editedOffer;
            }
            this.editingIndex = null;
            this.editedOffer = '';
        },

        // Delete an offer from the list
        deleteOffer(index) {
            this.formData.offers.splice(index, 1);
        },

        // Check if the current index is being edited
        isEditing(index) {
            return this.editingIndex === index;
        }
    };
}
</script>
@endsection
