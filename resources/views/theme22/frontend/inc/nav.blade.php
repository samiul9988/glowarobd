@if(get_setting('topbar_banner') != null)
<div class="position-relative top-banner removable-session d-none" data-key="top-banner" data-value="removed">
    <a href="{{ get_setting('topbar_banner_link') }}" class="d-block text-reset">
        <img src="{{ uploaded_asset(get_setting('topbar_banner')) }}" class="w-100 mw-100 h-50px h-lg-auto img-fit">
    </a>
    <button class="btn text-white absolute-top-right set-session" data-key="top-banner" data-value="removed" data-toggle="remove-parent" data-parent=".top-banner">
        <i class="la la-close la-2x"></i>
    </button>
</div>
@endif
<!-- Top Bar -->
<div class="top-navbar bg-white border-soft-secondary z-1035">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 col">
                <ul class="list-inline d-flex justify-content-between justify-content-lg-start mb-0">
                    @if(get_setting('show_language_switcher') == 'on')
                    <li class="list-inline-item dropdown mr-3" id="lang-change">
                        @php
                            if(Session::has('locale')){
                                $locale = Session::get('locale', Config::get('app.locale'));
                            }
                            else{
                                $locale = 'en';
                            }
                        @endphp
                        <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2" data-toggle="dropdown" data-display="static">
                            <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ static_asset('assets/img/flags/'.$locale.'.png') }}" class="mr-2 lazyload" alt="{{ $languages[0]['name'] }}" height="11">
                            <span class="opacity-60">{{ $languages[0]['name'] }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-left">
                            @foreach ($languages as $key => $language)
                                <li>
                                    <a href="javascript:void(0)" data-flag="{{ $language['code'] }}" class="dropdown-item @if($locale == $language['code']) active @endif">
                                        <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ static_asset('assets/img/flags/'.$language['code'].'.png') }}" class="mr-1 lazyload" alt="{{ $language['name'] }}" height="11">
                                        <span class="language">{{ $language['name'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endif

                    @if(get_setting('show_currency_switcher') == 'on')
                    <li class="list-inline-item dropdown ml-auto ml-lg-0 mr-0" id="currency-change">
                        @php
                            if(Session::has('currency_code')){
                                $currency_code = Session::get('currency_code');
                            }
                            else{
                                $currency_code = $currencies->where('status', 1)->first()['code'];
                            }
                        @endphp
                        <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2 opacity-60" data-toggle="dropdown" data-display="static">
                            {{ $currencies->where('status', 1)->first()['name'] }} {{ ($currencies->where('status', 1)->first()['symbol']) }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left">
                            @foreach ($currencies->where('status', 1) as $key => $currency)
                                <li>
                                    <a class="dropdown-item @if($currency_code == $currency['code']) active @endif" href="javascript:void(0)" data-currency="{{ $currency['code'] }}">{{ $currency['name'] }} ({{ $currency['symbol'] }})</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endif
                </ul>
            </div>

            <div class="col-5 text-right d-none d-lg-block">
                <ul class="list-inline mb-0 h-100 d-flex justify-content-end align-items-center">
                    <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                        <a href="tel:{{ get_setting('helpline_number') }}" class="text-reset d-inline-block opacity-60 py-2">
                            <i class="la la-phone"></i>
                            <span>{{ ('Help line')}}</span>
                            <span>{{ get_setting('helpline_number') }}</span>
                        </a>
                    </li>
                    @auth
                        @php
                            // $unreadNotifications = Auth::user()->unreadNotifications()->latest()->take(10)->get();
                            $unreadNotifications = [];
                        @endphp
                        @if(isAdmin())
                            {{-- <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                                <a href="{{ route('admin.dashboard') }}" class="text-reset d-inline-block opacity-60 py-2">{{ ('My Panel')}}</a>
                            </li> --}}
                        @else

                            <li class="list-inline-item mr-0 border-right-0 border-left-0 pr-0 pl-0 dropdown">
                                <a class="dropdown-toggle no-arrow text-reset" data-toggle="dropdown" href="javascript:void(0);" role="button" aria-haspopup="false" aria-expanded="false">
                                    <span class="">
                                        <span class="position-relative d-inline-block">
                                            <i class="las la-bell fs-18"></i>
                                            @if(count($unreadNotifications) > 0)
                                                <span class="badge badge-sm badge-dot badge-circle badge-secondary position-absolute absolute-top-right"></span>
                                            @endif
                                        </span>
                                    </span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg py-0">
                                    <div class="p-3 bg-light border-bottom">
                                        <h6 class="mb-0">{{ ('Notifications') }}</h6>
                                    </div>
                                    <div class="px-3 c-scrollbar-light overflow-auto " style="max-height:300px;">
                                        <ul class="list-group list-group-flush" >
                                            @forelse($unreadNotifications as $notification)
                                                <li class="list-group-item">
                                                    @if($notification->type == 'App\Notifications\OrderNotification')
                                                        @if(Auth::user()->user_type == 'customer')
                                                        <a href="javascript:void(0)" onclick="show_purchase_history_details({{ $notification->data['order_id'] }})" class="text-reset">
                                                            <span class="ml-2">
                                                                {{ ('Order code: ')}} <span class="text-secondary">{{$notification->data['order_code']}} </span> {{ ('has been '. ucfirst(str_replace('_', ' ', $notification->data['status'])))}}
                                                            </span>
                                                        </a>
                                                        @elseif (Auth::user()->user_type == 'seller')
                                                            @if(Auth::user()->id == $notification->data['user_id'])
                                                                <a href="javascript:void(0)" onclick="show_purchase_history_details({{ $notification->data['order_id'] }})" class="text-reset">
                                                                    <span class="ml-2">
                                                                        {{ ('Order code: ')}} <span class="text-secondary">{{$notification->data['order_code']}} </span> {{ ('has been '. ucfirst(str_replace('_', ' ', $notification->data['status'])))}}
                                                                    </span>
                                                                </a>
                                                            @else
                                                                <a href="javascript:void(0)" onclick="show_order_details({{ $notification->data['order_id'] }})" class="text-reset">
                                                                    <span class="ml-2">
                                                                        {{ ('Order code: ')}} <span class="text-secondary">{{$notification->data['order_code']}} </span> {{ ('has been '. ucfirst(str_replace('_', ' ', $notification->data['status'])))}}
                                                                    </span>
                                                                </a>
                                                            @endif
                                                        @endif
                                                    @endif
                                                </li>
                                            @empty
                                                <li class="list-group-item">
                                                    <div class="py-4 text-center fs-16">
                                                        {{ ('No notification found') }}
                                                    </div>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                    <div class="text-center border-top">
                                        <a href="{{ route('all-notifications') }}" class="text-reset d-block py-2">
                                            {{ ('View All Notifications')}}
                                        </a>
                                    </div>
                                </div>
                            </li>

                            {{-- <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                                <a href="{{ route('dashboard') }}" class="text-reset d-inline-block opacity-60 py-2">{{ ('My Panel')}}</a>
                            </li> --}}
                        @endif
                        {{-- <li class="list-inline-item">
                            <a href="{{ route('logout') }}" class="text-reset d-inline-block opacity-60 py-2">{{ ('Logout')}}</a>
                        </li> --}}
                    @else
                        {{-- <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                            <a href="{{ route('user.login') }}" class="text-reset d-inline-block opacity-60 py-2">{{ ('Login')}}</a>
                        </li>
                        <li class="list-inline-item">
                            <a href="{{ route('user.registration') }}" class="text-reset d-inline-block opacity-60 py-2">{{ ('Registration')}}</a>
                        </li> --}}
                    @endauth
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- END Top Bar -->
<header class="@if(get_setting('header_stikcy') == 'on') sticky-top @endif z-1020 bg-white border-bottom shadow-sm">
    <div class="position-relative logo-bar-area z-1">
        <div class="container">
            <div class="d-flex align-items-center">

                @if ( get_setting('customs_menu_71') !=  null && @intval(json_decode(@get_setting('mega_menu_71')))==1)
                <button class="navbar-toggler menu-tab" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="la la-bars"></span>
                </button>
                @endif

                <div class="col-auto col-xl-3 pl-0 pr-3 d-flex align-items-center">
                    <a class="d-block py-5px mr-3 ml-0" href="{{ route('home') }}">
                        @php
                            $header_logo = get_setting('header_logo');
                        @endphp
                        @if($header_logo != null)
                            <img src="{{ uploaded_asset($header_logo) }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-40px h-md-40px" height="40">
                        @else
                            <img src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-40px h-md-40px" height="40">
                        @endif
                    </a>

                    @if(@intval(json_decode(@get_setting('left_category_71')))==1)
                    @if(Route::currentRouteName() != 'home')
                        <div class="d-none d-xl-block align-self-stretch category-menu-icon-box ml-auto mr-0">
                            <div class="h-100 d-flex align-items-center" id="category-menu-icon">
                                <div class="dropdown-toggle navbar-light bg-light h-40px w-50px pl-0 rounded border c-pointer">
                                    <span class="navbar-toggler-icon mx-auto"></span>
                                </div>
                            </div>
                        </div>
                    @endif
                    @endif

                </div>
                <div class="d-lg-none ml-auto mr-0">
                    <a class="p-2 d-block text-reset" href="javascript:void(0);" data-toggle="class-toggle" data-target=".front-header-search">
                        <i class="las la-search la-flip-horizontal la-2x"></i>
                    </a>
                </div>

                <div class="flex-grow-1 front-header-search d-flex align-items-center">
                    <div class="position-relative flex-grow-1 d-flex justify-content-center">
                        <form action="{{ route('search') }}" method="GET" class="stop-propagation">
                            <div class="d-flex position-relative align-items-center">
                                <div class="d-lg-none" data-toggle="class-toggle" data-target=".front-header-search">
                                    <button class="btn px-2" type="button"><i class="la la-2x la-long-arrow-left"></i></button>
                                </div>
                                <div class="input-group">
                                    <input type="text" class="border-0 border-lg form-control" id="search" name="keyword" @isset($query)
                                        value="{{ $query }}"
                                    @endisset placeholder="{{ ('I am shopping for...')}}" autocomplete="off">
                                    <div class="input-group-append d-none d-lg-block">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="la la-search la-flip-horizontal fs-18"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="typed-search-box stop-propagation document-click-d-none d-none bg-white rounded shadow-lg position-absolute left-0 top-100 w-100" style="min-height: 200px">
                            <div class="search-preloader absolute-top-center">
                                <div class="dot-loader"><div></div><div></div><div></div></div>
                            </div>
                            <div class="search-nothing d-none p-3 text-center fs-16">

                            </div>
                            <div id="search-content" class="text-left">

                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-none d-lg-none ml-3 mr-0">
                    <div class="nav-search-box">
                        <a href="#" class="nav-box-link">
                            <i class="la la-search la-flip-horizontal d-inline-block nav-box-icon"></i>
                        </a>
                    </div>
                </div>


                <div class="d-none d-lg-block ml-3 mr-0">
                    <div id="auth-status">
                        @include(config('app.theme').'frontend.partials.auth_status')
                    </div>
                </div>


                <div class="d-none d-lg-block ml-3 mr-0">
                    <div class="" id="compare">
                        @include(config('app.theme').'frontend.partials.compare')
                    </div>
                </div>

                <div class="d-none d-lg-block ml-3 mr-0">
                    <div class="" id="wishlist">
                        @include(config('app.theme').'frontend.partials.wishlist')
                    </div>
                </div>

                <div class="d-none d-lg-block  align-self-stretch ml-3 mr-0" data-hover="dropdown">
                    <div class="nav-cart-box dropdown h-100" id="cart_items">
                        @include(config('app.theme').'frontend.partials.cart')
                    </div>
                </div>

            </div>
        </div>
        @if(Route::currentRouteName() != 'home')
        <div class="hover-category-menu position-absolute w-100 top-100 left-0 right-0 d-none z-3" id="hover-category-menu">
            <div class="container">
                <div class="row gutters-10 position-relative">
                    <div class="col-lg-3 position-static">
                        @include(config('app.theme').'frontend.partials.category_menu')
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if ( get_setting('customs_menu_71') !=  null && @intval(json_decode(@get_setting('mega_menu_71')))==1)
        <div class="bg-white border-top border-gray-200 py-1 menu-hide">
            <div class="container">
                <button class="menu-close"><i class="la la-close la-2x"></i></button>
                {{-- <ul class="list-inline mb-0 pl-0 mobile-hor-swipe text-center">
                    @foreach (json_decode( get_setting('header_menu_labels'), true) as $key => $value)
                    <li class="list-inline-item mr-0">
                        <a href="{{ json_decode( get_setting('header_menu_links'), true)[$key] }}" class="opacity-60 fs-14 px-3 py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                            {{ ($value) }}
                        </a>
                    </li>
                    @endforeach
                </ul> --}}
                @php
                    $menus = '';
                    $menusdata = get_setting('customs_menu_71');
                    if($menusdata && !empty($menusdata)){
                        $menus = $menusdata;
                    }
                @endphp
                @if ($menus != '')
                <ul class="list-inline mb-0 pl-0 mobile-hor-swipe text-center main_menu" id="accordion">
                    @foreach (json_decode($menus, true) as $key => $value)
                    <li class="list-inline-item mr-0 has_sub_menu">
                        <span class="la la-plus sub_menu_icon"></span>
                        <a href="{{ url('/').'/'.$value["url"] }}" class="opacity-60 fs-14 px-3 py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                            {{ ($value["label"]) }}
                        </a>
                        @if(@$value["children"])
                        @if(@$value["children"] > 0)
                        <div class="megamenu_wrapper row">
                            <div class="megamenu_column col">
                                @foreach (@$value["children"] as $key => $value)
                                <div class="megamenu_list">
                                    <a href="{{ url('/').'/'.$value["url"] }}"><h3>{{  translate($value["label"]) }}</h3></a>
                                    @if(@$value["children"])
                                    @if(@$value["children"] > 0)
                                    <ul>
                                        @foreach (@$value["children"] as $key => $value)
                                        <li><a href="{{ url('/').'/'.$value["url"] }}">{{ ($value["label"]) }}</a></li>
                                        @endforeach
                                    </ul>
                                    @endif
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @endif
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    @endif
</header>
