<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use App\Imports\MerchantProductsImport;

class RokomariService
{
    protected $apiKey;
    protected $apiSecret;
    protected $accessToken;
    protected $refreshToken;
    protected $refreshTokenValidUntil;
    protected $baseUrl;

    protected $products;
    public $merchant;

    public function __construct($products = [])
    {
        if(get_setting('enable_rokomari_service') != 1){
            Log::channel('merchant')->info('Rokomari service is disabled. Skipping initialization.');
            return;
        }
        $this->products = $products;
        $this->merchant = $this->getMerchant();

        $this->config();
    }

    protected function config()
    {
        if(app()->environment('production')) {
            $this->apiKey = get_setting('rokomari_api_key');
            $this->apiSecret = get_setting('rokomari_api_secret');
            $this->accessToken = get_setting('rokomari_access_token');
            $this->refreshToken = get_setting('rokomari_refresh_token');
            $this->refreshTokenValidUntil = Carbon::parse(get_setting('rokomari_refresh_token_last_updated', now()->addDays(7)));
            $this->baseUrl = get_setting('rokomari_base_url', 'https://www.rokomari.com/api');
        } else {
            $this->apiKey = 'e5ad20ee-2a7f-4b5b-9bf2-3818fcbd35b8';
            $this->apiSecret = 'xxmzOeV6Ts6UTMUed6xHIVVybwo4UCuFDiX3KsS79CAc2j4SFy8OBqXMaz9cAqLjWYahE3bBMqDJho2ocS/Zh5DbeIOGEYCkQDrjxFZ2zrGvsbrZVv7IOAmcxFS986SV';
            $this->accessToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxMDI5MTUiLCJyb2xlcyI6IlJPTEVfVVNFUiIsImV4cCI6MTc1NDIxNTIzNiwiaWF0IjoxNzU0MjE0MzM2LCJlbWFpbCI6Imthc3BlcndpbmRvd0BnbWFpbC5jb20ifQ.hp185Wv6_QBlMLGPBIdcmSx4V2nOQhef03G9V4YJen8';
            $this->refreshToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxMDI5MTUiLCJyb2xlcyI6IlJPTEVfVVNFUiIsImV4cCI6MTc1NDgxOTEzNiwiaWF0IjoxNzU0MjE0MzM2LCJlbWFpbCI6Imthc3BlcndpbmRvd0BnbWFpbC5jb20ifQ.8-IXzv0aKEgF6uDgHeY9VAYONKRxquk1GiUQQ8xo32o';
            $this->baseUrl = get_setting('rokomari_base_url', 'https://sandbox.rokomari.io/api');
        }
        // Ensure we have a valid access token
        if (blank($this->accessToken) || blank($this->refreshToken)) {
            $this->createToken();
        }
    }

    public function getMerchant()
    {
        return Cache::remember('rokomari_merchant', now()->addDay(), function() {
            return User::where('user_type', 'merchant')->where('name', 'Rokomari')->select('id', 'name')->first();
        });
    }

    public static function getMerchantId()
    {
        return (new self())->getMerchant()?->id ?? null;
    }

    public static function getMerchantName()
    {
        return (new self())->getMerchant()?->name ?? null;
    }

    protected function createToken()
    {
        $response = Http::post("{$this->baseUrl}/auth/business/create-token", [
            'apiKey' => $this->apiKey,
            'apiSecret' => $this->apiSecret,
        ]);

        $this->handleTokenResponse($response, 'Create token');
    }

    protected function refreshToken()
    {
        if ($this->refreshTokenValidUntil?->isFuture()) {
            $response = Http::post("{$this->baseUrl}/auth/business/refresh-token", [
                'apiKey' => $this->apiKey,
                'refreshToken' => $this->refreshToken,
            ]);

            $this->handleTokenResponse($response, 'Refresh token');
        } else {
            Log::channel('merchant')->info('Refresh token is expired or not set. Creating new token...');
            $this->createToken();
        }
    }

    protected function handleTokenResponse($response, $action)
    {
        if ($response->successful()) {
            $data = $response->json()['data'] ?? [];
            Log::channel('merchant')->info("{$action} successful", $data);

            if (isset($data['accessToken'], $data['refreshToken'])) {
                $this->updateTokens($data['accessToken'], $data['refreshToken']);
            }
        } else {
            Log::channel('merchant')->info("Failed to {$action}: " . $response->body());
            // throw new \Exception("Failed to {$action}: " . $response->body());
        }
    }

    protected function updateTokens($accessToken, $refreshToken)
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;

