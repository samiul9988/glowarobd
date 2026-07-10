<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\BlogCollection;
use App\Models\Blog;
use App\Utility\SearchUtility;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $limit = 10;

        $blog_query = Blog::query();

        $blog_query = $blog_query->where('status', 1);
        if($request->name != "" || $request->name != null){
            $blog_query->where('name', 'like', '%'.$request->name.'%');
        }

        if($request->limit != '' || $request->limit != null){
            $limit = $request->limit;
        }

        return new BlogCollection($blog_query->paginate($limit));
    }
}
