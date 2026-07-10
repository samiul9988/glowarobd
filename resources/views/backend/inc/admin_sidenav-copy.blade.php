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
                    @if(Auth::user()->user_type == 'admin' || in_array('1', json_decode(Auth::user()->staff->role->permissions)))
                        <li class="aiz-side-nav-item">
                            <a href="{{route('poin-of-sales.index')}}" class="aiz-side-nav-link">
                                <i class="las la-tasks aiz-side-nav-icon"></i>
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

                <!-- Accounting Addon -->
                @if (addon_is_activated('accounts_system'))
                    @if(Auth::user()->user_type == 'admin' || in_array('26', json_decode(Auth::user()->staff->role->permissions)))
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <i class="lab la-btc aiz-side-nav-icon"></i>
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
                @dd(json_decode(Auth::user()->staff->role->permissions, true))
                <!-- Order Management -->
                @if(Auth::user()->user_type == 'admin' || in_array('3', json_decode(Auth::user()->staff->role->permissions)))
                <li class="aiz-side-nav-item">
                    <a href="#" class="aiz-side-nav-link">
                        <i class="las la-money-bill aiz-side-nav-icon"></i>
                        <span class="aiz-side-nav-text">{{ ('Order Management')}}</span>
                        <span class="aiz-side-nav-arrow"></span>
                    </a>
                    <!--Submenu-->
                    <ul class="aiz-side-nav-list level-2">
                        @if(Auth::user()->user_type == 'admin' || in_array('3', json_decode(Auth::user()->staff->role->permissions)))
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('all_orders.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['all_orders.index', 'all_orders.show'])}}">
                                    <span class="aiz-side-nav-text">{{ ('All Orders')}}</span>
                                </a>
                            </li>
                        @endif

                        @if(@intval(json_decode(@get_setting('vendor_system_activation')))==1)
                            @if(Auth::user()->user_type == 'admin' || in_array('5', json_decode(Auth::user()->staff->role->permissions)))
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

                        {{-- @if(Auth::user()->user_type == 'admin' || in_array('6', json_decode(Auth::user()->staff->role->permissions)))
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('pick_up_point.order_index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['pick_up_point.order_index','pick_up_point.order_show'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Pickup Point Order')}}</span>
                                </a>
                            </li>
                        @endif --}}

                        {{-- Refund Requests --}}
                        @if(Auth::user()->user_type == 'admin' || in_array('22', json_decode(Auth::user()->staff->role->permissions)))
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('refund_request.all') }}" class="aiz-side-nav-link {{ areActiveRoutes(['refund_request.all'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Refund Requests') }}</span>
                                </a>
                            </li>
                        @endif

                        @if(Auth::user()->user_type == 'admin')
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('reviewcomments.index') }}" class="aiz-side-nav-link">
                                <span class="aiz-side-nav-text">{{ ('Review Comments')}}</span>
                            </a>
                        </li>
                        @endif
                        {{-- @if(Auth::user()->user_type == 'admin' || in_array('7', json_decode(Auth::user()->staff->role->permissions)))
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('upcoming.delivery') }}" class="aiz-side-nav-link {{ areActiveRoutes(['upcoming.delivery', 'upcoming.delivery'])}}">
                                <span class="aiz-side-nav-text">{{ ('Upcoming Delivery')}}</span>
                            </a>
                        </li>
                        @endif

                        @if(Auth::user()->user_type == 'admin' || in_array('4', json_decode(Auth::user()->staff->role->permissions)))
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
                @if(Auth::user()->user_type == 'admin' || in_array('2', json_decode(Auth::user()->staff->role->permissions)))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-shopping-cart aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Product Management')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">

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

                            {{--<li class="aiz-side-nav-item">
                                <a href="{{route('products.admin')}}" class="aiz-side-nav-link {{ areActiveRoutes(['products.admin', 'products.create', 'products.admin.edit']) }}" >
                                    <span class="aiz-side-nav-text">{{ ('In House Products') }}</span>
                                </a>
                            </li>--}}

                            <li class="aiz-side-nav-item">
                                <a href="{{route('categories.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['categories.index', 'categories.create', 'categories.edit'])}}" >
                                    <span class="aiz-side-nav-text">{{ ('Categories')}}</span>
                                </a>
                            </li>

                            <li class="aiz-side-nav-item">
                                <a href="{{route('brands.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['brands.index', 'brands.create', 'brands.edit'])}}" >
                                    <span class="aiz-side-nav-text">{{ ('Brand')}}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{route('attributes.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['attributes.index','attributes.create','attributes.edit'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Attribute')}}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{route('colors')}}" class="aiz-side-nav-link {{ areActiveRoutes(['attributes.index','attributes.create','attributes.edit'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Colors')}}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{route('reviews.index')}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Product Reviews')}}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{route('products.custom_fields.index')}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Product Custom Fields')}}</span>
                                </a>
                            </li>


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

                {{-- Meta Object Management --}}
                @if(Auth::user()->user_type == 'admin' || in_array('27', json_decode(Auth::user()->staff->role->permissions)))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-shopping-cart aiz-side-nav-icon"></i>
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
                @if(Auth::user()->user_type == 'admin' || in_array('25', json_decode(Auth::user()->staff->role->permissions)))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-shopping-cart aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Inventory Management')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
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
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('products.stock.new') }}" class="aiz-side-nav-link {{ areActiveRoutes(['product.stock.new'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Products Stock Report') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('products.stock.latest') }}" class="aiz-side-nav-link {{ areActiveRoutes(['product.stock.latest'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Products Stock Report New') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('reports.stock.product') }}" class="aiz-side-nav-link {{ areActiveRoutes(['reports.stock.product'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Stock Report By Product') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('reports.stock.product.new') }}" class="aiz-side-nav-link {{ areActiveRoutes(['reports.stock.product.new'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Stock Report By Product New') }}</span>
                                </a>
                            </li>
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
                        </ul>
                    </li>
                @endif

                <!-- Offer Management -->
                @if(Auth::user()->user_type == 'admin' || in_array('11', json_decode(Auth::user()->staff->role->permissions)))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-bullhorn aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Offer Management') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @if(Auth::user()->user_type == 'admin' || in_array('2', json_decode(Auth::user()->staff->role->permissions)))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('flash_deals.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['flash_deals.index', 'flash_deals.create', 'flash_deals.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Flash deals') }}</span>
                                    </a>
                                </li>
                            @endif

                            @if(Auth::user()->user_type == 'admin' || in_array('7', json_decode(Auth::user()->staff->role->permissions)))
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('newsletters.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Email Marketing') }}</span>
                                    </a>
                                </li>
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
                @if(Auth::user()->user_type == 'admin' || in_array('8', json_decode(Auth::user()->staff->role->permissions)))
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
                @if(Auth::user()->user_type == 'admin' || in_array('10', json_decode(Auth::user()->staff->role->permissions)))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-file-alt aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Reports') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('reports.purchase.supplier') }}" class="aiz-side-nav-link {{ areActiveRoutes(['reports.purchase.supplier'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Purchase Report By Supplier') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('admin.topSellingProducts') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.topSellingProducts'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Top Selling Products') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('sales.report') }}" class="aiz-side-nav-link {{ areActiveRoutes(['sales.report'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Sales Report') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('seller_sale_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['seller_sale_report.index'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Seller Products Sales') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('commission-log.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Commission History') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('user_search_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['user_search_report.index'])}}">
                                    <span class="aiz-side-nav-text">{{ ('User Searches') }}</span>
                                </a>
                            </li>

                            <li class="aiz-side-nav-item">
                                <a href="{{ route('admin.reports.shippingScannedReport') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.reports.shippingScannedReport'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Scanning Log Report') }}</span>
                                </a>
                            </li>


                            {{-- <li class="aiz-side-nav-item">
                                <a href="{{ route('purchase_order_report')}}" class="aiz-side-nav-link {{ areActiveRoutes(['purchase_order_report'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Purchase Order Product') }}</span>
                                </a>
                            </li>

                            <li class="aiz-side-nav-item">
                                <a href="{{ route('in_house_sale_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['in_house_sale_report.index'])}}">
                                    <span class="aiz-side-nav-text">{{ ('In House Product Sale') }}</span>
                                </a>
                            </li>

                            <li class="aiz-side-nav-item">
                                <a href="{{ route('stock_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['stock_report.index'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Products Current Stock') }}</span>
                                </a>
                            </li>


                            <li class="aiz-side-nav-item">
                                <a href="{{ route('wallet-history.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Wallet Recharge History') }}</span>
                                </a>
                            </li> --}}


                        </ul>
                    </li>
                @endif

                <!-- HR Management -->
                @if(Auth::user()->user_type == 'admin' || in_array('20', json_decode(Auth::user()->staff->role->permissions)))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-user-tie aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('HR Management')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('staffs.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['staffs.index', 'staffs.create', 'staffs.edit'])}}">
                                    <span class="aiz-side-nav-text">{{ ('All staffs')}}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('staffs.report') }}" class="aiz-side-nav-link {{ areActiveRoutes(['staffs.report', 'staffs.report.show'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Staffs Report')}}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{route('roles.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['roles.index', 'roles.create', 'roles.edit'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Staff permissions')}}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('merchants.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['merchants.index', 'merchants.create', 'merchants.edit'])}}">
                                    <span class="aiz-side-nav-text">{{ ('All Merchants')}}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Uploaded Files -->
                @if(Auth::user()->user_type == 'admin' || in_array('22', json_decode(Auth::user()->staff->role->permissions)))
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('uploaded-files.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['uploaded-files.create'])}}">
                            <i class="las la-folder-open aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Uploaded Files') }}</span>
                        </a>
                    </li>
                @endif

                <!--Blog System-->
                @if(Auth::user()->user_type == 'admin' || in_array('23', json_decode(Auth::user()->staff->role->permissions)))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-bullhorn aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Blog System') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('blog.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['blog.create', 'blog.edit'])}}">
                                    <span class="aiz-side-nav-text">{{ ('All Posts') }}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('blog-category.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['blog-category.create', 'blog-category.edit'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Categories') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Support -->
                @if(Auth::user()->user_type == 'admin' || in_array('12', json_decode(Auth::user()->staff->role->permissions)))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-headset aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Support')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">

                            @if(get_setting('live_chat_support') == 1)
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('support.live-chat') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Live Chat')}}</span>
                                </a>
                            </li>
                            @endif

                            @if(Auth::user()->user_type == 'admin' || in_array('12', json_decode(Auth::user()->staff->role->permissions)))
                                @php
                                    $support_ticket = DB::table('tickets')
                                                ->where('viewed', 0)
                                                ->select('id')
                                                ->count();
                                @endphp
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('support_ticket.admin_index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['support_ticket.admin_index', 'support_ticket.admin_show'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Ticket')}}</span>
                                        @if($support_ticket > 0)<span class="badge badge-info">{{ $support_ticket }}</span>@endif
                                    </a>
                                </li>
                            @endif


                            {{-- @php
                                $conversation = \App\Models\Conversation::where('receiver_id', Auth::user()->id)->where('receiver_viewed', '1')->get();
                            @endphp
                            @if(Auth::user()->user_type == 'admin' || in_array('12', json_decode(Auth::user()->staff->role->permissions)))
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('conversations.admin_index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['conversations.admin_index', 'conversations.admin_show'])}}">
                                        <span class="aiz-side-nav-text">{{ ('Product Queries')}}</span>
                                        @if (count($conversation) > 0)
                                            <span class="badge badge-info">{{ count($conversation) }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endif --}}

                        </ul>
                    </li>
                @endif

                <!-- Setup & Configurations -->
                @if(Auth::user()->user_type == 'admin' || in_array('14', json_decode(Auth::user()->staff->role->permissions)))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <i class="las la-dharmachakra aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">{{ ('Settings')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">

                            <!-- sms setting -->
                            <li class="aiz-side-nav-item">
                                <a href="#" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('SMS Settings')}}</span>
                                    <span class="aiz-side-nav-arrow"></span>
                                </a>
                                <ul class="aiz-side-nav-list level-3">
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('otp.configconfiguration') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('OTP Configurations')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('sms-templates.index')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('SMS Templates')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('otp_credentials.index')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Set OTP Credentials')}}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <!-- website setup -->
                            <li class="aiz-side-nav-item">
                                <a href="#" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Website Setup')}}</span>
                                    <span class="aiz-side-nav-arrow"></span>
                                </a>
                                <ul class="aiz-side-nav-list level-3">
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('general_setting.index')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('General Settings')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('website.dashboard')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Dashboard')}}</span>
                                        </a>
                                    </li>
                                     <li class="aiz-side-nav-item">
                                        <a href="{{ route('website.header') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Header')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('website.footer', ['lang'=>  App::getLocale()] ) }}" class="aiz-side-nav-link {{ areActiveRoutes(['website.footer'])}}">
                                            <span class="aiz-side-nav-text">{{ ('Footer')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('website.pages') }}" class="aiz-side-nav-link {{ areActiveRoutes(['website.pages', 'custom-pages.create' ,'custom-pages.edit'])}}">
                                            <span class="aiz-side-nav-text">{{ ('Pages')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('website.appearance') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Appearance')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('website.global_seo') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Global SEO')}}</span>
                                        </a>
                                    </li>

                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('languages.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['languages.index', 'languages.create', 'languages.store', 'languages.show', 'languages.edit'])}}">
                                            <span class="aiz-side-nav-text">{{ ('Languages')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('tax.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['tax.index', 'tax.create', 'tax.store', 'tax.show', 'tax.edit'])}}">
                                            <span class="aiz-side-nav-text">{{ ('Vat & TAX')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('ads.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Advertisement')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('mail_template.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Mail Template')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('block.ip.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Block Ip')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('smtp_settings.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('SMTP Settings')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('notification_settings.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Notification Settings')}}</span>
                                        </a>
                                    </li>

                                    @if(get_setting('reward_point_system') == 1)
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('business_settings.rewardPointSettings') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Reward Points')}}</span>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </li>
                            <!-- shipping -->
                            <li class="aiz-side-nav-item">
                                <a href="javascript:void(0);" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Shipping')}}</span>
                                    <span class="aiz-side-nav-arrow"></span>
                                </a>
                                <ul class="aiz-side-nav-list level-3">
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('shipping_configuration.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['shipping_configuration.index','shipping_configuration.edit','shipping_configuration.update'])}}">
                                            <span class="aiz-side-nav-text">{{ ('Shipping Configuration')}}</span>
                                        </a>
                                    </li>

                                    {{-- <li class="aiz-side-nav-item">
                                        <a href="{{route('countries.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['countries.index','countries.edit','countries.update'])}}">
                                            <span class="aiz-side-nav-text">{{ ('Country Setting')}}</span>
                                        </a>
                                    </li> --}}

                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('states.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['states.index','states.edit','states.update'])}}">
                                            <span class="aiz-side-nav-text">{{ ('Division Settings')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('cities.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['cities.index','cities.edit','cities.update'])}}">
                                            <span class="aiz-side-nav-text">{{ ('City Setting')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('areas.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['areas.index','areas.edit','areas.update'])}}">
                                            <span class="aiz-side-nav-text">{{ ('Area Setting')}}</span>
                                        </a>
                                    </li>

                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('shipping_method.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['shipping_method.index','shipping_method.edit','shipping_method.update'])}}">
                                            <span class="aiz-side-nav-text">{{ ('Methods Setting')}}</span>
                                        </a>
                                    </li>

                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('shipping_zone.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['shipping_zone.index','shipping_zone.edit','shipping_zone.update'])}}">
                                            <span class="aiz-side-nav-text">{{ ('Zone Setting')}}</span>
                                        </a>
                                    </li>

                                    @if(@get_setting('automated_pathao_shipping') == 1)
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('shipping.pathao.settings')}}" class="aiz-side-nav-link {{ areActiveRoutes(['shipping.pathao.settings'])}}">
                                            <span class="aiz-side-nav-text">{{ ('Configure Pathao')}}</span>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </li>

                            <li class="aiz-side-nav-item">
                                <a href="{{ route('social_login.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Social media Login')}}</span>
                                </a>
                            </li>

                            <li class="aiz-side-nav-item">
                                <a href="{{ route('user-notification.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('User Notifications')}}</span>
                                </a>
                            </li>

                            {{-- facebook --}}
                            <li class="aiz-side-nav-item">
                                <a href="javascript:void(0);" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Facebook')}}</span>
                                    <span class="aiz-side-nav-arrow"></span>
                                </a>
                                <ul class="aiz-side-nav-list level-3">
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('facebook_chat.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Chat')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('facebook-comment') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Comment')}}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            {{-- google --}}
                            <li class="aiz-side-nav-item">
                                <a href="javascript:void(0);" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Google')}}</span>
                                    <span class="aiz-side-nav-arrow"></span>
                                </a>
                                <ul class="aiz-side-nav-list level-3">
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('google_analytics.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Analytics Tools')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('google_tag_manager.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Tag Manager')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('google_recaptcha.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Google reCAPTCHA')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('google-map.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Google Map')}}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('google-firebase.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ ('Google Firebase')}}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="aiz-side-nav-item">
                                <a href="{{ route('onesignal.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Onesignal')}}</span>
                                </a>
                            </li>


                            @if(config('app.name')=='ECOM71')
                            <li class="aiz-side-nav-item">
                                <a href="{{route('activation.index')}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Features activation')}}</span>
                                </a>
                            </li>
                            @endif

                            @if(config('app.name')=='ECOM71')
                            <li class="aiz-side-nav-item">
                                <a href="{{route('currency.index')}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('Currency')}}</span>
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
                                    <span class="aiz-side-nav-text">{{ ('Payment Methods')}}</span>
                                </a>
                            </li>

                            <li class="aiz-side-nav-item">
                                <a href="{{ route('file_system.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{ ('File System & Cache Configuration')}}</span>
                                </a>
                            </li>
                            @endif

                        </ul>
                    </li>
                @endif

                <!-- Faqs -->
                @if(Auth::user()->user_type == 'admin' || in_array('29', json_decode(Auth::user()->staff->role->permissions)))
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
                @if(Auth::user()->user_type == 'admin' || in_array('28', json_decode(Auth::user()->staff->role->permissions)))
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
                    @if(Auth::user()->user_type == 'admin' || in_array('1', json_decode(Auth::user()->staff->role->permissions)))
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
                    @if(Auth::user()->user_type == 'admin' || in_array('7', json_decode(Auth::user()->staff->role->permissions)))
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
                @if((Auth::user()->user_type == 'admin' || in_array('9', json_decode(Auth::user()->staff->role->permissions))) && get_setting('vendor_system_activation') == 1)
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
                    @if(Auth::user()->user_type == 'admin' || in_array('15', json_decode(Auth::user()->staff->role->permissions)))
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
                    @if(Auth::user()->user_type == 'admin' || in_array('16', json_decode(Auth::user()->staff->role->permissions)))
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
                    @if(Auth::user()->user_type == 'admin' || in_array('17', json_decode(Auth::user()->staff->role->permissions)))
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
                    @if(Auth::user()->user_type == 'admin' || in_array('18', json_decode(Auth::user()->staff->role->permissions)))
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
                    @if(Auth::user()->user_type == 'admin' || in_array('19', json_decode(Auth::user()->staff->role->permissions)))
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
                    @if(Auth::user()->user_type == 'admin' || in_array('24', json_decode(Auth::user()->staff->role->permissions)))
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
                    @if(Auth::user()->user_type == 'admin' || in_array('21', json_decode(Auth::user()->staff->role->permissions)))
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
