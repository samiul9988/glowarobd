<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ProcessRokomariProducts;
use Storage;

class ProductPushCommand extends Command
{
    protected $signature = 'product:push {merchant}';

    protected $description = 'Push products to the specified merchant via queue, with chunking and logging';

    public function handle()
    {
        $merchant = $this->argument('merchant');

        if (blank($merchant)) {
            $this->error('Please specify a merchant. Usage: php artisan product:push {merchant}');
            return;
        }

        switch ($merchant) {
            case 'rokomari':
                $this->pushToRokomari();
                break;
            default:
                $this->error("Merchant '{$merchant}' is not supported.");
                break;
        }
    }

    protected function pushToRokomari()
    {
        if(get_setting('enable_rokomari_service') != 1){
            Log::channel('merchant')->info('Rokomari service is disabled. Skipping product push.');
            $this->info('Rokomari service is disabled. No products pushed.');
            return;
        }
        $alreadyUploadedProducts = array_filter(Storage::disk('public')->exists('rokomari_uploaded_products.txt')
            ? explode(',', Storage::disk('public')->get('rokomari_uploaded_products.txt'))
            : []);

        $chunkSize = 100;
        $jobId = uniqid();

        Log::channel('merchant')->info(PHP_EOL."🕒 Starting processing chunks of {$chunkSize} products in Rokomari with job ID: {$jobId} at " . now()->format('Y-m-d H:i:s'));
        $this->info("Processing products in chunks of {$chunkSize}...");

        Product::with('stocks')->published()
        ->when($alreadyUploadedProducts, function ($query) use ($alreadyUploadedProducts) {
            return $query->whereNotIn('id', $alreadyUploadedProducts);
        })->chunk($chunkSize, function ($products) use ($jobId) {
            ProcessRokomariProducts::dispatch($products); // Dispatch each chunk as a job

            $message = 'Dispatched Rokomari job ('.$jobId.') for product IDs: ' . $products->pluck('id')->implode(', ');
            $this->info($message);
            Log::channel('merchant')->info(PHP_EOL.$message);
        });

        Log::channel('merchant')->info(PHP_EOL."✅ All products dispatched for job ID: {$jobId} at " . now()->format('Y-m-d H:i:s'));
        $this->info('✅ All products dispatched. Queue worker will process them.');
    }
}
