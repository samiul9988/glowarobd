<?php

namespace App\Jobs;

use App\Models\{Smsuser, User};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $appName;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     *
     * @param array $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->appName = config('app.name');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $content = $this->data['content'];
            $templateId = $this->data['template_id'] ?? null;

            // Handle registered users (using lazyById + pluck for memory efficiency)
            if (isset($this->data['register_type']) && $this->data['register_type'] == 'All') {
                User::where('banned', 0)
                    ->whereNotNull('phone')
                    ->where('user_type', 'customer')
                    ->whereRaw('LENGTH(phone) >= 11')
                    ->lazyById(500, 'id')
                    ->pluck('phone')
                    ->unique()
                    ->each(function ($phone) use ($content, $templateId) {
                        $this->sendSms($phone, $content, $templateId);
                    });
            } elseif (!empty($this->data['user_phones']) && is_array($this->data['user_phones'])) {
                foreach (array_unique($this->data['user_phones']) as $phone) {
                    $this->sendSms($phone, $content, $templateId);
                }
            }

            // Handle unregistered users (using lazyById + pluck for memory efficiency)
            if (isset($this->data['unregister_type']) && $this->data['unregister_type'] == 'All') {
                Smsuser::where('status', 1)
                    ->whereNotNull('mobile_number')
                    ->whereRaw('LENGTH(mobile_number) >= 11')
                    ->lazyById(500, 'id')
                    ->pluck('mobile_number')
                    ->unique()
                    ->each(function ($phone) use ($content, $templateId) {
                        $this->sendSms($phone, $content, $templateId);
                    });
            } elseif (!empty($this->data['unregister_user_phones']) && is_array($this->data['unregister_user_phones'])) {
                foreach (array_unique($this->data['unregister_user_phones']) as $phone) {
                    $this->sendSms($phone, $content, $templateId);
                }
            }

            Log::info('Bulk SMS job completed successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to send bulk SMS: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send SMS to a single phone number.
     *
     * @param string $phone
     * @param string $content
     * @param string|null $templateId
     * @return void
     */
    protected function sendSms(string $phone, string $content, ?string $templateId = null)
    {
        try {
            sendSMS($phone, $content, $templateId, 'bulk_sms');
        } catch (\Exception $e) {
            Log::channel('sms')->error("Failed to send SMS to {$phone}: " . $e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::channel('sms')->error('SendBulkSmsJob failed: ' . $exception->getMessage());
    }
}
