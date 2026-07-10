<?php

namespace App\Listeners;

use App\Events\ProductStockAdjusted;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Log;

class UpdateStockWhenAdjusted
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ProductStockAdjusted  $event
     * @return void
     */
    public function handle(ProductStockAdjusted $event)
    {
        $item = $event->item;
        if($item){
            $productinfoarray = [
                'product_id' => $item->product_id,
                'variant' => $item->variant,
            ];
            adjust_products_stock($productinfoarray);
        }
    }
    public function handleOld(ProductStockAdjusted $event)
    {
        $item = $event->item;
        $product = Product::find($item->product_id);
        if ($item->variant == null || $item->variant == '' || $item->variant == 0 || $item->variant == 'null') {
            $productStock = ProductStock::where('product_id', $item->product_id)->first();
        } else {
            $productStock = ProductStock::where('id', $item->variant)->where('product_id', $item->product_id)->first();
        }
        $product_variation = $productStock->variant ?? null;
        $previousStock = $productStock->qty;
        $totalPurchase = DB::table('purchase_order_item')
            ->where('product_id', $product->id)
            ->where('variant', $productStock->id)
            ->sum('qty');

        empty($product_variation) ? $totalSales = DB::table('order_details')
        ->join('orders', 'orders.id', '=', 'order_details.order_id')
        ->where('order_details.product_id', $product->id)
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'preorder')
                ->orWhere('order_details.delivery_status', '<>', 'preorder');
        })
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'cancelled')
                ->orWhere('order_details.delivery_status', '<>', 'cancelled');
        })
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'returned')
                ->orWhere('order_details.delivery_status', '<>', 'returned');
        })
        ->sum('order_details.quantity')
        : $totalSales = DB::table('order_details')
        ->join('orders', 'orders.id', '=', 'order_details.order_id')
        ->where('order_details.product_id', $product->id)
        ->where('order_details.variation', $product_variation)
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'preorder')
                ->orWhere('order_details.delivery_status', '<>', 'preorder');
        })
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'cancelled')
                ->orWhere('order_details.delivery_status', '<>', 'cancelled');
        })
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'returned')
                ->orWhere('order_details.delivery_status', '<>', 'returned');
        })
        ->sum('order_details.quantity');

        $totalMinusAdjust = DB::table('stock_adjust_items')
            ->join('stock_adjust', 'stock_adjust.id', '=', 'stock_adjust_items.stock_adjust_id')
            ->where('stock_adjust_items.product_id', $product->id)
            ->where('stock_adjust_items.variant', $productStock->id)
            ->where(function ($query) {
                $query->where('stock_adjust.sa_type', 'damage')->orWhere('stock_adjust.sa_type', 'others');
            })
            ->sum('stock_adjust_items.qty');

        $totalPlusAdjust = DB::table('stock_adjust_items')
            ->join('stock_adjust', 'stock_adjust.id', '=', 'stock_adjust_items.stock_adjust_id')
            ->where('stock_adjust_items.product_id', $product->id)
            ->where('stock_adjust_items.variant', $productStock->id)
            ->where('stock_adjust.sa_type', 'returned')
            ->sum('stock_adjust_items.qty');

        $productStock->qty = $totalPurchase + $totalPlusAdjust - $totalSales - $totalMinusAdjust;
        $productStock->save();

        if($previousStock > 0 && $productStock->qty <= 0) {
            update_products_stock([$product->id]);
        }elseif($previousStock <= 0 && $productStock->qty > 0) {
            update_products_stock([$product->id]);
        }
    }
}
