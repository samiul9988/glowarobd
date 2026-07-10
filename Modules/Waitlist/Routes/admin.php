<?php

use Illuminate\Support\Facades\Route;
use Modules\Waitlist\Http\Controllers\Admin\WaitlistController;

Route::name('admin.waitlists.')->prefix('admin/waitlists')->group(function () {
    Route::get('/', [WaitlistController::class, 'index'])->name('index');
    Route::delete('destroy/{id}', [WaitlistController::class, 'destroy'])->name('destroy');
    Route::post('bulk-destroy', [WaitlistController::class, 'bulkDestroy'])->name('bulk-destroy');
    Route::post('notify', [WaitlistController::class, 'notify'])->name('notify');
});
