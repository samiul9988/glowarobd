<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RokomariProductStockUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update product stocks for Rokomari';

    public function handle()
    {
        if(get_setting('enable_rokomari_service') != 1){
            Log::channel('merchant')->info('Rokomari service is disabled. Skipping product stock updating.');
            $this->info('Rokomari service is disabled.');
            return;
        }

        $chunkSize = 100;
        $jobId = uniqid();
        Product::with('stocks')->published()
        ->chunk($chunkSize, function ($products) use ($jobId) {
            $productIds = $products->pluck('id')->toArray();
            update_products_stock($productIds);

            $message = 'Dispatched Rokomari job ('.$jobId.') for product stocks of IDs: ' . implode(', ', $productIds);
            $this->info($message);
            Log::channel('merchant')->info(PHP_EOL.$message);
        });
    }
}
