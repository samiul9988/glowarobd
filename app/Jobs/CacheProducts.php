<?php

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CacheProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        // Log::info('CacheProducts job initialized at ' . now()->format('Y-m-d H:i:s'));
    }

    public function handle()
    {
        $cachedProductsFilePath = storage_path('app/public/products/get_cached_products.json');
        $directory = dirname($cachedProductsFilePath);

        try {
            // Ensure directory exists
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Write to temporary file first
            $tempFile = tempnam($directory, 'products_temp');

            // Chunked query for memory efficiency
            $products = \App\Models\Product::with([
                    'stocks',
                    'productprices',
                    'flash_deal_product.flash_deals'
                ])
                ->where('published', 1)
                ->where('approved', '1')
                ->where('auction_product', 0)
                ->chunk(200, function ($chunk) use ($tempFile) {
                    file_put_contents(
                        $tempFile,
                        json_encode($chunk),
                        FILE_APPEND
                    );
                });

            // Atomic file replacement
            rename($tempFile, $cachedProductsFilePath);

            // Set proper permissions
            chmod($cachedProductsFilePath, 0644);

            // Log::info('CacheProducts job ends at ' . now()->format('Y-m-d H:i:s'));

        } catch (\Exception $e) {
            Log::error('Failed to generate products cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