        BusinessSetting::updateOrCreate(
            ['type' => 'rokomari_access_token'],
            ['value' => $accessToken]
        );
        BusinessSetting::updateOrCreate(
            ['type' => 'rokomari_refresh_token'],
            ['value' => $refreshToken]
        );
        BusinessSetting::updateOrCreate(
            ['type' => 'rokomari_refresh_token_last_updated'],
            ['value' => now()->toDateTimeString()]
        );
        Cache::forget('business_settings');
    }

    public function updateStock()
    {
        if(get_setting('enable_rokomari_service') != 1){
            Log::channel('merchant')->info('Rokomari service is disabled. Skipping stock update.');
            return;
        }

        foreach ($this->products as $product) {
            try {
                $productId = $product->id;
                $lastStock = optional($product->latestStock)->qty ?? 0;

                $response = $this->updateProductsStock($productId, $lastStock);

                if ($response->successful()) {
                    Log::channel('merchant')->info("✅ SUCCESS: Stock for product ID {$productId} updated successfully.");
                } else {
                    $this->logErrorResponse($response, $productId);
                }
            } catch (\Throwable $e) {
                Log::channel('merchant')->error("💥 Exception while updating stock for product ID {$product->id}: {$e->getMessage()}", [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    protected function updateProductsStock($productId, $lastStock)
    {
        $start = microtime(true);
        Log::channel('merchant')->info("🔄 Updating stock for product ID: {$productId}");

        $response = Http::withHeaders([
            'Authorization' => $this->accessToken,
            'Content-Type'  => 'application/json',
        ])->put("{$this->baseUrl}/v1/business/products/{$productId}/edit-status", [
            "hasVariation" => true,
            "sizeVariationId" => 123,
            "status" => $lastStock > 0 ? "AVAILABLE" : "STOCK_OUT"
        ]);

        $responseTime = microtime(true) - $start;

        if ($response->status() === 401) {
            $this->refreshToken();

            // ⚠️ Add slight delay before retrying to avoid rate-limiting
            usleep(300000); // 300ms
            return $this->updateProductsStock($productId, $lastStock);
        }

        // Log the API interaction
        \App\Models\MerchantApiLog::create([
            'user_id' => request()->user() ? request()->user()->id : null,
            'method' => 'PUT',
            'url' => "{$this->baseUrl}/v1/business/products/{$productId}/edit-status",
            'payload' => [
                "hasVariation" => true,
                "sizeVariationId" => 123,
                "status" => $lastStock > 0 ? "AVAILABLE" : "STOCK_OUT"
            ],
            'response' => $response->body(),
            'response_code' => $response->status(),
            'response_time' => number_format($responseTime * 1000, 2), // Convert to milliseconds
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $response;
    }

    public function newProductPush($product)
    {
        $start = microtime(true);
        Log::channel('merchant')->info("🔄 Pushing new product to Rokomari: {$product->id}");

        $payload = $this->getPayload($product);
        $response = Http::withHeaders([
            'Authorization' => $this->accessToken,
            'Content-Type'  => 'application/json',
        ])->post("{$this->baseUrl}/v1/business/product-entry", $payload);

        $responseTime = microtime(true) - $start;

        if ($response->status() === 401) {
            $this->refreshToken();

            // ⚠️ Add slight delay before retrying to avoid rate-limiting
            usleep(300000); // 300ms
            return $this->newProductPush($product);
        }

        // Log the API interaction
        \App\Models\MerchantApiLog::create([
            'user_id' => request()->user() ? request()->user()->id : null,
            'method' => 'POST',
            'url' => "{$this->baseUrl}/v1/business/product-entry",
            'payload' => $payload,
            'response' => $response->body(),
            'response_code' => $response->status(),
            'response_time' => number_format($responseTime * 1000, 2), // Convert to milliseconds
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $response;
    }

    protected function getPayload($product)
    {
        $photos = collect(explode(',', $product->photos))->filter();
        $variation = [
            "sizeVariationId" => 123,
            // "mrp" => ceil(floatval(getMinimumPriceByVariant($product, optional($product->stocks)->first()))),
            "mrp" => $product->unit_price,
            "purchaseDiscountPercentage" => 0,
            "primaryImage" => uploaded_asset($product->thumbnail_img),
            "multipleImages" => $photos->map(fn($p) => uploaded_asset($p))->all(),
            "videoUrl" => $product->video_link ?? "",
        ];

        $payload = [
            "sellerSku" => $product->id,
            "nameBangla" => $product->name,
            "nameEnglish" => $product->name,
            "hasVariation" => true,
            "productVariations" => [$variation]
        ];

        Log::channel('merchant')->info(PHP_EOL."🔷 PAYLOAD: Payload for '$product->name'", $payload);

        return $payload;
    }

    public function updatePrice($productId, $newPrice)
    {
        $start = microtime(true);
        // dd($this->merchant);
        Log::channel('merchant')->info("🔄 Updating price for product ID: {$productId}");

        $response = Http::withHeaders([
            'Authorization' => $this->accessToken,
            'Content-Type'  => 'application/json',
        ])->put("{$this->baseUrl}/v1/business/products/{$productId}/edit-price", [
            "hasVariation" => true,
            "sizeVariationId" => 123,
            "wholesalePrice" => $newPrice
        ]);

        $responseTime = microtime(true) - $start;

        if ($response->status() === 401) {
            $this->refreshToken();

            // ⚠️ Add slight delay before retrying to avoid rate-limiting
            usleep(300000); // 300ms
            return $this->updatePrice($productId, $newPrice);
        }

        // Log the API interaction
        \App\Models\MerchantApiLog::create([
            'user_id' => request()->user() ? request()->user()->id : null,
            'method' => 'PUT',
            'url' => "{$this->baseUrl}/v1/business/products/{$productId}/edit-price",
            'payload' => [
                "hasVariation" => true,
                "sizeVariationId" => 123,
                "wholesalePrice" => $newPrice
            ],
            'response' => $response->body(),
            'response_code' => $response->status(),
            'response_time' => number_format($responseTime * 1000, 2), // Convert to milliseconds
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $response;
    }

    protected function logErrorResponse($response, $productId, $update='stock')
    {
        $status = $response->status();
        $body = $response->body();

        $statusMessage = match ($status) {
            400 => "⛔ ERROR: Bad Request (400)",
            404 => "⛔ ERROR: Not Found (404)",
            500 => "🔥 ERROR: Server Error (500)",
            default => "⁉️ UNKNOWN ERROR ({$status})",
        };

        Log::channel('merchant')->warning("{$statusMessage} while updating {$update} for product ID {$productId}: {$body}");
    }

    public function importMerchantProducts(string $filePath)
    {
        Excel::import(new MerchantProductsImport($this->getMerchantId()), $filePath); // Default queue
    }
}
