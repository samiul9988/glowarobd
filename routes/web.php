<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Pathao Webhook
Route::post('webhook/pathao/status-update', 'PathaoController@updateOrderStatus');
Route::post('/webhooks/pathao', 'PathaoCallbackController')->name('webhooks.pathao');

Route::get('/refresh-csrf', 'HomeController@csrfToken');

Route::post('/aiz-uploader', 'AizUploadController@show_uploader');
Route::post('/aiz-uploader/upload', 'AizUploadController@upload');
Route::get('/aiz-uploader/get_uploaded_files', 'AizUploadController@get_uploaded_files');
Route::post('/aiz-uploader/get_file_by_ids', 'AizUploadController@get_preview_files');
Route::get('/aiz-uploader/get_file_by_id', 'AizUploadController@get_preview_file')->name('get_preview_file');
Route::get('/aiz-uploader/download/{id}', 'AizUploadController@attachment_download')->name('download_attachment');

Route::post('get-user-by-id', 'CustomerController@getUserInfoById');

Route::get('/export/customers', 'ExportController@export');

Route::post('/language', 'LanguageController@changeLanguage')->name('language.change');
Route::post('/currency', 'CurrencyController@changeCurrency')->name('currency.change');

Route::get('test', 'TestController@test');
Route::get('test2', 'TestController@test2');
Route::get('msearch', 'TestController@search');
Route::get('/test-broadcast', 'TestController@event');

Auth::routes(['verify' => true]);
Route::get('/logout', '\App\Http\Controllers\Auth\LoginController@logout');
Route::get('/email/resend', 'Auth\VerificationController@resend')->name('verification.resend');
Route::get('/verification-confirmation/{code}', 'Auth\VerificationController@verification_confirmation')->name('email.verification.confirmation');

Route::get('users/verify-otp', 'Auth\LoginController@verify_otp_form')->name('otp.verify_form')->middleware('guest');
Route::post('users/verify-and-login', 'Auth\LoginController@verify_and_login')->name('otp.verify_and_login')->middleware('guest');

Route::middleware(['feature.activation:enable_application_management,enable_jobs_management'])->group(function () {
    Route::get('careers', 'JobPostController@careers')->name('job_posts.careers');
    Route::get('jobs/{slug}', 'JobPostController@show')->name('job_posts.show');
    Route::post('jobs/apply', 'JobApplicationController@store')->name('job_applications.store');
});

