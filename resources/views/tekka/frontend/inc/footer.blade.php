@if(@intval(json_decode(@get_setting('terms_policy_71')))==1)
<section class="bg-white border-top mt-auto">
    <div class="container">
        <div class="row no-gutters">
            <div class="col-lg-3 col-md-6 col-6">
                <a class="text-reset border-md-left border-bottom border-md-bottom-none text-center p-md-4 p-2 d-block" href="{{ route('terms') }}">
                    <i class="la la-file-text la-3x text-primary mb-2"></i>
                    <h4 class="h6">{{ ('Terms & conditions') }}</h4>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <a class="text-reset border-left border-bottom border-md-bottom-none text-center p-md-4 p-2 d-block" href="{{ route('returnpolicy') }}">
                    <i class="la la-mail-reply la-3x text-primary mb-2"></i>
                    <h4 class="h6">{{ ('Return Policy') }}</h4>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <a class="text-reset border-md-left text-center p-md-4 p-2 d-block" href="{{ route('supportpolicy') }}">
                    <i class="la la-support la-3x text-primary mb-2"></i>
                    <h4 class="h6">{{ ('Support Policy') }}</h4>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <a class="text-reset border-left border-md-right text-center p-md-4 p-2 d-block" href="{{ route('privacypolicy') }}">
                    <i class="las la-exclamation-circle la-3x text-primary mb-2"></i>
                    <h4 class="h6">{{ ('Privacy Policy') }}</h4>
                </a>
            </div>
        </div>
    </div>
</section>
@endif

