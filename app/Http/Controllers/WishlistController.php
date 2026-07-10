<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_id = Auth::check() ? Auth::id() : $request->temp_user_id;
        $wishlists = Wishlist::whereNotNull($request->user_field)->where($request->user_field, $user_id)->paginate(9);
        return view('frontend.user.view_wishlist', compact('wishlists'));
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
        $user_id = Auth::check() ? Auth::id() : $request->temp_user_id;
        $wishlist = Wishlist::whereNotNull($request->user_field)
            ->where($request->user_field, $user_id)
            ->where('product_id', $request->id);
        if(!$wishlist->exists()){
            $wishlist = new Wishlist;
            $wishlist->{$request->user_field} = $user_id;
            $wishlist->product_id = $request->id;
            $wishlist->save();
            return response()->json([
                'success' => true,
                'html' => view('frontend.partials.wishlist')->render(),
                'message' => 'Item has been added to wishlist'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Item already in wishlist'
            ]);
        }
    }

    public function remove(Request $request)
    {
        $wishlist = Wishlist::findOrFail($request->id);
        if($wishlist!=null){
            if(Wishlist::destroy($request->id)){
                return view('frontend.partials.wishlist');
            }
        }
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
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
