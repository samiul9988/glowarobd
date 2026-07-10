<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use App\Services\KireibdService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class KireibdJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productIds = [];
    protected $action;

    public function __construct(array $productIds, string $action = 'update')
    {
        $this->productIds = $productIds;
        $this->action = $action;
    }

    public function handle()
    {
        if($this->action === 'stock') {
            Log::channel('merchant')->info('Kireibd stock update job started for product IDs: ' . implode(',', $this->productIds));
            $products = Product::with('latestStock', 'stocks')->whereIn('id', $this->productIds)->get();
            $service = new KireibdService($products);
            $service->updateStock();
        } else {
            $products = Product::with('latestStock', 'stocks', 'customFieldsData.productCustomField', 'customFieldsData.metaObject:id', 'customFieldsData.metaObject.items')->whereIn('id', $this->productIds)->get();
            $service = new KireibdService($products);
            $service->updateProduct();
        }
    }
}
