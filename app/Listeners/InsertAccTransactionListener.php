<?php

namespace App\Listeners;

use App\Events\InsertAccTransaction;
use App\Models\AccTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;
use Throwable;

class InsertAccTransactionListener
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
     * @param  \App\Events\InsertAccTransaction  $event
     * @return void
     */
    public function handle(InsertAccTransaction $event)
    {
        AccTransaction::create($event->data);
    }

    /**
     * Handle a job failure.
     */
    public function failed(InsertAccTransaction $event, Throwable $exception): void
    {
        Log::error($exception);
    }
}
