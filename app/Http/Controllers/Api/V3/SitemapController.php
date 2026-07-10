<?php

namespace App\Http\Controllers\Api\V3;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SitemapController extends Controller
{
    public function index()
    {
        if(file_exists(public_path('sitemaps/sitemap.xml'))){
            return response()->file(public_path('sitemaps/sitemap.xml'));
        }

        dispatch(function () {
            Artisan::call('sitemap:index');
        })->afterResponse();

        return response()->file(public_path('sitemaps/sitemap.xml'), ['Content-Type' => 'application/xml']);
    }

    public function show($file = 'sitemap.xml')
    {
        if(file_exists(public_path('sitemaps/'.$file))){
            return response()->file(public_path('sitemaps/'.$file), ['Content-Type' => 'application/xml']);
        }

        abort(404);
    }

    public function feed()
    {
        $path = Storage::disk('public')->path('facebook-feed.xml');

        abort_if(!file_exists($path), 404);

        return response()->file($path, ['Content-Type' => 'application/xml']);
    }
}
