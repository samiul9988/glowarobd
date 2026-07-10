<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeatureActivation
{
    public function handle(Request $request, Closure $next, ...$activationKeys): Response
    {
        foreach ($activationKeys as $key) {
            if (!get_setting($key, 0)) {
                abort(404);
            }
        }

        return $next($request);
    }
}
