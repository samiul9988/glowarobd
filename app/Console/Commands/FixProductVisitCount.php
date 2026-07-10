<?php
namespace App\Console\Commands;

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductVisit;

class FixProductVisitCount extends Command
{
    protected $signature = 'products:fix-visit-count';
    protected $description = 'Fix Product Visit Count Discrepancies';

    public function handle()
    {
        $totalProducts = Product::count();
        $fixedCount = 0;

        $bar = $this->output->createProgressBar($totalProducts);
        $bar->start();

        Product::select('id', 'views_count')
            ->chunkById(100, function ($products) use (&$fixedCount, $bar) {
                foreach ($products as $product) {
                    $correctCount = ProductVisit::where('product_id', $product->id)->count();
                    // $this->info("Product ID: {$product->id}, Current Count: {$product->views_count}, Correct Count: {$correctCount}");
                    if ($product->views_count != $correctCount) {
                        $product->update([
                            'views_count' => $correctCount,
                        ]);
                        $fixedCount++;
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Fixed visit counts for {$fixedCount} products.");
    }
}
