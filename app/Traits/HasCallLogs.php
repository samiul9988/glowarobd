<?php

namespace App\Traits;

use App\Models\CallLog;

trait HasCallLogs
{
    public function callLogs()
    {
        return $this->morphMany(CallLog::class, 'reference')->latest();
    }

    public function addCallLog(array $data)
    {
        return $this->callLogs()->create($data);
    }
}
