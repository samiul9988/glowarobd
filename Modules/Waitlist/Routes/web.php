<?php

use Illuminate\Support\Facades\Route;

use \Modules\Waitlist\Http\Controllers\Web\WaitlistController;

Route::name('waitlists.')->prefix('waitlists')->group(function () {
    Route::get('/', [WaitlistController::class, 'index'])->name('index');
});
