<?php

use App\Http\Controllers\Api\Merchant\V1\OrderController;
use App\Http\Controllers\Api\Merchant\V1\TokenController;
use App\Http\Controllers\Api\Merchant\V1\ProductController;
use App\Http\Controllers\Api\Merchant\V1\CategoryController;

Route::post('generate-token', [TokenController::class, 'generate']);
// Route::post('regenerate-token', [TokenController::class, 'regenerate']);


Route::middleware(['jwt'])->group(function() {
    Route::middleware('throttle:3,60')->group(function() {
        // Categories
        Route::get('categories', [CategoryController::class, 'getCategories'])->name('merchant.categories.index');
        Route::get('categories/{slug}', [CategoryController::class, 'getProductsByCategory'])->name('merchant.categories.products');

        // Products
        Route::get('products', [ProductController::class, 'getProducts'])->name('merchant.products.index');

        // Product Stocks
        Route::get('stocks', [ProductController::class, 'getProductsStock'])->name('merchant.products.stocks');
    });

    // Orders
    Route::post('orders/store', [OrderController::class, 'store'])->name('merchant.orders.store');
    Route::post('orders/update', [OrderController::class, 'update'])->name('merchant.orders.update');
    Route::put('orders/update-status', [OrderController::class, 'updateStatus'])->name('merchant.orders.updateStatus');
});
