<?php
namespace App\Jobs;

use App\Models\ProductsClosingStock;
use App\Models\ProductStock;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;


class CalculateClosingStock implements ShouldQueue
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
        $products = ProductStock::all();

        foreach ($products as $product) {
            $product_variation = $product->variant;
            $product_id = $product->product_id;
            $variant_id = $product->id;

            createOrUpdateClosingStock($product_variation, $product_id, $variant_id, $yesterday, $from, $to, $fromx, $tox);
        }
    }

}
