<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckForDisableWebRoutes
{
    public function handle(Request $request, Closure $next)
    {
        if (get_setting('disable_web_routes') == 1) {
            $data = [
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'routeName' => optional($request->route())->getName(),
            ];
            Log::channel('redirection')->info('Blocked web route access', $data);

            if($request->path() === '/') {
                return redirect()->route('admin.dashboard');
            }
            abort(404);
        }
        return $next($request);
    }
}
