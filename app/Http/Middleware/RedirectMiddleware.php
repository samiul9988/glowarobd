<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Barryvdh\Debugbar\Facades\Debugbar;

class RedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $url = $request->url();
        $trimUrl = str_replace(env('APP_URL'), '', $url);
        $trimUrl = Str::startsWith($trimUrl, '/') ? Str::after($trimUrl, '/') : $trimUrl;

        $queryString = $request->getQueryString();
        $rules = Cache::remember('rewrite_urls', now()->addHours(24), function () {
            return \App\Models\RewriteUrl::active()->get();
        });

        $matched_rule = $rules->first(function ($item) use ($trimUrl) {
            return $trimUrl === $item->url;
        });

        if ($matched_rule) {
            $parsedUrl = parse_url($matched_rule->redirect_to);

            if (isset($parsedUrl['host'])) {
                $newPath = $matched_rule->redirect_to;
            } else {
                $newPath = url($matched_rule->redirect_to);
            }
            if($queryString) {
                $newPath .= '?' . $queryString;
            }
            return redirect($newPath);
        }
        return $next($request);
    }
}
