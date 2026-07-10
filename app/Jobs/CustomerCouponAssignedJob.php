<?php

namespace App\Jobs;

use App\Mail\CustomerCouponAssignedMail;
use App\Utility\SmsUtility;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerCouponAssignedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $info;

    public function __construct($info)
    {
        $this->info = $info;
    }

    public function handle()
    {
        try {
            if(!is_null($this->info['email']) && filter_var($this->info['email'], FILTER_VALIDATE_EMAIL)) {
                Mail::to($this->info['email'])->send(new CustomerCouponAssignedMail($this->info));
            } elseif (!is_null($this->info['phone'])) {
                SmsUtility::coupon_assigned($this->info);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send coupon assigned sms/email: '.$e->getMessage());
        }
    }
}
