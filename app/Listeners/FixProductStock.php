<?php

namespace App\Listeners;

use App\Events\ProductStockAffected;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class FixProductStock
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
     * @param  \App\Events\ProductStockAffected  $event
     * @return void
     */
    public function handle(ProductStockAffected $event)
    {
        // Commented out because it is not used
        return false;
        $transaction = $event->transaction;
        try {
            $productStock = ProductStock::where('product_id', $transaction['product_id'])
                ->when($transaction['variation'], fn($q) => $q->where('variant', $transaction['variation']))
                ->first();
                $productinfoarray = [
                    'product_id' => $transaction['product_id'],
                    'variant' => $productStock->id,
                ];
                Log::info('Product stock affected: ' . json_encode($productinfoarray));
                adjust_products_stock($productinfoarray);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
    public function handleOld(ProductStockAffected $event)
    {
        // Commented out because it is not used
        return false;
        $transaction = $event->transaction;

        try {
            $product = Product::with('stocks')->find($transaction['product_id']);
            $product_variation = $transaction['variant'];
            $productStock = $product->stocks->where('variant', $product_variation)->first();

            if (empty($productStock)) {
                $productStock = $product->stocks->first();
            }

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
                ->where('order_details.variation', $productStock->variant)
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
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(ProductStockAffected $event, Throwable $exception): void
    {
        Log::error($exception);
    }
}
