<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class NotificationMarkAsRead extends Command
{
    protected $signature = 'notification:mark-as-read 
                            {--chunk=1000 : Number of records to process at a time} 
                            {--days=7 : Number of days to consider as "old"}';

    protected $description = 'Mark notifications as read';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $days = (int)$this->option('days');
        $chunkSize = (int)$this->option('chunk');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Marking notifications older than {$days} days as read...");
        $this->info("Processing in chunks of {$chunkSize} records...");

        $totalUpdated = 0;
        $query = DB::table('notifications')
            ->whereNull('read_at')
            ->where('created_at', '<', $cutoffDate);

        $query->chunkById($chunkSize, function ($notifications) use (&$totalUpdated) {
            $ids = $notifications->pluck('id')->toArray();

            $updated = DB::table('notifications')
                ->whereIn('id', $ids)
                ->update(['read_at' => now()]);

            $totalUpdated += $updated;
            $this->info("Processed chunk: {$updated} notifications marked as read");
        });

        $this->info("Completed! Total marked as read: {$totalUpdated}");
        return 0;
    }
}
