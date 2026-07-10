@php
    $categories = Cache::remember('frontend_categories', now()->addDay(), function() {
        return \App\Models\Category::all();
    });
@endphp

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

@if($sticky == 0)
<!-- Top Bar -->
<div class="top-navbar bg-white border-soft-secondary z-1035 d-none">
    <div class="container custom-container mt-2">
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
                            $unreadNotifications = Auth::user()->unreadNotifications()->latest()->take(10)->get();
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
                            <a href="{{ route('user.registration') }}" class="text-reset d-inline-block opacity-60 py-2">{{ ('Signup')}}</a>
                        </li> --}}
                    @endauth
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- END Top Bar -->
@endif
<header  class="@if($sticky == 1) sticky-top @endif z-1020 bg-white  shadow-sm site-navbar">
    <div class="position-relative logo-bar-area z-1 header-area">
        <div class="container custom-container mt-2">
            <div class="d-flex align-items-center justify-content-between px-3">

                @if ( get_setting('customs_menu_71') !=  null && @intval(json_decode(@get_setting('mega_menu_71')))==1)
                <button class="navbar-toggler menu-tab ml-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="la la-bars text-white"></span>
                </button>
                @endif

                <div class="col-auto col-xl-3 pl-0 pr-lg-3 pr-0 d-flex align-items-center">
                    <a class="d-block py-5px mr-0 mr-md-3 ml-0" href="{{ route('home') }}">
                        @php
                            $header_logo = get_setting('header_logo');
                        @endphp
                        @if($header_logo != null)
                            <img src="{{ uploaded_asset($header_logo) }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-40px" height="40">
                        @else
                            <img src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-40px" height="40">
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
                <div class="d-lg-none ml-lg-auto mr-0">
                    <div class="wishlist-search">
                        <a href="{{ route('flash-deals') }}">
                            <span>
                                <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="1em" height="1em" style="height: 24px; width: 22px;"><path d="M18 7h-.35A3.37 3.37 0 0 0 18 5.5a3.47 3.47 0 0 0-.59-1.95A3.49 3.49 0 0 0 12 3.06a3.48 3.48 0 0 0-5.41.49A3.47 3.47 0 0 0 6 5.5 3.37 3.37 0 0 0 6.35 7H6a3 3 0 0 0-3 3v2a1 1 0 0 0 .29.71A1 1 0 0 0 4 13h1v6a3 3 0 0 0 .88 2.12A3 3 0 0 0 8 22h8a3 3 0 0 0 2.12-.88A3 3 0 0 0 19 19v-6h1a1 1 0 0 0 .71-.29A1 1 0 0 0 21 12v-2a3 3 0 0 0-3-3Zm-7 13H8a1 1 0 0 1-.71-.29A1 1 0 0 1 7 19v-6h4Zm0-9H5v-1a1 1 0 0 1 1-1h5Zm0-4H9.5a1.5 1.5 0 0 1-.83-.25 1.58 1.58 0 0 1-.56-.67 1.51 1.51 0 0 1 1.1-2 1.41 1.41 0 0 1 .86.09 1.47 1.47 0 0 1 .68.55 1.55 1.55 0 0 1 .25.78Zm2-1.5a1.55 1.55 0 0 1 .25-.83 1.47 1.47 0 0 1 .68-.55 1.41 1.41 0 0 1 .86-.12 1.51 1.51 0 0 1 1.1 2.05 1.58 1.58 0 0 1-.56.67 1.5 1.5 0 0 1-.83.28H13ZM17 19a1 1 0 0 1-.29.71A1 1 0 0 1 16 20h-3v-7h4Zm2-8h-6V9h5a1 1 0 0 1 1 1Z" style="fill: rgb(233, 233, 233);"></path>
                                </svg>
                            </span>
                        </a>
                        <!-- <a class=" d-block text-reset" href="javascript:void(0);" data-toggle="class-toggle" data-target=".front-header-search">
                            <i class="las la-search la-flip-horizontal la-2x text-white "></i>
                        </a> -->
                        <a href="{{ route('wishlists.index') }}" class="d-block text-reset position-relative">
                            <!-- <i class="las la-search la-flip-horizontal la-2x text-white"></i> -->
                            <i class="far fa-heart  la-2x text-white fs-22"></i>
                            @if(Auth::check())
                                <span class="badge badge-secondary badge-inline badge-pill">{{ count(Auth::user()->wishlists)}}</span>
                            @else
                                <span class="badge badge-secondary badge-inline badge-pill">0</span>
                            @endif
                        </a>
                        <div class="nav-cart-box dropdown h-100" id="cart_items2">
                            @include(config('app.theme').'frontend.partials.cart')
                        </div>
                    </div>
                </div>


                <div class="flex-grow-1 front-header-search d-flex align-items-center">
                    <div class="position-relative flex-grow-1 d-flex justify-content-center">
                        <form action="{{ route('search') }}" method="GET" class="stop-propagation">
                            <div class="d-flex position-relative align-items-center">
                                <div class="d-lg-none" data-toggle="class-toggle" data-target=".front-header-search">
                                    <button class="btn px-2" type="button"><i class="la la-2x la-long-arrow-left"></i></button>
                                </div>
                                <div class="input-group">
                                    <input type="text" class="border-0 border-lg form-control navbar-input-field" id="search" name="keyword" @isset($query)
                                        value="{{ $query }}"
                                    @endisset placeholder="{{ ('Search in Tekka...')}}" autocomplete="off">
                                    <div class="input-group-append d-none d-lg-block">
                                        <button class="btn btn-primary navbar-search-btn d-flex align-items-center justify-content-center " type="submit">
                                            <i class="la la-search la-flip-horizontal fs-18 mr-1"></i>
                                            Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="typed-search-box stop-propagation document-click-d-none d-none bg-white rounded shadow-lg position-absolute left-0 top-100 w-100" style="min-height: 200px; max-width:80%;">
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





                <!-- <div class="d-none d-lg-block ml-3 mr-0">
                    <div class="" id="compare">
                        @include(config('app.theme').'frontend.partials.compare')
                    </div>
                </div> -->

                <div class="d-none d-lg-block mr-0 text-white navbar-login">
                    @auth
                        <div class="d-flex align-items-center text-center" id="login">
                            <i class="la la-user la-2x"></i>
                            <span class="inline_login">
                                <!-- <div>Welcome!</div> -->
                                @if(isAdmin())
                                    <div>
                                        <a href="{{ route('admin.dashboard') }}" class="text-reset d-inline-block py-2">{{ ('My Panel')}}</a> / <a href="{{ route('logout') }}" class="text-reset d-inline-block py-2">{{ ('Logout')}}</a>
                                    </div>
                                @else
                                <div>
                                    <a href="{{ route('dashboard') }}" class="text-reset d-inline-block py-2">{{ ('My Panel')}}</a> / <a href="{{ route('logout') }}" class="text-reset d-inline-block  py-2">{{ ('Logout')}}</a>
                                </div>
                                @endif
                            </span>
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-center" id="login">
                            <i class="la la-user la-2x"></i>
                            <span class="inline_login">
                                <!-- <div>Welcome! else</div> -->
                                <div>
                                    <a href="{{ route('user.login') }}" class="text-reset d-inline-block py-2">{{ ('Login')}}</a> / <a href="{{ route('user.registration') }}" class="text-reset d-inline-block  py-2">{{ ('Signup')}}</a>
                                </div>
                            </span>
                        </div>
                    @endauth
                </div>


                <div class="d-none d-lg-flex align-items-center  align-self-stretch ml-3 text-white navbar-cart" data-hover="dropdown">
                    <div class="" id="wishlist">
                        @include(config('app.theme').'frontend.partials.wishlist')
                    </div>
                </div>
                <div class="d-none d-lg-block ml-3 mr-0 text-white navbar-wishlist">
                    <div class="nav-cart-box dropdown h-100" id="cart_items">
                        @include(config('app.theme').'frontend.partials.cart')
                    </div>
                </div>



            </div>
        </div>
        @if(Route::currentRouteName() != 'home')
        <div class="hover-category-menu position-absolute w-100 top-100 left-0 right-0 d-none z-3" id="hover-category-menu">
            <div class="container custom-container">
                <div class="row gutters-10 position-relative">
                    <div class="col-lg-3 position-static">
                        @include(config('app.theme').'frontend.partials.category_menu')
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    <div class="d-block d-md-none position-relative mobile-searchbar">
        <form action="{{ route('search') }}" method="GET" class="stop-propagation mb-0" >
            <div class="d-flex position-relative align-items-center">
                <div class="search-icon-mobile" data-toggle="class-toggle" data-target=".front-header-search">
                    <button class="btn px-2" type="button">
                         <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="input-group">
                    <input type="text" class="form-control navbar-input-field" id="search2" name="keyword" @isset($query)
                        value="{{ $query }}"
                    @endisset placeholder="{{ ('Search in Tekka...')}}" autocomplete="off">
                    <div class="input-group-append d-none d-lg-block">
                        <button class="btn btn-primary navbar-search-btn d-flex align-items-center justify-content-center " type="submit">
                            <i class="la la-search la-flip-horizontal fs-18 mr-1"></i>
                            Search
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
            <div id="search-content2" class="text-left">

            </div>
        </div>
    </div>

    @if ( get_setting('customs_menu_71') !=  null && @intval(json_decode(@get_setting('mega_menu_71')))==1)
        <div class="bg-black menu-hide mobile-menu-responsive">
            <div class="container custom-container ">
                <div class="row align-items-center py-3 d-md-none">
                    <a class="col-8 d-flex justify-content-end  " href="{{ route('home') }}">
                        @php
                            $header_logo = get_setting('header_logo');
                        @endphp
                        @if($header_logo != null)
                            <img src="{{ uploaded_asset($header_logo) }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-40px" height="40">
                        @else
                            <img src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}" class="mw-100 h-40px" height="40">
                        @endif
                    </a>
                    <button class="menu-close col-4">
                        <i class="la la-close "></i>
                    </button>
                </div>
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
                    // dd(json_decode($menus, true));
                @endphp


                @if ($menus != '')
                <div>

                    <ul class="list-inline mb-0 pl-0 mobile-hor-swipe text-left main_menu">
                        <li class="list-inline-item px-3 py-2 mr-0 has_sub_menu all-category-hover active">
                            <!-- <span class="la la-plus "></span> -->
                            <div  class="fs-14 d-inline-block fw-600  text-reset hasSubItem"><i class="fas fa-bars mr-2 "></i>
                            All Category
                            <i class="fas fa-chevron-down sub_menu_icon"></i>
                            </div>


                            <div class="hover-category-menu position-absolute w-100 left-0 right-0 z-3" id="hover-category-menu" style = "top: 90%">
                                <div class="container custom-container">
                                    <div class="row gutters-10 position-relative">
                                        <div class="col-lg-3 position-static">
                                            @include(config('app.theme').'frontend.partials.category_menu')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <!-- start category nav -->
                                <div class="megamenu_wrapper row d-md-none shadow-none">
                                    <div class="megamenu_column col bg-md-white">
                                        <ul class="overflow-scroll-behav hide-scrollbar category-list">
                                            @foreach ($categories->where('level', 0)->sortBy('order_level')->take(11) as $key => $category)
                                            @php
                                                $featured_icons = json_decode($category->featured_icon, true);
                                                $featured_icon = $agent->isMobile() ? ($featured_icons['home_page'] ?? '') : ($featured_icons['web'] ?? '');
                                            @endphp
                                            <li class="" data-filter="{{ $category->name }}">
                                                @if(\App\Utility\CategoryUtility::get_immediate_children_count($category->id)>0)
                                                    <img
                                                        class="cat-image lazyload"
                                                        src="{{ uploaded_asset($featured_icon) }}"
                                                        data-src="{{ uploaded_asset($featured_icon) }}" width="16" alt=""
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                    >
                                                    <span class="cat-name text-center">{{ $category->name }}</span>
                                                @else
                                                    <a href = "{{ route('products.category', $category->slug) }}">
                                                        <img
                                                            class="cat-image lazyload"
                                                            src="{{ uploaded_asset($featured_icon) }}"
                                                            data-src="{{ uploaded_asset($featured_icon) }}" width="16" alt=""
                                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                        >
                                                        <span class="cat-name text-center">{{ $category->name }}</span>
                                                    </a>
                                                @endif
                                            </li>
                                            @endforeach
                                        </ul>
                                        <!-- sub category start -->
                                        <div class="sub-category-main" >
                                            @foreach ($categories->where('level', 0)->sortBy('order_level')->take(11) as $key => $category)
                                                @if(\App\Utility\CategoryUtility::get_immediate_children_count($category->id)>0)
                                                    <div class="sub-category-wrapper" data-filter="{{ $category->name }}">
                                                        <div class="sub-category-box">
                                                            @foreach (\App\Utility\CategoryUtility::get_immediate_children_ids($category->id) as $key => $first_level_id)
                                                            @php
                                                                $first_children_uni = cache()->remember('first_uni_immediate_children_ids_'.$first_level_id, 86400, function () use ($first_level_id) {
                                                                    return \App\Models\Category::find($first_level_id);
                                                                });
                                                            @endphp
                                                                <a href="{{ route('products.category', $first_children_uni->slug) }}" class="category-item">
                                                                    <span>{{ $first_children_uni->getTranslation('name') }}</span>
                                                                    <i class="fas fa-arrow-up"></i>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="sub-category-wrapper d-none" style="visibility: hidden; height: 0; padding:0;">
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                        <!-- sub category end -->
                                    </div>
                                </div>

                        <!-- end catergory nav -->
                            </li>

                            @foreach (json_decode($menus, true) as $key => $value)
                            <li class="list-inline-item mr-0 ">
                                    @if(@$value["children"])
                                        @if(@$value["children"] > 0)
                                        <i class="fas fa-chevron-down sub_menu_icon"></i>
                                        @endif
                                    @endif
                                    <a href="{{ url('/').'/'.$value["url"] }}" class="fs-14 px-3 py-3 d-inline-block fw-600 text-reset hasSubItem" style="color: white !important;">
                                        {{ ($value["label"]) }}
                                    </a>
                                    @if(@$value["children"])
                                    @if(@$value["children"] > 0)
                                    <div class="megamenu_wrapper row">
                                        <div class="megamenu_column col ">
                                            <div class="megamenu_list ">
                                                @foreach (@$value["children"] as $key => $value)
                                                <a href="{{ url('/').'/'.$value["url"] }}">
                                                        <h3>{{  translate($value["label"]) }}</h3>
                                                        <i class="fas fa-arrow-up"></i>
                                                </a>
                                                    @if(@$value["children"])
                                                    @if(@$value["children"] > 0)
                                                    <ul>
                                                        @foreach (@$value["children"] as $key => $value)
                                                        <li>
                                                            <a href="{{ url('/').'/'.$value["url"] }}">
                                                                {{ ($value["label"]) }}
                                                                <i class="fas fa-arrow-up"></i>
                                                            </a>
                                                        </li>
                                                        @endforeach

                                                    </ul>
                                                    @endif
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @endif
                                </li>
                            @endforeach
                            <div class="d-md-none download-app-nav">
                                <h4>Download Our App</h4>
                                <div class="download-app-nav-box">
                                    <img
                                        class="cat-image lazyload "
                                        src="{{ static_asset('assets/img/apple.png') }}"
                                        alt="download app"
                                    >
                                    <img
                                        class="cat-image lazyload "
                                        src="{{ static_asset('assets/img/play.png') }}"
                                        alt="download app"
                                    >
                                </div>
                            </div>
                        <div class="bg-white category-sub-menu">
                        <ul class="list-inline mb-0 pl-0 mobile-hor-swipe text-left hover_menu main_menu" id="accordion">
                            @foreach (json_decode($menus, true) as $key => $value)
                            <li class="list-inline-item mr-0 has_sub_menu ">
                                <!-- <span class="la la-plus sub_menu_icon"></span> -->
                                <i class="fas fa-chevron-down sub_menu_icon"></i>
                                <a href="{{ url('/').'/'.$value["url"] }}" class=" fs-16 px-2 py-2 d-inline-block fw-600  text-reset">
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

                   </div>

