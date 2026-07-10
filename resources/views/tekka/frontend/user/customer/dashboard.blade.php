@extends(config('app.theme').'frontend.layouts.user_panel')

@section('panel_content')
<div class="user-profile row bg-white py-2 rounded-sm align-items-center m-0 mb-3 px-3">
    <div class=" p-0 col-12 col-md-6 py-2 py-md-0">
        <p class="m-0 fw-500 fs-24 text-capitalize  text-lg-left text-start ">
            <span class="fw-700 " style="color:#FA7E16">Welcome, </span> {{ Auth::user()->name }}
        </p>
    </div>
    <div class="col-6 p-0 align-items-center justify-content-center justify-content-md-end pr-1 pr-md-4 d-none d-md-flex">
            <span class="avatar avatar-md pr-2 pr-md-0">
                @if (Auth::user()->avatar_original != null)
                    <img src="{{ uploaded_asset(Auth::user()->avatar_original) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                @else
                    <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="image rounded-circle" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                @endif

            </span>
            @php

                $user_group = $currentlyAuthenticatedUser->customeringroup;

            @endphp
            <div>
                <h4 class="h5 fs-16 fw-500 text-capitalize mb-1">
                    {{ Auth::user()->name }}
                </h4>
                @if(Auth::user()->phone != null)
                    <div class="text-truncate opacity-60 fs-14">{{ Auth::user()->phone }}</div>
                @else
                    <div class="text-truncate opacity-60 fs-14">{{ Auth::user()->email }}</div>
                @endif
            </div>
        </div>
