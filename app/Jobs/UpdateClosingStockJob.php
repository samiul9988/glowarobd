<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ProductsClosingStock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateClosingStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $product_id;
    protected $date;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($product_id, $date, $data)
    {
        $this->product_id = $product_id;
        $this->date = $date;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $openStock = ($this->data->opening_purchase + $this->data->opening_plus_adjustment) - ($this->data->opening_sell +  $this->data->opening_minus_adjustment);
        $adjustments = ($this->data->plus_adjustments - $this->data->minus_adjustments);
        $closingStock = $openStock + $this->data->purchases - $this->data->sales + $adjustments;
        $dd = ProductsClosingStock::updateOrCreate([
            'product_id' => $this->product_id,
            'date' => Carbon::parse($this->date)->format('Y-m-d 00:00:00'),
        ], [
            'closing_stock' => $closingStock,
            'last_opening_purchase' => $this->data->opening_purchase,
            'last_opening_sale' => $this->data->opening_sell,
            'last_opening_plus_adjustment' => $this->data->opening_plus_adjustment,
            'last_opening_minus_adjustment' => $this->data->opening_minus_adjustment,
        ]);

        Log::info('Closing stock updated: ' . $dd);
    }
}
