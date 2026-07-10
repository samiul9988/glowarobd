<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;

class KireibdService
{
    protected $appId;
    protected $appSecret;
    protected $baseUrl;
    protected $isDisabled = false;

    protected $products;

    public function __construct($products)
    {
        if(get_setting('enable_kireibd_service') != 1){
            $this->isDisabled = true;
            Log::channel('merchant')->info('Kireibd service is disabled. Skipping initialization.');
            return;
        }
        $merchant = User::where('user_type', 'merchant')->where('name', 'kireibd')->first();
        if (!$merchant || blank($merchant->app_id) || blank($merchant->app_key)) {
            $this->isDisabled = true;
            Log::channel('merchant')->error('Kireibd merchant not found or missing credentials. Please ensure the merchant is set up correctly.');
            return;
        }
        $this->appId = $merchant->app_id;
        $this->appSecret = $merchant->app_key;
        $this->baseUrl = 'https://app.kireibd.com/api/supplier/webhook';

        $this->products = $products;
    }

    public function updateStock()
    {
        if($this->isDisabled){
            Log::channel('merchant')->info('Kireibd service is disabled. Skipping stock update.');
            return;
        }

        foreach ($this->products as $product) {
            try {
                $productId = $product->id;
                $lastStock = optional($product->latestStock)->qty ?? 0;

                $response = $this->updateProductsStock($productId, $lastStock);

                if ($response->successful()) {
                    Log::channel('merchant')->info("✅ SUCCESS: Stock for product ID {$productId} updated successfully for merchant Kireibd.");
                } else {
                    $this->logErrorResponse($response, $productId);
                }
            } catch (\Throwable $e) {
                Log::channel('merchant')->error("💥 Exception while updating stock for product ID {$product->id}: {$e->getMessage()} for merchant Kireibd", [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    protected function updateProductsStock($productId, $lastStock)
    {
        $start = microtime(true);
        Log::channel('merchant')->info("🔄 Updating stock for product ID: {$productId} for merchant Kireibd");

        $response = Http::withHeaders([
            'App-ID' => $this->appId,
            'Secret-Key' => $this->appSecret,
            'Content-Type'  => 'application/json',
        ])->post("{$this->baseUrl}/update-stock", [
            "product_id" => $productId,
            "stock" => $lastStock,
        ]);

        $responseTime = microtime(true) - $start;
        // Log the API interaction
        \App\Models\MerchantApiLog::create([
            'user_id' => request()->user() ? request()->user()->id : null,
            'method' => 'POST',
            'url' => "{$this->baseUrl}/update-stock",
            'payload' => [
                "product_id" => $productId,
                "stock" => $lastStock,
            ],
            'response' => $response->body(),
            'response_code' => $response->status(),
            'response_time' => number_format($responseTime * 1000, 2), // Convert to milliseconds
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $response;
    }

    public function updateProduct()
    {
        if($this->isDisabled){
            Log::channel('merchant')->info('Kireibd service is disabled. Skipping product update.');
            return;
        }

        try {
            foreach($this->products as $product) {
                $start = microtime(true);
                $response = Http::withHeaders([
                    'App-ID' => $this->appId,
                    'Secret-Key' => $this->appSecret,
                    'Content-Type'  => 'application/json',
                ])->post("{$this->baseUrl}/products/update", $this->getPayload($product));

                $responseTime = microtime(true) - $start;
                // Log the API interaction
                \App\Models\MerchantApiLog::create([
                    'user_id' => request()->user() ? request()->user()->id : null,
                    'method' => 'POST',
                    'url' => "{$this->baseUrl}/products/update",
                    'payload' => $this->getPayload($product),
                    'response' => $response->body(),
                    'response_code' => $response->status(),
                    'response_time' => number_format($responseTime * 1000, 2), // Convert to milliseconds
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                if ($response->successful()) {
                    Log::channel('merchant')->info("✅ SUCCESS: Product ID {$product->id} updated successfully.");
                } else {
                    $this->logErrorResponse($response, $product->id);
                }
            }
        } catch (\Throwable $e) {
            Log::channel('merchant')->error("💥 Exception while updating product ID {$product->id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function getPayload($product)
    {
        $payload = [
            "id" => $product->id,
            "name" => $product->name,
            "slug" => $product->slug,
            'wholesale_price' => ceil(str_replace(',','',number_format(getMinimumPriceByVariant($product, $product->stocks->first()), 2))),
            'mrp_price' => ceil(str_replace(',','',number_format(getMinimumPriceByVariant($product, $product->stocks->first()), 2))),
        ];

        Log::channel('merchant')->info("Preparing payload for product ID {$product->id}: " . json_encode($payload));
        return $payload;
    }

    protected function logErrorResponse($response, $productId)
    {
        $status = $response->status();
        $body = $response->body();

        $statusMessage = match ($status) {
            401 => "⛔ ERROR: Unauthorized (401)",
            404 => "⛔ ERROR: Not Found (404)",
            422 => "⚠️ ERROR: Unprocessable Entity (422)",
            500 => "🔥 ERROR: Server Error (500)",
            default => "⁉️ UNKNOWN ERROR ({$status})",
        };

        Log::channel('merchant')->warning("{$statusMessage} while updating stock for product ID {$productId}: {$body}");
    }
}
