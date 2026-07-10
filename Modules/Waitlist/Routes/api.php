<?php

use Illuminate\Support\Facades\Route;
use Modules\Waitlist\Http\Controllers\Api\WaitlistController;

Route::prefix('waitlists')->name('api.waitlists.')->group(function () {
    Route::post('/', [WaitlistController::class, 'store'])->middleware(['throttle:waitlist']);
});
