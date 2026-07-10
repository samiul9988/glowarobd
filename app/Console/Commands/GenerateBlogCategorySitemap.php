<?php

namespace App\Console\Commands;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\BlogCategory;
use Illuminate\Console\Command;

class GenerateBlogCategorySitemap extends Command
{
    protected $signature = 'sitemap:blogs-categories';
    protected $description = 'Generate the blogs categories sitemap';

    public function handle()
    {
        $directory = public_path('sitemaps');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $chunkSize = 1000; // Number of blog categories per sitemap
        $sitemapIndex = 1;
        $currentSitemap = Sitemap::create();
        $currentCount = 0;

        // Process all blog categories in chunks efficiently
        BlogCategory::orderBy('id') // Ensure consistent ordering
            ->chunk(100, function ($categories) use (&$currentSitemap, &$currentCount, &$sitemapIndex, $chunkSize, $directory) {
                foreach ($categories as $category) {
                    // Add blog category to current sitemap
                    $currentSitemap->add(Url::create("/blog/category/{$category->slug}")
                        ->setLastModificationDate($category->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8));

                    $currentCount++;

                    // If we've reached the chunk size, save the sitemap and start a new one
                    if ($currentCount >= $chunkSize) {
                        $currentSitemap->writeToFile($directory . "/sitemap-blogs-categories-{$sitemapIndex}.xml");
                        $this->info("Generated sitemap-blogs-categories-{$sitemapIndex}.xml");

                        // Reset for next sitemap
                        $sitemapIndex++;
                        $currentSitemap = Sitemap::create();
                        $currentCount = 0;
                    }
                }
            });

        // Write the last sitemap if it has any blog categories
        if ($currentCount > 0) {
            $currentSitemap->writeToFile($directory . "/sitemap-blogs-categories-{$sitemapIndex}.xml");
            $this->info("Generated sitemap-blogs-categories-{$sitemapIndex}.xml");
        }

        $this->info('Blog categories sitemaps generated successfully.');
    }
}
