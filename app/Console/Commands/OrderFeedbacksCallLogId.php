<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OrderFeedbacksCallLogId extends Command
{
    protected $signature = 'fix:call_log_id_in_feedbacks';

    protected $description = 'Fix call log IDs in order feedbacks';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $updatedCount = 0;

        \App\Models\OrderFeedback::where('call_log_id', 'like', '%\_%')
            ->chunkById(100, function ($feedbacks) use (&$updatedCount) {
                foreach ($feedbacks as $item) {
                    if (strpos($item->call_log_id, '_') === false) {
                        continue;
                    }

                    [$staffId, $userId] = explode('_', $item->call_log_id);

                    $callLog = \App\Models\CallLog::where('called_by', $staffId)
                        ->where('reference_type', \App\Models\User::class)
                        ->where('reference_id', $userId)
                        ->first();

                    if ($callLog) {
                        $item->call_log_id = $callLog->id;
                        $item->save();
                        $updatedCount++;
                    }
                }
            });

        $this->info($updatedCount . ' feedbacks call log id fixed.');
    }
}
