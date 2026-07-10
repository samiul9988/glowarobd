<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use App\Models\CourierSuccessRate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;

class CourierSuccessRateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $phone;
    protected ?string $apiKey;
    protected bool $enabled;
    protected int $interval;

    public function __construct(string $phone)
    {
        $this->phone    = $phone;
        $this->enabled  = get_setting('enable_courier_success_rate') == 1;
        $this->apiKey   = get_setting('courier_success_rate_api_key') ?? null;
        $this->interval = (int) (get_setting('courier_success_rate_update_interval') ?? 7);
    }

    public function handle(): void
    {
        if (! $this->enabled || empty($this->apiKey)) {
            return;
        }

        $this->fetchCourierSuccessRate();
    }

    private function fetchCourierSuccessRate(): void
    {
        try {
            $model = CourierSuccessRate::where('phone', $this->phone)->first();
            if ($model && now()->diffInDays($model->updated_at) < $this->interval) {
                return; // Skip if recently updated
            }

            Log::channel('custom')->info('Fetching Courier Success Rate', [
                'phone' => $this->phone,
            ]);

            $response = Http::timeout(10)->get('https://dash.hoorin.com/api/courier/search', [
                'apiKey'     => $this->apiKey,
                'searchTerm' => str_replace('-','',$this->phone),
            ]);

            if (! $response->successful()) {
                Log::channel('custom')->info('Courier API failed', [
                    'phone'   => $this->phone,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);
                return;
            }

            $data    = $response->json();
            $summary = $data['Summaries'] ?? [];

            [$mappedSummary, $overallRate] = $this->processSummary($summary);

            CourierSuccessRate::updateOrCreate(
                ['phone' => $this->phone],
                [
                    'summary'      => $mappedSummary,
                    'success_rate' => $overallRate,
                ]
            );

        } catch (Exception $e) {
            Log::channel('custom')->info('Courier Success Rate Job Error', [
                'phone'   => $this->phone,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }

    private function processSummary(array $summary): array
    {
        $mapped    = [];
        $total     = 0;
        $delivered = 0;

        foreach ($summary as $courier => $data) {
            if(strtolower($courier) === 'pathao') {
                $t = Arr::get($data, 'Total Delivery', 0);
                $d = Arr::get($data, 'Successful Delivery', 0);
                $r = Arr::get($data, 'Canceled Delivery', 0);
            } else {
                $t = Arr::get($data, 'Total Parcels', 0);
                $d = Arr::get($data, 'Delivered Parcels', 0);
                $r = Arr::get($data, 'Canceled Parcels', 0);
            }

            $mapped[$courier] = [
                'total_parcels'     => $t,
                'delivered_parcels' => $d,
                'returned_parcels'  => $r,
                'success_rate'      => $t > 0 ? (int) round(($d / $t) * 100) : 0,
            ];

            $total     += $t;
            $delivered += $d;
        }

        $overallRate = $total > 0 ? (int) round(($delivered / $total) * 100) : 0;

        return [$mapped, $overallRate];
    }
}
