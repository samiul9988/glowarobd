<?php

namespace App\Listeners;

use App\Events\ProductStockAffected;
use App\Models\StockTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateStockTransaction
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
     * @param  \App\Events\ProductStockAffected  $event
     * @return void
     */
    public function handle(ProductStockAffected $event)
    {
        $transaction = $event->transaction;

        try {
            $tran = StockTransaction::create($transaction);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(ProductStockAffected $event, Throwable $exception): void
    {
        Log::error($exception);
    }
}
