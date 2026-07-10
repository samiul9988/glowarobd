<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use App\Services\RokomariService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdateProductStockForRokomari implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productIds = [];

    public function __construct(array $productIds)
    {
        $this->productIds = $productIds;
    }

    public function handle()
    {
        $products = Product::with('latestStock', 'stocks')->whereIn('id', $this->productIds)->get();
        $service = new RokomariService($products);
        $service->updateStock();
    }
}