Route::group(['middleware' => ['auth']], function () {
    Route::post('/product/store/', 'ProductController@store')->name('products.store');
    Route::post('/product/update/{id}', 'ProductController@update')->name('products.update');
    Route::get('/product/destroy/{id}', 'ProductController@destroy')->name('products.destroy');
    Route::get('/product/duplicate/{id}', 'ProductController@duplicate')->name('products.duplicate');
    Route::post('/product/sku_combination', 'ProductController@sku_combination')->name('products.sku_combination');
    Route::post('/product/sku_combination_edit', 'ProductController@sku_combination_edit')->name('products.sku_combination_edit');
    Route::post('/product/seller/featured', 'ProductController@updateSellerFeatured')->name('products.seller.featured');
    Route::post('/product/published', 'ProductController@updatePublished')->name('products.published');

    Route::post('/product/add-more-choice-option', 'ProductController@add_more_choice_option')->name('products.add-more-choice-option');

    Route::get('invoice/{order_id}', 'InvoiceController@invoice_download')->name('invoice.download');
    Route::get('invoice_bulk_download', 'InvoiceController@invoice_bulk_download')->name('invoice.invoice_bulk_download');
    Route::get('invoice_bulk_shipping_download', 'InvoiceController@generateLabels')->name('invoice.invoice_bulk_shipping_download');
    Route::get('pos_order_invoice_label/{id}', 'InvoiceController@posInvoiceLabel')->name('invoice.pos_invoice_label');

    Route::resource('orders', 'OrderController');
    Route::get('/orders/destroy/{id}', 'OrderController@destroy')->name('orders.destroy');
    Route::post('/orders/details', 'OrderController@order_details')->name('orders.details');
    Route::post('/orders/update_delivery_status', 'OrderController@update_delivery_status')->name('orders.update_delivery_status');
    Route::post('/orders/update_payment_status', 'OrderController@update_payment_status')->name('orders.update_payment_status');
    Route::post('/orders/delivery-boy-assign', 'OrderController@assign_delivery_boy')->name('orders.delivery-boy-assign');

    // Route::resource('/reviews', 'ReviewController');

    Route::resource('/withdraw_requests', 'SellerWithdrawRequestController');
    Route::get('/withdraw_requests_all', 'SellerWithdrawRequestController@request_index')->name('withdraw_requests_all');
    Route::post('/withdraw_request/payment_modal', 'SellerWithdrawRequestController@payment_modal')->name('withdraw_request.payment_modal');
    Route::post('/withdraw_request/message_modal', 'SellerWithdrawRequestController@message_modal')->name('withdraw_request.message_modal');

    Route::resource('conversations', 'ConversationController');
    Route::get('/conversations/destroy/{id}', 'ConversationController@destroy')->name('conversations.destroy');
    Route::post('conversations/refresh', 'ConversationController@refresh')->name('conversations.refresh');
    Route::resource('messages', 'MessageController');

    // Product Bulk Upload
    Route::get('/product-bulk-upload/index', 'ProductBulkUploadController@index')->name('product_bulk_upload.index');
    Route::post('/bulk-product-upload', 'ProductBulkUploadController@bulk_upload')->name('bulk_product_upload');
    Route::get('/product-csv-download/{type}', 'ProductBulkUploadController@import_product')->name('product_csv.download');
    Route::get('/vendor-product-csv-download/{id}', 'ProductBulkUploadController@import_vendor_product')->name('import_vendor_product.download');
    Route::group(['prefix' => 'bulk-upload/download'], function () {
        Route::get('/category', 'ProductBulkUploadController@pdf_download_category')->name('pdf.download_category');
        Route::get('/brand', 'ProductBulkUploadController@pdf_download_brand')->name('pdf.download_brand');
        Route::get('/seller', 'ProductBulkUploadController@pdf_download_seller')->name('pdf.download_seller');
    });

    // Product Export
    Route::get('/product-bulk-export', 'ProductBulkUploadController@export')->name('product_bulk_export.index');

    Route::resource('digitalproducts', 'DigitalProductController');
    Route::get('/digitalproducts/edit/{id}', 'DigitalProductController@edit')->name('digitalproducts.edit');
    Route::get('/digitalproducts/destroy/{id}', 'DigitalProductController@destroy')->name('digitalproducts.destroy');
    Route::get('/digitalproducts/download/{id}', 'DigitalProductController@download')->name('digitalproducts.download');

    // Reports
    Route::get('/commission-log', 'ReportController@commission_history')->name('commission-log.index');

    // Coupon Form
    Route::get('/coupon/get_assignee', 'CouponController@getAssignee')->name('coupon.get_assignee');
    Route::post('/coupon/get_form', 'CouponController@get_coupon_form')->name('coupon.get_coupon_form');
    Route::post('/coupon/get_form_edit', 'CouponController@get_coupon_form_edit')->name('coupon.get_coupon_form_edit');
});

Route::post('/get-city', 'CityController@get_city')->name('get-city');

Route::get('/checkout/continue-as-guest', 'CheckoutController@continueAsGuest')->name('checkout.continue_as_guest');

// Address
Route::post('/get-states', 'AddressController@getStates')->name('get-state');
Route::post('/get-cities', 'AddressController@getCities')->name('get-city');
Route::post('/get-areas', 'AddressController@getAreas')->name('get-area');

Route::middleware(dynamic_web_middlewares())->group(function () {
    Route::resource('addresses', 'AddressController')->only(['store', 'edit']);
    Route::post('/addresses/update/{id}', 'AddressController@update')->name('addresses.update');
    Route::get('/addresses/destroy/{id}', 'AddressController@destroy')->name('addresses.destroy');
    Route::get('/addresses/set_default/{id}', 'AddressController@set_default')->name('addresses.set_default');
});

Route::get('/sellerpolicy', 'HomeController@sellerpolicy')->name('sellerpolicy');
Route::get('/returnpolicy', 'HomeController@returnpolicy')->name('returnpolicy');
Route::get('/supportpolicy', 'HomeController@supportpolicy')->name('supportpolicy');
Route::get('/terms', 'HomeController@terms')->name('terms');
Route::get('/privacypolicy', 'HomeController@privacypolicy')->name('privacypolicy');
Route::get('/about-us', 'HomeController@aboutus')->name('about-us');

