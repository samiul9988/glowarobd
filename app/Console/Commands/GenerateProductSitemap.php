<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Product;

class GenerateProductSitemap extends Command
{
    protected $signature = 'sitemap:products';
    protected $description = 'Generate the product sitemaps';

    public function handle()
    {
        $directory = public_path('sitemaps');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $chunkSize = 1000; // Number of products per sitemap
        $sitemapIndex = 1;
        $currentSitemap = Sitemap::create();
        $currentCount = 0;

        // Process all products in chunks efficiently
        Product::published()
            ->orderBy('id') // Ensure consistent ordering
            ->chunk(100, function ($products) use (&$currentSitemap, &$currentCount, &$sitemapIndex, $chunkSize, $directory) {
                foreach ($products as $product) {
                    // Add product to current sitemap
                    $currentSitemap->add(Url::create(to_frontend(route('product', $product->slug)))
                        ->setLastModificationDate($product->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8));

                    $currentCount++;

                    // If we've reached the chunk size, save the sitemap and start a new one
                    if ($currentCount >= $chunkSize) {
                        $currentSitemap->writeToFile($directory . "/sitemap-products-{$sitemapIndex}.xml");
                        $this->info("Generated sitemap-products-{$sitemapIndex}.xml");

                        // Reset for next sitemap
                        $sitemapIndex++;
                        $currentSitemap = Sitemap::create();
                        $currentCount = 0;
                    }
                }
            });

        // Write the last sitemap if it has any products
        if ($currentCount > 0) {
            $currentSitemap->writeToFile($directory . "/sitemap-products-{$sitemapIndex}.xml");
            $this->info("Generated sitemap-products-{$sitemapIndex}.xml");
        }

        $this->info('Product sitemaps generated successfully.');
    }
}
