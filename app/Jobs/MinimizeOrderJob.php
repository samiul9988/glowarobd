<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class MinimizeOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $targetAveragePerMonth = 200000; // 2 lakh
    protected $batchSize = 200; // Process deletions in batches
    protected $dbConnection = 'staging';

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::channel('custom')->info('MinimizeOrderJob: Started');

            $conn = DB::connection($this->dbConnection);

            // Get all year-month combinations from orders
            $monthlyGroups = $conn->table('orders')
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            $totalDeleted = 0;

            foreach ($monthlyGroups as $group) {
                $deleted = $this->processMonthlyOrders($group->year, $group->month);
                $totalDeleted += $deleted;
            }

            Log::channel('custom')->info("MinimizeOrderJob: Completed. Total orders deleted: {$totalDeleted}");
        } catch (\Exception $e) {
            Log::channel('custom')->error('MinimizeOrderJob: Error - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process orders for a specific month and year
     */
    protected function processMonthlyOrders(int $year, int $month): int
    {
        Log::channel('custom')->info("Processing orders for {$year}-{$month}");

        $conn = DB::connection($this->dbConnection);
        // Get all orders for this month
        $ordersQuery = $conn->table('orders')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('delivery_status', ['returned', 'cancelled']);

        $totalOrders = $ordersQuery->count();
        if ($totalOrders === 0) {
            return 0;
        }

        $currentTotal = max(0, round($ordersQuery->sum('grand_total'), 2));
        if ($currentTotal <= 0 || $currentTotal <= $this->targetAveragePerMonth) {
            Log::channel('custom')->info("{$year}-{$month}: Current total ({$currentTotal}) is within target. No deletion needed.");
            return 0;
        }

        // Calculate how many orders we need to keep to maintain target average
        try{
            $averagePerOrder = $currentTotal / $totalOrders;
            $ordersToKeep = max(1, ceil($this->targetAveragePerMonth / $averagePerOrder));
        } catch (\DivisionByZeroError $e) {
            $ordersToKeep = 0;
        }

        if ($ordersToKeep >= $totalOrders) {
            Log::channel('custom')->info("{$year}-{$month}: {$totalOrders} orders, Total={$currentTotal}, No deletion needed.");
            return 0;
        }

        Log::channel('custom')->info("{$year}-{$month}: {$totalOrders} orders, Total={$currentTotal}, Keeping {$ordersToKeep} orders, Deleting " . ($totalOrders - $ordersToKeep) . " orders.");

        // Randomly select orders to keep
        $allOrderIds = $ordersQuery->pluck('id')->toArray();
        if (empty($allOrderIds)) {
            return 0;
        }

        shuffle($allOrderIds);

        $tempTotal = PHP_INT_MAX;
        $orders = collect();
        $attempts = 0;
        $maxAttempts = 10;

        while ($tempTotal > $this->targetAveragePerMonth && $attempts < $maxAttempts) {
            $sampleIds = array_slice($allOrderIds, 0, $ordersToKeep);
            $orders = $conn->table('orders')->whereIn('id', $sampleIds)->get(['id', 'grand_total']);
            $tempTotal = round($orders->sum('grand_total'), 2);
            shuffle($allOrderIds);
            $attempts++;
        }

        Log::channel('custom')->info("{$year}-{$month}: Selected orders total after {$attempts} attempts: {$tempTotal}");

        $ordersToKeepIds = $orders->pluck('id')->toArray();
        // $ordersToKeepIds = $ordersQuery
        //     ->inRandomOrder()
        //     ->limit($ordersToKeep)
        //     ->pluck('id')
        //     ->toArray();

        // Select orders to DELETE
        $ordersToDeleteIds = $ordersQuery
            ->whereNotIn('id', $ordersToKeepIds)
            ->pluck('id')
            ->toArray();

        if (empty($ordersToDeleteIds)) {
            return 0;
        }

        // Delete orders and related data in batches
        $deletedCount = 0;
        foreach (array_chunk($ordersToDeleteIds, $this->batchSize) as $batch) {
            $deletedCount += $this->deleteOrdersWithRelations($batch);
            usleep(100000); // short pause (0.1s) between batches to reduce DB stress
        }

        // Verify the result
        $remainingOrders = $conn->table('orders')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('delivery_status', ['returned', 'cancelled'])
            ->get(['id', 'grand_total']);

        $newTotal = round($remainingOrders->sum('grand_total'), 2);

        Log::channel('custom')->info("{$year}-{$month}: Deleted " . ($totalOrders - $ordersToKeep) . " orders, Kept {$ordersToKeep} orders, New Total: {$newTotal}");

        return $deletedCount;
    }

    /**
     * Delete orders with their related data
     */
    protected function deleteOrdersWithRelations(array $orderIds): int
    {
        return DB::connection($this->dbConnection)->transaction(function () use ($orderIds) {
            $conn = DB::connection($this->dbConnection);
            // Delete related records (ensure foreign data cleared before order delete)
            $conn->table('order_details')->whereIn('order_id', $orderIds)->delete();
            $conn->table('refund_requests')->whereIn('order_id', $orderIds)->delete();
            $conn->table('order_logs')->whereIn('order_id', $orderIds)->delete();
            $conn->table('order_cancellations')->whereIn('order_id', $orderIds)->delete();
            $conn->table('crm_orders_feedbacks')->whereIn('order_id', $orderIds)->delete();
            $conn->table('coupon_usages')->whereIn('order_id', $orderIds)->delete();
            $conn->table('call_logs')
                ->where('reference_type', Order::class)
                ->whereIn('reference_id', $orderIds)
                ->delete();
            $conn->table('payments')
                ->where('reference_type', Order::class)
                ->whereIn('reference_id', $orderIds)
                ->delete();

            // Handle order_returns + order_return_items
            $orderReturnIds = $conn->table('order_returns')
                ->whereIn('order_id', $orderIds)
                ->pluck('id');

            if ($orderReturnIds->isNotEmpty()) {
                $conn->table('order_return_items')->whereIn('order_return_id', $orderReturnIds)->delete();
                $conn->table('order_returns')->whereIn('id', $orderReturnIds)->delete();
            }

            // Finally delete orders
            return $conn->table('orders')->whereIn('id', $orderIds)->delete();
        });
    }
}