<section class="bg-plain-dark  py-4 py-md-5 text-light footer-widget">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-xl-3 text-center text-md-left">
                <div class="mt-4">
                    <a href="{{ route('home') }}" class="d-block">
                        @if(get_setting('footer_logo') != null)
                            <img class="lazyload" src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset(get_setting('footer_logo')) }}" alt="{{ env('APP_NAME') }}" height="44">
                        @else
                            <img class="lazyload" src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}" height="44">
                        @endif
                    </a>
                    <div class="my-3">
                        {!! get_setting('about_us_description',null,App::getLocale()) !!}
                    </div>
                    <div class="d-block d-md-block mb-4">
                        <form class="form-inline subscribe_form" method="POST" action="{{ route('subscribers.store') }}">
                            @csrf
                            <div class="form-group mb-0">
                                <input type="email" class="form-control" placeholder="{{ ('Your Email Address') }}" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                {{ ('Subscribe') }}
                            </button>
                        </form>
                    </div>

                    <div class=" ">

                        <h4 class="fs-13 text-uppercase fw-600 border-bottom border-gray-900 pb-2 mb-2 mt-4">
                            {{ ('Share Your Love') }}
                        </h4>

                        @if ( get_setting('show_social_links') )
                        <ul class="list-inline my-3 my-md-0 social colored">
                            @if ( get_setting('facebook_link') !=  null )
                            <li class="list-inline-item">
                                <a href="{{ get_setting('facebook_link') }}" target="_blank" class="facebook"><i class="fab fa-facebook-f fs-20"></i></a>
                            </li>
                            @endif
                            @if ( get_setting('twitter_link') !=  null )
                            <li class="list-inline-item">
                                <a href="{{ get_setting('twitter_link') }}" target="_blank" class="twitter"><i class="fab fa-twitter fs-20"></i></a>
                            </li>
                            @endif
                            @if ( get_setting('instagram_link') !=  null )
                            <li class="list-inline-item">
                                <a href="{{ get_setting('instagram_link') }}" target="_blank" class="instagram"><i class="fab fa-instagram fs-20"></i></a>
                            </li>
                            @endif
                            @if ( get_setting('youtube_link') !=  null )
                            <li class="list-inline-item">
                                <a href="{{ get_setting('youtube_link') }}" target="_blank" class="youtube"><i class="fab fa-youtube fs-20"></i></a>
                            </li>
                            @endif
                            @if ( get_setting('linkedin_link') !=  null )
                            <li class="list-inline-item">
                                <a href="{{ get_setting('linkedin_link') }}" target="_blank" class="linkedin"><i class="fab fa-linkedin-in fs-20"></i></a>
                            </li>
                            @endif
                        </ul>
                        @endif
                    </div>

                    <!-- <div class="w-300px mw-100 mx-auto mx-md-0">
                        @if(get_setting('play_store_link') != null)
                            <a href="{{ get_setting('play_store_link') }}" target="_blank" class="d-inline-block mr-3 ml-0">
                                <img src="{{ static_asset('assets/img/play.png') }}" class="mx-100 h-40px">
                            </a>
                        @endif
                        @if(get_setting('app_store_link') != null)
                            <a href="{{ get_setting('app_store_link') }}" target="_blank" class="d-inline-block">
                                <img src="{{ static_asset('assets/img/app.png') }}" class="mx-100 h-40px">
                            </a>
                        @endif
                    </div> -->

                </div>
            </div>
            <div class="col-md-3 col-lg-3 ml-auto mr-0">
                <div class="text-center text-md-left py-2 py-md-0 mt-md-4">
                    <h4 class="collaps-nav fs-16 text-uppercase fw-600 border-bottom border-gray-900 pb-2 mb-md-4" >
                        {{ ('Contact Info') }}
                        <i class="fas fa-chevron-down ml-2 d-md-none"></i>
                    </h4>
                    <ul class="collaps-contents list-unstyled fs-14" >
                        <li class="mb-2">
                           <span class="d-block footer__sub-heading">{{ ('Address') }}:</span>
                           <span class="d-block">{{ get_setting('contact_address',null,App::getLocale()) }}</span>
                        </li>
                        <li class="mb-2">
                           <span class="d-block footer__sub-heading">{{ ('Phone')}}:</span>
                           <span class="d-block">{{ get_setting('contact_phone') }}</span>
                        </li>
                        <li class="mb-2">
                           <span class="d-block footer__sub-heading">{{ ('Email')}}:</span>
                           <span class="d-block">
                               <a href="mailto:{{ get_setting('contact_email') }}" class="text-reset">{{ get_setting('contact_email')  }}</a>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-3 col-lg-3 ml-auto mr-0">
                <div class="text-center text-md-left  py-2 py-md-0 mt-md-4">
                    <h4 class="collaps-nav fs-16 text-uppercase fw-600 border-bottom border-gray-900 pb-2 mb-md-4" >
                        {{ get_setting('widget_one',null,App::getLocale()) }}
                        <i class="fas fa-chevron-down ml-2 d-md-none"></i>
                    </h4>
                    <ul class="collaps-contents list-unstyled fs-14" >
                        @if ( get_setting('widget_one_labels',null,App::getLocale()) !=  null )
                            @foreach (json_decode( get_setting('widget_one_labels',null,App::getLocale()), true) as $key => $value)
                            <li class="mb-2">
                                @if ($value === 'About Us')
                                <a href="{{ route('about-us') }}" class="text-reset">
                                    {{ $value }}
                                </a>
                                @else
                                <a href="{{ json_decode( get_setting('widget_one_links'), true)[$key] }}" class="text-reset">
                                    {{ $value }}
                                </a>
                                @endif
                                {{-- <a href="{{ json_decode( get_setting('widget_one_links'), true)[$key] }}" class="text-reset">
                                    {{ $value }}
                                </a> --}}
                            </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>

            <div class="col-md-3 col-lg-3 ml-auto mr-0">
                <div class="text-center text-md-left  py-2 py-md-0 mt-md-4">
                    <h4 class="collaps-nav fs-16 text-uppercase fw-600 border-bottom border-gray-900 pb-2 mb-md-4" >
                        {{ ('My Account') }}
                        <i class="fas fa-chevron-down ml-2 d-md-none"></i>
                    </h4>
                    <ul class=" collaps-contents list-unstyled fs-14 ">
                        @if (Auth::check())
                            <li class="mb-2">
                                <a class="text-reset" href="{{ route('logout') }}">
                                    {{ ('Logout') }}
                                </a>
                            </li>
                        @else
                            <li class="mb-2">
                                <a class="text-reset" href="{{ route('user.login') }}">
                                    {{ ('Login') }}
                                </a>
                            </li>
                        @endif
                        <li class="mb-2">
                            <a class="text-reset" href="{{ route('purchase_history.index') }}">
                                {{ ('Order History') }}
                            </a>
                        </li>
                        <li class="mb-2">
                            <a class="text-reset" href="{{ route('wishlists.index') }}">
                                {{ ('My Wishlist') }}
                            </a>
                        </li>
                        <li class="mb-2">
                            <a class="text-reset" href="{{ route('orders.track') }}">
                                {{ ('Track Order') }}
                            </a>
                        </li>
                        @if (addon_is_activated('affiliate_system'))
                            <li class="mb-2">
                                <a class="text-light" href="{{ route('affiliate.apply') }}">{{ ('Be an affiliate partner')}}</a>
                            </li>
                        @endif
                    </ul>
                </div>
                @if (get_setting('vendor_system_activation') == 1)
                    <div class="text-center text-md-left mt-4">
                        <h4 class="fs-16 text-uppercase fw-600 border-bottom border-gray-900 pb-2 mb-4">
                            {{ ('Be a Seller') }}
                        </h4>
                        <a href="{{ route('shops.create') }}" class="btn btn-secondary btn-sm shadow-md">
                            {{ ('Apply Now') }}
                        </a>
                    </div>
                @endif

                <div class=" text-center text-xl-left w-300px mw-100 mx-auto mx-md-0 d-flex justify-content-center flex-column">
                    <h4 class="fs-16 text-uppercase fw-600 border-bottom border-gray-900 pb-2 mb-2 mt-4">
                        {{ ('Download Our App') }}
                    </h4>

                    <div class="footer__app-link d-flex flex-row justify-content-center justify-content-xl-start">
                        @if(get_setting('play_store_link') != null)
                            <a href="{{ get_setting('play_store_link') }}" target="_blank" class="d-inline-block">
                                <img src="{{ static_asset('assets/img/play.png') }}" class="h-40px">
                            </a>
                        @endif
                        @if(get_setting('app_store_link') != null)
                            <a href="{{ get_setting('app_store_link') }}" target="_blank" class="d-inline-block">
                                <img src="{{ static_asset('assets/img/app.png') }}" class="h-40px">
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="pt-3 pb-8 pb-xl-3 footer_bottom text-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="text-center text-white text-md-left pb-2" current-verison="{{get_setting("current_version")}}">
                    {!! get_setting('frontend_copyright_text',null,App::getLocale()) !!}
                </div>
            </div>
            <!-- <div class="col-lg-3">
                @if ( get_setting('show_social_links') )
                <ul class="list-inline my-3 my-md-0 social colored text-center">
                    @if ( get_setting('facebook_link') !=  null )
                    <li class="list-inline-item">
                        <a href="{{ get_setting('facebook_link') }}" target="_blank" class="facebook"><i class="lab la-facebook-f"></i></a>
                    </li>
                    @endif
                    @if ( get_setting('twitter_link') !=  null )
                    <li class="list-inline-item">
                        <a href="{{ get_setting('twitter_link') }}" target="_blank" class="twitter"><i class="lab la-twitter"></i></a>
                    </li>
                    @endif
                    @if ( get_setting('instagram_link') !=  null )
                    <li class="list-inline-item">
                        <a href="{{ get_setting('instagram_link') }}" target="_blank" class="instagram"><i class="lab la-instagram"></i></a>
                    </li>
                    @endif
                    @if ( get_setting('youtube_link') !=  null )
                    <li class="list-inline-item">
                        <a href="{{ get_setting('youtube_link') }}" target="_blank" class="youtube"><i class="lab la-youtube"></i></a>
                    </li>
                    @endif
                    @if ( get_setting('linkedin_link') !=  null )
                    <li class="list-inline-item">
                        <a href="{{ get_setting('linkedin_link') }}" target="_blank" class="linkedin"><i class="lab la-linkedin-in"></i></a>
                    </li>
                    @endif
                </ul>
                @endif
            </div> -->
            <div class="col-lg-6">
                <div class="text-center text-md-right">
                    <ul class="list-inline mb-0">
                        @if ( get_setting('payment_method_images') !=  null )
                            @foreach (explode(',', get_setting('payment_method_images')) as $key => $value)
                                <li class="list-inline-item">
                                    <img src="{{ uploaded_asset($value) }}" height="30" class="mw-100 h-auto rounded">
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- $gg = Route::is('product'); -->

