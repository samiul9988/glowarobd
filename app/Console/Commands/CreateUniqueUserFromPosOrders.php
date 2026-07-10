<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\{Order, User, Customergroup, Customeringroup};

class CreateUniqueUserFromPosOrders extends Command
{
    protected $signature = 'pos:create-users';

    protected $description = 'Create unique users from POS orders without users';

    public function handle()
    {
        $total = Order::whereNull('user_id')
            ->where('order_source', 'pos')
            ->whereNotNull('shipping_address->phone')
            ->count();

        // Initialize progress bar
        $bar = $this->output->createProgressBar($total);
        $this->info("Creating users from POS orders ...");
        $bar->start();

        Log::channel('custom')->info("Starting POS users creation process. Total orders to process: {$total}");

        $phones = [];
        Order::whereNull('user_id')
            ->where('order_source', 'pos')
            ->whereNotNull('shipping_address->phone')
            ->chunkById(500, function ($orders) use (&$phones, $bar) {
                foreach ($orders as $order) {
                    $shippingAddress = json_decode($order->shipping_address, true);
                    $phone = trim(str_replace(['+88', '-', ' '], '', $shippingAddress['phone'] ?? ''));

                    // Validate phone number
                    if (strlen($phone) != 11 || in_array($phone, $phones)) {
                        $bar->advance();
                        continue;
                    }

                    $phones[] = $phone;
                    $payload = [
                        'name' => $shippingAddress['name'] ?? 'POS Customer',
                        'phone' => $phone,
                        'address' => $shippingAddress['address'] ?? null,
                    ];

                    $user = $this->createUser($payload);

                    if ($user) {
                        $order->user_id = $user->id;
                        $order->guest_id = null;
                        $order->save();
                        $this->fixUserGroup($user->id);
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("Users created successfully.");

        Log::channel('custom')->info("POS users creation process completed successfully.");
        return Command::SUCCESS;
    }

    protected function createUser($payloads): User
    {
        $user = User::whereIn('phone', [$payloads['phone'], '+88'.$payloads['phone']])->first();

        if(!$user) {
            $user = new User;
            $user->name = $payloads['name'] ?? 'POS User';
            $user->email = $payloads['email'] ?? null;
            $user->address = $payloads['address'] ?? null;
            $user->phone = $payloads['phone'];
            $password = Str::random(rand(8,10));
            $user->password = bcrypt($password);
            // $user->temp_password = $password;
            $user->email_verified_at = now()->toDateTimeString();
            $user->recent_login = null;
            $user->save();

            $customer = new \App\Models\Customer;
            $customer->user_id = $user->id;
            $customer->save();

            $group = Customergroup::orderBy('ordering', 'asc')->first();

            if($group->count() > 0){
                $first_group = new Customeringroup;
                $first_group->user_id = $user->id;
                $first_group->customer_groups_id = $group->id;
                $first_group->status = 1;
                $first_group->save();
            }
        }
        return $user;
    }

    protected function fixUserGroup($userId): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            $defaultGroup = Customergroup::where('min_order_qty', 0)->first();
            $defaultGroupId = $defaultGroup->id ?? 1;

            $totalDeliveredOrders = Order::where([
                'user_id' => $userId,
                'delivery_status' => 'delivered'
            ])->get();

            $totalDeliveredAmount = round($totalDeliveredOrders->sum('grand_total') ?? 0);
            $deliveredCount = $totalDeliveredOrders->count();

            $eligibleGroup = getCustomerGroup($deliveredCount, $totalDeliveredAmount) ?? $defaultGroupId;

            Customeringroup::updateOrCreate(
                ['user_id' => $userId],
                [
                    'customer_groups_id' => $eligibleGroup,
                    'status' => 1,
                ]
            );

            $user->delivered_order = $deliveredCount;
            $user->save();

            return true;
        } catch (\Exception $e) {
            $this->error("Failed to update user group for user {$userId}: " . $e->getMessage());
            return false;
        }
    }
}
