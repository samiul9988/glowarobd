<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DebuggingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true); // start timing

        $response = $next($request);

        $executionTime = round((microtime(true) - $start) * 1000, 2); // in ms
        $ip = $request->ip();

        $route = $request->route();

        \Log::channel('custom')->info('Controller Debug Info', [
            'controller' => class_basename(optional($route?->getController())),
            'action' => $route?->getActionMethod(),
            'url' => $request->fullUrl(),
            'ip' => $ip,
            'execution_time_ms' => $executionTime,
        ]);
        return $response;
    }
}
