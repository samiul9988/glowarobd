<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Category;

class GenerateCategorySitemap extends Command
{
    protected $signature = 'sitemap:categories';
    protected $description = 'Generate the category sitemap';

    public function handle()
    {
        $directory = public_path('sitemaps');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $chunkSize = 1000; // Number of categories per sitemap
        $sitemapIndex = 1;
        $currentSitemap = Sitemap::create();
        $currentCount = 0;

        // Process all categories in chunks efficiently
        Category::active()
            ->orderBy('id') // Ensure consistent ordering
            ->chunk(100, function ($categories) use (&$currentSitemap, &$currentCount, &$sitemapIndex, $chunkSize, $directory) {
                foreach ($categories as $category) {
                    // Add category to current sitemap
                    $currentSitemap->add(Url::create(to_frontend(url("/category/{$category->slug}"), 'category'))
                        ->setLastModificationDate($category->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8));

                    $currentCount++;

                    // If we've reached the chunk size, save the sitemap and start a new one
                    if ($currentCount >= $chunkSize) {
                        $currentSitemap->writeToFile($directory . "/sitemap-categories-{$sitemapIndex}.xml");
                        $this->info("Generated sitemap-categories-{$sitemapIndex}.xml");

                        // Reset for next sitemap
                        $sitemapIndex++;
                        $currentSitemap = Sitemap::create();
                        $currentCount = 0;
                    }
                }
            });

        // Write the last sitemap if it has any categories
        if ($currentCount > 0) {
            $currentSitemap->writeToFile($directory . "/sitemap-categories-{$sitemapIndex}.xml");
            $this->info("Generated sitemap-categories-{$sitemapIndex}.xml");
        }

        $this->info('Category sitemaps generated successfully.');
    }
}
