<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth', 'middleware' => ['app_language']], function () {
    Route::post('login', 'Api\V2\AuthController@login');
    Route::post('signup', 'Api\V2\AuthController@signup');
    Route::post('social-login', 'Api\V2\AuthController@socialLogin');
    Route::post('refresh', 'Api\V2\AuthController@refreshToken');
    Route::post('password/forget_request', 'Api\V2\PasswordResetController@forgetRequest');
    Route::post('password/confirm_reset', 'Api\V2\PasswordResetController@confirmReset');
    Route::post('password/resend_code', 'Api\V2\PasswordResetController@resendCode');
    Route::middleware('auth:api')->group(function () {
        Route::get('logout', 'Api\V2\AuthController@logout');
        Route::get('user', 'Api\V2\AuthController@user');
    });
    Route::post('resend_code', 'Api\V2\AuthController@resendCode');
    Route::post('confirm_code', 'Api\V2\AuthController@confirmCode');
});

Route::group(['middleware' => ['app_language']], function () {
    Route::prefix('delivery-boy')->group(function () {
        Route::get('dashboard-summary/{id}', 'Api\V2\DeliveryBoyController@dashboard_summary')->middleware('auth:api');
        Route::get('deliveries/completed/{id}', 'Api\V2\DeliveryBoyController@completed_delivery')->middleware('auth:api');
        Route::get('deliveries/cancelled/{id}', 'Api\V2\DeliveryBoyController@cancelled_delivery')->middleware('auth:api');
        Route::get('deliveries/on_the_way/{id}', 'Api\V2\DeliveryBoyController@on_the_way_delivery')->middleware('auth:api');
        Route::get('deliveries/picked_up/{id}', 'Api\V2\DeliveryBoyController@picked_up_delivery')->middleware('auth:api');
        Route::get('deliveries/assigned/{id}', 'Api\V2\DeliveryBoyController@assigned_delivery')->middleware('auth:api');
        Route::get('collection-summary/{id}', 'Api\V2\DeliveryBoyController@collection_summary')->middleware('auth:api');
        Route::get('earning-summary/{id}', 'Api\V2\DeliveryBoyController@earning_summary')->middleware('auth:api');
        Route::get('collection/{id}', 'Api\V2\DeliveryBoyController@collection')->middleware('auth:api');
        Route::get('earning/{id}', 'Api\V2\DeliveryBoyController@earning')->middleware('auth:api');
        Route::get('cancel-request/{id}', 'Api\V2\DeliveryBoyController@cancel_request')->middleware('auth:api');
        Route::post('change-delivery-status', 'Api\V2\DeliveryBoyController@change_delivery_status')->middleware('auth:api');
    });

    // Live message start
    Route::get('chat/all-message/{id}', 'Api\V2\LiveConversationController@getList');
    Route::get('chat/all-history', 'Api\V2\LiveConversationController@chatHistory');
    Route::post('chat/store-message', 'Api\V2\LiveConversationController@save');
    // Live message end

    Route::get('get-search-suggestions', 'Api\V2\SearchSuggestionController@getList');
    Route::get('languages', 'Api\V2\LanguageController@getList');

    Route::get('chat/conversations/{id}', 'Api\V2\ChatController@conversations')->middleware('auth:api');
    Route::get('chat/messages/{id}', 'Api\V2\ChatController@messages')->middleware('auth:api');
    Route::post('chat/insert-message', 'Api\V2\ChatController@insert_message')->middleware('auth:api');
    Route::get('chat/get-new-messages/{conversation_id}/{last_message_id}', 'Api\V2\ChatController@get_new_messages')->middleware('auth:api');
    Route::post('chat/create-conversation', 'Api\V2\ChatController@create_conversation')->middleware('auth:api');

    Route::apiResource('banners', 'Api\V2\BannerController')->only('index');

    Route::get('brands/top', 'Api\V2\BrandController@top');
    Route::apiResource('brands', 'Api\V2\BrandController')->only('index');

    Route::apiResource('business-settings', 'Api\V2\BusinessSettingController')->only('index');

    Route::get('categories/featured', 'Api\V2\CategoryController@featured');
    Route::get('categories/home', 'Api\V2\CategoryController@home');
    Route::get('categories/top', 'Api\V2\CategoryController@top');
    Route::apiResource('categories', 'Api\V2\CategoryController')->only('index');
    Route::get('sub-categories/{id}', 'Api\V2\SubCategoryController@index')->name('subCategories.index');
    Route::get('leftcategories', 'Api\V2\CategoryController@left_category')->name('leftcategories');

    Route::apiResource('home-categories', 'Api\V2\HomeCategoryController')->only('index');

    Route::apiResource('colors', 'Api\V2\ColorController')->only('index');

    Route::apiResource('currencies', 'Api\V2\CurrencyController')->only('index');

    Route::apiResource('customers', 'Api\V2\CustomerController')->only('show');

    Route::apiResource('general-settings', 'Api\V2\GeneralSettingController')->only('index');

    // Route::get('purchase-history/{id}', 'Api\V2\PurchaseHistoryController@index')->middleware('auth:api');
    // Route::get('purchase-history-details/{id}', 'Api\V2\PurchaseHistoryDetailController@index')->name('purchaseHistory.details')->middleware('auth:api');

    Route::get('purchase-history/{id}', 'Api\V2\PurchaseHistoryController@index');
    Route::get('purchase-history-details/{id}', 'Api\V2\PurchaseHistoryController@details');
    Route::get('purchase-history-items/{id}', 'Api\V2\PurchaseHistoryController@items');
    Route::post('purchase-history-cancel', 'Api\V2\PurchaseHistoryController@purchase_history_cancel');
    Route::get('cancellation-reasons', 'Api\V2\PurchaseHistoryController@cancellation_reasons');

    // Get Pending Reviews Product List
    Route::get('pending-reviews', 'Api\V2\UserController@getDeliverdedProductsWithPendingReview')->middleware('auth:api');
    Route::get('my-reviews', 'Api\V2\UserController@getMyProductReviews')->middleware('auth:api');

    Route::post('cart/check_min_order_amount', 'Api\V2\CartController@check_min_order_amount');

    Route::get('filter/categories', 'Api\V2\FilterController@categories');
    Route::get('filter/brands', 'Api\V2\FilterController@brands');

    Route::get('products/admin', 'Api\V2\ProductController@admin');
    Route::get('products/seller/{id}', 'Api\V2\ProductController@seller');
    Route::get('products/category/{id}', 'Api\V2\ProductController@category')->name('api.products.category');
    Route::get('products/sub-category/{id}', 'Api\V2\ProductController@category')->name('products.subCategory');
    Route::get('products/sub-sub-category/{id}', 'Api\V2\ProductController@subSubCategory')->name('products.subSubCategory');
    Route::get('products/brand/{id}', 'Api\V2\ProductController@brand')->name('api.products.brand');
    Route::get('products/todays-deal', 'Api\V2\ProductController@todaysDeal');
    Route::get('products/featured', 'Api\V2\ProductController@featured');
    Route::get('products/best-seller', 'Api\V2\ProductController@bestSeller')->name('api.products.best-seller');
    Route::get('products/related/{id}', 'Api\V2\ProductController@related')->name('products.related');
    Route::get('products/discounted', 'Api\V2\ProductController@discountedProducts')->name('products.discounted');

    Route::get('products/featured-from-seller/{id}', 'Api\V2\ProductController@newFromSeller')->name('products.featuredromSeller');
    Route::get('products/search', 'Api\V2\ProductController@search');
    Route::get('products/variant/price', 'Api\V2\ProductController@variantPrice');
    Route::get('products/home', 'Api\V2\ProductController@home');
    Route::apiResource('products', 'Api\V2\ProductController')->except(['store', 'update', 'destroy']);
    Route::get('cart-summary/{user_id}', 'Api\V2\CartController@summary')->middleware('auth:api');
    Route::post('carts/process', 'Api\V2\CartController@process')->middleware('auth:api');
    Route::post('carts/add', 'Api\V2\CartController@add')->middleware('auth:api');
    Route::post('carts/change-quantity', 'Api\V2\CartController@changeQuantity')->middleware('auth:api');
    Route::apiResource('carts', 'Api\V2\CartController')->only('destroy')->middleware('auth:api');
    Route::post('carts/{user_id}', 'Api\V2\CartController@getList')->middleware('auth:api');
    Route::post('cartswithdelivery/{user_id}/{address_id}', 'Api\V2\CartController@getListWithDelivery')->middleware('auth:api');
    Route::post('cart/store_delivery_info', 'Api\V2\CartController@store_delivery_info')->middleware('auth:api');

    Route::post('get-assigned-coupons', 'Api\V2\CouponCustomerAssignmentController@getAssignedCoupons')->middleware('auth:api');
    Route::post('coupon-apply', 'Api\V2\CheckoutController@apply_coupon_code')->middleware('auth:api');
    Route::post('coupon-remove', 'Api\V2\CheckoutController@remove_coupon_code')->middleware('auth:api');

    Route::post('update-address-in-cart', 'Api\V2\AddressController@updateAddressInCart')->middleware('auth:api');

    Route::get('payment-types', 'Api\V2\PaymentTypesController@getList');

    Route::get('reviews/product/{id}', 'Api\V2\ReviewController@index')->name('api.reviews.index');
    Route::get('reviews/getcomments', 'Api\V2\ReviewController@getcomments')->name('api.reviews.getcomments');
    Route::post('reviews/submit', 'Api\V2\ReviewController@submit')->name('api.reviews.submit');

    Route::get('shop/user/{id}', 'Api\V2\ShopController@shopOfUser')->middleware('auth:api');
    Route::get('shops/details/{id}', 'Api\V2\ShopController@info')->name('shops.info');
    Route::get('shops/products/all/{id}', 'Api\V2\ShopController@allProducts')->name('shops.allProducts');
    Route::get('shops/products/top/{id}', 'Api\V2\ShopController@topSellingProducts')->name('shops.topSellingProducts');
    Route::get('shops/products/featured/{id}', 'Api\V2\ShopController@featuredProducts')->name('shops.featuredProducts');
    Route::get('shops/products/new/{id}', 'Api\V2\ShopController@newProducts')->name('shops.newProducts');
    Route::get('shops/brands/{id}', 'Api\V2\ShopController@brands')->name('shops.brands');
    Route::apiResource('shops', 'Api\V2\ShopController')->only('index');

    Route::apiResource('sliders', 'Api\V2\SliderController')->only('index');

    Route::get('wishlists-check-product', 'Api\V2\WishlistController@isProductInWishlist');
    Route::get('wishlists-add-product', 'Api\V2\WishlistController@add');
    Route::get('wishlists-remove-product', 'Api\V2\WishlistController@remove');
    Route::get('wishlists/{id}', 'Api\V2\WishlistController@index');
    Route::apiResource('wishlists', 'Api\V2\WishlistController')->except(['index', 'update', 'show']);

    Route::apiResource('settings', 'Api\V2\SettingsController')->only('index');

    Route::get('policies/seller', 'Api\V2\PolicyController@sellerPolicy')->name('policies.seller');
    Route::get('policies/support', 'Api\V2\PolicyController@supportPolicy')->name('policies.support');
    Route::get('policies/return', 'Api\V2\PolicyController@returnPolicy')->name('policies.return');

    Route::get('user/info/{id}', 'Api\V2\UserController@info')->middleware('auth:api');
    Route::post('user/info/update', 'Api\V2\UserController@updateName')->middleware('auth:api');
    Route::get('user/shipping/address/{id}', 'Api\V2\AddressController@addresses')->middleware('auth:api');
    Route::post('user/shipping/create', 'Api\V2\AddressController@createShippingAddress')->middleware('auth:api');
    Route::post('user/shipping/update', 'Api\V2\AddressController@updateShippingAddress')->middleware('auth:api');
    Route::post('user/shipping/update-location', 'Api\V2\AddressController@updateShippingAddressLocation')->middleware('auth:api');
    Route::post('user/shipping/make_default', 'Api\V2\AddressController@makeShippingAddressDefault')->middleware('auth:api');
    Route::get('user/shipping/delete/{id}', 'Api\V2\AddressController@deleteShippingAddress')->middleware('auth:api');

    Route::get('clubpoint/get-list/{id}', 'Api\V2\ClubpointController@get_list')->middleware('auth:api');
    Route::post('clubpoint/convert-into-wallet', 'Api\V2\ClubpointController@convert_into_wallet')->middleware('auth:api');

    Route::get('refund-request/get-list/{id}', 'Api\V2\RefundRequestController@get_list')->middleware('auth:api');
    Route::post('refund-request/send', 'Api\V2\RefundRequestController@send')->middleware('auth:api');

    Route::post('get-user-by-access_token', 'Api\V2\UserController@getUserInfoByAccessToken');

    Route::post('get-group-list-with-user-current-group', 'Api\V2\UserController@get_group_list_with_user_current_group')->middleware('auth:api');

    Route::post('user-order-status', 'Api\V2\UserController@user_order_status')->middleware('auth:api');

    Route::post('delete_user_account', 'Api\V2\UserController@deleteuseraccount');

    Route::get('cities', 'Api\V2\AddressController@getCities');
    Route::get('states', 'Api\V2\AddressController@getStates');
    Route::get('countries', 'Api\V2\AddressController@getCountries');

    Route::get('areas-by-city/{city_id}', 'Api\V2\AddressController@getAreasByCity');
    Route::get('cities-by-state/{state_id}', 'Api\V2\AddressController@getCitiesByState');
    Route::get('states-by-country/{country_id}', 'Api\V2\AddressController@getStatesByCountry');

    Route::post('shipping_cost', 'Api\V2\ShippingController@shipping_cost')->middleware('auth:api');

    Route::post('coupon/apply', 'Api\V2\CouponController@apply')->middleware('auth:api');

    Route::any('stripe', 'Api\V2\StripeController@stripe');
    Route::any('/stripe/create-checkout-session', 'Api\V2\StripeController@create_checkout_session')->name('api.stripe.get_token');
    Route::any('/stripe/payment/callback', 'Api\V2\StripeController@callback')->name('api.stripe.callback');
    Route::any('/stripe/success', 'Api\V2\StripeController@success')->name('api.stripe.success');
    Route::any('/stripe/cancel', 'Api\V2\StripeController@cancel')->name('api.stripe.cancel');

    Route::any('paypal/payment/url', 'Api\V2\PaypalController@getUrl')->name('api.paypal.url');
    Route::any('paypal/payment/done', 'Api\V2\PaypalController@getDone')->name('api.paypal.done');
    Route::any('paypal/payment/cancel', 'Api\V2\PaypalController@getCancel')->name('api.paypal.cancel');

    Route::any('razorpay/pay-with-razorpay', 'Api\V2\RazorpayController@payWithRazorpay')->name('api.v2.razorpay.pay_with_razorpay');
    Route::any('razorpay/payment', 'Api\V2\RazorpayController@payment')->name('api.v2.razorpay.payment');
    Route::post('razorpay/success', 'Api\V2\RazorpayController@success')->name('api.v2.razorpay.success');

    Route::any('paystack/init', 'Api\V2\PaystackController@init')->name('api.paystack.init');
    Route::post('paystack/success', 'Api\V2\PaystackController@success')->name('api.paystack.success');

    Route::any('iyzico/init', 'Api\V2\IyzicoController@init')->name('api.iyzico.init');
    Route::any('iyzico/callback', 'Api\V2\IyzicoController@callback')->name('api.iyzico.callback');
    Route::post('iyzico/success', 'Api\V2\IyzicoController@success')->name('api.iyzico.success');

    Route::get('bkash/begin', 'Api\V2\BkashController@begin')->middleware('auth:api');
    Route::get('bkash/api/webpage/{token}/{amount}', 'Api\V2\BkashController@webpage')->name('api.v2.bkash.webpage');
    Route::any('bkash/api/checkout/{token}/{amount}', 'Api\V2\BkashController@checkout')->name('api.bkash.checkout');
    Route::any('bkash/api/execute/{token}', 'Api\V2\BkashController@execute')->name('api.bkash.execute');
    Route::any('bkash/api/fail', 'Api\V2\BkashController@fail')->name('api.bkash.fail');
    Route::any('bkash/api/success', 'Api\V2\BkashController@success')->name('api.bkash.success');
    Route::post('bkash/api/process', 'Api\V2\BkashController@process')->name('api.bkash.process');
    Route::get('bkash/api/callback', 'Api\V2\BkashController@callback')->name('api.bkash.callback');

    Route::get('nagad/begin', 'Api\V2\NagadController@begin')->middleware('auth:api');
    Route::any('nagad/verify/{payment_type}', 'Api\V2\NagadController@verify')->name('app.nagad.callback_url');
    Route::post('nagad/process', 'Api\V2\NagadController@process');

    Route::get('sslcommerz/begin', 'Api\V2\SslCommerzController@begin');
    Route::post('sslcommerz/success', 'Api\V2\SslCommerzController@payment_success');
    Route::post('sslcommerz/fail', 'Api\V2\SslCommerzController@payment_fail');
    Route::post('sslcommerz/cancel', 'Api\V2\SslCommerzController@payment_cancel');

    Route::post('aamarpay/init', 'Api\V2\AamarpayController@payment_init');
    Route::post('aamarpay/success', 'Api\V2\AamarpayController@payment_success');
    Route::post('aamarpay/fail', 'Api\V2\AamarpayController@payment_fail');
    Route::post('aamarpay/cancel', 'Api\V2\AamarpayController@payment_cancel');

    Route::any('flutterwave/payment/url', 'Api\V2\FlutterwaveController@getUrl')->name('api.flutterwave.url');
    Route::any('flutterwave/payment/callback', 'Api\V2\FlutterwaveController@callback')->name('api.flutterwave.callback');

    Route::any('paytm/payment/pay', 'Api\V2\PaytmController@pay')->name('api.paytm.pay');
    Route::any('paytm/payment/callback', 'Api\V2\PaytmController@callback')->name('api.paytm.callback');

    Route::post('payments/pay/wallet', 'Api\V2\WalletController@processPayment')->middleware('auth:api');
    Route::post('payments/pay/cod', 'Api\V2\PaymentController@cashOnDelivery')->middleware('auth:api');
    Route::post('payments/pay/manual', 'Api\V2\PaymentController@manualPayment')->middleware('auth:api');

    Route::post('offline/payment/submit', 'Api\V2\OfflinePaymentController@submit')->name('api.offline.payment.submit');

    Route::post('update-payment-status', 'Api\V2\OrderController@updatePaymentStatus')->middleware('auth:api');

    Route::post('order/store', 'Api\V2\OrderController@store')->middleware('auth:api');
    Route::get('profile/counters/{user_id}', 'Api\V2\ProfileController@counters')->middleware('auth:api');
    Route::post('profile/update', 'Api\V2\ProfileController@update')->middleware('auth:api');
    Route::post('profile/update-device-token', 'Api\V2\ProfileController@update_device_token')->middleware('auth:api');
    Route::post('profile/update-image', 'Api\V2\ProfileController@updateImage')->middleware('auth:api');
    Route::post('profile/image-upload', 'Api\V2\ProfileController@imageUpload')->middleware('auth:api');
    Route::post('profile/check-phone-and-email', 'Api\V2\ProfileController@checkIfPhoneAndEmailAvailable')->middleware('auth:api');

    Route::post('file/image-upload', 'Api\V2\FileController@imageUpload')->middleware('auth:api');

    Route::get('wallet/balance/{id}', 'Api\V2\WalletController@balance')->middleware('auth:api');
    Route::get('wallet/history/{id}', 'Api\V2\WalletController@walletRechargeHistory')->middleware('auth:api');

    Route::get('flash-deals', 'Api\V2\FlashDealController@index');
    Route::get('flash-deal/{id}', 'Api\V2\FlashDealController@show');
    Route::get('flash-deal-products/{id}', 'Api\V2\FlashDealController@products');

    // Aamarpay Api Calls
    Route::any('aamarpay', 'Api\V2\AamarpayController@index');

    // Get products by tag
    Route::any('tags/{tag}', 'Api\V2\ProductController@listingBytag');

    // Category Details by slug or Id
    Route::get('category/{id}', 'Api\V2\CategoryController@show');

    // Subscribers
    Route::resource('subscribers', 'Api\V2\SubscriberController');

    Route::post('get-user-data', function (Illuminate\Http\Request $request) {
        $validator = Validator::make($request->all(), [
            'ids' => 'array|required',
        ]);
        $data = [];

        if (count($request->ids) > 0) {

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()]);
            } else {

                $data = User::whereIn('id', $request->ids)->get();
            }

            return response()->json(['status' => 'success', 'data' => $data]);
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    });

    // advertisement route
    Route::get('ads', 'Api\V2\AdvertizementController@index');

    // reward point route
    Route::group(['middleware' => 'auth:api', 'prefix' => 'reward/point'], function () {
        Route::get('/', 'Api\V2\RewardPointController@rewardPoint');
        Route::get('/log', 'Api\V2\RewardPointController@rewardPointLog');
        Route::post('/redeem', 'Api\V2\RewardPointController@rewardPointRedeem');
    });
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('/notifications', 'Api\V2\UserNotificationController@notifications');
        Route::get('/notifications/details', 'Api\V2\UserNotificationController@details');
        Route::get('/notifications/notification-count', 'Api\V2\UserNotificationController@notification_count');

    });

    Route::get('order-details/{code}', 'Api\V2\PurchaseHistoryController@detailsByCode')->name('order.details.code');
    Route::group(['prefix' => 'blog'], function () {
        Route::get('/', 'Api\V2\BlogController@index');
    });

    // Pathao Webhook
    Route::post('pathao/status-update', 'PathaoController@updateOrderStatus');

});

Route::fallback(function () {
    return response()->json([
        'data' => [],
        'success' => false,
        'status' => 404,
        'message' => 'Invalid Route',
    ]);
});
