<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SearchReindex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:reindex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex the meilisearch data for products, brands, and categories';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Artisan::call('scout:flush', ['model' => 'App\Models\Product']);
        Artisan::call('scout:flush', ['model' => 'App\Models\Brand']);
        Artisan::call('scout:flush', ['model' => 'App\Models\Category']);
        Artisan::call('scout:sync-index-settings');
        $this->info('Reindexing products...');
        Artisan::call('scout:import', ['model' => 'App\Models\Product']);
        $this->info('Products reindexed successfully.');

        $this->info('Reindexing brands...');
        Artisan::call('scout:import', ['model' => 'App\Models\Brand']);
        $this->info('Brands reindexed successfully.');

        $this->info('Reindexing categories...');
        Artisan::call('scout:import', ['model' => 'App\Models\Category']);
        $this->info('Categories reindexed successfully.');

        $this->info('Process is in queue. Please wait...');
        return 0;
    }
}
