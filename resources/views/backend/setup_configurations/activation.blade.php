@extends('backend.layouts.app')

@section('content')

<h4 class="text-center text-muted">{{ ('System')}}</h4>
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
            	<h5 class="mb-0 h6 text-center">{{ ('HTTPS Activation')}}</h5>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'FORCE_HTTPS')" <?php if(env('FORCE_HTTPS') == 'On') echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Maintenance Mode Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'maintenance_mode')" <?php if(get_setting('maintenance_mode') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Disable image encoding?')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'disable_image_optimization')" <?php if(get_setting('disable_image_optimization') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Enable Cloudflare Caching?')}}</h3>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" onchange="updateSettings(this, 'enable_clouflare_cache')" <?php if(get_setting('enable_clouflare_cache') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert text-center" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure Cloudflare settings to enable this feature') }}. <a href="{{ route('cloudflare_setting.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">Disable Web Routes</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'disable_web_routes')" <?php if(get_setting('disable_web_routes') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
</div>

<h4 class="text-center text-muted">Feature</h4>
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
            	<h5 class="mb-0 h6 text-center">Enable Product Expire Date?</h5>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'enable_product_expire_date')" <?php if(get_setting('enable_product_expire_date') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
            	<h5 class="mb-0 h6 text-center">Enable CRM Module?</h5>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'enable_crm_module')" <?php if(get_setting('enable_crm_module') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
            	<h5 class="mb-0 h6 text-center">Enable Guest Order?</h5>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'guest_order_activation')" <?php if(get_setting('guest_order_activation') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
            	<h5 class="mb-0 h6 text-center">Enable Meilisearch?</h5>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'enable_meilisearch')" <?php if(get_setting('enable_meilisearch') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
            	<h5 class="mb-0 h6 text-center">Enable UTM Order Tracking?</h5>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'enable_utm_order_tracking')" <?php if(get_setting('enable_utm_order_tracking') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
            	<h5 class="mb-0 h6 text-center">Enable Attendance Management?</h5>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input id="enable_attendance_management" type="checkbox" onchange="updateSettings(this, 'enable_attendance_management')" <?php if(get_setting('enable_attendance_management') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
            	<h5 class="mb-0 h6 text-center">Enable Salary Sheet Generation?</h5>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'enable_salary_sheet_generation')" <?php if(get_setting('enable_salary_sheet_generation') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
                <div class="alert text-center" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    Required: Attendance Management
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
            	<h5 class="mb-0 h6 text-center">Enable Application Management?</h5>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input id="enable_application_management" type="checkbox" onchange="updateSettings(this, 'enable_application_management')" <?php if(get_setting('enable_application_management') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
            	<h5 class="mb-0 h6 text-center">Enable Jobs Management?</h5>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'enable_jobs_management')" <?php if(get_setting('enable_jobs_management') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
                <div class="alert text-center" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    Required: Application Management
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Enable Courier Success Rate?')}}</h3>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" onchange="updateSettings(this, 'enable_courier_success_rate')" <?php if(get_setting('enable_courier_success_rate') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert text-center" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure some settings to enable this feature') }}. <a href="{{ route('courier_success_rate_setting.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

@if (Module::count())
<h4 class="text-center text-muted">Modules</h4>
<div class="row">
    @foreach (Module::all() as $module)
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6 text-center">{{ $module->getName() }}</h5>
                </div>
                <div class="card-body text-center">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" onchange="updateModule(this, '{{ $module->getName() }}')" @if(Module::isEnabled($module->getName())) checked @endif>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif

