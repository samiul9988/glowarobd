<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PurgeCloudflareCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $maxExceptions = 2;
    public $timeout = 30;

    protected $slug = '';
    protected $urls = [];
    protected $zoneId;
    protected $apiKey;
    protected $force = false;

    public function __construct(string $slug = '', bool $force = false)
    {
        if(get_setting('enable_clouflare_cache', 0) != 1) {
            return;
        }
        if (blank(get_setting('cloudflare_zone_id'))) {
            // Log::error('CLOUDFLARE_ZONE_ID is not set or is empty, please check your settings.');
            throw new \RuntimeException('CLOUDFLARE_ZONE_ID is not set or is empty, please check your settings.');
        }

        if (blank(get_setting('cloudflare_api_key'))) {
            // Log::error('CLOUDFLARE_API_TOKEN is not set or is empty, please check your settings.');
            throw new \RuntimeException('CLOUDFLARE_API_TOKEN is not set or is empty, please check your settings.');
        }

        if (filled($slug)) {
            $this->slug = $slug;
            $this->urls = [
                url("/{$slug}"),
                url("/products/{$slug}"),
            ];
        }
        $this->force = $force;
        $this->zoneId = get_setting('cloudflare_zone_id');
        $this->apiKey = get_setting('cloudflare_api_key');
    }

    public function handle()
    {
        try {
            $payload = $this->force ? ['purge_everything' => true] : ['files' => $this->urls];
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("https://api.cloudflare.com/client/v4/zones/{$this->zoneId}/purge_cache", $payload);

            if ($response->failed()) {
                // Log::error('Cloudflare cache purge failed', [
                //     'status' => $response->status(),
                //     'response' => $response->json(),
                //     'urls' => $this->urls,
                // ]);
            }
            // Log::info('Cloudflare cache purge successful', [
            //     'status' => $response->status(),
            //     'urls' => $this->urls,
            //     'response' => $response->json(),
            // ]);
        } catch (\Exception $e) {
            // Log::error('Cloudflare cache purge exception', [
            //     'error' => $e->getMessage(),
            //     'urls' => $this->urls,
            // ]);
        }
    }

    public function failed(\Throwable $exception)
    {
        // Log::error('PurgeCloudflareCache job failed', [
        //     'slug' => $this->slug,
        //     'error' => $exception->getMessage(),
        //     'trace' => $exception->getTraceAsString()
        // ]);
    }
}
