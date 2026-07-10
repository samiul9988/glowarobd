@php
    $coupon_det = json_decode($coupon->details);
@endphp

<div class="card-header mb-2 px-0">
   <h3 class="h6">{{ ('Edit Your Cart Base Coupon')}}</h3>
</div>
<div class="form-group row">
   <label class="col-lg-3 col-from-label" for="coupon_code">{{ ('Coupon code')}} <span class="text-danger">*</span></label>
   <div class="col-lg-9">
       <input type="text" value="{{$coupon->code}}" id="coupon_code" name="coupon_code" class="form-control coupon_code" required>
   </div>
</div>


<div class="form-group row">
  <label class="col-lg-3 col-from-label">{{ ('Minimum Shopping')}} <span class="text-danger">*</span></label>
  <div class="col-lg-9">
     <input type="number" lang="en" min="0" step="0.01" name="min_buy" class="form-control" value="{{ $coupon_det->min_buy }}" required>
  </div>
</div>
<div class="form-group row">
   <label class="col-lg-3 col-from-label">{{ ('Discount')}} <span class="text-danger">*</span></label>
   <div class="col-lg-7">
       <input type="number" lang="en" min="0" step="0.01" placeholder="{{ ('Discount')}}" name="discount" class="form-control" value="{{ $coupon->discount }}" required>
   </div>
   <div class="col-lg-2">
       <select class="form-control aiz-selectpicker" name="discount_type">
           <option value="amount" @if ($coupon->discount_type == 'amount') selected  @endif >{{ ('Amount')}}</option>
           <option value="percent" @if ($coupon->discount_type == 'percent') selected  @endif>{{ ('Percent')}}</option>
       </select>
   </div>
</div>
<div class="form-group row">
  <label class="col-lg-3 col-from-label">{{ ('Maximum Discount Amount')}} <span class="text-danger">*</span></label>
  <div class="col-lg-9">
     <input type="number" lang="en" min="0" step="0.01" placeholder="{{ ('Maximum Discount Amount')}}" name="max_discount" class="form-control" value="{{ $coupon_det->max_discount }}" required>
  </div>
</div>

@php
  $start_date = date('m/d/Y', $coupon->start_date);
  $end_date = date('m/d/Y', $coupon->end_date);
@endphp
<div class="form-group row">
    <label class="col-sm-3 control-label" for="start_date">{{ ('Date')}}</label>
    <div class="col-sm-9">
      <input type="text" class="form-control aiz-date-range" value="{{ $start_date .' - '. $end_date }}" name="date_range" placeholder="Select Date">
    </div>
</div>
