<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ChangeOrderStatusFromOnTheWayToDelivered extends Seeder
{
    public function run()
    {
        \App\Models\Order::where('delivery_status', 'on_the_way')
            ->where('created_at', '<', now()->subMonth()) // Only change orders older than 1 month
            ->chunkById(100, function ($orders) {
                foreach ($orders as $order) {
                    $order->update([
                        'delivery_status' => 'delivered',
                        'payment_status' => 'paid',
                    ]);
                }
                echo $orders->count() .' orders updated to delivered status.'.PHP_EOL;
            });
    }
}
