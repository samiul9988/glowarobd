<?php

use Illuminate\Support\Facades\Route;

Route::post('/update', 'UpdateController@step0')->name('update');
Route::get('/update/step1', 'UpdateController@step1')->name('update.step1');
Route::get('/update/step2', 'UpdateController@step2')->name('update.step2');

Route::get('ticket_categories/get-subcategories', 'TicketCategoryController@getSubCategories')->name('ticket_categories.get_subcategories');
// Route::get('/admin', 'AdminController@index')->name('admin.dashboard.index')->middleware(['auth', 'admin']);
Route::get('/admin', 'AdminController@index')->name('admin.dashboard')->middleware(['auth', 'admin']);
Route::get('/admin/reset-dashboard-cache', 'AdminController@resetDashboardCache')->name('admin.dashboard.reset_cache');

Route::get('/ajax/products', 'ProductSearchController')->name('ajax.products.search')->middleware(['auth', 'admin']);

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin', 'unbanned']], function () {
    Route::get('/import-products', 'ProductController@import');
    // Paid Due Chart For Admin Dashboard
    Route::get('/orders-payments-chart', 'AdminController@orderPaymentsChart')->name('admin.dashboard.order_payments_chart');
    // Order Source Chart For Admin Dashboard
    Route::get('/orders-source-chart', 'AdminController@orderSourceChart')->name('admin.dashboard.order_source_chart');
    // Order Summary Graphs For Admin Dashboard
    Route::get('/orders-summary-graphs', 'AdminController@getGraphData')->name('admin.dashboard.order_summary_graphs');
    // Top Selling Products For Admin Dashboard
    Route::get('/dashboard-top-selling-products', 'AdminController@getTopSellingProducts')->name('admin.dashboard.get_top_selling_product');
    // Get Cards Data For Admin Dashboard
    Route::get('/get-cards-data', 'AdminController@getCardsData')->name('admin.dashboard.get_cards_data');

    Route::middleware(['feature.activation:enable_attendance_management'])->group(function () {
        // Attendances
        Route::get('/attendances', 'AttendanceController@index')->name('attendance.index');
        Route::get('/attendances/changelogs/{id}', 'AttendanceController@changelogs')->name('attendance.changelogs');
        Route::post('attendances/check-in', 'AttendanceController@checkIn')->name('attendance.checkIn');
        Route::post('attendances/check-out', 'AttendanceController@checkOut')->name('attendance.checkOut');
        Route::post('attendances/overtime-in', 'AttendanceController@overtimeIn')->name('attendance.overtimeIn');
        Route::post('attendances/overtime-out', 'AttendanceController@overtimeOut')->name('attendance.overtimeOut');
        Route::get('attendance/staff/{id}', 'AttendanceController@getByMonth')->name('attendance.filter');
        Route::put('attendances/update/{id}', 'AttendanceController@update')->name('attendance.update');
    });

    Route::middleware([
        'feature.activation:enable_attendance_management,enable_salary_sheet_generation'
    ])->group(function () {
        // Salary Sheets
        Route::get('/salary-sheets', 'SalarySheetController@index')->name('salary.sheet.index');
        Route::get('/salary-sheets/generate', 'SalarySheetController@generate')->name('salary.sheet.generate');
        Route::post('/salary-sheets/update', 'SalarySheetController@update')->name('salary.sheet.update');
        Route::get('/salary-sheets/staff/{id}', 'SalarySheetController@getByMonth')->name('salary.sheets.filter');
    });


    Route::middleware(['feature.activation:enable_application_management'])->group(function () {
        // Applications
        Route::get('applications', 'ApplicationController@index')->name('applications.index');
        Route::post('/applications', 'ApplicationController@store')->name('applications.store');
        Route::get('/applications/{id}', 'ApplicationController@show')->name('applications.show');
        Route::put('/applications/{id}', 'ApplicationController@update')->name('applications.update');
        Route::put('/applications/{id}/manage', 'ApplicationController@manage')->name('applications.manage');
        Route::delete('/applications/{id}', 'ApplicationController@destroy')->name('applications.destroy');
        Route::get('applications/staff/{id}', 'ApplicationController@getByMonth')->name('applications.filter');
    });

    Route::middleware([
        'feature.activation:enable_jobs_management'
    ])->group(function () {
        // Job Posts + Job Applications
        Route::get('/job-posts', 'JobPostController@index')->name('job_posts.index');
        Route::get('/job-posts/create', 'JobPostController@create')->name('job_posts.create');
        Route::post('/job-posts', 'JobPostController@store')->name('job_posts.store');
        Route::get('/job-posts/{jobPost}/edit', 'JobPostController@edit')->name('job_posts.edit');
        Route::put('/job-posts/{jobPost}', 'JobPostController@update')->name('job_posts.update');

        Route::get('job-applications', 'JobApplicationController@index')->name('job_applications.index');
        Route::get('job-applications/{id}', 'JobApplicationController@show')->name('job_applications.show');
        Route::post('job-applications/{id}/update-info', 'JobApplicationController@updateInfo')->name('job_applications.update_info');
        Route::post('job-applications/{id}/notes', 'JobApplicationController@addNote')->name('job_applications.add_note');
        Route::delete('job-applications/{id}/notes', 'JobApplicationController@deleteNote')->name('job_applications.delete_note');
        Route::patch('job-applications/{id}/status', 'JobApplicationController@updateStatus')->name('job_applications.update_status');
        Route::patch('job-applications/{id}/shortlist', 'JobApplicationController@updateShortlist')->name('job_applications.update_shortlist');
        Route::post('job-applications/{id}/shortlist', 'JobApplicationController@shortlist')->name('job_applications.shortlist');
        Route::post('job-applications/{id}/send-sms', 'JobApplicationController@sendSms')->name('job_applications.send_sms');
    });



    // Module Shortcuts
    Route::post('/get-module-shortcuts', 'AdminController@get_module_shortcuts')->name('admin.get_module_shortcuts');

    // Campaigns Categories
    Route::post('campaign-categories/bulk-status-update', 'CampaignCategoryController@bulkStatusUpdate')->name('campaign-categories.bulk-status-update');
    Route::post('campaign-categories/bulk-delete', 'CampaignCategoryController@bulkDelete')->name('campaign-categories.bulk-delete');
    Route::get('/campaign-categories/destroy/{id}', 'CampaignCategoryController@destroy')->name('campaign-categories.destroy');
    Route::resource('campaign-categories', 'CampaignCategoryController')->only(['index', 'store', 'update']);

    // Campaigns
    Route::post('campaigns/bulk-status-update', 'CampaignController@bulkStatusUpdate')->name('campaigns.bulk-status-update');
    Route::post('campaigns/bulk-delete', 'CampaignController@bulkDelete')->name('campaigns.bulk-delete');
    Route::get('/campaigns/destroy/{id}', 'CampaignController@destroy')->name('campaigns.destroy');
    Route::resource('campaigns', 'CampaignController')->except(['show', 'destroy']);

    // Notice Categories
    Route::post('notice-categories/bulk-status-update', 'NoticeCategoryController@bulkStatusUpdate')->name('notice-categories.bulk-status-update');
    Route::post('notice-categories/bulk-delete', 'NoticeCategoryController@bulkDelete')->name('notice-categories.bulk-delete');
    Route::get('/notice-categories/destroy/{id}', 'NoticeCategoryController@destroy')->name('notice-categories.destroy');
    Route::resource('notice-categories', 'NoticeCategoryController')->only(['index', 'store', 'update']);

    // Notices
    Route::post('notices/bulk-status-update', 'NoticeController@bulkStatusUpdate')->name('notices.bulk-status-update');
    Route::post('notices/bulk-delete', 'NoticeController@bulkDelete')->name('notices.bulk-delete');
    Route::get('/notices/destroy/{id}', 'NoticeController@destroy')->name('notices.destroy');
    Route::resource('notices', 'NoticeController')->except(['show', 'destroy']);

    // Support_Ticket
    Route::get('support_ticket/', 'SupportTicketController@admin_index_old')->name('support_ticket.admin_index');
    Route::get('support_ticket/{id}/show', 'SupportTicketController@admin_show_old')->name('support_ticket.admin_show');
    Route::post('support_ticket/reply', 'SupportTicketController@admin_store_old')->name('support_ticket.admin_store');

    // Support Ticket Categories
    Route::resource('ticket_categories', 'TicketCategoryController')->except(['show', 'destroy']);
    Route::post('ticket_categories/bulk-status', 'TicketCategoryController@bulkStatusUpdate')->name('ticket_categories.bulk_status');

    // Tickets Management
    Route::post('/bulk-ticket-delete', 'SupportTicketController@bulk_delete')->name('bulk-ticket-delete');
    Route::post('/bulk-ticket-status', 'SupportTicketController@bulk_status')->name('bulk-ticket-status');
    Route::get('/tickets/create/{id?}', 'SupportTicketController@create')->name('tickets.create');
    Route::post('/tickets/store', 'SupportTicketController@store')->name('tickets.store');
    Route::get('/tickets/message', 'SupportTicketController@message')->name('tickets.message');
    Route::get('/services/manage', 'ServiceController@manage')->name('services.manage');
    Route::post('tickets/reply', 'SupportTicketController@admin_store')->name('tickets.admin_store');
    Route::get('/tickets/{issue?}', 'SupportTicketController@admin_index')->name('tickets.admin_index');
    Route::get('/tickets/{id}/show', 'SupportTicketController@admin_show')->name('tickets.admin_show');

    // Shortcut Modules
    Route::get('/shortcut-modules', 'ShortcutModuleController@index')->name('admin.shortcut_modules');
    Route::post('/shortcut-modules/store', 'ShortcutModuleController@store')->name('admin.shortcut_modules.store');

    // Shortcuts
    Route::get('/shortcuts', 'ShortcutController@index')->name('admin.shortcuts');
    Route::post('/shortcuts/store', 'ShortcutController@store')->name('admin.shortcuts.store');

    // Rewrite Url
    Route::get('/rewrite-url', 'RewriteUrlController@index')->name('rewrite_url.index');
    Route::post('/rewrite-url', 'RewriteUrlController@store')->name('rewrite_url.store');
    Route::post('/rewrite-url/update/{id}', 'RewriteUrlController@update')->name('rewrite_url.update');
    Route::post('/rewrite-url/update-status/{id}', 'RewriteUrlController@update_status')->name('rewrite_url.update_status');
    Route::get('/rewrite-url/destroy/{id}', 'RewriteUrlController@destroy')->name('rewrite_url.destroy');
    // Route for store home page category collection
    Route::post('/home-category/store', 'BusinessSettingsController@store_home_category')->name('home_category.store');
    Route::post('/home-category-app/store', 'BusinessSettingsController@store_home_category_app')->name('home_category_app.store');

    Route::get('/getLowStockProducts', 'AdminController@getLowStockProducts')->name('admin.dashboard_LowStockProducts');

    // Update Routes
    Route::get('/categories/fetch', 'CategoryController@fetchAll')->name('categories.fetchAll');
    Route::resource('categories', 'CategoryController');
    Route::get('/categories/edit/{id}', 'CategoryController@edit')->name('categories.edit');
    Route::get('/categories/destroy/{id}', 'CategoryController@destroy')->name('categories.destroy');
    Route::post('/categories/featured', 'CategoryController@updateFeatured')->name('categories.featured');
    Route::post('/categories/update_status', 'CategoryController@update_status')->name('categories.update_status');

    Route::get('/brands/fetch', 'BrandController@fetchAll')->name('brands.fetchAll');
    Route::resource('brands', 'BrandController');
    Route::get('/brands/edit/{id}', 'BrandController@edit')->name('brands.edit');
    Route::get('/brands/destroy/{id}', 'BrandController@destroy')->name('brands.destroy');
    Route::post('/brands/update_status', 'BrandController@update_status')->name('brands.update_status');

    Route::get('/products/fetch', 'ProductController@fetchAll')->name('products.fetchAll');
    Route::get('/products/admin', 'ProductController@admin_products')->name('products.admin');
    Route::get('/products/seller', 'ProductController@seller_products')->name('products.seller');
    Route::get('/products', 'ProductController@all_products')->name('products.all');
    Route::get('/products/create', 'ProductController@create')->name('products.create');
    Route::get('/products/admin/{id}/edit', 'ProductController@admin_product_edit')->name('products.admin.edit');
    Route::get('/products/admin/{id}/stock', 'ProductController@admin_product_stock')->name('products.admin.stock');
    Route::post('/products/admin/{id}/updatestock', 'ProductController@updatestock')->name('products.admin.updatestock');
    Route::get('/products/seller/{id}/edit', 'ProductController@seller_product_edit')->name('products.seller.edit');
    Route::post('/products/todays_deal', 'ProductController@updateTodaysDeal')->name('products.todays_deal');
    Route::post('/products/featured', 'ProductController@updateFeatured')->name('products.featured');
    Route::post('/products/subscription', 'ProductController@updateSubscription')->name('products.subscription');
    Route::post('/products/approved', 'ProductController@updateProductApproval')->name('products.approved');
    Route::post('/products/get_products_by_subcategory', 'ProductController@get_products_by_subcategory')->name('products.get_products_by_subcategory');
    Route::post('/bulk-product-delete', 'ProductController@bulk_product_delete')->name('bulk-product-delete');

    Route::post('/products/export', 'ProductController@export')->name('products.export');

    // Highlighted Products
    Route::get('/products/highlighted', 'HighlightedItemController@index')->name('highlightedProduct.index');
    Route::get('/products/highlighted/create', 'HighlightedItemController@create')->name('highlightedProduct.create');
    Route::get('/products/highlighted/{id}/edit', 'HighlightedItemController@edit')->name('highlightedProduct.edit');
    Route::post('/products/highlighted', 'HighlightedItemController@store')->name('highlightedProduct.store');
    Route::put('/products/highlighted/{id}/update', 'HighlightedItemController@update')->name('highlightedProduct.update');
    Route::put('/products/highlighted/{id}/touch', 'HighlightedItemController@touch')->name('highlightedProduct.touch');
    Route::get('/products/highlighted/{id}/destroy', 'HighlightedItemController@destroy')->name('highlightedProduct.destroy');
    Route::post('/products/highlighted/bulk-destroy', 'HighlightedItemController@bulkDestroy')->name('highlightedProduct.bulk-destroy');

    // Status Wise Products
    Route::get('/products/stock-out-old', 'ProductController@allStockOutProductsOld');
    Route::get('/products/stock-out', 'ProductController@allStockOutProducts')->name('all_products.stock_out');
    Route::get('/products/stock-out/export', 'ProductController@allStockOutProductsExport')->name('all_products.stock_out.export');
    Route::get('/products/{status}', 'ProductController@all_products')->name('all_products.status');

    // Merchant Products
    Route::get('/merchant-products', 'MerchantProductController@index')->name('merchant_products.index');
    Route::post('/merchant-products/update-price', 'MerchantProductController@updatePrice')->name('merchant_products.update_price');
    Route::post('/merchant-products/bulk-update-price', 'MerchantProductController@bulkUpdatePrice')->name('merchant_products.bulk_update_price');
    Route::post('/merchant-products/import', 'MerchantProductController@import')->name('merchant_products.import');
    Route::post('/merchant-products/bulk-push', 'MerchantProductController@bulkPushProduct')->name('merchant_products.bulk_push');

    // Doctors Consultation Section
    Route::post('/doctors-consultation/store', 'BusinessSettingsController@doctorsConsultationStore')->name('doctors_consultation.store');

    // Products custom fields and meta objects and meta object items
    Route::get('/meta-objects', 'MetaObjectController@index')->name('meta-objects.index');
    Route::get('/meta-objects/{id}/show', 'MetaObjectController@show')->name('meta-objects.show');
    Route::post('/meta-objects/store', 'MetaObjectController@store')->name('meta-objects.store');
    Route::post('/meta-objects/update/{id}', 'MetaObjectController@update')->name('meta-objects.update');
    Route::patch('/meta-objects/update-status/{id}', 'MetaObjectController@updateStatus')->name('meta-objects.update_status');
    Route::get('/meta-objects/destroy/{id}', 'MetaObjectController@destroy')->name('meta-objects.destroy');
    Route::post('/meta-objects/bulk-destroy', 'MetaObjectController@bulk_delete')->name('meta-objects.bulk_destroy');

    Route::get('/meta-objects/items', 'MetaObjectItemController@index')->name('meta-object-items.index');
    Route::get('/meta-objects/items/create', 'MetaObjectItemController@create')->name('meta-object-items.create');
    Route::post('/meta-objects/items/store', 'MetaObjectItemController@store')->name('meta-object-items.store');
    Route::get('/meta-objects/items/edit/{id}', 'MetaObjectItemController@edit')->name('meta-object-items.edit');
    Route::post('/meta-objects/items/update/{id}', 'MetaObjectItemController@update')->name('meta-object-items.update');
    Route::patch('/meta-objects/items/update-status/{id}', 'MetaObjectItemController@updateStatus')->name('meta-object-items.update_status');
    Route::get('/meta-objects/items/destroy/{id}', 'MetaObjectItemController@destroy')->name('meta-object-items.destroy');
    Route::post('/meta-objects/items/bulk-destroy', 'MetaObjectItemController@bulk_delete')->name('meta-object-items.bulk_destroy');

    Route::get('/custom-fields', 'ProductCustomFieldController@index')->name('products.custom_fields.index');
    Route::post('/custom-fields/store', 'ProductCustomFieldController@store')->name('products.custom_fields.store');
    Route::post('/custom-fields/update/{id}', 'ProductCustomFieldController@update')->name('products.custom_fields.update');
    Route::patch('/custom-fields/update_status/{id}', 'ProductCustomFieldController@updateStatus')->name('products.custom_fields.update_status');
    Route::get('/custom-fields/destroy/{id}', 'ProductCustomFieldController@destroy')->name('products.custom_fields.destroy');
    Route::post('/custom-fields/bulk-destroy', 'ProductCustomFieldController@bulk_delete')->name('products.custom_fields.bulk_destroy');

    Route::resource('sellers', 'SellerController');
    Route::get('sellers_ban/{id}', 'SellerController@ban')->name('sellers.ban');
    Route::get('/sellers/destroy/{id}', 'SellerController@destroy')->name('sellers.destroy');
    Route::post('/bulk-seller-delete', 'SellerController@bulk_seller_delete')->name('bulk-seller-delete');
    Route::get('/sellers/view/{id}/verification', 'SellerController@show_verification_request')->name('sellers.show_verification_request');
    Route::get('/sellers/approve/{id}', 'SellerController@approve_seller')->name('sellers.approve');
    Route::get('/sellers/reject/{id}', 'SellerController@reject_seller')->name('sellers.reject');
    Route::get('/sellers/login/{id}', 'SellerController@login')->name('sellers.login');
    Route::post('/sellers/payment_modal', 'SellerController@payment_modal')->name('sellers.payment_modal');
    Route::get('/seller/payments', 'PaymentController@payment_histories')->name('sellers.payment_histories');
    Route::get('/seller/payments/show/{id}', 'PaymentController@show')->name('sellers.payment_history');

    Route::post('customers/fix-groups', 'CustomerController@fixGroups')->name('customers.fix-groups');
    Route::post('customers/create', 'CustomerController@createByAdmin')->name('customers.create');
    Route::post('customers/send-fallback-message', 'CustomerController@sendFallbackMessage')->name('customers.send-fallback-message');
    Route::post('customers/change-verification-status', 'CustomerController@changeVerificationStatus')->name('customers.change-verification-status');
    Route::resource('customers', 'CustomerController');
    if (get_setting('enable_crm_module') == 1) {
        Route::get('filtered_customers', 'CustomerController@filteredCustomers')->name('customers.filtered');
        Route::get('filtered_customers/{id}/details', 'CustomerController@filteredCustomersDetails')->name('customers.filtered.details');
        Route::get('filtered_customers/{id}/call-logs', 'CustomerController@getCallLogs')->name('customers.call-logs');
    }
    Route::post('customers/{id}/assign-coupon', 'CustomerController@assignCoupon')->name('customers.assign_coupon');
    Route::delete('customers/remove-coupon/{id}', 'CustomerController@removeCoupon')->name('customers.remove_coupon');
    Route::get('customer-orders/{id}', 'CustomerController@getOrdersByCustomer')->name('customers.orders');
    Route::get('customer-carts-wishlists/{id}', 'CustomerController@getCartsAndWishlistsByCustomer')->name('customers.carts_and_wishlists');
    Route::put('customers/{id}/update', 'CustomerController@update')->name('customers.update');
    Route::get('customers_ban/{customer}', 'CustomerController@ban')->name('customers.ban');
    Route::get('/customers/login/{id}', 'CustomerController@login')->name('customers.login');
    Route::get('/customers/destroy/{id}', 'CustomerController@destroy')->name('customers.destroy');
    Route::post('/bulk-customer-delete', 'CustomerController@bulk_customer_delete')->name('bulk-customer-delete');
    Route::get('/customers/details/{id}', 'CustomerController@details')->name('customers.details');

    Route::get('/faqs', 'FaqController@index')->name('faqs.index');
    Route::post('/faqs/store', 'FaqController@store')->name('faqs.store');
    Route::post('/faqs/update/{id}', 'FaqController@update')->name('faqs.update');
    Route::post('/faqs/destroy', 'FaqController@destroy')->name('faqs.destroy');

    // Templates
    Route::resource('/templates', 'TemplateController');

    // Route::get('/newsletter', 'NewsletterController@index')->name('newsletters.index');
    // Route::post('/newsletter/send', 'NewsletterController@send')->name('newsletters.send');
    // Route::post('/newsletter/test/smtp', 'NewsletterController@testEmail')->name('test.smtp');

    Route::resource('profile', 'ProfileController');
    Route::get('/sms_user/bulk_upload', 'SmsuserController@bulk_upload')->name('sms_user.bulk_upload');
    Route::post('/sms_user/bulk_sms_user_upload', 'SmsuserController@bulk_sms_user_upload')->name('sms_user.bulk_sms_user_upload');
    Route::resource('sms_user', 'SmsuserController');
    Route::get('/sms_user/edit/{id}', 'SmsuserController@edit')->name('sms_user.edit');
    Route::get('/sms_user/destroy/{id}', 'SmsuserController@destroy')->name('sms_user.destroy');

    Route::post('/business-settings/update', 'BusinessSettingsController@update')->name('business_settings.update');
    Route::post('/business-settings/update-shortcuts', 'BusinessSettingsController@updateShortcuts')->name('business_settings.updateShortcuts');

    Route::post('/business-settings/update-nestate-menu', 'BusinessSettingsController@updatenestate')->name('business_settings.updatenestate');
    Route::post('/business-settings/update/activation', 'BusinessSettingsController@updateActivationSettings')->name('business_settings.update.activation');
    Route::get('/general-setting', 'BusinessSettingsController@general_setting')->name('general_setting.index');
    Route::get('/notification-setting', 'BusinessSettingsController@notification_setting')->name('notification_settings.index');
    Route::get('/cloudflare-setting', 'BusinessSettingsController@cloudflare_setting')->name('cloudflare_setting.index');
    Route::get('/courier-success-rate-setting', 'BusinessSettingsController@courier_success_rate_setting')->name('courier_success_rate_setting.index');
    Route::get('/rokomari-setting', 'BusinessSettingsController@rokomari_setting')->name('rokomari_settings.index');
    Route::get('/activation', 'BusinessSettingsController@activation')->name('activation.index');
    Route::get('/payment-method', 'BusinessSettingsController@payment_method')->name('payment_method.index');
    Route::get('/file_system', 'BusinessSettingsController@file_system')->name('file_system.index');
    Route::get('/social-login', 'BusinessSettingsController@social_login')->name('social_login.index');
    Route::get('/smtp-settings', 'BusinessSettingsController@smtp_settings')->name('smtp_settings.index');

    // mail template route
    Route::prefix('/mail/template')->name('mail_template.')->group(function () {
        Route::get('/index', 'MailTemplateController@index')->name('index');
        Route::get('/edit/{id}', 'MailTemplateController@edit')->name('edit');
        Route::post('/update', 'MailTemplateController@update')->name('update');
        Route::post('/update/status', 'MailTemplateController@update_status')->name('update_status');
    });

    // advertizements route
    Route::prefix('/advertisement')->name('ads.')->group(function () {
        Route::get('/index', 'AdvertizementController@index')->name('index');
        Route::get('/create', 'AdvertizementController@create')->name('create');
        Route::post('/store', 'AdvertizementController@store')->name('store');
        Route::get('/edit/{id}', 'AdvertizementController@edit')->name('edit');
        Route::post('/update', 'AdvertizementController@update')->name('update');
        Route::get('/destroy/{id}', 'AdvertizementController@destroy')->name('destroy');
        Route::post('/update/status', 'AdvertizementController@update_status')->name('update_status');
    });

    // Block ip route
    Route::prefix('/block/ip')->name('block.ip.')->group(function () {
        Route::get('/', 'BlockIpController@index')->name('index');
        Route::get('/whitelist/{id}', 'BlockIpController@destroy')->name('destroy');
    });

    Route::get('/google-analytics', 'BusinessSettingsController@google_analytics')->name('google_analytics.index');
    Route::get('/google-tagmanager', 'BusinessSettingsController@google_tagmanager')->name('google_tag_manager.index');
    Route::get('/google-recaptcha', 'BusinessSettingsController@google_recaptcha')->name('google_recaptcha.index');
    Route::get('/google-map', 'BusinessSettingsController@google_map')->name('google-map.index');
    Route::get('/google-firebase', 'BusinessSettingsController@google_firebase')->name('google-firebase.index');
    Route::get('/onesignal', 'BusinessSettingsController@onesignal')->name('onesignal.index');

    // Facebook Settings
    Route::get('/facebook-chat', 'BusinessSettingsController@facebook_chat')->name('facebook_chat.index');
    Route::post('/facebook_chat', 'BusinessSettingsController@facebook_chat_update')->name('facebook_chat.update');
    Route::get('/facebook-comment', 'BusinessSettingsController@facebook_comment')->name('facebook-comment');
    Route::post('/facebook-comment', 'BusinessSettingsController@facebook_comment_update')->name('facebook-comment.update');
    Route::post('/facebook_pixel', 'BusinessSettingsController@facebook_pixel_update')->name('facebook_pixel.update');

    Route::post('/env_key_update', 'BusinessSettingsController@env_key_update')->name('env_key_update.update');
    Route::post('/payment_method_update', 'BusinessSettingsController@payment_method_update')->name('payment_method.update');
    Route::post('/google_analytics', 'BusinessSettingsController@google_analytics_update')->name('google_analytics.update');
    Route::post('/google_tagmanager', 'BusinessSettingsController@google_tagmanager_update')->name('google_tag_manager.update');
    Route::post('/google_recaptcha', 'BusinessSettingsController@google_recaptcha_update')->name('google_recaptcha.update');
    Route::post('/google-map', 'BusinessSettingsController@google_map_update')->name('google-map.update');
    Route::post('/google-firebase', 'BusinessSettingsController@google_firebase_update')->name('google-firebase.update');
    Route::post('/onesignal', 'BusinessSettingsController@onesignal_update')->name('onesignal.update');
    // Currency
    Route::get('/currency', 'CurrencyController@currency')->name('currency.index');
    Route::post('/currency/update', 'CurrencyController@updateCurrency')->name('currency.update');
    Route::post('/your-currency/update', 'CurrencyController@updateYourCurrency')->name('your_currency.update');
    Route::get('/currency/create', 'CurrencyController@create')->name('currency.create');
    Route::post('/currency/store', 'CurrencyController@store')->name('currency.store');
    Route::post('/currency/currency_edit', 'CurrencyController@edit')->name('currency.edit');
    Route::post('/currency/update_status', 'CurrencyController@update_status')->name('currency.update_status');

    // Tax
    Route::resource('tax', 'TaxController');
    Route::get('/tax/edit/{id}', 'TaxController@edit')->name('tax.edit');
    Route::get('/tax/destroy/{id}', 'TaxController@destroy')->name('tax.destroy');
    Route::post('tax-status', 'TaxController@change_tax_status')->name('taxes.tax-status');

    Route::get('/verification/form', 'BusinessSettingsController@seller_verification_form')->name('seller_verification_form.index');
    Route::post('/verification/form', 'BusinessSettingsController@seller_verification_form_update')->name('seller_verification_form.update');
    Route::get('/vendor_commission', 'BusinessSettingsController@vendor_commission')->name('business_settings.vendor_commission');
    Route::post('/vendor_commission_update', 'BusinessSettingsController@vendor_commission_update')->name('business_settings.vendor_commission.update');

    Route::resource('/languages', 'LanguageController');
    Route::post('/languages/{id}/update', 'LanguageController@update')->name('languages.update');
    Route::get('/languages/destroy/{id}', 'LanguageController@destroy')->name('languages.destroy');
    Route::post('/languages/update_rtl_status', 'LanguageController@update_rtl_status')->name('languages.update_rtl_status');
    Route::post('/languages/key_value_store', 'LanguageController@key_value_store')->name('languages.key_value_store');

    // App Trasnlation
    Route::post('/languages/app-translations/import', 'LanguageController@importEnglishFile')->name('app-translations.import');
    Route::get('/languages/app-translations/show/{id}', 'LanguageController@showAppTranlsationView')->name('app-translations.show');
    Route::post('/languages/app-translations/key_value_store', 'LanguageController@storeAppTranlsation')->name('app-translations.store');
    Route::get('/languages/app-translations/export/{id}', 'LanguageController@exportARBFile')->name('app-translations.export');

    // website setting
    Route::group(['prefix' => 'website'], function () {
        Route::get('/dashboard', 'WebsiteController@dashboard')->name('website.dashboard');
        Route::get('/footer', 'WebsiteController@footer')->name('website.footer');
        Route::get('/header', 'WebsiteController@header')->name('website.header');
        Route::get('/appearance', 'WebsiteController@appearance')->name('website.appearance');
        Route::get('/global-seo', 'WebsiteController@global_seo')->name('website.global_seo');
        Route::get('/pages', 'WebsiteController@pages')->name('website.pages');
        Route::resource('custom-pages', 'PageController');
        Route::get('/custom-pages/edit/{id}', 'PageController@edit')->name('custom-pages.edit');
        Route::get('/custom-pages/destroy/{id}', 'PageController@destroy')->name('custom-pages.destroy');
    });

    // Module Activator
    Route::post('/module/activator', 'BusinessSettingsController@moduleActivator')->name('module.activator');

    Route::get('/roles/create-copy', 'RoleController@createCopy')->name('roles.createCopy');
    Route::get('/roles/edit-copy/{id}', 'RoleController@editCopy')->name('roles.editeCopy');
    Route::resource('roles', 'RoleController');
    Route::get('/roles/edit/{id}', 'RoleController@edit')->name('roles.edit');
    Route::get('/roles/destroy/{id}', 'RoleController@destroy')->name('roles.destroy');

    Route::resource('purchaseorder', 'PurchaseorderController');
    Route::get('/purchaseorder/print_barcode/{id}', 'PurchaseorderController@print_barcode')->name('purchaseorder.print_barcode');
    Route::post('/purchaseorder/getproductvarient', 'PurchaseorderController@getproductvarient')->name('purchaseorder.getproductvarient');
    Route::post('/purchaseorder/delete_item', 'PurchaseorderController@delete_item')->name('purchaseorder.delete_item');
    Route::get('/purchaseorder/edit/{id}', 'PurchaseorderController@edit')->name('purchaseorder.edit');
    Route::post('/purchaseorder/update/{id}', 'PurchaseorderController@update')->name('purchaseorder.update');
    Route::get('/purchase-report-supplier', 'PurchaseorderController@purchases_by_supplier')->name('reports.purchase.supplier');

    // Stock Adjustment
    Route::resource('stock-adjust', 'StockAdjustController');
    Route::post('/stock-adjust/delete_item', 'StockAdjustController@delete_item')->name('stock-adjust.delete_item');
    // Route::get('/stock-adjust/edit/{id}', 'StockAdjustController@edit')->name('stock-adjust.edit');
    // Route::post('/stock-adjust/update/{id}', 'StockAdjustController@update')->name('stock-adjust.update');
    Route::get('inventory/return-supplier', 'ReturnSupplierController@index')->name('stock-adjust.return_supplier.index');
    Route::post('inventory/return-supplier', 'ReturnSupplierController@store')->name('stock-adjust.return_supplier.store');
    Route::get('inventory/return-supplier/create', 'ReturnSupplierController@create')->name('stock-adjust.return_supplier.create');
    Route::get('inventory/return-supplier/{id}/show', 'ReturnSupplierController@show')->name('stock-adjust.return_supplier.show');

    // Return Order
    Route::get('/return-orders-create', 'OrderReturnController@create')->name('return-orders.create');
    Route::get('/return-orders/{status?}', 'OrderReturnController@index')->name('return-orders.index');
    Route::post('/return-orders', 'OrderReturnController@store')->name('return-orders.store');
    Route::post('/return-orders/update-status', 'OrderReturnController@updateStatus')->name('return-orders.update-status');
    Route::post('/return-orders/bulk-update-status', 'OrderReturnController@bulkUpdateStatus')->name('return-orders.bulk-update-status');
    Route::get('/return-orders/{id}/show', 'OrderReturnController@show')->name('return-orders.show');
    Route::get('/return-orders-ratio', 'OrderReturnController@getReturnRatio')->name('return-orders.ratio');
    Route::get('/get-order-info', 'OrderController@getOrderInfo')->name('getOrderInfo');

    Route::get('/staffs/report', 'StaffController@report')->name('staffs.report');
    Route::get('staffs_ban/{id}', 'StaffController@ban')->name('staffs.ban');
    Route::get('/staffs/{id}/report', 'StaffController@reportById')->name('staffs.report.show');
    Route::post('/staffs/{id}/generate-doc', 'StaffController@generateDocuments')->name('staffs.generate_doc');
    Route::post('/staffs/{id}/send-doc', 'StaffController@sendDocuments')->name('staffs.send_doc');
    Route::resource('staffs', 'StaffController');
    Route::get('/staffs/destroy/{id}', 'StaffController@destroy')->name('staffs.destroy');

    Route::get('/log-reports', 'OrderLogController@index')->name('log-report.index');

    Route::get('/sales-contribution-reports', 'StaffController@salesContributionReports')->name('sales-contribution-reports.index');
    Route::get('/sales-contribution-reports/details', 'StaffController@salesContributionReportDetails')->name('sales-contribution-reports.details');
    Route::get('/coupon-assigned', 'StaffController@couponAssignedDetails')->name('coupon-assigned.details');

    Route::resource('merchants', 'MerchantController');
    Route::get('/merchants/destroy/{id}', 'MerchantController@destroy')->name('merchants.destroy');

    Route::resource('flash_deals', 'FlashDealController');
    Route::get('/flash_deals/edit/{id}', 'FlashDealController@edit')->name('flash_deals.edit');
    Route::get('/flash_deals/destroy/{id}', 'FlashDealController@destroy')->name('flash_deals.destroy');
    Route::post('/flash_deals/update_status', 'FlashDealController@update_status')->name('flash_deals.update_status');
    Route::post('/flash_deals/update_featured', 'FlashDealController@update_featured')->name('flash_deals.update_featured');
    Route::post('/flash_deals/product_discount', 'FlashDealController@product_discount')->name('flash_deals.product_discount');
    Route::post('/flash_deals/product_discount_edit', 'FlashDealController@product_discount_edit')->name('flash_deals.product_discount_edit');
    Route::get('/flash_deals/is_exist_in_any_deals/{id}', 'FlashDealController@is_exist_in_any_deals')->name('flash_deals.is_exist_in_any_deals');

    // Gift Offers
    Route::name('admin.gift_offers.')->group(function () {
        Route::get('/gift_offers', 'Backend\GiftOfferController@index')->name('index');
        Route::get('/gift_offers/create', 'Backend\GiftOfferController@create')->name('create');
        Route::post('/gift_offers/store', 'Backend\GiftOfferController@store')->name('store');
        Route::get('/gift_offers/edit/{id}', 'Backend\GiftOfferController@edit')->name('edit');
        Route::post('/gift_offers/update/{id}', 'Backend\GiftOfferController@update')->name('update');
        Route::get('/gift_offers/destroy/{id}', 'Backend\GiftOfferController@destroy')->name('destroy');
        Route::post('/gift_offers/update_status', 'Backend\GiftOfferController@updateStatus')->name('update_status');
    });

    // Subscribers
    Route::get('/subscribers', 'SubscriberController@index')->name('subscribers.index');
    Route::get('/subscribers/destroy/{id}', 'SubscriberController@destroy')->name('subscriber.destroy');

    // Route::get('/orders', 'OrderController@admin_orders')->name('orders.index.admin');
    // Route::get('/orders/{id}/show', 'OrderController@show')->name('orders.show');
    // Route::get('/sales/{id}/show', 'OrderController@sales_show')->name('sales.show');
    // Route::get('/sales', 'OrderController@sales')->name('sales.index');
    // All Orders
    Route::get('/all_orders', 'OrderController@all_orders')->name('all_orders.index');
    Route::get('/all_orders/{status}', 'OrderController@all_orders')->name('all_orders.status');
    Route::get('/all_orders/{id}/show', 'OrderController@all_orders_show')->name('all_orders.show');
    Route::get('/all_orders/{id}/package', 'OrderController@all_orders_package')->name('all_orders.package');
    Route::get('/orders/export', 'OrderController@export')->name('orders.export');
    Route::get('/orders/bulk_product_download', 'OrderController@bulk_product_download')->name('orders.bulk_product_download');
    Route::get('/orders/get-call-logs/{id}', 'OrderController@getOrdersCallLogs')->name('orders.get-call-logs');
    Route::get('/orders/get-order-logs/{id}', 'OrderController@getOrderLogs')->name('orders.get-order-logs');
    Route::get('/orders/get-customer-success-rate/{id}', 'OrderController@getCustomerSuccessRate')->name('orders.get-customer-success-rate');
    Route::get('/orders/checkProductStock/{id}', 'OrderController@checkProductStock')->name('orders.checkProductStock');
    Route::get('courier-success-rate', 'OrderController@getCourierSuccessRate')->name('get-courier-success-rate');
    Route::get('/orders/get-recent-orders/{id}', 'OrderController@getRecentOrders')->name('orders.get-recent-orders');
    Route::get('/orders/get-status-count', 'OrderController@getStatusCount')->name('orders.get-status-count');

    Route::any('/orders/{order}/extend-lock', 'OrderController@extendLock')->name('orders.extend-lock');
    Route::post('/orders/{order}/unlock', 'OrderController@unlock')->name('orders.unlock');
    Route::get('remove-paid-amount/{id}', 'OrderController@removePaidAmount')->name('orders.removePaidAmount');

    Route::get('order-lookup/{code}', 'OrderController@orderLookup')->name('orders.lookup');

    Route::get('get-order-summary/{id}', 'OrderController@getOrderSummary')->name('orders.get_order_summary');

    Route::get('/check-expire-date', 'OrderController@checkExpireDate')->name('orders.check_expire_date');
    Route::post('forcely-mark-as-packaged', 'OrderController@forcelyMarkAsPackaged')->name('orders.forcely_mark_as_packaged');

    // Call Logs
    Route::post('/call-logs/store', 'CallLogController@store')->name('call-logs.store');
    Route::get('/call-logs/{id}/delete', 'CallLogController@destroy')->name('call-logs.destroy');

    // Order Feedback Store
    Route::post('/order-feedback/store', 'OrderController@storeFeedback')->name('orders.feedback.store');

    // Order Call Logs
    // Route::get('/order-call-logs', 'OrderCallLogController@index')->name('order-call-logs.index');
    // Route::get('/order-call-logs/{id}/delete', 'OrderCallLogController@destroy')->name('order-call-logs.destroy');
    // Route::get('/order-call-logs/add', 'OrderCallLogController@addCallLog')->name('order-call-logs.add');
    // Route::post('/order-call-logs/store', 'OrderCallLogController@store')->name('order-call-logs.store');

    // Purchase Order
    // Route::get('/purchaseorder/{id}/show', 'PurchaseorderController@single_view')->name('purchaseorder.show');

    // Sticker Label Print
    Route::get('invoice_label_print', 'InvoiceController@generateLabels')->name('invoice.sticker_label_print');

    // Inhouse Orders
    Route::get('/inhouse-orders', 'OrderController@admin_orders')->name('inhouse_orders.index');
    Route::get('/inhouse-orders/{status}', 'OrderController@admin_orders')->name('inhouse_orders.status');
    Route::get('/inhouse-orders/{id}/show', 'OrderController@show')->name('inhouse_orders.show');

    // Seller Orders
    Route::get('/seller_orders', 'OrderController@seller_orders')->name('seller_orders.index');
    Route::get('/seller_orders/{id}/show', 'OrderController@seller_orders_show')->name('seller_orders.show');

    Route::post('/bulk-order-status', 'OrderController@bulk_order_status')->name('bulk-order-status');

    // Pickup point orders
    Route::get('orders_by_pickup_point', 'OrderController@pickup_point_order_index')->name('pick_up_point.order_index');
    Route::get('/orders_by_pickup_point/{id}/show', 'OrderController@pickup_point_order_sales_show')->name('pick_up_point.order_show');

    Route::get('/orders/destroy/{id}', 'OrderController@destroy')->name('orders.destroy');
    Route::post('/bulk-order-delete', 'OrderController@bulk_order_delete')->name('bulk-order-delete');

    Route::post('/pay_to_seller', 'CommissionController@pay_to_seller')->name('commissions.pay_to_seller');

    // Reports
    Route::get('/stock_report', 'ReportController@stock_report')->name('stock_report.index');
    Route::get('/in_house_sale_report', 'ReportController@in_house_sale_report')->name('in_house_sale_report.index');
    Route::get('/seller_sale_report', 'ReportController@seller_sale_report')->name('seller_sale_report.index');
    Route::get('/wish_report', 'ReportController@wish_report')->name('wish_report.index');
    Route::get('/user_search_report', 'ReportController@user_search_report')->name('user_search_report.index');
    Route::get('/wallet-history', 'ReportController@wallet_transaction_history')->name('wallet-history.index');
    Route::get('/purchase_order_report', 'ReportController@purchase_order_report')->name('purchase_order_report');
    Route::get('/top-selling-products', 'ReportController@topSellingProducts')->name('admin.topSellingProducts');
    Route::get('/not-selling-products', 'ReportController@notSellingProducts')->name('admin.notSellingProducts');
    Route::get('/not-selling-products/export', 'ReportController@notSellingProductsExport')->name('admin.notSellingProducts.export');
    Route::get('/shipping-scanned-report', 'ReportController@shippingScannedReport')->name('admin.reports.shippingScannedReport');
    Route::get('/expire-products-report', 'ReportController@expireProductsReport')->name('admin.expireProductsReport');
    Route::get('/expire-products-export', 'ReportController@expireProductsExport')->name('admin.expireProductsReport.export');

    // Order Cancellation Report
    Route::get('/order-cancellation-report', 'OrderCancellationController@index')->name('admin.orderCancellationReport.index');
    Route::get('/order-cancellation-ratio', 'OrderCancellationController@getCancellationRatio')->name('admin.orderCancellationReport.ratio');

    // Product Visits Report
    Route::get('/product-visits-report', 'ProductVisitController@index')->name('admin.productVisitsReport.index');
    // Order Tracking Report
    Route::get('/order-tracking-report', 'OrderTrackController@index')->name('admin.orderTrackingReport.index');
    Route::get('/order-analytics-data', 'OrderTrackController@reports')->name('admin.order_analytics_graphs.report');

    // SMS Log Report
    Route::get('/sms-log-report', 'SmsLogController@index')->name('admin.smsLogReport.index');
    Route::get('/coupon-usage-report', 'CouponUsageController@index')->name('admin.couponUsageReport.index');

    // Orders Loss Profit Report
    Route::get('/orders-loss-profit-report', 'OrderController@lossProfitReport')->name('admin.ordersLossProfitReport.index');

    // Blog Section
    Route::resource('blog-category', 'BlogCategoryController');
    Route::get('/blog-category/destroy/{id}', 'BlogCategoryController@destroy')->name('blog-category.destroy');
    Route::resource('blog', 'BlogController');
    Route::get('/blog/destroy/{id}', 'BlogController@destroy')->name('blog.destroy');
    Route::post('/blog/change-status', 'BlogController@change_status')->name('blog.change-status');

    // Coupons
    Route::put('coupons/touch', 'CouponController@touch')->name('coupons.touch');
    Route::resource('coupon', 'CouponController');
    Route::get('/coupon/destroy/{id}', 'CouponController@destroy')->name('coupon.destroy');

    // Reviews
    Route::get('/reviews/{id}/delete', 'ReviewController@destroy')->name('reviews.delete');
    Route::post('/reviews/published', 'ReviewController@updatePublished')->name('reviews.published');
    Route::post('/reviews/featured', 'ReviewController@updateFeatured')->name('reviews.featured');
    Route::post('/reviews/save', 'ReviewController@adminStore')->name('reviews.admin_store');
    Route::get('/reviews/fetch-products', 'ReviewController@fetchProducts')->name('reviews.fetch_products');
    Route::get('/reviews/fetch-customers', 'ReviewController@fetchCustomers')->name('reviews.fetch_customers');
    Route::post('/reviews/bulk-delete', 'ReviewController@bulkDelete')->name('reviews.bulk_delete');
    Route::resource('/reviews', 'ReviewController')->except(['store', 'destroy']);
    // Route::get('/reviews', 'ReviewController@index')->name('reviews.index');

    // Videos & Playlists
    Route::put('video-playlists/touch', 'VideoPlaylistController@touch')->name('video-playlists.touch');
    Route::post('video-playlists/bulk-status-update', 'VideoPlaylistController@bulkStatusUpdate')->name('video-playlists.bulk-status-update');
    Route::get('video-playlists/get-playlists', 'VideoPlaylistController@getPlaylists')->name('video-playlists.getPlaylists');
    Route::get('video-playlists/{id}/destroy', 'VideoPlaylistController@destroy')->name('video-playlists.destroy');
    Route::resource('video-playlists', 'VideoPlaylistController')->except(['show', 'destroy']);

    Route::put('videos/touch', 'VideoController@touch')->name('videos.touch');
    Route::post('videos/bulk-delete', 'VideoController@bulkDelete')->name('videos.bulk-delete');
    Route::post('videos/bulk-status-update', 'VideoController@bulkStatusUpdate')->name('videos.bulk-status-update');
    Route::get('videos/{id}/edit', 'VideoController@edit')->name('videos.edit');
    Route::get('videos/{id}/destroy', 'VideoController@destroy')->name('videos.destroy');
    Route::put('videos/{id}/update', 'VideoController@update')->name('videos.update');
    Route::resource('videos', 'VideoController')->except(['show', 'destroy']);

    // Pickup_Points
    Route::resource('pick_up_points', 'PickupPointController');
    Route::get('/pick_up_points/edit/{id}', 'PickupPointController@edit')->name('pick_up_points.edit');
    Route::get('/pick_up_points/destroy/{id}', 'PickupPointController@destroy')->name('pick_up_points.destroy');

    // conversation of seller customer
    Route::get('conversations', 'ConversationController@admin_index')->name('conversations.admin_index');
    Route::get('conversations/{id}/show', 'ConversationController@admin_show')->name('conversations.admin_show');

    Route::post('/sellers/profile_modal', 'SellerController@profile_modal')->name('sellers.profile_modal');
    Route::post('/sellers/approved', 'SellerController@updateApproved')->name('sellers.approved');

    Route::resource('attributes', 'AttributeController');
    Route::get('/attributes/edit/{id}', 'AttributeController@edit')->name('attributes.edit');
    Route::get('/attributes/destroy/{id}', 'AttributeController@destroy')->name('attributes.destroy');

    Route::resource('reviewcomments', 'ReviewCommentsController');
    Route::get('/reviewcomments/edit/{id}', 'ReviewCommentsController@edit')->name('reviewcomments.edit');
    Route::get('/reviewcomments/destroy/{id}', 'ReviewCommentsController@destroy')->name('reviewcomments.destroy');

    // Attribute Value
    Route::post('/store-attribute-value', 'AttributeController@store_attribute_value')->name('store-attribute-value');
    Route::get('/edit-attribute-value/{id}', 'AttributeController@edit_attribute_value')->name('edit-attribute-value');
    Route::post('/update-attribute-value/{id}', 'AttributeController@update_attribute_value')->name('update-attribute-value');
    Route::get('/destroy-attribute-value/{id}', 'AttributeController@destroy_attribute_value')->name('destroy-attribute-value');

    // Colors
    Route::get('/colors', 'AttributeController@colors')->name('colors');
    Route::post('/colors/store', 'AttributeController@store_color')->name('colors.store');
    Route::get('/colors/edit/{id}', 'AttributeController@edit_color')->name('colors.edit');
    Route::post('/colors/update/{id}', 'AttributeController@update_color')->name('colors.update');
    Route::get('/colors/destroy/{id}', 'AttributeController@destroy_color')->name('colors.destroy');

    Route::resource('addons', 'AddonController');
    Route::post('/addons/activation', 'AddonController@activation')->name('addons.activation');

    Route::get('/customer-bulk-upload/index', 'CustomerBulkUploadController@index')->name('customer_bulk_upload.index');
    Route::post('/bulk-user-upload', 'CustomerBulkUploadController@user_bulk_upload')->name('bulk_user_upload');
    Route::post('/bulk-customer-upload', 'CustomerBulkUploadController@customer_bulk_file')->name('bulk_customer_upload');
    Route::get('/user', 'CustomerBulkUploadController@pdf_download_user')->name('pdf.download_user');
    // Customer Package

    Route::resource('customer_packages', 'CustomerPackageController');
    Route::get('/customer_packages/edit/{id}', 'CustomerPackageController@edit')->name('customer_packages.edit');
    Route::get('/customer_packages/destroy/{id}', 'CustomerPackageController@destroy')->name('customer_packages.destroy');

    // Classified Products
    Route::get('/classified_products', 'CustomerProductController@customer_product_index')->name('classified_products');
    Route::post('/classified_products/published', 'CustomerProductController@updatePublished')->name('classified_products.published');

    // Shipping Configuration
    Route::get('/shipping_configuration', 'BusinessSettingsController@shipping_configuration')->name('shipping_configuration.index');
    Route::post('/shipping_configuration/update', 'BusinessSettingsController@shipping_configuration_update')->name('shipping_configuration.update');

    // Route::resource('pages', 'PageController');
    // Route::get('/pages/destroy/{id}', 'PageController@destroy')->name('pages.destroy');

    Route::resource('countries', 'CountryController');
    Route::post('/countries/status', 'CountryController@updateStatus')->name('countries.status');

    Route::resource('states', 'StateController');
    Route::post('/states/status', 'StateController@updateStatus')->name('states.status');

    Route::resource('cities', 'CityController');
    Route::get('/cities/edit/{id}', 'CityController@edit')->name('cities.edit');
    Route::get('/cities/destroy/{id}', 'CityController@destroy')->name('cities.destroy');
    Route::post('/cities/status', 'CityController@updateStatus')->name('cities.status');

    Route::resource('areas', 'AreaController');
    Route::get('/areas/edit/{id}', 'AreaController@edit')->name('areas.edit');
    Route::get('/areas/destroy/{id}', 'AreaController@destroy')->name('areas.destroy');
    Route::post('/areas/status', 'AreaController@updateStatus')->name('areas.status');

    // Route::resource('shipping-method', 'ShippingMethodController');
    Route::get('/shipping-method', 'ShippingMethodController@index')->name('shipping_method.index');
    Route::post('/shipping-method/store', 'ShippingMethodController@store')->name('shipping_method.store');
    Route::get('/shipping-method/edit/{id}', 'ShippingMethodController@edit')->name('shipping_method.edit');
    Route::patch('/shipping-method/update/{id}', 'ShippingMethodController@update')->name('shipping_method.update');
    Route::get('/shipping-method/destroy/{id}', 'ShippingMethodController@destroy')->name('shipping_method.destroy');
    Route::post('/shipping-method/status', 'ShippingMethodController@updateStatus')->name('shipping_method.status');

    Route::resource('shipping_zone', 'ShippingZoneController');
    Route::get('/shipping_zone/edit/{id}', 'ShippingZoneController@edit')->name('shipping_zone.edit');
    Route::get('/shipping_zone/destroy/{id}', 'ShippingZoneController@destroy')->name('shipping_zone.destroy');
    Route::get('/shipping_zone/rates/{id}', 'ShippingZoneController@rates')->name('shipping_zone.rates');
    Route::post('/shipping_zone/update_rates', 'ShippingZoneController@updateRates')->name('shipping_zone.update-rates');

    Route::view('/system/update', 'backend.system.update')->name('system_update');
    Route::view('/system/server-status', 'backend.system.server_status')->name('system_server');

    // uploaded files
    Route::any('/uploaded-files/file-info', 'AizUploadController@file_info')->name('uploaded-files.info');
    Route::resource('/uploaded-files', 'AizUploadController');
    Route::get('/uploaded-files/destroy/{id}', 'AizUploadController@destroy')->name('uploaded-files.destroy');

    Route::get('/all-notification', 'NotificationController@index')->name('admin.all-notification');

    Route::get('/clear-cache', 'AdminController@clearCache')->name('cache.clear');

    // Customer Group
    Route::get('/customer-group', 'CustomergroupController@index')->name('customer.group');
    Route::get('/customer-group/create', 'CustomergroupController@create')->name('customer.group.create');
    Route::post('/customer-group/store', 'CustomergroupController@store')->name('customer.group.store');
    Route::get('/customer-group/edit/{id}', 'CustomergroupController@edit')->name('customer.group.edit');
    Route::post('/customer-group/update/{id}', 'CustomergroupController@update')->name('customer.group.update');
    Route::get('/customer-group/delete/{id}', 'CustomergroupController@delete')->name('customer.group.delete');
    Route::post('/customer-group/update_status', 'CustomergroupController@update_status')->name('customer.group.update_status');
    Route::post('/customer-group/update_delivery_discount_status', 'CustomergroupController@update_delivery_discount_status')->name('customer.group.update_delivery_discount_status');

    Route::post('/customer_group', 'CustomergroupController@customer_group')->name('customer_group');

    Route::get('/test', 'ReportController@test')->name('test');

    Route::get('/sales-report', 'ReportController@sales_report')->name('sales.report');
    Route::get('/sales-report/export', 'ReportController@export_sales_report')->name('sales_report.export');
    Route::get('/sales-report/showroom', 'ShowroomReportController')->name('sales_report.showroom');
    Route::get('/products-stock-report', 'ReportController@products_stock_new')->name('products.stock.new');
    Route::get('/products-stock-report-new', 'ReportController@products_stock_latest')->name('products.stock.latest');
    Route::get('/brand-stock-report', 'ReportController@brandWiseStockReport')->name('brand.stock.report');
    Route::get('/stock-report-product', 'ReportController@stock_by_product')->name('reports.stock.product');
    Route::get('/stock-report-product-new', 'ReportController@stock_by_product_new')->name('reports.stock.product.new');

    // Invoice or Order Upadate Routes
    Route::get('/invoice/{id}/edit', 'InvoiceUpdateController@index')->name('invoice.edit');
    // Route::get('/invoice/productsOld', 'InvoiceUpdateController@searchOld')->name('invoice.search_product');
    Route::get('/invoice/products', 'InvoiceUpdateController@search')->name('invoice.search_product');
    Route::post('/add-to-cart-invoice', 'InvoiceUpdateController@addToCart')->name('invoice.addToCart');
    Route::post('/remove-from-cart-invoice', 'InvoiceUpdateController@removeFromCart')->name('invoice.removeFromCart');
    Route::post('/update-quantity-cart-invoice', 'InvoiceUpdateController@updateQuantity')->name('invoice.updateQuantity');
    Route::post('/invoice/setDiscount', 'InvoiceUpdateController@setDiscount')->name('invoice.setDiscount');
    Route::post('/invoice/setShipping', 'InvoiceUpdateController@setShipping')->name('invoice.setShipping');
    Route::post('/invoice/set-shipping-address', 'InvoiceUpdateController@set_shipping_address')->name('invoice.set-shipping-address');
    Route::post('/invoice/get_shipping_address', 'InvoiceUpdateController@getShippingAddress')->name('invoice.getShippingAddress');
    Route::post('/invoice-order', 'InvoiceUpdateController@order_update')->name('invoice.order_update');
    Route::post('/invoice-edit-order-summary', 'InvoiceUpdateController@get_order_update_summary')->name('invoice.getOrderSummary');
    Route::post('/invoice-partial-pay', 'InvoiceUpdateController@partialPayment')->name('invoice.partial-pay');

    // Shipping Info Update
    Route::post('/update-shipping-info', 'InvoiceUpdateController@updateShippingInfo')->name('invoice.updateShippingInfo');

    // refund request
    Route::get('/refund_request/all', 'RefundRequestController@requests')->name('refund_request.all');
    Route::get('/refund_request/{status}', 'RefundRequestController@requests')->name('refund_request.status');
    Route::post('/refund_request/accept', 'RefundRequestController@accept')->name('refund_request.accept');
    Route::post('/refund_request/cancel', 'RefundRequestController@cancel')->name('refund_request.cancel');

    Route::get('/messages', 'FirebaseMessageController@index')->name('support.live-chat');

    /**
     * User Notification Route
     */
    // Route::get('/user-notification','UserNotificationController@index')->name('user_notification.index');
    Route::resource('user-notification', 'UserNotificationController');
    // Reward Point Routes
    Route::get('/reward-point-settings', 'RewardPointController@rewardPointSettings')->name('business_settings.rewardPointSettings');
    Route::post('/business-settings/reward-point-settings', 'RewardPointController@updateRewardPointSettings')->name('business_settings.updateRewardPointSettings');
    Route::post('/reward-point/update/earn-reawrd-action/{id}', 'RewardPointController@editEarnRewardAction')->name('reward-point.editEarnRewardAction');
    Route::post('/reward-point/update/redeem-reawrd-action/{id}', 'RewardPointController@editRedeemRewardAction')->name('reward-point.editRedeemRewardAction');

    // Automated Shipping Method API Integration
    Route::get('/pathao-settings', 'PathaoController@settings')->name('shipping.pathao.settings');
    Route::get('/matched-pathao-ship-areas', 'PathaoController@generateOrMatchAreas')->name('shipping.pathao.areas.generate');
    Route::post('/pathao/serach-area', 'PathaoController@searchPathaoArea')->name('shipping.pathao.areas.search');
    Route::post('/system/serach-area', 'PathaoController@searchSystemArea')->name('shipping.system.areas.search');
    Route::post('/save-matched-patha-areas', 'PathaoController@saveMatchedAreas')->name('shipping.matched.areas.save');
    Route::post('/save-single-matched-pathao-area', 'PathaoController@saveSingleMatchedArea')->name('shipping.matched.single.area.save');
    Route::post('/delete-single-matched-pathao-area', 'PathaoController@deleteSingleMatchedArea')->name('shipping.matched.single.area.delete');

    Route::get('/orders/process-to-ship', 'OrderController@processToShip')->name('orders.shipping.process');
    Route::post('/orders/process-to-ship/save', 'OrderController@processToShipSave')->name('orders.shipping.process.save');

    // Order Note
    Route::get('/order-notes/{id}', 'OrderController@getNotes')->name('orders.get-notes');
    Route::post('/order-notes/store', 'OrderController@addNote')->name('orders.add-note');
    Route::post('/order-notes/delete', 'OrderController@deleteNote')->name('orders.delete-note');

    // Accounting routes
    Route::group(['prefix' => 'accounts'], function () {
        Route::resource('heads', 'Accounts\ACCHeadController');
        // Route::get('heads/search', 'Accounts\ACCHeadController@search')->name('accounts.heads.search');
        Route::get('heads/destroy/{id}', 'Accounts\ACCHeadController@destroy')->name('accounts.heads.destroy');
        Route::post('heads/get_subheads', 'Accounts\ACCHeadController@get_subheads')->name('accounts.heads.get_subheads');

        Route::resource('banks', 'Accounts\ACCBankController');
        Route::get('banks/search', 'Accounts\ACCBankController@search')->name('accounts.banks.search');
        Route::get('banks/destroy/{id}', 'Accounts\ACCBankController@destroy')->name('accounts.banks.destroy');

        Route::get('payments/create', 'PaymentController@paybill')->name('accounts.payments.create');
        Route::post('payments/save', 'PaymentController@saveInvoicePayment')->name('accounts.payments.save');
        Route::post('payments/bulk/save', 'PaymentController@saveBulkInvoicePayment')->name('accounts.payments.bulk.save');
        Route::get('payments/get_unpaid_bills', 'PurchaseorderController@get_due_invoices_by_supplier')->name('accounts.payments.get_unpaid_bills');

        // Reports
        Route::get('reports/ledger', 'Accounts\ReportController@ledger')->name('accounts.reports.ledger');
        Route::get('reports/sub_ledger', 'Accounts\ReportController@sub_head_ledger')->name('accounts.reports.sub_head_ledger');
        Route::get('reports/trial_balance', 'Accounts\ReportController@trial_balance')->name('accounts.reports.trial_balance');
        Route::get('reports/daily_report', 'Accounts\ReportController@daily_report')->name('accounts.reports.daily_report');
        Route::get('reports/expense_report', 'Accounts\ReportController@expense_report')->name('accounts.reports.expense_report');
        Route::get('reports/expense_report_chart', 'Accounts\ReportController@expense_report_chart')->name('accounts.reports.expense_report_chart');

        // Voucher Entries
        Route::get('voucher_entry', 'Accounts\VoucherController@create')->name('accounts.voucher.create');
        Route::post('voucher_entry/save', 'Accounts\VoucherController@save')->name('accounts.voucher.save');
        Route::get('vouchers', 'Accounts\VoucherController@index')->name('accounts.vouchers.index');
        Route::get('vouchers/{vno}', 'Accounts\VoucherController@show')->name('accounts.vouchers.show');
    });

    // Supplier Route
    Route::get('/suppliers', 'SupplierController@index')->name('supplier.index');
    Route::any('/suppliers/create', 'SupplierController@create')->name('supplier.create');
    Route::get('/suppliers/details/{id}', 'SupplierController@details')->name('suppliers.details');
    Route::get('/suppliers/edit/{id}', 'SupplierController@edit')->name('supplier.edit');
    Route::post('/suppliers/update/{id}', 'SupplierController@update')->name('supplier.update');
    Route::get('/suppliers/delete/{id}', 'SupplierController@delete')->name('supplier.delete');

    // Upcoming Delivery Route
    Route::get('/upcoming_delivery', 'OrderController@upcoming_delivery')->name('upcoming.delivery');

    // Shipping Discounts
    Route::resource('ship_discounts', 'ShippingDiscountController');
    Route::get('/ship_discount/destroy/{id}', 'ShippingDiscountController@destroy')->name('ship_discount.destroy');
    Route::post('/ship_discount/get_selections', 'ShippingDiscountController@get_discount_dropdown')->name('ship_discount.get_selections');
    Route::post('/ship_discounts/status', 'ShippingDiscountController@change_status')->name('ship_discounts.status');

    Route::get('/edit-parent-category-content/{id}', 'CategoryController@editCategoryContent')->name('admin.categories.edit-content');
    Route::post('/update-parent-category-content/{id}', 'CategoryController@updateCategoryContent')->name('admin.categories.update-content');

    // Holiday Calendar
    Route::get('/holidays', [\App\Http\Controllers\Admin\HolidayController::class, 'index'])->name('admin.holidays.index');
    Route::get('/holidays/events', [\App\Http\Controllers\Admin\HolidayController::class, 'events'])->name('admin.holidays.events');
    Route::post('/holidays/bulk', [\App\Http\Controllers\Admin\HolidayController::class, 'storeBulk'])->name('admin.holidays.bulk');
    Route::post('/holidays', [\App\Http\Controllers\Admin\HolidayController::class, 'store'])->name('admin.holidays.store');
    Route::put('/holidays/{holiday}', [\App\Http\Controllers\Admin\HolidayController::class, 'update'])->name('admin.holidays.update');
    Route::delete('/holidays/{holiday}', [\App\Http\Controllers\Admin\HolidayController::class, 'destroy'])->name('admin.holidays.destroy');

    Route::fallback([\App\Http\Controllers\AdminController::class, 'not_found']);

});
