<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\BusinessSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UnlockOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:unlock';
    protected $description = 'Unlock orders that have been locked for a specified duration';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $duration = Cache::remember('order_lock_duration', now()->addDay(), function () {
            return BusinessSetting::where('type', 'order_lock_duration')->first()->value ?? 10;
        });

        Order::where('locked', true)
            // ->where('locked_at', '<', now()->subMinutes($duration))
            ->each(function ($order) use ($duration) {
                if($order->delivery_status == 'pending' || $order->locked_at < now()->subMinutes($duration)){
                    $order->unlock();
                }
            });
        $this->info('Orders unlocked successfully.');
    }
}
