<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdateMerchantProductPriceChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $productIds;
    protected string $priceType;
    protected int $merchantId;
    protected float|int $amount;
    protected string $batchId;
    protected array $data;

    public function __construct(array $productIds, float|int $amount, string $priceType, int $merchantId, string $batchId, array $data)
    {
        $this->productIds = $productIds;
        $this->amount     = $amount;
        $this->priceType  = $priceType;
        $this->merchantId = $merchantId;
        $this->batchId    = $batchId;
        $this->data       = $data;
    }

    public function handle()
    {
        $rokomariService = new \App\Services\RokomariService();

        $products = \App\Models\Product::with('lastPurchaseOrderItem:id,product_id,price')->whereIn('id', $this->productIds)->get();

        foreach ($products as $product) {
            $minPrice = $product->lastPurchaseOrderItem?->price ?? 0;
            $maxPrice = $product->unit_price;
            if($minPrice == 0) {
                $newPrice = $this->priceType === 'percentage'
                ? $maxPrice - ($maxPrice * $this->amount / 100)
                : $maxPrice - $this->amount;
            }else{
                $newPrice = $this->priceType === 'percentage'
                ? $minPrice + ($minPrice * $this->amount / 100)
                : $minPrice + $this->amount;
            }

            if ($newPrice < $minPrice || $newPrice > $maxPrice) {
                Log::channel('merchant')->info("⚠️ Skipped product ID {$product->id}: invalid price calculated ({$newPrice}).");
                continue;
            }

            $response = $rokomariService->updatePrice($product->id, $newPrice);
            $status = $response->status();
            $body = is_array($response->body()) ? $response->body() : json_decode($response->body(), true);
            $success = $body['result'] ?? false;

            Log::channel('merchant')->info("Attempting to update price for product ID {$product->id} to {$newPrice}. Response status: {$status}, body: ", $body ?? []);

            if ($response->successful() && $status == 200 && $success) {
                \App\Models\MerchantProduct::updateOrCreate(
                    ['merchant_id' => $this->merchantId, 'product_id' => $product->id],
                    ['last_price' => $newPrice, 'price_updated_at' => now()]
                );
                Log::channel('merchant')->info("✅ Updated product ID {$product->id} to {$newPrice}.");
            } elseif($status == 200 && !$success) {
                Log::channel('merchant')->info("❌ Failed to update product ID {$product->id}: {$body['message']}");
            } else {
                $statusMessage = match ($status) {
                    400 => "⛔ Bad Request (400)",
                    404 => "⛔ Not Found (404)",
                    500 => "🔥 Server Error (500)",
                    default => "⁉️ Unknown Error ({$status})",
                };

                Log::channel('merchant')->info("{$statusMessage} while updating product ID {$product->id}:", $body ?? []);
            }
        }

        $key = "bulk-update:{$this->batchId}:remaining";
        $remaining = Cache::decrement($key);

        if ($remaining === 0) {
            // ✅ Last job → send mail
            $admin = \App\Models\User::where('user_type', 'admin')->whereNotNull('email')->first();
            if ($admin) {
                Log::channel('merchant')->info('Sending completion email to admin...');
                Mail::to($admin['email'])->send(new \App\Mail\JobCompletionMail('price_update', $this->merchantId, $this->data));
            }

            Log::channel('merchant')->info("✅ Bulk price update [{$this->batchId}] completed for merchant {$this->merchantId}");
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Job failed: ' . $exception->getMessage(), [
            'job' => self::class,
            'exception' => $exception->getTraceAsString()
        ]);
    }
}
