<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CloudflareCacheHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only set headers if response is successful
        if (get_setting('enable_clouflare_cache') == 1 && ($response->isSuccessful() || $response->isRedirection())) {
            $response->header('Cache-Control', 'public, s-maxage=18000'); // 3600 seconds (1 hour)
            $response->header('CDN-Cache-Control', 'public, s-maxage=18000'); // 3600 seconds (1 hour)
            $response->headers->remove('Set-Cookie'); // Remove Set-Cookie header to prevent caching issues
        }

        return $response;
    }
}
