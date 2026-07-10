<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:fixstock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Product Stock to original';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $products = Product::all();
        foreach($products as $product){
            $product = Product::find($product->id);
            $product_variation = null;
            $productStock = $product->stocks->where('variant', $product_variation)->first();

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
        }
        $this->info('Product stock reset command was successful!');
    }
}