// Blog Section
Route::get('/blog', 'BlogController@all_blog')->name('blog');
Route::get('/blog/{slug}', 'BlogController@blog_details')->name('blog.details');
Route::get('/blog/category/{slug}', 'BlogController@blogByCategory')->name('blog.category');

Route::get('/robots.txt', function () {
    if (app()->environment('staging', 'local')) {
        return "User-agent: *\nDisallow: /";
    }

    return "User-agent: *\nAllow: /";
});

Route::get('/.well-known/{fileName}', 'HomeController@wellKnownFile');

Route::middleware('is_disable_web_routes')->group(function () {
    Route::get('/redis-test', 'RedisController@testRedis');

    Route::get('/redis-tag', 'RedisController@testTag');
    Route::get('/redis-safe-tag', 'RedisController@testSafeCache');

    Route::get('/flush-redis-tag', 'RedisController@flushTag');
    Route::get('/flush-safe-redis-tag', 'RedisController@flushSafeCacheTag');

    Route::get('/redis-session', 'RedisController@testSession');

    Route::get('/ip-blocked', 'BlockIpController@blockedIp')->name('ip.blocked');

    Route::post('/update-header', 'HomeController@updateHeader')->name('update-header');

    // Get all Collection design
    Route::get('/collection-design', 'CollectionDesignController@show')->name('collection-design');
    // Create collection design
    Route::post('/collection-design/store', 'CollectionDesignController@store')->name('collection-design.store');

    Route::get('/email_change/callback', 'HomeController@email_change_callback')->name('email_change.callback');
    Route::post('/password/reset/email/submit', 'HomeController@reset_password_with_code')->name('password.update');

    Route::get('/social-login/redirect/{provider}', 'Auth\LoginController@redirectToProvider')->name('social.login');
    Route::get('/social-login/{provider}/callback', 'Auth\LoginController@handleProviderCallback')->name('social.callback');
    Route::get('/users/login', 'HomeController@login')->name('user.login');

    Route::get('/users/registration', 'HomeController@registration')->name('user.registration');
    // Route::post('/users/login', 'HomeController@user_login')->name('user.login.submit');
    Route::post('/users/login/cart', 'HomeController@cart_login')->name('cart.login.submit');

    // Home Page — replaced with new Next.js frontend
    Route::get("/", "HomeController@index")->name("home");
    Route::get('/home/section/featured', 'HomeController@load_featured_section')->name('home.section.featured');
    Route::post('/home/section/best_selling', 'HomeController@load_best_selling_section')->name('home.section.best_selling');
    Route::get('/home/section/home_categories', 'HomeController@load_home_categories_section')->name('home.section.home_categories');
    Route::post('/home/section/best_sellers', 'HomeController@load_best_sellers_section')->name('home.section.best_sellers');

    Route::get('/home/section/featured/load-more', 'HomeController@featuredLoadMoreProducts')->name('home.section.featured.loadmore');
    // category dropdown menu ajax call
    Route::post('/category/nav-element-list', 'HomeController@get_category_items')->name('category.elements');

    // Flash Deal Details Page
    Route::get('/flash-deals', 'HomeController@all_flash_deals')->name('flash-deals');
    Route::get('/flash-deals/{slug}', 'HomeController@flash_deal_details')->name('flash-deal-details');

    // Notices
    Route::get('/notice', 'NoticeController@customerIndex')->name('customer.notice');
    Route::get('/notice/{slug}', 'NoticeController@show')->name('notices.show');

    // Campaigns
    Route::get('/campaign', 'CampaignController@customerIndex')->name('customer.campaign');
    Route::get('/campaign/{slug}', 'CampaignController@show')->name('campaigns.show');

    Route::view('/api/documentation', 'documentation.index')->name('documentation');

    Route::get('/sitemap', 'SitemapController@index')->name('sitemap.index');

    Route::get('sitemaps/{file}', 'SitemapController@show')->name('sitemaps.show');

    Route::get('/facebook-feed.xml', 'SitemapController@feed')->name('facebook.feed');

    Route::get('/customer-products', 'CustomerProductController@customer_products_listing')->name('customer.products');
    Route::get('/customer-products?category={category_slug}', 'CustomerProductController@search')->name('customer_products.category');
    Route::get('/customer-products?city={city_id}', 'CustomerProductController@search')->name('customer_products.city');
    Route::get('/customer-products?q={search}', 'CustomerProductController@search')->name('customer_products.search');
    Route::get('/customer-products/admin', 'IyzicoController@initPayment')->name('profile.edit');
    Route::get('/customer-product/{slug}', 'CustomerProductController@customer_product')->name('customer.product');
    Route::get('/customer-packages', 'HomeController@premium_package_index')->name('customer_packages_list_show');
    //
    Route::get('/search', 'SearchController@index')->name('search');
    Route::get('/search?keyword={search}', 'SearchController@index')->name('suggestion.search');
    Route::post('/ajax-search', 'SearchController@ajax_search')->name('search.ajax');
    Route::get('/category/{category_slug}', 'SearchController@listingByCategory')->name('products.category');
    Route::get('/brand/{brand_slug}', 'SearchController@listingByBrand')->name('products.brand');

    // messaging dashboard product search
    Route::post('/message/search/product', 'SearchController@messageSearchProduct')->name('msg.search.product');

    Route::get("/{slug}", "HomeController@product")->name("product");
    Route::get("/{slug}", "HomeController@product")->name("product");
    Route::get("/{slug}", "HomeController@product")->name("product");
    Route::post('/product/variant_price', 'HomeController@variant_price')->name('products.variant_price');
    Route::get('/shop/{slug}', 'HomeController@shop')->name('shop.visit');
    Route::get('/shop/{slug}/{type}', 'HomeController@filter_shop')->name('shop.visit.type');

    // Global Route For Filter Product
    // Route::get('/all-products', 'HomeController@all_products')->name('products.all');

    Route::get('/cart', 'CartController@index')->name('cart');
    Route::post('/cart/show-cart-modal', 'CartController@showCartModal')->name('cart.showCartModal');
    Route::post('/cart/addtocart', 'CartController@addToCart')->name('cart.addToCart');
    Route::post('/cart/removeFromCart', 'CartController@removeFromCart')->name('cart.removeFromCart');
    Route::post('/cart/updateQuantity', 'CartController@updateQuantity')->name('cart.updateQuantity');
    Route::post('/cart/minordercheck', 'CartController@minordercheck')->name('cart.minordercheck');

    Route::get('get-available-coupons', 'NewCheckoutController@getAvailableCoupons')->name('get_available_coupons');

    Route::middleware(dynamic_web_middlewares())->group(function () {
        Route::resource('wishlists', 'WishlistController')->except(['store']);
        Route::post('/wishlists/remove', 'WishlistController@remove')->name('wishlists.remove');
        Route::post('/wishlists/store', 'WishlistController@store')->name('wishlists.store');

        Route::post('/cart/add-gift-to-cart', 'NewCheckoutController@addGiftToCart')->name('cart.addGiftToCart');
        // Checkout Routes
        Route::group(['prefix' => 'checkout'], function () {
            Route::get('/old', 'CheckoutController@get_shipping_info')->name('checkout.shipping_info_old');
            Route::get('/', 'NewCheckoutController@index')->name('checkout.shipping_info');
            // Route::get('/index', 'CheckoutController@index')->name('checkout.index');
            Route::any('/delivery_info', 'CheckoutController@store_shipping_info')->name('checkout.store_shipping_infostore');
            Route::post('/payment_select', 'CheckoutController@store_delivery_info')->name('checkout.store_delivery_info');

            Route::post('/payment', 'CheckoutController@checkout')->name('payment.checkout');
            Route::post('/get_pick_up_points', 'HomeController@get_pick_up_points')->name('shipping_info.get_pick_up_points');
            // Route::get('/payment-select', 'CheckoutController@get_payment_info')->name('checkout.payment_info');
            Route::post('/apply_coupon_code', 'CheckoutController@apply_coupon_code')->name('checkout.apply_coupon_code');
            Route::post('/remove_coupon_code', 'CheckoutController@remove_coupon_code')->name('checkout.remove_coupon_code');

            Route::get('/summary', 'NewCheckoutController@getCheckoutSummary')->name('checkout.summary');
            Route::get('/carts-view', 'NewCheckoutController@getCartsView')->name('checkout.view.carts');
            Route::get('/cart-summary-view', 'NewCheckoutController@getCartSummaryView')->name('checkout.view.cart_summary');
            Route::get('/shipping-methods-view', 'NewCheckoutController@getShippingMethods')->name('checkout.view.shipping_methods');
            Route::get('/payment-methods-view', 'NewCheckoutController@getPaymentMethods')->name('checkout.view.payment_methods');
            Route::get('/gift-offers-view', 'NewCheckoutController@getGiftOffersView')->name('checkout.view.gift_offers');

            // Extra
            Route::get('/get-shipping-methods', 'CheckoutController@get_shipping_methods')->name('checkout.get_shipping_methods');

            // AJAX Checkout Routes (Dynamic Updates)
            Route::prefix('ajax')->name('checkout.ajax.')->group(function () {
                Route::post('/update-quantity', 'NewCheckoutController@updateQuantity')->name('update_quantity');
                Route::post('/remove-item', 'NewCheckoutController@removeFromCart')->name('remove_item');
                Route::get('/shipping-methods', 'CheckoutAjaxController@getShippingMethods')->name('shipping_methods');
                Route::get('/cart-summary', 'CheckoutAjaxController@getCartSummary')->name('cart_summary');
                Route::get('/checkout-data', 'CheckoutAjaxController@getCheckoutData')->name('checkout_data');
                Route::post('/update-address', 'CheckoutAjaxController@updateAddress')->name('update_address');
                Route::post('/area-change', 'NewCheckoutController@onAreaChange')->name('area_change');
                Route::post('/shipping-method-change', 'CheckoutAjaxController@onShippingMethodChange')->name('shipping_method_change');

                // Coupon
                Route::post('/apply_coupon_code', 'NewCheckoutController@apply_coupon_code')->name('apply_coupon_code');
                Route::post('/remove_coupon_code', 'NewCheckoutController@remove_coupon_code')->name('remove_coupon_code');
            });

        });
    });
    // Validate Guest Order Data
    Route::post('validate-data', 'UserController@createBeforeOrder')->name('guest.validate_data');
    Route::post('resend-code', 'UserController@resendCode')->name('guest.resend_code');
    Route::post('verify-phone', 'UserController@verifyCode')->name('guest.verify_phone');

    // Checkout Routes
    Route::group(['prefix' => 'checkout', 'middleware' => 'auth'], function () {
        // Club point
        Route::post('/apply-club-point', 'CheckoutController@apply_club_point')->name('checkout.apply_club_point');
        Route::post('/remove-club-point', 'CheckoutController@remove_club_point')->name('checkout.remove_club_point');

        // Reward Point
        Route::post('/apply-reward-point', 'CheckoutController@apply_reward_point')->name('checkout.apply_reward_point');
        Route::post('/remove-reward-point', 'CheckoutController@remove_reward_point')->name('checkout.remove_reward_point');
    });

    Route::get('bkash/payment/callback', 'CheckoutController@bkashCallback')->name('payment.bkash.callback');

    Route::get('checkout/order-confirmed', 'CheckoutController@order_confirmed')->name('order_confirmed');

    // Paypal START
    Route::get('/paypal/payment/done', 'PaypalController@getDone')->name('payment.done');
    Route::get('/paypal/payment/cancel', 'PaypalController@getCancel')->name('payment.cancel');
    // Paypal END
    // SSLCOMMERZ Start
    Route::get('/sslcommerz/pay', 'PublicSslCommerzPaymentController@index');
    Route::POST('/sslcommerz/success', 'PublicSslCommerzPaymentController@success');
    Route::POST('/sslcommerz/fail', 'PublicSslCommerzPaymentController@fail');
    Route::POST('/sslcommerz/cancel', 'PublicSslCommerzPaymentController@cancel');
    Route::POST('/sslcommerz/ipn', 'PublicSslCommerzPaymentController@ipn');
    // SSLCOMMERZ END
    // Stipe Start
    Route::get('stripe', 'StripePaymentController@stripe');
    Route::post('/stripe/create-checkout-session', 'StripePaymentController@create_checkout_session')->name('stripe.get_token');
    Route::any('/stripe/payment/callback', 'StripePaymentController@callback')->name('stripe.callback');
    Route::get('/stripe/success', 'StripePaymentController@success')->name('stripe.success');
    Route::get('/stripe/cancel', 'StripePaymentController@cancel')->name('stripe.cancel');
    // Stripe END

    Route::get('/compare', 'CompareController@index')->name('compare');
    Route::get('/compare/reset', 'CompareController@reset')->name('compare.reset');
    Route::post('/compare/addToCompare', 'CompareController@addToCompare')->name('compare.addToCompare');

    Route::resource('subscribers', 'SubscriberController');

    Route::get('/brands', 'HomeController@all_brands')->name('brands.all');
    Route::get('/categories', 'HomeController@all_categories')->name('categories.all');
    Route::get('/sellers', 'HomeController@all_seller')->name('sellers');
    Route::get('/coupons', 'HomeController@all_coupons')->name('coupons.all');
    Route::get('/inhouse', 'HomeController@inhouse_products')->name('inhouse.all');

    Route::group(['middleware' => ['user', 'verified', 'unbanned']], function () {
        Route::get('/dashboard', 'HomeController@dashboard')->name('dashboard');
        Route::get('/profile', 'HomeController@profile')->name('profile');
        Route::get('/complain-suggestions', 'HomeController@complain_suggestions')->name('complain-suggestions');
        Route::post('/new-user-verification', 'HomeController@new_verify')->name('user.new.verify');
        Route::post('/new-user-email', 'HomeController@update_email')->name('user.change.email');

        Route::post('/user/update-profile', 'HomeController@userProfileUpdate')->name('user.profile.update');
        Route::post('/user/update-password', 'HomeController@userPasswordUpdate')->name('user.password.update');

        Route::resource('purchase_history', 'PurchaseHistoryController');
        Route::post('/purchase_history/details', 'PurchaseHistoryController@purchase_history_details')->name('purchase_history.details');
        Route::post('/purchase_history/cancel', 'PurchaseHistoryController@purchase_history_cancel')->name('purchase_history.cancel');
        Route::get('/purchase_history/destroy/{id}', 'PurchaseHistoryController@destroy')->name('purchase_history.destroy');

        Route::get('/wallet', 'WalletController@index')->name('wallet.index');
        Route::post('/recharge', 'WalletController@recharge')->name('wallet.recharge');

        Route::post('/tickets/rating', 'SupportTicketController@storeRating')->name('tickets.rating');
        Route::post('/tickets/store', 'SupportTicketController@user_store')->name('tickets.user_store');
        Route::post('tickets/reply', 'SupportTicketController@seller_store')->name('tickets.seller_store');
        Route::get('tickets', 'SupportTicketController@index')->name('tickets.index');
        Route::get('/tickets/{id}/show', 'SupportTicketController@show')->name('tickets.show');
        // Route::resource('tickets', 'SupportTicketController');
        // Route::resource('support_ticket', 'SupportTicketController');
        // Route::post('support_ticket/reply', 'SupportTicketController@seller_store')->name('support_ticket.seller_store');

        Route::post('/customer_packages/purchase', 'CustomerPackageController@purchase_package')->name('customer_packages.purchase');
        Route::resource('customer_products', 'CustomerProductController');
        Route::get('/customer_products/{id}/edit', 'CustomerProductController@edit')->name('customer_products.edit');
        Route::post('/customer_products/published', 'CustomerProductController@updatePublished')->name('customer_products.published');
        Route::post('/customer_products/status', 'CustomerProductController@updateStatus')->name('customer_products.update.status');

        Route::get('digital_purchase_history', 'PurchaseHistoryController@digital_index')->name('digital_purchase_history.index');

        Route::get('/all-notifications', 'NotificationController@index')->name('all-notifications');

        // refund request
        Route::post('/order_request/refund', 'RefundRequestController@requestRefund')->name('order_request.refund');

        // Notification route
        Route::get('notifications', 'UserNotificationController@notifications')->name('user_notification.notifications');
        Route::get('notifications/details', 'UserNotificationController@details')->name('user_notification.details');

        // reward point log history route
        Route::get('/roward_point/log', 'PurchaseHistoryController@reward_point_log')->name('user.reward.log');
    });

    Route::get('/aamarpay-done', 'AamarpayController@done_api')->name('aamarpay.done');

    Route::get('/customer_products/destroy/{id}', 'CustomerProductController@destroy')->name('customer_products.destroy');

    Route::group(['prefix' => 'seller', 'middleware' => ['seller', 'verified', 'user']], function () {
        Route::get('/product', 'HomeController@seller_product_list')->name('seller.products');
        Route::get('/product/upload', 'HomeController@show_product_upload_form')->name('seller.products.upload');
        Route::get('/product/{id}/edit', 'HomeController@show_product_edit_form')->name('seller.products.edit');
        Route::resource('payments', 'PaymentController');

        Route::get('/shop/apply_for_verification', 'ShopController@verify_form')->name('shop.verify');
        Route::post('/shop/apply_for_verification', 'ShopController@verify_form_store')->name('shop.verify.store');

        Route::get('/seller-reviews', 'ReviewController@seller_reviews')->name('reviews.seller');

        // digital Product
        Route::get('/digitalproducts', 'HomeController@seller_digital_product_list')->name('seller.digitalproducts');
        Route::get('/digitalproducts/upload', 'HomeController@show_digital_product_upload_form')->name('seller.digitalproducts.upload');
        Route::get('/digitalproducts/{id}/edit', 'HomeController@show_digital_product_edit_form')->name('seller.digitalproducts.edit');

        // Coupon
        Route::get('/coupons', 'CouponController@sellerIndex')->name('seller.coupon.index');
        Route::get('/coupons/create', 'CouponController@sellerCreate')->name('seller.coupon.create');
        Route::post('/coupons/store', 'CouponController@sellerStore')->name('seller.coupon.store');
        Route::get('/coupon/edit/{id}', 'CouponController@sellerEdit')->name('seller.coupon.edit');
        Route::get('/coupon/destroy/{id}', 'CouponController@sellerDestroy')->name('seller.coupon.destroy');
        Route::patch('/coupons/update/{id}', 'CouponController@sellerUpdate')->name('seller.coupon.update');

        // Upload
        Route::any('/uploads/', 'AizUploadController@index')->name('my_uploads.all');
        Route::any('/uploads/new', 'AizUploadController@create')->name('my_uploads.new');
        Route::any('/uploads/file-info', 'AizUploadController@file_info')->name('my_uploads.info');
        Route::get('/uploads/destroy/{id}', 'AizUploadController@destroy')->name('my_uploads.destroy');

        // Stock Adjustment
        // Route::resource('stock-adjust', 'StockAdjustController');
        // Route::get('/stock-adjust/create', 'StockAdjustController@create_seller');
        // Route::post('/stock-adjust/delete_item', 'StockAdjustController@delete_item')->name('stock-adjust.delete_item');
        // Route::get('/stock-adjust/edit/{id}', 'StockAdjustController@edit')->name('stock-adjust.edit');
        // Route::post('/stock-adjust/update/{id}', 'StockAdjustController@update')->name('stock-adjust.update');
    });

    Route::get('/reviews', 'ReviewController@userIndex')->name('reviews.userIndex');
    Route::get('/reviews/{type}', 'ReviewController@filterType')->name('reviews.filter_type');
    Route::post('/reviews/store', 'ReviewController@store')->name('reviews.store');

    Route::resource('shops', 'ShopController');
    Route::get('/track-your-order', 'HomeController@trackOrder')->name('orders.track');

    Route::get('/instamojo/payment/pay-success', 'InstamojoController@success')->name('instamojo.success');

    Route::post('rozer/payment/pay-success', 'RazorpayController@payment')->name('payment.rozer');

    Route::get('/paystack/payment/callback', 'PaystackController@handleGatewayCallback');

    Route::get('/vogue-pay', 'VoguePayController@showForm');
    Route::get('/vogue-pay/success/{id}', 'VoguePayController@paymentSuccess');
    Route::get('/vogue-pay/failure/{id}', 'VoguePayController@paymentFailure');

    // Iyzico
    Route::any('/iyzico/payment/callback/{payment_type}/{amount?}/{payment_method?}/{combined_order_id?}/{customer_package_id?}/{seller_package_id?}', 'IyzicoController@callback')->name('iyzico.callback');

    // payhere below
    Route::get('/payhere/checkout/testing', 'PayhereController@checkout_testing')->name('payhere.checkout.testing');
    Route::get('/payhere/wallet/testing', 'PayhereController@wallet_testing')->name('payhere.checkout.testing');
    Route::get('/payhere/customer_package/testing', 'PayhereController@customer_package_testing')->name('payhere.customer_package.testing');

    Route::any('/payhere/checkout/notify', 'PayhereController@checkout_notify')->name('payhere.checkout.notify');
    Route::any('/payhere/checkout/return', 'PayhereController@checkout_return')->name('payhere.checkout.return');
    Route::any('/payhere/checkout/cancel', 'PayhereController@chekout_cancel')->name('payhere.checkout.cancel');

    Route::any('/payhere/wallet/notify', 'PayhereController@wallet_notify')->name('payhere.wallet.notify');
    Route::any('/payhere/wallet/return', 'PayhereController@wallet_return')->name('payhere.wallet.return');
    Route::any('/payhere/wallet/cancel', 'PayhereController@wallet_cancel')->name('payhere.wallet.cancel');

    Route::any('/payhere/seller_package_payment/notify', 'PayhereController@seller_package_notify')->name('payhere.seller_package_payment.notify');
    Route::any('/payhere/seller_package_payment/return', 'PayhereController@seller_package_payment_return')->name('payhere.seller_package_payment.return');
    Route::any('/payhere/seller_package_payment/cancel', 'PayhereController@seller_package_payment_cancel')->name('payhere.seller_package_payment.cancel');

    Route::any('/payhere/customer_package_payment/notify', 'PayhereController@customer_package_notify')->name('payhere.customer_package_payment.notify');
    Route::any('/payhere/customer_package_payment/return', 'PayhereController@customer_package_return')->name('payhere.customer_package_payment.return');
    Route::any('/payhere/customer_package_payment/cancel', 'PayhereController@customer_package_cancel')->name('payhere.customer_package_payment.cancel');

    // N-genius
    Route::any('ngenius/cart_payment_callback', 'NgeniusController@cart_payment_callback')->name('ngenius.cart_payment_callback');
    Route::any('ngenius/wallet_payment_callback', 'NgeniusController@wallet_payment_callback')->name('ngenius.wallet_payment_callback');
    Route::any('ngenius/customer_package_payment_callback', 'NgeniusController@customer_package_payment_callback')->name('ngenius.customer_package_payment_callback');
    Route::any('ngenius/seller_package_payment_callback', 'NgeniusController@seller_package_payment_callback')->name('ngenius.seller_package_payment_callback');

    // bKash
    Route::post('/bkash/createpayment', 'BkashController@checkout')->name('bkash.checkout');
    Route::post('/bkash/executepayment', 'BkashController@excecute')->name('bkash.excecute');
    Route::get('/bkash/query', 'BkashController@queryPayment')->name('bkash.query');
    Route::get('/bkash/success', 'BkashController@success')->name('bkash.success');
    Route::get('/bkash/refund', 'BkashController@refund')->name('bkash.refund');
    Route::get('/bkash/search', 'BkashController@search')->name('bkash.search');
    Route::get('/bkash/callback', 'BkashController@bkashCallback')->name('bkash.callback');

    // Nagad
    Route::get('/nagad/callback', 'NagadController@verify')->name('nagad.callback');

    // aamarpay
    Route::post('/aamarpay/success', 'AamarpayController@success')->name('aamarpay.success');
    Route::post('/aamarpay/fail', 'AamarpayController@fail')->name('aamarpay.fail');

    Route::post('/aamarpay/api/success', 'AamarpayController@apisuccess')->name('api.aamarpay.success');

    // Authorize-Net-Payment
    Route::post('/dopay/online', 'AuthorizeNetController@handleonlinepay')->name('dopay.online');

    // payku
    Route::get('/payku/callback/{id}', 'PaykuController@callback')->name('payku.result');

    // mobile app balnk page for webview
    Route::get('/mobile-page/{slug}', 'PageController@mobile_custom_page')->name('mobile.custom-pages');

    // Custom page
    Route::get('/page/{slug}', 'PageController@show_custom_page')->name('custom-pages.show_custom_page');

    // Route::get('/search', 'SearchController@index')->name('search');
    // Route::get('/search?keyword={search}', 'SearchController@index')->name('suggestion.search');
    Route::get('/tags/{tag}', 'SearchController@listingByTag')->name('products.tags');
    // Route::get('/tags/archieve?keyword={tag}', 'SearchController@listingByTag')->name('listingByTag');
    // Route::get('/tags?keyword={search}', 'SearchController@listingByTagSearch');

    Route::get('/product/get-category-wise-products', 'ProductController@categoryWiseProducts')->name('products.category_wise_products');

    // Related Product
    Route::get('/related-products/{id}', 'HomeController@relatedProduct')->name('related.products');
    Route::get('/is-product-commentable/{id}', 'HomeController@isCommentable')->name('isCommentable.product');

    // Catch-all product route — disabled for new Next.js frontend
    Route::get("/{slug}", "HomeController@product")->name("product");

    Route::fallback([\App\Http\Controllers\AdminController::class, 'not_found']);
});
