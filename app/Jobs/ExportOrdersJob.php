<?php

namespace App\Jobs;

use App\Exports\OrdersExport; // Make sure this is your existing export class
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExportOrdersJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function handle()
    {
        // Handle the export logic in the background
        Excel::store(new OrdersExport($this->orders), 'orders.xlsx');
        
        // Optional: Notify the user when the export is complete
        // You can send an email, or trigger an event here
    }
}
