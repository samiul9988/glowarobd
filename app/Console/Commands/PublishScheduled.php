<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PublishScheduled extends Command
{
    protected $signature = 'publish:scheduled {type?}';
    protected $description = 'Publish scheduled items dynamically';

    public function handle()
    {
        $type = $this->argument('type');

        $map = config('publishable.models');

        if ($type) {
            if (!isset($map[$type])) {
                $this->error("Invalid type: {$type}");
                return 1;
            }

            $this->publish($type, $map[$type]);
        } else {
            foreach ($map as $key => $config) {
                $this->publish($key, $config);
            }
        }

        return 0;
    }

    private function publish($type, $config)
    {
        $model = $config['model'];
        $dateField = $config['date_field'];

        $count = $model::where('status', 'scheduled')
            ->where($dateField, '<=', now())
            ->update(['status' => 'published']);

        $this->info("Published {$count} {$type}");
    }
}
