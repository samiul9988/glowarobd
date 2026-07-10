<div class="modal fade" id="new-address-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ translate('New Address') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-default" role="form" action="{{ route('addresses.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:0">
                    <div class="p-3">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-3">
                                <label>{{ translate('Address')}}</label>
                            </div>
                            <div class="col-md-9">
                                <textarea class="form-control" placeholder="{{ translate('Your Address')}}" rows="2" name="address" minlength="15" required></textarea>
                            </div>
                        </div>
                        <div class="row align-items-center mb-3" @if(\App\Models\Country::where('status', 1)->count()==1) style="display:none" @endif>
                            <div class="col-md-3">
                                <label>{{ translate('Country')}}</label>
                            </div>
                            <div class="col-md-9">
                                <div class="">
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
                        
                        <div class="row align-items-center mb-3">
                            <div class="col-md-3">
                                <label>{{ translate('State')}}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="state_id" required>

                                </select>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-md-3">
                                <label>{{ translate('City')}}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="city_id" required>

                                </select>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-md-3">
                                <label>{{ translate('Area')}}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="area_id" required>

                                </select>
                            </div>
                        </div>

                        @if (get_setting('google_map') == 1)
                            <div class="row">
                                <input id="searchInput" class="controls" type="text" placeholder="{{translate('Enter a location')}}">
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
                                <label>{{ translate('Postal code')}}</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" class="form-control" placeholder="{{ translate('Your Postal Code')}}" name="postal_code" value="">
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-md-3">
                                <label>{{ translate('Phone')}}</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" class="form-control" placeholder="{{ translate('+880')}}" name="phone" value="" pattern="[0-9+]{11,}" title="Phone number should be minimum 11 characters and only contain +88 and numbers between [1-9]. e.g. +8809666767604" required>
                            </div>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
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
                <h5 class="modal-title" id="exampleModalLabel">{{ translate('Update Address') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body" id="edit_modal_body" style="padding:0">

            </div>
        </div>
    </div>
</div>

@section('script')
    <script type="text/javascript">
        function add_new_address(){
            $('#new-address-modal').modal('show');
            
            if($('[name=country_id]').find('option').length==1){
                var country_id = $('[name=country_id]').val();
                if(country_id!='')
                    get_states(country_id);
            }
        }

        function edit_address(address) {
            var url = '{{ route("addresses.edit", ":id") }}';
            url = url.replace(':id', address);
            
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function (response) {
                    $('#edit_modal_body').html(response.html);
                    $('#edit-address-modal').modal('show');
                    AIZ.plugins.bootstrapSelect('refresh');

                    @if (get_setting('google_map') == 1)
                        var lat     = -33.8688;
                        var long    = 151.2195;

                        if(response.data.address_data.latitude && response.data.address_data.longitude) {
                            lat     = response.data.address_data.latitude;
                            long    = response.data.address_data.longitude;
                        }

                        initialize(lat, long, 'edit_');
                    @endif
                }
            });
        }
        
        $(document).on('change', '[name=country_id]', function() {
            var country_id = $(this).val();
            get_states(country_id);
        });

        $(document).on('change', '[name=state_id]', function() {
            var state_id = $(this).val();
            get_city(state_id);
        });

        $(document).on('change', '[name=city_id]', function() {
            var city_id = $(this).val();
            get_area(city_id);
        });
        
        function get_states(country_id) {
            $('[name="state"]').html("");
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('get-state')}}",
                type: 'POST',
                data: {
                    country_id  : country_id
                },
                success: function (response) {
                    var obj = JSON.parse(response);
                    if(obj != '') {
                        $('[name="state_id"]').html(obj);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }

        function get_city(state_id) {
            $('[name="city"]').html("");
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('get-city')}}",
                type: 'POST',
                data: {
                    state_id: state_id
                },
                success: function (response) {
                    var obj = JSON.parse(response);
                    if(obj != '') {
                        $('[name="city_id"]').html(obj);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }

        function get_area(city_id) {
            $('[name="area"]').html("");
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('get-area')}}",
                type: 'POST',
                data: {
                    city_id: city_id
                },
                success: function (response) {
                    var obj = JSON.parse(response);
                    if(obj != '') {
                        $('[name="area_id"]').html(obj);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }
    </script>

    
    @if (get_setting('google_map') == 1)
        @include('frontend.partials.google_map')
    @endif
@endsection
