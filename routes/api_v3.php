<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

Route::get('/forge-cache', 'Api\V3\BusinessSettingController@clearCache');

Route::group(['prefix' => 'v3/auth'], function() {
    Route::post('login', 'Api\V3\AuthController@login');
    Route::post('login-with-otp', 'Api\V3\AuthController@loginWithOtp')->name('auth.login_with_otp');
    Route::post('signup', 'Api\V3\AuthController@signup');
    Route::post('social-login', 'Api\V3\AuthController@socialLogin');
    Route::post('refresh', 'Api\V3\AuthController@refreshToken');
    Route::post('password/forget_request', 'Api\V3\PasswordResetController@forgetRequest');
    Route::post('password/confirm_reset', 'Api\V3\PasswordResetController@confirmReset');
    Route::post('password/resend_code', 'Api\V3\PasswordResetController@resendCode');
    Route::middleware('auth:api')->group(function () {
        Route::get('logout', 'Api\V3\AuthController@logout');
        Route::get('user', 'Api\V3\AuthController@user');
    });
    Route::post('resend_code', 'Api\V3\AuthController@resendCode');
    Route::post('confirm_code', 'Api\V3\AuthController@confirmCode');
    Route::get('lookup/{contact}', 'Api\V3\AuthController@lookupContact')->name('auth.lookup');
});



