<div class="card-header mb-2 px-0">
    <h3 class="h6">{{ ('Add Your Cart Base Coupon')}}</h3>
</div>
<div class="form-group row">
    <label class="col-lg-3 col-from-label" for="coupon_code">{{ ('Coupon code')}} <span class="text-danger">*</span></label>
    <div class="col-lg-9">
        <input type="text" placeholder="{{ ('Coupon code')}}" id="coupon_code" name="coupon_code" class="form-control coupon_code" required>
    </div>
</div>
<div class="form-group row">
   <label class="col-lg-3 col-from-label">{{ ('Minimum Shopping')}} <span class="text-danger">*</span></label>
   <div class="col-lg-9">
      <input type="number" lang="en" min="0" step="0.01" placeholder="{{ ('Minimum Shopping')}}" name="min_buy" class="form-control" required>
   </div>
</div>
<div class="form-group row">
   <label class="col-lg-3 col-from-label">{{ ('Discount')}} <span class="text-danger">*</span></label>
   <div class="col-lg-7">
      <input type="number" lang="en" min="0" step="0.01" placeholder="{{ ('Discount')}}" name="discount" class="form-control" required>
   </div>
   <div class="col-lg-2">
       <select class="form-control aiz-selectpicker" name="discount_type">
           <option value="amount">{{ ('Amount')}}</option>
           <option value="percent">{{ ('Percent')}}</option>
       </select>
   </div>
</div>
<div class="form-group row">
   <label class="col-lg-3 col-from-label">{{ ('Maximum Discount Amount')}} <span class="text-danger">*</span></label>
   <div class="col-lg-9">
      <input type="number" lang="en" min="0" step="0.01" placeholder="{{ ('Maximum Discount Amount')}}" name="max_discount" class="form-control" required>
   </div>
</div>
<div class="form-group row">
    <label class="col-sm-3 control-label" for="start_date">{{ ('Date')}}</label>
    <div class="col-sm-9">
      <input type="text" class="form-control aiz-date-range" name="date_range" placeholder="Select Date">
    </div>
</div>
