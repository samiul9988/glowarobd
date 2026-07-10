<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogCategory;
use App\Models\Blog;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = null;
        $blogs = Blog::with('category')->orderBy('created_at', 'desc');

        if ($request->search != null){
            $blogs = $blogs->where('title', 'like', '%'.$request->search.'%');
            $sort_search = $request->search;
        }

        $blogs = $blogs->paginate(15);

        return view('backend.blog_system.blog.index', compact('blogs','sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $blog_categories = BlogCategory::all();
        return view('backend.blog_system.blog.create', compact('blog_categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'category_id' => 'required',
            'title' => 'required|max:255',
        ]);

        $blog = new Blog;

        $blog->category_id = $request->category_id;
        $blog->title = $request->title;
        $blog->banner = $request->banner;
        $blog->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        $blog->short_description = $request->short_description;
        $blog->description = $request->description;

        $blog->meta_title = $request->meta_title;
        $blog->meta_img = $request->meta_img;
        $blog->meta_description = $request->meta_description;
        $blog->meta_keywords = $request->meta_keywords;

        $blog->save();

        flash(('Blog post has been created successfully'))->success();
        return redirect()->route('blog.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $blog = Blog::find($id);
        $blog_categories = BlogCategory::all();

        return view('backend.blog_system.blog.edit', compact('blog','blog_categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required',
            'title' => 'required|max:255',
        ]);

        // dd($request->all());
        $blog = Blog::find($id);

        $old_slug = $blog->slug;
        $blog->category_id = $request->category_id;
        $blog->title = $request->title;
        $blog->banner = $request->banner;
        // $blog->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        $blog->slug = Str::slug($request->slug);
        $blog->short_description = $request->short_description;
        $blog->description = $request->description;

        $blog->meta_title = $request->meta_title;
        $blog->meta_img = $request->meta_img;
        $blog->meta_description = $request->meta_description;
        $blog->meta_keywords = $request->meta_keywords;

        $blog->save();

        if($old_slug != $blog->slug && filled($request->rewrite_url)){
            rewrite_url('blog/'.$old_slug, 'blog/'.$blog->slug);
        }
        flash(('Blog post has been updated successfully'))->success();
        return redirect()->route('blog.index');
    }

    public function change_status(Request $request) {
        $blog = Blog::find($request->id);
        $blog->status = $request->status;

        $blog->save();
        return 1;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $blog = Blog::find($id);
        remove_rewrite_url('blog/'.$blog->slug);
        $blog->delete();

        flash(('Blog post has been deleted successfully'))->success();
        return redirect()->route('blog.index');
    }


    public function all_blog(Request $request, $category_id = null) {

        $categories = BlogCategory::all();
        $blogs = Blog::where('status', 1);

        if($category_id != null){
            $blogs->where('category_id', $category_id);
        }
        $blogs = $blogs->orderBy('created_at', 'desc')->paginate(12);

        return view("frontend.blog.listing", compact('blogs', 'categories', 'category_id'));
    }

    public function blog_details(Request $request, $slug) {
        $isApp = $request->has('isApp') ? true : false;
        $categories = BlogCategory::all();
        $blog = Blog::where('slug', $slug)->first();
        return view("frontend.blog.details", compact('blog', 'categories', 'isApp'));
    }

    public function blogByCategory(Request $request, $category_slug)
    {
        $category = BlogCategory::where('slug', $category_slug)->first();
        if ($category != null) {
            return $this->all_blog($request, $category->id);
        }
        abort(404);
    }
}
