<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\V3\BlogCollection;
use App\Models\Blog;
use App\Utility\SearchUtility;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $blogs = Blog::active()
            ->with('category')
            ->when(filled($request->name), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->name . '%');
            })
            ->latest()
            ->paginate($request->limit ?? 10);
        return new BlogCollection($blogs);
    }
}
