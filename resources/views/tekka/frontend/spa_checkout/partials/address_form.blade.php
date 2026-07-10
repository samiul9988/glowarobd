@php
    $countries = Cache::remember('frontend_countries', now()->addDay(), function() {
        return \App\Models\Country::latest()->where('status', 1)->get();
    });
@endphp
<!-- New Address Form -->
<div class="new-address-form">
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-floating-custom">
                <label for="name">Full Name <span class="required">*</span></label>
                <input type="text" class="form-control" id="name"
                    placeholder="Enter your full name" required>
                <small id="name-error" class="form-error"></small>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-floating-custom">
                <label for="phone">Phone Number <span
                        class="required">*</span></label>
                <input type="text" class="form-control" id="phone"
                    placeholder="01xxxxxxxxx" required>
                <small id="phone-error" class="form-error"></small>
            </div>
        </div>
        <div class="col-12">
            <div class="form-floating-custom">
                <label for="address">Street Address <span
                        class="required">*</span></label>
                <textarea class="form-control" id="address" rows="3" placeholder="House no, Street, Area" required></textarea>
                <small id="address-error" class="form-error"></small>
            </div>
        </div>
        <div class="col-12 col-md-6 d-none d-md-block">
            <div class="form-floating-custom">
                <label for="country">Country</label>
                <input type="hidden" class="form-control" id="country" value="{{ $countries->first()?->id ?? '' }}">
                <input type="text" class="form-control" value="{{ $countries->first()?->name ?? 'Bangladesh' }}" readonly disabled>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-floating-custom">
                <label for="state_id">Division <span class="required">*</span></label>
                <select class="form-control form-select aiz-selectpicker"
                    data-live-search="true" name="state_id" id="state_id" required>
                    <option value="" disabled selected>Select Division</option>
                </select>
                <small id="state_id-error" class="form-error"></small>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-floating-custom">
                <label for="city">City <span class="required">*</span></label>
                <select class="form-control form-select aiz-selectpicker"
                    data-live-search="true" name="city" id="city" required>
                    <option value="" disabled selected>Select City</option>
                </select>
                <small id="city-error" class="form-error"></small>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-floating-custom">
                <label for="area">Area <span class="required">*</span></label>
                <select class="form-control form-select aiz-selectpicker"
                    data-live-search="true" name="area" id="area" required>
                    <option value="" disabled selected>Select Area</option>
                </select>
                <small id="area-error" class="form-error"></small>
            </div>
        </div>
    </div>
</div>
