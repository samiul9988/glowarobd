<?php

namespace Modules\Waitlist\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Modules\Waitlist\Entities\Waitlist;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Waitlist\Emails\ProductBackInStockMail;

class NotifyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 120; // 2 minutes between retries

    public $modelId;
    public function __construct(int $modelId)
    {
        $this->modelId = $modelId;
    }

    public function handle()
    {
        Log::channel('custom')->info("NotifyJob dispatched", ['id' => $this->modelId]);

        $entry = $this->findWaitlistEntry();
        if (! $entry) return;

        try {
            $this->sendNotification($entry);

            Log::channel('custom')->info("Waitlist notification completed", [
                'id' => $entry->id,
                'type' => $entry->contact_type,
            ]);

        } catch (\Exception $e) {
            $entry->update([
                'notified'     => false,
                'notified_at'  => null,
            ]);
            Log::channel('sms')->error("Waitlist notification failed", [
                'id'    => $this->modelId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Fetch the waitlist entry or log missing.
     */
    protected function findWaitlistEntry(): ?Waitlist
    {
        $entry = Waitlist::find($this->modelId);

        if (! $entry) {
            Log::error("Waitlist entry not found", ['id' => $this->modelId]);
            return null;
        }

        // If already notified more than 5 minutes ago, skip
        if ($entry->notified && !is_null($entry->notified_at) && $entry->notified_at->lt(now()->subMinutes(5))) {
            Log::channel('custom')->info("Waitlist entry already notified", ['id' => $entry->id]);
            return null;
        }

        return $entry;
    }

    /**
     * Decide notification method (email or sms)
     */
    protected function sendNotification(Waitlist $entry): void
    {
        if ($entry->contact_type === 'email') {
            $this->sendEmail($entry);
        } else {
            $this->sendSms($entry);
        }
    }

    /**
     * Send email notification.
     */
    protected function sendEmail(Waitlist $entry): void
    {
        Mail::to($entry->contact)->send(
            new ProductBackInStockMail($entry->product)
        );

        Log::channel('custom')->info("Waitlist email sent", [
            'email' => $entry->contact,
            'id'    => $entry->id,
        ]);
    }

    /**
     * Send SMS notification.
     */
    protected function sendSms(Waitlist $entry): void
    {
        $link = to_frontend(route('product', $entry->product->slug));

        $content = "Good news! The product you’re waiting for is now available: {$link}\nHurry before it runs out again!";

        $response = sendSMS(
            $entry->contact,
            $content,
            type: 'waitlist'
        );

        Log::channel('sms')->info("Waitlist SMS sent", [
            'contact'  => $entry->contact,
            'response' => $response,
            'id'       => $entry->id,
        ]);
    }
}
