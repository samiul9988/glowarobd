<?php

namespace App\Http\Controllers\Api\V3;

use Auth;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Resources\V3\WishlistCollection;

class WishlistController extends Controller
{
    public function index($id)
    {
        $userField = 'user_id';
        if (Str::startsWith($id, 'tmp')) {
            $userField = 'temp_user_id';
        }
        $product_ids = Wishlist::where($userField, $id)->pluck("product_id")->toArray();
        $existing_product_ids = Product::with('stocks')->whereIn('id', $product_ids)->pluck("id")->toArray();

        $query = Wishlist::where($userField, $id)->whereIn("product_id", $existing_product_ids);

        return new WishlistCollection($query->latest()->get());
    }

    public function store(Request $request)
    {
        $wishlist = Wishlist::where([$request->user_field => $request->user_id, 'product_id' => $request->product_id])->first();
        if ($wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Product present in wishlist',
                'is_in_wishlist' => true,
                'product_id' => (int) $request->product_id,
                'wishlist_id' => (int) $wishlist->id
            ], 200);
        } else {
            $wishlist = Wishlist::Create([
                $request->user_field => $request->user_id,
                'product_id' => $request->product_id
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist',
                'is_in_wishlist' => true,
                'product_id' => (int) $request->product_id,
                'wishlist_id' => (int) $wishlist->id
            ], 200);
        }
    }

    public function destroy($id)
    {
        try {
            Wishlist::destroy($id);
            return response()->json(['result' => true, 'success' => true, 'message' => 'Product removed from your wishlist'], 200);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'success' => false, 'message' => $e->getMessage()], 200);
        }

    }

    public function add(Request $request)
    {
        $wishlist = Wishlist::where(['product_id' => $request->product_id, $request->user_field => $request->user_id])->first();
        if ($wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Product present in wishlist',
                'is_in_wishlist' => true,
                'product_id' => (int) $request->product_id,
                'wishlist_id' => (int) $wishlist->id
            ], 200);
        } else {
            $wishlist = Wishlist::create([$request->user_field => $request->user_id, 'product_id' => $request->product_id]);
            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist',
                'is_in_wishlist' => true,
                'product_id' => (int) $request->product_id,
                'wishlist_id' => (int) $wishlist->id
            ], 200);
        }
    }

    public function remove(Request $request)
    {
        $wishlist = Wishlist::where(['product_id' => $request->product_id, $request->user_field => $request->user_id])->first();
        if ($wishlist) {
            $wishlist->delete();
            return response()->json([
                'success' => true,
                'message' => 'Product removed from your wishlist',
                'is_in_wishlist' => false,
                'product_id' => (int) $request->product_id,
                'wishlist_id' => 0
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Product is not in wishlist',
                'is_in_wishlist' => false,
                'product_id' => (int) $request->product_id,
                'wishlist_id' => 0
            ], 200);
        }
    }

    public function isProductInWishlist(Request $request)
    {
        $wishlist = Wishlist::where(['product_id' => $request->product_id, $request->user_field => $request->user_id])->first();
        return response()->json([
            'success' => $wishlist ? true : false,
            'message' => $wishlist ? 'Product present in wishlist' : 'Product is not present in wishlist',
            'is_in_wishlist' => $wishlist ? true : false,
            'product_id' => (int) $request->product_id,
            'wishlist_id' => (int) $wishlist?->id ?? 0
        ], 200);
    }
}
