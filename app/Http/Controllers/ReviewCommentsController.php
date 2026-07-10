<?php

namespace App\Http\Controllers;

use App\Models\ReviewComments;
use Illuminate\Http\Request;
use Coder71ecom71\Ecom71Repo\Ecom71Repo;

class ReviewCommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reviewcomments = ReviewComments::orderBy('created_at', 'desc')->get();
        return view('backend.product.reviewcomments.index', compact('reviewcomments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $reviewcomments = new ReviewComments;
        $reviewcomments->title = $request->title;
        $reviewcomments->save();

        flash(('Review Comments has been inserted successfully'))->success();
        return redirect()->route('reviewcomments.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $reviewcomment = ReviewComments::findOrFail($id);
        return view('backend.product.reviewcomments.edit', compact('reviewcomment'));
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
        $reviewcomments = ReviewComments::findOrFail($id);
        $reviewcomments->title = $request->title;
        $reviewcomments->save();

        flash(('Review Comments has been updated successfully'))->success();
        return redirect()->route('reviewcomments.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ReviewComments::destroy($id);
        flash(('Review Comments has been deleted successfully'))->success();
        return redirect()->route('reviewcomments.index');

    }
}
