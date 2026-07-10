<?php
use Illuminate\Http\Request;

if(get_setting('enable_clouflare_cache') == 1) {
    // Route::middleware(['cfcache'])->group(function () {
    //     Route::get('/{slug}', 'HomeController@product')->name('product');
    // });
}
