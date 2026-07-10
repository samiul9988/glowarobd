<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\AttendeeOvertime;
use App\Models\Leave;
use App\Models\Order;
use App\Observers\AttendanceObserver;
use App\Observers\AttendeeOvertimeObserver;
use App\Observers\LeaveObserver;
use App\Observers\OrderObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Jenssegers\Agent\Agent;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Temporarily disabled until SSL is set up for api.glowaro.com
        // if (app()->environment('production')) {
        //     URL::forceScheme('https');
        // }
        // Define the file path where you want to store the JSON file
        storeJsonData();
        Schema::defaultStringLength(255);
        Paginator::useBootstrap();

        view()->composer('*', function ($view) {
            if (str_starts_with($view->name(), 'errors::') || ! app()->bound('auth')) {
                return;
            }

            $user = auth()->check() ? auth()->user() : null;

            // Load relationships once
            if ($user && $user->user_type === 'customer') {
                $user->loadMissing('customeringroup.group');
            }

            // Set currentlyAuthenticatedUser
            if (!$view->offsetExists('currentlyAuthenticatedUser')) {
                $view->with('currentlyAuthenticatedUser', $user);
            }

            if (! $view->offsetExists('authPermissions')) {
                $permissions = $user
                    ? json_decode($user?->staff?->role?->permissions ?? '[]', true) ?? []
                    : [];

                $view->with('_authPermissions', $permissions);
            }

            $view->with('agent', app('agent'));
        });

        // Register observers
        $this->initObservers();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('agent', function () {
            return new Agent;
        });
    }

    public function initObservers()
    {
        Order::observe(OrderObserver::class);
        Attendance::observe(AttendanceObserver::class);
        AttendeeOvertime::observe(AttendeeOvertimeObserver::class);
        Leave::observe(LeaveObserver::class);
    }
}
