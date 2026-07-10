<?php

namespace App\Console\Commands;

use App\Models\ProductStock;
use App\Models\PurchaseOrderItem;
use App\Models\StockAdjustItem;
use Illuminate\Console\Command;
use Log;

class PurchaseAndAstockAdjustVariantID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:variantMissmatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Purchase And Stock Adjust Variant ID Missmatch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $purchase_items = PurchaseOrderItem::all();
        foreach($purchase_items as $item){
            $product_stock = ProductStock::where('id', $item->variant)->where('product_id', $item->product_id)->first();
            if(empty($product_stock)){
                $product_stock = ProductStock::where('product_id', $item->product_id)->first();
                $item->variant = $product_stock->id;
                $item->save();
            }
        }

        $adjust_items = StockAdjustItem::all();
        foreach($adjust_items as $item){
            $product_stock = ProductStock::where('id', $item->variant)->where('product_id', $item->product_id)->first();
            if(empty($product_stock)){
                $product_stock = ProductStock::where('product_id', $item->product_id)->first();
                $item->variant = $product_stock->id;
                $item->save();
            }
        }

        $this->info('Purchase And Stock Adjust Variant ID Missmatch Fix Successful');
    }
}
