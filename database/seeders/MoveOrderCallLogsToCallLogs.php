<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\CallLog;
use App\Models\OrderCallLog;
use Illuminate\Database\Seeder;

class MoveOrderCallLogsToCallLogs extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrderCallLog::chunk(300, function ($logs) {
            foreach ($logs as $log) {
                $order = Order::find($log->order_id);
                if ($order) {
                    $order->addCallLog([
                        'called_by' => $log->called_by,
                        'status' => $log->status,
                        'duration' => $log->duration,
                        'note' => $log->note,
                        'created_at' => $log->created_at,
                        'updated_at' => $log->updated_at
                    ]);
                }
            }
        });
    }
}
