@extends('backend.layouts.app')

@section('content')

<div class="col-lg-8 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ ('Create Shipping Discount')}}</h5>
        </div>
        <div class="card-body" x-data="newShipDiscount()" x-cloak>
            <form id="shipDiscountForm" class="form-horizontal" action="{{ route('ship_discounts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="zone_id">{{ ('Shipping Zone')}}</label>
                    <div class="col-lg-9">
                        <select name="zone_id" id="zone_id" class="form-control aiz-selectpicker" required>
                            <option value="">{{ ('Select Zone') }}</option>
                            <option value="0">{{ ('All Zones') }}</option>
                            @foreach($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="type">{{ ('Discount Type')}}</label>
                    <div class="col-lg-9">
                        <select name="type" id="type" x-model="type" class="form-control aiz-selectpicker" onchange="getSelections()" required>
                            <option value="">{{ ('Select One') }}</option>
                            <option value="all">{{ ('For All Products')}}</option>
                            <option value="product">{{ ('For Selected Products')}}</option>
                            <option value="brand">{{ ('For Selected Brand')}}</option>
                            <option value="category">{{ ('For Selected Category')}}</option>
                        </select>
                    </div>
                </div>
                <div x-show="type != '' && type != 'all'" class="form-group row">
                    <label class="col-lg-3 col-from-label text-capitalize" for="type" x-text="'Select ' + type + '(s)'"></label>
                    <div class="col-lg-9">
                        <select id="selections" name="type_ids[]" class="form-control aiz-selectpicker" data-live-search="true" data-selected-text-format="count" multiple :required="type != 'all'">

                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="threshold_amount">{{ ('Minimum Shopping')}}</label>
                    <div class="col-lg-9">
                        <input id="threshold_amount" type="number" min="0" step="0.01" placeholder="Minimum Shopping" name="threshold_amount" class="form-control" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="s_charge">{{ ('Shipping Charge')}}</label>
                    <div class="col-lg-9">
                        <input id="s_charge" type="number" min="0" step="1" placeholder="Shipping charge in amount" name="s_charge" class="form-control" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="date_range">{{ ('Date')}}</label>
                    <div class="col-lg-9">
                        <input id="date_range" type="text" class="form-control aiz-date-range" name="date_range" placeholder="Select Date">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="name">{{ ('Status')}}</label>
                    <div class="col-lg-9">
                        <select name="status" id="type" class="form-control aiz-selectpicker" required>
                            <option value="1" selected>{{ ('Active')}}</option>
                            <option value="0">{{ ('Inactive')}}</option>
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

    $(document).ready(function(){
        $('.aiz-selectpicker').selectpicker();
        $('.aiz-date-range').daterangepicker();

        @if (count($errors) > 0)
            @foreach ($errors->all() as $error)
                AIZ.plugins.notify('danger', "{{ $error }}");
            @endforeach
        @endif
    });
    function newShipDiscount() {
        return {
            type: ''
        }
    }
    function getSelections(){
        var type = $('#type').val();
        if(type != '' && type != 'all'){
            $.post("{{ route('ship_discount.get_selections') }}",{_token:'{{ csrf_token() }}', type: type}, function(data){
                $('#selections').html(data);
                AIZ.plugins.bootstrapSelect('refresh');
            });
        }
    }
</script>

@endsection
