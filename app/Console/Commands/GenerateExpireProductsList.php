<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\BusinessSetting;
use Illuminate\Console\Command;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class GenerateExpireProductsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:expire-soon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a list of products that are expiring soon';

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
        $beforeDays = (int) get_setting('expire_products_alert_duration', 7);
        $expireDate = now()->addDays($beforeDays);

        $items = PurchaseOrderItem::with('product')
            ->whereNotNull('expire_date')
            ->whereDate('expire_date', '<=', $expireDate)
            ->where('left_qty', '>', 0)
            ->get();

        $expireSoonProductIds = $items->pluck('product_id')->unique();
        $expireSoonCount = $expireSoonProductIds->count();

        BusinessSetting::updateOrCreate(
            ['type' => 'expire_products_count'],
            ['value' => $expireSoonCount]
        );

        Cache::forget('business_settings'); // Clear cache to ensure the updated count is reflected
        get_setting('expire_products_count', $expireSoonCount); // Recache the setting

        if ($expireSoonCount > 0) {
            $this->info("There are {$expireSoonCount} products expiring in the next {$beforeDays} days.");
        } else {
            $this->info("No products are expiring in the next {$beforeDays} days.");
        }

        return 0;
    }
}