</div>
<div class="bg-white px-3 py-4">
    <div class="aiz-titlebar mb-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3 m-0 fs-24 fw-500">{{ ('Dashboard') }}</h1>
            </div>
        </div>
    </div>
    <div class="row  ">
        <div class="col-6 pr-md-2 mb-2 mb-md-0 pr-1">
            <div class=" rounded-sm overflow-hidden gray-bg">
                <div class="px-2 py-3 px-sm-3 py-sm-4">

                    @php
                        $user_id = Auth::user()->id;
                        $cart = \App\Models\Cart::where('user_id', $user_id)->get();
                    @endphp
                    <svg class="fs-22 mb-2" width="25" height="25" viewBox="0 0 41 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.5 36.25C14.8807 36.25 16 35.1307 16 33.75C16 32.3693 14.8807 31.25 13.5 31.25C12.1193 31.25 11 32.3693 11 33.75C11 35.1307 12.1193 36.25 13.5 36.25Z" fill="#1F2029"/>
                        <path d="M29.75 36.25C31.1307 36.25 32.25 35.1307 32.25 33.75C32.25 32.3693 31.1307 31.25 29.75 31.25C28.3693 31.25 27.25 32.3693 27.25 33.75C27.25 35.1307 28.3693 36.25 29.75 36.25Z" fill="#1F2029"/>
                        <path d="M7.60715 11.25H35.6429L31.5181 25.6868C31.3688 26.2092 31.0535 26.6687 30.6197 26.9959C30.186 27.323 29.6575 27.5 29.1142 27.5H14.1358C13.5925 27.5 13.064 27.323 12.6303 26.9959C12.1965 26.6687 11.8812 26.2092 11.7319 25.6868L6.08046 5.9066C6.00583 5.64541 5.84816 5.41564 5.6313 5.25206C5.41443 5.08849 5.15019 5 4.87855 5H2.25" stroke="#1F2029" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                    @if(count($cart) > 0)
                    <div class="h4 fw-500 product-info">
                        {{ count($cart) }} {{ ('Product(s)') }}
                    </div>
                    @else
                    <div class="h4 fw-500 product-info">
                        0 {{ ('Product') }}
                    </div>
                    @endif
                    <div class="opacity-50 fw-400 fs-16 product-info-sub">{{ ('in your cart') }}</div>
                </div>

            </div>
        </div>
        {{-- <div class="col-md-4 p-0">
            <div class="bg-grad-2 text-white rounded-lg mb-4 overflow-hidden">
                <div class="px-3 py-4">
                    @php
                        $orders = \App\Models\Order::where('user_id', Auth::user()->id)->get();
                        $total = 0;
                        foreach ($orders as $key => $order) {
                            $total += count($order->orderDetails);
                        }
                    @endphp
                    <div class="h3 fw-700">{{ count(Auth::user()->wishlists)}} {{ ('Product(s)') }}</div>
                    <div class="opacity-50">{{ ('in your wishlist') }}</div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                    <path fill="rgba(255,255,255,0.3)" fill-opacity="1" d="M0,128L34.3,112C68.6,96,137,64,206,96C274.3,128,343,224,411,250.7C480,277,549,235,617,213.3C685.7,192,754,192,823,181.3C891.4,171,960,149,1029,117.3C1097.1,85,1166,43,1234,58.7C1302.9,75,1371,149,1406,186.7L1440,224L1440,320L1405.7,320C1371.4,320,1303,320,1234,320C1165.7,320,1097,320,1029,320C960,320,891,320,823,320C754.3,320,686,320,617,320C548.6,320,480,320,411,320C342.9,320,274,320,206,320C137.1,320,69,320,34,320L0,320Z"></path>
                </svg>
            </div>
        </div> --}}

        <div class="col-6 pl-md-2 pl-1">
            <div class="gray-bg rounded-sm  overflow-hidden">
                <div class="px-2 py-3 px-md-3 py-md-4">
                    @php
                        $orders = \App\Models\Order::where('user_id', Auth::user()->id)->get();
                        $total = 0;
                        foreach ($orders as $key => $order) {
                            $total += count($order->orderDetails);
                        }
                    @endphp
                    <i class="far fa-check-circle fs-22 mb-2"></i>
                    <div class="h4 fw-500 product-info">{{ $total }} {{ ('Product(s)') }}</div>
                    <div class="opacity-50 fs-16 fw-400 product-info-sub">{{ ('you total ordered') }}</div>
                </div>

            </div>
        </div>
    </div>
    <div class="row pt-3 pt-md-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-none">
                <div class="card-header border-0 p-0">
                    <h6 class="mb-0 fs-20 fw-500">{{ ('My Group') }} ({{ @$user_group->group->group_name }})</h6>
                </div>
                <div class="card-body px-0 py-2">
                @php

                    @$user_group = App\Models\Customeringroup::where('user_id', '=', Auth::user()->id)
                        ->where('status', '=', 1)
                        ->first();
                    if(!$user_group){
                        $user_group = App\Models\Customeringroup::where('status', '=', 1)->first();
                    }

                    @$groups = App\Models\Customergroup::all();

                    $found_key = array_search($user_group->group->id, array_column($groups->toArray(), 'id'));
                @endphp

                    <div class="customer-group-dashboard">

                        @foreach($groups as $index => $group)
                            <div class="shadow-none border-0 card position-relative @if(@$user_group->group->group_name == @$group->group_name) group-opacity @endif">

                                <i data-toggle="modal" data-target="#CustomerGroup{{@$group->id}}" class="las la-info-circle"></i>
                                <div class="card-body px-2 group-bg-deactive py-2 py-sm-3 py-md-4 py-xl-5 @if($index <= $found_key) group-bg-active @endif">

                                    <div class="text-center mb-1 mb-md-2 ">
                                        @if(@$group->group_image != '')
                                            <img width="50" src="{{ uploaded_asset(@$group->group_image)}}"  alt="">
                                        @else
                                            {!!@$group->group_icon!!}
                                        @endif

                                    </div>


                                    <p class="text-center fs-16 fw-400 @if(@$user_group->group->group_name == @$group->group_name) current-customer-group-name @endif" >{{@$group->group_name}}</p>
                                </div>
                            </div>
                            <!-- Modal -->
                                <div class="modal fade" id="CustomerGroup{{@$group->id}}" tabindex="-1" role="dialog" aria-labelledby="CustomerGroupTitle{{@$group->id}}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content p-0">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="CustomerGroupTitle{{@$group->id}}">{{@$group->group_name}}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    <div class="modal-body">
                                        @php
                                            $decodedMessage = json_decode(@$group->message);
                                            $decodedMessage = (array) $decodedMessage;
                                            if(is_array($decodedMessage) && count($decodedMessage) > 0){
                                                $decodedMessage = $decodedMessage;
                                            }else{
                                                $decodedMessage = @$group->message;
                                            }
                                        @endphp

                                        @if(is_array($decodedMessage))
                                            <h6>{{ $decodedMessage['title']}}</h6>
                                            <ul>
                                                @foreach($decodedMessage['offers'] as $offer)
                                                <li>{{$offer}}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            {{ $decodedMessage }}
                                        @endif
                                    </div>
                                    </div>
                                </div>
                                </div>



                        @endforeach



                    </div>
                    <div class="pt-2 mb-3 mt-5">
                        <div class="horizontal timeline">
                            <div class="steps">

                                 @foreach($groups as $index => $group)

                                    <div class="step  @if($index <= $found_key) bg-dark @endif">
                                        <span class="group-name  @php if(@$index == 0){
                                        echo "ml-2"; }@endphp @php if(@$index == 3){
                                        echo "last-group-name"; }@endphp @php if(@$user_group->group->group_name == @$group->group_name){
                                        echo "text-dark"; }@endphp">{{@$group->group_name}}</span>

                                        <span class="group-order   @php if(@$index == 0){
                                        echo "ml-3"; }@endphp @php if(@$index == 3){
                                        echo "last-group-name"; }@endphp">{{@$group->min_order_qty}} Order</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="line"
                                style="
                                    <?php
                                        if(isset($group->min_order_qty)) {
                                            if($group->min_order_qty <= 0) {
                                                echo 'width: 0%;';
                                            } elseif($group->min_order_qty < 20 ) {
                                                echo 'width: 33%;';
                                            } elseif($group->min_order_qty >= 20) {
                                                echo 'width: 66%;';
                                            } elseif($group->min_order_qty >= 50) {
                                                echo 'width: 100%;';
                                            }
                                        } else {
                                            echo 'width: 0%;';
                                        }
                                    ?>"

                            ></div>


                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5 ">
        @if(Auth::user()->addresses != null)
        @php
            $address = Auth::user()->addresses->where('set_default', 1)->first();
        @endphp
        @if($address != null)
        <div class="col-md-6 pr-2">
            <div class="card shadow-none border-0">
                <div class="card-header border-0  p-0">
                    <h6 class="mb-0 fw-500 fs-20 py-2">{{ ('Default Shipping Address') }}</h6>
                </div>
                <div class="card-body  reward-bg py-2">
                <div class="row py-1 edit-delivery-address w-100 mx-auto rounded-md justify-content-between mt-2">
                    <div class="col-10 p-0">
                        <h4 class="m-0 fw-500 fs-18 ">
                            {{ Auth::user()->name }}
                        </h4>
                        <button class="px-4 py-1 btn my-2 bg-light text-dark border-none rounded-sm">{{ $address->address_type ?? 'Home' }}</button>
                        <p class="fs-14 fw-400 m-0 pb-1 ">{{ $address->phone }}</p>
                        <span class="fs-14 fw-400 m-0">{{ $address->address }}<span>
                        @if(!empty($address->area))
                        <span>{{ optional($address->area)->name }}</span>,
                        @endif
                        @if(!empty($address->city))
                        <span>{{ optional($address->city)->name }}</span>,
                        @endif
                        @if(!empty($address->state))
                        <span>{{ optional($address->state)->name }}</span>
                        @endif
                        @if(!empty($address->postal_code))
                        <span>{{ $address->postal_code }}</span>,
                        @endif
                        @if(!empty($address->country))
                        {{-- <span>{{ optional($address->country)->name }}</span> --}}
                        @endif
                    </div>
                    <div class="col-2 d-flex justify-content-end p-0">
                        <span class="c-pointer border-0" onclick="window.location.href='{{ route('profile') }}'">
                            <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 18.25H1.5C1.30109 18.25 1.11032 18.171 0.96967 18.0303C0.829018 17.8897 0.75 17.6989 0.75 17.5V13.3107C0.75 13.2122 0.769399 13.1146 0.80709 13.0237C0.844781 12.9327 0.900026 12.85 0.96967 12.7803L12.2197 1.53034C12.3603 1.38968 12.5511 1.31067 12.75 1.31067C12.9489 1.31067 13.1397 1.38968 13.2803 1.53034L17.4697 5.71968C17.6103 5.86033 17.6893 6.05109 17.6893 6.25001C17.6893 6.44892 17.6103 6.63968 17.4697 6.78034L6 18.25Z" stroke="#1F2029" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                </div>
                </div>
            </div>
        </div>
        @endif
        @endif
        @if(get_setting('reward_point_system') == 1)
        <div class="col-md-6 pl-2">
            <div class="card shadow-none border-0">
            <div class="card-header border-0 p-0">
                <h6 class="mb-0 fw-500 fs-20 py-2">{{ ('Reward points') }}</h6>
            </div>
            <div class="card-body reward-bg d-flex justify-content-center flex-column rounded-md ">
                <svg class="mb-2" width="35" height="35" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M38.1377 20.0781C38.1184 20.0297 38.118 19.9758 38.1364 19.9271L39.7885 15.7844C40.0371 15.152 40.0551 14.4522 39.8394 13.8078C39.6238 13.1635 39.1882 12.6155 38.609 12.2601L34.728 9.83988C34.6699 9.80555 34.626 9.7515 34.6043 9.68755L33.3922 5.40811C33.2088 4.75643 32.8066 4.18778 32.2532 3.79776C31.6998 3.40774 31.029 3.22013 30.3536 3.26647L25.7201 3.48644C25.6306 3.48601 25.543 3.46028 25.4675 3.41223L21.856 0.632597C21.3226 0.222323 20.6685 -0.00012207 19.9955 -0.00012207C19.3226 -0.00012207 18.6685 0.222323 18.1351 0.632597L14.5263 3.4109C14.4496 3.45864 14.3614 3.48473 14.2711 3.48641L9.63759 3.26644C8.96347 3.22774 8.29612 3.41851 7.74431 3.80767C7.19249 4.19682 6.78876 4.7614 6.59891 5.40939L5.38681 9.68624C5.36631 9.74972 5.32323 9.80347 5.26573 9.83728L1.38339 12.2588C0.803652 12.6147 0.36763 13.1632 0.151731 13.8083C-0.0641676 14.4534 -0.0461734 15.1539 0.202561 15.787L1.85339 19.9219C1.87268 19.9703 1.87316 20.0242 1.85472 20.0729L0.202561 24.2156C-0.0459923 24.848 -0.0640132 25.5478 0.151657 26.1922C0.367328 26.8366 0.802928 27.3845 1.3821 27.7399L5.2631 30.1601C5.32124 30.1945 5.36513 30.2485 5.38678 30.3125L6.59886 34.5919C6.78075 35.2445 7.18261 35.8142 7.73637 36.2045C8.29013 36.5948 8.96177 36.7817 9.63753 36.7335L14.271 36.5136C14.3607 36.5133 14.4484 36.5391 14.5236 36.5878L18.1351 39.3674C18.6686 39.7777 19.3226 40.0001 19.9956 40.0001C20.6685 40.0001 21.3226 39.7777 21.856 39.3674L25.4649 36.5891C25.5402 36.5384 25.6292 36.512 25.72 36.5136L30.3535 36.7336C31.027 36.7688 31.6927 36.5766 32.2437 36.188C32.7948 35.7994 33.1993 35.2368 33.3922 34.5906L34.6043 30.3139C34.6248 30.2504 34.6679 30.1966 34.7254 30.1628L38.6077 27.7413C39.1874 27.3854 39.6235 26.8369 39.8394 26.1918C40.0553 25.5467 40.0373 24.8462 39.7885 24.2131L38.1377 20.0781ZM28.9588 18.826L25.0903 22.0186C24.9392 22.1462 24.8277 22.3143 24.7691 22.5032C24.7104 22.6921 24.707 22.8938 24.7593 23.0846L26.0946 28.0455C26.1559 28.2376 26.1561 28.444 26.0953 28.6362C26.0346 28.8285 25.9157 28.9972 25.7552 29.1191C25.5946 29.2411 25.4003 29.3104 25.1988 29.3174C24.9973 29.3244 24.7985 29.2688 24.6299 29.1583L20.5314 26.2974C20.3746 26.1868 20.1874 26.1274 19.9956 26.1274C19.8037 26.1274 19.6165 26.1868 19.4597 26.2974L15.3612 29.1583C15.1926 29.2688 14.9939 29.3244 14.7924 29.3174C14.5909 29.3104 14.3965 29.2411 14.2359 29.1191C14.0754 28.9972 13.9565 28.8285 13.8958 28.6362C13.835 28.444 13.8352 28.2376 13.8965 28.0455L15.2319 23.0846C15.2841 22.8938 15.2807 22.6921 15.2221 22.5032C15.1634 22.3143 15.0519 22.1462 14.9008 22.0186L11.0323 18.826C10.8761 18.6974 10.7618 18.5252 10.7039 18.3314C10.646 18.1375 10.6472 17.9308 10.7072 17.7377C10.7672 17.5445 10.8834 17.3735 11.0409 17.2466C11.1985 17.1197 11.3902 17.0426 11.5918 17.0251L16.5156 16.8207C16.7094 16.8102 16.8957 16.7418 17.0502 16.6244C17.2048 16.5069 17.3205 16.3458 17.3826 16.1619L19.0904 11.3273C19.152 11.1357 19.2729 10.9687 19.4356 10.8502C19.5982 10.7316 19.7943 10.6678 19.9956 10.6678C20.1968 10.6678 20.3929 10.7316 20.5556 10.8502C20.7182 10.9687 20.8391 11.1357 20.9008 11.3273L22.6086 16.1619C22.6706 16.3458 22.7864 16.5069 22.9409 16.6243C23.0955 16.7418 23.2817 16.8102 23.4755 16.8207L28.3993 17.0251C28.6009 17.0426 28.7926 17.1197 28.9502 17.2466C29.1078 17.3735 29.224 17.5444 29.284 17.7376C29.344 17.9308 29.3451 18.1375 29.2872 18.3313C29.2293 18.5252 29.115 18.6974 28.9588 18.826Z" fill="#1F2029"/>
                </svg>

                <div class="h4 fs-24 fw-600  py-1">
                    {{ Auth::user()->point_balance }}
                </div>
                <div class="fs-16 fw-400 pb-1">
                    Your Total Rewards points
                </div>
            </div>
            </div>
        </div>
        @endif
        <!-- @if (get_setting('classified_product'))
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Purchased Package') }}</h6>
                </div>
                <div class="card-body text-center">
                    @php
                        $customer_package = \App\Models\CustomerPackage::find(Auth::user()->customer_package_id);
                    @endphp
                    @if($customer_package != null)
                        <img src="{{ uploaded_asset($customer_package->logo) }}" class="img-fluid mb-4 h-110px">
                        <p class="mb-1 text-muted">{{ ('Product Upload') }}: {{ $customer_package->product_upload }} {{ ('Times')}}</p>
                        <p class="text-muted mb-4">{{ ('Product Upload Remaining') }}: {{ Auth::user()->remaining_uploads }} {{ ('Times')}}</p>
                        <h5 class="fw-600 mb-3 text-primary">{{ ('Current Package') }}: {{ $customer_package->getTranslation('name') }}</h5>
                    @else
                        <h5 class="fw-600 mb-3 text-primary">{{ ('Package Not Found')}}</h5>
                    @endif
                        <a href="{{ route('customer_packages_list_show') }}" class="btn btn-success d-inline-block">{{ ('Upgrade Package') }}</a>
                </div>
            </div>
        </div>
        @endif -->
    </div>


</div>
<div class="my-order-link">
    <a href="{{ route('purchase_history.index') }}" class="text-primary fs-16 fw-400 py-3 " >My order</a>
</div>
@endsection
