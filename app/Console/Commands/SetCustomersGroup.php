<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Customergroup;
use App\Models\Customeringroup;
use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;

class SetCustomersGroup extends Command
{
    protected $signature = 'customers:setgroup';
    protected $description = 'Set appropriate group for all individual customers';

    public function handle()
    {
        // Count total target customers (for progress bar)
        $total = User::active()->where('user_type', 'customer')->count();

        if ($total === 0) {
            $this->info("No active customers found.");
            return Command::SUCCESS;
        }

        // Initialize progress bar
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        // Process in chunks
        User::active()->where('user_type', 'customer')->chunkById(300, function ($users) use ($bar) {
                $defaultGroup = Customergroup::where('min_order_qty', 0)->first();
                $defaultGroupId = $defaultGroup->id ?? 1;

                foreach ($users as $user) {

                    $totalDeliveredAmount = Order::where([
                        'user_id' => $user->id,
                        'delivery_status' => 'delivered'
                    ])->sum('grand_total');

                    $deliveredCount = Order::where([
                        'user_id' => $user->id,
                        'delivery_status' => 'delivered'
                    ])->count();

                    $user->update(['delivered_order' => $deliveredCount]);

                    $eligibleGroup = getCustomerGroup($deliveredCount, $totalDeliveredAmount)
                        ?? $defaultGroupId;

                    Customeringroup::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'customer_groups_id' => $eligibleGroup,
                            'status' => 1,
                        ]
                    );

                    // Advance progress bar
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info('Customer group reset command was successful!');

        return Command::SUCCESS;
    }
}
