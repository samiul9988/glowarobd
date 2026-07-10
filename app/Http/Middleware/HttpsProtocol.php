<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HttpsProtocol {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // return $next($request);
        // if (env('FORCE_HTTPS') == "On" && !$request->secure()) {
        //     return redirect()->secure($request->getRequestUri());
        // }
        return $next($request);
    }
}
