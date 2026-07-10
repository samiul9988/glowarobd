<?php

namespace App\Jobs;

use App\Exports\CustomerAddressExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ExportCustomerAddresses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filePath;

    public function __construct()
    {
        $this->filePath = 'customer_exports/customer_addresses_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
    }

    public function handle()
    {
        // Ensure directory exists
        if (!file_exists(storage_path('app/customer_exports'))) {
            mkdir(storage_path('app/customer_exports'), 0755, true);
        }

        // Process export
        Excel::store(new CustomerAddressExport, $this->filePath);
    }
}