<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Product;
use App\Models\Review;

class FixProductRatingCount extends Command
{
    protected $signature = 'fix:product-rating-count';
    protected $description = 'Fix product rating count inconsistencies';

    public function handle()
    {
        // Get total number of unique products having reviews
        $total = Review::where('status', 1)
            ->distinct()
            ->count('product_id');

        if ($total === 0) {
            $this->warn('No product reviews found.');
            return Command::SUCCESS;
        }

        // Create progress bar
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        // Fetch grouped rating counts, 500 records at a time
        Review::where('status', 1)
            ->select('product_id', DB::raw('AVG(rating) as rating_count'))
            ->groupBy('product_id')
            ->orderBy('product_id')
            ->chunk(500, function ($rows) use ($bar) {
                foreach ($rows as $row) {
                    $product = Product::find($row->product_id);

                    if (! $product || $product->rating == round($row->rating_count, 1)) {
                        $bar->advance();
                        continue;
                    }

                    // Log previous and new values
                    // $log = "Product ID: {$row->product_id} | Previous Rating: {$product->rating} | New Rating: " . round($row->rating_count, 1) . "\n";
                    // File::append(storage_path('logs/product_rating_fix_'. date('Y_m_d') .'.log'), $log);

                    // Update product rating
                    $product->rating = round($row->rating_count, 1);
                    $product->save();

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);
        $this->info("Product rating counts updated successfully.");

        return Command::SUCCESS;
    }
}
