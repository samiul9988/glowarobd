<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrderItem;
use App\Models\StockAdjustItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdjustStock
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
     * @param  \App\Events\OrderPlaced  $event
     * @return void
     */
    public function handle(OrderPlaced $event)
    {
        $orderDetails = $event->orderDetails;
        if($orderDetails){
            $productinfoarray = [];
            foreach($orderDetails as $item){
                $productStock = ProductStock::where('product_id', $item->product_id)
                    ->when($item->variation, fn($q) => $q->where('variant', $item->variation))
                    ->first();
                $productinfoarray = [
                    'product_id' => $item->product_id,
                    'variant' => $productStock->id,
                ];
                adjust_products_stock($productinfoarray);
            }
        }
    }

    public function handleOld(OrderPlaced $event)
    {
        $orderDetails = $event->orderDetails;
        if($orderDetails){
            foreach($orderDetails as $item){
                $product = Product::find($item->product_id);
                $product_variation = $item->variation;
                $productStock = $product->stocks->where('variant', $product_variation)->first();

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
                ->where('order_details.variation', $item->variation)
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
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderPlaced $event, Throwable $exception): void
    {
        Log::error($exception);
    }
}
