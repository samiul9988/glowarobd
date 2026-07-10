<?php

namespace App\Listeners;

use App\Events\InsertAccHead;
use App\Models\AccHead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;
use Throwable;

class CreateNewAccHead
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\InsertAccHead  $event
     * @return void
     */
    public function handle(InsertAccHead $event)
    {
        AccHead::create($event->data);
    }

    /**
     * Handle a job failure.
     */
    public function failed(InsertAccHead $event, Throwable $exception): void
    {
        Log::error($exception);
    }
}
