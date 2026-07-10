<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| POS Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Route::get('/pos/products-old', 'PosController@searchOld')->name('pos.search_product');
Route::get('/pos/products', 'PosController@search')->name('pos.search_product');
Route::post('/add-to-cart-pos', 'PosController@addToCart')->name('pos.addToCart');
Route::post('/add-gift-to-cart-pos', 'PosController@addGiftToCart')->name('pos.addGiftToCart');
Route::post('/apply-coupon', 'PosController@applyCoupon')->name('pos.applyCoupon');
Route::post('/update-quantity-cart-pos', 'PosController@updateQuantity')->name('pos.updateQuantity');
Route::post('/update-gift-quantity-cart-pos', 'PosController@updateGiftQuantity')->name('pos.updateGiftQuantity');
Route::post('/remove-from-cart-pos', 'PosController@removeFromCart')->name('pos.removeFromCart');
Route::post('/get_shipping_address', 'PosController@getShippingAddress')->name('pos.getShippingAddress');
Route::post('/get_shipping_address_seller', 'PosController@getShippingAddressForSeller')->name('pos.getShippingAddressForSeller');
Route::post('/setDiscount', 'PosController@setDiscount')->name('pos.setDiscount');
Route::post('/setShipping', 'PosController@setShipping')->name('pos.setShipping');
Route::post('/set-shipping-address', 'PosController@set_shipping_address')->name('pos.set-shipping-address');
Route::post('/pos-order-summary', 'PosController@get_order_summary')->name('pos.getOrderSummary');
Route::post('/pos-order', 'PosController@order_store')->name('pos.order_place');
Route::post('/partial-pay', 'PosController@partialPayment')->name('pos.partial-pay');
Route::get('/remove-paid-amount', 'PosController@removePaidAmount')->name('pos.removePaidAmount');
Route::get('/get-order-summary', 'PosController@getOrderSummary')->name('pos.get_order_summary');
Route::get('/get-customer-address', 'PosController@getCustomerAddress')->name('pos.getCustomerAddress');
Route::get('/get-recent-orders', 'PosController@getRecentOrders')->name('pos.getRecentOrders');
Route::get('/get-customer-success-rate', 'PosController@getCustomerSuccessRate')->name('pos.getCustomerSuccessRate');
Route::post('/store-call-log', 'PosController@storeCallLog')->name('pos.storeCallLog');
Route::get('/reset-cart', 'PosController@resetCart')->name('pos.resetCart');
Route::get('/pos/customers/search', 'PosController@searchCustomer')->name('pos.customers.search');

//Admin
Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'admin']], function(){
    //pos
	Route::get('/pos', 'PosController@index')->name('poin-of-sales.index');
	Route::get('/pos-activation', 'PosController@pos_activation')->name('poin-of-sales.activation');
    Route::get('/check-session-health', 'PosController@checkSessionHealth')->name('pos.checkSessionHealth');
});
Route::group(['prefix' =>'seller', 'middleware' => ['seller', 'verified']], function(){
    //pos
	Route::get('/pos', 'PosController@index')->name('poin-of-sales.seller_index');
});
