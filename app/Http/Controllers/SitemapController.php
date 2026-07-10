<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SitemapController extends Controller
{
    public function index()
    {
        if(file_exists(public_path('sitemap.xml'))){
            return response()->file(public_path('sitemap.xml'));
        }

        Artisan::call('sitemap:index');

        return response()->file(public_path('sitemap.xml'), ['Content-Type' => 'application/xml']);
    }

    public function show($file)
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