@if (!Route::is('job_posts.show'))
    <div class="aiz-mobile-bottom-nav d-xl-none fixed-bottom  shadow-lg {{ Route::is('product') ? 'd-none' : '' }}" style="box-shadow: 0px -1px 10px rgb(0 0 0 / 15%)!important; ">
        <div class="row align-items-center gutters-5">
            <div class="col">
                <a href="{{ route('home') }}" class="text-reset d-block text-center pb-1 pt-1">
                    <!-- <i class="fas fa-home "></i> -->
                    <span class="fa-2x {{ areActiveRoutes(['home'],'opacity-100 text-active mobile-isActive')}}">
                        <svg width="22" height="23" viewBox="0 0 22 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20.9312 9.34202L12.1812 1.39046C11.8586 1.09532 11.4372 0.931641 11 0.931641C10.5627 0.931641 10.1413 1.09532 9.81874 1.39046L1.06874 9.34202C0.889588 9.50592 0.746504 9.70531 0.648588 9.9275C0.550672 10.1497 0.500066 10.3898 0.499989 10.6326V20.7061C0.492875 21.1473 0.648637 21.5756 0.937489 21.9092C1.10143 22.0956 1.30338 22.2448 1.52977 22.3467C1.75616 22.4485 2.00174 22.5008 2.24999 22.4998H7.49999C7.73205 22.4998 7.95461 22.4076 8.11871 22.2436C8.2828 22.0795 8.37499 21.8569 8.37499 21.6248V16.3748C8.37499 16.1428 8.46718 15.9202 8.63127 15.7561C8.79537 15.592 9.01792 15.4998 9.24999 15.4998H12.75C12.9821 15.4998 13.2046 15.592 13.3687 15.7561C13.5328 15.9202 13.625 16.1428 13.625 16.3748V21.6248C13.625 21.8569 13.7172 22.0795 13.8813 22.2436C14.0454 22.4076 14.2679 22.4998 14.5 22.4998H19.75C20.0402 22.5024 20.3263 22.4309 20.5812 22.292C20.8586 22.141 21.0902 21.9181 21.2519 21.6468C21.4135 21.3755 21.4992 21.0657 21.5 20.7498V10.6326C21.4999 10.3898 21.4493 10.1497 21.3514 9.9275C21.2535 9.70531 21.1104 9.50592 20.9312 9.34202Z" fill="#fff"/>
                        </svg>
                    </span>


                    <span class="d-block fs-10 fw-600 text-white mt-1  {{ areActiveRoutes(['home'],'opacity-100 fw-600 active-nav')}}">{{ ('Home') }}</span>
                </a>
            </div>

            <div class="col">
                <a href="{{ route('categories.all') }}" class="text-reset d-block text-center pb-1 pt-1">
                    <!-- <i class="fa fa-cog "></i> -->
                    <span class="fa-spin fa-2x  {{ areActiveRoutes(['categories.all'],'opacity-100 text-active mobile-isActive')}}">
                        <svg width="21" height="22" viewBox="0 0 21 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.16667 9.83333H8.16667C8.47609 9.83333 8.77283 9.71042 8.99162 9.49162C9.21042 9.27283 9.33333 8.97609 9.33333 8.66667V1.66667C9.33333 1.35725 9.21042 1.0605 8.99162 0.841709C8.77283 0.622916 8.47609 0.5 8.16667 0.5H1.16667C0.857247 0.5 0.560501 0.622916 0.341709 0.841709C0.122916 1.0605 0 1.35725 0 1.66667V8.66667C0 8.97609 0.122916 9.27283 0.341709 9.49162C0.560501 9.71042 0.857247 9.83333 1.16667 9.83333ZM12.8333 9.83333H19.8333C20.1428 9.83333 20.4395 9.71042 20.6583 9.49162C20.8771 9.27283 21 8.97609 21 8.66667V1.66667C21 1.35725 20.8771 1.0605 20.6583 0.841709C20.4395 0.622916 20.1428 0.5 19.8333 0.5H12.8333C12.5239 0.5 12.2272 0.622916 12.0084 0.841709C11.7896 1.0605 11.6667 1.35725 11.6667 1.66667V8.66667C11.6667 8.97609 11.7896 9.27283 12.0084 9.49162C12.2272 9.71042 12.5239 9.83333 12.8333 9.83333ZM1.16667 21.5H8.16667C8.47609 21.5 8.77283 21.3771 8.99162 21.1583C9.21042 20.9395 9.33333 20.6428 9.33333 20.3333V13.3333C9.33333 13.0239 9.21042 12.7272 8.99162 12.5084C8.77283 12.2896 8.47609 12.1667 8.16667 12.1667H1.16667C0.857247 12.1667 0.560501 12.2896 0.341709 12.5084C0.122916 12.7272 0 13.0239 0 13.3333V20.3333C0 20.6428 0.122916 20.9395 0.341709 21.1583C0.560501 21.3771 0.857247 21.5 1.16667 21.5ZM16.3333 21.5C18.907 21.5 21 19.407 21 16.8333C21 14.2597 18.907 12.1667 16.3333 12.1667C13.7597 12.1667 11.6667 14.2597 11.6667 16.8333C11.6667 19.407 13.7597 21.5 16.3333 21.5Z" fill="#fff"/>
                        </svg>

                    </span>
                    <span class="d-block fs-10 fw-600 text-white mt-1  {{ areActiveRoutes(['categories.all'],'opacity-100 fw-600 active-nav')}}">{{ ('Categories') }}</span>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('flash-deals') }}" class="text-reset d-block text-center pb-1 pt-1">
                    <!-- <i class="fas fa-tag "></i> -->
                    <span class="fa-2x  {{ areActiveRoutes(['flash-deals'],'opacity-100 text-active mobile-isActive')}}">
                        <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M23.4784 11.5102L12.9842 1.016C12.5584 0.595996 11.975 0.333496 11.3334 0.333496H3.16671C1.87754 0.333496 0.833374 1.37766 0.833374 2.66683V10.8335C0.833374 11.481 1.09587 12.0643 1.52171 12.4843L12.0217 22.9843C12.4417 23.4043 13.025 23.6668 13.6667 23.6668C14.3084 23.6668 14.8975 23.4043 15.3175 22.9843L23.4842 14.8177C23.9042 14.3918 24.1667 13.8085 24.1667 13.1668C24.1667 12.5193 23.9042 11.936 23.4784 11.5102ZM4.91671 6.16683C3.94837 6.16683 3.16671 5.38516 3.16671 4.41683C3.16671 3.4485 3.94837 2.66683 4.91671 2.66683C5.88504 2.66683 6.66671 3.4485 6.66671 4.41683C6.66671 5.38516 5.88504 6.16683 4.91671 6.16683Z" fill="#fff"/>
                        </svg>
                    </span>
                    <span class="d-block fs-10 fw-600 text-white mt-1   {{ areActiveRoutes(['flash-deals'],'opacity-100 fw-600 active-nav')}}">{{ ('Flashdeal') }}</span>

                </a>
            </div>

            @php
                if(auth()->user() != null) {
                    $user_id = Auth::user()->id;
                    $cart = \App\Models\Cart::where('user_id', $user_id)->get();
                } else {
                    $temp_user_id = Session()->get('temp_user_id');
                    if($temp_user_id) {
                        $cart = \App\Models\Cart::where('temp_user_id', $temp_user_id)->get();
                    }
                }
            @endphp
            <div class="col">
                <a href="{{ route('cart') }}" class="text-reset d-block text-center pb-1 pt-1">
                    <span class="d-inline-block position-relative px-2">
                        <!-- <i class="fas fa-shopping-cart "></i> -->
                        <span class="fa-2x  {{ areActiveRoutes(['cart'],'opacity-100 text-active mobile-isActive')}}">
                            <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.06663 18.0668C5.84746 18.0668 4.84996 19.0643 4.84996 20.2835C4.84996 21.5027 5.84746 22.5002 7.06663 22.5002C8.28579 22.5002 9.28329 21.5027 9.28329 20.2835C9.28329 19.0643 8.28579 18.0668 7.06663 18.0668ZM0.416626 0.333496V2.55016H2.63329L6.62329 10.9735L5.07163 13.6335C4.96079 13.966 4.84996 14.4093 4.84996 14.7418C4.84996 15.961 5.84746 16.9585 7.06663 16.9585H20.3666V14.7418H7.50996C7.39913 14.7418 7.28829 14.631 7.28829 14.5202V14.4093L8.28579 12.5252H16.4875C17.3741 12.5252 18.0391 12.0818 18.3716 11.4168L22.3616 4.21266C22.5833 3.991 22.5833 3.88016 22.5833 3.6585C22.5833 2.9935 22.14 2.55016 21.475 2.55016H5.07163L4.07413 0.333496H0.416626ZM18.15 18.0668C16.9308 18.0668 15.9333 19.0643 15.9333 20.2835C15.9333 21.5027 16.9308 22.5002 18.15 22.5002C19.3691 22.5002 20.3666 21.5027 20.3666 20.2835C20.3666 19.0643 19.3691 18.0668 18.15 18.0668Z" fill="#fff"/>
                            </svg>

                        </span>
                    </span>
                    <span class="d-block mt-1 fs-10 fw-600 text-white {{ areActiveRoutes(['cart'],'active-nav opacity-100 fw-600')}}">
                        {{ ('Cart') }}
                        @php
                            $count = (isset($cart) && count($cart)) ? count($cart) : 0;
                        @endphp
                        (<span class="cart-count">{{$count}}</span>)
                    </span>
                </a>
            </div>
            {{-- <div class="col">
                <a href="{{ route('all-notifications') }}" class="text-reset d-block text-center pb-1 pt-1">
                    <span class="d-inline-block position-relative px-2">
                        <i class="fas fa-bell fa-2x opacity-60 {{ areActiveRoutes(['all-notifications'],'opacity-100 text-primary')}}"></i>
                        @if(Auth::check() && count(Auth::user()->unreadNotifications) > 0)
                            <span class="badge badge-sm badge-dot badge-circle badge-primary position-absolute absolute-top-right" style="right: 7px;top: -2px;"></span>
                        @endif
                    </span>
                    <span class="d-block fs-10 fw-600 text-white mt-1 {{ areActiveRoutes(['all-notifications'],'opacity-100 fw-600')}}">{{ ('Notifications') }}</span>
                </a>
            </div> --}}
            <div class="col">
            @if (Auth::check())
                @if(isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="text-reset d-block text-center pb-1 pt-1">
                        <span class="d-block mx-auto">
                            @if(Auth::user()->photo != null)
                                <!-- <img src="{{ custom_asset(Auth::user()->avatar_original)}}" class="rounded-circle size-20px"> -->
                                <!-- <i class="fas fa-user fa-2x opacity-60 {{ areActiveRoutes(['dashboard'],'opacity-100 text-active')}}"></i> -->
                                <span class="fa-2x  {{ areActiveRoutes(['dashboard'],'opacity-100 text-active mobile-isActive')}}">
                                    <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12.5 14.3335C15.7216 14.3335 18.3333 11.1995 18.3333 7.3335C18.3333 3.4675 15.7216 0.333496 12.5 0.333496C9.2783 0.333496 6.66663 3.4675 6.66663 7.3335C6.66663 11.1995 9.2783 14.3335 12.5 14.3335Z" fill="#fff"/>
                                        <path d="M23.9333 20.2833C22.8833 18.1833 20.9 16.4333 18.3333 15.3833C17.6333 15.15 16.8166 15.15 16.2333 15.5C15.0666 16.2 13.9 16.55 12.5 16.55C11.1 16.55 9.9333 16.2 8.76663 15.5C8.1833 15.2667 7.36663 15.15 6.66663 15.5C4.09997 16.55 2.11663 18.3 1.06663 20.4C0.249968 21.9167 1.5333 23.6667 3.2833 23.6667H21.7166C23.4666 23.6667 24.75 21.9167 23.9333 20.2833Z" fill="#fff"/>
                                    </svg>

                                </span>
                            @else
                                <!-- <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="rounded-circle size-20px"> -->
                                <span class="fa-2x  {{ areActiveRoutes(['dashboard'],'opacity-100 text-active mobile-isActive')}}">
                                    <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12.5 14.3335C15.7216 14.3335 18.3333 11.1995 18.3333 7.3335C18.3333 3.4675 15.7216 0.333496 12.5 0.333496C9.2783 0.333496 6.66663 3.4675 6.66663 7.3335C6.66663 11.1995 9.2783 14.3335 12.5 14.3335Z" fill="#fff"/>
                                        <path d="M23.9333 20.2833C22.8833 18.1833 20.9 16.4333 18.3333 15.3833C17.6333 15.15 16.8166 15.15 16.2333 15.5C15.0666 16.2 13.9 16.55 12.5 16.55C11.1 16.55 9.9333 16.2 8.76663 15.5C8.1833 15.2667 7.36663 15.15 6.66663 15.5C4.09997 16.55 2.11663 18.3 1.06663 20.4C0.249968 21.9167 1.5333 23.6667 3.2833 23.6667H21.7166C23.4666 23.6667 24.75 21.9167 23.9333 20.2833Z" fill="#fff"/>
                                    </svg>

                                </span>

                            @endif
                        </span>
                        <span class="d-block fs-10 fw-600 text-white mt-1 {{ areActiveRoutes(['dashboard'],' opacity-100 text-active mobile-isActive')}}" >{{ ('Account') }}</span>
                    </a>
                @else
                    <a href="javascript:void(0)" class="text-reset d-block text-center pb-1 pt-1 mobile-side-nav-thumb" data-toggle="class-toggle" data-backdrop="static" data-target=".aiz-mobile-side-nav">
                        <span class="d-block mx-auto">
                            @if(Auth::user()->photo != null)
                                <!-- <img src="{{ custom_asset(Auth::user()->avatar_original)}}" class="rounded-circle size-20px"> -->
                                <!-- <i class="fas fa-user "></i> -->
                                <span class="fa-2x  {{ areActiveRoutes(['dashboard'],'opacity-100 text-active mobile-isActive')}}">
                                    <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12.5 14.3335C15.7216 14.3335 18.3333 11.1995 18.3333 7.3335C18.3333 3.4675 15.7216 0.333496 12.5 0.333496C9.2783 0.333496 6.66663 3.4675 6.66663 7.3335C6.66663 11.1995 9.2783 14.3335 12.5 14.3335Z" fill="#fff"/>
                                        <path d="M23.9333 20.2833C22.8833 18.1833 20.9 16.4333 18.3333 15.3833C17.6333 15.15 16.8166 15.15 16.2333 15.5C15.0666 16.2 13.9 16.55 12.5 16.55C11.1 16.55 9.9333 16.2 8.76663 15.5C8.1833 15.2667 7.36663 15.15 6.66663 15.5C4.09997 16.55 2.11663 18.3 1.06663 20.4C0.249968 21.9167 1.5333 23.6667 3.2833 23.6667H21.7166C23.4666 23.6667 24.75 21.9167 23.9333 20.2833Z" fill="#fff"/>
                                    </svg>

                                </span>
                            @else
                                <!-- <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="rounded-circle size-20px"> -->
                                <!-- <i class="fas "></i> -->
                                <span class=" fa-2x  {{ areActiveRoutes(['dashboard'],'opacity-100 text-active mobile-isActive')}}">
                                    <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12.5 14.3335C15.7216 14.3335 18.3333 11.1995 18.3333 7.3335C18.3333 3.4675 15.7216 0.333496 12.5 0.333496C9.2783 0.333496 6.66663 3.4675 6.66663 7.3335C6.66663 11.1995 9.2783 14.3335 12.5 14.3335Z" fill="#fff"/>
                                    <path d="M23.9333 20.2833C22.8833 18.1833 20.9 16.4333 18.3333 15.3833C17.6333 15.15 16.8166 15.15 16.2333 15.5C15.0666 16.2 13.9 16.55 12.5 16.55C11.1 16.55 9.9333 16.2 8.76663 15.5C8.1833 15.2667 7.36663 15.15 6.66663 15.5C4.09997 16.55 2.11663 18.3 1.06663 20.4C0.249968 21.9167 1.5333 23.6667 3.2833 23.6667H21.7166C23.4666 23.6667 24.75 21.9167 23.9333 20.2833Z" fill="#fff"/>
                                    </svg>
                                </span>
                            @endif
                        </span>
                        <span class="d-block fs-10 fw-600 text-white mt-1 {{ areActiveRoutes(['dashboard'],' opacity-100 text-active mobile-isActive')}}" >{{ ('Account') }}</span>
                    </a>
                @endif
            @else
                <a href="{{ route('user.login') }}" class="text-reset d-block text-center pb-1 pt-1">
                    <!-- <span class="d-block mx-auto">
                        <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="rounded-circle size-25px">
                    </span> -->
                    <!-- <i class="fas fa-user fa-2x opacity-60 {{ areActiveRoutes(['dashboard'],'opacity-100 text-active')}}"></i> -->
                    <span class="fa-2x  {{ areActiveRoutes(['dashboard'],'opacity-100 text-active mobile-isActive')}}">
                        <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.5 14.3335C15.7216 14.3335 18.3333 11.1995 18.3333 7.3335C18.3333 3.4675 15.7216 0.333496 12.5 0.333496C9.2783 0.333496 6.66663 3.4675 6.66663 7.3335C6.66663 11.1995 9.2783 14.3335 12.5 14.3335Z" fill="#fff"/>
                            <path d="M23.9333 20.2833C22.8833 18.1833 20.9 16.4333 18.3333 15.3833C17.6333 15.15 16.8166 15.15 16.2333 15.5C15.0666 16.2 13.9 16.55 12.5 16.55C11.1 16.55 9.9333 16.2 8.76663 15.5C8.1833 15.2667 7.36663 15.15 6.66663 15.5C4.09997 16.55 2.11663 18.3 1.06663 20.4C0.249968 21.9167 1.5333 23.6667 3.2833 23.6667H21.7166C23.4666 23.6667 24.75 21.9167 23.9333 20.2833Z" fill="#fff"/>
                        </svg>

                    </span>
                    <span class="d-block fs-10 fw-600 text-white mt-1 {{ areActiveRoutes(['dashboard'],' opacity-100 text-active mobile-isActive')}}" >{{ ('Account') }}</span>
                </a>
            @endif
            </div>
        </div>
    </div>
@endif

@if (Auth::check() && !isAdmin())
    <div class="aiz-mobile-side-nav collapse-sidebar-wrap sidebar-xl d-xl-none z-1035">
        <div class="overlay dark c-pointer overlay-fixed" data-toggle="class-toggle" data-backdrop="static" data-target=".aiz-mobile-side-nav" data-same=".mobile-side-nav-thumb"></div>
        <div class="collapse-sidebar bg-white">
            @include(config('app.theme').'frontend.inc.user_side_nav')
        </div>
    </div>
@endif
