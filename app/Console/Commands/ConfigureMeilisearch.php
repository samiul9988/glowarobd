<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use MeiliSearch\Client;

class ConfigureMeilisearch extends Command
{
    protected $signature = 'search:configure';
    protected $description = 'Configure Meilisearch index settings';

    public function handle()
    {
        $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

        // Configure your products index
        $index = $client->index(config('scout.prefix') . 'products_index');

        $index->updateSettings([
            'pagination' => [
                'maxTotalHits' => Product::published()->count()
            ]
        ]);

        $this->info('Meilisearch index configured successfully!');

        return 0;
    }
}
