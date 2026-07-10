<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LastThreeMonthsOrdersExport;

class ExportLastThreeMonthsOrders extends Command
{
    protected $signature = 'export:orders';

    protected $description = 'Export orders from the last three months';

    public function handle()
    {
        $startDate = now()->subMonth(3)->startOfMonth();
        $endDate = now()->subDay(1)->endOfDay();

        $fileName = 'orders_between_' . $startDate->format('Y_m_d') . '_and_' . $endDate->format('Y_m_d') . '_' . time() . '.csv';

        Excel::store(new LastThreeMonthsOrdersExport(), 'exports/' . $fileName, 'public', \Maatwebsite\Excel\Excel::CSV);

        $this->info("Export completed: storage/app/public/exports/{$fileName}");
    }
}
