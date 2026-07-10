<?php

namespace App\Console\Commands;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Blog;
use Illuminate\Console\Command;

class GenerateBlogSitemap extends Command
{
    protected $signature = 'sitemap:blogs';
    protected $description = 'Generate the blog sitemap';

    public function handle()
    {
        $directory = public_path('sitemaps');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $chunkSize = 1000; // Number of blogs per sitemap
        $sitemapIndex = 1;
        $currentSitemap = Sitemap::create();
        $currentCount = 0;

        // Process all active blogs in chunks efficiently
        Blog::active()
            ->orderBy('id') // Ensure consistent ordering
            ->chunk(100, function ($blogs) use (&$currentSitemap, &$currentCount, &$sitemapIndex, $chunkSize, $directory) {
                foreach ($blogs as $blog) {
                    // Add blog to current sitemap
                    $currentSitemap->add(Url::create(to_frontend(url("/blog/{$blog->slug}"), 'blog'))
                        ->setLastModificationDate($blog->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8));

                    $currentCount++;

                    // If we've reached the chunk size, save the sitemap and start a new one
                    if ($currentCount >= $chunkSize) {
                        $currentSitemap->writeToFile($directory . "/sitemap-blogs-{$sitemapIndex}.xml");
                        $this->info("Generated sitemap-blogs-{$sitemapIndex}.xml");

                        // Reset for next sitemap
                        $sitemapIndex++;
                        $currentSitemap = Sitemap::create();
                        $currentCount = 0;
                    }
                }
            });

        // Write the last sitemap if it has any blogs
        if ($currentCount > 0) {
            $currentSitemap->writeToFile($directory . "/sitemap-blogs-{$sitemapIndex}.xml");
            $this->info("Generated sitemap-blogs-{$sitemapIndex}.xml");
        }

        $this->info('Blog sitemaps generated successfully.');
    }
}