<h4 class="text-center text-muted mt-4">{{ ('Business Related')}}</h4>
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Vendor System Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'vendor_system_activation')" <?php if(get_setting('vendor_system_activation') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Classified Product')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'classified_product')" <?php if(get_setting('classified_product') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Wallet System Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'wallet_system')" <?php if(get_setting('wallet_system') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Coupon System Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'coupon_system')" <?php if(get_setting('coupon_system') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Pickup Point Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'pickup_point')" <?php if(get_setting('pickup_point') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Conversation Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'conversation_system')" <?php if(get_setting('conversation_system') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Seller Product Manage By Admin')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'product_manage_by_admin')"
                        <?php if(\App\Models\BusinessSetting::where('type', 'product_manage_by_admin')->first() &&
                                get_setting('product_manage_by_admin') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('After activate this option Cash On Delivery of Seller product will be managed by Admin')}}.
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Admin Approval On Seller Product')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'product_approve_by_admin')"
                        <?php if(\App\Models\BusinessSetting::where('type', 'product_approve_by_admin')->first() &&
                                get_setting('product_approve_by_admin') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('After activate this option, Admin approval need to seller product')}}.
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Email Verification')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'email_verification')" <?php if(get_setting('email_verification') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    You need to configure SMTP correctly to enable this feature. <a href="{{ route('smtp_settings.index') }}">Configure Now</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Live Chat Support')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input value="1" type="checkbox" onchange="updateSettings(this, 'live_chat_support')" <?php if(@get_setting('live_chat_support') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Rewards Points On/Off')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input value="1" type="checkbox" onchange="updateSettings(this, 'reward_point_system')" <?php if(@get_setting('reward_point_system') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Automated Pathao Shipping')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input value="1" type="checkbox" onchange="updateSettings(this, 'automated_pathao_shipping')" <?php if(@get_setting('automated_pathao_shipping') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Single Page Checkout')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input value="1" type="checkbox" onchange="updateSettings(this, 'spa_checkout')" <?php if(@get_setting('spa_checkout') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Rokomari Service')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'enable_rokomari_service')" <?php if(get_setting('enable_rokomari_service') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    You need to configure Rokomari credentials. <a href="{{ route('rokomari_settings.index') }}">Configure Now</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Kiriebd Service')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'enable_kireibd_service')" <?php if(get_setting('enable_kireibd_service') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
</div>

<h4 class="text-center text-muted mt-4">{{ ('Payment Related')}}</h4>
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header text-center bord-btm">
                <h3 class="mb-0 h6 text-center">{{ ('Paypal Payment Activation')}}</h3>
            </div>
            <div class="card-body">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/paypal.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'paypal_payment')" <?php if(get_setting('paypal_payment') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert text-center" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure Paypal correctly to enable this feature') }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Stripe Payment Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img   class="float-left" src="{{ static_asset('assets/img/cards/stripe.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'stripe_payment')" <?php if(get_setting('stripe_payment') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    You need to configure Stripe correctly to enable this feature. <a href="{{ route('payment_method.index') }}">Configure Now</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('SSlCommerz Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/sslcommerz.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'sslcommerz_payment')" <?php if(get_setting('sslcommerz_payment') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    You need to configure SSlCommerz correctly to enable this feature. <a href="{{ route('payment_method.index') }}">Configure Now</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Instamojo Payment Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/instamojo.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'instamojo_payment')" <?php if(get_setting('instamojo_payment') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure Instamojo Payment correctly to enable this feature') }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Razor Pay Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/rozarpay.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'razorpay')" <?php if(get_setting('razorpay') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure Razor correctly to enable this feature') }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('PayStack Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/paystack.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'paystack')" <?php if(get_setting('paystack') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure PayStack correctly to enable this feature')  }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('VoguePay Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/vogue.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'voguepay')" <?php if(get_setting('voguepay') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure VoguePay correctly to enable this feature') }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Payhere Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/payhere.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'payhere')" <?php if(get_setting('payhere') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure VoguePay correctly to enable this feature') }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Ngenius Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/ngenius.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'ngenius')" <?php if(get_setting('ngenius') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure Ngenius correctly to enable this feature') }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Iyzico Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/iyzico.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'iyzico')" <?php if(get_setting('iyzico') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure iyzico correctly to enable this feature') }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Bkash Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/bkash.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'bkash')" <?php if(get_setting('bkash') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure bkash correctly to enable this feature') }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Nagad Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/nagad.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'nagad')" <?php if(get_setting('nagad') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure nagad correctly to enable this feature') }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Proxy Pay Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/proxypay.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'proxypay')" <?php if(get_setting('proxypay') == '1') echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure proxypay correctly to enable this feature') }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Amarpay Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/aamarpay.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'aamarpay')" @if(get_setting('aamarpay') == '1') checked @endif>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure amarpay correctly to enable this feature') }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Authorize Net Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/authorizenet.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'authorizenet')" <?php if(get_setting('authorizenet') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure authorize net correctly to enable this feature') }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Payku Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/payku.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'payku')" <?php if(get_setting('payku') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure payku net correctly to enable this feature') }}. <a href="{{ route('payment_method.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Cash Payment Activation')}}</h3>
            </div>
            <div class="card-body text-center">
                <div class="clearfix">
                    <img class="float-left" src="{{ static_asset('assets/img/cards/cod.png') }}" height="30">
                    <label class="aiz-switch aiz-switch-success mb-0 float-right">
                        <input type="checkbox" onchange="updateSettings(this, 'cash_payment')" <?php if(get_setting('cash_payment') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

</div>

<h4 class="text-center text-muted mt-4">{{ ('Social Media Login')}}</h4>
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Facebook login')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'facebook_login')" <?php if(get_setting('facebook_login') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure Facebook Client correctly to enable this feature') }}. <a href="{{ route('social_login.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Google login')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'google_login')" <?php if(get_setting('google_login') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure Google Client correctly to enable this feature') }}. <a href="{{ route('social_login.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ ('Twitter login')}}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'twitter_login')" <?php if(get_setting('twitter_login') == 1) echo "checked";?>>
                    <span class="slider round"></span>
                </label>
                <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                    {{ ('You need to configure Twitter Client correctly to enable this feature') }}. <a href="{{ route('social_login.index') }}">{{ ('Configure Now') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function updateSettings(el, type){
            const isChecked = $(el).is(':checked') ? 1 : 0;

            if (isChecked) {
                if (type === 'enable_jobs_management' && !$('#enable_application_management').is(':checked')) {
                    showAlert('warning', 'Please activate Application Management before activating Jobs Management.');
                    el.checked = false;
                    return;
                } else if (type === 'enable_salary_sheet_generation' && !$('#enable_attendance_management').is(':checked')) {
                    showAlert('warning', 'Please activate Attendance Management before activating Salary Sheet Generation.');
                    el.checked = false;
                    return;
                }
            }

            $.post('{{ route('business_settings.update.activation') }}', {_token:'{{ csrf_token() }}', type:type, value:isChecked}, function(data){
                if(data == '1'){
                    AIZ.plugins.notify('success', '{{ ('Settings updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
        function updateModule(el, module){
            const isChecked = $(el).is(':checked') ? 1 : 0;
            $.ajax({
                url: '{{ route('module.activator') }}',
                type: 'POST',
                data: {_token:'{{ csrf_token() }}', module:module, status: isChecked},
                success: function(response){
                    if(response.success){
                        AIZ.plugins.notify('success', '{{ ('Settings updated successfully') }}');
                    }
                    else{
                        AIZ.plugins.notify('danger', 'Something went wrong');
                        el.checked = !isChecked;
                    }
                }, error: function(){
                    AIZ.plugins.notify('danger', 'Something went wrong');
                    el.checked = !isChecked;
                }
            });
        }
    </script>
@endsection
