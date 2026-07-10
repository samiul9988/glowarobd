@php
    $address_type = $address_data->address_type;
@endphp
<form class="form-default" role="form" action="{{ route('addresses.update', $address_data->id) }}" method="POST" x-data="app()" x-cloak>
    @csrf
    <div class="p-3">
        <div class="row align-items-center mb-3">
            <div class="col-md-3">
                <label>{{ translate('Address Type')}}</label>
            </div>
            <div class="col-md-9">
                <div class="btn-group" role="group" aria-label="Basic example">
                    <label :class="address_type == 'Home' ? 'active' : ''" class="btn btn-secondary @if($address_type == 'Home') active @endif">
                        <input name="address_type" x-model="address_type" value="Home" type="radio" style="display: none;"/>
                        <i class="las la-home"></i> Home
                    </label>
                    <label :class="address_type == 'Office' ? 'active' : ''" class="btn btn-secondary @if($address_type == 'Office') active @endif">
                        <input name="address_type" x-model="address_type" value="Office" type="radio"  style="display: none;" />
                        <i class="las la-briefcase"></i> Office
                    </label>
                    <label :class="address_type == 'Other' ? 'active' : ''" class="btn btn-secondary @if($address_type == 'Other') active @endif">
                        <input name="address_type" x-model="address_type" value="Other" type="radio" style="display: none;" />
                        <i class="las la-map-marker"></i> Other
                    </label>
                </div>
            </div>
        </div>
        <div class="row align-items-center mb-3">
            <div class="col-md-3">
                <label>{{ translate('Address')}}</label>
            </div>
            <div class="col-md-9">
                <textarea class="form-control" placeholder="{{ translate('Please type house number, road number , area name, nearby places etc...')}}" rows="2" name="address" minlength="10" required>{{ $address_data->address }}</textarea>
            </div>
        </div>
        {{--<div class="row align-items-center mb-3 d-none">
            <div class="col-md-3">
                <label>{{ translate('Address Type')}}</label>
            </div>
            <div class="col-md-9">
                <select class="form-control aiz-selectpicker address_type_dropdown_edit" data-live-search="true" name="address_type" x-model="address_type" required>
                    <option value="">{{ translate('Select Type') }}</option>
                    <option @if($address_data->address_type == 'Home')
                        selected
                    @endif value="Home">{{ translate('Home') }}</option>
                    <option @if($address_data->address_type == 'Office')
                        selected
                    @endif value="Office">{{ translate('Office') }}</option>
                    <option @if($address_data->address_type == 'Other')
                        selected
                    @endif value="Other">{{ translate('Other') }}</option>
                </select>
            </div>
        </div>--}}
        <div class="row align-items-center mb-3" @if(\App\Models\Country::where('status', 1)->count()==1) style="display:none" @endif>
            <div class="col-md-3">
                <label>{{ translate('Country')}}</label>
            </div>
            <div class="col-md-9">
                <div class="">
                    <select class="form-control aiz-selectpicker" data-live-search="true" data-placeholder="{{ translate('Select your country')}}" name="country_id" id="edit_country" required>
                        <option value="">{{ translate('Select your country') }}</option>
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
                <label>{{ translate('State')}}</label>
            </div>
            <div class="col-md-9">
                <select class="form-control aiz-selectpicker" name="state_id" id="edit_state"  data-live-search="true" @change="handleStateChange($event.target)" required>
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
                <label>{{ translate('City')}}</label>
            </div>
            <div class="col-md-9">
                <select class="form-control aiz-selectpicker" data-live-search="true" name="city_id" id="edit_city" @change="handleCityChange($event.target)" required>
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
                <label>{{ translate('Area')}}</label>
            </div>
            <div class="col-md-9">
                <select class="form-control aiz-selectpicker" data-live-search="true" id="edit_area" name="area_id" required>
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
                <label>{{ translate('Postal code')}}</label>
            </div>
            <div class="col-md-9">
                <input type="text" class="form-control" placeholder="{{ translate('Your Postal Code')}}" value="{{ $address_data->postal_code }}" name="postal_code" value="">
            </div>
        </div>
        <div class="row align-items-center mb-3">
            <div class="col-md-3">
                <label>{{ translate('Phone')}}</label>
            </div>
            <div class="col-md-9">
                <input type="number" class="form-control" placeholder="{{ translate('01xxxxxxxxx')}}" value="{{ $address_data->phone }}" name="phone" value="" pattern="[0-9+]{11,}" min="11" title="Phone number should be minimum 11 characters and only contain +88 and numbers between [1-9]. e.g. 01714117604" required>
            </div>
        </div>
        <div class="form-group text-center">
            <button type="submit" class="btn btn-sm btn-primary">{{translate('Update')}}</button>
        </div>
    </div>
</form>
<script type="text/javascript">
AIZ.plugins.bootstrapSelect('refresh');
</script>
