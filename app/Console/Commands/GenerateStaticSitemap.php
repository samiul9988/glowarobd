<?php

namespace App\Console\Commands;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Console\Command;

class GenerateStaticSitemap extends Command
{
    protected $signature = 'sitemap:static';
    protected $description = 'Generate the static sitemap';

    public function handle()
    {
        $directory = public_path('sitemaps');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $data = [
            '/',
            '/users/login',
            '/users/registration',
        ];

        $sitemap = Sitemap::create();
        foreach ($data as $item) {
            $sitemap->add(Url::create($item)
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.8));
        }

        // Fixed filename to avoid conflict with dynamic pages sitemap
        $sitemap->writeToFile($directory . "/sitemap-static.xml");

        $this->info('Static sitemap generated successfully.');
    }
}
