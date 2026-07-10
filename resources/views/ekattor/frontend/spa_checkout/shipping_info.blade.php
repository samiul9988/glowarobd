@if(Auth::check())
    <div class="">
        <div class="row gutters-5">
            @php $i=0; @endphp
            @foreach (Auth::user()->addresses as $key => $address)
            @php $i++; @endphp
            <div class="col-md-6 mb-3" x-show="showMore || @if($loop->first) true @else false @endif">
                <label class="aiz-megabox d-block bg-white mb-0">
                    <input type="radio" name="address_id" x-model="address_id" @change="saveAddress($event.target)" value="{{ $address->id }}" @if ($address->set_default)
                        checked
                    @else
                        @if($i==1) checked @endif
                    @endif required>
                    <span class="d-flex p-3 aiz-megabox-elem position-relative">
                        <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                        <span class="flex-grow-1 pl-3 text-left">
                            <div>
                                <span class="opacity-60">{{ translate('Address') }}:</span>
                                <span class="fw-600 ml-2">{{ $address->address }}</span>
                            </div>
                            {{-- <div>
                                <span class="opacity-60">{{ translate('Postal Code') }}:</span>
                                <span class="fw-600 ml-2">{{ $address->postal_code }}</span>
                            </div> --}}
                            <div>
                                <span class="opacity-60">{{ translate('Area') }}:</span>
                                <span class="fw-600 ml-2">{{ optional($address->area)->name }}</span>
                            </div>
                            <div>
                                <span class="opacity-60">{{ translate('City') }}:</span>
                                <span class="fw-600 ml-2">{{ optional($address->city)->name }}</span>
                            </div>
                            <div>
                                <span class="opacity-60">{{ translate('State') }}:</span>
                                <span class="fw-600 ml-2">{{ optional($address->state)->name }}</span>
                            </div>
                            <div>
                                <span class="opacity-60">{{ translate('Country') }}:</span>
                                <span class="fw-600 ml-2">{{ optional($address->country)->name }}</span>
                            </div>
                            <div>
                                <span class="opacity-60">{{ translate('Phone') }}:</span>
                                <span class="fw-600 ml-2">{{ $address->phone }}</span>
                            </div>
                        </span>
                        <span title="{{@$address->address_type}}" class="position-absolute text-secondary" style="bottom: 10px; right: 10px; font-size: 25px;">
                            @if($address->address_type == 'Home')
                            <i class="las la-home"></i>
                            @elseif($address->address_type == 'Office')
                            <i class="las la-briefcase"></i>
                            @else
                            <i class="las la-map-marker"></i>
                            @endif
                        </span>
                    </span>
                </label>
                <div class="dropdown position-absolute right-0 top-0">
                    <button class="btn bg-gray px-2" type="button" data-toggle="dropdown">
                        <i class="la la-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" @click="edit_address('{{$address->id}}')">
                            {{ translate('Edit') }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
            <input type="hidden" name="checkout_type" value="logged">
            <div x-show="showMore" class="col-md-6 mx-auto mb-3" >
                <div class="border p-3 rounded mb-3 c-pointer text-center bg-white h-100 d-flex flex-column justify-content-center" @click="add_new_address()">
                    <i class="las la-plus la-2x mb-3"></i>
                    <div class="alpha-7">{{ translate('Add New Address') }}</div>
                </div>
            </div>
            <div x-show="!showMore" class="col-md-6 mx-auto mb-3" >
                <div class="border p-3 rounded mb-3 c-pointer text-center bg-white h-100 d-flex flex-column justify-content-center" @click="showMore = !showMore">
                    <i class="las la-edit la-2x mb-3"></i>
                    <div class="alpha-7">{{ translate('Change Shipping Address') }}</div>
                </div>
            </div>
        </div>
    </div>

    @if (get_setting('google_map') == 1)
        @include('frontend.partials.google_map')
    @endif
@endif