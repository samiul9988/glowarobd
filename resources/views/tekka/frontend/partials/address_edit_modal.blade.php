<form class="form-default" role="form" action="{{ route('addresses.update', $address_data->id) }}" method="POST">
    @csrf
    <div class="p-3">
        <div class="row align-items-center mb-3">
            <div class="col-md-3">
                <label>{{ ('Address Type')}}</label>
            </div>
            <div class="col-md-9">
                <div class="btn-group" role="group" aria-label="Basic example">
                    <button data-address_type="Home" type="button" class="btn btn-secondary @if($address_data->address_type == 'Home') active @endif clickAddressTypeEdit"><i class="las la-home"></i> Home</button>
                    <button data-address_type="Office" type="button" class="btn btn-secondary @if($address_data->address_type == 'Office') active @endif clickAddressTypeEdit"><i class="las la-briefcase"></i> Office</button>
                    <button data-address_type="Other" type="button" class="btn btn-secondary @if($address_data->address_type == 'Other') active @endif clickAddressTypeEdit"><i class="las la-map-marker"></i> Other</button>
                  </div>
            </div>
        </div>
        <div class="row align-items-center mb-3">
            <div class="col-md-3">
                <label>{{ ('Address')}}</label>
            </div>
            <div class="col-md-9">
                <textarea class="form-control" placeholder="{{ ('House # 5, Road # 7, Gulshan Model Town, Dhaka-1212')}}" rows="2" name="address" minlength="10" required>{{ $address_data->address }}</textarea>
            </div>
        </div>
        <div class="row align-items-center mb-3 d-none">
            <div class="col-md-3">
                <label>{{ ('Address Type')}}</label>
            </div>
            <div class="col-md-9">
                <select class="form-control aiz-selectpicker address_type_dropdown_edit" data-live-search="true" name="address_type" required>
                    <option value="">{{ ('Select Type') }}</option>
                    <option @if($address_data->address_type == 'Home')
                        selected
                    @endif value="Home">{{ ('Home') }}</option>
                    <option @if($address_data->address_type == 'Office')
                        selected
                    @endif value="Office">{{ ('Office') }}</option>
                    <option @if($address_data->address_type == 'Other')
                        selected
                    @endif value="Other">{{ ('Other') }}</option>
                </select>
            </div>
        </div>
        <div class="row align-items-center mb-3" @if(\App\Models\Country::where('status', 1)->count()==1) style="display:none" @endif>
            <div class="col-md-3">
                <label>{{ ('Country')}}</label>
            </div>
            <div class="col-md-9">
                <div class="">
                    <select class="form-control aiz-selectpicker" data-live-search="true" data-placeholder="{{ ('Select your country')}}" name="country_id" id="edit_country" required>
                        <option value="">{{ ('Select your country') }}</option>
                        @foreach (\App\Models\Country::where('status', 1)->get() as $key => $country)
                        <option value="{{ $country->id }}" @if($address_data->country_id == $country->id) selected @endif>
                            {{ $country->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row align-items-center mb-3">
            <div class="col-md-3">
                <label>{{ ('State')}}</label>
            </div>
            <div class="col-md-9">
                <select class="form-control aiz-selectpicker" name="state_id" id="edit_state"  data-live-search="true" required>
                    @foreach ($states as $key => $state)
                        <option value="{{ $state->id }}" @if($address_data->state_id == $state->id) selected @endif>
                            {{ $state->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row align-items-center mb-3">
            <div class="col-md-3">
                <label>{{ ('City')}}</label>
            </div>
            <div class="col-md-9">
                <select class="form-control aiz-selectpicker" data-live-search="true" name="city_id" required>
                    @foreach ($cities as $key => $city)
                        <option value="{{ $city->id }}" @if($address_data->city_id == $city->id) selected @endif>
                            {{ $city->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row align-items-center mb-3">
            <div class="col-md-3">
                <label>{{ ('Area')}}</label>
            </div>
            <div class="col-md-9">
                <select class="form-control aiz-selectpicker" data-live-search="true" name="area_id" required>
                    @foreach ($areas as $key => $area)
                        <option value="{{ $area->id }}" @if($address_data->area_id == $area->id) selected @endif>
                            {{ $area->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @if (get_setting('google_map') == 1)
            <div class="row">
                <input id="edit_searchInput" class="controls" type="text" placeholder="Enter a location">
                <div id="edit_map"></div>
                <ul id="geoData">
                    <li style="display: none;">Full Address: <span id="location"></span></li>
                    <li style="display: none;">Postal Code: <span id="postal_code"></span></li>
                    <li style="display: none;">Country: <span id="country"></span></li>
                    <li style="display: none;">Latitude: <span id="lat"></span></li>
                    <li style="display: none;">Longitude: <span id="lon"></span></li>
                </ul>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-md-3" id="">
                    <label for="exampleInputuname">Longitude</label>
                </div>
                <div class="col-md-9" id="">
                    <input type="text" class="form-control" id="edit_longitude" name="longitude" value="{{ $address_data->longitude }}" readonly="">
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-md-3" id="">
                    <label for="exampleInputuname">Latitude</label>
                </div>
                <div class="col-md-9" id="">
                    <input type="text" class="form-control" id="edit_latitude" name="latitude" value="{{ $address_data->latitude }}" readonly="">
                </div>
            </div>
        @endif

        <div class="row align-items-center mb-3" style="display: none">
            <div class="col-md-3">
                <label>{{ ('Postal code')}}</label>
            </div>
            <div class="col-md-9">
                <input type="text" class="form-control" placeholder="{{ ('Your Postal Code')}}" value="{{ $address_data->postal_code }}" name="postal_code" value="">
            </div>
        </div>
        <div class="row align-items-center mb-3">
            <div class="col-md-3">
                <label>{{ ('Phone')}}</label>
            </div>
            <div class="col-md-9">
                <input type="number" class="form-control" placeholder="{{ ('01xxxxxxxxx')}}" value="{{ $address_data->phone }}" name="phone" value="" pattern="[0-9+]{11,}" min="11" title="Phone number should be minimum 11 characters and only contain +88 and numbers between [1-9]. e.g. 01714117604" required>
            </div>
        </div>
        <div class="form-group text-center">
            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
        </div>
    </div>
</form>
