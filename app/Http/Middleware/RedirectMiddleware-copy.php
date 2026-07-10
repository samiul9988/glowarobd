<?php

namespace App\Http\Middleware;

use Barryvdh\Debugbar\Facades\Debugbar;
use Cache;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class RedirectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $path = $request->path();
        // dd($path, $request->getQueryString());
        $hasPostfix = Str::contains($path, '.html');
        $queryString = $request->getQueryString();
        if($hasPostfix) {
            $path = Str::before($path, '.html');
        }

        $pathArray = explode('/', $path);

        $rules = Cache::remember('rewrite_urls', now()->addHours(24), function () {
            return \App\Models\RewriteUrl::active()->get();
        });

        $matched_rule = $rules->first(function ($item) use ($pathArray) {
            // return Str::contains($path, $item->url);
            return in_array($item->url, $pathArray);
        });

        // dd($matched_rule, $pathArray, $path, $queryString, $hasQuery, $hasPostfix);

        if ($matched_rule) {
            $newPathArray = [];
            foreach ($pathArray as $pathPart) {
                if($pathPart == $matched_rule->url) {
                    $newPathArray[] = $matched_rule->redirect_to;
                } else {
                    $newPathArray[] = $pathPart;
                }
            }
            $newPath = implode('/', $newPathArray);

            if($hasPostfix) {
                $newPath .= '.html';
            }
            if($queryString) {
                $newPath .= '?' . $queryString;
            }
            // dd($newPathArray, $newPath, $queryString);
            return redirect(url($newPath));
        }
        return $next($request);
    }
}
