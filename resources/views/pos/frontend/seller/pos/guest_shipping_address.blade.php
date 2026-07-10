<div class="form-group">
    <div class="row">
        <label class="col-sm-2 control-label" for="name">{{ ('Name')}}</label>
        <div class="col-sm-10">
            <input type="text" placeholder="{{ ('Name')}}" id="name" name="name" class="form-control" required>
        </div>
    </div>
</div>
<div class="form-group">
    <div class=" row">
        <label class="col-sm-2 control-label" for="email">{{ ('Email')}}</label>
        <div class="col-sm-10">
            <input type="email" placeholder="{{ ('Email')}}" id="email" name="email" class="form-control" required>
        </div>
    </div>
</div>
<div class="form-group">
    <div class=" row">
        <label class="col-sm-2 control-label" for="address">{{ ('Address')}}</label>
        <div class="col-sm-10">
            <textarea placeholder="{{ ('Address')}}" id="address" name="address" class="form-control" required></textarea>
        </div>
    </div>
</div>
<div class="form-group" @if(\App\Models\Country::where('status', 1)->count() == 1) style="display:none" @endif>
    <div class=" row">
        <label class="col-sm-2 control-label">{{ ('Country')}}</label>
        <div class="col-sm-10">
            <select class="form-control aiz-selectpicker" data-live-search="true" data-placeholder="{{ ('Select your country') }}" name="country_id" required>
                <option value="">{{ ('Select your country') }}</option>
                @foreach (\App\Models\Country::where('status', 1)->get() as $key => $country)
                    <option value="{{ $country->id }}" @if(\App\Models\Country::where('status', 1)->count() == 1) selected @endif>{{ $country->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="row">
        <div class="col-sm-2 control-label"">
            <label>{{ ('State')}}</label>
        </div>
        <div class="col-sm-10">
            <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="state_id" required>

            </select>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="row">
        <div class="col-sm-2">
            <label>{{ ('City')}}</label>
        </div>
        <div class="col-sm-10">
            <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="city_id" required>

            </select>
        </div>
    </div>
</div>
<div class="form-group">
    <div class=" row">
        <label class="col-sm-2 control-label" for="postal_code">{{ ('Postal code')}}</label>
        <div class="col-sm-10">
            <input type="number" min="0" placeholder="{{ ('Postal code')}}" id="postal_code" name="postal_code" class="form-control" required>
        </div>
    </div>
</div>
<div class="form-group">
    <div class=" row">
        <label class="col-sm-2 control-label" for="phone">{{ ('Phone')}}</label>
        <div class="col-sm-10">
            <input type="number" min="0" placeholder="{{ ('Phone')}}" id="phone" name="phone" class="form-control" required>
        </div>
    </div>
</div>