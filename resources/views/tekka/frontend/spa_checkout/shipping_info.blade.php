@if (Auth::check() || get_setting('guest_order_activation') == 1)
    <div class="">
        <div class="row gutters-5 mb-3 mx-0">
            <input type="hidden" name="checkout_type" value="logged">
            <div class="add-address-wrapper">
                <div class="fs-16 fw-400">Please add your address before order</div>
                <div class="add-address btn c-pointer text-center" @click="add_new_address()">
                    <div class="alpha-7 ">{{ 'Add Address' }}</div>
                </div>
            </div>
        </div>
        @php
            $cartAddress = $user_addresses->where('id', $carts->first()->address_id)->first();
        @endphp
        <div class="row">
            @if ($cartAddress)
                <div class="col-12 mb-2" x-show="showMore || true">
                    <label class="aiz-megabox d-block bg-white mb-0">
                        <input type="radio" name="address_id" x-model="address_id"
                            @change="saveAddress($event.target)" value="{{ $cartAddress->id }}" checked required>
                        <span class="d-flex p-3 aiz-megabox-elem position-relative align-items-center">
                            <span class="flex-grow-1  text-left">
                                <div>
                                    <span class=" d-block fs-18 fw-600">
                                        {{ optional($cartAddress->user)->name }}
                                    </span>
                                    <span class="px-4 py-1 btn my-2 bg-light text-dark border-none rounded-sm"
                                        title="{{ @$cartAddress->address_type }}">{{ @$cartAddress->address_type }}
                                    </span>
                                    <br />
                                    <span class="d-block fs-16 fw-400 mb-1 ">{{ @$cartAddress->phone }}</span>
                                    <span class="fs-16 fw-400 ">
                                        {{ $cartAddress->address }},
                                        {{ optional($cartAddress->area)->name }},
                                        {{ optional($cartAddress->city)->name }},
                                        {{ optional($cartAddress->state)->name }}
                                    </span>
                                </div>
                            </span>
                        </span>
                    </label>
                    <div class="position-absolute" style="top: 10px; right: 25px;">
                        <a href="javascript:void(0)" class="fw-700 text-secondary"
                            @click="edit_address('{{ $cartAddress->id }}')">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M9 20.25H4.5C4.30109 20.25 4.11032 20.171 3.96967 20.0303C3.82902 19.8897 3.75 19.6989 3.75 19.5V15.3107C3.75 15.2122 3.7694 15.1146 3.80709 15.0237C3.84478 14.9327 3.90003 14.85 3.96967 14.7803L15.2197 3.53034C15.3603 3.38968 15.5511 3.31067 15.75 3.31067C15.9489 3.31067 16.1397 3.38968 16.2803 3.53034L20.4697 7.71968C20.6103 7.86033 20.6893 8.05109 20.6893 8.25001C20.6893 8.44892 20.6103 8.63968 20.4697 8.78034L9 20.25Z"
                                    stroke="#1F2029" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M12.75 6L18 11.25" stroke="#1F2029" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </a>
                        @auth
                            <a href="javascript:void(0)" class="fw-700 text-danger"
                                @click="remove_address('{{ $cartAddress->id }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M7 21q-.825 0-1.412-.587T5 19V6H4V4h5V3h6v1h5v2h-1v13q0 .825-.587 1.413T17 21zM17 6H7v13h10zM9 17h2V8H9zm4 0h2V8h-2zM7 6v13z"/></svg>
                            </a>
                        @endauth
                    </div>
                </div>
            @endif
            @foreach ($user_addresses as $key => $address)
                @continue($cartAddress && $address->id == $cartAddress->id)
                <div class="col-12 mb-2" x-show="showMore || @if (!$cartAddress) true @else false @endif">
                    <label class="aiz-megabox d-block bg-white mb-0">
                        <input type="radio" name="address_id" x-model="address_id"
                            @change="saveAddress($event.target)" value="{{ $address->id }}"
                            @if (!$cartAddress && ($address->set_default || $loop->first)) checked @endif required>
                        <span class="d-flex p-3 aiz-megabox-elem position-relative align-items-center ">
                            <span class="flex-grow-1  text-left">
                                <div>
                                    <span class=" d-block fs-18 fw-600">
                                        {{ optional($address->user)->name }}
                                    </span>
                                    <span class="px-4 py-1 btn my-2 bg-light text-dark border-none rounded-sm"
                                        title="{{ @$address->address_type }}">{{ @$address->address_type }}
                                    </span>
                                    <br />
                                    <span class="d-block fs-16 fw-400 mb-1 ">{{ @$address->phone }}</span>
                                    <span class="fs-16 fw-400 ">
                                        {{ $address->address }},
                                        {{ optional($address->area)->name }},
                                        {{ optional($address->city)->name }},
                                        {{ optional($address->state)->name }}
                                    </span>
                                </div>
                            </span>
                        </span>
                    </label>
                    <div class="position-absolute" style="top: 10px; right: 25px;">
                        <a href="javascript:void(0)" class="fw-700 text-secondary"
                            @click="edit_address('{{ $address->id }}')">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M9 20.25H4.5C4.30109 20.25 4.11032 20.171 3.96967 20.0303C3.82902 19.8897 3.75 19.6989 3.75 19.5V15.3107C3.75 15.2122 3.7694 15.1146 3.80709 15.0237C3.84478 14.9327 3.90003 14.85 3.96967 14.7803L15.2197 3.53034C15.3603 3.38968 15.5511 3.31067 15.75 3.31067C15.9489 3.31067 16.1397 3.38968 16.2803 3.53034L20.4697 7.71968C20.6103 7.86033 20.6893 8.05109 20.6893 8.25001C20.6893 8.44892 20.6103 8.63968 20.4697 8.78034L9 20.25Z"
                                    stroke="#1F2029" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M12.75 6L18 11.25" stroke="#1F2029" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </a>
                        {{-- @auth
                            <a href="javascript:void(0)" class="fw-700 text-danger"
                                @click="remove_address('{{ $address->id }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M7 21q-.825 0-1.412-.587T5 19V6H4V4h5V3h6v1h5v2h-1v13q0 .825-.587 1.413T17 21zM17 6H7v13h10zM9 17h2V8H9zm4 0h2V8h-2zM7 6v13z"/></svg>
                            </a>
                        @endauth --}}
                    </div>
                </div>
            @endforeach
            @if($user_addresses->count() > 1)
                <div class="d-flex justify-content-end  pr-3  w-100">
                    <button @click="showMore = !showMore" class="border-0 bg-transparent fs-16 pr-0">
                        <span x-text="showMore ? 'Show Less' : 'More Address'"></span>
                        <i class="fas fa-chevron-down pl-1 "></i>
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if (get_setting('google_map') == 1)
        @include('frontend.partials.google_map')
    @endif
@endif
