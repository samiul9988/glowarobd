<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Brand;

class GenerateBrandSitemap extends Command
{
    protected $signature = 'sitemap:brands';
    protected $description = 'Generate the brand sitemap';

    public function handle()
    {
        $directory = public_path('sitemaps');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $chunkSize = 1000; // Number of brands per sitemap
        $sitemapIndex = 1;
        $currentSitemap = Sitemap::create();
        $currentCount = 0;

        // Process all brands in chunks efficiently
        Brand::query()
            ->orderBy('id') // Ensure consistent ordering
            ->chunk(100, function ($brands) use (&$currentSitemap, &$currentCount, &$sitemapIndex, $chunkSize, $directory) {
                foreach ($brands as $brand) {
                    // Add brand to current sitemap
                    $currentSitemap->add(Url::create(to_frontend(url("/brand/{$brand->slug}"), 'brand'))
                        ->setLastModificationDate($brand->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8));

                    $currentCount++;

                    // If we've reached the chunk size, save the sitemap and start a new one
                    if ($currentCount >= $chunkSize) {
                        $currentSitemap->writeToFile($directory . "/sitemap-brands-{$sitemapIndex}.xml");
                        $this->info("Generated sitemap-brands-{$sitemapIndex}.xml");

                        // Reset for next sitemap
                        $sitemapIndex++;
                        $currentSitemap = Sitemap::create();
                        $currentCount = 0;
                    }
                }
            });

        // Write the last sitemap if it has any brands
        if ($currentCount > 0) {
            $currentSitemap->writeToFile($directory . "/sitemap-brands-{$sitemapIndex}.xml");
            $this->info("Generated sitemap-brands-{$sitemapIndex}.xml");
        }

        $this->info('Brand sitemaps generated successfully.');
    }
}
