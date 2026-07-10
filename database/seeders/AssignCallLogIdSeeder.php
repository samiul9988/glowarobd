<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CallLog;
use App\Models\OrderFeedback;
use Illuminate\Database\Seeder;

class AssignCallLogIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrderFeedback::query()
            ->with('order:id,user_id')
            ->whereNull('call_log_id')
            ->chunkById(100, function ($feedbacks) {
                foreach ($feedbacks as $feedback) {
                    $callLog = CallLog::where('reference_id', $feedback->order->user_id)
                        ->where('reference_type', User::class)
                        ->where('called_by', $feedback->created_by)
                        ->latest()
                        ->first();

                    if ($callLog) {
                        // file_put_contents(
                        //     storage_path('logs/feedback_debug.log'),
                        //     $feedback->id . ' --- ' . $callLog->id . PHP_EOL,
                        //     FILE_APPEND
                        // );

                        $feedback->call_log_id = $callLog->id;
                        $feedback->save();
                    }
                }
            });
    }
}
