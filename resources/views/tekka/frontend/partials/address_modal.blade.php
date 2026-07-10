<div class="modal fade" id="new-address-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ ('New Address') }}</h5>
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
                                <label>{{ ('Address Type')}}</label>
                            </div>
                            <div class="col-md-9">
                                <div class="btn-group" role="group" aria-label="Basic example">
                                    <button data-address_type="Home" type="button" class="btn btn-secondary active clickAddressType"><i class="las la-home"></i> Home</button>
                                    <button data-address_type="Office" type="button" class="btn btn-secondary clickAddressType"><i class="las la-briefcase"></i> Office</button>
                                    <button data-address_type="Other" type="button" class="btn btn-secondary clickAddressType"><i class="las la-map-marker"></i> Other</button>
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
                        <div class="row align-items-center mb-3 d-none">
                            <div class="col-md-3">
                                <label>{{ ('Address Type')}}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker address_type_dropdown" data-live-search="true" name="address_type" required>
                                    <option selected value="Home">{{ ('Home') }}</option>
                                    <option value="Office">{{ ('Office') }}</option>
                                    <option value="Other">{{ ('Other') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row align-items-center mb-3" @if(\App\Models\Country::where('status', 1)->count()==1) style="display:none" @endif>
                            <div class="col-md-3">
                                <label>{{ ('Country')}}</label>
                            </div>
                            <div class="col-md-9">
                                <div class="">
                                    <select class="form-control aiz-selectpicker" data-live-search="true" data-placeholder="{{ ('Select your country') }}" name="country_id" required>
                                        @if(\App\Models\Country::where('status', 1)->count()>1)
                                            <option value="">{{ ('Select your country') }}</option>
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
                                <label>{{ ('State')}}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="state_id" required>

                                </select>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-md-3">
                                <label>{{ ('City')}}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="city_id" required>

                                </select>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-md-3">
                                <label>{{ ('Area')}}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" data-live-search="true" name="area_id" required>

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
                                <input type="number" class="form-control" placeholder="{{ ('01xxxxxxxxx')}}" name="phone" value="" pattern="[0-9+]{11,}" min="11" title="Phone number should be minimum 11 characters and only contain +88 and numbers between [1-9]. e.g. 01714117604" required>
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
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

@section('script')
    <script type="text/javascript">
    $(document).on('click', '.clickAddressType', function(){
        $('.clickAddressType').removeClass('active');
        $(this).addClass('active');
        var address_type = $(this).attr('data-address_type');
        $('.address_type_dropdown').val(address_type);
        AIZ.plugins.bootstrapSelect('refresh');
    });
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
        $(document).on('click', '.clickAddressTypeEdit', function(){
            $('.clickAddressTypeEdit').removeClass('active');
            $(this).addClass('active');
            var address_type = $(this).attr('data-address_type');
            $('.address_type_dropdown_edit').val(address_type);
            AIZ.plugins.bootstrapSelect('refresh');
        });

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
