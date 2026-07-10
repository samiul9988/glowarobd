<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        $limit  = $request->integer('limit', 10);

        $products = DB::table('products')
            ->where('products.published', 1)
            ->where('products.name', 'like', "%{$search}%")
            ->leftJoin('purchase_order_item as last_purchase', function ($join) {
                $join->on('last_purchase.product_id', '=', 'products.id')
                    ->whereRaw('last_purchase.id = (
                        SELECT id FROM purchase_order_item
                        WHERE product_id = products.id
                        ORDER BY updated_at DESC
                        LIMIT 1
                    )');
            })
            ->leftJoin('product_stocks as stock', function ($join) {
                $join->on('stock.product_id', '=', 'products.id')
                    ->whereRaw('stock.id = (
                        SELECT id FROM product_stocks
                        WHERE product_id = products.id
                        ORDER BY updated_at DESC
                        LIMIT 1
                    )');
            })
            ->where('stock.qty', '>', 0)
            ->leftJoin('uploads', 'uploads.id', '=', 'products.thumbnail_img')
            ->select(
                'products.id',
                'products.name',
                'products.unit_price as price',
                'products.discount',
                'products.discount_type',
                'stock.qty as qty',
                'last_purchase.price as purchase_price',
                'uploads.file_name as image',
                'products.created_at'
            )
            ->orderBy('products.created_at', 'desc')
            ->simplePaginate($limit);

        return response()->json([
            'products' => array_map(function ($product) {
                $imageUrl = $product->image ? my_asset($product->image) : static_asset('assets/img/placeholder.jpg');
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'formatted_price' => single_price($product->price),
                    'purchase_price' => $product->purchase_price,
                    'formatted_purchase_price' => single_price($product->purchase_price),
                    'discount' => $product->discount,
                    'discount_type' => $product->discount_type,
                    'qty' => $product->qty,
                    'stock_qty' => $product->qty,
                    'image_url' => $imageUrl,
                ];
            }, $products->items()),
            'next' => $products->nextPageUrl(),
        ]);
    }
}
