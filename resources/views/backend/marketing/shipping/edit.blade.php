@extends('backend.layouts.app')

@section('content')

@php
  $start_date = date('m/d/Y', $discount->start_date);
  $end_date = date('m/d/Y', $discount->end_date);
@endphp
<div class="col-lg-8 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ ('Update Shipping Discount')}}</h5>
        </div>
        <div class="card-body" x-data="newShipDiscount()" x-cloak>
            <form id="shipDiscountForm" class="form-horizontal" action="{{ route('ship_discounts.update', $discount->id) }}" method="POST" enctype="multipart/form-data">
                <input name="_method" type="hidden" value="PATCH">
                @csrf
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="zone_id">{{ ('Shipping Zone')}}</label>
                    <div class="col-lg-9">
                        <select name="zone_id" id="zone_id" class="form-control" required>
                            <option value="0" @if($discount->zone_id == '0') selected @endif>{{ ('All Zones') }}</option>
                            @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" @if($discount->zone_id == $zone->id) selected @endif>{{ $zone->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="type">{{ ('Discount Type')}}</label>
                    <div class="col-lg-9">
                        <select name="type" id="type" x-model="type" class="form-control" onchange="getSelections()" required>
                            <option value="">{{ ('Select One') }}</option>
                            <option value="all">{{ ('For All Products')}}</option>
                            <option value="product">{{ ('For Selected Products')}}</option>
                            <option value="brand">{{ ('For Selected Brand')}}</option>
                            <option value="category">{{ ('For Selected Category')}}</option>
                        </select>
                    </div>
                </div>
                <div x-show="type != '' && type != 'all'" class="form-group row">
                    <label class="col-lg-3 col-from-label text-capitalize" for="selections" x-text="'Select ' + type + '(s)'"></label>
                    <div class="col-lg-9">
                        <select id="selections" name="type_ids[]" class="form-control aiz-selectpicker" data-live-search="true" :required="type != 'all'" data-selected="{{$discount->details}}" multiple>

                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="threshold_amount">{{ ('Minimum Shopping')}}</label>
                    <div class="col-lg-9">
                        <input id="threshold_amount" type="number" min="0" step="0.01" placeholder="Minimum Shopping" name="threshold_amount" class="form-control" value="{{$discount->threshold_amount}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="s_charge">{{ ('Shipping Charge')}}</label>
                    <div class="col-lg-9">
                        <input id="s_charge" type="number" min="0" step="1" placeholder="Shipping charge in amount" name="s_charge" class="form-control" value="{{$discount->s_charge}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="date_range">{{ ('Date')}}</label>
                    <div class="col-lg-9">
                        <input id="date_range" type="text" class="form-control aiz-date-range" name="date_range" value="{{ $start_date .' - '. $end_date }}" placeholder="Select Date">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="status">{{ ('Status')}}</label>
                    <div class="col-lg-9">
                        <select name="status" id="status" class="form-control" required>
                            <option value="1" @if($discount->status == 1) selected @endif>{{ ('Active')}}</option>
                            <option value="0" @if($discount->status == 0) selected @endif>{{ ('Inactive')}}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary">{{ ('Save')}}</button>
                </div>
            </from>
        </div>
    </div>
</div>

@endsection
@section('script')

<script type="text/javascript">

    $(document).ready(async function(){
        await getSelections();
        // $('.aiz-selectpicker').selectpicker();
        $('.aiz-date-range').daterangepicker();
        var discountDetails = {!! json_encode($discount->details) !!};
        $('.aiz-selectpicker').selectpicker('val', JSON.parse(discountDetails));

        @if (count($errors) > 0)
            @foreach ($errors->all() as $error)
                AIZ.plugins.notify('danger', "{{ $error }}");
            @endforeach
        @endif
    });
    function newShipDiscount() {
        return {
            type: '{{ $discount->type }}'
        }
    }
    async function getSelections(){
        var type = $('#type').val();
        if(type != '' && type != 'all'){
            await $.post("{{ route('ship_discount.get_selections') }}",{_token:'{{ csrf_token() }}', type: type}, function(data){
                $('#selections').html(data);
                AIZ.plugins.bootstrapSelect('refresh');
            });
        }
    }
</script>

@endsection
