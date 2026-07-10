<?php

namespace Modules\Waitlist\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\Waitlist\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapAdminRoutes();

        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->configureRateLimiting();
    }

    /**
     * Define the "admin" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapAdminRoutes()
    {
        if (file_exists($file = module_path('Waitlist', 'Routes') . '/admin.php')) {
            Route::middleware(['web','admin'])->namespace($this->moduleNamespace . '\\Admin')->group($file);
        }
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        if (file_exists($file = module_path('Waitlist', 'Routes') . '/web.php')) {
            Route::middleware('web')->namespace($this->moduleNamespace . '\\Web')->group($file);
        }
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        if (file_exists($file = module_path('Waitlist', 'Routes') . '/api.php')) {
            Route::prefix('api/v3')->middleware('api')->namespace($this->moduleNamespace . '\\Api')->group($file);
        }
    }

    // Throttle key for rate limiting
    protected function configureRateLimiting()
    {
        RateLimiter::for('waitlist', function (Request $request) {
            $contact = $request->input('contact');
            $product = $request->input('product_id');

            // fallback to IP if any is missing
            $key = "waitlist:" . ($contact ?: $request->ip()) . ":" . ($product ?: 'none');

            return Limit::perHour(1)->by($key)
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many waitlist requests for this product. Please try again later.'
                    ], 429);
                });
        });
    }
}