<div class="category-sub-menu aiz-category-menu bg-white rounded @if(Route::currentRouteName() == 'home') shadow-sm" @else shadow-lg" id="category-sidebar" @endif>
    <ul class="list-unstyled categories no-scrollbar py-1 mb-0 text-left">
        @foreach ($categories->where('level', 0)->sortByDesc('order_level')->take(11) as $key => $category)
            @php
                $icons = json_decode($category->icon, true);
                $icon = $agent->isMobile() ? ($icons['mobile'] ?? '') : ($icons['home_page'] ?? $icons['web'] ?? '');
            @endphp
            <li class="category-nav-element" data-id="{{ $category->id }}">
                <a href="{{ route('products.category', $category->slug) }}" class="text-truncate py-2 px-2 d-block">
                    <img
                        class="cat-image lazyload mr-2 opacity-60"
                        src="{{ uploaded_asset($icon) }}"
                        data-src="{{ uploaded_asset($icon) }}"
                        width="16"
                        alt="{{ $category->name }}"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                    >
                    <span class="cat-name">{{ $category->name }}</span>
                </a>
                @if(\App\Utility\CategoryUtility::get_immediate_children_count($category->id) > 0)
                    <div class="sub-cat-menu c-scrollbar-light rounded shadow-lg p-4">
                        {{-- <div class="c-preloader text-center absolute-center">
                            <i class="las la-spinner la-spin la-3x opacity-70"></i>
                        </div> --}}
                        @include(config('app.theme').'frontend.partials.category_elements',['category' => $category, 'categories' => $categories])
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
<!--
                        <li class="list-inline-item mr-0 has_sub_menu">
                            <span class="la la-plus sub_menu_icon"></span>
                            <a href="" class="opacity-60 fs-16 px-3 py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                                Hot Offers
                            </a>
                        </li>
                        <li class="list-inline-item mr-0 has_sub_menu">
                            <span class="la la-plus sub_menu_icon"></span>
                            <a href="" class="opacity-60 fs-16 px-3 py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                                Brands
                            </a>
                        </li>
                        <li class="list-inline-item mr-0 has_sub_menu">
                            <span class="la la-plus sub_menu_icon"></span>
                            <a href="" class="opacity-60 fs-16 px-3 py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                                Menu Items
                            </a>
                        </li>
                        <li class="list-inline-item mr-0 has_sub_menu">
                            <span class="la la-plus sub_menu_icon"></span>
                            <a href="" class="opacity-60 fs-16 px-3 py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                                My Order
                            </a>
                        </li>
                        <li class="list-inline-item mr-0 has_sub_menu">
                            <span class="la la-plus sub_menu_icon"></span>
                            <a href="" class="opacity-60 fs-16 px-3 py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                                Help <i class="fas fa-angle-down"></i>
                            </a>
                        </li> -->

                    </ul>

                </div>

                @endif
            </div>
        </div>
    @endif



</header>
