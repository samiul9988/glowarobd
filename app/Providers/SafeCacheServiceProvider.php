<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SafeCache;

class SafeCacheServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('safecache', function () {
            return new SafeCache();
        });
    }

    public function boot()
    {
        //
    }
}
