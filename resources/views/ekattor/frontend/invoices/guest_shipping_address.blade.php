<div class="form-group">
    <div class="row">
        <label class="col-sm-2 control-label" for="name">{{translate('Name')}}</label>
        <div class="col-sm-10">
            <input type="text" placeholder="{{translate('Name')}}" id="name" name="name" class="form-control" required>
        </div>
    </div>
</div>
<div class="form-group">
    <div class=" row">
        <label class="col-sm-2 control-label" for="phone">{{translate('Phone')}}</label>
        <div class="col-sm-10">
            <input type="number" min="0" placeholder="{{translate('Phone')}}" id="phone" name="phone" class="form-control" required>
        </div>
    </div>
</div>
<div class="form-group" style="display: none">
    <div class=" row">
        <label class="col-sm-2 control-label" for="email">{{translate('Email')}}</label>
        <div class="col-sm-10">
            <input type="email" placeholder="{{translate('Email')}}" id="email" name="email" class="form-control">
        </div>
    </div>
</div>
<div class="form-group">
    <div class=" row">
        <label class="col-sm-2 control-label" for="address">{{translate('Address')}}</label>
        <div class="col-sm-10">
            <textarea placeholder="{{translate('Address')}}" id="address" name="address" class="form-control" required></textarea>
        </div>
    </div>
</div>
<div class="form-group" @if(\App\Models\Country::where('status', 1)->count()==1) style="display:none" @endif>
    <div class=" row">
        <label class="col-sm-2 control-label">{{translate('Country')}}</label>
        <div class="col-sm-10">
            <select class="form-control aiz-selectpicker" data-live-search="true" data-placeholder="{{ translate('Select your country') }}" name="country_id" required>
                @if(\App\Models\Country::where('status', 1)->count()>1)
                    <option value="">{{ translate('Select your country') }}</option>
                    @foreach (\App\Models\Country::where('status', 1)->get() as $key => $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                @else
                    @foreach (\App\Models\Country::where('status', 1)->get() as $key => $country)
                        <option value="{{ $country->id }}" selected>{{ $country->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="row">
        <div class="col-sm-2 control-label">
            <label>{{ translate('State')}}</label>
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
            <label>{{ translate('City')}}</label>
        </div>
        <div class="col-sm-10">
            <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="city_id" required>

            </select>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="row">
        <div class="col-sm-2">
            <label>{{ translate('Area')}}</label>
        </div>
        <div class="col-sm-10">
            <select class="form-control aiz-selectpicker" data-live-search="true" name="area_id" required>

            </select>
        </div>
    </div>
</div>
<div class="form-group" style="display: none">
    <div class=" row">
        <label class="col-sm-2 control-label" for="postal_code">{{translate('Postal code')}}</label>
        <div class="col-sm-10">
            <input type="number" min="0" placeholder="{{translate('Postal code')}}" id="postal_code" name="postal_code" class="form-control">
        </div>
    </div>
</div>
