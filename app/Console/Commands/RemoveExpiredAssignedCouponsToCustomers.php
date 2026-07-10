<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RemoveExpiredAssignedCouponsToCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coupons:remove-expire-assigned-coupons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired assigned coupons from customers';

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
        $assignments = \App\Models\CouponCustomerAssignment::whereNotNull('expire_date')
            ->where('expire_date', '<', now())
            ->get();

        foreach ($assignments as $assignment) {
            $assignment->delete();
        }
        $this->info($assignments->count() . ' expired coupons removed successfully.');
    }
}
