<div class="modal fade" id="new-address-modal" tabindex="-1" role="dialog" aria-labelledby="newAddressModal" aria-hidden="true" x-data="app()" x-cloak>
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mm" id="newAddressModal">{{ ('Add New Address') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-default m" role="form" action="{{ route('addresses.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:0">
                    <div class="p-3">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-3">
                                <label>{{ ('Address Type')}}</label>
                            </div>
                            <div class="col-md-9">
                                <div class="btn-group" role="group" aria-label="Basic example">
                                    <label :class="address_type == 'Home' ? 'active' : 'inactive'" class="btn btn-secondary" id="address_type_home">
                                        <input name="address_type" x-model="address_type" value="Home" type="radio" style="opacity: 0; width: 0;" required />
                                        <i class="las la-home"></i> Home
                                    </label>
                                    <label :class="address_type == 'Office' ? 'active' : 'inactive'" class="btn btn-secondary" id="address_type_office">
                                        <input name="address_type" x-model="address_type" value="Office" type="radio"  style="opacity: 0; width: 0;" required />
                                        <i class="las la-briefcase"></i> Office
                                    </label>
                                    <label :class="address_type == 'Other' ? 'active' : 'inactive'" class="btn btn-secondary" id="address_type_other">
                                        <input name="address_type" x-model="address_type" value="Other" type="radio" style="opacity: 0; width: 0;" required />
                                        <i class="las la-map-marker"></i> Other
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-md-3">
                                <label>{{ ('Address')}}</label>
                            </div>
                            <div class="col-md-9">
                                <textarea class="form-control" placeholder="{{ ('Write house number, road number and area name')}}" rows="2" name="address" minlength="10" required></textarea>
                            </div>
                        </div>
                        {{--<div class="row align-items-center mb-3 d-none">
                            <div class="col-md-3">
                                <label>{{ ('Address Type')}}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker address_type_dropdown" data-live-search="true" name="address_type" x-model="address_type" required>
                                    <option selected value="Home">{{ ('Home') }}</option>
                                    <option value="Office">{{ ('Office') }}</option>
                                    <option value="Other">{{ ('Other') }}</option>
                                </select>
                            </div>
                        </div>--}}
                        <div class="row align-items-center mb-3" @if($countries->where('status', 1)->count()==1) style="display:none" @endif>
                            <div class="col-md-3">
                                <label>{{ ('Country')}}</label>
                            </div>
                            <div class="col-md-9">
                                <div class="">
                                    <select class="form-control aiz-selectpicker" data-live-search="true" data-placeholder="{{ ('Select your country') }}" name="country_id" x-model="country_id" @change="handleCountryChange($event.target)" required>
                                        @if($countries->where('status', 1)->count()>1)
                                            <option value="">{{ ('Select your country') }}</option>
                                            @foreach ($countries->where('status', 1)->all() as $key => $country)
                                                @php $country = (object) $country @endphp
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        @else
                                            @foreach ($countries->where('status', 1)->all() as $key => $country)
                                                @php $country = (object) $country @endphp
                                                <option value="{{ $country->id }}" selected>{{ $country->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-md-3">
                                <label>{{ ('State')}}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="state_id" x-model="state_id" @change="handleStateChange($event.target)" id="new_state" required>

                                </select>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-md-3">
                                <label>{{ ('City')}}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="city_id" x-model="city_id" @change="handleCityChange($event.target)" id="new_city" required>

                                </select>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-md-3">
                                <label>{{ ('Area')}}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="area_id" id="new_area" required>

                                </select>
                            </div>
                        </div>

                        @if (get_setting('google_map') == 1)
                            <div class="row">
                                <input id="searchInput" class="controls" type="text" placeholder="{{ ('Enter a location')}}">
                                <div id="map"></div>
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
                                    <input type="text" class="form-control" id="longitude" name="longitude" readonly="">
                                </div>
                            </div>
                            <div class="row align-items-center mb-3">
                                <div class="col-md-3" id="">
                                    <label for="exampleInputuname">Latitude</label>
                                </div>
                                <div class="col-md-9" id="">
                                    <input type="text" class="form-control" id="latitude" name="latitude" readonly="">
                                </div>
                            </div>
                        @endif

                        <div class="row align-items-center mb-3" style="display: none">
                            <div class="col-md-3">
                                <label>{{ ('Postal code')}}</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" class="form-control" placeholder="{{ ('Your Postal Code')}}" name="postal_code" value="">
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-md-3">
                                <label>{{ ('Phone')}}</label>
                            </div>
                            <div class="col-md-9">
                                <input type="number" class="form-control" placeholder="{{ ('01xxxxxxxxx')}}" name="phone" x-model="newMobileNumber" title="Phone number should be minimum 11 characters and only contain numbers between [0-9]. e.g. 01714117604" required>
                                <span x-show="newMobileNumber.length < 11 || newMobileNumber.length > 11" class="text-danger fw-600">Phone number should be minimum 11 characters and only contain numbers between [0-9]. e.g. 01714117604</span>
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <button x-bind:type="(newMobileNumber.length < 11 || newMobileNumber.length > 11) ? 'button' : 'submit'"  x-bind:disabled="(newMobileNumber.length < 11 || newMobileNumber.length > 11)" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="edit-address-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ ('Update Address') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="edit_modal_body" style="padding:0">

            </div>
        </div>
    </div>
</div>
