<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ProductsClosingStock;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CalculateClosingStockOld implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // DB::table('products_closing_stocks')->truncate();
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        // $yesterday = Carbon::parse('2024-12-31')->format('Y-m-d');
        $from = date('Y-m-d 00:00:00', strtotime($yesterday));
        $fromx = strtotime($from);

        $to = date('Y-m-d 23:59:59', strtotime($yesterday));
        $tox = strtotime($to);

        // $products = Product::published()->get();

        Product::published()->chunk(100, function ($products) use ($yesterday, $from, $fromx, $to, $tox) {
            foreach ($products as $product) {
                $data = DB::table('product_stocks')
                    ->join('products', 'products.id', '=', 'product_stocks.product_id')
                    ->select(
                        DB::raw("'$yesterday' AS 'date'"),

                        DB::raw("COALESCE((SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date < '$fromx'), 0) AS opening_purchase"),

                        DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.delivery_status <> 'returned' AND order_details.created_at < '$from'), 0) AS opening_sell"),

                        DB::raw("COALESCE((SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox'), 0) AS purchases"),

                        DB::raw("(SELECT purchase_order.id FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_id"),

                        DB::raw("(SELECT purchase_order.po_number FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_number"),

                        DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.delivery_status <> 'returned' AND order_details.created_at BETWEEN '$from' AND '$to'), 0) AS sales"),

                        DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date < '$fromx'), 0) AS opening_minus_adjustment"),

                        DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox'), 0) AS minus_adjustments"),

                        DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date < '$fromx'), 0) AS opening_plus_adjustment"),

                        DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox'), 0) AS plus_adjustments")
                    )
                    ->where('product_id', $product->id)
                    ->get()->toArray();

                if ($data) {
                    $data = $data[0];
                    // dd($data);
                    $openStock = ($data->opening_purchase + $data->opening_plus_adjustment) - ($data->opening_sell + $data->opening_minus_adjustment);
                    $adjustments = ($data->plus_adjustments - $data->minus_adjustments);
                    $closingStock = $openStock + $data->purchases - $data->sales + $adjustments;

                    ProductsClosingStock::updateOrCreate([
                        'product_id' => $product->id,
                        'date' => Carbon::parse($yesterday)->format('Y-m-d 23:59:59'),
                    ], [
                        'closing_stock' => $closingStock,
                        'last_opening_purchase' => $data->opening_purchase,
                        'last_opening_sale' => $data->opening_sell,
                        'last_opening_plus_adjustment' => $data->opening_plus_adjustment,
                        'last_opening_minus_adjustment' => $data->opening_minus_adjustment,
                    ]);
                }
            }
        });
    }
}
