<form action="{{ route('payment.checkout') }}" class="form-default" role="form" method="POST" id="checkout-form">
    @csrf
    <input type="hidden" name="owner_id" value="{{ $carts[0]['owner_id'] }}">
    <input type="hidden" name="address_id" x-model="address_id">
    <div class="card shadow-sm border-0 rounded">
        <div class="card-header p-3">
            <h3 class="fs-16 fw-600 mb-0">
                {{ ('Select a payment option')}}
            </h3>
        </div>
        <div class="card-body text-center">
            <div class="mx-auto">
                <div class="row gutters-10 payment_select_option justify-content-center">
                    @if(get_setting('cash_payment') == 1)
                    @php
                    $digital = 0;
                    $cod_on = 1;
                    foreach($carts as $cartItem){
                        $product = $cartItem->product;
                        if($product['digital'] == 1){
                            $digital = 1;
                        }
                        if($product['cash_on_delivery'] == 0){
                            $cod_on = 0;
                        }
                    }
                    @endphp
                    @if($digital != 1 && $cod_on == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="cash_on_delivery" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/cod.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('Cash on Delivery')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @endif

                    @if(get_setting('sslcommerz_payment') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="sslcommerz" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option">
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/sslcommerz.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('sslcommerz')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif

                    @if(get_setting('bkash') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="bkash" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option">
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/bkash.jpg')}}" class="img-fluid mb-2 rounded">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('Bkash')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif

                    @if(get_setting('paypal_payment') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="paypal" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/paypal.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('Paypal')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @if(get_setting('stripe_payment') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="stripe" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/stripe.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('Stripe')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif

                    @if(get_setting('instamojo_payment') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="instamojo" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/instamojo.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('Instamojo')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @if(get_setting('razorpay') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="razorpay" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/rozarpay.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('Razorpay')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @if(get_setting('paystack') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="paystack" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/paystack.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('Paystack')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @if(get_setting('voguepay') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="voguepay" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/vogue.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('VoguePay')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @if(get_setting('payhere') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="payhere" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/payhere.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('payhere')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @if(get_setting('ngenius') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="ngenius" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/ngenius.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('ngenius')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @if(get_setting('iyzico') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="iyzico" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/iyzico.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('Iyzico')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @if(get_setting('nagad') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="nagad" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/nagad.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('Nagad')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif

                    @if(get_setting('aamarpay') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="aamarpay" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/aamarpay.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('Aamarpay')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @if(get_setting('authorizenet') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="authorizenet" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/authorizenet.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('Authorize Net')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @if(get_setting('payku') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="payku" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/payku.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('Payku')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @if(addon_is_activated('african_pg'))
                    @if(get_setting('mpesa') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="mpesa" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/mpesa.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('mpesa')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @if(get_setting('flutterwave') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="flutterwave" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/flutterwave.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('flutterwave')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @if(get_setting('payfast') == 1)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="payfast" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/payfast.png')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('payfast')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif
                    @endif
                    @if(addon_is_activated('paytm'))
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="paytm" class="online_payment" @click="handleOnlinePayment($event.target)" type="radio" name="payment_option" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ static_asset('assets/img/cards/paytm.jpg')}}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ ('Paytm')}}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endif

                    @if (Auth::check())
                    @if (addon_is_activated('offline_payment'))
                    @foreach(\App\Models\ManualPaymentMethod::all() as $method)
                    <div class="col-md-3 col-auto">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="{{ $method->heading }}" type="radio" name="payment_option" @change="toggleManualPaymentData({{ $method->id }})" data-id="{{ $method->id }}" checked>
                            <span class="d-block p-3 aiz-megabox-elem">
                                <img src="{{ uploaded_asset($method->photo) }}" class="img-fluid mb-2">
                                <span class="d-block text-center">
                                    <span class="d-block fw-600 fs-15">{{ $method->heading }}</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @endforeach

                    @foreach(\App\Models\ManualPaymentMethod::all() as $method)
                    <div id="manual_payment_info_{{ $method->id }}" class="d-none">
                        @php echo $method->description @endphp
                        @if ($method->bank_info != null)
                        <ul>
                            @foreach (json_decode($method->bank_info) as $key => $info)
                            <li>{{ ('Bank Name') }} - {{ $info->bank_name }}, {{ ('Account Name') }} - {{ $info->account_name }}, {{ ('Account Number') }} - {{ $info->account_number}}, {{ ('Routing Number') }} - {{ $info->routing_number }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @endforeach
                    @endif
                    @endif
                </div>
            </div>

            @if (addon_is_activated('offline_payment'))
            <div class="bg-white border mb-3 p-3 rounded text-left d-none">
                <div id="manual_payment_description">

                </div>
            </div>
            @endif
            @if (Auth::check() && get_setting('wallet_system') == 1)
            <div class="separator mb-3">
                <span class="bg-white px-3">
                    <span class="opacity-60">{{ ('Or')}}</span>
                </span>
            </div>
            <div class="text-center py-4">
                <div class="h6 mb-3">
                    <span class="opacity-80">{{ ('Your wallet balance :')}}</span>
                    <span class="fw-600">{{ single_price(Auth::user()->balance) }}</span>
                </div>
                @if(Auth::user()->balance < $total) <button type="button" class="btn btn-secondary" disabled>
                    {{ ('Insufficient balance')}}
                    </button>
                    @else
                    <button type="button" @click="use_wallet($event.target)" class="btn btn-primary fw-600">
                        {{ ('Pay with wallet')}}
                    </button>
                    @endif
            </div>
            @endif
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header p-3">
            <h3 class="fs-16 fw-600 mb-0">
                {{ ('Additional Note')}}
            </h3>
        </div>
        <div class="card-body text-center">
            <div class="mx-auto">
                <textarea class="form-control" style="border-radius: 5px !important;" rows="4" placeholder="{{ ('Order Note')}}" id="order_note" name="note" placeholder="Enter note here"></textarea>
            </div>
        </div>
    </div>
    <div class="pt-3 fs-15 font-weight-bold">
        <label class="aiz-checkbox">
            <input type="checkbox" required id="agree_checkbox">
            <span class="aiz-square-check"></span>
            <span class="fs-15 font-weight-bold">{{ ('I Agree to the')}}</span>
        </label>
        <a href="{{ route('terms') }}" class="text-secondary">Terms and Conditions</a>,
        <a href="{{ route('returnpolicy') }}" class="text-secondary">Return Policy</a> &
        <a href="{{ route('privacypolicy') }}" class="text-secondary">Privacy Policy</a>
    </div>

    <div class="row align-items-center pt-3">
        <div class="col-12 col-md-6 text-md-left text-center mb-3">
            <a href="{{ route('home') }}" class="link link--style-3 text-center">
                <i class="las la-arrow-left"></i>
                {{ ('Return to shop')}}
            </a>
        </div>
        <div class="col-12 col-md-6 text-md-right text-center">
            <button type="button" @click="submitOrder($event.target)" class="btn btn-primary fw-600 text-center">{{ ('Complete Order')}}</button>
        </div>
    </div>
</form>