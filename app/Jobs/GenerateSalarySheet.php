<?php

namespace App\Jobs;

use App\Services\SalaryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSalarySheet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $year,
        public int $month,
        public ?int $generatedBy = null
    ) {}

    public function handle(SalaryService $salaryService): void
    {
        if (get_setting('enable_attendance_management', 0) == 1 && get_setting('enable_salary_sheet_generation', 0) == 1){
            try{
                $salarySheet = $salaryService->generateSalarySheet($this->month, $this->year, $this->generatedBy);

                Log::info("Salary sheet generated for {$this->month}/{$this->year} with ID: {$salarySheet->id}");
            } catch(\Exception $e){
                Log::error("Failed to generate salary sheet for {$this->month}/{$this->year}: " . $e->getMessage());
            }
        }
    }
}
