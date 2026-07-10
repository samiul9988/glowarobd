@php
    $isAdmin = Auth::check() && Auth::user()->user_type == 'admin';
@endphp
<div class="aiz-sidebar-wrap">
    <div class="aiz-sidebar left c-scrollbar">
        <div class="aiz-side-nav-logo-wrap">
            <a href="{{ route('admin.dashboard') }}" class="d-block text-left">
                @if(get_setting('system_logo_white') != null)
                    <img class="mw-100" src="{{ uploaded_asset(get_setting('system_logo_white')) }}" class="brand-icon" alt="{{ get_setting('site_name') }}">
                @else
                    <img class="mw-100" src="{{ static_asset('assets/img/logo.png') }}" class="brand-icon" alt="{{ get_setting('site_name') }}">
                @endif
            </a>
        </div>
        <div class="aiz-side-nav-wrap">
            <div class="px-20px mb-3">
                <input class="form-control bg-soft-secondary border-0 form-control-sm text-white" type="text" name="" placeholder="{{ ('Search in menu') }}" id="menu-search" onkeyup="menuSearch()">
            </div>
            <ul class="aiz-side-nav-list" id="search-menu">
            </ul>
            <ul class="aiz-side-nav-list" id="main-menu" data-toggle="aiz-side-menu">

                {{-- dashboard --}}
                <li class="aiz-side-nav-item">
                    <a href="{{route('admin.dashboard')}}" class="aiz-side-nav-link">
                        <i class="las la-home aiz-side-nav-icon"></i>
                        <span class="aiz-side-nav-text">{{ ('Dashboard')}}</span>
                    </a>
                </li>

                <!-- POS Addon-->
                @if (addon_is_activated('pos_system'))
                    @if($isAdmin || in_array('1', $_authPermissions))
                        <li class="aiz-side-nav-item">
                            <a href="{{route('poin-of-sales.index')}}" class="aiz-side-nav-link">
                                <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h2a2 2 0 1 0 0-4H3v8m9-8a2 2 0 0 1 2 2v4a2 2 0 1 1-4 0v-4a2 2 0 0 1 2-2m5 7a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1h-2a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1"/></svg>
                                <span class="aiz-side-nav-text">{{ ('POS System')}}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <span class="badge badge-inline badge-danger">Addon</span>
                                @endif
                                {{-- <span class="aiz-side-nav-arrow"></span> --}}
                            </a>

                            {{-- <ul class="aiz-side-nav-list level-2">
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('poin-of-sales.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['poin-of-sales.index', 'poin-of-sales.create'])}}">
                                        <span class="aiz-side-nav-text">{{ ('POS Manager')}}</span>
                                    </a>
                                </li>
                                @if(config('app.name')=='ECOM71')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('poin-of-sales.activation')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('POS Configuration')}}</span>
                                    </a>
                                </li>
                                @endif
                            </ul> --}}

                        </li>
                    @endif
                @endif

                @if (get_setting('enable_crm_module') == 1)
                    @if($isAdmin || any_in_array(['manage_crm', 'crm_customers', 'manage_modules', 'manage_module_waitlist'], $_authPermissions))
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><path d="M3.778 11.942C2.83 10.29 2.372 8.94 2.096 7.572c-.408-2.024.526-4.001 2.073-5.263c.654-.533 1.404-.35 1.791.343l.873 1.567c.692 1.242 1.038 1.862.97 2.52c-.069.659-.536 1.195-1.469 2.267zm0 0c1.919 3.346 4.93 6.36 8.28 8.28m0 0c1.653.948 3.002 1.406 4.37 1.682c2.024.408 4.001-.526 5.262-2.073c.534-.654.351-1.404-.342-1.791l-1.567-.873c-1.242-.692-1.862-1.038-2.52-.97c-.659.069-1.195.536-2.267 1.469zM12 7h.857c.404 0 .606 0 .732.122c.125.122.125.319.125.711c0 .786 0 1.179-.25 1.423c-.192.186-.471.23-.95.24c-.245.006-.367.009-.44.082S12 9.767 12 10v1.167c0 .393 0 .589.126.711c.125.122.327.122.731.122h.857M18 7v2.5m0 0h-1.457c-.323 0-.485 0-.585-.098c-.1-.097-.1-.254-.1-.569V7M18 9.5V12"/><path d="M10 4.305q.133-.15.277-.294A6.867 6.867 0 1 1 19.695 14"/></g></svg>
                                <span class="aiz-side-nav-text">{{ ('CRM')}}</span>
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                @if (Module::isEnabled('Waitlist') && ($isAdmin || in_array('manage_module_waitlist', $_authPermissions)))
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('admin.waitlists.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">Waitlist</span>
                                        </a>
                                    </li>
                                @endif
                                @if($isAdmin || in_array('crm_customers', $_authPermissions))
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('customers.filtered') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Filter Customer') }}</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif

                <!-- Accounting Addon -->
                @if (addon_is_activated('accounts_system'))
                    @if($isAdmin || in_array('26', $_authPermissions))
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M13 6.25a1 1 0 1 1-2 0a1 1 0 0 1 2 0m.032-3.925a1.75 1.75 0 0 0-2.064 0L3.547 7.74c-.978.713-.473 2.26.736 2.26H4.5v5.8A2.75 2.75 0 0 0 3 18.25v1.5c0 .413.336.75.75.75h7.416a4.8 4.8 0 0 1-.16-1.5H4.5v-.75c0-.691.56-1.25 1.25-1.25h5.816a4.8 4.8 0 0 1 1.268-1.5h-.084V10h1.75v4.666a4.8 4.8 0 0 1 1.25-.167H16V10h2v4.5h1.25q.126 0 .25.007V10h.217c1.21 0 1.714-1.546.736-2.26zm-1.18 1.211a.25.25 0 0 1 .295 0L18.95 8.5H5.05zM11.25 10v5.5H9.5V10zM6 15.5V10h2v5.5zm17 3.75a3.75 3.75 0 0 0-3.75-3.75l-.102.007A.75.75 0 0 0 19.25 17l.154.006a2.25 2.25 0 0 1-.154 4.494l-.003.005l-.102.007a.75.75 0 0 0 .108 1.493V23l.2-.005A3.75 3.75 0 0 0 23 19.25m-6.5-3a.75.75 0 0 0-.75-.75l-.2.005a3.75 3.75 0 0 0 .2 7.495l.102-.006a.75.75 0 0 0-.102-1.494l-.154-.005A2.25 2.25 0 0 1 15.75 17l.102-.006a.75.75 0 0 0 .648-.744m3.5 3a.75.75 0 0 0-.75-.75h-3.5l-.102.007A.75.75 0 0 0 15.75 20h3.5l.102-.006A.75.75 0 0 0 20 19.25"/></svg>
                                <span class="aiz-side-nav-text">{{ ('Accounts')}}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <span class="badge badge-inline badge-danger">Addon</span>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('heads.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Heads')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('banks.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Banks')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('accounts.payments.create') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Pay Bill')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('accounts.voucher.create') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Voucher Entry')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('accounts.vouchers.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Voucher Entries')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('accounts.reports.ledger') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Account/Ledger Report')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('accounts.reports.sub_head_ledger') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Sub Head Account/Ledger Report')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('accounts.reports.trial_balance') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Trial Balance')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('accounts.reports.daily_report') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Daily Report')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endif

                <!-- Order Management -->
                @if($isAdmin || any_in_array(['3', 'pending_orders', 'processing_orders', 'hold_orders', 'confirmed_orders', 'packaging_orders', 'picked_up_orders', 'on_the_way_orders', 'delivered_orders', 'returned_orders', 'cancelled_orders', 'manage_service'], $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M2 20q-.425 0-.712-.288T1 19t.288-.712T2 18h4v-2H3q-.425 0-.712-.288T2 15t.288-.712T3 14h3v-2H4.05q-.425 0-.712-.288T3.05 11t.288-.712T4.05 10H6V7.05l-1.525-3.3q-.175-.375-.038-.762t.513-.563t.763-.037t.562.512L8.2 7h11.6l-1.525-3.25q-.175-.375-.037-.762t.512-.563t.763-.037t.562.512L21.8 6.6q.1.2.15.413t.05.437V18q0 .825-.587 1.413T20 20zm10-7h4q.425 0 .713-.288T17 12t-.288-.712T16 11h-4q-.425 0-.712.288T11 12t.288.713T12 13m-4 5h12V9H8zm0 0V9z"/></svg>
                            <span class="aiz-side-nav-text">{{ ('Order Management')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            @if($isAdmin || any_in_array(['3', 'pending_orders', 'processing_orders', 'hold_orders', 'confirmed_orders', 'packaging_orders', 'picked_up_orders', 'on_the_way_orders', 'delivered_orders', 'returned_orders', 'cancelled_orders', 'manage_service'], $_authPermissions))
                                @php
                                    if(in_array(3, $_authPermissions)){
                                        $route = route('all_orders.index');
                                    }elseif(in_array('pending_orders', $_authPermissions)){
                                        $route = route('all_orders.status', 'pending');
                                    }elseif(in_array('processing_orders', $_authPermissions)){
                                        $route = route('all_orders.status', 'processing');
                                    }elseif(in_array('hold_orders', $_authPermissions)){
                                        $route = route('all_orders.status', 'hold');
                                    }elseif(in_array('confirmed_orders', $_authPermissions)){
                                        $route = route('all_orders.status', 'confirmed');
                                    }elseif(in_array('packaging_orders', $_authPermissions)){
                                        $route = route('all_orders.status', 'packaging');
                                    }elseif(in_array('picked_up_orders', $_authPermissions)){
                                        $route = route('all_orders.status', 'picked_up');
                                    }elseif(in_array('on_the_way_orders', $_authPermissions)){
                                        $route = route('all_orders.status', 'on_the_way');
                                    }elseif(in_array('delivered_orders', $_authPermissions)){
                                        $route = route('all_orders.status', 'delivered');
                                    }elseif(in_array('returned_orders', $_authPermissions)){
                                        $route = route('all_orders.status', 'returned');
                                    }elseif(in_array('cancelled_orders', $_authPermissions)){
                                        $route = route('all_orders.status', 'cancelled');
                                    }else{
                                        $route = route('all_orders.index');
                                    }
                                @endphp
                                <li class="aiz-side-nav-item">
                                    <a href="{{ $route }}" class="aiz-side-nav-link {{ areActiveRoutes(['all_orders.index', 'all_orders.show'])}}">
                                        <span class="aiz-side-nav-text">{{ ('All Orders')}}</span>
                                    </a>
                                </li>
                            @endif

                            @if(@intval(json_decode(@get_setting('vendor_system_activation')))==1)
                                @if($isAdmin || in_array('5', $_authPermissions))
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('seller_orders.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['seller_orders.index', 'seller_orders.show'])}}">
                                            <span class="aiz-side-nav-text">{{ ('Seller Orders')}}</span>
                                        </a>
                                    </li>
                                @endif
                            @endif

                            @if(@get_setting('automated_pathao_shipping') == 1)
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('orders.shipping.process') }}" class="aiz-side-nav-link {{ areActiveRoutes(['orders.shipping.process'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Process To Ship')}}</span>
                                    </a>
                                </li>
                            @endif

                            {{-- @if($isAdmin || in_array('6', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('pick_up_point.order_index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['pick_up_point.order_index','pick_up_point.order_show'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Pickup Point Order')}}</span>
                                    </a>
                                </li>
                            @endif --}}

                            {{-- Refund Requests --}}
                            @if($isAdmin || in_array('22', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('refund_request.all') }}" class="aiz-side-nav-link {{ areActiveRoutes(['refund_request.all'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Refund Requests') }}</span>
                                    </a>
                                </li>
                            @endif

                            @if($isAdmin)
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('reviewcomments.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['reviewcomments.index'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Review Comments')}}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('manage_order_return_request', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('return-orders.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['return-orders.index', 'return-orders.show'])}}">
                                        <span class="aiz-side-nav-text">{{ ('All Return Request')}}</span>
                                    </a>
                                </li>
                            @endif
                            {{-- @if($isAdmin || in_array('manage_service', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('services.manage') }}" class="aiz-side-nav-link {{ areActiveRoutes(['services.manage', 'tickets.create'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Manage Service')}}</span>
                                    </a>
                                </li>
                            @endif --}}

                            {{-- @if($isAdmin || in_array('7', $_authPermissions))
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('upcoming.delivery') }}" class="aiz-side-nav-link {{ areActiveRoutes(['upcoming.delivery', 'upcoming.delivery'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Upcoming Delivery')}}</span>
                                </a>
                            </li>
                            @endif

                            @if($isAdmin || in_array('4', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('inhouse_orders.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['inhouse_orders.index', 'inhouse_orders.show'])}}" >
                                        <span class="aiz-side-nav-text">{{ ('Inhouse orders')}}</span>
                                    </a>
                                </li>
                            @endif --}}


                        </ul>
                    </li>
                @endif

                <!-- Product Management -->
                @if($isAdmin || any_in_array(['2', 'brands', 'categories', 'attributes', 'colors', 'product_reviews', 'product_custom_fields'], $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-shopping-cart aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Product Management')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            @if($isAdmin || in_array('2', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a class="aiz-side-nav-link" href="{{route('products.create')}}">
                                        <span class="aiz-side-nav-text">{{ ('Add New product')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('products.all')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('All Products') }}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('all_products.stock_out')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">All Stock Out Products</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('merchant_products', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('merchant_products.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Merchant Products') }}</span>
                                    </a>
                                </li>
                            @endif

                            {{--<li class="aiz-side-nav-item">
                                <a href="{{route('products.admin')}}" class="aiz-side-nav-link {{ areActiveRoutes(['products.admin', 'products.create', 'products.admin.edit']) }}" >
                                    <span class="aiz-side-nav-text">{{ ('In House Products') }}</span>
                                </a>
                            </li>--}}
                            @if($isAdmin || in_array('categories', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('categories.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['categories.index', 'categories.create', 'categories.edit'])}}" >
                                        <span class="aiz-side-nav-text">{{ ('Categories')}}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('brands', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('brands.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['brands.index', 'brands.create', 'brands.edit'])}}" >
                                        <span class="aiz-side-nav-text">{{ ('Brand')}}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('attributes', $_authPermissions))
                            <li class="aiz-side-nav-item">
                                <a href="{{route('attributes.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['attributes.index','attributes.create','attributes.edit'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Attribute')}}</span>
                                </a>
                            </li>
                            @endif
                            @if($isAdmin || in_array('colors', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('colors')}}" class="aiz-side-nav-link {{ areActiveRoutes(['attributes.index','attributes.create','attributes.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Colors')}}</span>
                                    </a>
                                </li>
                            @endif
                            {{-- @if($isAdmin || in_array('product_reviews', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('reviews.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Product Reviews')}}</span>
                                    </a>
                                </li>
                            @endif --}}
                            @if($isAdmin || in_array('product_custom_fields', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('products.custom_fields.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Product Custom Fields')}}</span>
                                    </a>
                                </li>
                            @endif


                            {{-- @if(get_setting('vendor_system_activation') == 1)
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('products.seller')}}" class="aiz-side-nav-link {{ areActiveRoutes(['products.seller', 'products.seller.edit']) }}">
                                        <span class="aiz-side-nav-text">{{ ('Seller Products') }}</span>
                                    </a>
                                </li>
                            @endif
                            <li class="aiz-side-nav-item">
                                <a href="{{route('digitalproducts.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['digitalproducts.index', 'digitalproducts.create', 'digitalproducts.edit']) }}">
                                    <span class="aiz-side-nav-text">{{ ('Digital Products') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('product_bulk_upload.index') }}" class="aiz-side-nav-link" >
                                    <span class="aiz-side-nav-text">{{ ('Bulk Import') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{route('product_bulk_export.index')}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Bulk Export')}}</span>
                                </a>
                            </li> --}}
                        </ul>
                    </li>
                @endif

                {{-- Reviews Management --}}
                @if($isAdmin || in_array('product_reviews', $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 32 32"><path fill="currentColor" d="M18 26h8v2h-8zm0-4h12v2H18zm0-4h12v2H18z"/><path fill="currentColor" d="M20.549 11.217L16 2l-4.549 9.217L1.28 12.695l7.36 7.175L6.902 30L14 26.269v-2.26l-4.441 2.335l1.052-6.136l.178-1.037l-.753-.733l-4.458-4.347l6.161-.895l1.04-.151l.466-.943L16 6.519l2.755 5.583l.466.943l1.04.151l7.454 1.085L28 12.3z"/></svg>
                            <span class="aiz-side-nav-text">{{ ('Reviews Management') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('reviews.create') }}" class="aiz-side-nav-link {{ areActiveRoutes(['reviews.create'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Create Review') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('reviews.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['reviews.index', 'reviews.show', 'reviews.edit'])}}">
                                    <span class="aiz-side-nav-text">{{ ('All Reviews') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif


                {{-- Videos Management --}}
                @if($isAdmin || in_array('manage_videos', $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="m11.5 14.5l7-4.5l-7-4.5zM8 18q-.825 0-1.412-.587T6 16V4q0-.825.588-1.412T8 2h12q.825 0 1.413.588T22 4v12q0 .825-.587 1.413T20 18zm0-2h12V4H8zm-4 6q-.825 0-1.412-.587T2 20V6h2v14h14v2zM8 4v12z"/></svg>
                            <span class="aiz-side-nav-text">{{ ('Videos Management') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('video-playlists.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['video-playlists.index', 'video-playlists.edit'])}}">
                                    <span class="aiz-side-nav-text">All Categories</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('videos.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['videos.index','videos.create', 'videos.edit'])}}">
                                    <span class="aiz-side-nav-text">All Videos</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif


                {{-- Meta Object Management --}}
                @if($isAdmin || in_array('27', $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M15 20q-.425 0-.712-.288T14 19t.288-.712T15 18h2q.425 0 .713-.288T18 17v-2q0-.95.55-1.725t1.45-1.1v-.35q-.9-.325-1.45-1.1T18 9V7q0-.425-.288-.712T17 6h-2q-.425 0-.712-.288T14 5t.288-.712T15 4h2q1.25 0 2.125.875T20 7v2q0 .425.288.713T21 10t.713.288T22 11v2q0 .425-.288.713T21 14t-.712.288T20 15v2q0 1.25-.875 2.125T17 20zm-8 0q-1.25 0-2.125-.875T4 17v-2q0-.425-.288-.712T3 14t-.712-.288T2 13v-2q0-.425.288-.712T3 10t.713-.288T4 9V7q0-1.25.875-2.125T7 4h2q.425 0 .713.288T10 5t-.288.713T9 6H7q-.425 0-.712.288T6 7v2q0 .95-.55 1.725T4 11.825v.35q.9.325 1.45 1.1T6 15v2q0 .425.288.713T7 18h2q.425 0 .713.288T10 19t-.288.713T9 20z"/></svg>
                            <span class="aiz-side-nav-text">{{ ('Meta Object')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a class="aiz-side-nav-link" href="{{route('meta-objects.index')}}">
                                    <span class="aiz-side-nav-text">{{ ('All Meta Objects')}}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{route('meta-object-items.index')}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Meta Object Items') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Inventory Management --}}
                @if($isAdmin || any_in_array(['25', 'stock_adjust', 'stock_report', 'suppliers'], $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M5.06 3c-.43 0-.84.14-1.22.42s-.6.64-.7 1.08L2.11 8.91c-.25 1.09-.05 2.04.61 2.86l.28.28V19c0 .5.2 1 .61 1.39S4.5 21 5 21h6v-1.89l.11-.11H5v-6h.25c.91 0 1.64-.33 2.2-.95c.63.62 1.41.95 2.35.95c.84 0 1.58-.33 2.2-.95c.69.62 1.45.95 2.3.95c.87 0 1.62-.33 2.25-.95c.25.28.54.49.86.65l1.65-1.65l.01.01l.13-.14c-.14.05-.29.08-.45.08c-.31 0-.58-.1-.8-.34c-.22-.23-.34-.5-.37-.82L16.97 5l1.92-.03l1.08 4.41c.09.41.03.78-.24 1.12c.67-.4 1.5-.47 2.22-.19c.05-.44.05-.9-.06-1.4L20.86 4.5c-.13-.44-.36-.8-.73-1.08A1.88 1.88 0 0 0 18.94 3M5.06 5h1.97l-.61 4.84C6.3 10.63 5.91 11 5.25 11c-.41 0-.72-.14-.94-.45c-.28-.35-.37-.74-.28-1.17M9.05 5H11v4.7c0 .35-.11.65-.36.92c-.25.26-.56.38-.94.38c-.34 0-.63-.12-.86-.41S8.5 10 8.5 9.66V9.5M13 5h1.95l.55 4.5c.08.42 0 .77-.29 1.07c-.26.3-.6.43-1.01.43c-.31 0-.59-.12-.84-.38A1.3 1.3 0 0 1 13 9.7M15.06 22H13v-2.06l6.06-6.06l2.05 2.05m-.46-3.63c.1-.1.22-.15.35-.16c.15 0 .31.05.42.16l1.28 1.28c.21.21.21.56 0 .77l-1 1l-2.05-2.05Z"/></svg>
                            <span class="aiz-side-nav-text">{{ ('Inventory Management')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            @if($isAdmin || in_array('25', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a class="aiz-side-nav-link" href="{{route('purchaseorder.create')}}">
                                        <span class="aiz-side-nav-text">{{ ('Add New Purchase Order')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('purchaseorder.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Manage Purchases') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('stock_adjust', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a class="aiz-side-nav-link" href="{{route('stock-adjust.create')}}">
                                        <span class="aiz-side-nav-text">{{ ('Add New Stock Adjust')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('stock-adjust.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('All Adjusted Stock Order') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('return_supplier', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a class="aiz-side-nav-link" href="{{route('stock-adjust.return_supplier.create')}}">
                                        <span class="aiz-side-nav-text">Product Return To Supplier</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('stock-adjust.return_supplier.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['stock-adjust.return_supplier.index', 'stock-adjust.return_supplier.show'])}}">
                                        <span class="aiz-side-nav-text">All Returned Products</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('stock_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('products.stock.new') }}" class="aiz-side-nav-link {{ areActiveRoutes(['product.stock.new'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Products Stock Report') }}</span>
                                    </a>
                                </li>
                                {{-- <li class="aiz-side-nav-item">
                                    <a href="{{ route('products.stock.latest') }}" class="aiz-side-nav-link {{ areActiveRoutes(['product.stock.latest'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Products Stock Report New') }}</span>
                                    </a>
                                </li> --}}
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('brand.stock.report') }}" class="aiz-side-nav-link {{ areActiveRoutes(['brand.stock.report'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Stock Report By Brand') }}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('reports.stock.product') }}" class="aiz-side-nav-link {{ areActiveRoutes(['reports.stock.product'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Stock Report By Product') }}</span>
                                    </a>
                                </li>
                                {{-- <li class="aiz-side-nav-item">
                                    <a href="{{ route('reports.stock.product.new') }}" class="aiz-side-nav-link {{ areActiveRoutes(['reports.stock.product.new'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Stock Report By Product New') }}</span>
                                    </a>
                                </li> --}}
                            @endif
                            @if($isAdmin || in_array('suppliers', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('supplier.create')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Add Supplier') }}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('supplier.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Supplier list') }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <!-- Offer Management -->
                @if($isAdmin || in_array('11', $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-bullhorn aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Offer Management') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @if($isAdmin || in_array('2', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('flash_deals.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['flash_deals.index', 'flash_deals.create', 'flash_deals.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Flash Deals') }}</span>
                                    </a>
                                </li>
                            @endif

                            @if($isAdmin || in_array('2', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.gift_offers.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['gift_offers.index', 'gift_offers.create', 'gift_offers.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Gift Offers') }}</span>
                                    </a>
                                </li>
                            @endif

                            @if($isAdmin || in_array('7', $_authPermissions))
                                {{-- <li class="aiz-side-nav-item">
                                    <a href="{{route('newsletters.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Email Marketing') }}</span>
                                    </a>
                                </li> --}}
                                @if (addon_is_activated('otp_system'))

                                    <li class="aiz-side-nav-item">
                                        <a href="#" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('SMS Marketing')}}</span>
                                            <span class="aiz-side-nav-arrow"></span>
                                        </a>
                                        <ul class="aiz-side-nav-list level-3">

                                            <li class="aiz-side-nav-item">
                                                <a href="{{route('sms_user.bulk_upload')}}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{ ('Number Entry') }}</span>
                                                </a>
                                            </li>
                                            <li class="aiz-side-nav-item">
                                                <a href="{{route('sms_user.index')}}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{ ('Phonebook') }}</span>
                                                </a>
                                            </li>
                                            <li class="aiz-side-nav-item">
                                                <a href="{{route('sms.index')}}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{ ('Send Bulk SMS')}}</span>
                                                    @if (env("DEMO_MODE") == "On")
                                                        <span class="badge badge-inline badge-danger">Addon</span>
                                                    @endif
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                @endif
                            @endif

                            <li class="aiz-side-nav-item">
                                <a href="{{route('coupon.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['coupon.index','coupon.create','coupon.edit'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Coupon Management') }}</span>
                                </a>
                            </li>

                            <li class="aiz-side-nav-item">
                                <a href="{{route('ship_discounts.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['ship_discounts.index','ship_discounts.create','ship_discounts.edit'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Shipping Discounts') }}</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                @endif

                <!-- Customers -->
                @if($isAdmin || in_array('8', $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-user-friends aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Customers') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('customers.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Customer list') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('customer.group') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Customer Groups') }}</span>
                                </a>
                            </li>
                            {{-- <li class="aiz-side-nav-item">
                                <a href="{{ route('wish_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['wish_report.index'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Wishlist') }}</span>
                                </a>
                            </li> --}}
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('subscribers.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Subscribers') }}</span>
                                </a>
                            </li>

                            {{-- @if(get_setting('classified_product') == 1)
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('classified_products')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Classified Products')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('customer_packages.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['customer_packages.index', 'customer_packages.create', 'customer_packages.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Classified Packages') }}</span>
                                    </a>
                                </li>
                            @endif --}}

                        </ul>
                    </li>
                @endif

                <!-- Reports -->
                @if($isAdmin || any_in_array(['10', 'purchase_report', 'top_selling_products_report', 'not_selling_products_report', 'sales_report', 'showroom_sales_report', 'seller_products_sales_report', 'comission_history_report', 'user_searches_report', 'scanning_log_report', 'expire_products_report', 'sales_contribution_report', 'expense_report', 'order_cancellation_report', 'order_loss_profit_report', 'product_visits_report', 'order_tracking_report'], $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-file-alt aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Reports') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @if($isAdmin || any_in_array(['10', 'purchase_report'], $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('reports.purchase.supplier') }}" class="aiz-side-nav-link {{ areActiveRoutes(['reports.purchase.supplier'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Purchase Report By Supplier') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('top_selling_products_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.topSellingProducts') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.topSellingProducts'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Top Selling Products') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('not_selling_products_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.notSellingProducts') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.notSellingProducts'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Not Selling Products') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if(get_setting('enable_product_expire_date') == 1 && ($isAdmin || in_array('expire_products_report', $_authPermissions)))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.expireProductsReport') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.expireProductsReport'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Expire/d Products') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('sales_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('sales.report') }}" class="aiz-side-nav-link {{ areActiveRoutes(['sales.report'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Sales Report') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('showroom_sales_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('sales_report.showroom') }}" class="aiz-side-nav-link {{ areActiveRoutes(['sales_report.showroom'])}}">
                                        <span class="aiz-side-nav-text">Sales Report (Showroom)</span>
                                    </a>
                                </li>
                            @endif
                            @if(get_setting('enable_crm_module') == 1 && ($isAdmin || in_array('sales_contribution_report', $_authPermissions)))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('sales-contribution-reports.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['sales-contribution-reports.index', 'sales-contribution-reports.details'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Sales Contribution Report') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('seller_products_sales_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('seller_sale_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['seller_sale_report.index'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Seller Products Sales') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('comission_history_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('commission-log.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Commission History') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('user_searches_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('user_search_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['user_search_report.index'])}}">
                                        <span class="aiz-side-nav-text">{{ ('User Searches') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('scanning_log_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.reports.shippingScannedReport') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.reports.shippingScannedReport'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Scanning Log Report') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('order_cancellation_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.orderCancellationReport.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.orderCancellationReport.index'])}}">
                                        <span class="aiz-side-nav-text">Order Cancellation Report</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('expense_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('accounts.reports.expense_report') }}" class="aiz-side-nav-link {{ areActiveRoutes(['accounts.reports.expense_report'])}}">
                                        <span class="aiz-side-nav-text">Expense Report</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('order_loss_profit_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.ordersLossProfitReport.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.ordersLossProfitReport.index'])}}">
                                        <span class="aiz-side-nav-text">Order Loss/Profit Report</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('product_visits_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.productVisitsReport.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.productVisitsReport.index'])}}">
                                        <span class="aiz-side-nav-text">Product Visit Report</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('order_tracking_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.orderTrackingReport.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.orderTrackingReport.index'])}}">
                                        <span class="aiz-side-nav-text">Order Tracking Report</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('sms_log_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.smsLogReport.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.smsLogReport.index'])}}">
                                        <span class="aiz-side-nav-text">SMS Log Report</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('coupon_usage_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.couponUsageReport.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.couponUsageReport.index'])}}">
                                        <span class="aiz-side-nav-text">Coupon Usage Report</span>
                                    </a>
                                </li>
                            @endif


                            {{-- <li class="aiz-side-nav-item">
                                <a href="{{ route('purchase_order_report')}}" class="aiz-side-nav-link {{ areActiveRoutes(['purchase_order_report'])}}">
                                    <span class="aiz-side-nav-text">Purchase Order Product</span>
                                </a>
                            </li>

                            <li class="aiz-side-nav-item">
                                <a href="{{ route('in_house_sale_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['in_house_sale_report.index'])}}">
                                    <span class="aiz-side-nav-text">In House Product Sale</span>
                                </a>
                            </li>

                            <li class="aiz-side-nav-item">
                                <a href="{{ route('stock_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['stock_report.index'])}}">
                                    <span class="aiz-side-nav-text">Products Current Stock</span>
                                </a>
                            </li>


                            <li class="aiz-side-nav-item">
                                <a href="{{ route('wallet-history.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">Wallet Recharge History</span>
                                </a>
                            </li> --}}


                        </ul>
                    </li>
                @endif

                {{-- Notices --}}
                @if($isAdmin || any_in_array(['notice_categories', 'notices'], $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 48 48"><g fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="4"><rect width="40" height="26" x="4" y="15" rx="2"/><path stroke-linecap="round" d="m24 7l-8 8h16zM12 24h18m-18 8h8"/></g></svg>
                            {{-- <i class="las la-file-alt aiz-side-nav-icon"></i> --}}
                            <span class="aiz-side-nav-text">Notice Management</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @if($isAdmin || in_array('notice_categories', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('notice-categories.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['notice-categories.index'])}}">
                                        <span class="aiz-side-nav-text">All Categories</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('notices', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('notices.create') }}" class="aiz-side-nav-link {{ areActiveRoutes(['notices.create'])}}">
                                        <span class="aiz-side-nav-text">Create Notice</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('notices.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['notices.index', 'notices.show', 'notices.edit'])}}">
                                        <span class="aiz-side-nav-text">All Notices</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Campaigns --}}
                @if($isAdmin || any_in_array(['campaign_categories', 'campaigns'], $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M18 11v2h4v-2zm-2 6.61c.96.71 2.21 1.65 3.2 2.39c.4-.53.8-1.07 1.2-1.6c-.99-.74-2.24-1.68-3.2-2.4c-.4.54-.8 1.08-1.2 1.61M20.4 5.6c-.4-.53-.8-1.07-1.2-1.6c-.99.74-2.24 1.68-3.2 2.4c.4.53.8 1.07 1.2 1.6c.96-.72 2.21-1.65 3.2-2.4M4 9c-1.1 0-2 .9-2 2v2c0 1.1.9 2 2 2h1v4h2v-4h1l5 3V6L8 9zm5.03 1.71L11 9.53v4.94l-1.97-1.18l-.48-.29H4v-2h4.55zM15.5 12c0-1.33-.58-2.53-1.5-3.35v6.69c.92-.81 1.5-2.01 1.5-3.34"/></svg>
                            <span class="aiz-side-nav-text">Campaign Management</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @if($isAdmin || in_array('campaign_categories', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('campaign-categories.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['campaign-categories.index'])}}">
                                        <span class="aiz-side-nav-text">All Categories</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('campaigns', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('campaigns.create') }}" class="aiz-side-nav-link {{ areActiveRoutes(['campaigns.create'])}}">
                                        <span class="aiz-side-nav-text">Create Campaign</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('campaigns.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['campaigns.index', 'campaigns.show', 'campaigns.edit'])}}">
                                        <span class="aiz-side-nav-text">All Campaigns</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <!-- HR Management -->
                @if($isAdmin || any_in_array(['20', 'merchants', 'staffs_report', 'role_permissions', 'create_staff', 'edit_staff', 'view_staff'], $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-user-tie aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">HR Management</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @if($isAdmin || any_in_array(['20', 'create_staff', 'edit_staff', 'view_staff'], $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('staffs.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['staffs.index', 'staffs.create', 'staffs.edit', 'staffs.show'])}}">
                                        <span class="aiz-side-nav-text">All staffs</span>
                                    </a>
                                </li>
                            @endif
                            @if(get_setting('enable_attendance_management') == 1 && ($isAdmin || any_in_array(['view_holidays', 'manage_holidays'], $_authPermissions)))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.holidays.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Holidays</span>
                                    </a>
                                </li>
                            @endif
                            @if(get_setting('enable_application_management') == 1 && ($isAdmin || any_in_array(['view_applications', 'manage_applications'], $_authPermissions)))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('applications.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Applications</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('staffs_report', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('staffs.report') }}" class="aiz-side-nav-link {{ areActiveRoutes(['staffs.report', 'staffs.report.show'])}}">
                                        <span class="aiz-side-nav-text">Staffs Report</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('log-report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['log-report.index'])}}">
                                        <span class="aiz-side-nav-text">Log Report</span>
                                    </a>
                                </li>
                            @endif
                            @if(get_setting('enable_attendance_management') == 1 && ($isAdmin || any_in_array(['view_attendance', 'edit_attendance'], $_authPermissions)))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('attendance.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Attendance Report</span>
                                    </a>
                                </li>
                            @endif
                            @if(get_setting('enable_attendance_management', 0) == 1 && get_setting('enable_salary_sheet_generation', 0) == 1 && ($isAdmin || any_in_array(['view_salary_sheet', 'edit_salary_sheet'], $_authPermissions)))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('salary.sheet.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Salary Sheets</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('role_permissions', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('roles.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['roles.index', 'roles.create', 'roles.edit'])}}">
                                        <span class="aiz-side-nav-text">Staff permissions</span>
                                    </a>
                                </li>
                            @endif
                            @if(get_setting('enable_jobs_management', 0) == 1 && ($isAdmin || any_in_array(['view_job_posts', 'create_job_posts', 'edit_job_posts', 'manage_job_applications'], $_authPermissions)))
                                <li class="aiz-side-nav-item">
                                    <a href="#" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Jobs Management</span>
                                        <span class="aiz-side-nav-arrow"></span>
                                    </a>
                                    <ul class="aiz-side-nav-list level-3">
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('job_posts.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['job_posts.index', 'job_posts.create', 'job_posts.edit'])}}">
                                                <span class="aiz-side-nav-text">Job List</span>
                                            </a>
                                        </li>
                                        @if($isAdmin || in_array('manage_job_applications', $_authPermissions))
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('job_applications.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['job_applications.index', 'job_applications.show'])}}">
                                                    <span class="aiz-side-nav-text">Job Applications</span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif
                            @if($isAdmin || in_array('merchants', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('merchants.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['merchants.index', 'merchants.create', 'merchants.edit'])}}">
                                        <span class="aiz-side-nav-text">All Merchants</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <!-- Uploaded Files -->
                @if($isAdmin || in_array('22', $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('uploaded-files.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['uploaded-files.create'])}}">
                            <i class="las la-folder-open aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">Uploaded Files</span>
                        </a>
                    </li>
                @endif

                <!--Blog System-->
                @if($isAdmin || in_array('23', $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M7 17h7v-2H7zm0-4h10v-2H7zm0-4h10V7H7zM5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h14q.825 0 1.413.588T21 5v14q0 .825-.587 1.413T19 21zm0-2h14V5H5zM5 5v14z"/></svg>
                            <span class="aiz-side-nav-text">Blog System</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('blog.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['blog.create', 'blog.edit'])}}">
                                    <span class="aiz-side-nav-text">All Posts</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('blog-category.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['blog-category.create', 'blog-category.edit'])}}">
                                    <span class="aiz-side-nav-text">Categories</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Support -->
                @if($isAdmin || in_array('12', $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-headset aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">Support</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">

                            @if(get_setting('live_chat_support') == 1)
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('support.live-chat') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Live Chat</span>
                                    </a>
                                </li>
                            @endif

                            {{-- @if($isAdmin || in_array('12', $_authPermissions))
                                @php
                                    $support_ticket = DB::table('tickets')
                                                ->where('viewed', 0)
                                                ->select('id')
                                                ->count();
                                @endphp
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('support_ticket.admin_index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['support_ticket.admin_index', 'support_ticket.admin_show'])}}">
                                        <span class="aiz-side-nav-text">Ticket</span>
                                        @if($support_ticket > 0)<span class="badge badge-info">{{ $support_ticket }}</span>@endif
                                    </a>
                                </li>
                            @endif --}}


                            {{-- @php
                                $conversation = \App\Models\Conversation::where('receiver_id', Auth::user()->id)->where('receiver_viewed', '1')->get();
                            @endphp
                            @if($isAdmin || in_array('12', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('conversations.admin_index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['conversations.admin_index', 'conversations.admin_show'])}}">
                                        <span class="aiz-side-nav-text">Product Queries</span>
                                        @if (count($conversation) > 0)
                                            <span class="badge badge-info">{{ count($conversation) }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endif --}}

                        </ul>
                    </li>
                @endif

                {{-- Tickets --}}
                @if($isAdmin || any_in_array(['support_tickets', 'ticket_categories', 'manage_service'], $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.709 7.65c.157-.157.428-.173.587 0c.932 1.018 1.449 1.772 1.619 2.606c.097.48.111.968.04 1.443c-.19 1.285-1.23 2.325-3.312 4.406l-2.538 2.538c-2.08 2.081-3.121 3.122-4.406 3.313c-.475.07-.963.056-1.443-.041c-.834-.17-1.588-.686-2.605-1.619c-.174-.16-.158-.43 0-.588c.876-.876.834-2.338-.093-3.266c-.928-.927-2.39-.969-3.266-.093c-.157.158-.429.174-.588 0c-.933-1.017-1.449-1.77-1.619-2.605a4.2 4.2 0 0 1-.04-1.443c.19-1.285 1.23-2.325 3.312-4.406l2.538-2.538c2.08-2.081 3.121-3.122 4.406-3.313c.475-.07.963-.056 1.443.041c.835.17 1.588.687 2.605 1.62c.174.159.158.43 0 .586c-.876.877-.834 2.339.094 3.266c.927.928 2.39.97 3.265.093M19 15L9 5" color="currentColor"/></svg>
                            <span class="aiz-side-nav-text">Tickets Management</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @if($isAdmin || in_array('ticket_categories', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('ticket_categories.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['ticket_categories.index', 'ticket_categories.create', 'ticket_categories.edit'])}}">
                                        <span class="aiz-side-nav-text">All Categories</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('support_tickets', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('tickets.admin_index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['tickets.admin_index', 'tickets.admin_show', 'tickets.create'])}}">
                                        <span class="aiz-side-nav-text">Support Tickets</span>
                                    </a>
                                </li>
                            @endif
                            @if($isAdmin || in_array('manage_service', $_authPermissions))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('services.manage') }}" class="aiz-side-nav-link {{ areActiveRoutes(['services.manage'])}}">
                                        <span class="aiz-side-nav-text">Manage Service</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Templates --}}
                @if($isAdmin || in_array('manage_templates', $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1zm0 8a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1zm10-1h6m-6 4h6m-6 4h6"/></svg>

                            <span class="aiz-side-nav-text">Template Management</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('templates.create') }}" class="aiz-side-nav-link {{ areActiveRoutes(['templates.create'])}}">
                                    <span class="aiz-side-nav-text">Create Template</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('templates.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['templates.index', 'templates.show', 'templates.edit'])}}">
                                    <span class="aiz-side-nav-text">All Templates</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if($isAdmin || in_array('highlighted_items', $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M4.95 7.325L4.2 6.6q-.3-.275-.288-.687T4.2 5.2q.3-.3.713-.312t.712.287l.725.725q.275.275.288.688T6.35 7.3q-.275.275-.687.288t-.713-.263M11 4V3q0-.425.288-.712T12 2t.713.288T13 3v1q0 .425-.288.713T12 5t-.712-.288T11 4m6.7 1.875l.7-.7q.275-.275.688-.275t.712.3q.275.275.275.7t-.275.7l-.7.7q-.275.275-.688.288t-.712-.263q-.3-.3-.3-.725t.3-.725M9 20v-3l-2.425-2.425q-.275-.275-.425-.638T6 13.176V11q0-.825.587-1.412T8 9h8q.825 0 1.413.588T18 11v2.175q0 .4-.15.763t-.425.637L15 17v3q0 .825-.587 1.413T13 22h-2q-.825 0-1.412-.587T9 20m2 0h2v-3.825l3-3V11H8v2.175l3 3zm1-4.5"/></svg>
                            <span class="aiz-side-nav-text">Highlighted Items</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>

                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('highlightedProduct.create') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">Create Highlighted Item</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('highlightedProduct.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['highlightedProduct.index', 'highlightedProduct.edit'])}}">
                                    <span class="aiz-side-nav-text">Highlighted Items</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Setup & Configurations -->
                @if($isAdmin || any_in_array(['13', '14', '19'], $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <svg class="aiz-side-nav-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46a.5.5 0 0 0-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65A.49.49 0 0 0 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1a.6.6 0 0 0-.18-.03c-.17 0-.34.09-.43.25l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46a.5.5 0 0 0 .61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1q.09.03.18.03c.17 0 .34-.09.43-.25l2-3.46c.12-.22.07-.49-.12-.64zm-1.98-1.71c.04.31.05.52.05.73s-.02.43-.05.73l-.14 1.13l.89.7l1.08.84l-.7 1.21l-1.27-.51l-1.04-.42l-.9.68c-.43.32-.84.56-1.25.73l-1.06.43l-.16 1.13l-.2 1.35h-1.4l-.19-1.35l-.16-1.13l-1.06-.43c-.43-.18-.83-.41-1.23-.71l-.91-.7l-1.06.43l-1.27.51l-.7-1.21l1.08-.84l.89-.7l-.14-1.13c-.03-.31-.05-.54-.05-.74s.02-.43.05-.73l.14-1.13l-.89-.7l-1.08-.84l.7-1.21l1.27.51l1.04.42l.9-.68c.43-.32.84-.56 1.25-.73l1.06-.43l.16-1.13l.2-1.35h1.39l.19 1.35l.16 1.13l1.06.43c.43.18.83.41 1.23.71l.91.7l1.06-.43l1.27-.51l.7 1.21l-1.07.85l-.89.7zM12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4s4-1.79 4-4s-1.79-4-4-4m0 6c-1.1 0-2-.9-2-2s.9-2 2-2s2 .9 2 2s-.9 2-2 2"/></svg>
                            <span class="aiz-side-nav-text">Settings</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">

                            @if($isAdmin || in_array('19', $_authPermissions))
                                <!-- sms setting -->
                                <li class="aiz-side-nav-item">
                                    <a href="#" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">SMS Settings</span>
                                        <span class="aiz-side-nav-arrow"></span>
                                    </a>
                                    <ul class="aiz-side-nav-list level-3">
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('otp.configconfiguration') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">OTP Configurations</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('sms-templates.index')}}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">SMS Templates</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('otp_credentials.index')}}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Set OTP Credentials</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif
                            @if($isAdmin || in_array('13', $_authPermissions))
                                <!-- website setup -->
                                <li class="aiz-side-nav-item">
                                    <a href="#" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Website Setup</span>
                                        <span class="aiz-side-nav-arrow"></span>
                                    </a>
                                    <ul class="aiz-side-nav-list level-3">
                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('general_setting.index')}}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">General Settings</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('website.dashboard')}}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Dashboard</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('website.header') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Header</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('website.footer', ['lang'=>  App::getLocale()] ) }}" class="aiz-side-nav-link {{ areActiveRoutes(['website.footer'])}}">
                                                <span class="aiz-side-nav-text">Footer</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('website.pages') }}" class="aiz-side-nav-link {{ areActiveRoutes(['website.pages', 'custom-pages.create' ,'custom-pages.edit'])}}">
                                                <span class="aiz-side-nav-text">Pages</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('website.appearance') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Appearance</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('website.global_seo') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Global SEO</span>
                                            </a>
                                        </li>

                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('languages.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['languages.index', 'languages.create', 'languages.store', 'languages.show', 'languages.edit'])}}">
                                                <span class="aiz-side-nav-text">Languages</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('tax.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['tax.index', 'tax.create', 'tax.store', 'tax.show', 'tax.edit'])}}">
                                                <span class="aiz-side-nav-text">Vat & TAX</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('ads.index') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Advertisement</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('mail_template.index') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Mail Template</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('block.ip.index') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Block Ip</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('smtp_settings.index') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">SMTP Settings</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('notification_settings.index') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Notification Settings</span>
                                            </a>
                                        </li>
                                        @if(get_setting('enable_clouflare_cache') == 1)
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('cloudflare_setting.index') }}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">Cloudflare Settings</span>
                                                </a>
                                            </li>
                                        @endif

                                        @if(get_setting('reward_point_system') == 1)
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('business_settings.rewardPointSettings') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Reward Points</span>
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif
                            @if($isAdmin || in_array('14', $_authPermissions))
                                <!-- shipping -->
                                <li class="aiz-side-nav-item">
                                    <a href="javascript:void(0);" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Shipping</span>
                                        <span class="aiz-side-nav-arrow"></span>
                                    </a>
                                    <ul class="aiz-side-nav-list level-3">
                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('shipping_configuration.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['shipping_configuration.index','shipping_configuration.edit','shipping_configuration.update'])}}">
                                                <span class="aiz-side-nav-text">Shipping Configuration</span>
                                            </a>
                                        </li>

                                        {{-- <li class="aiz-side-nav-item">
                                            <a href="{{route('countries.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['countries.index','countries.edit','countries.update'])}}">
                                                <span class="aiz-side-nav-text">{{ ('Country Setting')}}</span>
                                            </a>
                                        </li> --}}

                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('states.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['states.index','states.edit','states.update'])}}">
                                                <span class="aiz-side-nav-text">Division Settings</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('cities.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['cities.index','cities.edit','cities.update'])}}">
                                                <span class="aiz-side-nav-text">City Setting</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('areas.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['areas.index','areas.edit','areas.update'])}}">
                                                <span class="aiz-side-nav-text">Area Setting</span>
                                            </a>
                                        </li>

                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('shipping_method.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['shipping_method.index','shipping_method.edit','shipping_method.update'])}}">
                                                <span class="aiz-side-nav-text">Methods Setting</span>
                                            </a>
                                        </li>

                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('shipping_zone.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['shipping_zone.index','shipping_zone.edit','shipping_zone.update'])}}">
                                                <span class="aiz-side-nav-text">Zone Setting</span>
                                            </a>
                                        </li>

                                        @if(@get_setting('automated_pathao_shipping') == 1)
                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('shipping.pathao.settings')}}" class="aiz-side-nav-link {{ areActiveRoutes(['shipping.pathao.settings'])}}">
                                                <span class="aiz-side-nav-text">Configure Pathao</span>
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </li>

                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('social_login.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Social media Login</span>
                                    </a>
                                </li>

                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('user-notification.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">User Notifications</span>
                                    </a>
                                </li>

                                {{-- facebook --}}
                                <li class="aiz-side-nav-item">
                                    <a href="javascript:void(0);" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Facebook</span>
                                        <span class="aiz-side-nav-arrow"></span>
                                    </a>
                                    <ul class="aiz-side-nav-list level-3">
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('facebook_chat.index') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Chat</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('facebook-comment') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Comment</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                {{-- google --}}
                                <li class="aiz-side-nav-item">
                                    <a href="javascript:void(0);" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Google</span>
                                        <span class="aiz-side-nav-arrow"></span>
                                    </a>
                                    <ul class="aiz-side-nav-list level-3">
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('google_analytics.index') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Analytics Tools</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('google_tag_manager.index') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Tag Manager</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('google_recaptcha.index') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Google reCAPTCHA</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('google-map.index') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Google Map</span>
                                            </a>
                                        </li>
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('google-firebase.index') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">Google Firebase</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('onesignal.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Onesignal</span>
                                    </a>
                                </li>


                                @if(config('app.name')=='ECOM71')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('activation.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Features activation</span>
                                    </a>
                                </li>
                                @endif

                                @if(config('app.name')=='ECOM71')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('currency.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Currency</span>
                                    </a>
                                </li>
                                @endif

                                {{-- <li class="aiz-side-nav-item">
                                    <a href="{{route('pick_up_points.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['pick_up_points.index','pick_up_points.create','pick_up_points.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Pickup point')}}</span>
                                    </a>
                                </li> --}}

                                @if(config('app.name')=='ECOM71')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('payment_method.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">Payment Methods</span>
                                    </a>
                                </li>

                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('file_system.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">File System & Cache Configuration</span>
                                    </a>
                                </li>
                                @endif
                            @endif
                        </ul>
                    </li>
                @endif

                <!-- Faqs -->
                @if($isAdmin || in_array('29', $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-question-circle aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Faqs') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>

                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('faqs.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Faqs list') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif


                {{-- Rewrite Urls --}}
                @if($isAdmin || in_array('28', $_authPermissions))
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('rewrite_url.index') }}" class="aiz-side-nav-link">
                            <i class="las la-link aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Rewrite URL')}}</span>
                        </a>
                    </li>
                @endif


                <!-- Auction Product -->
                @if(addon_is_activated('auction'))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-gavel aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Auction Products')}}</span>
                            @if (env("DEMO_MODE") == "On")
                                <span class="badge badge-inline badge-danger">Addon</span>
                            @endif
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a class="aiz-side-nav-link" href="{{route('auction_products.create')}}">
                                    <span class="aiz-side-nav-text">{{ ('Add New auction product')}}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{route('auction.all_products')}}" class="aiz-side-nav-link {{ areActiveRoutes(['auction_products.edit','product_bids.show']) }}">
                                    <span class="aiz-side-nav-text">{{ ('All Auction Products') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{route('auction.inhouse_products')}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Inhouse Auction Products') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{route('auction.seller_products')}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Seller Auction Products') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{route('auction_products_orders')}}" class="aiz-side-nav-link {{ areActiveRoutes(['auction_products_orders.index']) }}">
                                    <span class="aiz-side-nav-text">{{ ('Auction Products Orders') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Wholesale Product -->
                @if(addon_is_activated('wholesale'))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-luggage-cart aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Wholesale Products')}}</span>
                            @if (env("DEMO_MODE") == "On")
                                <span class="badge badge-inline badge-danger">Addon</span>
                            @endif
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a class="aiz-side-nav-link" href="{{route('wholesale-products.create')}}">
                                    <span class="aiz-side-nav-text">{{ ('Add new wholesale product')}}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{route('wholesale-products.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['wholesale-products.edit','wholesale-products.show']) }}">
                                    <span class="aiz-side-nav-text">{{ ('All wholesale products') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Deliver Boy Addon-->
                @if (addon_is_activated('delivery_boy'))
                    @if($isAdmin || in_array('1', $_authPermissions))
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <i class="las la-truck aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Delivery Boy')}}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <span class="badge badge-inline badge-danger">Addon</span>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('delivery-boys.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('All Delivery Boy')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('delivery-boys.create')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Add Delivery Boy')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('delivery-boys-payment-histories')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Payment Histories')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('delivery-boys-collection-histories')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Collected Histories')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('delivery-boy.cancel-request')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Cancel Request')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('delivery-boy-configuration')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Configuration')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endif

                <!-- Refund addon -->
                @if (addon_is_activated('refund_request'))
                    @if($isAdmin || in_array('7', $_authPermissions))
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <i class="las la-backward aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Refunds') }}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <span class="badge badge-inline badge-danger">Addon</span>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('refund_requests_all')}}" class="aiz-side-nav-link {{ areActiveRoutes(['refund_requests_all', 'reason_show'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Refund Requests')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('paid_refund')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Approved Refunds')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('rejected_refund')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('rejected Refunds')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('refund_time_config')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Refund Configuration')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endif

                <!-- Sellers -->
                @if(($isAdmin || in_array('9', $_authPermissions)) && get_setting('vendor_system_activation') == 1)
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-user aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Sellers') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                @php
                                    $sellers = \App\Models\Seller::where('verification_status', 0)->where('verification_info', '!=', null)->count();
                                @endphp
                                <a href="{{ route('sellers.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['sellers.index', 'sellers.create', 'sellers.edit', 'sellers.payment_history','sellers.approved','sellers.profile_modal','sellers.show_verification_request'])}}">
                                    <span class="aiz-side-nav-text">{{ ('All Seller') }}</span>
                                    @if($sellers > 0)<span class="badge badge-info">{{ $sellers }}</span> @endif
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('sellers.payment_histories') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Payouts') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('withdraw_requests_all') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Payout Requests') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('business_settings.vendor_commission') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Seller Commission') }}</span>
                                </a>
                            </li>

                            @if (addon_is_activated('seller_subscription'))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('seller_packages.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['seller_packages.index', 'seller_packages.create', 'seller_packages.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Seller Packages') }}</span>
                                        @if (env("DEMO_MODE") == "On")
                                            <span class="badge badge-inline badge-danger">Addon</span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('seller_verification_form.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Seller Verification Form') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Affiliate Addon -->
                @if (addon_is_activated('affiliate_system'))
                    @if($isAdmin || in_array('15', $_authPermissions))
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <i class="las la-link aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Affiliate System')}}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <span class="badge badge-inline badge-danger">Addon</span>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('affiliate.configs')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Affiliate Registration Form')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('affiliate.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Affiliate Configurations')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('affiliate.users')}}" class="aiz-side-nav-link {{ areActiveRoutes(['affiliate.users', 'affiliate_users.show_verification_request', 'affiliate_user.payment_history'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Affiliate Users')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('refferals.users')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Referral Users')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('affiliate.withdraw_requests')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Affiliate Withdraw Requests')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('affiliate.logs.admin')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Affiliate Logs')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endif

                <!-- Offline Payment Addon-->
                @if (addon_is_activated('offline_payment'))
                    @if($isAdmin || in_array('16', $_authPermissions))
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <i class="las la-money-check-alt aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Offline Payment System')}}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <span class="badge badge-inline badge-danger">Addon</span>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('manual_payment_methods.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['manual_payment_methods.index', 'manual_payment_methods.create', 'manual_payment_methods.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Manual Payment Methods')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('offline_wallet_recharge_request.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Offline Wallet Recharge')}}</span>
                                    </a>
                                </li>
                                @if(get_setting('classified_product') == 1)
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('offline_customer_package_payment_request.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Offline Customer Package Payments')}}</span>
                                        </a>
                                    </li>
                                @endif
                                @if (addon_is_activated('seller_subscription'))
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('offline_seller_package_payment_request.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Offline Seller Package Payments')}}</span>
                                            @if (env("DEMO_MODE") == "On")
                                                <span class="badge badge-inline badge-danger">Addon</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif

                <!-- Paytm Addon -->
                @if (addon_is_activated('paytm'))
                    @if($isAdmin || in_array('17', $_authPermissions))
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <i class="las la-mobile-alt aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Paytm Payment Gateway')}}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <span class="badge badge-inline badge-danger">Addon</span>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('paytm.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Set Paytm Credentials')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endif

                <!-- Club Point Addon-->
                @if (addon_is_activated('club_point'))
                    @if($isAdmin || in_array('18', $_authPermissions))
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <i class="lab la-btc aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Club Point System')}}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <span class="badge badge-inline badge-danger">Addon</span>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('club_points.configs') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Club Point Configurations')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('set_product_points')}}" class="aiz-side-nav-link {{ areActiveRoutes(['set_product_points', 'product_club_point.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Set Product Point')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('club_points.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['club_points.index', 'club_point.details'])}}">
                                        <span class="aiz-side-nav-text">{{ ('User Points')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endif

                @if(addon_is_activated('african_pg'))
                    @if($isAdmin || in_array('19', $_authPermissions))
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <i class="las la-phone aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('African Payment Gateway Addon')}}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <span class="badge badge-inline badge-danger">Addon</span>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('african.configuration') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('African PG Configurations')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('african_credentials.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Set African PG Credentials')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endif

                @if(config('app.name')=='ECOM71')
                    @if($isAdmin || in_array('24', $_authPermissions))
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <i class="las la-user-tie aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('System')}}</span>
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('system_update') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Update')}}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('system_server')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Server status')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    <!-- Addon Manager -->
                    @if($isAdmin || in_array('21', $_authPermissions))
                        <li class="aiz-side-nav-item">
                            <a href="{{route('addons.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['addons.index', 'addons.create'])}}">
                                <i class="las la-wrench aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Addon Manager')}}</span>
                            </a>
                        </li>
                    @endif
                @endif

            </ul><!-- .aiz-side-nav -->
        </div><!-- .aiz-side-nav-wrap -->
    </div><!-- .aiz-sidebar -->
    <div class="aiz-sidebar-overlay"></div>
</div><!-- .aiz-sidebar -->