Route::group(['prefix' => 'v3', 'middleware' => ['app_language']], function () {
    Route::get('/sitemap', 'Api\V3\SitemapController@index')->name('sitemap.index');

    Route::get('sitemaps/{file?}', 'Api\V3\SitemapController@show')->name('sitemaps.show');

    Route::get('/facebook-feed.xml', 'Api\V3\SitemapController@feed')->name('facebook.feed');

    Route::middleware(['feature.activation:enable_application_management,enable_jobs_management'])->group(function () {
        Route::get('careers', 'Api\V3\JobPostController@getPosts')->name('api.job_posts.careers');
        Route::get('jobs/{slug}', 'Api\V3\JobPostController@jobDetails')->name('api.job_posts.details');
        Route::post('jobs/apply', 'Api\V3\JobPostController@apply')->name('api.job_posts.apply');
    });

    Route::prefix('delivery-boy')->group(function () {
        Route::get('dashboard-summary/{id}', 'Api\V3\DeliveryBoyController@dashboard_summary')->middleware('auth:api');
        Route::get('deliveries/completed/{id}', 'Api\V3\DeliveryBoyController@completed_delivery')->middleware('auth:api');
        Route::get('deliveries/cancelled/{id}', 'Api\V3\DeliveryBoyController@cancelled_delivery')->middleware('auth:api');
        Route::get('deliveries/on_the_way/{id}', 'Api\V3\DeliveryBoyController@on_the_way_delivery')->middleware('auth:api');
        Route::get('deliveries/picked_up/{id}', 'Api\V3\DeliveryBoyController@picked_up_delivery')->middleware('auth:api');
        Route::get('deliveries/assigned/{id}', 'Api\V3\DeliveryBoyController@assigned_delivery')->middleware('auth:api');
        Route::get('collection-summary/{id}', 'Api\V3\DeliveryBoyController@collection_summary')->middleware('auth:api');
        Route::get('earning-summary/{id}', 'Api\V3\DeliveryBoyController@earning_summary')->middleware('auth:api');
        Route::get('collection/{id}', 'Api\V3\DeliveryBoyController@collection')->middleware('auth:api');
        Route::get('earning/{id}', 'Api\V3\DeliveryBoyController@earning')->middleware('auth:api');
        Route::get('cancel-request/{id}', 'Api\V3\DeliveryBoyController@cancel_request')->middleware('auth:api');
        Route::post('change-delivery-status', 'Api\V3\DeliveryBoyController@change_delivery_status')->middleware('auth:api');
    });

    // Live message start
    Route::get('chat/all-message/{id}', 'Api\V3\LiveConversationController@getList');
    Route::get('chat/all-history', 'Api\V3\LiveConversationController@chatHistory');
    Route::post('chat/store-message', 'Api\V3\LiveConversationController@save');
    // Live message end

    // Track Order
    Route::get('track-order', 'Api\V3\OrderController@trackOrder')->name('track.order');

    // Search All
    Route::get('search', 'Api\V3\SearchController@index');

    // New Search Suggestion (Products, categories, Brands)
    Route::get('search-suggestion', 'Api\V3\SearchSuggestionController@globalSearch');

    Route::get('get-search-suggestions', 'Api\V3\SearchSuggestionController@getList');
    Route::get('languages', 'Api\V3\LanguageController@getList');

    Route::get('chat/conversations/{id}', 'Api\V3\ChatController@conversations')->middleware('auth:api');
    Route::get('chat/messages/{id}', 'Api\V3\ChatController@messages')->middleware('auth:api');
    Route::post('chat/insert-message', 'Api\V3\ChatController@insert_message')->middleware('auth:api');
    Route::get('chat/get-new-messages/{conversation_id}/{last_message_id}', 'Api\V3\ChatController@get_new_messages')->middleware('auth:api');
    Route::post('chat/create-conversation', 'Api\V3\ChatController@create_conversation')->middleware('auth:api');

    Route::apiResource('banners', 'Api\V3\BannerController')->only('index');

    Route::get('brands/top', 'Api\V3\BrandController@top');
    Route::apiResource('brands', 'Api\V3\BrandController')->only('index', 'show');

    Route::apiResource('business-settings', 'Api\V3\BusinessSettingController')->only('index');

    Route::get('categories/featured', 'Api\V3\CategoryController@featured');
    Route::get('categories/home', 'Api\V3\CategoryController@home');
    Route::get('categories/top', 'Api\V3\CategoryController@top');
    Route::apiResource('categories', 'Api\V3\CategoryController')->only('index');
    Route::get('sub-categories/{id}', 'Api\V3\SubCategoryController@index')->name('subCategories.index');
    Route::get('leftcategories', 'Api\V3\CategoryController@left_category')->name('leftcategories');

    Route::apiResource('colors', 'Api\V3\ColorController')->only('index');

    Route::apiResource('currencies', 'Api\V3\CurrencyController')->only('index');

    Route::apiResource('customers', 'Api\V3\CustomerController')->only('show');

    Route::apiResource('general-settings', 'Api\V3\GeneralSettingController')->only('index');

    Route::apiResource('home-categories', 'Api\V3\HomeCategoryController')->only('index');

    // Route::get('purchase-history/{id}', 'Api\V3\PurchaseHistoryController@index')->middleware('auth:api');
    // Route::get('purchase-history-details/{id}', 'Api\V3\PurchaseHistoryDetailController@index')->name('purchaseHistory.details')->middleware('auth:api');

    Route::get('purchase-history/{id}', 'Api\V3\PurchaseHistoryController@index');
    Route::get('purchase-history-details/{id}', 'Api\V3\PurchaseHistoryController@details');
    Route::get('purchase-history-items/{id}', 'Api\V3\PurchaseHistoryController@items');
    Route::post('purchase-history-cancel', 'Api\V3\PurchaseHistoryController@purchase_history_cancel');
    Route::get('cancellation-reasons', 'Api\V3\PurchaseHistoryController@cancellation_reasons');

    // Get Pending Reviews Product List
    Route::get('pending-reviews', 'Api\V3\UserController@getDeliverdedProductsWithPendingReview')->middleware('auth:api');
    Route::get('my-reviews', 'Api\V3\UserController@getMyProductReviews')->middleware('auth:api');

    Route::post('cart/check_min_order_amount', 'Api\V3\CartController@check_min_order_amount');

    Route::get('filter/categories', 'Api\V3\FilterController@categories');
    Route::get('filter/brands', 'Api\V3\FilterController@brands');

    // All New Arrivals Products
    Route::get('products/category/new-arrivals', 'Api\V3\ProductController@newArrivals')->name('products.allNewArrivals');

    Route::get('products/admin', 'Api\V3\ProductController@admin');
    Route::get('products/seller/{id}', 'Api\V3\ProductController@seller');
    Route::get('products/category/{id}', 'Api\V3\ProductController@category')->name('api.products.category');
    Route::get('products/sub-category/{id}', 'Api\V3\ProductController@category')->name('products.subCategory');
    Route::get('products/sub-sub-category/{id}', 'Api\V3\ProductController@subSubCategory')->name('products.subSubCategory');
    Route::get('products/brand/{id}', 'Api\V3\ProductController@brand')->name('api.products.brand');
    Route::get('products/todays-deal', 'Api\V3\ProductController@todaysDeal');
    Route::get('products/featured', 'Api\V3\ProductController@featured');
    Route::get('products/best-seller', 'Api\V3\ProductController@bestSeller')->name('api.products.best-seller');
    Route::get('products/related/{id}', 'Api\V3\ProductController@related')->name('products.related');
    Route::get('products/discounted', 'Api\V3\ProductController@discountedProducts')->name('products.discounted');

    // New arrival products
    Route::get('products/new-arrivals', 'Api\V3\ProductController@newArrivals')->name('products.newArrivals');

    // Product Related Videos
    Route::get('products/related-videos/{id}', 'Api\V3\ProductController@relatedVideos')->name('products.relatedVideos');

    // Highlighted Items
    Route::get('highlights', 'Api\V3\HighlightedItemsController@index')->name('highlights.index');

    // Doctors Consultation
    Route::get('/doctors-consultation', 'Api\V3\BusinessSettingController@getDoctorsConsultation');

    // Playlist With Videos
    Route::get('playlists/featured', 'Api\V3\VideoPlaylistController@featuredPlaylists')->name('playlists.featured');

    // Video View Counter
    Route::post('videos-views', 'Api\V3\VideoController@viewCounter')->name('videos.viewCounter');

    // Track product visits and user info
    Route::post('product-visit', 'Api\V3\ProductVisitController@store')->name('product_visit.store');

    // Pages
    Route::get('pages/{slug}', 'Api\V3\PageController@getPageContent')->name('pages.getPageContent');

    Route::get('products/featured-from-seller/{id}', 'Api\V3\ProductController@newFromSeller')->name('products.featuredromSeller');
    Route::get('products/search', 'Api\V3\ProductController@search');
    Route::get('products/variant/price', 'Api\V3\ProductController@variantPrice');
    Route::get('products/home', 'Api\V3\ProductController@home');
    Route::apiResource('products', 'Api\V3\ProductController')->except(['store', 'update', 'destroy']);

    // Route::middleware(dynamic_api_middlewares())->group(function () {
    Route::middleware(['dynamic_api', 'is_guest_user'])->group(function () {
        Route::get('cart-summary/{user_id}', 'Api\V3\CartController@summary');
        Route::post('carts/process', 'Api\V3\CartController@process');
        Route::post('carts/add', 'Api\V3\CartController@add');
        Route::post('carts/change-quantity', 'Api\V3\CartController@changeQuantity');
        Route::apiResource('carts', 'Api\V3\CartController')->only('destroy');
        Route::post('cartswithdelivery/{user_id}/{address_id}', 'Api\V3\CartController@getListWithDelivery');
        Route::post('carts/{user_id}', 'Api\V3\CartController@getList');
        Route::post('cart/store_delivery_info', 'Api\V3\CartController@store_delivery_info');
        Route::post('update-address-in-cart', 'Api\V3\AddressController@updateAddressInCart');
        Route::get('user/shipping/address/{id}', 'Api\V3\AddressController@addresses');
        Route::post('user/shipping/create', 'Api\V3\AddressController@createShippingAddress');
        Route::post('user/shipping/update', 'Api\V3\AddressController@updateShippingAddress');
        Route::post('user/shipping/update-location', 'Api\V3\AddressController@updateShippingAddressLocation');
        Route::post('user/shipping/make_default', 'Api\V3\AddressController@makeShippingAddressDefault');
        Route::get('user/shipping/delete/{id}', 'Api\V3\AddressController@deleteShippingAddress');
        // Route::post('order/update-order-status', 'Api\V3\OrderController@update_order_status');
        Route::post('coupon/apply', 'Api\V3\CouponController@apply');
        Route::post('coupon-apply', 'Api\V3\CheckoutController@apply_coupon_code');
        Route::post('coupon-remove', 'Api\V3\CheckoutController@remove_coupon_code');
        Route::get('wishlists-add-product', 'Api\V3\WishlistController@add');
        Route::get('wishlists-remove-product', 'Api\V3\WishlistController@remove');
        Route::get('wishlists-check-product', 'Api\V3\WishlistController@isProductInWishlist');
        Route::get('wishlists/{id}', 'Api\V3\WishlistController@index');
        Route::apiResource('wishlists', 'Api\V3\WishlistController')->except(['index', 'update', 'show']);

        // Gift Offers
        Route::get('gift-offers/{user_id?}', 'Api\V3\GiftOfferController@getGiftOffers');
        Route::post('add-gift-to-cart', 'Api\V3\GiftOfferController@addGiftToCart');
    });
    Route::post('payments/pay/cod', 'Api\V3\PaymentController@cashOnDelivery')->middleware('auth:api');
    Route::post('order/store', 'Api\V3\OrderController@store')->middleware('auth:api');

    // Validate Guest Order Data
    Route::post('validate-data', 'Api\V3\UserController@createBeforeOrder');
    Route::post('resend-code', 'Api\V3\UserController@resendCode');
    Route::post('verify-phone', 'Api\V3\UserController@verifyCode');

    Route::post('get-assigned-coupons', 'Api\V3\CouponCustomerAssignmentController@getAssignedCoupons');//->middleware('auth:api');

    Route::get('payment-types', 'Api\V3\PaymentTypesController@getList');

    Route::get('reviews/product/{id}', 'Api\V3\ReviewController@index')->name('api.reviews.index');
    Route::get('reviews/getcomments', 'Api\V3\ReviewController@getcomments')->name('api.reviews.getcomments');
    Route::post('reviews/submit', 'Api\V3\ReviewController@submit')->name('api.reviews.submit');

    // New reviews api
    Route::get('reviews/featured', 'Api\V3\ReviewController@featuredReviews')->name('api.reviews.featured');

    // Highlight Brand With Products
    Route::get('highlight-brand/products', 'Api\V3\BrandController@highlight')->name('brands.highlight');

    Route::get('shop/user/{id}', 'Api\V3\ShopController@shopOfUser')->middleware('auth:api');
    Route::get('shops/details/{id}', 'Api\V3\ShopController@info')->name('shops.info');
    Route::get('shops/products/all/{id}', 'Api\V3\ShopController@allProducts')->name('shops.allProducts');
    Route::get('shops/products/top/{id}', 'Api\V3\ShopController@topSellingProducts')->name('shops.topSellingProducts');
    Route::get('shops/products/featured/{id}', 'Api\V3\ShopController@featuredProducts')->name('shops.featuredProducts');
    Route::get('shops/products/new/{id}', 'Api\V3\ShopController@newProducts')->name('shops.newProducts');
    Route::get('shops/brands/{id}', 'Api\V3\ShopController@brands')->name('shops.brands');
    Route::apiResource('shops', 'Api\V3\ShopController')->only('index');

    Route::apiResource('sliders', 'Api\V3\SliderController')->only('index');

    Route::apiResource('settings', 'Api\V3\SettingsController')->only('index');

    Route::get('policies/seller', 'Api\V3\PolicyController@sellerPolicy')->name('policies.seller');
    Route::get('policies/support', 'Api\V3\PolicyController@supportPolicy')->name('policies.support');
    Route::get('policies/return', 'Api\V3\PolicyController@returnPolicy')->name('policies.return');

    Route::get('user/info/{id}', 'Api\V3\UserController@info')->middleware('auth:api');
    Route::post('user/info/update', 'Api\V3\UserController@updateName')->middleware('auth:api');

    Route::get('clubpoint/get-list/{id}', 'Api\V3\ClubpointController@get_list')->middleware('auth:api');
    Route::post('clubpoint/convert-into-wallet', 'Api\V3\ClubpointController@convert_into_wallet')->middleware('auth:api');

    Route::get('refund-request/get-list/{id}', 'Api\V3\RefundRequestController@get_list')->middleware('auth:api');
    Route::post('refund-request/send', 'Api\V3\RefundRequestController@send')->middleware('auth:api');

    Route::match(['get', 'post'], 'get-user-by-access_token', 'Api\V3\UserController@getUserInfoByAccessToken')->middleware('auth:api');

    Route::post('get-group-list-with-user-current-group', 'Api\V3\UserController@get_group_list_with_user_current_group')->middleware('auth:api');

    Route::post('user-order-status', 'Api\V3\UserController@user_order_status')->middleware('auth:api');

    Route::post('delete_user_account', 'Api\V3\UserController@deleteuseraccount');

    Route::get('cities', 'Api\V3\AddressController@getCities');
    Route::get('states', 'Api\V3\AddressController@getStates');
    Route::get('countries', 'Api\V3\AddressController@getCountries');

    Route::get('areas-by-city/{city_id}', 'Api\V3\AddressController@getAreasByCity');
    Route::get('cities-by-state/{state_id}', 'Api\V3\AddressController@getCitiesByState');
    Route::get('states-by-country/{country_id}', 'Api\V3\AddressController@getStatesByCountry');

    Route::post('shipping_cost', 'Api\V3\ShippingController@shipping_cost')->middleware('auth:api');

    Route::any('stripe', 'Api\V3\StripeController@stripe');
    Route::any('/stripe/create-checkout-session', 'Api\V3\StripeController@create_checkout_session')->name('api.stripe.get_token');
    Route::any('/stripe/payment/callback', 'Api\V3\StripeController@callback')->name('api.stripe.callback');
    Route::any('/stripe/success', 'Api\V3\StripeController@success')->name('api.stripe.success');
    Route::any('/stripe/cancel', 'Api\V3\StripeController@cancel')->name('api.stripe.cancel');

    Route::any('paypal/payment/url', 'Api\V3\PaypalController@getUrl')->name('api.paypal.url');
    Route::any('paypal/payment/done', 'Api\V3\PaypalController@getDone')->name('api.paypal.done');
    Route::any('paypal/payment/cancel', 'Api\V3\PaypalController@getCancel')->name('api.paypal.cancel');

    Route::any('razorpay/pay-with-razorpay', 'Api\V3\RazorpayController@payWithRazorpay')->name('api.v3.razorpay.pay_with_razorpay');
    Route::any('razorpay/payment', 'Api\V3\RazorpayController@payment')->name('api.v3.razorpay.payment');
    Route::post('razorpay/success', 'Api\V3\RazorpayController@success')->name('api.v3.razorpay.success');

    Route::any('paystack/init', 'Api\V3\PaystackController@init')->name('api.paystack.init');
    Route::post('paystack/success', 'Api\V3\PaystackController@success')->name('api.paystack.success');

    Route::any('iyzico/init', 'Api\V3\IyzicoController@init')->name('api.iyzico.init');
    Route::any('iyzico/callback', 'Api\V3\IyzicoController@callback')->name('api.iyzico.callback');
    Route::post('iyzico/success', 'Api\V3\IyzicoController@success')->name('api.iyzico.success');

    Route::get('bkash/begin', 'Api\V3\BkashController@begin');
    Route::get('bkash/api/webpage/{token}/{amount}/{order_id?}', 'Api\V3\BkashController@webpage')->name('api.bkash.webpage');
    Route::any('bkash/api/checkout/{token}/{amount}/{order_id?}', 'Api\V3\BkashController@checkout')->name('api.bkash.checkout');
    Route::any('bkash/api/execute/{token}', 'Api\V3\BkashController@execute')->name('api.bkash.execute');
    Route::any('bkash/api/fail', 'Api\V3\BkashController@fail')->name('api.bkash.fail');
    Route::any('bkash/api/success', 'Api\V3\BkashController@success')->name('api.bkash.success');
    Route::post('bkash/api/process', 'Api\V3\BkashController@process')->name('api.bkash.process');
    Route::get('bkash/api/callback', 'Api\V3\BkashController@callback')->name('api.bkash.callback');

    Route::get('nagad/begin', 'Api\V3\NagadController@begin')->middleware('auth:api');
    Route::any('nagad/verify/{payment_type}', 'Api\V3\NagadController@verify')->name('app.nagad.callback_url');
    Route::post('nagad/process', 'Api\V3\NagadController@process');

    Route::get('sslcommerz/begin', 'Api\V3\SslCommerzController@begin');
    Route::post('sslcommerz/success', 'Api\V3\SslCommerzController@payment_success');
    Route::post('sslcommerz/fail', 'Api\V3\SslCommerzController@payment_fail');
    Route::post('sslcommerz/cancel', 'Api\V3\SslCommerzController@payment_cancel');

    Route::post('aamarpay/init', 'Api\V3\AamarpayController@payment_init');
    Route::post('aamarpay/success', 'Api\V3\AamarpayController@payment_success');
    Route::post('aamarpay/fail', 'Api\V3\AamarpayController@payment_fail');
    Route::post('aamarpay/cancel', 'Api\V3\AamarpayController@payment_cancel');

    Route::any('flutterwave/payment/url', 'Api\V3\FlutterwaveController@getUrl')->name('api.flutterwave.url');
    Route::any('flutterwave/payment/callback', 'Api\V3\FlutterwaveController@callback')->name('api.flutterwave.callback');

    Route::any('paytm/payment/pay', 'Api\V3\PaytmController@pay')->name('api.paytm.pay');
    Route::any('paytm/payment/callback', 'Api\V3\PaytmController@callback')->name('api.paytm.callback');

    Route::post('payments/pay/wallet', 'Api\V3\WalletController@processPayment')->middleware('auth:api');
    Route::post('payments/pay/manual', 'Api\V3\PaymentController@manualPayment')->middleware('auth:api');

    Route::post('offline/payment/submit', 'Api\V3\OfflinePaymentController@submit')->name('api.offline.payment.submit');

    Route::post('update-payment-status', 'Api\V3\OrderController@updatePaymentStatus')->middleware('auth:api');

    Route::get('profile/counters/{user_id}', 'Api\V3\ProfileController@counters')->middleware('auth:api');
    Route::post('profile/update', 'Api\V3\ProfileController@update')->middleware('auth:api');
    Route::post('profile/update-device-token', 'Api\V3\ProfileController@update_device_token')->middleware('auth:api');
    Route::post('profile/update-image', 'Api\V3\ProfileController@updateImage')->middleware('auth:api');
    Route::post('profile/image-upload', 'Api\V3\ProfileController@imageUpload')->middleware('auth:api');
    Route::post('profile/check-phone-and-email', 'Api\V3\ProfileController@checkIfPhoneAndEmailAvailable')->middleware('auth:api');
    Route::get('profile', 'Api\V3\ProfileController@profile')->middleware('auth:api');

    Route::post('file/image-upload', 'Api\V3\FileController@imageUpload')->middleware('auth:api');

    Route::get('wallet/balance/{id}', 'Api\V3\WalletController@balance')->middleware('auth:api');
    Route::get('wallet/history/{id}', 'Api\V3\WalletController@walletRechargeHistory')->middleware('auth:api');

    Route::get('flash-deals', 'Api\V3\FlashDealController@index');
    Route::get('flash-deal/{id}', 'Api\V3\FlashDealController@show');
    Route::get('flash-deal-products/{id}', 'Api\V3\FlashDealController@products');

    // Gift Offers API
    Route::get('gift-offers', 'Api\V3\GiftOfferController@index');
    Route::get('gift-offers/{id}', 'Api\V3\GiftOfferController@show');
    Route::get('gift-offers/{id}/items', 'Api\V3\GiftOfferController@items');
    Route::get('gift-offers/product/{productId}', 'Api\V3\GiftOfferController@getOffersForProduct');
    Route::get('gift-offers/cart/{userId}', 'Api\V3\GiftOfferController@getOffersForCart');

    // Gift Offer Selections API
    Route::post('gift-offers/select', 'Api\V3\GiftOfferController@selectGift');
    Route::get('gift-offers/selections/{userId}', 'Api\V3\GiftOfferController@getSelections');
    Route::delete('gift-offers/selections/{id}', 'Api\V3\GiftOfferController@removeSelection');
    Route::delete('gift-offers/selections/clear', 'Api\V3\GiftOfferController@clearSelections');
    Route::get('gift-offers/cart-with-gifts/{userId}', 'Api\V3\GiftOfferController@getCartWithGifts');

    // Aamarpay Api Calls
    Route::any('aamarpay', 'Api\V3\AamarpayController@index');

    // Get products by tag
    Route::any('tags/{tag}', 'Api\V3\ProductController@listingBytag');

    // Category Details by slug or Id
    Route::get('category/{id}', 'Api\V3\CategoryController@show');

    // Subscribers
    Route::resource('subscribers', 'Api\V3\SubscriberController');

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
    Route::get('ads', 'Api\V3\AdvertizementController@index');

    // reward point route
    Route::group(['middleware' => 'auth:api', 'prefix' => 'reward/point'], function () {
        Route::get('/', 'Api\V3\RewardPointController@rewardPoint');
        Route::get('/log', 'Api\V3\RewardPointController@rewardPointLog');
        Route::post('/redeem', 'Api\V3\RewardPointController@rewardPointRedeem');
    });
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('/notifications', 'Api\V3\UserNotificationController@notifications');
        Route::get('/notifications/details', 'Api\V3\UserNotificationController@details');
        Route::get('/notifications/notification-count', 'Api\V3\UserNotificationController@notification_count');

    });

    // Skin Concern Api
    Route::get('skin-concerns', 'Api\V3\SkinController@getSkinConcerns');

    Route::get('order-details/{code}', 'Api\V3\PurchaseHistoryController@detailsByCode')->name('order.details.code');
    Route::group(['prefix' => 'blog'], function () {
        Route::get('/', 'Api\V3\BlogController@index');
    });

    // Pathao Webhook
    Route::post('pathao/status-update', 'PathaoController@updateOrderStatus');

    // Parent Category Content
    Route::get('parent-category-content/{id}', 'Api\V3\CategoryController@getCategoryContent');

    // faqs
    Route::get('faqs', 'Api\V3\FaqController@fetchFaqs');
    Route::get('faq/{id}', 'Api\V3\FaqController@getFaq');
});

Route::fallback(function () {
    return response()->json([
        'data' => [],
        'success' => false,
        'status' => 404,
        'message' => 'Invalid Route',
    ]);
});
