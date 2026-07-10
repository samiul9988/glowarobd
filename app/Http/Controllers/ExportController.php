<?php

namespace App\Http\Controllers;

use App\Jobs\ExportCustomerAddresses;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function export()
    {
        $job = new ExportCustomerAddresses();
        dispatch($job);

        return response()->json([
            'message' => 'Export started',
            'file' => $job->filePath  // Return path for future download
        ]);
    }
}