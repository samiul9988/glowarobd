<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function getPageContent(Request $request, $slug)
    {
        $page = \App\Models\Page::where('slug', $slug)->first();

        if ($page) {
            return response()->json([
                'result' => true,
                'data' => [
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'content' => $page->content,
                    'mobile_banner' => $page->mobile_banner ? api_asset($page->mobile_banner) : null,
                    'desktop_banner' => $page->desktop_banner ? api_asset($page->desktop_banner) : null,
                    'meta_title' => $page->meta_title,
                    'meta_description' => $page->meta_description,
                    'keywords' => $page->keywords,
                    'meta_image' => $page->meta_image ? api_asset($page->meta_image) : null,
                ],
            ]);
        } else {
            return response()->json([
                'result' => false,
                'message' => 'Page not found',
            ], 404);
        }
    }
}
