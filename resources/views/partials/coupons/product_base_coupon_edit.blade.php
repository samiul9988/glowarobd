@php
    $products = Cache::remember('filter_products', now()->addDay(), function () {
        return DB::table('products')->where('published', 1)
            ->pluck('name', 'id')
            ->toArray();
    });
    $coupon_details = json_decode($coupon->details, true);
    $productIds = collect($coupon_details)->pluck('product_id')->toArray();
@endphp
<div class="card-header mb-2 px-0">
    <h5 class="mb-0 h6">{{ ('Add Your Product Base Coupon')}}</h5>
</div>
<div class="form-group row">
    <label class="col-lg-3 control-label" for="coupon_code">{{ ('Coupon code')}} <span class="text-danger">*</span></label>
    <div class="col-lg-9">
        <input type="text" placeholder="{{ ('Coupon code')}}" id="coupon_code" name="coupon_code" value="{{ $coupon->code }}" class="form-control coupon_code" required>
    </div>
</div>
<div class="product-choose-list">
    <div class="product-choose">
        <div class="form-group row">
            <label class="col-lg-3 control-label" for="name">{{ ('Product')}} <span class="text-danger">*</span></label>
            <div class="col-lg-9">
                <select name="product_ids[]" class="form-control product_id aiz-selectpicker" data-live-search="true" data-selected-text-format="count" multiple>
                    @foreach ($products as $productId => $productName)
                        <option value="{{ $productId }}" @if (in_array($productId, $productIds)) selected @endif>
                            {{ $productName }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
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

<div class="form-group row">
   <label class="col-lg-3 col-from-label">{{ ('Discount')}} <span class="text-danger">*</span></label>
   <div class="col-lg-7">
       <input type="number" lang="en" min="0" step="0.01" placeholder="{{ ('Discount')}}" value="{{ $coupon->discount }}" name="discount" class="form-control" required>
   </div>
   <div class="col-lg-2">
       <select class="form-control aiz-selectpicker" name="discount_type">
           <option value="amount" @if ($coupon->discount_type == 'amount') selected  @endif>{{ ('Amount')}}</option>
           <option value="percent" @if ($coupon->discount_type == 'percent') selected  @endif>{{ ('Percent')}}</option>
       </select>
   </div>
</div>
