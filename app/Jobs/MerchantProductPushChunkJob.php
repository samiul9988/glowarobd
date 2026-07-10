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

class MerchantProductPushChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $productIds;
    protected int $merchantId;
    protected string $batchId;
    protected array $data;

    public function __construct(array $productIds, int $merchantId, string $batchId, array $data)
    {
        $this->productIds = $productIds;
        $this->merchantId = $merchantId;
        $this->batchId    = $batchId;
        $this->data       = $data;
    }

    public function handle()
    {
        $rokomariService = new \App\Services\RokomariService();

        $products = \App\Models\Product::with('stocks')->whereIn('id', $this->productIds)->get();

        foreach ($products as $product) {
            $response = $rokomariService->newProductPush($product);
            $status = $response->status();
            $body = is_array($response->body()) ? $response->body() : json_decode($response->body(), true);
            $success = $body['success'] ?? false;

            Log::channel('merchant')->info("Attempting to push new product to Rokomari: {$product->id}. Response status: {$status}, body: ", $body ?? []);

            if (($response->successful() && $status == 201 && $success) || $status == 403) {
                \App\Models\MerchantProduct::updateOrCreate(
                    ['merchant_id' => $this->merchantId, 'product_id' => $product->id],
                    ['last_price' => $product->unit_price, 'pushed_at' => now()]
                );
                if($status == 201) {
                    Log::channel('merchant')->info("✅ Product pushed successfully: {$product->id}");
                } else {
                    Log::channel('merchant')->info("ℹ️ Product already exists on Rokomari: {$product->id}");
                }
            } elseif($status == 201 && !$success) {
                Log::channel('merchant')->info("❌ Failed to push product ID {$product->id}: {$body['message']}");
            } else {
                $statusMessage = match ($status) {
                    400 => "⛔ Bad Request (400)",
                    404 => "⛔ Not Found (404)",
                    500 => "🔥 Server Error (500)",
                    default => "⁉️ Unknown Error ({$status})",
                };

                Log::channel('merchant')->info("{$statusMessage} while pushing product ID {$product->id}:", $body ?? []);
            }
        }

        $key = "bulk-push:{$this->batchId}:remaining";
        $remaining = Cache::decrement($key);

        if ($remaining === 0) {
            // ✅ Last job → send mail
            $admin = \App\Models\User::where('user_type', 'admin')->whereNotNull('email')->first();
            if ($admin) {
                Log::channel('merchant')->info('Sending completion email to admin...');
                Mail::to($admin['email'])->send(new \App\Mail\JobCompletionMail('product_push', $this->merchantId, $this->data));
            }

            Log::channel('merchant')->info("✅ Bulk product push [{$this->batchId}] completed for merchant {$this->merchantId}");
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
