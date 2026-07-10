<?php

namespace App\Console\Commands;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Page;
use Illuminate\Console\Command;

class GeneratePageSitemap extends Command
{
    protected $signature = 'sitemap:pages';
    protected $description = 'Generate the page sitemap';

    public function handle()
    {
        $directory = public_path('sitemaps');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $chunkSize = 1000; // Number of pages per sitemap
        $sitemapIndex = 1;
        $currentSitemap = Sitemap::create();
        $currentCount = 0;

        // Process all pages in chunks efficiently
        // Note: Consider adding ->published() or ->active() scope if available
        Page::orderBy('id') // Ensure consistent ordering
            ->chunk(100, function ($pages) use (&$currentSitemap, &$currentCount, &$sitemapIndex, $chunkSize, $directory) {
                foreach ($pages as $page) {
                    // Determine the correct URL slug based on page type
                    $slug = $page->type == 'custom_page' ? "/page/{$page->slug}" : "/{$page->slug}";

                    // Add page to current sitemap
                    $currentSitemap->add(Url::create(to_frontend(url($slug), 'page'))
                        ->setLastModificationDate($page->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8));

                    $currentCount++;

                    // If we've reached the chunk size, save the sitemap and start a new one
                    if ($currentCount >= $chunkSize) {
                        $currentSitemap->writeToFile($directory . "/sitemap-pages-{$sitemapIndex}.xml");
                        $this->info("Generated sitemap-pages-{$sitemapIndex}.xml");

                        // Reset for next sitemap
                        $sitemapIndex++;
                        $currentSitemap = Sitemap::create();
                        $currentCount = 0;
                    }
                }
            });

        // Write the last sitemap if it has any pages
        if ($currentCount > 0) {
            $currentSitemap->writeToFile($directory . "/sitemap-pages-{$sitemapIndex}.xml");
            $this->info("Generated sitemap-pages-{$sitemapIndex}.xml");
        }

        $this->info('Page sitemaps generated successfully.');
    }
}
