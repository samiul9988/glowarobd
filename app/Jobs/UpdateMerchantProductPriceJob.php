<?php

namespace App\Jobs;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Jobs\UpdateMerchantProductPriceChunkJob;

class UpdateMerchantProductPriceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;
    protected int $merchantId;

    public function __construct(array $data, int $merchantId)
    {
        $this->data = $data;
        $this->merchantId = $merchantId;
    }

    public function handle()
    {
        $productIds   = $this->data['product_ids'] ?? [];
        $amount       = $this->data['amount'] ?? 0;
        $price_type   = $this->data['price_type'] ?? 'flat';
        $brand_id     = $this->data['brand_id'] ?? null;
        $category_id  = $this->data['category_id'] ?? null;
        $search       = $this->data['search'] ?? null;

        if ($amount == 0) {
            Log::channel('merchant')->error("❌ ERROR: Invalid parameters for bulk price update.");
            return;
        }

        $batchId = Str::uuid()->toString();
        // Build query
        if (!empty($productIds) && is_array($productIds)) {
            $products = \App\Models\Product::whereIn('id', $productIds);
        } else {
            $products = \App\Models\Product::latest()->published()
                ->when($brand_id, fn($q) => $q->where('brand_id', $brand_id))
                ->when($category_id, fn($q) => $q->where('category_id', $category_id))
                ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        // Dispatch smaller jobs per chunk
        $count = 0;
        $products->chunk(50, function ($productChunk) use ($amount, $price_type, $batchId, &$count) {
            $ids = $productChunk->pluck('id')->toArray();
            UpdateMerchantProductPriceChunkJob::dispatch(
                $ids,
                $amount,
                $price_type,
                $this->merchantId,
                $batchId,
                $this->data
            );
            $count++;
        });

        // Save counter in cache for this batch
        Cache::put("bulk-update:{$batchId}:remaining", $count, now()->addHour());

        Log::channel('merchant')->info("🚀 Dispatched {$count} chunk jobs for bulk price update [{$batchId}] for merchant {$this->merchantId}");
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Job failed: ' . $exception->getMessage(), [
            'job' => self::class,
            'exception' => $exception->getTraceAsString()
        ]);
    }
}
