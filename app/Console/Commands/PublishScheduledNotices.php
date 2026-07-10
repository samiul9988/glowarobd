<?php

namespace App\Console\Commands;

use App\Models\Notice;
use Illuminate\Console\Command;

class PublishScheduledNotices extends Command
{
    protected $signature = 'notices:publish';
    protected $description = 'Publish scheduled notices that are due for publication';

    public function handle()
    {
        $count = Notice::where('status', 'scheduled')
            ->where('publish_at', '<=', now())
            ->update(['status' => 'published']);

        $this->info("Published {$count} scheduled notices.");
        
        return 0;
    }
}