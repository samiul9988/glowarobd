<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DynamicApiMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (
            get_setting('guest_order_activation') == 1 &&
            $request->header('source', 'app') === 'web'
        ) {
            // Guest allowed
            return $next($request);
        }

        // Otherwise require auth
        return app('auth')->guard('api')->authenticate()
            ? $next($request)
            : response()->json(['message' => 'Unauthenticated'], 401);
    }
}
